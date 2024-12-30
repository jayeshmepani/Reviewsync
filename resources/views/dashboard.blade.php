@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="center-align">Welcome to ReviewSync!</h3>
    <p class="center-align">Manage your businesses and customer reviews efficiently.</p>

    <div class="row">
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <h4>Manage Businesses</h4>
                    <p>Add, view, and manage your registered businesses.</p>
                </div>
                <div class="card-action">
                    <a href="{{ route('businesses.index') }}" class="btn green darken-2">Manage Businesses</a>
                </div>
            </div>
        </div>

        <div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <h4>Customer Reviews</h4>
                    <p>View and respond to reviews for your businesses.</p>
                </div>
                <div class="card-action">
                    @foreach ($businesses as $business)
                        <div id="card-action-cust">
                            <h4>{{ $business->title }}</h4>
                            <a href="{{ route('businesses.reviews', $business->id) }}" class="btn green darken-2">Manage
                                Reviews</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <style class="123">
        .card-action {
            border-top: 2px solid rgba(160, 160, 160, 0.2);

            &:last-child {
                border-radius: 0 0 8px 8px !important;

            }
        }

        #card-action-cust {
            border-bottom: 1px hsl(203 37% 47%) solid;
            padding-block: 1rem;
            border-radius: 0 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;

            &:last-child {
                border-bottom: 0;
            }
        }
    </style>
@endpush
