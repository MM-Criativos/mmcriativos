<!-- CDN GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<script>
    /* ==========================================================
🔷 MM Criativos – Holo Modal System (com partículas animadas)
========================================================== */

    window.addEventListener('DOMContentLoaded', () => {

        // Referências principais
        const modal = document.getElementById('holoModal');
        const content = modal ? modal.querySelector('.holo-content') : null;
        const title = modal ? modal.querySelector('.holo-title') : null;
        const text = modal ? modal.querySelector('.holo-text') : null;

        // Partículas (Canvas)
        let holoCtx, holoCanvas, particles = [],
            animating = false,
            cx = 0,
            cy = 0,
            clipRect = { x: 0, y: 0, w: 0, h: 0, r: 16 };
        let glitchLoop; // controla o glitch constante enquanto aberto
        let holoTL; // timeline principal do efeito

        /* ==============================
           ✨ Sistema de Partículas (Canvas)
           ============================== */

        function createParticles(count = 180) {
            particles = [];
            const colors = ["#ff8800", "#ffaa33", "#ff6600", "#ffb866"];
            const maxR = Math.hypot(clipRect.w, clipRect.h) * 0.5; // baseado no painel
            cx = clipRect.x + clipRect.w / 2;
            cy = clipRect.y + clipRect.h / 2;

            for (let i = 0; i < count; i++) {
                particles.push({
                    radius: Math.random() * maxR,
                    theta: Math.random() * Math.PI * 2,
                    size: 1.5 + Math.random() * 2.5,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    alpha: 0
                });
            }
        }

        function renderParticles() {
            if (!animating) return;
            holoCtx.clearRect(0, 0, holoCanvas.width, holoCanvas.height);

            // Clip na área do painel para a explosão ficar contida
            holoCtx.save();
            // rounded-rect path (com fallback)
            const x = clipRect.x, y = clipRect.y, w = clipRect.w, h = clipRect.h, r = clipRect.r;
            if (typeof holoCtx.roundRect === 'function') {
                holoCtx.beginPath();
                holoCtx.roundRect(x, y, w, h, r);
                holoCtx.clip();
            } else {
                const rr = Math.min(r, w / 2, h / 2);
                holoCtx.beginPath();
                holoCtx.moveTo(x + rr, y);
                holoCtx.lineTo(x + w - rr, y);
                holoCtx.quadraticCurveTo(x + w, y, x + w, y + rr);
                holoCtx.lineTo(x + w, y + h - rr);
                holoCtx.quadraticCurveTo(x + w, y + h, x + w - rr, y + h);
                holoCtx.lineTo(x + rr, y + h);
                holoCtx.quadraticCurveTo(x, y + h, x, y + h - rr);
                holoCtx.lineTo(x, y + rr);
                holoCtx.quadraticCurveTo(x, y, x + rr, y);
                holoCtx.clip();
            }

            particles.forEach(p => {
                // posição polar -> cartesiana (com leve jitter)
                const x = cx + Math.cos(p.theta) * p.radius + (Math.random() - 0.5) * 0.6;
                const y = cy + Math.sin(p.theta) * p.radius + (Math.random() - 0.5) * 0.6;
                p.alpha = Math.min(1, p.alpha + 0.06);

                holoCtx.globalAlpha = p.alpha;
                holoCtx.fillStyle = p.color;
                holoCtx.fillRect(x, y, p.size, p.size);
            });

            holoCtx.restore();
            requestAnimationFrame(renderParticles);
        }

        function startParticleEffect() {
            holoCanvas = document.getElementById("holoParticles");
            holoCtx = holoCanvas.getContext("2d");

            // dimensiona o canvas para o container atual
            holoCanvas.width = holoCanvas.offsetWidth;
            holoCanvas.height = holoCanvas.offsetHeight;

            // calcula o retângulo do painel dentro do canvas (para centralizar/limitar partículas)
            const canvasRect = holoCanvas.getBoundingClientRect();
            const panelRect = content.getBoundingClientRect();
            clipRect = {
                x: panelRect.left - canvasRect.left,
                y: panelRect.top - canvasRect.top,
                w: panelRect.width,
                h: panelRect.height,
                r: 16
            };
            cx = clipRect.x + clipRect.w / 2;
            cy = clipRect.y + clipRect.h / 2;

            createParticles(200);
            animating = true;
            renderParticles();

            // Timeline: 1) Convergir com rotação 2) Expandir 3) Revelar conteúdo ao iniciar expansão
            if (holoTL) holoTL.kill();
            holoTL = gsap.timeline();

            // Fade-in das partículas
            gsap.set(particles, { alpha: 0 });
            holoTL.to(particles, { duration: 0.6, alpha: 1, stagger: 0.002, ease: "sine.out" }, 0);

            // Convergência girando para o centro
            holoTL.to(particles, {
                duration: 1.2,
                radius: 16,
                theta: "+=" + (Math.PI * 4), // ~2 voltas
                stagger: 0.001,
                ease: "power3.inOut"
            }, 0);

            // Expansão (controlada ao limite do painel) + início da revelação do painel
            const halfW = () => clipRect.w / 2;
            const halfH = () => clipRect.h / 2;
            const maxRadiusForTheta = (theta) => {
                const c = Math.abs(Math.cos(theta));
                const s = Math.abs(Math.sin(theta));
                const a = halfW();
                const b = halfH();
                const r1 = c > 1e-6 ? a / c : Infinity;
                const r2 = s > 1e-6 ? b / s : Infinity;
                return Math.min(r1, r2) + 8; // pequena margem
            };

            holoTL.to(particles, {
                duration: 0.9,
                radius: (i, p) => maxRadiusForTheta(p.theta),
                alpha: 0,
                ease: "power2.out",
                onStart: () => revealContent(),
                onComplete: () => { animating = false; }
            });
        }

        function disperseParticles() {
            if (!particles.length) return;
            const maxR = Math.hypot(holoCanvas.width, holoCanvas.height);
            gsap.to(particles, {
                duration: 0.8,
                radius: maxR,
                alpha: 0,
                ease: "power2.in",
                onComplete: () => { animating = false; }
            });
        }

        /* ==============================
           ✨ Abertura holográfica
           ============================== */

        window.openHoloModal = function() {
            if (!modal || !content) return;

            // Garante que o painel esteja invisível antes de mostrar o modal
            gsap.set(content, {
                autoAlpha: 0,
                opacity: 0,
                scale: 0.7,
                filter: "blur(20px)",
                clipPath: "circle(0% at 50% 50%)"
            });

            document.body.classList.add('modal-open');
            modal.style.display = 'flex';
            modal.classList.add('active');

            // Inicia apenas as partículas; o painel aparece no início da expansão
            startParticleEffect();
        };

        // Revela o conteúdo quando as partículas começam a se expandir
        function revealContent() {
            gsap.to(content, {
                duration: 1.0,
                autoAlpha: 1,
                opacity: 1,
                scale: 1,
                filter: "blur(0px)",
                clipPath: "circle(150% at 50% 50%)",
                ease: "power4.out",
                onComplete: () => startGlitchLoop()
            });

            gsap.fromTo(content, {
                boxShadow: "0 0 20px rgba(255,136,0,0.3)"
            }, {
                boxShadow: "0 0 40px rgba(255,136,0,0.85)",
                duration: 0.8,
                repeat: 1,
                yoyo: true,
                ease: "sine.inOut"
            });

            // Glitch curto inicial
            gsap.fromTo(content, {
                x: -2
            }, {
                x: 2,
                repeat: 8,
                yoyo: true,
                duration: 0.05,
                ease: "none"
            });
        }

        /* ==============================
           🔻 Fechamento holográfico
           ============================== */

        window.closeHoloModal = function() {
            if (!modal || !content) return;

            stopGlitchLoop();
            if (holoTL) holoTL.kill();
            disperseParticles();

            gsap.fromTo(content, {
                x: -4
            }, {
                x: 4,
                repeat: 6,
                yoyo: true,
                duration: 0.04
            });

            gsap.to(content, {
                duration: 0.5,
                opacity: 0,
                scale: 0.9,
                filter: "blur(10px)",
                clipPath: "circle(0% at 50% 50%)",
                ease: "power2.in",
                onComplete: () => {
                    modal.style.display = 'none';
                    modal.classList.remove('active');
                    document.body.classList.remove('modal-open');
                }
            });
        };

        /* ==============================
           💥 Glitch contínuo (enquanto aberto)
           ============================== */

        function startGlitchLoop() {
            if (glitchLoop) glitchLoop.kill();
            glitchLoop = gsap.timeline({
                repeat: -1,
                repeatDelay: 3
            });

            glitchLoop
                .to(content, {
                    x: 1,
                    duration: 0.05,
                    ease: "none"
                })
                .to(content, {
                    x: -1,
                    duration: 0.05,
                    ease: "none"
                })
                .to(content, {
                    x: 0,
                    duration: 0.05,
                    ease: "none"
                })
                .to(content, {
                    skewX: 1,
                    duration: 0.08,
                    yoyo: true,
                    repeat: 1,
                    ease: "none"
                })
                .to(content, {
                    opacity: 0.98,
                    duration: 0.1,
                    yoyo: true,
                    repeat: 1,
                    ease: "none"
                });
        }

        function stopGlitchLoop() {
            if (glitchLoop) glitchLoop.kill();
        }

        /* ==============================
           🧠 Modais de Serviço (dinâmicos)
           ============================== */

        window.openServiceModal = function(service) {
            if (!title || !text) return;

            switch (service) {
                case 'landing':
                    title.innerHTML = "Landing Page";
                    text.innerHTML = `
                    <strong>Feita para conversão</strong> — ideal para campanhas, produtos e lançamentos.
                    <br><br>
                    Criamos páginas únicas, com foco em impacto visual e desempenho de conversão.
                    Design fluido, chamadas estratégicas e integração com automações e rastreamento.
                `;
                    break;

                case 'single':
                    title.innerHTML = "Site Single Page";
                    text.innerHTML = `
                    Estrutura enxuta, carregamento rápido e design elegante.
                    <br><br>
                    Ideal para empresas, profissionais e produtos que precisam apresentar tudo em uma só página,
                    com navegação fluida e identidade forte.
                `;
                    break;

                case 'multi':
                    title.innerHTML = "Site Multipage";
                    text.innerHTML = `
                    Para quem precisa de profundidade e organização.
                    <br><br>
                    Desenvolvemos sites com múltiplas páginas interligadas, focando em arquitetura da informação,
                    SEO e performance.
                `;
                    break;

                case 'plataforma':
                    title.innerHTML = "Plataforma Básica";
                    text.innerHTML = `
                    Um ambiente digital personalizado, com login, painel e recursos sob medida.
                    <br><br>
                    Ideal para portais, áreas restritas e negócios que precisam evoluir de um site
                    para um ecossistema completo.
                `;
                    break;

                case 'sistema':
                    title.innerHTML = "Sistema Empresarial";
                    text.innerHTML = `
                    Soluções corporativas robustas, escaláveis e integradas.
                    <br><br>
                    Criamos sistemas administrativos que otimizam processos e conectam departamentos.
                `;
                    break;

                case 'saas':
                    title.innerHTML = "SaaS e Integrações";
                    text.innerHTML = `
                    Desenvolvimento de plataformas SaaS completas e automatizadas.
                    <br><br>
                    Infraestrutura multitenant, autenticação segura, integrações com APIs e serviços em nuvem.
                    A base da nova geração de produtos digitais.
                `;
                    break;

                default:
                    title.innerHTML = "Serviço Digital";
                    text.innerHTML = `
                    Soluções personalizadas criadas sob medida pela <strong>MM Criativos</strong>.
                    <br><br>
                    Design, código e automação unidos em uma experiência digital completa.
                `;
            }

            openHoloModal();
        };

    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const carousels = document.querySelectorAll('.ogency-owl__carousel');

        carousels.forEach(function(carousel) {
            $(carousel).on('mouseenter', function() {
                $(this).trigger('stop.owl.autoplay');
            });

            $(carousel).on('mouseleave', function() {
                // Reinicia o autoplay do zero, com novo timeout
                $(this).trigger('play.owl.autoplay', [
                    3000
                ]); // 3000ms = 3s (ajuste conforme quiser)
            });
        });
    });
</script>
