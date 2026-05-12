<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MM Criativos' }}</title>

    {{-- Favicons --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicons/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicons/mmfavicon.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicons/mmfavicon.png') }}" />
    <link rel="manifest" href="{{ asset('assets/images/favicons/site.webmanifest') }}" />

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Auth CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    {{-- Vite (Tailwind) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="mm-guest-body">

    <main class="mm-guest-wrapper">
        <div class="mm-guest-container">
            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </main>

    <script>
        if (window.lucide) lucide.createIcons();
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();

            const toggles = document.querySelectorAll('[data-toggle-password]');
            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const targetId = toggle.getAttribute('data-toggle-password');
                    const input = document.getElementById(targetId);
                    if (!input) {
                        return;
                    }

                    const showPassword = input.type === 'password';
                    input.type = showPassword ? 'text' : 'password';
                    toggle.classList.toggle('is-visible', showPassword);
                    toggle.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                    toggle.setAttribute('aria-label', showPassword ? 'Ocultar senha' : 'Mostrar senha');
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
