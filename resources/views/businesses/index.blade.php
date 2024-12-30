@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="center-align">All Businesses</h1>

    <!-- Main content of businesses goes here -->
    <div class="row">
        @foreach ($businesses as $business)
            <div class="col s12 m6 l4">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">{{ $business->title }}</span>
                        <p>{{ $business->formatted_address }}</p>
                    </div>
                    <div class="card-action">
                        @if (Auth::id() == $business->user_id)
                            <!-- Delete Form -->
                            <form action="{{ route('businesses.delete', $business) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-flat red-text">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($businesses->isEmpty())
        <p class="center-align grey-text">No businesses found. Click "Create Business" to add one.</p>
    @endif
</div>

<style>
    /* Base style for the button */
    .btn-create-business {
        position: relative;
        display: inline-block;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: white;
        background-color: #2196F3;
        border: none;
        border-radius: 50% !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;/ cursor: pointer;
    }

    /* Hover effect */
    .btn-create-business:hover {
        background-color: #1976D2;
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);

    }

    /* Active (click) effect */
    .btn-create-business:active {
        transform: translateY(2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* Focus effect */
    .btn-create-business:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.5);
    }

    i.material-icons:hover {
        transform: rotate(45deg);
        transition: transform 0.3s ease-in-out;
    }
</style>

@endsection