<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Review;
use App\Models\LocalReview;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ReviewController
{
    protected GoogleAuthService $client;

    public function index($id, Request $request)
    {
        $location = Location::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $location->user_id) {
            return abort(403, 'Unauthorized action.');
        }

        $shouldSync = $request->has('sync');

        try {
            if ($shouldSync) {
                Log::info('Deleting old reviews for location', ['location_id' => $location->id]);
                $location->reviews()->delete();

                Log::info('Fetching new reviews for location', [
                    'shouldSync' => $shouldSync,
                    'reviewCount' => $location->reviews()->count(),
                ]);

                $this->fetchGoogleReviews($user->google_id, $location->name, $location->id);

                session()->flash('sync_status', 'Reviews synced successfully.');
            } elseif ($location->reviews()->count() === 0) {
                Log::info('No reviews found, fetching initial reviews.');
                $this->fetchGoogleReviews($user->google_id, $location->name, $location->id);
            }

            $query = $location->reviews();

            // Searching
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('reviewer_name', 'like', "%{$searchTerm}%")
                        ->orWhere('comment', 'like', "%{$searchTerm}%")
                        ->orWhere('reply_comment', 'like', "%{$searchTerm}%");
                });
            }

            // Sorting
            $sortField = $request->input('sort', 'reviewer_name');
            $allowedSortFields = ['reviewer_name', 'comment', 'reply_comment', 'star_rating'];
            $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'reviewer_name';

            $sortDirection = strtolower($request->input('direction', 'asc'));
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

            if ($sortField === 'star_rating') {
                $query->orderByRaw(
                    "FIELD(star_rating, 'FIVE', 'FOUR', 'THREE', 'TWO', 'ONE') {$sortDirection}"
                );
            } else {
                $query->orderBy($sortField, $sortDirection);
            }

            // Fetch Paginated Results
            $reviews = $query->paginate(10)->withQueryString();

            // Map Star Ratings to Numeric Values
            foreach ($reviews as $review) {
                $review->rating = match ($review->star_rating) {
                    'ONE' => 1,
                    'TWO' => 2,
                    'THREE' => 3,
                    'FOUR' => 4,
                    'FIVE' => 5,
                    default => null
                };
            }

            // Pagination
            $reviews = $query->paginate(10)->withQueryString();

            return view('businesses.reviews', [
                'reviews' => $reviews,
                'location' => $location,
                'searchTerm' => $request->input('search'),
                'currentSortField' => $sortField,
                'currentSortDirection' => $sortDirection,
            ]);

        } catch (Exception $e) {
            Log::error('Error during review sync for location ' . $location->id, [
                'error' => $e->getMessage(),
            ]);

            session()->flash('sync_status', 'An error occurred while syncing reviews.');

            $reviews = $location->reviews();

            return view('businesses.reviews', [
                'reviews' => $reviews->paginate(10)->withQueryString(),
                'location' => $location,
                'searchTerm' => $request->input('search'),
                'currentSortField' => $request->input('sort', 'reviewer_name'),
                'currentSortDirection' => strtolower($request->input('direction', 'asc')),
            ]);
        }
    }


    private function fetchGoogleReviews($googleId, $locationName, $locationId)
    {
        $userId = auth()->id();
        
        $accessToken = decrypt(auth()->user()->google_token);
        $accessToken = json_decode($accessToken, true);
        $baseUri = "https://mybusiness.googleapis.com/v4/{$googleId}/{$locationName}/reviews";

        $reviews = [];
        $nextPageToken = null;

        try {
            do {
                $uri = $baseUri;

                if ($nextPageToken) {
                    $uri .= '?pageToken=' . $nextPageToken;
                }

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken['access_token']}",
                    'Accept' => 'application/json',
                ])->get($uri);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['reviews'])) {
                        foreach ($data['reviews'] as $review) {

                            $createTime = Carbon::parse($review['createTime']);
                            $updateTime = isset($review['updateTime']) ? Carbon::parse($review['updateTime']) : null;
                            $replyUpdateTime = isset($review['reviewReply']['updateTime'])
                                ? Carbon::parse($review['reviewReply']['updateTime'])
                                : null;

                            try {

                                // Save and fetch profile image
                                $imageUrl = $review['reviewer']['profilePhotoUrl'] ?? null;

                                $imageName = md5($imageUrl) . '.jpg';

                                $savedImagePath = public_path('images/reviewers/' . $imageName);

                                if (!file_exists($savedImagePath)) {
                                    try {
                                        $imageContents = file_get_contents($imageUrl);
                                        file_put_contents($savedImagePath, $imageContents);
                                    } catch (Exception $e) {
                                        Log::error('Failed to save image: ' . $e->getMessage());
                                    }
                                }

                                Review::updateOrCreate([
                                    'review_id' => $review['reviewId']
                                ], [
                                    'user_id' => $userId,
                                    'review_id' => $review['reviewId'],
                                    'reviewer_name' => $review['reviewer']['displayName'],
                                    'profile_photo_url' => 'images/reviewers/' . $imageName,
                                    'star_rating' => $review['starRating'],
                                    'comment' => $review['comment'] ?? null,
                                    'create_time' => $createTime,
                                    'update_time' => $updateTime,
                                    'reply_comment' => $review['reviewReply']['comment'] ?? null,
                                    'reply_update_time' => $replyUpdateTime,
                                    'review_name' => $review['name'],
                                    'location_id' => $locationId,
                                ]);

                                Log::info('Review saved successfully', [
                                    'review_id' => $review['reviewId'],
                                    'star_rating' => $review['starRating'],
                                    'location_id' => $locationId,
                                ]);

                            } catch (Exception $e) {
                                Log::error('Failed to save review', [
                                    'review_id' => $review['reviewId'],
                                    'error' => $e->getMessage(),
                                    'data' => $review,
                                ]);
                            }
                        }

                        $reviews = array_merge($reviews, $data['reviews']);
                    }

                    $nextPageToken = $data['nextPageToken'] ?? null;
                } else {
                    Log::error('Error fetching Google reviews: ' . $response->body());
                    break;
                }
            } while ($nextPageToken);
        } catch (Exception $e) {
            Log::error('Exception fetching Google reviews: ' . $e->getMessage());
        }

        return $reviews;
    }

    public function replyToReview(Request $request, $id, $reviewId)
    {
        $request->validate([
            'reply' => 'required|string|max:255',
        ]);

        $location = Location::findOrFail($id);
        $review = Review::where('id', $reviewId)
            ->where('location_id', $id)
            ->firstOrFail();

        if (Auth::user() && Auth::id() === $location->user_id) {
            try {
                $this->postGoogleReviewReply(
                    Auth::user()->google_id,
                    $location->name,
                    $review->review_id,
                    $request->input('reply')
                );

                $this->updateReplyInDatabase($id, $reviewId, $request->input('reply'));

                return back()->with('success', 'Reply posted successfully!');
            } catch (Exception $e) {
                throw $e;
                Log::error('Error posting reply: ' . $e->getMessage());

                return back()->with('error', 'Failed to post reply. Please try again later.');
            }
        }

        return abort(403, 'Unauthorized action.');
    }

    private function postGoogleReviewReply($googleId, $locationName, $reviewId, $replyText)
    {
        $this->client = new GoogleAuthService(auth()->id());
        $token = $this->client->ensureToken();

        if (!$token || !$googleId) {
            return $this->success(__('Token is missing. Redirecting to authorization.'), response::HTTP_OK, [
                'error' => true,
                'auth_url' => $this->client->createAuthUrl(),
            ]);
        }

        $accessToken = decrypt(auth()->user()->google_token);
        $accessToken = json_decode($accessToken, true);
        $uri = "https://mybusiness.googleapis.com/v4/{$googleId}/{$locationName}/reviews/{$reviewId}/reply";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken['access_token']}",
                'Accept' => 'application/json',
            ])->put($uri, ['comment' => $replyText]);

            if (!$response->successful()) {
                throw new Exception('Failed to post reply: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Error posting reply: ' . $e->getMessage());
            throw $e;
        }
    }

    private function updateReplyInDatabase($locationId, $reviewId, $replyText)
    {
        $review = Review::where('id', $reviewId)
            ->where('location_id', $locationId)
            ->firstOrFail();

        $review->update([
            'reply_comment' => $replyText,
            'reply_update_time' => now(),
        ]);
    }

    public function deleteReply($id, $reviewId)
    {
        $location = Location::findOrFail($id);
        $review = Review::where('id', $reviewId)
            ->where('location_id', $id)
            ->firstOrFail();

        if (Auth::user() && Auth::id() === $location->user_id) {
            try {
                $this->deleteGoogleReviewReply(
                    Auth::user()->google_id,
                    $location->name,
                    $review->review_id
                );

                $review->update([
                    'reply_comment' => null,
                    'reply_update_time' => null,
                ]);

                return redirect()->back()->with('success', 'Reply deleted successfully.');
            } catch (Exception $e) {
                throw $e;
                Log::error('Error deleting reply: ' . $e->getMessage());

                return redirect()->back()->with('error', 'Failed to delete reply. Please try again later.');
            }
        }

        return abort(403, 'Unauthorized action.');
    }

    private function deleteGoogleReviewReply($googleId, $locationName, $reviewId)
    {
        $this->client = new GoogleAuthService(auth()->id());
        $token = $this->client->ensureToken();

        if (!$token || !$googleId) {
            return $this->success(__('Token is missing. Redirecting to authorization.'), response::HTTP_OK, [
                'error' => true,
                'auth_url' => $this->client->createAuthUrl(),
            ]);
        }

        $accessToken = decrypt(auth()->user()->google_token);
        $accessToken = json_decode($accessToken, true);
        $uri = "https://mybusiness.googleapis.com/v4/{$googleId}/{$locationName}/reviews/{$reviewId}/reply";


        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken['access_token']}",
                'Accept' => 'application/json',
            ])->delete($uri);

            if (!$response->successful()) {
                throw new Exception('Failed to delete reply: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Error deleting reply: ' . $e->getMessage());
            throw $e;
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'reviewer_name' => 'required|string|max:255',
                'comment' => 'nullable|string',
                'star_rating' => 'required|in:ONE,TWO,THREE',
                'location_id' => 'required|exists:locations,id'
            ]);

            $location = Location::findOrFail($request->location_id);

            // Check if user is authorized to review this location
            // if (Auth::user() && Auth::id() === $location->user_id) {
            //     return response()->json([
            //         'error' => 'Cannot review your own location'
            //     ], 403);
            // }

            // Generate a unique review ID
            $reviewId = 'local_' . Str::uuid();

            // Create the review in the new local_reviews table
            $review = LocalReview::create([
                'review_id' => $reviewId,
                'reviewer_name' => $request->reviewer_name,
                'star_rating' => $request->star_rating,
                'comment' => $request->comment,
                'create_time' => Carbon::now(),
                'location_id' => $request->location_id
            ]);

            // Log successful review creation
            Log::info('Local review created successfully', [
                'review_id' => $reviewId,
                'location_id' => $request->location_id,
                'star_rating' => $request->star_rating
            ]);

            return response()->json([
                'message' => 'Review submitted successfully',
                'review' => $review
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Review validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating review', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create review'
            ], 500);
        }
    }
}