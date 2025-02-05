@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-8">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Payment Cancelled</h2>
                <p class="text-gray-600">Your payment was cancelled and you haven't been charged.</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="font-semibold text-gray-700 mb-2">Need Help?</h3>
                <p class="text-gray-600 mb-4">If you're experiencing any issues or have questions about our subscription plans, please don't hesitate to contact our support team.</p>
                
                <div class="space-y-3">
                    <a href="{{ route('subscriptions.index') }}" class="block text-center w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View Pricing Plans
                    </a>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection