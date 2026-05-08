<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MM Criativos | Painel Administrativo</title>
    <meta name="description" content="Painel Administrativo MM Criativos">

    {{-- Favicons --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicons/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicons/mmfavicon.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicons/mmfavicon.png') }}" />
    <link rel="manifest" href="{{ asset('assets/images/favicons/site.webmanifest') }}" />

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        referrerpolicy="no-referrer" />

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    {{-- Wowdash Assets --}}
    <link rel="stylesheet" href="{{ asset('admin/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/editor.quill.snow.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/full-calendar.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/prism.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/file-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/lib/audioplayer.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/style.css') }}">

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific styles (ex: mind-elixir, etc.) --}}
    @stack('styles')

    {{-- Layout & component styles --}}
    <style>
        /* icon-project — Lucide SVG stroke states */
        svg.icon-project {
            stroke: rgb(255 136 0);
            width: 1em; height: 1em;
            stroke-width: 2;
            flex-shrink: 0;
            transition: stroke 0.2s ease;
        }
        .btn-mmcriativos:hover svg.icon-project,
        .dark .btn-mmcriativos:hover svg.icon-project { stroke: #ffffff !important; }
        .btn-mmcriativos-active svg.icon-project,
        .dark .btn-mmcriativos-active svg.icon-project { stroke: #ffffff !important; }

        /* NORMAL */
        .btn-mmcriativos {
            background-color: transparent !important;
            border: 2px solid #ff8800 !important;
            color: #000 !important;
            transition: all 0.25s ease-in-out !important;
        }
        /* HOVER */
        .btn-mmcriativos:hover {
            background-image: linear-gradient(to right, #feb365, #ff8800) !important;
            border-color: transparent !important;
            color: #000 !important;
        }
        /* DARK NORMAL */
        .dark .btn-mmcriativos {
            background-color: transparent !important;
            border: 2px solid #ff8800 !important;
            color: #fff !important;
        }
        /* DARK HOVER */
        .dark .btn-mmcriativos:hover {
            background-image: linear-gradient(to right, #feb365, #ff8800) !important;
            border-color: transparent !important;
            color: #fff !important;
        }
    </style>
</head>
