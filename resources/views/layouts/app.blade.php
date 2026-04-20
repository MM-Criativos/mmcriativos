<style>
    /* icon-project — Lucide SVG stroke states */
    svg.icon-project {
        stroke: rgb(255 136 0);
        width: 1em; height: 1em;
        stroke-width: 2;
        flex-shrink: 0;
        transition: stroke 0.2s ease;
    }

    /* btn-mmcriativos hover → icon white */
    .btn-mmcriativos:hover svg.icon-project,
    .dark .btn-mmcriativos:hover svg.icon-project {
        stroke: #ffffff !important;
    }

    /* btn-mmcriativos-active → icon white */
    .btn-mmcriativos-active svg.icon-project,
    .dark .btn-mmcriativos-active svg.icon-project {
        stroke: #ffffff !important;
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

    {{-- Vee Agent Config --}}
    <script>
        window.VEE_AGENT_URL   = '{{ config('vee.agent_url') }}';
        window.VEE_AGENT_TOKEN = '{{ config('vee.agent_token') }}';
    </script>

    {{-- Vee Drawer --}}
    <x-vee-drawer />
</body>

</html>
