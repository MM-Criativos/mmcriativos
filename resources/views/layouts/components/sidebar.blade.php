<style>
    /* Modo claro */
    .icon-project.fa-duotone::before,
    .icon-project.fad::before {
        color: rgb(255 136 0);
        /* Camada primária */
    }

    .icon-project.fa-duotone::after,
    .icon-project.fad::after {
        color: rgb(0 0 0);
        /* Camada secundária */
        opacity: 1;
    }

    /* Modo escuro */
    .dark .icon-project.fa-duotone::before,
    .dark .icon-project.fad::before {
        color: rgb(255 136 0);
        /* Mantém o laranja */
    }

    .dark .icon-project.fa-duotone::after,
    .dark .icon-project.fad::after {
        color: rgb(255 255 255);
        /* Cinza escuro no dark mode */
        opacity: 1;
    }
</style>

<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>

    {{-- Logo --}}
    <div class="h-[100px] flex items-center px-6 bg-transparent">
        <a href="{{ route('dashboard') }}" class="sidebar-logo flex items-center">
            <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM logo"
                class="light-logo h-10 bg-transparent border-none shadow-none">
            <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM logo"
                class="dark-logo h-10 bg-transparent border-none shadow-none hidden">
            <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM logo"
                class="logo-icon h-10 bg-transparent border-none shadow-none hidden">
        </a>
    </div>


    {{-- Menu --}}
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fa-duotone fa-solid fa-grip icon-project mr-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">Executivo</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="fa-duotone fa-grid-2 icon-project mr-2"></i>
                    <span>Projetos</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('admin.projects.create') }}">Criar Projeto</a></li>
                    <li><a href="{{ route('admin.projects.progress.index') }}">Em Andamento</a></li>
                    <li><a href="{{ route('admin.projects.completed.index') }}">Concluídos</a></li>
                    <li><a href="{{ route('admin.processes.index') }}">Processos</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ route('admin.tasks.index') }}">
                    <i class="fa-duotone fa-solid fa-list-check icon-project mr-2"></i>
                    <span>Tarefas</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="fa-duotone fa-solid fa-user-tie-hair icon-project mr-2"></i>
                    <span>Clientes</span>
                </a>
                <ul class="sidebar-submenu">
                    {{-- <li><a href="{{ route('admin.projects.index') }}">Resumo</a></li> --}}
                    <li><a href="{{ route('admin.clients.create') }}">Adicionar Clientes</a></li>
                    <li><a href="{{ route('admin.clients.index') }}">Clientes</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Comercial</li>

            <li>
                <a href="{{ route('admin.commercial.budgets.index') }}">
                    <i class="fa-duotone fa-solid fa-message-dollar icon-project mr-2"></i>
                    <span>Orçamentos</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.plans.index') }}">
                    <i class="fa-duotone fa-solid fa-layer-group icon-project mr-2"></i>
                    <span>Planos</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.extras.index') }}">
                    <i class="fa-duotone fa-solid fa-sparkles icon-project mr-2"></i>
                    <span>Serviços Extras</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.email-templates.index') }}">
                    <i class="fa-duotone fa-solid fa-envelope-open-dollar icon-project mr-2"></i>
                    <span>E-mail Templates</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.kpi.index') }}">
                    <i class="fa-duotone fa-solid fa-chart-mixed-up-circle-dollar icon-project mr-2"></i>
                    <span>KPIs</span>
                </a>
            </li>

            {{-- <li>
                <a href="{{ route('admin.commercial.dashboard') }}">
                    <iconify-icon icon="mdi:chart-bar" class="menu-icon"></iconify-icon>
                    <span>Resumo Comercial</span>
                </a>
            </li> --}}

            <li class="sidebar-menu-group-title">Site MM Criativos</li>

            <li>
                <a href="{{ route('admin.layout.index') }}">
                    <i class="fa-duotone fa-solid fa-grid-2 icon-project mr-2"></i>
                    <span>Layout</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.services.index') }}">
                    <i class="fa-duotone fa-solid fa-puzzle icon-project mr-2"></i>
                    <span>Serviços</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.skills.index') }}">
                    <i class="fa-duotone fa-solid fa-brain-circuit icon-project mr-2"></i>
                    <span>Habilidades</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.testimonials.index') }}">
                    <i class="fa-duotone fa-solid fa-comment-lines icon-project mr-2"></i>
                    <span>Depoimentos</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">MM Criativos Cloud</li>

            <li>
                <a href="{{ route('mmcloud.tenants.create') }}">
                    <i class="fa-duotone fa-solid fa-cloud-plus icon-project mr-2"></i>
                    <span>Criar Empresas</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
