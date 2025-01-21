<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReviewSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --bg-light: hsl(222 9 91% / 1);
            --text-light: #333333;
        }

        body {
            transition: background-color 0.3s, color 0.3s;
            line-height: 1.6;

            &.theme-light {
                background-color: var(--bg-light);
                color: var(--text-light);
            }
        }

        .theme-toggle {
            z-index: 50;
            cursor: pointer;
            display: flex;
            flex-direction: row;
            column-gap: 1rem;
            align-items: center;
            justify-content: center;
            float: right;
            position: relative;
            right: 15px;

            #theme-toggle {
                color: hsl(240 100% 87% / 1);

                &:hover {
                    transform: scale(1.11);
                    transition: transform 0.37s ease-in-out;
                }
            }
        }

        .container {
            width: 95% !important;
            margin: auto !important;
            max-width: 1200px;
        }

        .brand-logo {
            position: relative;
            left: 1rem;
            font-weight: bold;
        }

        .blue {
            /* background-color: hsl(258 57% 31% / 1) !important; */
            background-color: hsl(215deg 37% 23%) !important;
            display: flex;
            align-items: center;
            height: 56px !important;
        }

        nav {
            color: #fff;
            background-color: #ee6e73;
            width: 100%;
            height: auto !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

            .nav-wrapper.container {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: -webkit-fill-available !important;
            }
        }

        .get-started {
            padding: 0 1rem;
            color: white;
            transition: all 0.3s ease;

            &:hover {
                color: hsl(59 77% 97%);
            }
        }

        .section-wrapper {
            background-color: ghostwhite;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;

            &:hover {
                transform: translateY(-5px);
            }
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;

            li {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem;
                border-radius: 12px;
                transition: all 0.3s ease;
                background-color: rgba(33, 150, 243, 0.05);
                border: 1px solid transparent;
                padding: 0.5rem 1rem;
                border-left: 1.3px solid #2196F3;

                &:hover {
                    background-color: rgba(33, 150, 243, 0.1);
                    border-color: rgba(33, 150, 243, 0.2);
                    transform: translateX(7px);
                }

                .material-icons {
                    background-color: var(--accent-color);
                    padding: 0.5rem;
                    border-radius: 8px;
                    margin-right: 1rem;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.23);
                    font-size: 2rem;
                }
            }
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background-color: rgb(111 123 210 / 13%);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid transparent;
            border-left: 1.3px solid rgb(111 123 210);

            &:hover {
                background-color: rgb(111 123 210 / 19%);
                border: 1.5px solid rgba(33, 150, 243, 0.2);
                transform: translateX(7px);
            }

            .material-icons {
                background-color: var(--accent-color);
                padding: 0.5rem;
                border-radius: 8px;
                margin-right: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.23);
                font-size: 2rem;
            }
        }

        .cta-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;

            &::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                transition: all 0.5s ease;
            }

            &:hover::before {
                left: 100%;
            }
        }

        blockquote {
            position: relative;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 12px;
            background-color: rgba(33, 150, 243, 0.05);
            font-style: italic;

            &::before {
                content: '\201C';
                font-size: 4rem;
                position: absolute;
                top: -20px;
                left: 10px;
                color: var(--accent-color);
                opacity: 0.2;
            }
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

        .grid-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }


        @media (width < 768px) {
            h1 {
                font-size: 2rem;
            }

            a,
            button {
                padding: 8px 16px;
                font-size: 14px;
            }

            .brand-logo {
                left: 0.5rem;
                position: relative;
            }

            .container {
                width: 100% !important;
                margin: auto !important;
            }

            .section-wrapper {
                padding: 1rem;
            }
        }
    </style>
</head>

