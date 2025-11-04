<header class="main-header main-header-two">
    <style>
        .glass-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .25);
            background: rgba(255, 255, 255, .08);
            color: #fff;
            backdrop-filter: blur(8px) saturate(140%);
            -webkit-backdrop-filter: blur(8px) saturate(140%);
            transition: .2s;
            text-decoration: none;
        }

        .glass-btn:hover {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .35);
            color: #ff8800;
        }

        /* WhatsApp button: só aparece no header fixo (sticky) */
        .glass-whatsapp {
            display: none;
        }

        .stricky-header .glass-whatsapp {
            display: inline-flex;
        }

        .site-menu-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .88);
        }

        .site-menu-overlay.active {
            display: flex;
        }

        .site-menu-panel {
            width: min(920px, 92vw);
            padding: 40px 24px;
        }

        .site-menu-list {
            list-style: none;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .site-menu-list li {
            margin: 14px 0;
        }

        .site-menu-list a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: clamp(28px, 6vw, 72px);
            letter-spacing: .5px;
        }

        .site-menu-sub {
            margin-top: 26px;
            color: #d6d6d6;
            display: flex;
            gap: 26px;
            justify-content: center;
            font-size: 16px;
        }

        .site-menu-close {
            position: fixed;
            top: 18px;
            right: 18px;
        }

        /* Keep the top menu visible and unchanged while hero is pinned */
        body.hero-sticky-lock .main-header {
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
            pointer-events: auto !important;
            position: fixed;
            /* freeze in place over the video */
            top: 0;
            left: 0;
            right: 0;
            z-index: 1005;
            background: transparent;
            /* preserve current look */
        }

        /* Hide sticky clone while hero is pinned to avoid swap */
        body.hero-sticky-lock .stricky-header {
            display: none !important;
        }
    </style>
    <nav class="main-menu">
        <div class="container-fluid">
            <div class="main-menu__logo">
                <a href="/">
                    <img src="assets/images/mmsite.png" width="50" height="50" alt="MM Criativos">
                </a>
            </div>

            <div class="main-menu__right" style="display:flex; align-items:center; gap:10px;">
                <!-- Botão Menu (glass) -->
                <button type="button" class="glass-btn js-open-menu" aria-label="Abrir menu">
                    <i class="fa-solid fa-bars"></i>
                    <span class="d-none d-md-inline">Menu</span>
                </button>

                <!-- WhatsApp (apenas no sticky header) -->
                @php
                    $setting = $setting ?? \App\Models\Setting::query()->first();
                    $rawWa = optional($setting)->whatsapp;
                    $isUrl = is_string($rawWa) && preg_match('/^https?:\/\//i', $rawWa);
                    $waHref = $isUrl ? $rawWa : ($rawWa ? 'https://wa.me/' . preg_replace('/\D+/', '', $rawWa) : null);
                @endphp
                @if ($waHref)
                    <a class="glass-btn glass-whatsapp" href="{{ $waHref }}" target="_blank" rel="noopener"
                        aria-label="Abrir WhatsApp">
                        <i class="fa-brands fa-whatsapp fa-2x"></i>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Overlay do Menu -->
    <div id="siteOverlayMenu" class="site-menu-overlay" aria-hidden="true">
        <div class="site-menu-panel">
            <button class="glass-btn site-menu-close js-close-menu" aria-label="Fechar menu"><i
                    class="fa-solid fa-xmark"></i></button>
            <ul class="site-menu-list">
                <li><a href="{{ route('about') }}">Sobre</a></li>
                <li><a href="{{ route('contact') }}">Contato</a></li>
            </ul>
            <div class="site-menu-sub">
                @php $setting = $setting ?? \App\Models\Setting::query()->first(); @endphp
                @php
                    $socials = [
                        'instagram' => ['icon' => 'fab fa-instagram'],
                        'whatsapp' => ['icon' => 'fab fa-whatsapp'],
                        'linkedin' => ['icon' => 'fab fa-linkedin-in'],
                        'behance' => ['icon' => 'fab fa-behance'],
                        'github' => ['icon' => 'fab fa-github'],
                    ];
                @endphp
                @foreach ($socials as $field => $meta)
                    @php
                        if ($field === 'whatsapp') {
                            $wa = optional($setting)->whatsapp;
                            $url =
                                is_string($wa) && preg_match('/^https?:\/\//i', $wa)
                                    ? $wa
                                    : ($wa
                                        ? 'https://wa.me/' . preg_replace('/\D+/', '', $wa)
                                        : null);
                        } else {
                            $url = optional($setting)->{$field};
                        }
                    @endphp
                    @if (!empty($url))
                        <a href="{{ $url }}" target="_blank" aria-label="{{ ucfirst($field) }}">
                            <i class="{{ $meta['icon'] }}"></i>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <script>
        (function() {
            const overlay = document.getElementById('siteOverlayMenu');

            function open() {
                if (overlay) overlay.classList.add('active');
            }

            function close() {
                if (overlay) overlay.classList.remove('active');
            }
            document.addEventListener('click', function(ev) {
                if (ev.target.closest('.js-open-menu')) {
                    ev.preventDefault();
                    open();
                }
                if (ev.target.closest('.js-close-menu') || (ev.target === overlay)) {
                    ev.preventDefault();
                    close();
                }
            }, true);
            // Esc fecha
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') close();
            });
        })();
    </script>
</header>
