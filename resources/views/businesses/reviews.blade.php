@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Flash Message -->
    @if (session('sync_status'))
        <div class="alert alert-success">
            {{ session('sync_status') }}
        </div>
    @endif

    <form method="GET" action="{{ route('businesses.reviews', $location->id) }}">
        <input type="hidden" name="sync" value="1">
        <button type="submit" class="btn btn-primary">Sync Reviews</button>
    </form>

    @if($reviews && count($reviews) > 0)
        <!-- For Larger Screens, Display Table View -->
        <div class="reviews-table table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Owner's Reply</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                                <tr>
                                    <td>
                                        <div class="author">
                                            <div class="profile-avatar">
                                                @if(isset($review->profile_photo_url))
                                                    <img src="{{ asset($review->profile_photo_url) }}" alt="{{ $review->reviewer_name }}"
                                                        width="50" height="50">
                                                @endif
                                            </div>
                                            <strong>{{ $review->reviewer_name }}</strong>
                                            <small>
                                                (Created: {{ \Carbon\Carbon::parse($review->create_time)->toDayDateTimeString() }}
                                                @if($review->update_time)
                                                    , Updated: {{ \Carbon\Carbon::parse($review->update_time)->toDayDateTimeString() }}
                                                @endif)
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $ratingMap = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5];
                                            $stars = $ratingMap[$review->star_rating] ?? 0;
                                        @endphp
                                        {!! str_repeat('★', $stars) !!}
                                        {!! str_repeat('☆', 5 - $stars) !!}
                                    </td>
                                    <td title="{{ $review->comment ?? 'No review text available' }}">
                                        {{ $review->comment ?? 'No review text available' }}
                                    </td>
                                    <td>{{ $review->reply_comment ?? 'No reply yet' }}</td>
                                    <td>
                                        <form method="POST"
                                            action="{{ route('reviews.reply', ['id' => $location->id, 'reviewId' => $review->id]) }}">
                                            @csrf
                                            @method('POST')
                                            <textarea name="reply" class="form-control"
                                                placeholder="Write your reply...">{{ $review->reply_comment ?? '' }}</textarea>
                                            <div class="mt-2">
                                                <button type="submit" class="btn btn-primary">
                                                    {{ $review->reply_comment ? 'Update Reply' : 'Submit Reply' }}
                                                </button>
                                                @if($review->reply_comment)
                                                    <form method="POST"
                                                        action="{{ route('reviews.reply.delete', ['id' => $location->id, 'reviewId' => $review->id]) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete Reply</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- For Smaller Screens, Display Reviews as Individual Blocks -->
        <div class="reviews-list">
            @foreach($reviews as $review)
                <div class="review">
                    <div class="review-header">
                        <div class="author">
                            <div class="profile-avatar">
                                @if(isset($review->profile_photo_url))
                                    <img src="{{ asset($review->profile_photo_url) }}" alt="{{ $review->reviewer_name }}" width="50"
                                        height="50">
                                @endif
                            </div>
                            <strong>{{ $review->reviewer_name }}</strong>
                            <small>
                                (Created: {{ \Carbon\Carbon::parse($review->create_time)->toDayDateTimeString() }}
                                @if($review->update_time)
                                    , Updated: {{ \Carbon\Carbon::parse($review->update_time)->toDayDateTimeString() }}
                                @endif)
                            </small>
                        </div>
                        <div class="rating">
                            <strong>Rating:</strong>
                            @php
                                $ratingMap = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5];
                                $stars = $ratingMap[$review->star_rating] ?? 0;
                            @endphp
                            {!! str_repeat('★', $stars) !!}
                            {!! str_repeat('☆', 5 - $stars) !!}
                        </div>
                    </div>

                    <div class="review-text">
                        <p>{{ $review->comment ?? 'No review text available' }}</p>
                    </div>

                    <div class="review-reply">
                        @if($review->reply_comment)
                            <strong>Owner's Reply:</strong>
                            <p>{{ $review->reply_comment }}</p>
                        @else
                            <strong>Owner's Reply:</strong>
                            <p>No reply yet.</p>
                        @endif
                    </div>

                    <form method="POST"
                        action="{{ route('reviews.reply', ['id' => $location->id, 'reviewId' => $review->id]) }}">
                        @csrf
                        @method('POST')
                        <textarea name="reply" class="form-control"
                            placeholder="Write your reply...">{{ $review->reply_comment ?? '' }}</textarea>
                        <div class="my-4">
                            <button type="submit" class="btn btn-primary">
                                {{ $review->reply_comment ? 'Update Reply' : 'Submit Reply' }}
                            </button>
                            @if($review->reply_comment)
                                <form method="POST"
                                    action="{{ route('reviews.reply.delete', ['id' => $location->id, 'reviewId' => $review->id]) }}"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete Reply</button>
                                </form>
                            @endif
                        </div>
                    </form>
                    <hr>
                </div>
            @endforeach
        </div>
    @else
        <p>No reviews found for this business.</p>
    @endif
</div>
@endsection

@push('styles')
    <style>
        @media (width > 1024px) {
            .reviews-list {
                display: none;
            }

            .author {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .table {
                width: 100%;
                display: table;
                border-collapse: collapse;
                margin: 1rem 0;
                transition: all 0.3s ease-in-out;

                .profile-avatar>* {
                    max-width: 100%;
                    height: -webkit-fill-available;
                    border-radius: 0.5rem !important;
                }

                tbody {
                    font-size: 1rem;
                }

                th,
                td {
                    border: 1px solid;
                    padding: 8px;
                    text-align: left;
                }

                tr {
                    border: 0 !important;

                    &:nth-child(1) {
                        border-bottom: 1px solid hsl(0deg 0% 0%) !important;

                        body.theme-dark & {
                            border-bottom: 1px solid hsl(0deg 0% 100%) !important;
                        }
                    }

                    &:nth-child(1) {
                        &~tr {
                            border-bottom: 1px solid hsl(0deg 0% 0%) !important;

                            body.theme-dark & {
                                border-bottom: 1px solid hsl(0deg 0% 100%) !important;
                            }

                            td {
                                &:nth-child(3) {
                                    border-inline: 0 !important;
                                    border-bottom: 0 !important;
                                }
                            }
                        }
                    }

                    td {
                        &:nth-child(3) {
                            display: -webkit-box;
                            -webkit-line-clamp: 10;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            border: 0 !important;
                            border-radius: 0 !important;
                            height: calc(10* 1rem* 1.59);
                            font-size: 1rem;
                        }

                        &:last-child>* {
                            margin: 1rem !important;
                        }

                        &:nth-child(5) {
                            width: 37%;
                            text-align: center;
                        }
                    }
                }

                th {
                    background-color: hsl(from var(--bg-dark) h 51% 15%);
                    font-weight: bold;
                    border-radius: 0 !important;

                    body.theme-light & {
                        background-color: hsl(from var(--bg-light) h 51% 85%);
                    }
                }
            }

        }

        @media (width <=1024px) {
            .reviews-list {
                display: grid;
                gap: 1.1rem;

                .profile-avatar>* {
                    max-width: 100%;
                    height: -webkit-fill-available;
                    border-radius: 0.5rem !important;
                    left: 0.75rem;
                    position: relative;
                }
            }

            .author {
                display: flex;
                gap: 1rem;
                margin-bottom: 0.5rem;

                >* {
                    display: flex;
                    flex: 1 1 0;
                }
            }

            .table {
                display: none !important;
            }
        }
    </style>
@endpush