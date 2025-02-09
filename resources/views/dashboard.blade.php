@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="center-align">Welcome to ReviewSync!</h3>
    <p class="center-align">Manage your businesses and customer reviews efficiently.</p>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="cards">
        @if ($isSync)
                <div class="card">
                    <span class="card-title">
                        <i class="material-icons">business_center</i> Total Businesses
                    </span>
                    <p>
                        {{ $totalBusinesses ?? 0 }}/{{ $businessLimit === -1 ? '∞' : $businessLimit }}
                    </p>
                </div>
                <div class="card">
                    <span class="card-title"><i class="material-icons">
                            reviews
                        </i>Total Reviews</span>
                    <p>{{ $totalReviews ?? 0 }}</p>
                </div>
                <div class="card">
                    <span class="card-title">
                        <i class="material-icons">auto_awesome</i>
                        Total AI Replies
                    </span>
                    <p>
                        {{ $totalAiReplies ?? 0 }}/{{ $aiReplyLimit === -1 ? '∞' : $aiReplyLimit }}
                    </p>
                </div>
            </div>
        @else
            <a class="glogin" href="{{ route('auth.google') }}">Google Login</a>
        @endif
</div>

@push('styles')
    <style>
        .glogin {
            background: hsl(215deg 37% 23%) !important;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            text-decoration: none;
            cursor: pointer;
            text-align: center;
            font-size: 16px;
            box-shadow: inset 4px 4px 9px hsl(261deg 87% 13% / 23%), inset -4px -4px 9px hsl(261deg 85% 79% / 20%);
            transition: all 0.2s ease-in-out;
            display: inline-grid;
            width: max-content !important;

            &:hover {
                box-shadow:
                    inset 2px 2px 5px 0px hsl(261deg 85% 79% / 20%),
                    inset -2px -2px 5px hsl(261deg 87% 13% / 23%),
                    2px 2px 5px hsl(261deg 85% 79% / 20%),
                    -2px -2px 5px hsl(261deg 87% 13% / 23%);
            }
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
            animation: fadeOutUp 2s ease-out forwards;
        }

        @keyframes fadeOutUp {

            0%,
            15% {
                opacity: 1;
                transform: translateY(0);
                scale: 1;
            }

            100% {
                opacity: 0;
                scale: 0.97;
                transform: translateY(-100%);
                display: none;
            }
        }

        p.center-align{
            margin-bottom: 1rem;
        }

        div.container {
            display: grid;
            justify-items: center;
        }

        .col.s12 {
            width: 100% !important;
            height: 100% !important;
            place-items: center;
            display: grid !important;
        }

        .cards {
            display: grid;
            column-gap: 2rem;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            width: min(90%, 1200px);
            margin: auto;
            padding: 0 0 1rem;

            i {
                margin-top: 0.1ch;
                font-size: 1.6rem;
            }

            p {
                margin-inline: 2.47rem;
                font-size: 1.2rem;
            }

            >* {
                padding: 1rem;
                font-weight: 400;

                .card-title {
                    font-size: 24px;
                    font-weight: 400;
                    display: flex;
                    align-items: center;
                    gap: 0.7rem;
                }

                &:nth-child(1) {
                    background: hsl(243 43% 53%) !important;
                    color: white;
                }

                &:nth-child(2) {
                    background: hsl(173 43% 47%) !important;
                    color: white;

                    i {
                        margin-top: 0.2ch;
                    }
                }

                &:nth-child(3) {
                    background: hsl(0 43% 53%) !important;
                    color: white;
                }
            }
        }
    </style>
@endpush
@endsection