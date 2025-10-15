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
        const dynamicSlot = modal ? modal.querySelector('#holoDynamic') : null;

        // Partículas (Canvas)
        let holoCtx, holoCanvas, particles = [],
            animating = false,
            cx = 0,
            cy = 0,
            clipRect = { x: 0, y: 0, w: 0, h: 0, r: 16 },
            clipToPanel = false;
            cx = holoCanvas.width / 2;
            cy = holoCanvas.height / 2;

            createParticles(200);
            animating = true;
            renderParticles();

            // Timeline: 1) Convergir com rotação 2) Expandir 3) Revelar conteúdo ao iniciar expansão
            if (holoTL) holoTL.kill();
            holoTL = gsap.timeline();

            // Fade-in das partículas
            gsap.set(particles, {
                alpha: 0
            });
            holoTL.to(particles, {
                duration: 0.6,
                alpha: 1,
                stagger: 0.002,
                ease: "sine.out"
            }, 0);

            // Convergência girando para o centro
            holoTL.to(particles, {
                duration: 1.2,
                radius: 16,
                theta: "+=" + (Math.PI * 4), // ~2 voltas
                stagger: 0.001,
                ease: "power3.inOut"
            }, 0);            // Expans�o at� preencher a tela + in�cio da revela��o do painel
            holoTL.to(particles, {
                duration: 0.9,
                radius: () => Math.hypot(holoCanvas.width, holoCanvas.height),
                alpha: 0,
                ease: "power2.out",
                onStart: () => revealContent(),
                onComplete: () => {
                    animating = false;
                }
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
                onComplete: () => {
                    animating = false;
                }
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
                    if (dynamicSlot) dynamicSlot.innerHTML = '';
                    if (text) text.style.display = '';
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
           🧠 Conteúdos dinâmicos (services/skills/projects)
           ============================== */

        async function loadBladeIntoModal(type, slug, heading) {
            if (!title) return;
            // heading no topo
            title.textContent = heading || slug;
            // placeholder
            if (dynamicSlot) dynamicSlot.innerHTML = '<p style="opacity:.8">Carregando…</p>';
            if (text) text.style.display = 'none';

            try {
                const resp = await fetch(`/modal-content/${type}/${slug}`);
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const html = await resp.text();
                if (dynamicSlot) dynamicSlot.innerHTML = html;
            } catch (err) {
                if (dynamicSlot) dynamicSlot.innerHTML = '<p style="color:#ff6;">Não foi possível carregar o conteúdo.</p>';
            }
        }

        const SERVICE_TITLES = {
            landing: 'Landing Page',
            single: 'Site Single Page',
            multi: 'Site Multipage',
            plataforma: 'Plataforma Básica',
            sistema: 'Sistema Empresarial',
            saas: 'SaaS e Integrações'
        };

        window.openContentModal = function(type, slug, heading) {
            loadBladeIntoModal(type, slug, heading);
            openHoloModal();
        };

        window.openServiceModal = function(service) {
            const heading = SERVICE_TITLES[service] || 'Serviço Digital';
            window.openContentModal('services', service, heading);
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






