<?php

namespace App\Http\Controllers;

use App\Actions\SyncLocations;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class BusinessController
{
    public function destroy($id)
    {
        $business = Location::findOrFail($id);

        if ($business->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $business->delete();

        return redirect()->route('businesses')->with('success', 'Business deleted successfully!');
    }

    public function showDashboard()
    {
        $user = Auth::user();
        $isSync = false;

        if ($user->google_id && $user->google_token) {
            $isSync = true;
        }

        $businesses = Auth::user()->locations;

        // Get the subscription plan and corresponding limit
        $subscriptionPlan = $user->subscription; // e.g., 'trial', 'standard', 'premium'
        $subscriptionLimits = config('business.subscription_limits');

        // Fetch the limit for the user's subscription plan
        $businessLimit = $subscriptionLimits[$subscriptionPlan] ?? 0;

        // AI Reply Limits based on Subscription
        $aiReplyLimits = [
            'trial' => 0,
            'standard' => 700,
            'premium' => -1, // Unlimited
        ];

        $aiReplyLimit = $aiReplyLimits[$subscriptionPlan] ?? 0;

        // Calculate total AI replies used by the user
        $totalAiReplies = DB::table('ai_replies')
            ->where('user_id', $user->id)
            ->count();

        // Count total businesses for authenticated user
        $totalBusinesses = $businesses->count();

        // Count total reviews for authenticated user's businesses
        $totalReviews = $businesses->sum(function ($business) {
            return $business->reviews()->count();
        });

        // Add total AI replies for the authenticated user
        $totalAiReplies = DB::table('ai_replies')
            ->where('user_id', Auth::id()) // Filter by authenticated user
            ->count();

        Log::info('Total AI replies for user: ' . Auth::id() . ' - ' . $totalAiReplies);

        return view('dashboard', compact(
            'isSync',
            'businesses',
            'totalBusinesses',
            'totalReviews',
            'totalAiReplies',
            'businessLimit',
            'aiReplyLimit'
        ));
    }

    public function showSyncOptions()
    {
        $user = Auth::user();
        $syncLocations = new SyncLocations();
        $availableLocations = $syncLocations->fetchLocations($user->id);

        if (!$availableLocations) {
            return redirect()->route('dashboard')->with('error', 'Failed to fetch locations!');
        }

        $trialLimit = config('business.subscription_limits.trial');

        if ($user->subscription === 'trial') {
            // Count existing synced locations
            $existingCount = Location::where('user_id', $user->id)->count();

            if ($existingCount >= $trialLimit) {
                return redirect()->route('dashboard')
                    ->with('error', "Trial users can only sync up to {$trialLimit} business(es). Please upgrade for more.");
            }

            // Calculate remaining slots
            $remainingSlots = $trialLimit - $existingCount;
        }

        return view('businesses.sync-options', [
            'locations' => $availableLocations,
            'isTrialUser' => $user->subscription === 'trial',
            'trialLimit' => $trialLimit,
            'remainingSlots' => $remainingSlots ?? null,
        ]);
    }

    public function sync(Request $request)
    {
        $user = Auth::user();
        $selectedLocations = $request->input('selected_locations', []);

        if (empty($selectedLocations)) {
            return redirect()->route('dashboard')->with('error', 'Please select at least one business!');
        }

        if ($user->subscription === 'trial') {
            $trialLimit = config('business.subscription_limits.trial');
            $existingCount = Location::where('user_id', $user->id)->count();
            $selectionCount = count($selectedLocations);

            if (($existingCount + $selectionCount) > $trialLimit) {
                return redirect()->route('dashboard')
                    ->with('error', "Trial users can only sync up to {$trialLimit} business(es). Please upgrade for more.");
            }
        }

        $success = (new SyncLocations)->executeSelected($user->id, $selectedLocations);

        if (!$success) {
            return redirect()->route('dashboard')->with('error', 'Failed to sync locations!');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Business(es) synced successfully!');
    }
}
