<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Gemini\Laravel\Facades\Gemini;
use App\Models\Review;
use App\Models\AiReply;
use Illuminate\Support\Str;
use Google\Cloud\Gemini\V1\GenerateContentResponse;

class AIController
{
    public function index()
    {
        Log::info('Accessing AI generation view', ['user_id' => Auth::id()]);
        return view('aigeneration');
    }

    public function aigeneration(Request $request)
    {
        try {
            $request->validate([
                'review_id' => 'required|integer',
                'num_replies' => 'nullable|integer|min:1|max:10',
                'start_from' => 'nullable|integer|min:0',
            ]);

            $review = Review::findOrFail($request->input('review_id'));

            // Check if user has access to this review
            if ($review->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to review'
                ], 403);
            }

            $numReplies = $request->input('num_replies', 1);
            $startFrom = $request->input('start_from', 0);

            $response = $this->generateAppropriateResponse($review, $numReplies, $startFrom);

            return response()->json([
                'success' => true,
                'replies' => $response['replies'],
                'total_count' => $startFrom + count($response['replies'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error during AI reply generation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error generating AI reply.'
            ], 500);
        }
    }

    private function createPrompt($review, $numReplies)
    {
        $toneAndStructure = match ($review->star_rating) {
            1, 2 => [
                "tone" => "- Express sincere apology and urgent commitment to resolution\n- Professional yet empathetic tone\n- Include specific solution steps",
                "structure" => "Acknowledge issue → Apologize → Provide solution → Offer contact",
                "emoticon" => "Do not use emoticons"
            ],
            3 => [
                "tone" => "- Balanced and constructive tone\n- Focus on specific improvements\n- Encourage detailed feedback",
                "structure" => "Acknowledge feedback → Address concerns → Suggest improvements → Invite further feedback",
                "emoticon" => "Do not use emoticons"
            ],
            4 => [
                "tone" => "- Positive and appreciative tone\n- Highlight specific positives\n- Encourage continued engagement",
                "structure" => "Acknowledge feedback → Reinforce positives → Encourage future engagement",
                "emoticon" => "Optional use of 😊 where appropriate"
            ],
            5 => [
                "tone" => "- Enthusiastic and grateful tone\n- Reinforce positive experience\n- Express excitement for future interaction",
                "structure" => "Acknowledge feedback → Reinforce positives → Encourage future engagement",
                "emoticon" => "Occasional use of ✨ or 🌟 where natural"
            ],
            default => [
                "tone" => "- Balanced and constructive tone\n- Focus on specific improvements\n- Encourage detailed feedback",
                "structure" => "Acknowledge feedback → Address concerns → Suggest improvements → Invite further feedback",
                "emoticon" => "Do not use emoticons"
            ]
        };

        return "Review Details:
        Star Rating: {$review->star_rating}/5
        Customer Name: {$review->reviewer_name}
        Review: '{$review->comment}'
        
        Instructions:
        Generate exactly {$numReplies} unique customer service replies following these guidelines:
        
        1. Language & Style:
        - Use the same language as the review
        - Use proper grammar and punctuation
        - Keep customer name unchanged (no translation)
        - Natural, conversational tone
        - Avoid generic customer service phrases
        
        2. Response Format:
        - Each reply should be 1-50 words based on context
        - Include reviewer's name naturally within response (never at start)
        - End with forward-looking statement or gratitude
        
        3. Tone Guidelines:
        {$toneAndStructure['tone']}
        
        4. Content Structure:
        {$toneAndStructure['structure']}
        
        5. Emoticon Usage:
        {$toneAndStructure['emoticon']}
        
        6. Natural Language Requirements:
        - Write as a human customer service representative would speak
        - Use casual language when appropriate
        - Include natural speech patterns
        - Vary sentence structures
        - Add subtle imperfections for human-like text
        - Avoid robotic language
        - Use relatable and empathetic language
        
        7. Avoid AI Patterns:
        - Don't use repetitive structures
        - Avoid overly perfect grammar
        - Skip generic phrases like 'We value your feedback'
        - Never use mechanical transitions
        - Vary greeting and closing phrases
        - Don't overuse customer's name
        - Avoid perfectly structured sentences
        
        FORMAT OF RESPONSE:
        - Return ONLY {$numReplies} replies
        - Each reply on a new line
        - No numbering, analysis, or additional text
        - No introductory text or explanations
        - Just the replies, nothing else";
    }

