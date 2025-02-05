<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController
{
    public function index()
    {
        // Fetch predefined subscriptions
        $subscriptions = Subscription::all();
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function choosePlan(Request $request)
    {

        Log::info('Received plan: ' . $request->input('plan'));
    
        $request->validate([
            'plan' => 'required|in:trial,standard,premium',
        ]);
    
        $user = Auth::user();
        $user->subscription = $request->input('plan');
        $user->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully.',
        ]);
    }

    public function getPlanDetails(Request $request)
    {
        $plan = $request->input('plan');
        $subscription = Subscription::where('name', $plan)->first();

        return response()->json([
            'name' => $subscription->name,
            'price' => $subscription->price
        ]);
    }
    
}