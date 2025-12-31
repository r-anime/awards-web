<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AppAnswer;
use App\Models\AppScore;
use App\Models\Category;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    /**
     * Display the application form
     */
    public function index()
    {
        // Get the application with the latest year
        $application = Application::orderBy('year', 'desc')->first();
        
        // Get categories for the latest application's year, or current year if no application exists
        $year = $application ? $application->year : app('current-year');
        $categories = Category::where('year', $year)->get();
        $mainCategories = $categories->where('type', 'main');
        $nonMainCategories = $categories->where('type', '!=', 'main');
        
        // Load existing answers if user is logged in
        $existingAnswers = [];
        if (auth()->check()) {
            $existingAnswers = AppAnswer::where('applicant_id', auth()->id())
                ->pluck('answer', 'question_id')
                ->toArray();
            
        }
        
        
        return view('application', compact('application', 'year', 'categories', 'mainCategories', 'nonMainCategories', 'existingAnswers'));
    }

    /**
     * Store a newly created application submission
     */
    public function store(Request $request)
    {

        // All fields are now optional - no validation required

        $user = auth()->user();
        $application = Application::orderBy('year', 'desc')->first();
        if (!$application) {
            return redirect()->route('application.index')->with('error', 'No application found.');
        }

        // String tracking changed questions
        $changed_questions = '';
        
        $app_questions = array_reduce($application->form, function($list, $question) {
            $list[$question['id']]=$question['question'];
            return $list;
        }, []);

        // Only delete grades for questions where the answer has changed

        // Process each question answer
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'question_')) {
                $questionId = str_replace('question_', '', $key);
                
                // Handle different answer types
                if (is_array($value)) {
                    $answer = json_encode($value);
                } else {
                    $answer = $value;
                }

                // Check if answer changed
                $existingAnswer = AppAnswer::where('applicant_id', $user->id)
                    ->where('question_id', $questionId)
                    ->first();

                // If new answer, add to log
                if(!$existingAnswer)
                    $changed_questions = $changed_questions.'- '.$app_questions[$questionId]."\n";
                // dd($changed_questions);
                if ($existingAnswer && $existingAnswer->answer !== $answer) {
                    // Changed Answer, add to log
                    $changed_questions = $changed_questions.'- '.$app_questions[$questionId]."\n";
                    // Delete grades for this applicant and this specific question only
                    AppScore::where('applicant_id', $user->id)
                        ->where(function ($q) use ($questionId) {
                            $q->where('question_id', $questionId)
                              ->orWhere('question_uuid', $questionId);
                        })
                        ->delete();
                }

                // Update or create the answer
                AppAnswer::updateOrCreate(
                    [
                        'applicant_id' => $user->id,
                        'question_id' => $questionId,
                    ],
                    [
                        'answer' => $answer,
                    ]
                );
            }
        }

        // Handle preference questions separately
        if ($request->has('preference_non_main') || $request->has('preference_main_order') || $request->has('preference_no_main_order')) {
            $preferenceData = [
                'non_main' => $request->input('preference_non_main', []),
                'main_order' => $request->input('preference_main_order', ''),
                'no_main_order' => $request->input('preference_no_main_order', ''),
            ];
            

            // Find the preference question ID (assuming it's the last question or has a specific type)
            $preferenceQuestion = null;
            if ($application->form) {
                foreach ($application->form as $question) {
                    if ($question['type'] === 'preference') {
                        $preferenceQuestion = $question;
                        break;
                    }
                }
            }

            if ($preferenceQuestion) {
                $preferenceQuestionId = $preferenceQuestion['id'];
                $newPreferenceAnswer = json_encode($preferenceData);

                $existingPreferenceAnswer = AppAnswer::where('applicant_id', $user->id)
                    ->where('question_id', $preferenceQuestionId)
                    ->first();

                # If new preference answer, add to log
                if(!$existingPreferenceAnswer){
                    $changed_questions = $changed_questions.'- '.'Updated Preferences'."\n";
                }

                if ($existingPreferenceAnswer && $existingPreferenceAnswer->answer !== $newPreferenceAnswer) {
                    // Log preference update
                    $changed_questions = $changed_questions.'- '.'Updated Preferences'."\n";
                    
                    AppScore::where('applicant_id', $user->id)
                        ->where(function ($q) use ($preferenceQuestionId) {
                            $q->where('question_id', $preferenceQuestionId)
                              ->orWhere('question_uuid', $preferenceQuestionId);
                        })
                        ->delete();
                }

                AppAnswer::updateOrCreate(
                    [
                        'applicant_id' => $user->id,
                        'question_id' => $preferenceQuestionId,
                    ],
                    [
                        'answer' => $newPreferenceAnswer,
                    ]
                );
            }
        }
        \Log::info('Application submitted successfully for user: ' . $user->id);
        $this->sendToDiscord($user, $changed_questions);
        return redirect()->route('application.index')->with('success', 'Application submitted successfully!');
    }

    /**
     * Set redirect URL after login
     */
    public function setRedirectUrl(Request $request)
    {
        $redirectUrl = $request->input('redirect_url');
        if ($redirectUrl) {
            session(['redirect_after_login' => $redirectUrl]);
        }
        return response()->json(['success' => true]);
    }

    /**
     * Redirect after login
     */
    public function redirectAfterLogin()
    {
        $redirectUrl = session('redirect_after_login', '/');
        session()->forget('redirect_after_login');
        return redirect($redirectUrl);
    }

    /**
     * Send info about changed application to Audit channel
     */
    private function sendToDiscord($user, $changed_questions)
    {
        $webhookUrl = Option::get('audit_channel_webhook', '');
        
        if (empty($webhookUrl)) {
            return; // No webhook configured
        }

        if($changed_questions == '') {
            return; // No changes
        }

        $embed = [
            'title' => 'Application Submission',
            'color' => 0x00ff00, // Green color
            'fields' => [
                [
                    'name' => 'Username',
                    'value' => $user->name,
                    'inline' => true
                ],
                [
                    'name' => 'Submitted At',
                    'value' => now()->format('Y-m-d H:i:s'),
                    'inline' => true
                ],
                [
                    'name' => 'Changed Questions',
                    'value' => $changed_questions,
                    'inline' => false
                ]
            ],
            'timestamp' => now()->toISOString(),
        ];

        $payload = [
            'embeds' => [$embed]
        ];

        try {
            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            // Log error but don't fail the application submission
            \Log::error('Failed to send application submission to Discord: ' . $e->getMessage());
        }
    }
}
