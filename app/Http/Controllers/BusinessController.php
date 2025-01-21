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

        return redirect()->route('businesses.info')->with('success', 'Business deleted successfully!');
    }

    public function showDashboard()
    {
        $user = Auth::user();
        $isSync = false;

        if ($user->google_id && $user->google_token) {
            $isSync = true;
        }

        $businesses = Auth::user()->locations;

        // Modified query to include user_id filter
        $tokenData = DB::table('ai_replies')
            ->where('user_id', Auth::id()) // Only get data for authenticated user
            ->selectRaw('
                DATE_FORMAT(DATE_SUB(created_at, INTERVAL SECOND(created_at) % 3 SECOND), "%Y-%m-%d %H:%i:00") as grouped_time,
                input_tokens,
                output_tokens
            ')
            ->groupBy('grouped_time', 'input_tokens', 'output_tokens')
            ->orderBy('grouped_time')
            ->get()
            ->map(function ($item) {
                $inputCost = $item->input_tokens * 0.0375 / 1000000;
                $outputCost = $item->output_tokens * 0.15 / 1000000;
                $item->total_cost = $inputCost + $outputCost;

                $item->grouped_time = \Carbon\Carbon::parse($item->grouped_time)
                    ->format('d-M-Y h:i:s A');

                Log::info('Cost Calculation:', [
                    'user_id' => Auth::id(), // Add user_id to logging
                    'grouped_time' => $item->grouped_time,
                    'input_tokens' => $item->input_tokens,
                    'output_tokens' => $item->output_tokens,
                    'input_cost' => number_format($inputCost, 9),
                    'output_cost' => number_format($outputCost, 9),
                    'total_cost' => number_format($item->total_cost, 9),
                ]);

                return $item;
            });

        $totalCost = $tokenData->sum('total_cost');

        Log::info('Total cost for user: ' . Auth::id() . ' - ' . $totalCost);

        // Count total businesses for authenticated user
        $totalBusinesses = $businesses->count();

        // Count total reviews for authenticated user's businesses
        $totalReviews = $businesses->sum(function ($business) {
            return $business->reviews()->count();
        });

        return view('dashboard', compact('isSync', 'businesses', 'tokenData', 'totalCost', 'totalBusinesses', 'totalReviews'));
    }

    public function showSyncOptions()
    {
        $syncLocations = new SyncLocations();
        $availableLocations = $syncLocations->fetchLocations(Auth::id());
        
        if (!$availableLocations) {
            return redirect()->route('dashboard')->with('error', 'Failed to fetch locations!');
        }

        return view('businesses.sync-options', [
            'locations' => $availableLocations
        ]);
    }

    public function sync(Request $request)
    {
        $selectedLocations = $request->input('selected_locations', []);
        
        if (empty($selectedLocations)) {
            return redirect()->route('dashboard')->with('error', 'Please select at least one business!');
        }

        $success = (new SyncLocations)->executeSelected(Auth::id(), $selectedLocations);
        
        if (!$success) {
            return redirect()->route('dashboard')->with('error', 'Failed to sync locations!');
        }

        return redirect()->route('dashboard')->with('success', 'Selected locations synced successfully!');
    }
}
