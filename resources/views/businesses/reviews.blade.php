@extends('layouts.app')

@section('content')
<div class="breadcrumb">
    <a href="{{ url('/businesses') }}">Businesses</a> /
    <a href="{{ url('/businesses/' . $location->id . '/reviews') }}">Reviews</a>
</div>
<div class="card-content">
    <h4>Customer Reviews</h4>
    <p>View and respond to reviews for your business.</p>
</div>
<div class="container">
    @if (session('sync_status'))
        <div class="alert alert-success">
            {{ session('sync_status') }}
        </div>
    @endif

    <form method="GET" action="{{ route('businesses.reviews', $location->id) }}" class="sync-form"
        style="margin-bottom:1rem;">
        <input type="hidden" name="sync" value="1">
        <button type="submit" class="btn btn-primary"><i class="material-icons">sync</i>Sync Reviews</button>
    </form>

    @if($reviews && count($reviews) > 0)
        <div class="reviews-table table">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>
                            <a
                                href="{{ route('businesses.reviews', ['id' => $location->id, 'sort' => 'reviewer_name', 'direction' => $currentSortField === 'reviewer_name' && $currentSortDirection === 'asc' ? 'desc' : 'asc', 'search' => $searchTerm]) }}">
                                Reviewer
                                @if($currentSortField === 'reviewer_name')
                                    @if($currentSortDirection === 'asc')
                                        <i class="fa-solid fa-arrow-down-a-z"></i>
                                    @else
                                        <i class="fa-solid fa-arrow-up-z-a"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('businesses.reviews', ['id' => $location->id, 'sort' => 'star_rating', 'direction' => $currentSortField === 'star_rating' && $currentSortDirection === 'asc' ? 'desc' : 'asc', 'search' => $searchTerm]) }}">
                                Rating
                                @if($currentSortField === 'star_rating')
                                    @if($currentSortDirection === 'asc')
                                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                                    @else
                                        <i class="fa-solid fa-arrow-up-short-wide"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('businesses.reviews', ['id' => $location->id, 'sort' => 'comment', 'direction' => $currentSortField === 'comment' && $currentSortDirection === 'asc' ? 'desc' : 'asc', 'search' => $searchTerm]) }}">
                                Review
                                @if($currentSortField === 'comment')
                                    @if($currentSortDirection === 'asc')
                                        <i class="fa-solid fa-arrow-down"></i>
                                    @else
                                        <i class="fa-solid fa-arrow-up"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('businesses.reviews', ['id' => $location->id, 'sort' => 'reply_comment', 'direction' => $currentSortField === 'reply_comment' && $currentSortDirection === 'asc' ? 'desc' : 'asc', 'search' => $searchTerm]) }}">
                                Owner's Reply
                                @if($currentSortField === 'reply_comment')
                                    @if($currentSortDirection === 'asc')
                                        <i class="fa-solid fa-arrow-down"></i>
                                    @else
                                        <i class="fa-solid fa-arrow-up"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
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
                                    <td id="rating">
                                        @php
                                            $ratingMap = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5];
                                            $stars = $ratingMap[$review->star_rating] ?? 0;
                                        @endphp
                                        {!! str_repeat('★', $stars) !!}{!! str_repeat('☆', 5 - $stars) !!}
                                    </td>
                                    <td class="custom-tooltip">
                                        {{ $review->comment ?? 'No review text available' }}
                                        <div class="tooltip-text">{{ $review->comment ?? 'No review text available' }}</div>
                                    </td>
                                    <td>{{ $review->reply_comment ?? 'No reply yet' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary"
                                            onclick="toggleReplyForm('replyForm{{ $review->id }}')" title="Reply"><i
                                                class="fa-solid fa-reply fa-flip-horizontal fa-2xs"></i>
                                        </button>

                                        <div class="reply-form-wrapper">
                                            <div id="replyForm{{ $review->id }}" style="display: none;" class="mt-3">
                                                <button type="button" class="btn-close" aria-label="Close"
                                                    onclick="toggleReplyForm('replyForm{{ $review->id }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                                                        <path d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                                    </svg>
                                                </button>

                                                <form method="POST"
                                                    action="{{ route('reviews.reply', ['id' => $location->id, 'reviewId' => $review->id]) }}">
                                                    @csrf
                                                    @method('POST')
                                                    <div class="position-relative">
                                                        <textarea name="reply" id="selectedReply{{ $review->id }}" class="form-control"
                                                            placeholder="Write your reply...">{{ $review->reply_comment ?? '' }}</textarea>

                                                        <div class="flex items-center mt-2 btns">
                                                            <div class="input-group">
                                                                <input type="hidden" id="numReplies{{ $review->id }}"
                                                                    class="form-control" value="1" min="1" max="10"
                                                                    placeholder="Number of replies">
                                                            </div>

                                                            <a type="button"
                                                                class="btn btn-secondary ai-btn {{ auth()->user()->subscription === 'trial' ? 'disabled' : '' }}"
                                                                onclick="{{ auth()->user()->subscription !== 'trial' ? 'fetchAIReplies(' . $review->id . ')' : 'void(0)' }}"
                                                                {{ auth()->user()->subscription === 'trial' ? 'data-bs-toggle=tooltip data-bs-title="Upgrade your plan to use AI replies"' : '' }}>
                                                                <i class="material-icons left">tips_and_updates</i>
                                                                AI reply
                                                            </a>
                                                        </div>

                                                        <div id="aiReplies{{ $review->id }}" class="ai-replies-panel"
                                                            style="display: none;">
                                                            <div id="aiRepliesList{{ $review->id }}">
                                                                <div class="loading-spinner" style="display: none;">Loading...</div>
                                                            </div>
                                                        </div>

                                                        <div class="buttons" data-review-id="{{ $review->id }}">
                                                            <button class="btn btn-secondary mt-2" type="button"
                                                                onclick="appendAIReplies({{ $review->id }})">
                                                                Generate More Replies
                                                            </button>
                                                            <button class="btn btn-warning mt-2 me-2" type="button"
                                                                onclick="clearRepliesPanel({{ $review->id }})">
                                                                Clear Panel
                                                            </button>
                                                        </div>

                                                        <div class="mt-2 btns">
                                                            @if($review->reply_comment)
                                                                <form method="POST"
                                                                    action="{{ route('reviews.reply.update', ['id' => $location->id, 'reviewId' => $review->id]) }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <button type="submit" class="btn btn-primary mt-2">Update Reply</button>
                                                                </form>

                                                                <form method="POST"
                                                                    action="{{ route('reviews.reply.delete', ['id' => $location->id, 'reviewId' => $review->id]) }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger mt-2">Delete Reply</button>
                                                                </form>
                                                            @else
                                                                <form method="POST"
                                                                    action="{{ route('reviews.reply', ['id' => $location->id, 'reviewId' => $review->id]) }}">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary mt-2">Submit Reply</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="reviews-list">
            <div class="search-box">
                <i class="material-icons">search</i>
                <input type="text" id="reviewSearch" placeholder="Search by business name...">
            </div>
            @foreach($reviews as $review)
                <div class="review-card">
                    <div class="review-header">
                        <div class="profile-avatar">
                            @if(isset($review->profile_photo_url))
                                <img src="{{ asset($review->profile_photo_url) }}" alt="{{ $review->reviewer_name }}">
                            @endif
                        </div>
                        <div class="reviewer-info">
                            <strong>{{ $review->reviewer_name }}</strong>
                            <small>
                                Created: {{ \Carbon\Carbon::parse($review->create_time)->toDayDateTimeString() }}
                                @if($review->update_time)
                                    <br>Updated: {{ \Carbon\Carbon::parse($review->update_time)->toDayDateTimeString() }}
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="rating">
                        @php
                            $ratingMap = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5];
                            $stars = $ratingMap[$review->star_rating] ?? 0;
                        @endphp
                        {!! str_repeat('★', $stars) !!}{!! str_repeat('☆', 5 - $stars) !!}
                    </div>

                    <div class="review-content">
                        {{ $review->comment ?? 'No review text available' }}
                    </div>

                    @if($review->reply_comment)
                        <div class="owner-reply">
                            <strong>Owner's Reply:</strong>
                            <p>{{ $review->reply_comment }}</p>
                        </div>
                    @endif

                    <button type="button" class="btn btn-primary" onclick="toggleReplyForm('replyForm{{ $review->id }}')"
                        title="Reply">
                        <i class="fa-solid fa-reply fa-flip-horizontal fa-2xs"></i>
                    </button>
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
        :root {
            --lineHeight: 1.59;
            --clamp: 7;
        }

        .alert.alert-success {
            background: hsl(215deg 37% 23%) !important;
            color: white !important;
            padding: 1rem;
            width: max-content;
            margin: auto;
            margin-block: 1rem;
            float: inline-end;
            margin-inline-end: 1rem;
            z-index: 1000;
            opacity: 1;
            animation: fadeOutUp 2.22s ease-out forwards;
        }

        @keyframes fadeOutUp {

            0%,
            17% {
                opacity: 1;
                transform: translateY(0);
                scale: 1;
            }

            100% {
                opacity: 0;
                scale: 0.97;
                transform: translateY(-123%);
                display: none;
            }
        }

        .profile-avatar {
            right: 0 !important;
        }

        .custom-tooltip {
            position: relative;
        }

        .tooltip-text {
            display: none;
            position: absolute;
            background: #333;
            color: white;
            padding: 8px;
            border-radius: 4px;
            top: calc(1rem* var(--lineHeight));
            white-space: pre-wrap;
            z-index: 1000;
            height: max-content;
        }

        .custom-tooltip:hover {
            overflow: visible !important;
            color: transparent;

            .tooltip-text {
                display: block;
                overflow: visible;
            }
        }

        .card-content {
            display: grid;
            justify-items: center;
            margin-bottom: 1rem;
        }

        div.container {
            background: ghostwhite;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease-in-out;
        }

        .search-box {
            display: grid;
            background: white;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            grid-column: span 2;
            grid-auto-flow: column;
            align-items: center;
            justify-content: start;

            i {
                color: #666;
                margin-right: 0.5rem;
            }

            input {
                border: none !important;
                outline: none !important;
                padding: 0.5rem !important;
                width: 100%;
                font-size: 1rem;
                background: transparent !important;
                margin: 0 !important;
                height: auto !important;
            }
        }

        div.container {
            display: grid;
        }

        .sync-form {
            margin: 0;
            place-self: end;

            .btn {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0 1.5rem;
                height: 42px;
                border-radius: 4px;
                font-weight: 500;
                text-transform: none;
            }

            .btn i {
                font-size: 20px;
            }
        }


        body button {
            text-transform: capitalize !important;
        }

        .ai-replies-panel {
            --sb-track-color: #c7fff8;
            --sb-thumb-color: hsl(215, 37%, 23%);
            --sb-size: 8px;

            &::-webkit-scrollbar {
                width: var(--sb-size);
            }

            &::-webkit-scrollbar-track {
                background: var(--sb-track-color);
                border-radius: 8px;
            }

            &::-webkit-scrollbar-thumb {
                background: var(--sb-thumb-color);
                border-radius: 8px;
                border: 1px solid #f0f8ff;
            }

            @supports not selector(::-webkit-scrollbar) {
                scrollbar-color: var(--sb-thumb-color) var(--sb-track-color);
            }
        }

        td#rating {
            color: hsl(37 100% 43%);
            font-size: 1.5rem;

            body.theme-light & {
                border-color: black;
            }
        }

        body .btn {
            border-radius: 9px !important;
            background-color: hsl(215deg 37% 23%) !important;
            box-shadow: inset 4px 4px 9px hsl(261deg 87% 13% / 23%), inset -4px -4px 9px hsl(261deg 85% 79% / 20%);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: inline-grid;

            &:hover {
                box-shadow:
                    inset 2px 2px 5px 0px hsl(261deg 85% 79% / 20%),
                    inset -2px -2px 5px hsl(261deg 87% 13% / 23%),
                    2px 2px 5px hsl(261deg 85% 79% / 20%),
                    -2px -2px 5px hsl(261deg 87% 13% / 23%);
            }
        }

        .mt-2 {
            margin-top: 0.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        @media (width > 1200px) {
            .reviews-list {
                display: none;
            }

            .author {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .hamburger.active~.container .reply-form-wrapper.active {
                height: 100svh;
                display: flex;
                position: fixed;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                inset: 0 -16rem 0 0;
                z-index: 9999;
                margin-left: auto !important;
                width: 900px;
                margin-right: auto !important;
            }

            .hamburger:not(.active)~.container .reply-form-wrapper.active {
                height: 100svh;
                display: grid;
                position: fixed;
                align-items: center;
                justify-content: center;
                inset: 0 0 0 0;
                z-index: 9999;
                margin-left: auto !important;
                width: calc(900px + 7.5rem);
                margin-right: auto !important;
            }

            .reply-form-wrapper:not(.active) {
                z-index: -9999;
                display: none;
            }

            .reply-form-wrapper .btn-close {
                position: relative;
                top: -486px;
                left: 100%;
                margin-left: -2.5rem;
                z-index: 9999;
                cursor: pointer;
                display: grid;
                grid-row: 3;
                background: hsl(240 100% 108% / 1);
                width: 2rem !important;
                padding: 3px;
                box-shadow: 1px 1px 5px hsl(0deg 0% 0% / 10%), -1px -1px 5px hsl(0deg 0% 0% / 10%);
            }

            svg {
                scale: 1.29;
            }
        }

        .reply-form-wrapper {
            transition: all 0.3s ease-in-out;

            input {
                text-align: center;
            }

            .btn-close:hover {
                svg:hover {
                    scale: 1.31;
                }
            }
        }

        .buttons {
            width: 92.5%;
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            gap: 1rem;
            position: absolute;
            bottom: 2rem;
            height: 0;
            order: 1;
            z-index: 9999;
            right: 2rem;

            @media (width < 1200px) {
                width: -webkit-fill-available;
                top: 99%;
                right: 0;
                justify-content: center;
                margin-top: 1rem;
            }
        }

        .btns.flex.items-center.mt-2 {
            gap: 0 !important;
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
                padding: 1rem 2rem;
                text-align: left;
                border-radius: 0 !important;
            }

            th::before,
            th::after {
                content: '' !important;
            }

            tr {
                transition: background 0.3s ease-in-out;

                &:nth-child(1) {
                    &~tr {
                        border-top: 2px solid hsl(0 0% 75%) !important;
                    }
                }

                &:hover,
                &:focus,
                &:focus-within {
                    background: hsl(165 23% 87%);
                }

                td {
                    &:nth-child(5) {
                        text-align: center;

                        div[id*="replyForm"][style*="display: flex"] {
                            width: -webkit-fill-available;
                            height: 420px;
                            overflow: hidden;
                            background: aliceblue;
                            border: 1.3px solid hsl(258 53% 53%) !important;
                            border-radius: 0.5rem !important;
                            box-shadow: 2px 2px 8px #00000073, -2px -2px 8px #ffffff73 !important;
                            padding-top: 1.7rem;
                            flex-direction: row;
                            flex-direction: row-reverse;
                            gap: 3rem;
                            display: grid !important;
                            grid-template-rows: repeat(4, 1fr);

                            .position-relative {
                                position: relative;
                                width: -webkit-fill-available;
                                margin: 1.5rem 3rem 1rem 3rem;
                                display: grid;
                                column-gap: 3rem;

                                &:has(.ai-replies-panel[style*="display: block"]) {
                                    grid-template-columns: repeat(2, 1fr);
                                }

                                &:has(.ai-replies-panel[style*="display: none"]) {
                                    grid-template-columns: repeat(1, 1fr);
                                }
                            }

                            textarea {
                                background-color: white;
                                width: 100%;
                                height: 251px !important;
                                padding: 0.5rem !important;
                                margin: 0 !important;

                                @media (width<1200px) {
                                    height: 9rem !important;
                                }
                            }

                            .ai-btn {
                                display: block;
                                color: darkorchid;
                                font-size: 16px;
                                cursor: pointer;
                                background: transparent !important;
                                box-shadow: none;
                                border: 1px solid;

                                &:hover,
                                &:focus,
                                &:focus-within,
                                &:active {
                                    color: hsl(215deg 37% 23%);
                                    border: 1px solid;
                                }
                            }

                            i.left {
                                float: left;
                                margin-right: 7px;
                            }

                            .ai-btn i {
                                font-size: 19px;
                            }

                            .ai-replies-panel {
                                height: 300px;
                                overflow-y: auto;
                                border-block: 1px solid #ccc;
                                padding: 0 0 0 2.9ch;
                                width: fit-content;
                                grid-row: span 4;

                                &:not(:has(.reply-option)):has(.loading-spinner[style*="display: none"]) {
                                    display: none !important;
                                }

                                .loading-spinner[style*="display: none"] .btns {
                                    color: darkorchid;
                                    font-size: 1.2rem;
                                    border: 1px solid;
                                    margin: 1rem;
                                    padding: 0.5rem;
                                    border-radius: 9px !important;

                                    &:hover,
                                    &:focus,
                                    &:focus-within,
                                    &:active {
                                        color: hsl(215deg 37% 23%);
                                        border: 1px solid;
                                    }
                                }
                            }

                            div[id*="aiReplies"]:not(:has(.reply-option))[style*="display: none"]+.buttons {
                                display: none !important;
                            }

                            div[id*="aiReplies"]:has(.reply-option)[style*="display: block"]+.buttons {
                                display: flex !important;
                            }

                            .btns {
                                order: 2;
                                position: relative;
                                bottom: -0.9rem;
                                gap: 1rem;
                            }

                            div[id*="aiRepliesList"]>* {
                                display: list-item;
                                list-style: auto;
                                text-align: justify;
                                padding: 0.5rem;

                                &:first-of-type {
                                    list-style-type: none;
                                }
                            }

                            form {
                                display: grid;
                                grid-auto-flow: column;
                                grid-auto-columns: 1fr 1fr;
                                gap: 3rem;
                            }
                        }
                    }

                    &:nth-child(3) {
                        display: -webkit-box;
                        -webkit-line-clamp: calc(var(--clamp) + 1);
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        border: 0 !important;
                        border-radius: 0 !important;
                        height: calc(var(--clamp)* 1rem* var(--lineHeight));
                        font-size: 1rem;
                        /* place-content: center; */
                        white-space: pre-line;
                    }

                    &:last-child>* {
                        margin: 1rem !important;
                    }
                }
            }

            th {
                font-weight: bold;
                border-radius: 0 !important;

                body.theme-light & {
                    background-color: hsl(from var(--bg-light) h 61% 83%);
                }
            }
        }

        @media (width <=1200px) {
            .reviews-table {
                position: fixed;
                top: 999%;
            }

            div.container>.pagination-controls {
                display: none;
            }

            .reviews-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
                background: ghostwhite;
                padding: 1rem;
                z-index: -1;
                position: absolute;
                padding-top: 4rem;

                @media (width <=768px) {
                    transform: translateX(-1rem);
                    width: 100%;
                    margin-right: 1.5rem;
                }

                @media (768px < width <=1200px) {
                    transform: translateX(-1rem);
                    /* width: 100%; */
                    margin-right: 1.5rem;
                }
            }

            @media (768px < width <=1200px) {
                .hamburger.active~.container .reviews-list {
                    transform: translateX(-1rem);
                    /* width: 100%; */
                    margin-right: 1.5rem;
                }
            }

            .review-card {
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 1.5rem;
                display: grid;
                gap: 1rem;
                grid-template-rows: subgrid;
                grid-row: span 5;
                border: 1.3px solid hsl(258 53% 53% / 1) !important;

                >*:last-child {
                    width: fit-content;
                    justify-self: end;
                }
            }

            .review-header {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .profile-avatar {
                width: 50px;
                height: 50px;
                overflow: hidden;
                border-radius: 0.5rem;

                img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
            }

            .reviewer-info {
                flex: 1;

                strong {
                    display: block;
                    margin-bottom: 0.25rem;
                }

                small {
                    color: #666;
                    font-size: 0.875rem;
                }
            }

            .rating {
                color: hsl(37 100% 43%);
                font-size: 1.5rem;
            }

            .review-content {
                line-height: 1.5;
                color: #333;
            }

            .owner-reply {
                background: #f5f5f5;
                padding: 1rem;
                border-radius: 0.375rem;
                margin-top: 0.5rem;
            }

            .reply-form-wrapper {
                position: fixed;
                inset: 0;
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;

                >div {
                    width: 95%;
                    max-width: 600px;
                    max-height: 90vh;
                    overflow-y: auto;
                    position: relative;
                }
            }

            .reply-form-wrapper.active {
                display: flex;
            }

            .position-relative {
                margin: 2.5rem 1.5rem 1rem 1.5rem !important;

                &:has(.ai-replies-panel[style*="display: block"]) {
                    grid-template-rows: repeat(1, 1fr);
                    grid-template-columns: auto !important;
                }
            }

            div[id*="replyForm"][style*="display: flex"] {
                height: 666px !important;

                &:has(.ai-replies-panel[style*="display: none"]) {
                    height: 300px !important;
                }
            }

            .btn-close {
                position: absolute;
                top: 0.7rem;
                right: 0.7rem;
                z-index: 9999;
                cursor: pointer;
                display: grid;
                background: hsl(240 100% 108% / 1);
                width: 2rem !important;
                padding: 2px;
                box-shadow: 1px 1px 5px hsl(0deg 0% 0% / 10%), -1px -1px 5px hsl(0deg 0% 0% / 10%);
            }

            .ai-replies-panel {
                max-height: 300px;
                overflow-y: auto;
                margin-top: 1rem;
            }
        }

        input.form-control.form-control-sm {
            padding: 0 1rem !important;
        }

        .reviews-table {
            .row {
                display: flex;
                align-items: center;
            }

            label {
                border: 2px solid hsl(208deg 100% 89% / 90%);
                background: aliceblue;
                padding-inline: 2rem 1rem;
                display: inline-block;
            }

            .dataTables_filter {
                position: relative;
                display: flex;
                align-items: center;

                &::before {
                    content: "\1F50D";
                    position: absolute;
                    inset: 1ch auto auto 0;
                    width: 1.5rem;
                    height: 1.5rem;
                    font-size: 1.5rem;
                    color: #666;
                    margin-left: 0.5rem;
                }

                * {
                    float: inline-end;
                    width: -webkit-fill-available !important;
                }
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endpush
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    searchPlaceholder: "Search...",
                    search: "",
                },
                columnDefs: [
                    {
                        targets: 'no-sort',
                        orderable: false
                    }
                ]
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('reviewSearch');
            const urlParams = new URLSearchParams(window.location.search);

            if (searchInput) {
                const currentSearch = urlParams.get('search');
                if (currentSearch) {
                    searchInput.value = currentSearch;
                }

                searchInput.addEventListener('input', debounce(handleSearch, 350));
                searchInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleSearch();
                    }
                });
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function handleSearch() {
            const searchInput = document.getElementById('reviewSearch');
            if (!searchInput) return;

            const searchText = searchInput.value.trim();
            updateUrlAndRefresh('search', searchText);
        }

        function updateUrlAndRefresh(param, value) {
            const url = new URL(window.location.href);

            if (value) {
                url.searchParams.set(param, value);
            } else {
                url.searchParams.delete(param);
            }

            url.searchParams.set('page', '1');

            document.body.style.cursor = 'wait';

            window.location.href = url.toString();
        }

        function changePage(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }

        function toggleReplyForm(formId) {
            const targetForm = document.getElementById(formId);
            const wrapper = targetForm.closest('.reply-form-wrapper');

            if (wrapper.classList.contains('active')) {
                wrapper.classList.remove('active');
                wrapper.style.display = "none";
                targetForm.style.display = "none";
            } else {
                const allWrappers = document.querySelectorAll('.reply-form-wrapper');
                allWrappers.forEach(wrap => {
                    wrap.classList.remove('active');
                    wrap.style.display = "none";
                    const innerForm = wrap.querySelector('div[id^="replyForm"]');
                    if (innerForm) innerForm.style.display = "none";
                });

                wrapper.classList.add('active');
                wrapper.style.display = "flex";
                targetForm.style.display = "flex";
            }
        }

        document.addEventListener('click', (event) => {
            if (event.target.closest('.btn-close') && event.target.closest('.reply-form-wrapper')) {
                const wrapper = event.target.closest('.reply-form-wrapper');
                const targetForm = wrapper.querySelector('div[id^="replyForm"]');

                wrapper.classList.remove('active');
                wrapper.style.display = "none";
                if (targetForm) targetForm.style.display = "none";
            }
        });

        function validateNumReplies(reviewId) {
            const input = document.getElementById(`numReplies${reviewId}`);
            if (!input) return 1;
            let value = parseInt(input.value);
            value = Math.min(Math.max(value || 1, 1), 10);
            input.value = value;
            return value;
        }

        function toggleButtons(reviewId, enable) {
            const buttons = document.querySelectorAll(`[data-review-id="${reviewId}"] .btn`);
            buttons.forEach(button => {
                button.disabled = !enable;
            });
        }

        async function checkAndUpdateButtonStates(reviewId) {
            try {
                const response = await fetch(`/ai/check-reply-status/${reviewId}`);
                const data = await response.json();

                if (data.success) {
                    const aiBtn = document.querySelector(`[data-review-id="${reviewId}"] .ai-btn`);
                    const generateMoreBtn = document.querySelector(`[data-review-id="${reviewId}"] button[onclick*="appendAIReplies"]`);

                    if (data.disable_buttons) {
                        // Disable buttons and update tooltips
                        aiBtn.classList.add('disabled');
                        aiBtn.setAttribute('data-bs-toggle', 'tooltip');
                        aiBtn.setAttribute('data-bs-title', 'Reply limit reached for your plan');

                        if (generateMoreBtn) {
                            generateMoreBtn.classList.add('disabled');
                            generateMoreBtn.setAttribute('data-bs-toggle', 'tooltip');
                            generateMoreBtn.setAttribute('data-bs-title', 'Reply limit reached for your plan');
                        }
                    }

                    // Update tooltip with remaining replies if not disabled
                    if (!data.disable_buttons && data.remaining_replies > 0) {
                        aiBtn.setAttribute('data-bs-title', `${data.remaining_replies} replies remaining`);
                        if (generateMoreBtn) {
                            generateMoreBtn.setAttribute('data-bs-title', `${data.remaining_replies} replies remaining`);
                        }
                    }

                    // Initialize/refresh tooltips
                    const tooltips = [aiBtn];
                    if (generateMoreBtn) tooltips.push(generateMoreBtn);
                    tooltips.forEach(el => {
                        const tooltip = bootstrap.Tooltip.getInstance(el);
                        if (tooltip) tooltip.dispose();
                        new bootstrap.Tooltip(el);
                    });
                }
            } catch (error) {
                console.error('Error checking reply status:', error);
            }
        }

        function fetchStoredReplies(reviewId) {
            console.log('Inside fetchStoredReplies for review:', reviewId);
            const aiRepliesPanel = document.getElementById(`aiReplies${reviewId}`);
            const aiRepliesList = document.getElementById(`aiRepliesList${reviewId}`);

            if (!aiRepliesPanel || !aiRepliesList) {
                console.error('Required elements not found');
                return;
            }

            const loadingSpinner = aiRepliesList.querySelector('.loading-spinner');
            if (loadingSpinner) loadingSpinner.style.display = 'block';
            aiRepliesPanel.style.display = 'block';

            // Check reply status before fetching
            checkAndUpdateButtonStates(reviewId);

            fetch(`/api/reviews/${reviewId}/stored-replies`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    aiRepliesList.innerHTML = '<div class="loading-spinner" style="display: none;">Loading...</div>';
                    if (data.replies && data.replies.length) {
                        data.replies.forEach(reply => {
                            const replyItem = document.createElement('div');
                            replyItem.className = 'reply-option';
                            replyItem.style.cursor = 'pointer';
                            replyItem.textContent = reply.reply_text;
                            replyItem.onclick = () => {
                                const textarea = document.getElementById(`selectedReply${reviewId}`);
                                if (textarea) textarea.value = reply.reply_text;
                            };
                            aiRepliesList.appendChild(replyItem);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching stored replies:', error);
                    aiRepliesList.innerHTML = '<p>Failed to load stored replies. Please try again.</p>';
                })
                .finally(() => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                });
        }

        function fetchAIReplies(reviewId) {
            const aiRepliesPanel = document.getElementById(`aiReplies${reviewId}`);
            const aiRepliesList = document.getElementById(`aiRepliesList${reviewId}`);

            if (!aiRepliesPanel || !aiRepliesList) {
                console.error('Required elements not found');
                return;
            }

            // Show panel and loading spinner
            aiRepliesPanel.style.display = 'block';
            const loadingSpinner = aiRepliesList.querySelector('.loading-spinner');
            if (loadingSpinner) loadingSpinner.style.display = 'block';

            // Check reply status
            checkAndUpdateButtonStates(reviewId);

            // Fetch stored replies
            fetch(`/api/reviews/${reviewId}/stored-replies`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    // Clear previous content
                    aiRepliesList.innerHTML = '<div class="loading-spinner" style="display: none;">Loading...</div>';

                    if (data.replies && data.replies.length) {
                        // If stored replies exist, display them
                        data.replies.forEach(reply => {
                            const replyItem = document.createElement('div');
                            replyItem.className = 'reply-option';
                            replyItem.style.cursor = 'pointer';
                            replyItem.textContent = reply.reply_text;
                            replyItem.onclick = () => {
                                const textarea = document.getElementById(`selectedReply${reviewId}`);
                                if (textarea) textarea.value = reply.reply_text;
                            };
                            aiRepliesList.appendChild(replyItem);
                        });
                    } else {
                        // If no stored replies, show message
                        const noRepliesMessage = document.createElement('p');
                        noRepliesMessage.textContent = 'No existing replies. Use "Generate More Replies" to create suggestions.';
                        aiRepliesList.appendChild(noRepliesMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    aiRepliesList.innerHTML = `<p>Error: ${error.message}</p>`;
                })
                .finally(() => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                });
        }

        function appendAIReplies(reviewId) {
            const aiRepliesList = document.getElementById(`aiRepliesList${reviewId}`);

            // Ensure panel is visible
            const aiRepliesPanel = document.getElementById(`aiReplies${reviewId}`);
            aiRepliesPanel.style.display = 'block';

            // First get current number of replies
            const currentRepliesCount = aiRepliesList.querySelectorAll('.reply-option').length;

            // If no existing replies, generate first 10
            const numReplies = currentRepliesCount === 0 ? 10 : 1;

            const loadingSpinner = aiRepliesList.querySelector('.loading-spinner');
            if (loadingSpinner) loadingSpinner.style.display = 'block';

            // Check reply status before generating
            checkAndUpdateButtonStates(reviewId);

            fetch(`/api/reviews/${reviewId}/ai-replies?num_replies=${numReplies}`)
                .then(response => {
                    if (!response.ok) throw new Error('You have used all your available credits as per your plan. Please upgrade your plan.');
                    return response.json();
                })
                .then(data => {
                    if (data.replies && data.replies.length) {
                        data.replies.forEach(reply => {
                            const replyItem = document.createElement('div');
                            replyItem.className = 'reply-option';
                            replyItem.style.cursor = 'pointer';
                            replyItem.textContent = reply;
                            replyItem.onclick = () => {
                                const textarea = document.getElementById(`selectedReply${reviewId}`);
                                if (textarea) textarea.value = reply;
                            };
                            aiRepliesList.appendChild(replyItem);
                        });
                    } else {
                        const noRepliesMessage = document.createElement('p');
                        noRepliesMessage.textContent = 'No additional replies available.';
                        aiRepliesList.appendChild(noRepliesMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = document.createElement('p');
                    errorMessage.textContent = `Error: ${error.message}`;
                    aiRepliesList.appendChild(errorMessage);
                })
                .finally(() => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                });
        }

        function clearRepliesPanel(reviewId) {
            if (!confirm('Are you sure you want to clear the replies panel?')) return;

            const replyList = document.getElementById(`aiRepliesList${reviewId}`);
            if (replyList) {
                replyList.innerHTML = '<div class="loading-spinner" style="display: none;">Loading...</div>';
            }
            toggleButtons(reviewId, true);
        }

        function showToast(title, message, type = 'info') {
            if (typeof toastr !== 'undefined') {
                toastr[type](message, title);
            } else {
                alert(`${title}: ${message}`);
            }
        }
    </script>
@endpush

