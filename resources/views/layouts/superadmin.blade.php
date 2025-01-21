<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Dashboard - @yield('title')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        a.block.px-4.py-2.hover\:bg-gray-100 {
            display: flex;
            gap: 0.5rem;
        }

        nav.nav {
            display: flex;
            justify-content: space-between;
            margin-inline: 1.5rem;
            align-items: center;
            height: 56px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white">
        <nav class="nav">
            <div>
                <h1 class="text-xl font-bold">SuperAdmin Dashboard</h1>
            </div>
            <div class="flex items-center">
                <div class="mr-4">{{ auth()->user()->name ?? '' }}</div>
                <div class="relative">
                    @if(auth()->user()?->profile_picture)
                        <img src="{{ asset(auth()->user()->profile_picture) }}" alt="Profile Picture"
                            class="rounded-full w-10 h-10 object-cover cursor-pointer" id="profile-menu-trigger">
                    @else
                        <img src="{{ asset('images/user.png') }}" alt="Default Profile Picture"
                            class="rounded-full w-10 h-10 object-cover cursor-pointer" id="profile-menu-trigger">
                    @endif
                    <!-- Dropdown Menu -->
                    <ul id="profile-menu" class="absolute right-0 mt-2 bg-white text-gray-800 shadow-lg rounded hidden">
                        <li class="border-b">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 hover:bg-gray-100">
                                <i class="material-icons">account_circle</i> Profile
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="block px-4 py-2 hover:bg-gray-100">
                                <i class="material-icons">logout</i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-semibold">SuperAdmin</h2>
            </div>
            <nav class="mt-4">
                <a href="{{ route('superadmin.dashboard') }}"
                    class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('superadmin.dashboard') ? 'bg-gray-700' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('superadmin.users') }}"
                    class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('superadmin.users*') ? 'bg-gray-700' : '' }}">
                    Manage Users
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success') }}
                </div>
            @endif


            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Dropdown toggle functionality
        const profileMenuTrigger = document.getElementById('profile-menu-trigger');
        const profileMenu = document.getElementById('profile-menu');
        profileMenuTrigger.addEventListener('click', () => {
            profileMenu.classList.toggle('hidden');
        });

        // Hide menu when clicking outside
        document.addEventListener('click', (event) => {
            if (!profileMenuTrigger.contains(event.target) && !profileMenu.contains(event.target)) {
                profileMenu.classList.add('hidden');
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    @stack('scripts')
</body>

</html>