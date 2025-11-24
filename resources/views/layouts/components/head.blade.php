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

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">

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
</head>
