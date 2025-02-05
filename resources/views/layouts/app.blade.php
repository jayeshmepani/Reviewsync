<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReviewSync')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg-light: hsl(222 9 91% / 1);
            --text-light: #333333;
            --degree: 37deg;
            --distance: 0.29ch;
        }

        .breadcrumb {
            margin-bottom: 15px;
            font-size: 20px;
            color: #555;
            font-weight: 500;
            margin-inline: 1rem;
        }

        .breadcrumb a {
            text-decoration: none;
            color: #007BFF;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        body :is(button, a, .btn) {
            text-transform: capitalize !important;
        }

        body.theme-light {
            background-color: var(--bg-light);
            color: var(--text-light);
        }

        body.theme-light .card {
            background-color: hsl(0deg 0% 97%);
        }

        .theme-toggle {
            z-index: 50;
            cursor: pointer;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        button#theme-toggle {
            display: flex;
            aspect-ratio: 1;
            height: 40px;
            width: auto;
            align-items: center;
            justify-content: center;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
            position: absolute;
            left: 1.5rem;
            top: 1rem;

            &.active {
                transform: translateX(15rem);

                .hamburger-line:nth-child(1) {
                    transform: rotate(calc(0deg - var(--degree))) translateY(calc(0ch - var(--distance)));
                    transform-origin: right;
                }

                .hamburger-line:nth-child(2) {
                    opacity: 0;
                }

                .hamburger-line:nth-child(3) {
                    transform: rotate(var(--degree)) translateY(var(--distance));
                    transform-origin: right;
                }
            }

            .hamburger-line {
                width: 19px;
                height: 3px;
                background-color: white;
                margin: 3px 0;
                transition: all 0.3s ease;
            }
        }

        .mobile-menu {
            display: grid;
            position: fixed;
            top: 0;
            width: max-content;
            padding-inline: 1.5rem;
            height: 100%;
            background-color: hsl(0, 0%, 95%);
            color: black;
            font-weight: 500;
            backdrop-filter: blur(3px) saturate(111.11%);
            z-index: 90;
            border-radius: 0 !important;
            border-right: 1.5px solid hsl(0 0 50% / 0.5);
            transform: translateX(0);
            transition: transform 0.3s ease-in-out, background-color 0.3s, color 0.3s;

            &:not(.active) {
                transform: translateX(-100%);
            }

            a {
                color: inherit;
                font-size: 1.5rem;
                margin: 3px 0;
                text-decoration: none;
                cursor: pointer;
            }

            .active {
                background: hsl(215 53% 73%);
                color: hsl(231 69% 37%);
                font-weight: bold;
                border-radius: 5px !important;
            }
        }

        .menu_wrap {
            display: grid;
            justify-items: flex-start;
            align-self: flex-start;

            i {
                bottom: 0.1ch;
                position: relative;
                vertical-align: middle;
            }

            >* {
                width: -webkit-fill-available;
                padding: 0.5rem;
                border-radius: 0 !important;
                font-size: 1rem !important;

                &:first-child,
                &:last-child {
                    border-bottom: none;
                }

                body.theme-light & {
                    &:first-child {
                        filter: invert(1);
                    }
                }
            }
        }

        @media screen and (max-width: 768px) {
            .hamburger {
                display: flex;
                transform: translateX(0);

                &.active {
                    transform: translateX(15rem);
                }
            }

            .mobile-menu {
                transform: translateX(-100%);

                &.active {
                    transform: translateX(0);
                }
            }
        }

        @media screen and (min-width: 769px) {
            .hamburger {
                display: flex;
                transform: translateX(15rem);

                .hamburger-line:nth-child(1) {
                    /* transform: rotate(-45deg) translate(-50%, -0.1ch); */

                    transform: rotate(calc(0deg - var(--degree))) translateY(calc(0ch - var(--distance)));
                    transform-origin: right;
                }

                .hamburger-line:nth-child(2) {
                    opacity: 0;
                }

                .hamburger-line:nth-child(3) {
                    /* transform: rotate(45deg) translate(-50%, -0.1ch); */

                    transform: rotate(var(--degree)) translateY(var(--distance));
                    transform-origin: right;
                }

                &:not(.active) {
                    transform: translateX(0);

                    .hamburger-line:nth-child(1) {
                        transform: none;
                    }

                    .hamburger-line:nth-child(2) {
                        opacity: 1;
                    }

                    .hamburger-line:nth-child(3) {
                        transform: none;
                    }
                }
            }

            .mobile-menu {
                transform: translateX(0);

                &:not(.active) {
                    transform: translateX(-100%);
                }
            }
        }

        .theme-toggle {
            display: none;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Roboto', sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        h1 {
            font-size: 2.75rem !important;
            font-weight: 700 !important;

            @media (max-width: 768px) {
                font-size: 2.1rem !important;
            }
        }

        h2 {
            font-size: 2.5rem !important;
            font-weight: 600 !important;

            @media (max-width: 768px) {
                font-size: 1.8rem !important;
            }
        }

        h3 {
            font-size: 2rem !important;
            font-weight: 600 !important;

            @media (max-width: 768px) {
                font-size: 1.4rem !important;
            }
        }

        h4 {
            font-size: 1.75rem !important;
            font-weight: 500 !important;

            @media (max-width: 768px) {
                font-size: 1.3rem !important;
            }
        }

        h5 {
            font-size: 1.5rem !important;
            font-weight: 500 !important;

            @media (max-width: 768px) {
                font-size: 1.2rem !important;
            }
        }

        h6 {
            font-size: 1.25rem !important;
            font-weight: 400 !important;

            @media (max-width: 768px) {
                font-size: 0.9rem !important;
            }
        }

        nav.blue {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        input,
        textarea {
            box-shadow: none !important;
        }

        textarea {
            height: 9rem !important;
            border: 1.5px solid gray !important;
            border-radius: 5px !important;
            margin-top: 0.7rem !important;
            padding: .8rem 0.5rem .8rem 0.5rem !important;

            &::placeholder {
                padding-left: 0.7rem;
            }
        }

        .input-field:has(textarea)>label {
            font-size: 1.3rem !important;
            position: relative !important;
        }

        .input-field>label {
            font-size: 1.3rem !important;
            color: gray !important;
        }

        nav ul a {
            font-size: 1.3rem !important;
        }

        hr {
            border-top-width: 3px;
            border-color: gray;
            display: none !important;
        }

        .review-text {
            padding-bottom: 0.5rem !important;
            font-size: 1rem !important;
            line-height: 1.5 !important;
            transition: color 0.3s ease !important;
        }

        body.theme-light .review {
            padding: 1rem 1rem 0 !important;
            border: 1.3px solid hsl(258 53% 53% / 1) !important;
            border-radius: 0.5rem !important;
            box-shadow: 2px 2px 8px #00000073, -2px -2px 8px #ffffff73 !important;
        }

        .container:has(.review) {
            width: 95% !important;
            display: flex !important;
            flex-direction: column !important;
            row-gap: 1rem !important;
            padding: 1rem !important;
            border-radius: 1rem !important;
        }

        .profile-avatar>* {
            border-radius: 100vh !important;
        }

        @media (max-width: 768px) {
            .container:has(.review) {
                width: 100% !important;
            }

            .review {
                padding: 0.8rem !important;
            }

            .brand-logo {
                display: flex;
                align-items: center;
                flex-direction: row;
                width: -webkit-fill-available !important;
            }

            .profile-avatar {
                right: 0.7rem;
                position: relative;
            }
        }

        @media (width > 768px) {
            .hamburger.active~.container {
                max-width: calc(100svw - 21.25rem) !important;
                margin-right: 2.5rem;
            }

            .hamburger:not(.active)~.container {
                max-width: auto !important;
                margin: auto;
            }

            .nav-wrapper.container:is(.brand-logo) {
                display: none;
            }

            .profile-avatar {
                right: 0.7rem !important;
                position: relative;
            }
        }

        .container {
            transition: max-width 0.3s ease-in-out,
                margin 0.3s ease-in-out;
        }

        * {
            border-radius: 0.5rem !important;
        }

        header {
            border-radius: 0 !important;
        }

        .blue {
            background-color: hsl(215deg 37% 23%) !important;
            border-radius: 0 !important;
        }

        .rounded-full {
            border-radius: 50% !important;
        }

        .masked-logo {
            width: 173px;
            height: 47px;
            background-color: white;
            mask: url('{{ asset('images/reviewsync.png') }}') no-repeat center;
            -webkit-mask: url('{{ asset('images/reviewsync.png') }}') no-repeat center;
            mask-size: contain;
            -webkit-mask-size: contain;
        }

        nav .brand-logo {
            display: none !important;
        }

        .green.darken-2 {
            background-color: hsl(215deg 41% 35%) !important;
            box-shadow: inset 4px 4px 10px hsl(215 41% 37% / 1), inset -4px -4px 10px hsl(215deg 47% 57% / 96.86%);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: 1px solid hsl(215 37% 57% / 1);

            &:hover {
                box-shadow: inset 2px 2px 5px 0px hsl(215deg 85% 79% / 20%), inset -2px -2px 5px hsl(215deg 87% 13% / 23%), 2px 2px 5px hsl(215deg 85% 79% / 20%), -2px -2px 5px hsl(215deg 87% 13% / 23%);
            }
        }

        .red-text,
        .blue-text {
            font-weight: 500;
            transition: all 0.23s ease-in-out !important;

            &:is(.blue-text) {
                color: hsl(222, 89%, 47%) !important;

                &:not(.blue-text) {
                    color: hsl(4, 89%, 45%) !important;
                }
            }

            &:hover {
                font-size: 1.11rem;
            }
        }

        .row>* {
            width: -webkit-fill-available !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }


        .profile-pic {
            cursor: pointer;
        }

        .right-profile {
            display: flex;
            flex-direction: row;
            align-items: center;
            position: absolute;
            right: 0.3rem;
            gap: 1.5rem;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 4rem;
            right: 2.5rem;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 0;
            list-style: none;
            z-index: 1000;
            border-radius: 4px;

            @media (width < 768px) {
                right: 2.5rem;
                left: auto;
            }

        }

        .dropdown-menu li {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-menu li:last-child {
            border-bottom: none;
        }

        .dropdown-menu li a {
            display: flex;
            align-items: center;
            color: #000;
            text-decoration: none;
        }

        .dropdown-menu li a i {
            margin-right: 10px;
        }

        .dropdown-menu.show {
            display: block;
        }
    </style>

    @stack('styles')
</head>

<body class="theme-light">
    <div class="hamburger">
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
        <div class="hamburger-line"></div>
    </div>
    <div class="mobile-menu">
        <div class="menu_wrap">
            @guest
                <a href="{{ route('login') }}">
                    <i class="material-icons">login</i> Login
                </a>
            @else
                <a href="/" class="brand-logo">
                    <div class="masked-logo"></div>
                </a>
                <a href="{{ route('dashboard') }}" class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <i class="material-icons">dashboard</i> Dashboard
                </a>
                <hr style="display:block !important; padding:0;">
                <a href="{{ route('businesses') }}" class="{{ Request::routeIs('businesses') ? 'active' : '' }}">
                    <i class="material-icons">business</i> Businesses
                </a>
                <hr style="display:block !important; padding:0;">
                <a href="{{ route('subscriptions.index') }}"
                    class="{{ Request::routeIs('subscriptions.index') ? 'active' : '' }}">
                    <i class="material-icons">subscriptions</i> Subscription
                </a>
            @endguest
        </div>
    </div>

    <header>
        <nav class="blue">
            <div class="right-profile">
                <div class="user-name">{{ auth()->user()->name ?? '' }}</div>
                <div class="profile-avatar">
                    @if(auth()->user()?->profile_picture)
                        <img src="{{ asset(auth()->user()->profile_picture) }}" alt="Profile Picture"
                            class="profile-pic dropdown-trigger" width="44" height="44" data-target="profile-menu">
                    @else
                        <img src="{{ asset('images/user.png') }}" alt="Default Profile Picture"
                            class="profile-pic dropdown-trigger" width="44" height="44" data-target="profile-menu">
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <ul id="profile-menu" class="dropdown-menu">
        <li>
            <a href="{{ route('profile') }}">
                <i class="material-icons">account_circle</i> Profile
            </a>
        </li>
        <li>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="material-icons">logout</i> Logout
            </a>
        </li>
    </ul>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>

    <main class="container">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const hamburger = document.querySelector('.hamburger');
            const mobileMenu = document.querySelector('.mobile-menu');
            const profilePic = document.querySelector('.profile-pic');
            const dropdownMenu = document.getElementById('profile-menu');

            const updateMobileThemeText = () => {
                mobileThemeText.textContent = body.classList.contains('theme-dark') ? 'Light' : 'Dark';
            };

            const updateMenuState = () => {
                const isWideScreen = window.innerWidth > 768;
                hamburger.classList.toggle('active', isWideScreen);
                mobileMenu.classList.toggle('active', isWideScreen);
            };

            updateMenuState();
            window.addEventListener('resize', updateMenuState);

            hamburger?.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                mobileMenu.classList.toggle('active');
            });

            profilePic?.addEventListener('click', () => {
                dropdownMenu?.classList.toggle('show');
            });

            document.addEventListener('click', (event) => {
                if (profilePic && dropdownMenu && !profilePic.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>