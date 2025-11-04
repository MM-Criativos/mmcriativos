<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.theme === 'dark' }" x-init="if (darkMode) document.documentElement.classList.add('dark');
$watch('darkMode', value => {
    localStorage.theme = value ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', value);
});">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MM Criativos | Painel Administrativo</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicons/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicons/mmfavicon.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicons/mmfavicon.png') }}" />
    <link rel="manifest" href="{{ asset('assets/images/favicons/site.webmanifest') }}" />
    <meta name="description" content="Ogency HTML Template For Creative Agency" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
</head>

<body
    class="font-sans antialiased bg-gray-100 dark:bg-dark-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-dark-800 shadow transition-colors duration-300">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <script>
        // Inicializa conforme preferência salva/sistema
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Fallback para botões que usam data-toggle-theme
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-toggle-theme]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.documentElement.classList.toggle('dark');
                    localStorage.theme = document.documentElement.classList.contains('dark') ?
                        'dark' : 'light';
                });
            });
        });
    </script>
</body>

</html>
