<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function index()
    {
        return view('feedback');
    }

    public function store(Request $request)
    {
        $turnstile_enabled = config('cloudflare-turnstile.enable');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $ipAddress = $request->ip();
        $turnstile_validated = false;
        
        if($turnstile_enabled) {
            $turnstile_token = $request['cf-turnstile-response'];
            
            $turnstile_response = $this->validateTurnstile($turnstile_token, $ipAddress);
            $turnstile_validated = $turnstile_response['success'];
    }
        // Check rate limit
        if (Feedback::hasExceededWeeklyLimit($ipAddress)) {
            return back()->withErrors([
                'message' => 'You have exceeded the weekly limit of 5 feedback submissions. Please try again next week.'
            ])->withInput();
        }

        // Create feedback record
        $feedback = Feedback::create([
            'name' => $request->name,
            'message' => $request->message,
            'ip_hash' => Feedback::generateIpHash($ipAddress),
            'validated' => $turnstile_validated
        ]);

        // If not validated when Turnstile enabled, log failure
        if($turnstile_validated == false && $turnstile_enabled) {
                \Log::info('Feedback id '.$feedback->id.' validation failed: ' . $turnstile_response['error-codes']);
        }

        // If validated, or if Turnstile disabled, send to Discord webhook
        if($turnstile_validated || !$turnstile_enabled) {
            $this->sendToDiscord($feedback);
        }

        return back()->with('success', 'Thank you for your feedback! It has been submitted successfully.');
    }

    // Validate turnstile token
    private function validateTurnstile($token, $remoteip = null) {
        $secret = config('cloudflare-turnstile.secret');
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = [
            'secret' => $secret,
            'response' => $token
        ];

        if ($remoteip) {
            $data['remoteip'] = $remoteip;
        }

        try {
        $response = Http::acceptJson()
            ->post($url, $data);
        } catch(\Exception $e) {
            \Log::error('Failed to validate Turnstile token: ' . $e->getMessage());
            return ['success' => false, 'error-codes' => ['internal-error']];
        }

        if ($response === FALSE) {
            return ['success' => false, 'error-codes' => ['internal-error']];
        }

        return json_decode($response, true);
    }

    private function sendToDiscord(Feedback $feedback)
    {
        $webhookUrl = Option::get('feedback_channel_webhook', '');
        
        if (empty($webhookUrl)) {
            return; // No webhook configured
        }

        $embed = [
            'title' => 'New Feedback Submission',
            'color' => 0x00ff00, // Green color
            'fields' => [
                [
                    'name' => 'Name',
                    'value' => $feedback->name,
                    'inline' => true
                ],
                [
                    'name' => 'Submitted At',
                    'value' => $feedback->created_at->format('Y-m-d H:i:s'),
                    'inline' => true
                ],
                [
                    'name' => 'Message',
                    'value' => $feedback->message,
                    'inline' => false
                ]
            ],
            'timestamp' => $feedback->created_at->toISOString(),
        ];

        $payload = [
            'embeds' => [$embed]
        ];

        try {
            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            // Log error but don't fail the feedback submission
            \Log::error('Failed to send feedback to Discord: ' . $e->getMessage());
        }
    }
}
