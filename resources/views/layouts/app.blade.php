<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ReviewSync')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg-light: hsl(222 9 91% / 1);
            --text-light: #333333;
            --bg-dark: hsl(222 25 7%);
            --text-dark: #e2e8f0;
        }

        body {
            transition: background-color 0.3s, color 0.3s;
        }

        body.theme-dark {
            background-color: var(--bg-dark);
            color: var(--text-dark);
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
            transition: transform 0.2s;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        body.theme-dark .card {
            background-color: #2d3748;
            color: var(--text-dark);
        }

        body.theme-dark .card-panel {
            background-color: #2d3748;
            color: var(--text-dark);
        }

        body.theme-dark .card-content {
            color: var(--text-dark);
        }

        body.theme-dark .btn {
            background-color: #4a5568;
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
                transform: translateX(21.5rem);

                .hamburger-line:nth-child(1) {
                    transform: rotate(-45deg) translate(-50%, -0.1ch);
                }

                .hamburger-line:nth-child(2) {
                    opacity: 0;
                }

                .hamburger-line:nth-child(3) {
                    transform: rotate(45deg) translate(-50%, -0.1ch);
                }
            }

            .hamburger-line {
                width: 25px;
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
            background-color: var(--bg-light);
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

            a,
            .theme-switch {
                color: inherit;
                font-size: 1.5rem;
                margin: 3px 0;
                text-decoration: none;
                cursor: pointer;
            }
        }

        body.theme-dark {
            .mobile-menu {
                background-color: hsl(from var(--bg-dark) h s l);
                backdrop-filter: blur(3px) saturate(111.11%);
                color: white;
                font-weight: 500;
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
                margin: 0;
                border-bottom: 1px solid gray;
                border-radius: 0 !important;

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
                    transform: translateX(20.8rem);
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
                transform: translateX(20.8rem);

                .hamburger-line:nth-child(1) {
                    transform: rotate(-45deg) translate(-50%, -0.1ch);
                }

                .hamburger-line:nth-child(2) {
                    opacity: 0;
                }

                .hamburger-line:nth-child(3) {
                    transform: rotate(45deg) translate(-50%, -0.1ch);
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

        .brand-logo {
            left: 1rem !important;
            position: relative;
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

        body.theme-dark .review {
            padding: 1rem 1rem 0 !important;
            border: 1.3px solid hsl(258 53% 53% / 1) !important;
            border-radius: 0.5rem !important;
            box-shadow: 2px 2px 8px #ffffff73, -2px -2px 8px #00000073 !important;
            transition: all 0.3s ease-in-out !important;
        }

        body.theme-light .review {
            padding: 1rem 1rem 0 !important;
            border: 1.3px solid hsl(258 53% 53% / 1) !important;
            border-radius: 0.5rem !important;
            box-shadow: 2px 2px 8px #00000073, -2px -2px 8px #ffffff73 !important;
            transition: all 0.3s ease-in-out !important;
        }

        .container:has(.review) {
            width: 95% !important;
            display: flex !important;
            flex-direction: column !important;
            row-gap: 1rem !important;
            padding: 1rem !important;
            border-radius: 1rem !important;
            transition: all 0.3s ease-in-out !important;
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
                max-width: calc(100svw - 26.25rem) !important;
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
            background-color: hsl(258 57% 31% / 1) !important;
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
            background-color: hsl(123deg 41% 35%) !important;
            box-shadow: inset 4px 4px 10px hsl(123 41% 37% / 1), inset -4px -4px 10px hsl(137deg 47% 57% / 96.86%);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: 1px solid hsl(123 37% 57% / 1);

            &:hover {
                box-shadow: inset 2px 2px 5px 0px hsl(137deg 85% 79% / 20%), inset -2px -2px 5px hsl(137deg 87% 13% / 23%), 2px 2px 5px hsl(137deg 85% 79% / 20%), -2px -2px 5px hsl(137deg 87% 13% / 23%);
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


            body.theme-dark & {
                filter: invert(1);
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
            right: 0;
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

            body.theme-dark & {
                filter: invert(1);
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
                <a href="{{ route('dashboard') }}">
                    <i class="material-icons">dashboard</i> Dashboard
                </a>

                <a href="{{ route('business-info') }}">
                    <i class="material-icons">business</i> Business info
                </a>
            @endguest

            <div class="theme-switch" id="mobile-theme-switch">
                <i class="material-icons">brightness_6</i> Switch to&nbsp;<span
                    id="mobile-theme-text">Dark</span>&nbsp;Theme
            </div>
        </div>
    </div>

    <header>
        <nav class="blue">
            <div class="right-profile">
                <div class="user-name">{{ auth()->user()->name ?? '' }}</div>
                <div class="profile-avatar">
                    @if(auth()->user() && auth()->user()->profile_picture)
                        <img src="{{ asset(auth()->user()->profile_picture) }}" alt="Profile Picture"
                            class="profile-pic dropdown-trigger" width="44" height="44" data-target="profile-menu">
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <ul id="profile-menu" class="dropdown-menu">
        <li>
            <a href="{{ route('Profile') }}">
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
            const mobileThemeSwitch = document.getElementById('mobile-theme-switch');
            const mobileThemeText = document.getElementById('mobile-theme-text');
            const profilePic = document.querySelector('.profile-pic');
            const dropdownMenu = document.getElementById('profile-menu');

            const updateMobileThemeText = () => {
                mobileThemeText.textContent = body.classList.contains('theme-dark') ? 'Light' : 'Dark';
            };

            const toggleTheme = () => {
                const isDark = body.classList.contains('theme-dark');
                body.className = isDark ? 'theme-light' : 'theme-dark';
                localStorage.setItem('theme', body.className);
                updateMobileThemeText();
            };

            body.className = localStorage.getItem('theme') || 'theme-light';
            updateMobileThemeText();

            mobileThemeSwitch?.addEventListener('click', toggleTheme);

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

</body>

</html>