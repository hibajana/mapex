<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Marrakech Smart Travel')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sand: #F5EDD6;
            --clay: #C2714F;
            --terracotta: #A0522D;
            --olive: #6B7C4B;
            --midnight: #1A1A2E;
            --gold: #D4A857;
        }
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Cormorant Garamond', serif; }
    </style>
</head>
<body class="bg-[#FAF7F2] text-[#1A1A2E] min-h-screen antialiased">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-[#E8E0D0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#C2714F] to-[#D4A857] flex items-center justify-center">
                        <span class="text-white text-sm font-bold">M</span>
                    </div>
                    <span class="font-display text-xl font-semibold text-[#1A1A2E]">Marrakech<span class="text-[#C2714F]">AI</span></span>
                </a>

                <!-- Nav links -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('planner') }}" class="text-sm font-medium text-gray-600 hover:text-[#C2714F] transition-colors">Planifier</a>
                    <a href="{{ route('history.index') }}" class="text-sm font-medium text-gray-600 hover:text-[#C2714F] transition-colors">Historique</a>
                    <a href="{{ route('profile.show') }}" class="text-sm font-medium text-gray-600 hover:text-[#C2714F] transition-colors">Profil</a>
                </div>

                <!-- User menu -->
                <div class="flex items-center gap-3">
                    @auth
                        <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-[#C2714F] hover:underline">Déconnexion</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Page content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-20 border-t border-[#E8E0D0] py-8 text-center">
        <p class="text-sm text-gray-400 font-display italic">
            Marrakech Smart Eco & Health Travel Assistant · Powered by Gemini AI
        </p>
    </footer>

    @stack('scripts')
</body>
</html>
