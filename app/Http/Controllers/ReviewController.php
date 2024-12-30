<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReviewController
{
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

            $reviews = $location->reviews;

            return view('businesses.reviews', compact('reviews', 'location'));

        } catch (\Exception $e) {
            Log::error('Error during review sync for location ' . $location->id, [
                'error' => $e->getMessage(),
            ]);

            session()->flash('sync_status', 'An error occurred while syncing reviews.');

            $reviews = $location->reviews;

            return view('businesses.reviews', compact('reviews', 'location'));
        }
    }

    private function fetchGoogleReviews($googleId, $locationName, $locationId)
    {
        $accessToken = auth()->user()->google_token;
        $baseUri = "https://mybusiness.googleapis.com/v4/accounts/{$googleId}/{$locationName}/reviews";

        $reviews = [];
        $nextPageToken = null;

        try {
            do {
                $uri = $baseUri;

                if ($nextPageToken) {
                    $uri .= '?pageToken=' . $nextPageToken;
                }

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept' => 'application/json',
                ])->get($uri);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['reviews'])) {
                        foreach ($data['reviews'] as $review) {
                            $starRating = match ((int) $review['starRating']) {
                                1 => 'ONE',
                                2 => 'TWO',
                                3 => 'THREE',
                                4 => 'FOUR',
                                5 => 'FIVE',
                                default => 'THREE',
                            };

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

                                Review::create([
                                    'review_id' => $review['reviewId'],
                                    'reviewer_name' => $review['reviewer']['displayName'],
                                    'profile_photo_url' => 'images/reviewers/' . $imageName,
                                    'star_rating' => $starRating,
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
                                    'location_id' => $locationId,
                                ]);
                            } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                Log::error('Error posting reply: ' . $e->getMessage());
                return back()->with('error', 'Failed to post reply. Please try again later.');
            }
        }

        return abort(403, 'Unauthorized action.');
    }

    private function postGoogleReviewReply($googleId, $locationName, $reviewId, $replyText)
    {
        $accessToken = auth()->user()->google_token;
        $uri = "https://mybusiness.googleapis.com/v4/accounts/{$googleId}/{$locationName}/reviews/{$reviewId}/reply";

        try {
            $response = Http::withToken($accessToken)
                ->put($uri, ['comment' => $replyText]);

            if (!$response->successful()) {
                throw new \Exception('Failed to post reply: ' . $response->body());
            }
        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                Log::error('Error deleting reply: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to delete reply. Please try again later.');
            }
        }

        return abort(403, 'Unauthorized action.');
    }

    private function deleteGoogleReviewReply($googleId, $locationName, $reviewId)
    {
        $accessToken = auth()->user()->google_token;
        $uri = "https://mybusiness.googleapis.com/v4/accounts/{$googleId}/{$locationName}/reviews/{$reviewId}/reply";

        try {
            $response = Http::withToken($accessToken)->delete($uri);

            if (!$response->successful()) {
                throw new \Exception('Failed to delete reply: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Error deleting reply: ' . $e->getMessage());
            throw $e;
        }
    }
}
