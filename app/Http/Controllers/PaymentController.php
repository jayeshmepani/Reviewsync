<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Carbon\Carbon;

class PaymentController
{
    public function checkout(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'plan' => 'required|exists:subscriptions,name',
            ]);

            $user = Auth::user();
            $subscription = Subscription::where('name', $request->plan)->first();

            // Prevent upgrading to the same plan
            if (strtolower($request->plan) === $user->subscription) {
                return response()->json([
                    'error' => 'You are already on this plan.'
                ], 400);
            }

            // Check if plan is free
            if ($subscription->price <= 0) {
                $user->subscription = strtolower($request->plan);
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription updated successfully'
                ]);
            }

            // Set payment pending flag
            $user->payment_pending = true;
            $user->pending_subscription = strtolower($request->plan);
            $user->save();

            // Set Stripe API key
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $subscription->name,
                                'description' => $subscription->description,
                            ],
                            'unit_amount' => (int) ($subscription->price * 100), // Convert to cents
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('payment.success', [
                    'plan' => $subscription->name,
                    'user' => $user->id
                ]),
                'cancel_url' => route('payment.cancel'),
                'customer_email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => strtolower($request->plan)
                ]
            ]);

            return response()->json([
                'success' => true,
                'url' => $session->url
            ]);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Checkout error: ' . $e->getMessage());

            // Reset payment pending state
            if (isset($user)) {
                $user->payment_pending = false;
                $user->pending_subscription = null;
                $user->save();
            }

            return response()->json([
                'error' => 'Unable to process payment. Please try again.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function success(Request $request)
    {
        try {
            // Validate request parameters
            if (!$request->has('plan') || !$request->has('user')) {
                Log::warning('Invalid payment success parameters');
                return redirect()->route('home')->with('error', 'Invalid payment session.');
            }

            $user = Auth::user();
            $plan = strtolower($request->plan);

            // Verify payment is pending and matches requested plan
            if (!$user->payment_pending || $user->pending_subscription !== $plan) {
                Log::warning('Unauthorized subscription update attempt', [
                    'user_id' => $user->id,
                    'current_subscription' => $user->subscription,
                    'pending_subscription' => $user->pending_subscription,
                    'requested_plan' => $plan
                ]);

                return redirect()->route('dashboard')->with('error', 'Unauthorized subscription update.');
            }

            // Update subscription based on plan
            if ($plan == 'standard') {
                $user->subscription = 'standard';
                $user->subscription_billing_start = Carbon::now();
                $user->subscription_billing_end = Carbon::now()->addYear(); // 1-year validity
            } elseif ($plan == 'premium') {
                $user->subscription = 'premium';
                $user->subscription_billing_start = Carbon::now();
                $user->subscription_billing_end = null; // Lifetime plan (unlimited)
            }

            // Clear pending flags
            $user->payment_pending = false;
            $user->pending_subscription = null;
            $user->save();

            // Log successful subscription update
            Log::info('Subscription updated successfully', [
                'user_id' => $user->id,
                'new_plan' => $user->subscription
            ]);

            return view('payment.success', [
                'plan' => ucfirst($plan)
            ]);

        } catch (\Exception $e) {
            // Log error
            Log::error('Subscription update error: ' . $e->getMessage());

            return redirect()->route('dashboard')->with('error', 'Error updating subscription. Please contact support.');
        }
    }

    public function cancel()
    {
        $user = Auth::user();
        
        // Reset pending subscription if user explicitly cancels
        $user->payment_pending = false;
        $user->pending_subscription = null;
        $user->save();

        // Log cancellation
        Log::info('Payment cancelled', [
            'user_id' => $user->id,
            'pending_subscription' => $user->pending_subscription
        ]);

        return view('payment.cancel');
    }
}