<body class="theme-light">
    <header>
        <nav class="blue">
            <div class="nav-wrapper container">
                <a href="/" class="brand-logo">
                    <div class="masked-logo"></div>
                </a>

                <div class="theme-toggle z-50">
                    <a href="{{ route('login') }}" class="get-started">
                        Get Started
                    </a>
                </div>

            </div>
        </nav>
    </header>


    <div class="container mx-auto px-4 py-20">
        <div class="section-wrapper text-center">
            <i class="material-icons text-6xl mb-4 text-blue-500">reviews</i>
            <h1 class="text-4xl font-bold mb-4">Welcome to ReviewSync</h1>
            <p class="text-xl mb-8">
                Transform how you manage your online reviews and build a stronger brand reputation.
            </p>
        </div>

        <div class="section-wrapper">
            <h2 class="text-2xl font-semibold mb-6 flex items-center">
                <i class="material-icons mr-2 text-blue-500">stars</i>
                Why Choose ReviewSync?
            </h2>
            <p class="text-lg mb-8 text-center">
                In today's competitive market, managing your online reputation is critical.
                ReviewSync offers powerful features not available on any other platform.
            </p>
            <ul class="feature-list">
                <li>
                    <i class="material-icons">dashboard</i>
                    <div>
                        <strong>Centralized Review Management</strong>
                        <p class="text-sm">Aggregate and respond to reviews from multiple platforms in one dashboard.
                        </p>
                    </div>
                </li>
                <li>
                    <i class="material-icons">electric_bolt</i>
                    <div>
                        <strong>AI-Powered Insights</strong>
                        <p class="text-sm">Identify patterns and trends in customer feedback using advanced analytics.
                        </p>
                    </div>
                </li>
                <li>
                    <i class="material-icons">notifications_active</i>
                    <div>
                        <strong>Custom Notifications</strong>
                        <p class="text-sm">Get real-time alerts for new reviews and act swiftly.</p>
                    </div>
                </li>
                <li>
                    <i class="material-icons">public</i>
                    <div>
                        <strong>Localized Review Insights</strong>
                        <p class="text-sm">Compare your performance regionally or globally.</p>
                    </div>
                </li>
                <li>
                    <i class="material-icons">chat_bubble_outline</i>
                    <div>
                        <strong>Built-in Reply Templates</strong>
                        <p class="text-sm">Save time with customizable response templates.</p>
                    </div>
                </li>
                <li>
                    <i class="material-icons">group</i>
                    <div>
                        <strong>Multi-User Access</strong>
                        <p class="text-sm">Collaborate with your team by assigning roles securely.</p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Unique Value Proposition -->
        <div class="section-wrapper mb-12">
            <h2 class="text-2xl font-semibold mb-4 flex flex-col items-center">
                <i class="material-icons">different_color</i>
                What Makes Us Different?
            </h2>
            <p class="text-lg mb-6 max-w-2xl mx-auto">
                Unlike other platforms, ReviewSync goes beyond review management.
                Our advanced features are designed to give you the edge:
            </p>
            <div class="grid-wrapper max-w-3xl mx-auto">
                <div class="feature-item">
                    <i class="material-icons">compare_arrows</i>
                    <div>
                        <strong>Competitor Analysis:</strong>
                        Track and compare your reputation with competitors.
                    </div>
                </div>
                <div class="feature-item">
                    <i class="material-icons">sync_alt</i>
                    <div>
                        <strong>Seamless Integration:</strong>
                        Sync with Google My Business, Yelp, Facebook, and more.
                    </div>
                </div>
                <div class="feature-item">
                    <i class="material-icons">palette</i>
                    <div>
                        <strong>Custom Branding:</strong>
                        Maintain a consistent brand voice with fully customizable responses.
                    </div>
                </div>
                <div class="feature-item">
                    <i class="material-icons">file_download</i>
                    <div>
                        <strong>Data Export:</strong>
                        Download detailed reports for presentations and strategy meetings.
                    </div>
                </div>
            </div>
        </div>


        <!-- Call to Action -->
        <div class="section-wrapper text-center">
            <h2 class="text-2xl font-semibold mb-4 flex items-center justify-center">
                <i class="material-icons mr-2 text-blue-500">rocket_launch</i>
                Start Your Journey
            </h2>
            <p class="text-lg mb-6">
                Whether you're a small business or managing multiple locations, ReviewSync has you covered.
            </p>
            <a href="{{ route('login') }}"
                class="cta-button bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-full inline-flex items-center">
                <i class="material-icons mr-2">play_arrow</i>
                Get Started Now
            </a>
        </div>

        <!-- Testimonials -->
        <div class="section-wrapper">
            <h2 class="text-2xl font-semibold mb-6 flex items-center">
                <i class="material-icons mr-2 text-blue-500">format_quote</i>
                What Our Users Say
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <blockquote>
                    "ReviewSync has revolutionized how we handle customer feedback. We've seen a 30% increase in
                    positive reviews!"
                    <span class="block font-bold mt-2 text-right">— Jane Doe, Small Business Owner</span>
                </blockquote>
                <blockquote>
                    "The insights and analytics provided by ReviewSync helped us identify areas for improvement and
                    build a better customer experience."
                    <span class="block font-bold mt-2 text-right">— John Smith, Marketing Manager</span>
                </blockquote>
            </div>
        </div>
    </div>


    <!-- <script>
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const body = document.body;

        // Initialize theme from localStorage
        const savedTheme = localStorage.getItem('theme') || 'theme-light';
        body.classList.add(savedTheme);
        updateThemeIcon();

        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('theme-light')) {
                body.classList.remove('theme-light');
                body.classList.add('theme-dark');
                localStorage.setItem('theme', 'theme-dark');
            } else {
                body.classList.remove('theme-dark');
                body.classList.add('theme-light');
                localStorage.setItem('theme', 'theme-light');
            }
            updateThemeIcon();
        });

        function updateThemeIcon() {
            const isDark = body.classList.contains('theme-dark');
            themeIcon.innerHTML = isDark
                ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />`
                : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m3.343-5.657L5.636 5.636m12.728 12.728L18.364 18.364M12 7a5 5 0 110 10 5 5 0 010-10z" />`;
        }
    </script> -->
</body>

</html>