<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AppAnswer;
use App\Models\AppScore;
use App\Models\Category;
use Illuminate\Http\Request;
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
        $year = $application ? $application->year : date('Y');
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

        // Delete any existing grades for this user's application
        // This includes grades by applicant_id and grades where question_id matches question UUIDs from this application
        $questionUuids = [];
        if ($application->form) {
            foreach ($application->form as $question) {
                if (isset($question['id'])) {
                    $questionUuids[] = $question['id'];
                }
            }
        }
        
        AppScore::where('applicant_id', $user->id)
            ->orWhereIn('question_id', $questionUuids)
            ->delete();

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
                AppAnswer::updateOrCreate(
                    [
                        'applicant_id' => $user->id,
                        'question_id' => $preferenceQuestion['id'],
                    ],
                    [
                        'answer' => json_encode($preferenceData),
                    ]
                );
            }
        }

        \Log::info('Application submitted successfully for user: ' . $user->id);
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
}