    private function generateAppropriateResponse($review, $numReplies)
    {
        Log::info('Starting response generation', [
            'review_id' => $review->id,
            'requested_replies' => $numReplies
        ]);

        $responsePrompt = $this->createPrompt($review, $numReplies);

        Log::info('Calling Gemini API', [
            'review_id' => $review->id,
            'model' => 'gemini-1.5-flash-8b-001'
        ]);

        Log::info('Response Prompt:', ['prompt' => $responsePrompt]);

        $response = Gemini::generativeModel('models/gemini-1.5-flash-8b-001')
            ->generateContent($responsePrompt);

        // Check if the response is an object and convert it to a string or array if needed
        if (is_object($response)) {
            if (method_exists($response, 'toArray')) {
                $responseContent = $response->toArray(); // Convert to array if method exists
            } elseif (method_exists($response, 'toJson')) {
                $responseContent = json_decode($response->toJson(), true); // Convert JSON string to array
            } else {
                $responseContent = (array) $response; // Fallback: Convert object to array
            }
        } else {
            // If response is already a string, decode it
            $responseContent = json_decode($response, true);
        }

        // Log the decoded response
        Log::info('Gemini Response:', ['response' => $responseContent]);


        Log::info('Received Gemini API response', [
            'review_id' => $review->id,
            'response_length' => strlen($response->text())
        ]);

        $replies = explode("\n", $response->text());
        $replies = array_map('trim', $replies);
        $replies = array_filter($replies);
        $replies = array_unique($replies);

        Log::info('Processed initial replies', [
            'review_id' => $review->id,
            'initial_reply_count' => count($replies)
        ]);

        $repliesNeeded = $numReplies - count($replies);
        if ($repliesNeeded > 0) {
            Log::warning('Insufficient unique replies generated', [
                'review_id' => $review->id,
                'initial_count' => count($replies),
                'additional_needed' => $repliesNeeded
            ]);

            $additionalReplies = $this->generateAppropriateResponse($review, $repliesNeeded);
            $replies = array_merge($replies, $additionalReplies['replies']);

            Log::info('Generated additional replies', [
                'review_id' => $review->id,
                'total_replies' => count($replies)
            ]);
        }

        $replies = array_values(array_slice($replies, 0, $numReplies));

        Log::info('Storing replies in database', [
            'review_id' => $review->id,
            'reply_count' => count($replies)
        ]);

        foreach ($replies as $index => $replyText) {
            try {
                $uuid = (string) Str::uuid();

                $promptTokenCount = $response->usageMetadata->promptTokenCount ?? null;
                $candidatesTokenCount = $response->usageMetadata->candidatesTokenCount ?? null;
                $totalTokenCount = $response->usageMetadata->totalTokenCount ?? null;


                // Create AI reply entry with token counts
                $aiReply = AiReply::create([
                    'uuid' => $uuid,
                    'review_id' => $review->id,
                    'user_id' => Auth::id(),
                    'reply_text' => $replyText,
                    'model_used' => 'gemini-1.5-flash-8b-001',
                    'input_tokens' => $promptTokenCount,
                    'output_tokens' => $candidatesTokenCount,
                    'total_tokens' => $totalTokenCount,
                ]);

                Log::info('Stored AI reply', [
                    'review_id' => $review->id,
                    'user_id' => Auth::id(),
                    'reply_id' => $aiReply->id,
                    'uuid' => $uuid,
                    'reply_index' => $index + 1
                ]);

            } catch (\Exception $e) {
                Log::error('Error storing AI reply', [
                    'review_id' => $review->id,
                    'user_id' => Auth::id(),
                    'reply_index' => $index + 1,
                    'error' => $e->getMessage()
                ]);
            }
        }


        return ['replies' => $replies];
    }

    public function fetchAIReplies(Request $request, $reviewId)
    {
        Log::info('Fetching AI replies', [
            'review_id' => $reviewId,
            'request_data' => $request->all()
        ]);

        try {
            $request->validate([
                'num_replies' => 'nullable|integer|min:1|max:10',
                'append' => 'nullable|in:0,1,true,false',
            ]);

            Log::info('Request validation passed', [
                'review_id' => $reviewId,
                'num_replies' => $request->input('num_replies'),
                'append' => $request->input('append')
            ]);

            $review = Review::findOrFail($reviewId);
            $numReplies = $request->input('num_replies', 1);
            $append = filter_var($request->input('append', false), FILTER_VALIDATE_BOOLEAN);

            Log::info('Generating responses', [
                'review_id' => $reviewId,
                'num_replies' => $numReplies,
                'append_mode' => $append
            ]);

            $response = $this->generateAppropriateResponse($review, $numReplies);

            Log::info('Response generation completed', [
                'review_id' => $reviewId,
                'generated_replies' => count($response['replies'])
            ]);

            if ($append) {
                return response()->json(['replies' => $response['replies']]);
            }

            return response()->json([
                'success' => true,
                'replies' => $response['replies']
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching AI replies', [
                'review_id' => $reviewId,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Unable to fetch AI replies.'
            ], 500);
        }
    }

    public function getStoredReplies($reviewId)
    {
        try {
            Log::info('Fetching stored replies for review:', [
                'review_id' => $reviewId,
                'user_id' => Auth::id()
            ]);

            // Only fetch replies belonging to the authenticated user
            $replies = AiReply::where('review_id', $reviewId)
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'asc')
                ->get(['reply_text', 'created_at']);

            Log::info('Found replies:', ['count' => $replies->count()]);

            return response()->json([
                'success' => true,
                'replies' => $replies,
                'total_count' => $replies->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching stored replies', [
                'review_id' => $reviewId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch stored replies'
            ], 500);
        }
    }
}