<style>
    /* ==============================
       🔶 BASE DO HERO (VIDEO SLIDER)
       ============================== */
    .main-slider {
        position: relative;
        overflow: hidden;
        z-index: 50;
        /* ✅ fica sempre acima das outras seções */
        background: #000;
    }

    .video-hero {
        position: relative;
        min-height: 100dvh;
    }

    .video-hero__media {
        position: absolute;
        inset: 0;
        overflow: hidden;
        z-index: 1;
    }

    .video-hero__media video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .video-hero__overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0);
        z-index: 2;
        pointer-events: none;
        transition: background-color 0.2s ease;
    }

    .video-hero__content {
        position: relative;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100dvh;
        text-align: center;
    }

    @media (max-width: 767px) {

        .video-hero,
        .video-hero__content {
            min-height: 92dvh;
        }
    }

    /* ==============================
       🔶 DIFERENCIAIS CENTRAIS
       ============================== */
    .hero-diffs {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        pointer-events: none;
    }

    .hero-diff {
        color: #fff;
        font-weight: 600;
        line-height: 1.15;
        font-size: clamp(24px, 5vw, 56px);
        text-shadow: 0 2px 24px rgba(0, 0, 0, 0.45);
        opacity: 0;
        pointer-events: none;
        text-align: center;
        padding: 0 20px;
        grid-area: 1 / 1;
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
    }

    /* ==============================
       🔶 HEADER FIXO (LOCK DURANTE PIN)
       ============================== */
    .stricky-header {
        transition: all 0.3s ease;
    }

    body.hero-sticky-lock .stricky-header {
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translateY(-100%) !important;
        pointer-events: none !important;
    }

    /* ==============================
       🔶 ICONES GLASSMORPHISM
       ============================== */
    .glass-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        backdrop-filter: blur(8px) saturate(140%);
        -webkit-backdrop-filter: blur(8px) saturate(140%);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .glass-icon:hover {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-1px);
    }

    /* ==============================
       🔶 CORREÇÃO DO FLASH
       ============================== */
    body.hero-prelock .main-slider {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
    }

    body.hero-prelock .page-wrapper {
        overflow: hidden;
    }
</style>


<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<!--Main Slider Start-->
<section class="main-slider">

    <div class="video-hero">
        <div class="video-hero__media">
            @php
                $sliderRec = \App\Models\Slider::first();
                $videoPath = $sliderRec?->video ? asset($sliderRec->video) : asset('assets/video/MMConnect.mp4');
                $t1 = $sliderRec?->text_1 ?: 'Diferencial';
                $t2 = $sliderRec?->text_2 ?: 'Diferencial 2';
                $t3 = $sliderRec?->text_3 ?: 'Diferencial 3';
            @endphp
            <video autoplay muted loop playsinline>
                <source src="{{ $videoPath }}" type="video/mp4">
            </video>
        </div>
        <div class="video-hero__overlay"></div>
        <div class="container video-hero__content">
            <div class="row w-100">
                <div class="col-xl-12">
                    <div class="main-slider__two__content text-center hero-diffs">
                        <div class="hero-diff" data-overlay="0.2">{{ $t1 }}</div>
                        <div class="hero-diff" data-overlay="0.4">{{ $t2 }}</div>
                        <div class="hero-diff" data-overlay="0.6">{{ $t3 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- social start -->
    @php
        $setting = \App\Models\Setting::query()->first();
        $socials = [
            'instagram' => ['icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
            'whatsapp' => ['icon' => 'fa-brands fa-whatsapp', 'label' => 'WhatsApp'],
            'linkedin' => ['icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
            'behance' => ['icon' => 'fa-brands fa-behance', 'label' => 'Behance'],
            'github' => ['icon' => 'fa-brands fa-github', 'label' => 'GitHub'],
        ];
    @endphp

    <div class="main-slider__socails">
        @foreach ($socials as $field => $meta)
            @php $url = optional($setting)->{$field}; @endphp
            @if (!empty($url))
                <a class="glass-icon" href="{{ $url }}" target="_blank" rel="noopener"
                    aria-label="{{ $meta['label'] }}">
                    <i class="{{ $meta['icon'] }}"></i>
                </a>
            @endif
        @endforeach
    </div>

    <!-- social end -->
</section>
<!--Main Slider End-->

<script>
    (function() {
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const s = document.createElement("script");
                s.src = src;
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            });
        }

        function initHeroScroll() {
            if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
            window.scrollTo(0, 0);

            // ✅ fixa o hero imediatamente antes do GSAP agir
            document.body.classList.add("hero-prelock");

            const startTimeline = function() {
                try {
                    gsap.registerPlugin(ScrollTrigger);
                } catch (e) {}

                const section = document.querySelector(".main-slider");
                const overlay = section?.querySelector(".video-hero__overlay");
                const diffs = gsap.utils.toArray(".hero-diff");
                if (!section || !overlay || !diffs.length) return;

                document.querySelectorAll(".pin-spacer").forEach(s => s.remove());
                gsap.set(overlay, {
                    backgroundColor: "rgba(0,0,0,0)"
                });
                gsap.set(diffs, {
                    autoAlpha: 0,
                    y: 40
                });

                const lockSticky = flag => {
                    document.body.classList.toggle("hero-sticky-lock", !!flag);
                };

                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: section,
                        start: "top top",
                        end: "+=" + diffs.length * 100 + "%",
                        pin: true,
                        pinSpacing: true,
                        scrub: 1,
                        anticipatePin: 1,
                        invalidateOnRefresh: true,
                        onEnter: () => lockSticky(true),
                        onEnterBack: () => lockSticky(true),
                        onLeave: () => lockSticky(false),
                        onLeaveBack: () => lockSticky(false),
                    }
                });

                diffs.forEach((el, i) => {
                    const targetOverlay = parseFloat(el.getAttribute("data-overlay") || "0.2");
                    tl.to(overlay, {
                        duration: 0.4,
                        backgroundColor: "rgba(0,0,0," + targetOverlay + ")",
                    });
                    tl.fromTo(el, {
                            autoAlpha: 0,
                            y: 40
                        }, {
                            autoAlpha: 1,
                            y: 0,
                            duration: 0.5,
                            ease: "power2.out"
                        },
                        "<"
                    );
                    if (i < diffs.length - 1) {
                        tl.to(el, {
                                autoAlpha: 0,
                                y: -30,
                                duration: 0.45,
                                ease: "power2.in"
                            },
                            "+=0.5"
                        );
                    }
                });

                tl.to({}, {
                    duration: 0.4
                });

                // 🔓 libera o fluxo normal assim que o pin estiver ativo
                setTimeout(() => {
                    document.body.classList.remove("hero-prelock");
                }, 200);
            };

            const afterGSAP = function() {
                if (window.ScrollTrigger) startTimeline();
                else {
                    loadScript("https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js")
                        .then(startTimeline)
                        .catch(() => {});
                }
            };

            if (window.gsap) afterGSAP();
            else {
                loadScript("https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js")
                    .then(afterGSAP)
                    .catch(() => {});
            }
        }

        if (document.readyState === "loading")
            document.addEventListener("DOMContentLoaded", initHeroScroll);
        else
            initHeroScroll();
    })();
</script>
