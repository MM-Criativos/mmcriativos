<style>
    /* ── Lucide — controles globais do sidebar ─────────────────
       Ajuste as duas variáveis abaixo para mudar todos os ícones
       do sidebar de uma vez.
    ────────────────────────────────────────────────────────── */
    .sidebar {
        --sidebar-icon-size: 20px;
        --sidebar-icon-stroke: 3;
        --sidebar-icon-color: #000000;
    }

    .dark .sidebar {
        --sidebar-icon-color: #ffffff;
    }

    .sidebar [data-lucide] {
        width: var(--sidebar-icon-size);
        height: var(--sidebar-icon-size);
        stroke-width: var(--sidebar-icon-stroke);
        stroke: var(--sidebar-icon-color);
        flex-shrink: 0;
        display: inline-block;
        vertical-align: middle;
        transition: stroke 0.15s ease;
    }

    .sidebar a:hover [data-lucide],
    .sidebar a.active-page [data-lucide] {
        stroke: #ff8800;
    }

    /* ── Sidebar scroll fix ────────────────────────────────────
       Garante que o menu-area ocupe o espaço restante e role
       quando o conteúdo ultrapassa a altura da tela.
    ────────────────────────────────────────────────────────── */
    .sidebar {
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    .sidebar-menu-area {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    /* scrollbar discreta no sidebar */
    .sidebar-menu-area::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-menu-area::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-menu-area::-webkit-scrollbar-thumb {
        background: rgba(255,136,0,0.35);
        border-radius: 4px;
    }
    .sidebar-menu-area::-webkit-scrollbar-thumb:hover {
        background: rgba(255,136,0,0.65);
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
                    <i data-lucide="layout-dashboard" class="mr-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">Executivo</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i data-lucide="folder-kanban" class="mr-2"></i>
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
                    <i data-lucide="list-checks" class="mr-2"></i>
                    <span>Tarefas</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i data-lucide="users" class="mr-2"></i>
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
                    <i data-lucide="receipt" class="mr-2"></i>
                    <span>Orçamentos</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.plans.index') }}">
                    <i data-lucide="layers" class="mr-2"></i>
                    <span>Planos</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.extras.index') }}">
                    <i data-lucide="sparkles" class="mr-2"></i>
                    <span>Serviços Extras</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.email-templates.index') }}">
                    <i data-lucide="mail" class="mr-2"></i>
                    <span>E-mail Templates</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.commercial.kpi.index') }}">
                    <i data-lucide="trending-up" class="mr-2"></i>
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
                    <i data-lucide="layout-template" class="mr-2"></i>
                    <span>Layout</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.services.index') }}">
                    <i data-lucide="puzzle" class="mr-2"></i>
                    <span>Serviços</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.skills.index') }}">
                    <i data-lucide="brain" class="mr-2"></i>
                    <span>Habilidades</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.testimonials.index') }}">
                    <i data-lucide="message-square" class="mr-2"></i>
                    <span>Depoimentos</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">MM Cloud</li>

            <li>
                <a href="{{ route('mmcloud.tenants.create') }}">
                    <i data-lucide="building-2" class="mr-2"></i>
                    <span>Criar empresa</span>
                </a>
            </li>

            <li>
                <a href="{{ route('mmcloud.tenants.index') }}">
                    <i data-lucide="building" class="mr-2"></i>
                    <span>Empresas</span>
                </a>
            </li>

            @if (in_array(Auth::user()->role, ['admin', 'comercial', 'user']))
            <li>
                <a href="{{ route('admin.commercial.prospeccao.index') }}"
                   class="{{ request()->is('admin/mmcloud/prospeccao*') ? 'active-page' : '' }}">
                    <i data-lucide="crosshair" class="mr-2"></i>
                    <span>Prospecção</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) lucide.createIcons();
    });
</script>
