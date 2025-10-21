<!-- CDN GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<script>
    // MM Criativos — Holo Modal System (full-screen overlay + dynamic content)
    window.addEventListener('DOMContentLoaded', () => {
        // Refs
        const modal = document.getElementById('holoModal');
        const content = modal ? modal.querySelector('.holo-content') : null;
        const title = modal ? modal.querySelector('.holo-title') : null;
        const text = modal ? modal.querySelector('.holo-text') : null;
        const dynamicSlot = modal ? modal.querySelector('#holoDynamic') : null;
        const holoSelect = modal ? modal.querySelector('#holoSelect') : null;

        // Particles (Canvas)
        let holoCtx, holoCanvas, particles = [],
            animating = false,
            cx = 0,
            cy = 0;
        let glitchLoop, holoTL;

        function createParticles(count = 260) {
            particles = [];
            const colors = ['#ff8800', '#ffaa33', '#ff6600', '#ffb866'];
            const maxR = Math.hypot(holoCanvas.width, holoCanvas.height) * 0.6;
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
            particles.forEach(p => {
                const x = cx + Math.cos(p.theta) * p.radius + (Math.random() - 0.5) * 0.6;
                const y = cy + Math.sin(p.theta) * p.radius + (Math.random() - 0.5) * 0.6;
                p.alpha = Math.min(1, p.alpha + 0.06);
                holoCtx.globalAlpha = p.alpha;
                holoCtx.fillStyle = p.color;
                holoCtx.fillRect(x, y, p.size, p.size);
            });
            requestAnimationFrame(renderParticles);
        }

        function startParticleEffect() {
            holoCanvas = document.getElementById('holoParticles');
            holoCtx = holoCanvas.getContext('2d');
            holoCanvas.width = holoCanvas.offsetWidth;
            holoCanvas.height = holoCanvas.offsetHeight;
            cx = holoCanvas.width / 2;
            cy = holoCanvas.height / 2;

            createParticles(260);
            animating = true;
            renderParticles();

            if (holoTL) holoTL.kill();
            holoTL = gsap.timeline();
            gsap.set(particles, {
                alpha: 0
            });
            holoTL.to(particles, {
                duration: 0.6,
                alpha: 1,
                stagger: 0.002,
                ease: 'sine.out'
            }, 0);
            holoTL.to(particles, {
                duration: 1.2,
                radius: 16,
                theta: '+=' + (Math.PI * 4),
                stagger: 0.001,
                ease: 'power3.inOut'
            }, 0);
            holoTL.to(particles, {
                duration: 0.9,
                radius: () => Math.hypot(holoCanvas.width, holoCanvas.height),
                alpha: 0,
                ease: 'power2.out',
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
                ease: 'power2.in',
                onComplete: () => {
                    animating = false;
                }
            });
        }

        // Open modal
        window.openHoloModal = function() {
            if (!modal || !content) return;
            gsap.set(content, {
                autoAlpha: 0,
                opacity: 0,
                scale: 0.7,
                filter: 'blur(20px)',
                clipPath: 'circle(0% at 50% 50%)'
            });
            document.body.classList.add('modal-open');
            modal.style.display = 'flex';
            modal.classList.add('active');
            startParticleEffect();
        };

        function revealContent() {
            gsap.to(content, {
                duration: 1.0,
                autoAlpha: 1,
                opacity: 1,
                scale: 1,
                filter: 'blur(0px)',
                clipPath: 'circle(150% at 50% 50%)',
                ease: 'power4.out',
                onComplete: () => startGlitchLoop()
            });
            gsap.fromTo(content, {
                boxShadow: '0 0 20px rgba(255,136,0,0.3)'
            }, {
                boxShadow: '0 0 40px rgba(255,136,0,0.85)',
                duration: 0.8,
                repeat: 1,
                yoyo: true,
                ease: 'sine.inOut'
            });
            gsap.fromTo(content, {
                x: -2
            }, {
                x: 2,
                repeat: 8,
                yoyo: true,
                duration: 0.05,
                ease: 'none'
            });
        }

        // Close modal
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
                filter: 'blur(10px)',
                clipPath: 'circle(0% at 50% 50%)',
                ease: 'power2.in',
                onComplete: () => {
                    modal.style.display = 'none';
                    modal.classList.remove('active');
                    document.body.classList.remove('modal-open');
                    if (dynamicSlot) dynamicSlot.innerHTML = '';
                    if (text) text.style.display = '';
                }
            });
        };

        // Glitch loop
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
                    ease: 'none'
                })
                .to(content, {
                    x: -1,
                    duration: 0.05,
                    ease: 'none'
                })
                .to(content, {
                    x: 0,
                    duration: 0.05,
                    ease: 'none'
                })
                .to(content, {
                    skewX: 1,
                    duration: 0.08,
                    yoyo: true,
                    repeat: 1,
                    ease: 'none'
                })
                .to(content, {
                    opacity: 0.98,
                    duration: 0.1,
                    yoyo: true,
                    repeat: 1,
                    ease: 'none'
                });
        }

        function stopGlitchLoop() {
            if (glitchLoop) glitchLoop.kill();
        }

        // Dynamic content
        async function loadBladeIntoModal(type, slug, heading) {
            if (!title) return;
            title.textContent = heading || slug;
            if (dynamicSlot) dynamicSlot.innerHTML = '<p style="opacity:.8">Carregando…</p>';
            if (text) text.style.display = 'none';
            try {
                const resp = await fetch(`/modal-content/${type}/${slug}`);
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const html = await resp.text();
                if (dynamicSlot) {
                    dynamicSlot.innerHTML = html;
                    initDynamicEffects(dynamicSlot);
                }
            } catch (err) {
                if (dynamicSlot) dynamicSlot.innerHTML =
                    '<p style="color:#ff6;">Não foi possível carregar o conteúdo.</p>';
            }
        }

        const SERVICE_TITLES = {
            landing: 'Landing Page',
            single: 'Site Single Page',
            multi: 'Site Multipage',
            portal: 'Portal',
            sistema: 'Sistema Empresarial',
            saas: 'SaaS e Integrações'
        };
        const SKILL_TITLES = {
            frontend: 'Layout e Interface',
            backend: 'Estrutura e Lógica',
            uxui: 'Experiência do Usuário',
            seo: 'SEO e Desempenho',
            automacao: 'Automação e Integração'
        };
        // Se quiser exibir opções para projetos no select, adicione aqui
        // const PROJECT_TITLES = { project: 'Projeto' };
        let currentType = 'services';
        let currentSlug = '';

        // Initialize Owl Carousel inside a container
        function initDynamicCarousels(root) {
            const $root = $(root || document);
            const $carousels = $root.find('.ogency-owl__carousel');
            if (!$carousels.length) return;
            $carousels.each(function() {
                const $elm = $(this);
                if ($elm.hasClass('owl-loaded')) {
                    try {
                        $elm.trigger('destroy.owl.carousel');
                    } catch (e) {}
                }
                let options = $elm.data('owl-options');
                try {
                    options = (typeof options === 'object') ? options : JSON.parse(options);
                } catch (e) {
                    options = {};
                }
                $elm.owlCarousel(options);
            });
        }

        // Attach pause/resume hover handlers for carousels inside a container
        function attachCarouselHover(root) {
            const $root = $(root || document);
            $root.find('.ogency-owl__carousel')
                .off('mouseenter.mm pause mouseleave.mm resume')
                .on('mouseenter.mm', function() {
                    $(this).trigger('stop.owl.autoplay');
                })
                .on('mouseleave.mm', function() {
                    $(this).trigger('play.owl.autoplay', [3000]);
                });
        }

        // WOW + Owl + Curved Circle + Video popup for dynamically injected fragments
        function initDynamicEffects(root) {
            // 1) WOW.js – reinit usando o container do modal
            if (window.WOW) {
                try {
                    new WOW({
                        live: false,
                        scrollContainer: '.holo-body'
                    }).init();
                } catch (e) {}
            } else {
                (root.querySelectorAll ? root.querySelectorAll('.wow') : []).forEach(el => {
                    el.style.visibility = 'visible';
                });
            }
            // 2) Owl dentro do fragmento
            initDynamicCarousels(root);
            attachCarouselHover(root);

            // 3) Curved circle text (CircleType) re-init inside the fragment
            try {
                const $root = window.jQuery ? window.jQuery(root) : null;
                if ($root && $.fn && $.fn.circleType) {
                    const safeInit = (selector, options) => {
                        $root.find(selector).each(function() {
                            const $el = $(this);
                            if ($el.data('ct-init')) return; // avoid double init
                            $el.circleType(options);
                            $el.data('ct-init', true);
                        });
                    };
                    safeInit('.curved-circle--item', { radius: 70, forceHeight: true, forceWidth: true });
                    safeInit('.curved-circle-item', { radius: 90, forceHeight: true, forceWidth: true });
                    safeInit('.curved-circle---item', { radius: 75, forceHeight: true, forceWidth: true });
                }
            } catch (e) {}

            // 4) Video popup (magnific) for dynamically injected links
            try {
                const $root = window.jQuery ? window.jQuery(root) : null;
                if ($root && $.fn && $.fn.magnificPopup) {
                    $root.find('.video-popup').magnificPopup({
                        type: 'iframe',
                        mainClass: 'mfp-fade',
                        removalDelay: 160,
                        preloader: true,
                        fixedContentPos: false
                    });
                }
            } catch (e) {}

            // 5) Process Modal inside dynamic content (modal dentro do holo-modal)
            try {
                const processModal = root.querySelector('#processModal');
                if (processModal && !processModal.dataset.bound) {
                    const overlay = processModal.querySelector('.process-modal__overlay');
                    const closeBtn = processModal.querySelector('.process-modal__close');
                    const titleEl = processModal.querySelector('#processModalTitle');
                    const etapaEl = processModal.querySelector('#processModalEtapa');
                    const descEl = processModal.querySelector('#processModalDescricao');
                    
                    const projectEl = processModal.querySelector('#processModalProject');

                    // Open handlers scoped to the same root
                    const triggers = root.querySelectorAll('.open-process-modal');
                    triggers.forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const categoria = e.currentTarget.getAttribute('data-category') || '';
                            const src = 'assets/images/feature/feature-1.jpg';
                            if (titleEl) titleEl.textContent = (categoria === 'wireframes') ? 'Wireframe da Home' : 'Detalhes do Processo';
                            if (etapaEl) etapaEl.textContent = 'Etapa: ' + categoria;
                            if (descEl) descEl.textContent = 'Descrição da etapa ' + categoria;
                            
                            if (projectEl) { try { const ht = document.querySelector('#holoModal .holo-title'); projectEl.textContent = ht ? ht.textContent : 'Projeto'; } catch(_) { projectEl.textContent = 'Projeto'; } }

                            // Preenche carrossel com imagens
                            const $carousel = window.jQuery ? window.jQuery('#processModalCarousel') : null;
                            if ($carousel) {
                                try { $carousel.trigger('destroy.owl.carousel'); } catch (err) {}
                                $carousel.removeClass('owl-loaded');
                                $carousel.html('');
                                const imgs = Array(5).fill(src);
                                $carousel.html(imgs.map(s => `<div class="item"><img src="${s}" alt="${categoria}"></div>`).join(''));
                                $carousel.owlCarousel({
                                    items: 3,
                                    margin: 12,
                                    loop: true,
                                    dots: true,
                                    nav: false,
                                    responsive: { 0: { items: 1 }, 600: { items: 2 }, 992: { items: 3 } }
                                });
                            }

                            // Delegação para trocar destaque/descrição a partir do carrossel
                            try {
                                const $c = window.jQuery ? window.jQuery('#processModalCarousel') : null;
                                if ($c) {
                                    const slides = Array(5).fill(0).map((_, i) => ({
                                        src: src,
                                        title: i === 0 ? ((categoria === 'wireframes') ? 'Wireframe da Home' : 'Detalhes do Processo') : `Etapa ${categoria} - ${i + 1}`,
                                        desc: `Descri��o da etapa ${categoria} - ${i + 1}`
                                    }));
                                    // Atribui índices reais aos itens (considerando clones do Owl)
                                    $c.find('.item').each(function(i){ window.jQuery(this).attr('data-idx', i % slides.length); });
                                    $c.data('slides', slides);
                                    $c.off('click.mm').on('click.mm', '.item img', function(){
                                        const $it = window.jQuery(this).closest('.item');
                                        const idx = parseInt($it.attr('data-idx')) || 0;
                                        const data = $c.data('slides') || [];
                                        const sl = data[idx] || { src };
                                        if (imgEl) imgEl.src = sl.src;
                                        if (titleEl && sl.title) titleEl.textContent = sl.title;
                                        if (descEl && sl.desc) descEl.textContent = sl.desc;
                                    });
                                }
                            } catch (e) {}

                            // Mostra como modal centralizado (flex); não altera o body/holo-modal
                            processModal.style.display = 'flex';
                            processModal.style.display = 'flex';
                            const scroller = document.querySelector('#holoModal .holo-body');
                            if (scroller) scroller.classList.add('submodal-open');
                        }, { passive: true });
                    });

                    
                    // Fechamento do submodal
                    const closeModal = () => {
                        processModal.style.display = 'none';
                        const scroller2 = document.querySelector('#holoModal .holo-body');
                        if (scroller2) scroller2.classList.remove('submodal-open');
                    };
                    const scroller2 = document.querySelector('#holoModal .holo-body');
                    if (scroller2) scroller2.classList.remove('submodal-open');
                    if (overlay) overlay.addEventListener('click', closeModal, { passive: true });
                    if (closeBtn) closeBtn.addEventListener('click', closeModal, { passive: true });

                    processModal.dataset.bound = '1';
                }
            } catch (e) {}
        }

        function populateSelect(type, slug) {
            if (!holoSelect) return;
            const maps = {
                services: SERVICE_TITLES,
                skills: SKILL_TITLES
                // ,projects: PROJECT_TITLES
            };
            const map = maps[type];
            if (!map) {
                holoSelect.style.display = 'none';
                return;
            }
            holoSelect.style.display = '';
            holoSelect.innerHTML = Object.entries(map).map(([value, label]) =>
                `<option value="${value}">${label}</option>`).join('');
            if (slug) holoSelect.value = slug;
        }
        if (holoSelect) {
            holoSelect.addEventListener('change', (e) => {
                const nextSlug = e.target.value;
                currentSlug = nextSlug;
                const heading = SERVICE_TITLES[nextSlug] || nextSlug;
                loadBladeIntoModal(currentType, nextSlug, heading);
            });
        }

        window.openContentModal = function(type, slug, heading) {
            currentType = type;
            currentSlug = slug;
            populateSelect(type, slug);
            loadBladeIntoModal(type, slug, heading);
            openHoloModal();
        };

        window.openServiceModal = function(service) {
            const heading = SERVICE_TITLES[service] || 'Serviço Digital';
            window.openContentModal('services', service, heading);
        };
        window.openSkillModal = function(skill) {
            const heading = SKILL_TITLES[skill] || 'Habilidade';
            window.openContentModal('skills', skill, heading);
        };
        window.openProjectModal = function(slug, heading) {
            const safeSlug = slug || 'project';
            const safeHeading = heading || 'Projeto';
            window.openContentModal('projects', safeSlug, safeHeading);
        };
    });
</script>

<script>
    // Pause/resume carousels on hover
    document.addEventListener('DOMContentLoaded', function() {
        const carousels = document.querySelectorAll('.ogency-owl__carousel');
        carousels.forEach(function(carousel) {
            $(carousel).on('mouseenter', function() {
                $(this).trigger('stop.owl.autoplay');
            });
            $(carousel).on('mouseleave', function() {
                $(this).trigger('play.owl.autoplay', [3000]);
            });
        });
    });
</script>



