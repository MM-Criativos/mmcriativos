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

    {{-- Vee Agent Config + Drawer — somente admin --}}
    @if (auth()->user()?->role === 'admin')
    <script>
        window.VEE_AGENT_URL   = '{{ config('vee.agent_url') }}';
        window.VEE_AGENT_TOKEN = '{{ config('vee.agent_token') }}';
    </script>
    <x-vee-drawer />
    @endif
</body>

</html>
