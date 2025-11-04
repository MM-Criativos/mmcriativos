<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<!--Main Slider Start-->
<section class="main-slider">
    <style>
        .video-hero {
            position: relative;
            min-height: 100vh;
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
        }

        .video-hero__overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0);
            z-index: 2;
            pointer-events: none;
            transition: background-color .2s ease;
        }

        .video-hero__content {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        @media (max-width: 767px) {

            .video-hero,
            .video-hero__content {
                min-height: 92vh;
            }
        }

        /* Central messages (diferenciais) */
        .hero-diffs {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            /* center in both axes */
            pointer-events: none;
            /* allow menu/buttons to remain clickable */
        }

        .hero-diff {
            color: #fff;
            font-weight: 600;
            line-height: 1.15;
            font-size: clamp(24px, 5vw, 56px);
            text-shadow: 0 2px 24px rgba(0, 0, 0, .45);
            opacity: 0;
            /* GSAP controls */
            pointer-events: none;
            text-align: center;
            padding: 0 20px;
            /* mobile safe gutters */
            grid-area: 1 / 1;
            /* stack all items centered */
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Lock sticky header while hero is pinned */
        body.hero-sticky-lock .stricky-header {
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translateY(-100%) !important;
            pointer-events: none !important;
        }

        /* Glassmorphism controls (social + phone) */
        .glass-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .25);
            background: rgba(255, 255, 255, .08);
            color: #fff;
            backdrop-filter: blur(8px) saturate(140%);
            -webkit-backdrop-filter: blur(8px) saturate(140%);
            transition: .2s;
            text-decoration: none;
        }

        .glass-icon:hover {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .4);
            transform: translateY(-1px);
        }

        .glass-pill {
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

        .glass-pill:hover {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .4);
            transform: translateY(-1px);
        }
    </style>
    <div class="video-hero">
        <div class="video-hero__media">
            @php
                $sliderRec = \App\Models\Slider::first();
                $videoPath = $sliderRec?->video ? asset($sliderRec->video) : asset('assets/video/MMConnect.mp4');
                $t1 = $sliderRec?->text_1 ?: 'Diferencial 1';
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
            return new Promise(function(resolve, reject) {
                var s = document.createElement('script');
                s.src = src;
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            });
        }

        function initHeroScroll() {
            var startTimeline = function() {
                try {
                    gsap.registerPlugin(ScrollTrigger);
                } catch (e) {}

                // Pin the whole slider section so its absolutely-positioned
                // children (social/phone) stay aligned during the pin.
                var section = document.querySelector('.main-slider');
                var overlay = section && section.querySelector('.video-hero__overlay');
                var diffs = gsap.utils.toArray('.hero-diff');
                if (!section || !overlay || !diffs.length) return;

                gsap.set(overlay, {
                    backgroundColor: 'rgba(0,0,0,0)'
                });
                gsap.set(diffs, {
                    autoAlpha: 0,
                    y: 40
                });

                // Evita que qualquer margem residual crie "gap" ao refrescar/recarregar
                try {
                    ScrollTrigger.addEventListener('refreshInit', function() {
                        gsap.set(section, {
                            clearProps: 'margin'
                        });
                    });
                } catch (e) {}

                var lockSticky = function(flag) {
                    document.body.classList.toggle('hero-sticky-lock', !!flag);
                };

                var tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: section,
                        start: 'top top',
                        end: '+=' + (diffs.length * 100) + '%',
                        pin: true, // pin the slider section
                        // Use padding (padrão) para evitar colapso de margens que causa espaço em branco
                        pinSpacing: true,
                        scrub: 1,
                        anticipatePin: 1,
                        invalidateOnRefresh: true,
                        onEnter: function() {
                            lockSticky(true);
                        },
                        onEnterBack: function() {
                            lockSticky(true);
                        },
                        onLeave: function() {
                            lockSticky(false);
                        },
                        onLeaveBack: function() {
                            lockSticky(false);
                        }
                    }
                });

                diffs.forEach(function(el, i) {
                    var targetOverlay = parseFloat(el.getAttribute('data-overlay') || '0.2');
                    tl.to(overlay, {
                        duration: 0.4,
                        backgroundColor: 'rgba(0,0,0,' + targetOverlay + ')'
                    });
                    tl.fromTo(el, {
                        autoAlpha: 0,
                        y: 40
                    }, {
                        autoAlpha: 1,
                        y: 0,
                        duration: 0.5,
                        ease: 'power2.out'
                    }, '<');
                    if (i < diffs.length - 1) {
                        tl.to(el, {
                            autoAlpha: 0,
                            y: -30,
                            duration: 0.45,
                            ease: 'power2.in'
                        }, '+=0.5');
                    }
                });
                // Hold a beat with the 3º diferencial visível antes de liberar o pin
                tl.to({}, {
                    duration: 0.4
                });
            };

            var afterGSAP = function() {
                if (window.ScrollTrigger) {
                    startTimeline();
                    // make sure positions are correct after assets load
                    window.addEventListener('load', function() {
                        try {
                            ScrollTrigger.refresh();
                        } catch (e) {}
                    });
                } else {
                    loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js')
                        .then(startTimeline)
                        .catch(function() {});
                }
            };

            if (window.gsap) {
                afterGSAP();
            } else {
                loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js')
                    .then(afterGSAP)
                    .catch(function() {});
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHeroScroll);
        } else {
            initHeroScroll();
        }
    })();
</script>
