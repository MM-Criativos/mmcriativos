<nav x-data="{ open: false }"
    class="bg-white dark:bg-dark-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.layout.index')" :active="request()->is('admin/layout*')">
                        {{ __('Layout') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.content.dashboard')" :active="request()->is('admin/content*') || request()->is('admin/services*') || request()->is('admin/skills*') || request()->is('admin/processes*')">
                        {{ __('Conteúdo') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.projects.index')" :active="request()->is('admin/projects*')">
                        {{ __('Projetos') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.commercial.dashboard')" :active="request()->is('admin/commercial*') || request()->is('admin/clients*') || request()->is('admin/testimonials*')">
                        {{ __('Comercial') }}
                    </x-nav-link>

                    <x-nav-link :href="route('admin.tasks.index')" :active="request()->is('admin/tasks*')">
                        {{ __('Tarefas') }}
                    </x-nav-link>

                    @if (Auth::user()->role === 'admin' && Route::has('admin.team.index'))
                        <x-nav-link :href="route('admin.team.index')" :active="request()->is('admin/team*')">
                            {{ __('Equipe') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings + Dark mode -->
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <!-- Botão Dark Mode -->
                <button @click="darkMode = !darkMode"
                    class="p-2 rounded-full bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 transition flex items-center justify-center"
                    title="Alternar modo escuro">
                    <!-- ☀️ Ícone do modo claro -->
                    <i
                        class="fa-solid fa-sun text-yellow-400 text-lg transition transform scale-100 dark:scale-0 dark:opacity-0"></i>

                    <!-- 🌙 Ícone do modo escuro -->
                    <i
                        class="fa-solid fa-moon text-gray-600 dark:text-gray-300 text-lg absolute transition transform scale-0 opacity-0 dark:scale-100 dark:opacity-100"></i>
                </button>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 dark:text-gray-300 bg-transparent hover:text-orange-500 focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (Route::has('admin.settings.index'))
                            {{-- Novo item: Configurações --}}
                            <x-dropdown-link :href="route('admin.settings.index')">
                                <i class="fa-solid fa-gear mr-2 text-gray-400"></i> {{ __('Configurações') }}
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100 my-1"></div>

                        {{-- Perfil --}}
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fa-regular fa-user mr-2 text-gray-400"></i> {{ __('Perfil') }}
                        </x-dropdown-link>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fa-solid fa-arrow-right-from-bracket mr-2 text-gray-400"></i>
                                {{ __('Sair') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>

            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (mobile) -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden dark:bg-dark-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.services.index')" :active="request()->is('admin/services*')">
                {{ __('Serviços') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.content.dashboard')" :active="request()->is('admin/content*') || request()->is('admin/services*') || request()->is('admin/skills*') || request()->is('admin/processes*')">
                {{ __('Conteúdo') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.projects.index')" :active="request()->is('admin/projects*')">
                {{ __('Projetos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.layout.index')" :active="request()->is('admin/layout*')">
                {{ __('Layout') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.commercial.dashboard')" :active="request()->is('admin/commercial*') || request()->is('admin/clients*') || request()->is('admin/testimonials*')">
                {{ __('Comercial') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.tasks.index')" :active="request()->is('admin/tasks*')">
                {{ __('Tarefas') }}
            </x-responsive-nav-link>
            @if (Auth::user()->role === 'admin' && Route::has('admin.team.index'))
                <x-responsive-nav-link :href="route('admin.team.index')" :active="request()->is('admin/team*')">
                    {{ __('Equipe') }}
                </x-responsive-nav-link>
            @endif
        </div>
    </div>
</nav>
