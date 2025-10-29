<style>
    /* =========================================
🔶 BASE DA SEÇÃO - CAMPO HOLOGRÁFICO GLOBAL
========================================= */
    .project-two {
        position: relative;
        overflow: hidden;
    }

    #holo-field {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        mix-blend-mode: screen;
        pointer-events: none;
        filter: blur(1.2px);
    }

    /* =========================================
🔶 BASE DO CARD HOLOGRÁFICO - MMCriativos
========================================= */
    .project-portal {
        position: relative;
        border-radius: 16px;
        overflow: visible;
        --accent: #ff8800;
        transition: all 0.4s ease;
        cursor: pointer;
        perspective: 1000px;
        height: 350px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        z-index: 2;
    }

    .project-two__item__holo {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 16px;
        transform-style: preserve-3d;
        will-change: transform;
        display: flex;
        justify-content: center;
        align-items: center;
        transform: translateY(-15px);
    }

    /* =========================================
💫 CAMADA — LOGO HOLOGRÁFICO (Three.js)
========================================= */
    .portal-interface {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        transform: translate(-50%, -50%);
        opacity: 1;
        z-index: 3;
        pointer-events: none;
        transition: opacity 0.4s ease, filter 0.4s ease;
        overflow: visible;
    }

    .portal-interface img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: none !important;
    }

    .portal-interface canvas.logo-points {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) !important;
        width: 400px !important;
        height: 400px !important;
        z-index: 4;
        pointer-events: none;
    }

    /* =========================================
💬 LOGO REAL
========================================= */
    .portal-logo {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        z-index: 5;
        transition: opacity 0.5s ease;
        pointer-events: none;
    }

    .portal-logo img {
        width: 230px;
        height: 230px;
        object-fit: contain;
    }

    /* =========================================
⚡ HOVER: transição entre holograma e logo
========================================= */
    .project-two__item__holo:hover .portal-interface {
        opacity: 0;
        visibility: hidden;
    }

    .project-two__item__holo:hover .portal-logo {
        opacity: 1;
        visibility: visible;
    }

    .project-two__item__holo:hover .portal-logo img {
        animation: holographicIn 0.6s ease forwards;
    }

    @keyframes holographicIn {
        0% {
            opacity: 0;
            filter: blur(8px) brightness(1.4);
            transform: scale(0.9);
        }

        100% {
            opacity: 1;
            filter: blur(0) brightness(1);
            transform: scale(1);
        }
    }

    /* =========================================
🧠 LEGENDA / TEXTO INFERIOR
========================================= */
    .project-two__item__content {
        position: absolute;
        bottom: 3px;
        text-align: center;
        z-index: 6;
        width: 100%;
        color: #fff;
        text-shadow: 0 0 10px rgba(255, 136, 0, 0.3);
    }

    .project-two__item__content__cats {
        margin-bottom: 3px;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .project-two__item__content__title span {
        display: inline-block;
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        line-height: 1.3;
    }

    #holo-field {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        mix-blend-mode: screen;
    }
</style>

<section class="project-two">
    <!-- 🌌 Campo holográfico global -->
    <canvas id="holo-field"></canvas>

    <div class="container">
        <div class="section-title text-center">
            <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
            <h2 class="section-title__title">
                Conheça alguns dos trabalhos<br>
                que deram vida a grandes ideias
            </h2>
        </div>

        <div class="project-two__carousel ogency-owl__carousel owl-theme owl-carousel"
            data-owl-options='{
                "items": 3,
                "margin": 30,
                "smartSpeed": 1200,
                "loop": true,
                "autoplay": true,
                "nav": false,
                "dots": false,
                "responsive": {
                    "0": {"items": 1},
                    "768": {"items": 2},
                    "992": {"items": 3},
                    "1200": {"items": 3}
                }
            }'>

            @php
                $projects = \App\Models\Project::with(['client', 'service'])
                    ->orderByDesc('finished_at')
                    ->orderBy('name')
                    ->get();
            @endphp

            @foreach ($projects as $project)
                <div class="project-two__item project-portal cursor-pointer" data-slug="{{ $project->slug }}"
                    data-name="{{ addslashes($project->name) }}">
                    <div class="project-two__item__holo">
                        <!-- 💫 Camada 2: Holograma principal -->
                        <div class="portal-interface">
                            <img src="{{ asset($project->thumb ?: $project->cover ?: 'assets/images/project/project-2-1.jpg') }}"
                                alt="{{ $project->name }}" class="project-thumb" loading="lazy" decoding="async">
                        </div>

                        <!-- 💬 Logo real -->
                        @php
                            $logo = $project->client
                                ? $project->client->logo_url
                                : asset('assets/images/logos/default-logo.png');
                        @endphp
                        <div class="portal-logo">
                            <img src="{{ $logo }}" alt="{{ optional($project->client)->name ?? 'Cliente' }}"
                                class="client-logo" loading="lazy" decoding="async">
                        </div>
                    </div>

                    <!-- 🧠 Legenda -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats">
                            @if ($project->service)
                                <span>{{ $project->service->name }}</span>
                            @endif
                            @if ($project->client)
                                <span>, {{ $project->client->name }}</span>
                            @endif
                        </p>
                        <h3 class="project-two__item__content__title">
                            <span>{{ $project->name }}</span>
                        </h3>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <canvas id="holo-field"></canvas>
