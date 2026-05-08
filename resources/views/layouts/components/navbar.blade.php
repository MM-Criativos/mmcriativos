<style>
    /* Keep navbar hard-attached to viewport top */
    .dashboard-main {
        --dashboard-main-offset: 0px;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    @media (min-width: 1200px) {
        .dashboard-main {
            --dashboard-main-offset: 13.75rem;
        }
    }

    @media (min-width: 1400px) {
        .dashboard-main {
            --dashboard-main-offset: 17.1875rem;
        }
    }

    @media (min-width: 1650px) {
        .dashboard-main {
            --dashboard-main-offset: 19.5rem;
        }
    }

    .dashboard-main.active {
        --dashboard-main-offset: 5.375rem;
    }

    .dashboard-main > .navbar-header {
        position: fixed !important;
        top: 0 !important;
        left: var(--dashboard-main-offset) !important;
        right: 0 !important;
        z-index: 40 !important;
        margin: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        height: 72px !important;
        display: flex !important;
        align-items: center !important;
    }

    .dashboard-main-body {
        padding-top: calc(72px + 0.9375rem) !important;
    }

    @media (min-width: 1400px) {
        .dashboard-main-body {
            padding-top: calc(72px + 1.5rem) !important;
        }
    }

    @media (max-width: 1199.98px) {
        .dashboard-main > .navbar-header {
            left: 0 !important;
        }
    }
    /* icon-project â€” Lucide SVG stroke states for navbar */
    svg.icon-project {
        stroke: rgb(255 136 0);
        width: 1em; height: 1em;
        stroke-width: 2;
        flex-shrink: 0;
        transition: stroke 0.15s ease;
    }

    /* Hover/Active: icon white */
    .navbar-tab-btn:hover svg.icon-project,
    .dark .navbar-tab-btn:hover svg.icon-project,
    .navbar-tab-active svg.icon-project,
    .dark .navbar-tab-active svg.icon-project {
        stroke: #ffffff !important;
    }

    /* --------------------------------------
   NAVBAR TAB BUTTON (botÃµes)
--------------------------------------- */

    .navbar-tab-btn {
        background-color: transparent;
        border: 1px solid #000;
        color: #000;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .dark .navbar-tab-btn {
        border-color: #fff;
        color: #fff;
    }

    /* HOVER â€“ igual para light e dark */
    .navbar-tab-btn:hover,
    .dark .navbar-tab-btn:hover {
        background-color: #ff8800 !important;
        border-color: #ff8800 !important;
        color: #fff !important;
    }

    /* ACTIVE â€“ igual para light e dark */
    .navbar-tab-active,
    .dark .navbar-tab-active {
        background-color: #ff8800 !important;
        border-color: #ff8800 !important;
        color: #fff !important;
    }
</style>

@php
    $currentUser = auth()->user();
    $userPhoto = $currentUser && $currentUser->photo ? asset($currentUser->photo) : asset('assets/images/user.png');
    $userName = $currentUser->name ?? 'User';
    $userRole = $currentUser && $currentUser->role ? ucfirst($currentUser->role) : 'Role';
@endphp

<div class="navbar-header border-b border-[transparent]
     px-6 h-[72px] flex items-center bg-white dark:bg-[#000]">

    <div class="flex items-center justify-between gap-6 w-full">


        <!-- ðŸ”¹ LEFT: TITLE + SIDEBAR BUTTON -->
        <div class="flex items-center gap-4">
            <!-- Sidebar Toggle -->
            <button type="button" id="sidebarToggle"
                class="sidebar-toggle p-2 text-neutral-600 dark:text-neutral-300 hover:text-[#ff8800]
            transition relative w-8 h-8 flex items-center justify-center">

                <!-- Ãcone menu aberto -->
                <i id="iconOpen"
                    data-lucide="menu" class="icon-project text-2xl absolute transition-all duration-300 ease-in-out"></i>

                <!-- Ãcone menu fechado -->
                <i id="iconClosed"
                    data-lucide="menu" class="icon-project text-2xl absolute opacity-0 scale-[0.6] transition-all duration-300 ease-in-out"></i>
            </button>



            <!-- Mobile Toggle -->
            <button type="button"
                class="sidebar-mobile-toggle md:hidden w-10 h-10 flex items-center justify-center rounded-xl
                bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-white shadow-sm">
                <iconify-icon icon="heroicons:bars-3-solid" class="icon text-xl"></iconify-icon>
            </button>

            <!-- Title + Subtitle -->
            <div class="flex flex-col leading-tight min-w-[250px] max-w-[250px]">
                <h1 class="text-3xl font-semibold text-neutral-900 dark:text-white">
                    {{ $title ?? 'Dashboard' }}
                </h1>

                <p class="text-sm text-[#262626] dark:text-[#f5f5f5]">
                    {{ $subTitle ?? "Let's check your update today" }}
                </p>
            </div>

            <!-- ðŸ”¸ NAV TABS -->
            @if (isset($navbarTabs))
                <div class="flex items-center gap-3 mt-2">

                    @foreach ($navbarTabs as $tab)
                        <a href="{{ $tab['route'] }}"
                            class="navbar-tab-btn
                flex items-center justify-center
                {{ $tab['active'] ? 'navbar-tab-active' : '' }}"
                            title="{{ $tab['label'] }}"> {{-- Tooltip natural --}}

                            <i data-lucide="{{ $tab['icon'] }}" class="icon-project" style="width:1.125rem;height:1.125rem;"></i>
                        </a>
                    @endforeach

                </div>
            @endif

        </div>

        <!-- ðŸ”¹ RIGHT: ACTIONS + PROFILE -->
        <div class="flex items-center gap-4">

            {{-- Vee Button â€” somente admin --}}
            @if (auth()->user()?->role === 'admin')
            <button @click="$dispatch('open-vee')"
                class="w-10 h-10 rounded-xl bg-[#ff8800] text-white flex items-center justify-center
                       shadow-sm hover:bg-orange-600 transition" title="Abrir Vee">
                <i data-lucide="bot" style="stroke:#fff;width:1.125rem;height:1.125rem;"></i>
            </button>
            @endif

            <!-- Theme Toggle -->
            <button id="theme-toggle"
                class="w-10 h-10 rounded-xl bg-neutral-200 dark:bg-neutral-700 dark:text-white flex items-center justify-center shadow-sm">
                <span id="theme-toggle-dark-icon" class="hidden">
                    <i data-lucide="sun" class="icon-project"></i>
                </span>
                <span id="theme-toggle-light-icon" class="hidden">
                    <i data-lucide="moon" class="icon-project"></i>
                </span>
            </button>

            <!-- Profile -->
            <div class="relative">
                <button data-dropdown-toggle="dropdownProfile"
                    class="flex items-center gap-3 rounded-full border border-neutral-300 dark:border-neutral-600 px-2 py-1 pr-3
                    bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition">

                    <img src="{{ $userPhoto }}" class="w-10 h-10 rounded-full object-cover"
                        alt="{{ $userName }}">

                    <div class="hidden md:flex flex-col text-left leading-tight">
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white">
                            {{ $userName }}
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ $userRole }}
                        </span>
                    </div>

                    <i data-lucide="chevron-down" class="text-sm text-neutral-500 dark:text-neutral-300"></i>
                </button>

                <!-- Dropdown -->
                <div id="dropdownProfile"
                    class="hidden absolute right-0 mt-2 bg-white dark:bg-neutral-700 shadow-lg rounded-xl p-4 w-56 z-50">

                    <div class="flex items-center gap-3 border-b pb-3 border-neutral-200 dark:border-neutral-600">
                        <img src="{{ $userPhoto }}" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h6 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $userName }}</h6>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $userRole }}</p>
                        </div>
                    </div>

                    <ul class="mt-3 space-y-2">
                        <li>
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 text-neutral-700 dark:text-neutral-200 hover:text-[#ff8800] dark:hover:text-[#ff8800] transition">
                                <i data-lucide="user" class="icon-project"></i> Meu perfil
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settings.index') }}"
                                class="flex items-center gap-3 text-neutral-700 dark:text-neutral-200 hover:text-[#ff8800] dark:hover:text-[#ff8800] transition">
                                <i data-lucide="settings-2" class="icon-project"></i> ConfiguraÃ§Ãµes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.team.index') }}"
                                class="flex items-center gap-3 text-neutral-700 dark:text-neutral-200 hover:text-[#ff8800] dark:hover:text-[#ff8800] transition">
                                <i data-lucide="users" class="icon-project"></i> Equipe
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-3 text-neutral-700 dark:text-neutral-200 hover:text-[#ff8800] dark:hover:text-[#ff8800] transition w-full text-left">
                                    <i data-lucide="arrow-left" class="icon-project"></i>
                                    Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('sidebarToggle');
        const iconOpen = document.getElementById('iconOpen');
        const iconClosed = document.getElementById('iconClosed');

        const body = document.body;

        toggleBtn.addEventListener('click', () => {

            // Toggle da sidebar
            body.classList.toggle('sidebar-collapsed');

            const isCollapsed = body.classList.contains('sidebar-collapsed');

            if (isCollapsed) {
                // ANIMAÃ‡ÃƒO PARA FECHAR
                iconOpen.classList.add("icon-squash");
                iconOpen.classList.remove("icon-stretch");

                iconClosed.classList.add("icon-stretch");
                iconClosed.classList.remove("icon-squash");

            } else {
                // ANIMAÃ‡ÃƒO PARA ABRIR
                iconOpen.classList.add("icon-stretch");
                iconOpen.classList.remove("icon-squash");

                iconClosed.classList.add("icon-squash");
                iconClosed.classList.remove("icon-stretch");
            }
        });
    });
</script>



