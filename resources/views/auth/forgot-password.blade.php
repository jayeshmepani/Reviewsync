@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="center-align">Forgot Password</h1>

    @if (session('status'))
        <div class="card-panel green lighten-3">
            <li>
                {{ session('status') }}
            </li>
        </div>
    @endif

    <div class="row">
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="input-field col s12">
                <input id="email" type="email" name="email" class="validate" required placeholder="Email">
                @error('email')
                    <span class="red-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="center-align">
                <button type="submit" class="btn waves-effect waves-light">Send Password Reset Link</button>
            </div>
        </form>
    </div>
</div>
@endsection
<style>
    input {
        border-radius: 7.5px !important;
        padding-left: 1rem !important;
        width: -webkit-fill-available !important;
        width: -moz-available !important;
    }

    .input-field {
        position: relative;
        margin-bottom: 1rem !important;
        padding: 0;
    }

    div.container:has(form) {
        width: 640px !important;
        height: calc(97svh - 9rem) !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .center-align:has(a, button)>* {
        width: min(97%, 621px) !important;
    }

    .center-align {
        width: -webkit-fill-available;
        width: -moz-available;

        p {
            margin: 1rem 0 0.5rem;
        }
    }

    @media (max-width: 640px) {
        div.container:has(form) {
            width: 90% !important;
            height: calc(97.3svh - 8.7rem) !important;
        }

        .center-align:has(a, button)>* {
            width: 97% !important;
        }

        form {
            width: 85svw;
        }
    }

    body .btn {
        border-radius: 5.5rem !important;
        /* background-color: hsl(258 57% 37%) !important; */
        background-color: hsl(215deg 37% 23%) !important;
        box-shadow: inset 4px 4px 9px hsl(261deg 87% 13% / 23%), inset -4px -4px 9px hsl(261deg 85% 79% / 20%);
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: inline-flex;
        gap: 0.7rem;
        align-items: center;
        justify-content: center;
        font-size: 1rem;

        &:hover {
            box-shadow:
                inset 2px 2px 5px 0px hsl(261deg 85% 79% / 20%),
                inset -2px -2px 5px hsl(261deg 87% 13% / 23%),
                2px 2px 5px hsl(261deg 85% 79% / 20%),
                -2px -2px 5px hsl(261deg 87% 13% / 23%);
        }
    }

    form {
        display: grid;
        justify-items: center;
        padding-top: 1rem !important;
        border: 1.5px solid;
        padding: 1rem;
        border-top: 0px;
        border-radius: 0 0 1rem 1rem !important;
        background: hsl(210 11% 85%/ 1);
    }

    h1.center-align {
        padding-top: 1rem !important;
        border: 1.5px solid;
        padding: 1rem !important;
        border-bottom: 0px;
        border-radius: 1rem 1rem 0 0 !important;
        background: hsl(210 11% 85%/ 1);
    }

    .row {
        display: contents;
    }

    .theme-light {

        input,
        textarea {
            border-radius: 7.5px !important;
        }

        &:is(.theme-light) {

            input,
            textarea {
                border: 1px solid #cccccc !important;
                background-color: #f0f4f8 !important;
            }

            .input-field.col.s12:has(input)::before {
                background: #cccccc;
                color: black;
            }
        }

        .input-field.col.s12:has(input)::before {
            content: '';
            display: none;
            width: max-content;
            height: max-content;
            position: absolute;
            inset: -1.5ch 24px;
            border-radius: 7px;
            padding: 0 0.5rem;
            background: #4a5568;
            color: white;
            opacity: 0;
            transform: translateY(5px);
            transition: opacity 0.37s ease-in-out, transform 0.37s ease-in-out !important;
        }

        .input-field.col.s12 {
            &:has(#email)::before {
                content: 'Email';
            }

            &:has(input:not(:placeholder-shown))::before,
            &:has(input:focus-within)::before {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }

            &:has(input:placeholder-shown)::before {
                opacity: 0;
            }
        }
    }

    li {
        background-color: hsl(143 55% 33% / 1);
        width: max-content;
        margin: auto;
        padding: 1rem;
        color: white;
        list-style: none;
    }

    .card-panel.green.lighten-3 {
        border-right: 1px solid !important;
        border-left: 1px solid !important;
        border-radius: 0 !important;
        width: -webkit-fill-available !important;
        width: -moz-available !important;
        background: hsl(210 11% 85%/ 1) !important;
        -webkit-box-shadow: 0 0 0 0 rgb(0 0 0 / 0%) !important;
        box-shadow: 0 0 0 0 rgb(0 0 0 / 0%) !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    header,
    .hamburger,
    .mobile-menu {
        display: none !important;
    }

    .hamburger.active~.container {
        max-width: calc(100svw) !important;
        margin-right: auto !important;
    }

    .hamburger:not(.active)~.container {
        max-width: auto !important;
        margin: auto !important;
    }

    div.container:has(form) {
        height: calc(100svh) !important;
        place-content: center;
    }
</style>
