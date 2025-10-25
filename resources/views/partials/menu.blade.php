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
            </div>
        </div>
    </nav>

    <!-- Overlay do Menu -->
    <div id="siteOverlayMenu" class="site-menu-overlay" aria-hidden="true">
        <div class="site-menu-panel">
            <button class="glass-btn site-menu-close js-close-menu" aria-label="Fechar menu"><i
                    class="fa-solid fa-xmark"></i></button>
            <ul class="site-menu-list">
                <li><a href="#">Work</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Impact</a></li>
            </ul>
            <div class="site-menu-sub">
                <a href="#">Contact</a>
                <a href="#">Latest</a>
                <a href="#">Careers</a>
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
