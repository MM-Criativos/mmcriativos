<style>
    /* ======== CARD BASE ======== */
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
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: none;
    }

    .project-portal .project-two__item__holo {
        position: absolute;
        inset: 0;
        border-radius: 16px;
        overflow: hidden;
        transform-style: preserve-3d;
        will-change: transform;
    }

    /* ======== LOGO ======== */
    .portal-logo {
        position: absolute;
        top: 45%;
        left: 50%;
        z-index: 4;
        /* acima de partÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­culas/feixe para respeitar PNG */
        animation: floatLogo 4s ease-in-out infinite;
        pointer-events: none;
        isolation: isolate;
        /* garante blend isolado */
    }

    .portal-logo::before {
        /* halo atrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡s do PNG para realÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ar sem tingir o logo */
        content: "";
        position: absolute;
        inset: -30px;
        background: radial-gradient(ellipse at center, rgba(255, 136, 0, .28), rgba(255, 136, 0, 0) 60%);
        filter: blur(12px);
        z-index: -1;
        border-radius: 50%;
    }

    .portal-logo img {
        width: 120px;
        height: auto;
        object-fit: contain;
        opacity: 1;
        filter: none;
        /* nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o coloriza PNG */
        transition: opacity 0.6s ease, transform 0.6s ease;
        will-change: transform, opacity;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: optimizeQuality;
        background: transparent;
    }

    @keyframes floatLogo {

        0%,
        100% {
            transform: translate(-50%, -50%) scale(1);
        }

        50% {
            transform: translate(-50%, -54%) scale(1.05);
        }
    }

    /* ======== SWARM PARTICLES ======== */
    .holo-swarm {
        position: absolute;
        inset: 10% 8% 18% 8%;
        z-index: 2;
        pointer-events: none;
        overflow: hidden;
        border-radius: 12px;
        mix-blend-mode: screen;
    }

    .holo-swarm .px {
        position: absolute;
        width: 3px;
        height: 3px;
        border-radius: 1px;
        background: rgba(255, 136, 0, 0.95);
        box-shadow: 0 0 10px rgba(255, 136, 0, 1), 0 0 18px rgba(255, 136, 0, .8);
        opacity: 0;
        will-change: transform, opacity, filter;
    }

    /* (portal-interface removed) */

    /* ======== TEXTO / LEGENDA ======== */
    .project-two__item__content {
        position: absolute;
        bottom: 15px;
        text-align: center;
        z-index: 4;
        width: 100%;
        color: #fff;
        text-shadow: 0 0 10px rgba(255, 136, 0, 0.3);
        background: none;
        padding: 0;
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

    /* ======== LOGO ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ PARTICLES (HOLOGRAM) ======== */
    .portal-logo .logo-particles {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 120px;
        /* sync with logo width */
        height: auto;
        pointer-events: none;
        transform-style: preserve-3d;
        mix-blend-mode: screen;
        will-change: transform;
    }

    .portal-logo .logo-particles .dot {
        position: absolute;
        width: 3px;
        height: 3px;
        border-radius: 1.5px;
        background: rgba(255, 136, 0, 0.95);
        box-shadow: 0 0 10px rgba(255, 136, 0, 0.9), 0 0 18px rgba(255, 136, 0, 0.7);
        opacity: 0;
        will-change: transform, opacity, filter;
    }

    /* WebGL portal rings canvas; hide original CSS portals */
</style>
<style>
    /* Hologram refinements: remove original halo, brighter dense particles, hide on hover */
    .portal-logo::before {
        display: none !important;
    }

    .portal-logo .logo-particles {
        will-change: transform, opacity;
        transition: opacity .4s ease, transform .4s ease;
    }

    .portal-logo .logo-particles .dot {
        background: rgba(255, 136, 0, 0.98);
        box-shadow: 0 0 14px rgba(255, 136, 0, 1), 0 0 28px rgba(255, 136, 0, 0.9);
    }

    .portal-logo canvas.logo-points {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        mix-blend-mode: screen;
        transition: opacity .4s ease, transform .4s ease;
    }

    /* hover behavior is controlled via GSAP; no CSS hide */
</style>

<!-- Project Start -->
<section class="project-two">
    <div class="container">
        <div class="section-title text-center">
            <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
            <h2 class="section-title__title">
                Conheça alguns dos trabalhos<br> que deram vida a grandes ideias
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
                    "0": { "items": 1 },
                    "768": { "items": 2 },
                    "992": { "items": 3 },
                    "1200": { "items": 3 }
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

                        <!-- Partículas -->
                        <div class="holo-swarm"></div>

                        <!-- Logo do cliente -->
                        @php
                            $logo = $project->client
                                ? $project->client->logo_url
                                : asset('assets/images/logos/default-logo.png');
                        @endphp
                        <div class="portal-logo">
                            <img src="{{ $logo }}" alt="{{ optional($project->client)->name ?? 'Cliente' }}"
                                loading="lazy" decoding="async">
                        </div>

                    </div>

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
</section>
<!-- Project End -->


<script>
    (function() {
        function loadScript(src) {
            return new Promise(function(res, rej) {
                var s = document.createElement('script');
                s.src = src;
                s.onload = res;
                s.onerror = rej;
                document.head.appendChild(s);
            });
        }

        function initHoloCards() {
            try {
                if (!window.gsap) return;
            } catch (e) {
                return;
            }

            // Ensure three.js is available
            function ensureThree() {
                return new Promise(function(resolve, reject) {
                    if (window.THREE) return resolve();
                    loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js')
                        .then(resolve)
                        .catch(reject);
                });
            }

            // Build hologram with WebGL points (three.js)
            function buildLogoHologramThree(card, wrap, img) {
                function ready() {
                    try {
                        var wTarget = 210; // larger logo
                        var cw = Math.min(wTarget, img.naturalWidth || wTarget);
                        var ratio = (img.naturalHeight || wTarget) / (img.naturalWidth || wTarget);
                        var ch = Math.max(1, Math.round(cw * ratio));

                        // Sample alpha from the logo
                        var off = document.createElement('canvas');
                        off.width = cw;
                        off.height = ch;
                        var ctx = off.getContext('2d');
                        ctx.clearRect(0, 0, cw, ch);
                        ctx.drawImage(img, 0, 0, cw, ch);
                        var data = ctx.getImageData(0, 0, cw, ch).data;

                        // Build positions from non-transparent pixels
                        // dynamic step to cap particle count for FPS (~14k per logo)
                        var target = 14000;
                        var step = Math.max(1, Math.floor(Math.sqrt((cw * ch) / target)));
                        if (step < 1) step = 1;
                        var pts = [];
                        var phases = [];
                        for (var y = 0; y < ch; y += step) {
                            for (var x = 0; x < cw; x += step) {
                                var a = data[(y * cw + x) * 4 + 3];
                                if (a > 60) {
                                    // center geometry at (0,0)
                                    pts.push(x - cw / 2);
                                    pts.push((ch / 2) - y);
                                    pts.push((Math.random() - 0.5) * 18); // slight depth
                                    phases.push(Math.random() * Math.PI * 2);
                                }
                            }
                        }
                        var count = pts.length / 3;
                        if (count < 5) {
                            // Not enough pixels to form a hologram; keep PNG only
                            return;
                        }

                        // Create renderer/camera/scene
                        var scene = new THREE.Scene();
                        var camera = new THREE.PerspectiveCamera(45, cw / ch, 0.1, 1000);
                        camera.position.z = Math.max(cw, ch) * 1.6;

                        var renderer = new THREE.WebGLRenderer({
                            alpha: true,
                            antialias: true
                        });
                        renderer.setPixelRatio(Math.min(1.5, window.devicePixelRatio || 1));
                        renderer.setSize(cw, ch);
                        renderer.domElement.className = 'logo-points';
                        wrap.appendChild(renderer.domElement);

                        // Round disc texture for points
                        function discTexture() {
                            var c = document.createElement('canvas');
                            c.width = c.height = 32;
                            var g = c.getContext('2d');
                            var grd = g.createRadialGradient(16, 16, 0, 16, 16, 16);
                            grd.addColorStop(0, 'rgba(255,255,255,1)');
                            grd.addColorStop(0.5, 'rgba(255,255,255,0.9)');
                            grd.addColorStop(1, 'rgba(255,255,255,0)');
                            g.fillStyle = grd;
                            g.fillRect(0, 0, 32, 32);
                            var tex = new THREE.CanvasTexture(c);
                            tex.minFilter = THREE.LinearFilter;
                            return tex;
                        }

                        // Use card accent color if present
                        var accent = (getComputedStyle(card).getPropertyValue('--accent') || '#ff8800').trim() ||
                            '#ff8800';
                        var material = new THREE.PointsMaterial({
                            size: 3.4,
                            sizeAttenuation: true,
                            map: discTexture(),
                            transparent: true,
                            depthWrite: false,
                            color: new THREE.Color(accent),
                            blending: THREE.AdditiveBlending,
                            opacity: 1
                        });

                        var geometry = new THREE.BufferGeometry();
                        var positionAttr = new THREE.Float32BufferAttribute(new Float32Array(pts), 3);
                        geometry.setAttribute('position', positionAttr);
                        var points = new THREE.Points(geometry, material);
                        scene.add(points);

                        // High-density point cloud for hover (densify effect)
                        var targetHi = 22000;
                        var stepHi = Math.max(1, Math.floor(Math.sqrt((cw * ch) / targetHi)));
                        if (stepHi < 1) stepHi = 1;
                        var ptsHi = [],
                            phasesHi = [];
                        for (var y2 = 0; y2 < ch; y2 += stepHi) {
                            for (var x2 = 0; x2 < cw; x2 += stepHi) {
                                var a2 = data[(y2 * cw + x2) * 4 + 3];
                                if (a2 > 60) {
                                    ptsHi.push(x2 - cw / 2);
                                    ptsHi.push((ch / 2) - y2);
                                    ptsHi.push((Math.random() - 0.5) * 18);
                                    phasesHi.push(Math.random() * Math.PI * 2);
                                }
                            }
                        }
                        var countHi = ptsHi.length / 3;
                        var geometryHi = new THREE.BufferGeometry();
                        var posHi = new THREE.Float32BufferAttribute(new Float32Array(ptsHi), 3);
                        geometryHi.setAttribute('position', posHi);
                        var materialHi = new THREE.PointsMaterial({
                            size: 3.2,
                            sizeAttenuation: true,
                            map: discTexture(),
                            transparent: true,
                            depthWrite: false,
                            color: new THREE.Color(accent),
                            blending: THREE.AdditiveBlending,
                            opacity: 0
                        });
                        var pointsHi = new THREE.Points(geometryHi, materialHi);
                        scene.add(pointsHi);

                        // Default: hologram visible, PNG logo hidden
                        try {
                            img.style.opacity = '0';
                        } catch (e) {}

                        // Gentle ambient motion preserving shape
                        var t0 = performance.now();
                        var ampX = 0.6,
                            ampY = 0.8,
                            ampZ = 1.1; // tighter jitter to preserve shape
                        function update() {
                            var t = (performance.now() - t0) / 1000;
                            var pos = geometry.attributes.position.array;
                            for (var i = 0; i < count; i++) {
                                var baseX = pts[i * 3];
                                var baseY = pts[i * 3 + 1];
                                var baseZ = pts[i * 3 + 2];
                                var ph = phases[i];
                                pos[i * 3] = baseX + Math.sin(t * 1.4 + ph) * ampX;
                                pos[i * 3 + 1] = baseY + Math.cos(t * 1.3 + ph) * ampY;
                                pos[i * 3 + 2] = baseZ + Math.sin(t * 1.7 + ph) * ampZ;
                            }
                            geometry.attributes.position.needsUpdate = true;
                            if (countHi) {
                                var pos2 = geometryHi.attributes.position.array;
                                for (var j = 0; j < countHi; j++) {
                                    var bx = ptsHi[j * 3];
                                    var by = ptsHi[j * 3 + 1];
                                    var bz = ptsHi[j * 3 + 2];
                                    var ph2 = phasesHi[j];
                                    pos2[j * 3] = bx + Math.sin(t * 1.5 + ph2) * (ampX * 0.9);
                                    pos2[j * 3 + 1] = by + Math.cos(t * 1.4 + ph2) * (ampY * 0.9);
                                    pos2[j * 3 + 2] = bz + Math.sin(t * 1.8 + ph2) * (ampZ * 0.9);
                                }
                                geometryHi.attributes.position.needsUpdate = true;
                            }
                            renderer.render(scene, camera);
                        }
                        gsap.ticker.add(update);

                        // store refs for transition + hover
                        card.__holoData = {
                            material: material,
                            points: points,
                            materialHi: materialHi,
                            pointsHi: pointsHi,
                            object: points,
                            img: img,
                            hasPoints: true
                        };

                        // Hover timeline: particles condense and PNG logo reappears
                        if (!card.__hoverTl) {
                            var hoverTl = gsap.timeline({
                                paused: true,
                                defaults: {
                                    ease: 'power2.inOut'
                                }
                            });
                            // densify + glow
                            hoverTl.to(material, {
                                duration: 0.2,
                                size: 3.6,
                                opacity: 1
                            }, 0);
                            hoverTl.to(materialHi, {
                                duration: 0.25,
                                opacity: 1,
                                size: 3.9
                            }, 0);
                            // reveal PNG and fade particles after a brief focus
                            hoverTl.to(img, {
                                duration: 0.35,
                                opacity: 1
                            }, 0.2);
                            hoverTl.to([material, materialHi], {
                                duration: 0.35,
                                opacity: 0.0
                            }, 0.2);
                            card.__hoverTl = hoverTl;

                            function onHoverEnter() {
                                if (card.__transitionRunning) return;
                                hoverTl.play();
                            }

                            function onHoverLeave() {
                                if (card.__transitionRunning) return;
                                hoverTl.reverse();
                            }
                            card.addEventListener('mouseenter', onHoverEnter);
                            card.addEventListener('mouseleave', onHoverLeave);
                        }
                    } catch (err) {
                        /* ignore */
                    }
                }

                if (img && (img.complete && img.naturalWidth)) ready();
                else if (img) {
                    if (img.decode) {
                        img.decode().then(ready).catch(function() {
                            img.addEventListener('load', ready, {
                                once: true
                            });
                        });
                    } else {
                        img.addEventListener('load', ready, {
                            once: true
                        });
                    }
                }
            }

            // Build particle cloud from a logo image (PNG with alpha)
            function buildLogoHologram(card, wrap, img) {
                function ready() {
                    try {
                        var wTarget = 120; // same as CSS width of logo
                        var cw = Math.min(wTarget, img.naturalWidth || wTarget);
                        var ratio = (img.naturalHeight || wTarget) / (img.naturalWidth || wTarget);
                        var ch = Math.max(1, Math.round(cw * ratio));

                        var off = document.createElement('canvas');
                        off.width = cw;
                        off.height = ch;
                        var ctx = off.getContext('2d');
                        ctx.clearRect(0, 0, cw, ch);
                        ctx.drawImage(img, 0, 0, cw, ch);
                        var data = ctx.getImageData(0, 0, cw, ch).data;

                        var container = document.createElement('div');
                        container.className = 'logo-particles';
                        container.style.width = cw + 'px';
                        container.style.height = ch + 'px';
                        wrap.appendChild(container);

                        var step = cw > 140 ? 4 : 3; // denser sampling
                        for (var y = 0; y < ch; y += step) {
                            for (var x = 0; x < cw; x += step) {
                                var idx = (y * cw + x) * 4;
                                var a = data[idx + 3]; // alpha channel
                                if (a > 60) {
                                    var dot = document.createElement('span');
                                    dot.className = 'dot';
                                    dot.style.left = x + 'px';
                                    dot.style.top = y + 'px';
                                    container.appendChild(dot);

                                    var z0 = (Math.random() - 0.5) * 30;
                                    var dly = Math.random() * 0.4;
                                    var amp = 1.4 + Math.random() * 2.2;
                                    gsap.set(dot, {
                                        x: 0,
                                        y: 0,
                                        z: z0,
                                        opacity: 0
                                    });
                                    gsap.to(dot, {
                                        duration: 0.8,
                                        opacity: 0.85,
                                        delay: dly,
                                        ease: 'power2.out'
                                    });
                                    gsap.to(dot, {
                                        duration: 2 + Math.random() * 2.5,
                                        x: '+=' + (Math.random() * amp - amp / 2),
                                        y: '+=' + (Math.random() * amp - amp / 2),
                                        z: z0 + (Math.random() * 18 - 9),
                                        repeat: -1,
                                        yoyo: true,
                                        ease: 'sine.inOut',
                                        delay: dly
                                    });
                                    gsap.to(dot, {
                                        duration: 1.2 + Math.random(),
                                        opacity: 0.7 + Math.random() * 0.3,
                                        filter: 'blur(' + (Math.random() * 1.2) + 'px)',
                                        repeat: -1,
                                        yoyo: true,
                                        ease: 'sine.inOut',
                                        delay: dly * 0.5
                                    });
                                }
                            }
                        }

                        // Hide original logo completely (no shadow)
                        gsap.to(img, {
                            duration: 0.4,
                            opacity: 0,
                            ease: 'power2.out'
                        });

                        // Hover intensifies the hologram
                        card.addEventListener('mouseenter', function() {
                            gsap.to(container, {
                                duration: 0.4,
                                scale: 1.06,
                                filter: 'brightness(1.2)'
                            });
                        });
                        card.addEventListener('mouseleave', function() {
                            gsap.to(container, {
                                duration: 0.6,
                                scale: 1.0,
                                filter: 'brightness(1)'
                            });
                        });
                    } catch (err) {
                        /* ignore */
                    }
                }

                if (img && (img.complete && img.naturalWidth)) ready();
                else if (img) img.addEventListener('load', ready, {
                    once: true
                });
            }

            function initLogoForCard(card) {
                if (card.__holoLogoBuilt) return; // avoid duplicates
                var logoWrap = card.querySelector('.portal-logo');
                var logoImg = logoWrap ? logoWrap.querySelector('img') : null;
                if (logoWrap && logoImg) {
                    // ensure load even if lazy
                    try {
                        if ('loading' in logoImg) logoImg.loading = 'eager';
                    } catch (e) {}
                    ensureThree().then(function() {
                        buildLogoHologramThree(card, logoWrap, logoImg);
                        card.__holoLogoBuilt = true;
                        if (!card.__transitionBound) {
                            card.addEventListener('click', function() {
                                var slug = card.getAttribute('data-slug');
                                var name = card.getAttribute('data-name');
                                if (window.openProjectModal) {
                                    try {
                                        openProjectModal(slug, name);
                                    } catch (e) {}
                                }
                            });
                            card.__transitionBound = true;
                        }
                    }).catch(function() {
                        buildLogoHologram(card, logoWrap, logoImg);
                        card.__holoLogoBuilt = true;
                    });
                }
            }


            var cards = document.querySelectorAll('.project-portal');
            cards.forEach(function(card) {
                var holo = card.querySelector('.project-two__item__holo');
                var swarm = card.querySelector('.holo-swarm');
                if (!holo || !swarm) return;

                // create particles
                var N = 36;
                for (var i = 0; i < N; i++) {
                    var p = document.createElement('span');
                    p.className = 'px';
                    swarm.appendChild(p);
                    var x = Math.random() * 100,
                        y = Math.random() * 100;
                    p.style.left = x + '%';
                    p.style.top = y + '%';
                    var driftX = (Math.random() * 2 - 1) * 20; // px
                    var driftY = (Math.random() * 2 - 1) * 30; // px
                    var d = 2 + Math.random() * 3;
                    gsap.set(p, {
                        opacity: Math.random() * 0.6 + 0.2
                    });
                    gsap.to(p, {
                        duration: 2.5 + Math.random() * 2.5,
                        x: driftX,
                        y: driftY,
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut'
                    });
                    gsap.to(p, {
                        duration: 1.8 + Math.random() * 1.4,
                        opacity: 0.15 + Math.random() * 0.7,
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut'
                    });
                    gsap.to(p, {
                        duration: 4 + Math.random() * 3,
                        filter: 'blur(' + (Math.random() * 1.2) + 'px)',
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut'
                    });
                }

                // Build holographic logo as particles
                initLogoForCard(card);

                // Build WebGL portal rings + central beam
                // portals removed as requested

                // tilt on hover/move
                var rect;
                var maxTilt = 8;

                function onMove(e) {
                    rect = rect || holo.getBoundingClientRect();
                    var cx = rect.left + rect.width / 2;
                    var cy = rect.top + rect.height / 2;
                    var x = (e.clientX - cx) / rect.width; // -0.5..0.5
                    var y = (e.clientY - cy) / rect.height;
                    gsap.to(holo, {
                        duration: 0.3,
                        rotateY: x * maxTilt,
                        rotateX: -y * maxTilt,
                        transformPerspective: 800,
                        transformStyle: 'preserve-3d'
                    });
                }

                function onLeave() {
                    gsap.to(holo, {
                        duration: 0.5,
                        rotateX: 0,
                        rotateY: 0
                    });
                }
                card.addEventListener('mousemove', onMove);
                card.addEventListener('mouseleave', onLeave);

                // touch pulse
                card.addEventListener('touchstart', function() {
                    card.classList.add('mobile-active');
                    setTimeout(function() {
                        card.classList.remove('mobile-active');
                    }, 1200);
                });

                // beam intensity on hover (only if CSS beam exists)
                var beam = card.querySelector('.portal-beam');
                if (beam) {
                    card.addEventListener('mouseenter', function() {
                        gsap.to(beam, {
                            duration: 0.4,
                            opacity: 1
                        });
                    });
                    card.addEventListener('mouseleave', function() {
                        gsap.to(beam, {
                            duration: 0.6,
                            opacity: 0.8
                        });
                    });
                }
            });

            // Safety: re-scan after carousel initializes/clones
            setTimeout(function() {
                document.querySelectorAll('.project-portal').forEach(initLogoForCard);
            }, 400);
            setTimeout(function() {
                document.querySelectorAll('.project-portal').forEach(initLogoForCard);
            }, 1200);

            // Observe carousel DOM for new slides (e.g., Owl clones)
            var carousel = document.querySelector('.project-two__carousel');
            if (carousel && window.MutationObserver) {
                var mo = new MutationObserver(function(mutations) {
                    mutations.forEach(function(m) {
                        if (!m.addedNodes) return;
                        m.addedNodes.forEach(function(n) {
                            if (n.nodeType !== 1) return;
                            if (n.matches && n.matches('.project-portal')) initLogoForCard(
                                n);
                            if (n.querySelectorAll) n.querySelectorAll('.project-portal')
                                .forEach(initLogoForCard);
                        });
                    });
                });
                mo.observe(carousel, {
                    childList: true,
                    subtree: true
                });
            }
        }

        if (window.gsap) {
            initHoloCards();
        } else {
            loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js').then(initHoloCards).catch(
                function() {});
        }
    })();
</script>
