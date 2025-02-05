<?php

namespace App\Http\Controllers;

use Exception;
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
    private const STANDARD_PLAN_LIMIT = 700;

    private function getRemainingReplies($userId)
    {
        $usedReplies = AiReply::where('user_id', $userId)->count();

        if (Auth::user()->subscription === 'standard') {
            return max(0, self::STANDARD_PLAN_LIMIT - $usedReplies);
        }

        return PHP_INT_MAX;
    }

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
            $remainingReplies = $this->getRemainingReplies(Auth::id());

            // Check if user has enough remaining replies
            if ($remainingReplies <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Reply limit reached for your plan',
                    'remaining_replies' => 0,
                    'disable_buttons' => true
                ], 403);
            }

            // Adjust number of replies if it would exceed the limit
            if ($numReplies > $remainingReplies) {
                $numReplies = $remainingReplies;
            }

            $response = $this->generateAppropriateResponse($review, $numReplies);

            // Get updated remaining replies after generation
            $updatedRemainingReplies = $this->getRemainingReplies(Auth::id());

            return response()->json([
                'success' => true,
                'replies' => $response['replies'],
                'remaining_replies' => $updatedRemainingReplies,
                'disable_buttons' => $updatedRemainingReplies <= 0
            ]);

        } catch (Exception $e) {
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

    private function createPrompt($review, $numReplies, $detectedLanguage)
    {
        // Extract only the original text if a translation exists
        $comment = $review->comment;
    
        if (str_contains($comment, "(Original)")) {
            // Extract text after "(Original)"
            $commentParts = explode("(Original)", $comment);
            $comment = trim(end($commentParts)); // Take the last part (original text)
        }
    
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
        Review (Original Language Only): '{$comment}'
        Language of review: {$detectedLanguage}
        
        Instructions:
        Generate exactly {$numReplies} unique customer service replies following these guidelines:
        
        1. Language & Style:
        - Use the same language as the review (original language only, do NOT use translated versions)
        - Use proper grammar and punctuation
        - Natural, conversational tone
        - Avoid generic customer service phrases
        - Keep customer name unchanged (no translation, no transliteration)
        Example: If the name is Jay Mepani, it should remain Jay Mepani in all languages
        
        2. Response Format:
        - Each reply should be 1-50 words based on context
        - Include reviewer's name naturally within response (never at start)
        - End with forward-looking statement or gratitude
        - Ensure the response is entirely in the target language except for necessary transliterations.
        
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
    
        8. Original Language Only:
        - Use only the original language from the review
        - Do not use 'Translated by Google' or any translated content
        - Do not add any language indicators like 'Original' or 'Translated' in responses

        9. Translation & Transliteration Rules:
            9.1 Translation Priority:
            - Always prioritize a natural and legitimate translation within the target language when an English word appears.
            - Use contextually appropriate translations instead of relying on transliteration.
            - Refer to '9.3 Some Examples' when determining proper translations (provided separately).
            - DO NOT use transliteration if a proper translation already exists.
            
            9.2 Transliteration (Last Resort):
            - If an English word has no natural and legitimate translation in the target language, use transliteration instead of leaving it in English.
            - Ensure transliterations sound natural and follow the phonetics of the target language.

            9.3 Some Examples:
            - English Word → Gujarati (Preferred Translation / Transliteration) , Hindi (Preferred Translation / Transliteration)
            - Feedback → પ્રતિભાવ (preferred) / ફીડબેક (transliterated) , प्रतिक्रिया (preferred) / फीडबैक (transliterated)
            - Features → સુવિધાઓ (preferred) / ફીચર્સ (transliterated) , विशेषताएँ (preferred) / फीचर्स (transliterated)
            - Issues → મુદ્દાઓ or સમસ્યાઓ (preferred) / ઇશ્યુઝ (transliterated)

            Contextual Example (Gujarati):
            - Input: મને આ એપ બહુ ગમી, પણ થોડા ફીચર્સ ખૂટે છે.
            - Output: Jay Mepani, તમારા પ્રતિભાવ માટે આભાર! અમે નવી સુવિધાઓ ઉમેરવા માટે કામ કરી રહ્યા છીએ. (Uses સુવિધાઓ instead of ફીચર્સ)

            Contextual Example (Hindi):
            - Input: यह ऐप मुझे बहुत पसंद आया, लेकिन कुछ फीचर्स कम हैं।
            - Output: Jay Mepani, आपकी प्रतिक्रिया के लिए धन्यवाद! हम विशेषताओं को जोड़ने पर काम कर रहे हैं। (Uses विशेषताएँ instead of फीचर्स)
        
        10. FORMAT OF RESPONSE:
        - Return ONLY {$numReplies} replies
        - Each reply on a new line
        - No numbering, analysis, or additional text
        - No introductory text or explanations
        - Just the replies, nothing else";
    }

    private function detectLanguage($text)
    {
        Log::info('Starting language detection', [
            'text_length' => strlen($text)
        ]);

        $languageDetectionPrompt = "Detect the language of the following text. Return ONLY the name of the language (in English) with no additional explanation or text:

        Text: '{$text}'";

        try {
            $languageResponse = Gemini::generativeModel('models/gemini-1.5-flash-8b-001')
                ->generateContent($languageDetectionPrompt);

            $detectedLanguage = trim($languageResponse->text());

            Log::info('Language Detection Result', [
                'detected_language' => $detectedLanguage
            ]);

            return $detectedLanguage;
        } catch (Exception $e) {
            Log::error('Language Detection Error', [
                'error' => $e->getMessage(),
                'text' => $text
            ]);

            return 'Unknown';
        }
    }
    
    private function generateAppropriateResponse($review, $numReplies)
    {
        Log::info('Starting response generation', [
            'review_id' => $review->id,
            'requested_replies' => $numReplies
        ]);

        $comment = $review->comment ?? '';
        
        // Remove translation markers if present
        if (str_contains($comment, "(Original)")) {
            $commentParts = explode("(Original)", $comment);
            $comment = trim(end($commentParts));
        }

        // Validate comment is not empty
        if (empty($comment)) {
            Log::error('Empty comment for review', [
                'review_id' => $review->id,
                'original_comment' => $review->comment
            ]);
            
            // Return empty replies or handle as needed
            return ['replies' => []];
        }

        $detectedLanguage = $this->detectLanguage($comment);

        $responsePrompt = $this->createPrompt($review, $numReplies, $detectedLanguage);

        Log::info('Calling Gemini API', [
            'review_id' => $review->id,
            'model' => 'gemini-1.5-flash-8b-001'
        ]);

        Log::info('Response Prompt:', ['prompt' => $responsePrompt]);

        $response = Gemini::generativeModel('models/gemini-1.5-flash-8b-001')
            ->generateContent($responsePrompt);

        function prettifyJson($data)
        {
            if (is_string($data)) {
                $decodedData = json_decode($data, true);
                $data = $decodedData ?: json_decode(json_encode($data), true);
            }
            return json_encode($data, JSON_PRETTY_PRINT);
        }

        $geminiResponse = ['response' => $response];

        Log::info("Gemini Response:\n" . prettifyJson($geminiResponse));

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

            } catch (Exception $e) {
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

            // Check remaining replies BEFORE generating new ones
            $remainingReplies = $this->getRemainingReplies(Auth::id());

            if ($remainingReplies <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Reply limit reached for your plan',
                    'remaining_replies' => 0,
                    'disable_buttons' => true
                ], 403);
            }

            $review = Review::findOrFail($reviewId);
            $numReplies = min($request->input('num_replies', 1), $remainingReplies);
            $append = filter_var($request->input('append', false), FILTER_VALIDATE_BOOLEAN);

            Log::info('Generating responses', [
                'review_id' => $reviewId,
                'num_replies' => $numReplies,
                'remaining_replies' => $remainingReplies,
                'append_mode' => $append
            ]);

            $response = $this->generateAppropriateResponse($review, $numReplies);

            // Get updated remaining replies after generation
            $updatedRemainingReplies = $this->getRemainingReplies(Auth::id());

            if ($append) {
                return response()->json([
                    'success' => true,
                    'replies' => $response['replies'],
                    'remaining_replies' => $updatedRemainingReplies,
                    'disable_buttons' => $updatedRemainingReplies <= 0
                ]);
            }

            return response()->json([
                'success' => true,
                'replies' => $response['replies'],
                'remaining_replies' => $updatedRemainingReplies,
                'disable_buttons' => $updatedRemainingReplies <= 0
            ]);

        } catch (Exception $e) {
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
        } catch (Exception $e) {
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

    public function checkReplyStatus($reviewId)
    {
        try {
            $remainingReplies = $this->getRemainingReplies(Auth::id());
            $totalReplies = AiReply::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'remaining_replies' => $remainingReplies,
                'total_replies' => $totalReplies,
                'disable_buttons' => $remainingReplies <= 0
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error checking reply status'
            ], 500);
        }
    }
}