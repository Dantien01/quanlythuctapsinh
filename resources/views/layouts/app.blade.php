<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Quan trọng cho AJAX nếu phần này của app cũng dùng --}}
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome (nếu cần cho phần này của app) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

        <!-- Styles biên dịch bởi Vite -->
        @vite(['resources/css/app.css'])

        @stack('styles')

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>

        {{-- ============================================================= --}}
        {{-- =================== PHẦN SCRIPTS Ở CUỐI BODY =================== --}}
        {{-- ============================================================= --}}

        {{-- Alpine.js từ CDN (nếu phần này của app dùng Alpine và bạn không import trong app.js) --}}
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        {{-- File JS chính của bạn được biên dịch bởi Vite --}}
        {{-- Bao gồm jQuery (nếu cần), Alpine (nếu bạn import), và các logic JS chung. --}}
        @vite(['resources/js/app.js'])

        {{-- Các scripts được push từ các view con --}}
        @stack('scripts')

    </body>
</html>