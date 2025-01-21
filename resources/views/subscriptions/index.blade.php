@extends('layouts.app')

@section('content')
<div class="container mx-auto my-10">
    <h1 class="text-4xl font-bold text-center mb-8 text-hsl(125, 37%, 23)">Subscription Plans</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach ($subscriptions as $subscription)
        <div class="card bg-white shadow-lg rounded-lg p-6 border border-hsl(125, 37%, 23)">
            <h2 class="text-2xl font-bold text-center text-hsl(125, 37%, 23) mb-4">{{ $subscription->name }}</h2>
            <p class="text-gray-700 text-center">{{ $subscription->description }}</p>
            <p class="text-center font-semibold text-lg mt-4">
                Price: {{ $subscription->price == 0 ? 'Free' : '$' . number_format($subscription->price, 2) }}
            </p>
            @if($subscription->billing_cycle)
            <p class="text-center">Billing Cycle: {{ $subscription->billing_cycle }}</p>
            @endif
            <p class="text-center">Business Limit: 
                @if($subscription->business_limit == -1)
                    Unlimited
                @else
                    {{ $subscription->business_limit }}
                @endif
            </p>
            <p class="text-center">AI Replies: {{ $subscription->ai_generated_replies ? 'Yes' : 'No' }}</p>
            <div class="mt-6 text-center">
                <button class="btn-primary px-6 py-2 rounded-lg bg-hsl(125, 37%, 23) text-white hover:bg-hsl(125, 47%, 30)">Choose Plan</button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