</section>


<script>
    (function() {
        // utilitário para carregar libs externas
        function loadScript(src) {
            return new Promise((res, rej) => {
                const s = document.createElement('script');
                s.src = src;
                s.onload = res;
                s.onerror = rej;
                document.head.appendChild(s);
            });
        }

        // campo global de partículas/luz holográfica
        function createGlobalHoloField() {
            const canvas = document.getElementById('holo-field');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const particles = [];
            const numParticles = 300;
            let w, h;
            let mouseX = 0,
                mouseY = 0;

            function resize() {
                const rect = canvas.getBoundingClientRect();
                w = rect.width;
                h = rect.height;
                canvas.width = w;
                canvas.height = h;
                particles.length = 0;

                for (let i = 0; i < numParticles; i++) {
                    particles.push({
                        x: Math.random() * w,
                        y: Math.random() * h,
                        z: Math.random() * 400 - 200, // profundidade Z
                        r: Math.random() * 2 + 0.5,
                        a: Math.random() * Math.PI * 2,
                        v: 0.25 + Math.random() * 0.35,
                        alpha: Math.random() * 0.4 + 0.3
                    });
                }
            }

            function animate() {
                ctx.clearRect(0, 0, w, h);

                // 🔶 gradiente base tipo “faixa energética”
                const grad = ctx.createRadialGradient(w / 2, h, h / 4, w / 2, h, h);
                grad.addColorStop(0, 'rgba(255,136,0,0.25)');
                grad.addColorStop(0.5, 'rgba(255,136,0,0.1)');
                grad.addColorStop(1, 'rgba(0,0,0,0)');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, w, h);

                // ✨ partículas com eixo Z e parallax
                for (const p of particles) {
                    p.x += Math.cos(p.a) * p.v;
                    p.y += Math.sin(p.a) * p.v * 0.6;
                    p.z += Math.sin(p.a * 0.5) * 0.8;
                    p.a += (Math.random() - 0.5) * 0.05;

                    // reaparece se sair
                    if (p.x < -100) p.x = w + 100;
                    if (p.x > w + 100) p.x = -100;
                    if (p.y < -100) p.y = h + 100;
                    if (p.y > h + 100) p.y = -100;

                    // efeito de profundidade (quanto mais distante, menor)
                    const scale = 1 - p.z / 400;
                    const px = (p.x - w / 2) * scale + w / 2 + (mouseX - w / 2) * 0.05;
                    const py = (p.y - h / 2) * scale + h / 2 + (mouseY - h / 2) * 0.03;

                    ctx.beginPath();
                    ctx.fillStyle = `rgba(255,136,0,${p.alpha * scale})`;
                    ctx.arc(px, py, p.r * scale * 1.2, 0, Math.PI * 2);
                    ctx.fill();
                }

                requestAnimationFrame(animate);
            }

            window.addEventListener('mousemove', e => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });

            window.addEventListener('resize', resize);
            resize();
            animate();
        }


        // inicializador dos hologramas por card
        function initHoloCards() {
            if (!window.gsap) return;

            function ensureThree() {
                return new Promise((resolve, reject) => {
                    if (window.THREE) return resolve();
                    loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js')
                        .then(() => window.THREE ? resolve() : reject())
                        .catch(reject);
                });
            }

            function makeDiscTexture() {
                const c = document.createElement('canvas');
                c.width = c.height = 32;
                const g = c.getContext('2d');
                const grd = g.createRadialGradient(16, 16, 0, 16, 16, 16);
                grd.addColorStop(0, 'rgba(255,255,255,1)');
                grd.addColorStop(0.5, 'rgba(255,255,255,0.8)');
                grd.addColorStop(1, 'rgba(255,255,255,0)');
                g.fillStyle = grd;
                g.fillRect(0, 0, 32, 32);
                return c;
            }

            // 💫 holograma individual
            function buildLogoHologramThree(card, wrap, img) {
                if (wrap.querySelector('canvas.logo-points')) return;

                const accent = (getComputedStyle(card).getPropertyValue('--accent') || '#ff8800').trim();

                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 1000);
                camera.position.z = 370;

                const renderer = new THREE.WebGLRenderer({
                    alpha: true,
                    antialias: true
                });
                renderer.setClearColor(0x000000, 0);
                renderer.setSize(230, 230);
                renderer.domElement.className = 'logo-points';
                Object.assign(renderer.domElement.style, {
                    position: 'absolute',
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%, -50%)',
                    width: '100%',
                    height: '100%',
                    zIndex: 5,
                    mixBlendMode: 'screen',
                    pointerEvents: 'none',
                    transition: 'opacity 0.4s ease'
                });
                wrap.appendChild(renderer.domElement);

                if (img) img.style.display = 'none';

                // 🧠 Pré-processamento do logo em partículas
                const off = document.createElement('canvas');
                const targetSize = 300;
                const aspect = img.naturalWidth / img.naturalHeight;
                if (aspect >= 1) {
                    off.width = targetSize;
                    off.height = targetSize / aspect;
                } else {
                    off.width = targetSize * aspect;
                    off.height = targetSize;
                }

                const ctx = off.getContext('2d', {
                    willReadFrequently: true
                });
                ctx.drawImage(img, 0, 0, off.width, off.height);
                const data = ctx.getImageData(0, 0, off.width, off.height).data;

                const pts = [],
                    phases = [];
                const step = 2;
                for (let y = 0; y < off.height; y += step) {
                    for (let x = 0; x < off.width; x += step) {
                        const a = data[(y * off.width + x) * 4 + 3];
                        if (a > 40) {
                            pts.push(x - off.width / 2, off.height / 2 - y, (Math.random() - 0.5) * 6);
                            phases.push(Math.random() * Math.PI * 2);
                        }
                    }
                }

                const geometry = new THREE.BufferGeometry();
                geometry.setAttribute('position', new THREE.Float32BufferAttribute(new Float32Array(pts), 3));

                const texture = new THREE.CanvasTexture(makeDiscTexture());
                const material = new THREE.PointsMaterial({
                    size: 3.4,
                    map: texture,
                    transparent: true,
                    blending: THREE.AdditiveBlending,
                    depthWrite: false,
                    color: new THREE.Color(accent),
                    opacity: 0.85
                });

                const points = new THREE.Points(geometry, material);
                points.scale.set(0.75, 0.75, 0.75);
                scene.add(points);

                const pos = geometry.attributes.position.array;
                const baseZ = [...pos];
                const t0 = performance.now();

                // ✴️ Controle de glitch
                let glitching = false;
                let glitchProgress = 0;

                function triggerGlitchOnce() {
                    if (glitching) return;
                    glitching = true;
                    glitchProgress = 0;
                    setTimeout(() => (glitching = false), 320 + Math.random() * 120);
                }

                // registra no barramento global
                (window.__holoBus ||= {
                    instances: [],
                    started: false
                }).instances.push({
                    trigger: triggerGlitchOnce,
                    el: renderer.domElement
                });

                function animate() {
                    const t = (performance.now() - t0) / 1000;

                    if (glitching) {
                        glitchProgress = Math.min(glitchProgress + 0.08, 1);
                    } else if (glitchProgress > 0) {
                        glitchProgress = Math.max(glitchProgress - 0.05, 0);
                    }

                    const spread = glitching ?
                        Math.sin(glitchProgress * Math.PI) * 35 // expansão momentânea
                        :
                        0;

                    for (let i = 0; i < pts.length / 3; i++) {
                        const iz = i * 3 + 2;
                        const base = baseZ[iz];
                        const pulse = Math.sin(t * 3 + phases[i]) * 2;
                        const zOffset = base + pulse + (Math.random() - 0.5) * (glitching ? 2.5 : 0.2);

                        // partículas expandem e se recolhem no glitch
                        const xBase = pts[i * 3];
                        const yBase = pts[i * 3 + 1];
                        const angle = phases[i];
                        const radius = spread * 0.2;

                        pos[i * 3] = xBase + Math.cos(angle) * radius;
                        pos[i * 3 + 1] = yBase + Math.sin(angle) * radius;
                        pos[iz] = zOffset;
                    }

                    geometry.attributes.position.needsUpdate = true;

                    // leve rotação constante
                    points.rotation.y = Math.sin(t * 0.5) * 0.2;
                    points.rotation.x = Math.cos(t * 0.3) * 0.1;

                    // brilho intenso durante glitch, depois volta
                    const pulse = glitching ? 1.35 : 1;
                    material.opacity = (0.7 + Math.sin(t * 2) * 0.25) * pulse;

                    renderer.render(scene, camera);
                    requestAnimationFrame(animate);
                }
                animate();

                // hover padrão
                const parent = wrap.parentElement;
                parent.addEventListener('mouseenter', () => {
                    gsap.to(renderer.domElement, {
                        opacity: 0,
                        duration: 0.4,
                        ease: 'power2.out'
                    });
                });
                parent.addEventListener('mouseleave', () => {
                    gsap.to(renderer.domElement, {
                        opacity: 1,
                        duration: 0.6,
                        ease: 'power2.out'
                    });
                });
            }

            // inicia o agendador global de glitch (um a cada 3s)
            const bus = (window.__holoBus ||= {
                instances: [],
                started: false
            });
            if (!bus.started) {
                bus.started = true;
                setInterval(() => {
                    const list = bus.instances.filter(i => i && i.el && document.body.contains(i.el));
                    if (!list.length) return;
                    const idx = Math.floor(Math.random() * list.length);
                    list[idx].trigger(); // 💥 dispara glitch em UM holograma
                }, 3000); // ⏱️ a cada 3 segundos
            }



            function initThumbHologram(card) {
                if (card.__holoThumbBuilt) return;
                const wrap = card.querySelector('.portal-interface');
                const img = wrap ? wrap.querySelector('img') : null;
                if (!wrap || !img) return;
                ensureThree().then(() => buildLogoHologramThree(card, wrap, img));
                card.__holoThumbBuilt = true;
            }

            // inicializa todos os cards
            document.querySelectorAll('.project-portal').forEach(card => {
                const holo = card.querySelector('.project-two__item__holo');
                if (!holo) return;

                initThumbHologram(card);

                let rect;
                const maxTilt = 8;
                card.addEventListener('mousemove', e => {
                    rect = rect || holo.getBoundingClientRect();
                    const x = (e.clientX - rect.left - rect.width / 2) / rect.width;
                    const y = (e.clientY - rect.top - rect.height / 2) / rect.height;
                    gsap.to(holo, {
                        duration: 0.3,
                        rotateY: x * maxTilt,
                        rotateX: -y * maxTilt,
                        transformPerspective: 800
                    });
                });
                card.addEventListener('mouseleave', () => {
                    gsap.to(holo, {
                        duration: 0.5,
                        rotateX: 0,
                        rotateY: 0
                    });
                });
            });

            // reativa quando owl duplica slides
            document.querySelectorAll('.ogency-owl__carousel').forEach(carousel => {
                carousel.addEventListener('translated.owl.carousel', () => {
                    document.querySelectorAll('.project-portal').forEach(initThumbHologram);
                });
            });

            setTimeout(() => document.querySelectorAll('.project-portal').forEach(initThumbHologram), 800);

            // ativa o campo de luz global
            createGlobalHoloField();
        }

        if (window.gsap) initHoloCards();
        else loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js')
            .then(initHoloCards)
            .catch(() => console.error("GSAP não pôde ser carregado."));
    })();
</script>
