@extends('layouts.superadmin')

@section('title', 'User Data')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <!-- User Info Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-semibold">User Data: {{ $user->name }}</h2>
        <p class="text-gray-600">{{ $user->email }}</p>
        <div class="mt-2 text-sm">
            <p>Member since: {{ $user->created_at->format('M d, Y') }}</p>
            <p>Total Locations: {{ $user->locations->count() }}</p>
            <p>Total Reviews: {{ $user->locations->sum(function ($location) {
    return $location->reviews->count(); }) }}
            </p>
        </div>
    </div>

    <!-- Locations Section -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Locations</h3>
            <a href="{{ route('superadmin.users') }}" class="text-blue-600 hover:text-blue-800">Back to Users</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($user->locations as $location)
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h4 class="font-semibold">{{ $location->title }}</h4>
                    <p class="text-gray-600">{{ $location->address }}</p>
                    <div class="mt-2 space-y-1 text-sm text-gray-500">
                        <p>Reviews: {{ $location->reviews->count() }}</p>
                        <p>Created: {{ $location->created_at->format('M d, Y') }}</p>
                        @if($location->google_id)
                            <p class="text-green-600">Google Connected</p>
                        @endif
                    </div>
                    <form action="{{ route('superadmin.users.data.delete', $location->id) }}" method="POST" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                            Delete Location
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-3">
                    <p class="text-gray-500 text-center">No locations found for this user.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Reviews & AI Replies Section -->
    <div>
        <h3 class="text-xl font-semibold mb-4">Recent Reviews & AI Replies</h3>
        <div class="space-y-4">
            @forelse($user->locations as $location)
                        @foreach($location->reviews as $review)
                                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="mb-2">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold">Review for {{ $location->title }}</p>
                                                    <p class="text-sm text-gray-500">Posted: {{ $review->created_at->format('M d, Y H:i') }}</p>
                                                    <p class="pt-2"><em class="font-italic">Review: {{$review->comment}}</em></p>
                                                    <p class="p-2 rounded-lg bg-gray-100"><em class="font-italic">Reply:
                                                            {{ $review->reply_comment ?? 'No reply yet' }}</em></p>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-sm font-medium text-gray-600">Rating:</span>
                                                    <span class="ml-1 text-yellow-500">
                                                        @php
                                                            $ratingMap = [
                                                                'ONE' => 1,
                                                                'TWO' => 2,
                                                                'THREE' => 3,
                                                                'FOUR' => 4,
                                                                'FIVE' => 5,
                                                            ];

                                                            $numericRating = $ratingMap[$review->star_rating] ?? 0;
                                                        @endphp

                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $numericRating)
                                                                ★
                                                            @else
                                                                ☆
                                                            @endif
                                                        @endfor
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <p class="text-gray-600">{{ $review->content }}</p>
                                            </div>

                                            <!-- AI Replies -->
                                            @if($review->aiReplies->isNotEmpty())
                                                        <div class="mt-4 pl-4 border-l-2 border-blue-200">
                                                            @php
                                                                $uniqueReplies = $review->aiReplies
                                                                    ->groupBy(function ($reply) {
                                                                        // Group by exact timestamp
                                                                        return $reply->created_at->format('Y-m-d H:i:s');
                                                                    })
                                                                    ->map(function ($group) {
                                                                        // Take only the first entry from each group
                                                                        return $group->first();
                                                                    });
                                                            @endphp

                                                            @foreach($uniqueReplies as $aiReply)
                                                                        <div class="mb-2">
                                                                            <p class="text-sm font-medium text-blue-600">AI Generated Reply</p>
                                                                            <p class="text-gray-600">{{ $aiReply->content }}</p>
                                                                            <div class="mt-1 text-xs text-gray-500">
                                                                                <span>Generated: {{ $aiReply->created_at->format('d-M-Y h:i:s A') }}</span>
                                                                                @php
                                                                                    $inputCost = $aiReply->input_tokens * 0.0375 / 1000000;
                                                                                    $outputCost = $aiReply->output_tokens * 0.15 / 1000000;
                                                                                    $totalCost = $inputCost + $outputCost;
                                                                                @endphp
                                                                                <span class="ml-2">Total Tokens:
                                                                                    {{ $aiReply->input_tokens + $aiReply->output_tokens }}</span>
                                                                                <span class="ml-2">Cost: ${{ number_format($totalCost, 6) }}</span>
                                                                                <div class="mt-1">
                                                                                    <span class="text-xs text-gray-400">Input Tokens:
                                                                                        {{ $aiReply->input_tokens }}</span>
                                                                                    <span class="ml-2 text-xs text-gray-400">Output Tokens:
                                                                                        {{ $aiReply->output_tokens }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                            @endforeach
                                                        </div>
                                            @else
                                                <p class="mt-2 text-sm text-gray-500">No AI replies generated yet.</p>
                                            @endif
                                        </div>
                                    </div>
                        @endforeach
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">No reviews found for this user's locations.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination if needed -->
    @if(isset($reviews) && $reviews->hasPages())
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                if (!confirm('Are you sure you want to delete this location?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush

@endsection