@extends('layouts.app')

@section('content')
<div class="container mx-auto my-10">
    <h1 class="text-4xl font-bold text-center mb-8">Subscription Plans</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 h-[450px]">
        @foreach ($subscriptions as $subscription)
            <div class="subscription-card card bg-white shadow-lg rounded-lg p-6 border relative"
                data-plan="{{ $subscription->name }}">
                @if(Auth::user()->subscription === strtolower($subscription->name))
                    <div class="absolute top-4 right-4">
                        <span class="bg-green-500 text-white px-3 py-2 rounded-100 text-sm">
                            Current Plan
                        </span>
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-center mb-4">
                    {{ $subscription->name }}
                </h2>
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
                <p class="text-center">AI Replies:
                    @if($subscription->ai_generated_replies == -1)
                        Unlimited
                    @else
                        {{ $subscription->ai_generated_replies }}
                    @endif
                </p>
                <div class="mt-6 text-center">
                    <button class="plan_btn" data-plan="{{ $subscription->name }}"
                        @if(Auth::user()->subscription === strtolower($subscription->name)) disabled @endif
                        onclick="handlePlanSelection('{{ $subscription->name }}')">
                        {{ Auth::user()->subscription === strtolower($subscription->name) ? 'Current Plan' : 'Choose Plan' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
    <style>
        .subscription-card {
            display: grid;
            grid-template-rows: subgrid;
            grid-row: span 6;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }

        .subscription-card.active-plan {
            border-color: #22c55e !important;
            transform: scale(1.02);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .mb-8 {
            margin-bottom: 2rem !important;
        }

        .mt-6 {
            margin-top: 2.5rem !important;
        }

        h2 {
            margin-top: 1.5rem !important;
        }

        button.plan_btn {
            background: hsl(215deg 37% 23%) !important;
            padding: 0.5rem 1rem;
            color: white;
            transition: all 0.3s ease;
            border-radius: 0.375rem;
        }

        button.plan_btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        button.current-plan-btn {
            background: #22c55e !important;
        }

        .absolute {
            position: absolute;
        }

        .top-4 {
            top: 1rem;
        }

        .right-4 {
            right: 1rem;
        }
    </style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const planButtons = document.querySelectorAll('.plan_btn');

        // Prevent back button navigation during payment process
        window.history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function () {
            window.location.href = '/payment/cancel'; // Redirect to cancel page
        });

        function updateUIForPlan(selectedPlan) {
            document.querySelectorAll('.subscription-card').forEach(card => {
                const cardPlan = card.dataset.plan.toLowerCase();
                const button = card.querySelector('.plan_btn');

                if (cardPlan === selectedPlan.toLowerCase()) {
                    card.classList.add('active-plan');
                    button.textContent = 'Current Plan';
                    button.disabled = true;
                    button.classList.add('current-plan-btn');
                } else {
                    card.classList.remove('active-plan');
                    button.textContent = 'Choose Plan';
                    button.disabled = false;
                    button.classList.remove('current-plan-btn');
                }
            });
        }

        planButtons.forEach(button => {
            button.addEventListener('click', async function () {
                try {
                    const plan = this.dataset.plan.toLowerCase();
                    const currentPlan = '{{ Auth::user()->subscription }}';

                    // If selecting the same plan, do nothing
                    if (plan === currentPlan.toLowerCase()) {
                        return;
                    }

                    // Fetch plan details to check if it's a paid plan
                    const planResponse = await fetch('/get-plan-details', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ plan: plan })
                    });

                    const planData = await planResponse.json();

                    // If plan requires payment, initiate checkout
                    if (planData.price > 0) {
                        handlePlanSelection(plan);
                        return;
                    }

                    // For free plans, proceed with direct update
                    const response = await fetch('/choose-plan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ plan: plan })
                    });

                    const data = await response.json();

                    if (data.success) {
                        updateUIForPlan(plan);
                        location.reload();
                        alert('Subscription updated successfully!');
                    } else {
                        throw new Error(data.message || 'Failed to update subscription');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to update subscription. Please try again.');
                }
            });
        });

        const currentPlan = '{{ Auth::user()->subscription }}';
        if (currentPlan) {
            updateUIForPlan(currentPlan);
        }
    });

    async function handlePlanSelection(plan) {
        try {
            const response = await fetch('/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ plan: plan })
            });

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            if (data.url) {
                window.location.href = data.url;
            } else if (data.success) {
                fetch('/api/check-payment-status')
                    .then(response => response.json())
                    .then(data => {
                        if (data.payment_pending) {
                            alert('Please complete the payment to activate the plan.');
                        } else {
                            location.reload();
                            alert('Subscription updated successfully!');
                        }
                    });
            } else {
                alert('Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to initiate payment. Please try again.');
        }
    }
</script>
@endpush
@endsection