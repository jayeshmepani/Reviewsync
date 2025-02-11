@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="center-align">Login</h1>

    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="card-panel red lighten-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="input-field col s12">
                <input id="email" type="email" name="email" class="validate" required placeholder="Email">
            </div>

            <div class="input-field col s12 password-field">
                <input class="password" id="password" type="password" name="password" class="validate" required
                    placeholder="Password">
                <button type="button" class="password-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <!-- Remember Me Checkbox -->
            <div class="input-field col s12">
                <label>
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember Me</span>
                </label>
            </div>

            <div class="center-align">
                <button type="submit" class="btn waves-effect waves-light">Login</button>
            </div>

            <!-- Forgot Password Link -->
            <div class="center-align">
                <a href="{{ route('password.request') }}">Forgot Your Password?</a>
            </div>

            <div class="or-separator">
                <p>OR</p>
            </div>

            <div class="center-align">
                <a href="{{ route('login.google') }}" class="btn btn-danger">
                    <i class="fa-brands fa-google"></i> Continue with Google
                </a>
            </div>

            <div class="center-align">
                <p>Don't have an account?</p>
                <a href="{{ route('signup') }}" class="btn waves-effect waves-light">Sign Up</a>
            </div>
        </form>
    </div>
</div>
<script>
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function () {
            const passwordField = this.closest('.password-field');
            const passwordInput = passwordField.querySelector('.password');
            const toggleButton = this.querySelector('svg');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                passwordInput.type = 'password';
                toggleButton.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        });
    });
</script>
@endsection
<style>
    .password-field {
        position: relative;
    }

    .input-field.col.s12.password-field {
        display: flex;
        flex-direction: row-reverse;
        align-items: center;
    }

    .password-toggle {
        position: absolute;
        padding: 0.9rem;
        transform: translateY(-0.5ch);
        background: none;
        border: none;
        cursor: pointer;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;

        &:hover {
            color: #6b46c1;
        }
    }

    button:focus {
        outline: none;
        background: none !important;
    }

    h1.center-align {
        padding: 1rem !important;
        border: 1.5px solid;
        border-bottom: 0px;
        border-radius: 1rem 1rem 0 0 !important;
        background: hsl(210 11% 85%/ 1);
    }

    .input-field>label {
        font-size: 1.3rem !important;
        position: relative !important;
    }

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

    .row {
        display: contents;
    }

    .or-separator {
        display: flex;
        align-items: center;
        margin: 16px 0;
        color: hsl(222deg 7% 37%);
        font-size: 14px;
        font-weight: 500;
        width: -webkit-fill-available;
        width: -moz-available;
    }

    .or-separator::before,
    .or-separator::after {
        content: '';
        flex-grow: 1;
        display: flex;
        border-top: 1.5px solid hsl(0 0% 51% / 1);
    }

    .or-separator::before {
        margin: 0 37px 0 17px;
    }

    .or-separator::after {
        margin: 0 17px 0 37px;
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

    li {
        background-color: hsl(4, 89.60%, 58.40%) !important;
        width: max-content;
        margin: auto;
        padding: 1rem;
        color: white;
    }

    .card-panel.red.lighten-3 {
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

            &:has(#password)::before {
                content: 'Password';
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
    }
</style>
