<style>
    /* Modo claro */
    .icon-project.fa-duotone::before,
    .icon-project.fad::before {
        color: rgb(255 136 0) !important;
        /* Camada primária */
    }

    .icon-project.fa-duotone::after,
    .icon-project.fad::after {
        color: rgb(0 0 0) !important;
        /* Camada secundária */
        opacity: 1 !important;
    }

    /* Modo escuro */
    .dark .icon-project.fa-duotone::before,
    .dark .icon-project.fad::before {
        color: rgb(255 136 0) !important;
        /* Mantém o laranja */
    }

    .dark .icon-project.fa-duotone::after,
    .dark .icon-project.fad::after {
        color: rgb(255 255 255) !important;
        /* Cinza escuro no dark mode */
        opacity: 1 !important;
    }

    /* HOVER — LIGHT MODE → foreground branco */
    .btn-mmcriativos:hover .icon-project.fa-duotone::before,
    .btn-mmcriativos:hover .icon-project.fad::before {
        color: #ffffff !important;
    }

    /* HOVER — DARK MODE → foreground preto */
    .dark .btn-mmcriativos:hover .icon-project.fa-duotone::before,
    .dark .btn-mmcriativos:hover .icon-project.fad::before {
        color: #000000 !important;
    }

    /* ACTIVE — LIGHT MODE → foreground branco */
    .btn-mmcriativos-active .icon-project.fa-duotone::before,
    .btn-mmcriativos-active .icon-project.fad::before {
        color: #ffffff !important;
    }

    /* ACTIVE — DARK MODE → foreground preto */
    .dark .btn-mmcriativos-active .icon-project.fa-duotone::before,
    .dark .btn-mmcriativos-active .icon-project.fad::before {
        color: #000000 !important;
    }

    /* NORMAL */
    .btn-mmcriativos {
        background-color: transparent !important;
        border: 2px solid #ff8800 !important;
        color: #000 !important;
        transition: all 0.25s ease-in-out !important;
    }

    /* HOVER com DEGRADÊ */
    .btn-mmcriativos:hover {
        background-image: linear-gradient(to right, #feb365, #ff8800) !important;
        border-color: 2px solid transparent !important;
        color: #000 !important;
    }

    /* DARK MODE — NORMAL */
    .dark .btn-mmcriativos {
        background-color: transparent !important;
        border: 2px solid #ff8800 !important;
        color: #fff !important;
    }

    /* DARK MODE — HOVER com DEGRADÊ */
    .dark .btn-mmcriativos:hover {
        background-image: linear-gradient(to right, #feb365, #ff8800) !important;
        border-color: 2px solid transparent !important;
        color: #fff !important;
    }
</style>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.theme === 'dark' }" x-init="if (darkMode) document.documentElement.classList.add('dark');
$watch('darkMode', value => {
    localStorage.theme = value ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', value);
});">

{{-- Head --}}
@include('layouts.components.head')

<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white">
    {{-- Sidebar --}}
    @include('layouts.components.sidebar')

    {{-- Main Content --}}
    <main class="dashboard-main">

        {{-- Navbar --}}
        @include('layouts.components.navbar')

        {{-- Page Content Area --}}
        <div class="dashboard-main-body">

            {{-- Slot Content --}}
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </div>

        {{-- Footer --}}
        @include('layouts.components.footer')
    </main>

    {{-- Scripts --}}
    @include('layouts.components.script')
</body>

</html>
