<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<section class="page-header">
    @php
        $cover = $service->cover;
        $isVideo =
            $cover &&
            \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                '.mp4',
                '.webm',
                '.ogg',
                '.mov',
            ]);
    @endphp

    @if ($isVideo)
        <video class="page-header__bg" autoplay muted loop playsinline preload="metadata"
            style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85;z-index:1;">
            <source src="{{ asset($cover) }}">
        </video>
    @else
        <div class="page-header__bg" style="background-image: url('{{ asset($cover) }}');"></div>
    @endif
    <div class="page-header__overlay"></div>
    <div class="container">
        <h2 class="page-header__title">{{ $service->name }}</h2>
    </div>
</section>

<section class="services-details">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 wow fadeInUp animated" data-wow-delay="400ms">
                <div class="services-details__content">
                    <div class="why-choose-two">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-6 wow fadeInLeft animated" data-wow-delay="200ms">
                                    <div class="why-choose-two__left">
                                        <div class="section-title">
                                            <h5 class="section-title__tagline section-title__tagline--has-dots">
                                                {{ $service->info->subtitle }}</h5>
                                            <h2 class="section-title__title">{{ $service->info->title }}</h2>

                                        </div><!-- section-title -->
                                        <p class="why-choose-two__left--text">
                                            {{ $service->info->description }}
                                        </p>
                                        <div class="row">
                                            <div class="service-page__carousel-modal">
                                                <div class="container">
                                                    <style>
                                                        /* Benefit glitch card (front/back swap) */
                                                        .benefit-card {
                                                            position: relative;
                                                            min-height: 160px;
                                                            /* must be >= inner modal min-height */
                                                        }

                                                        .benefit-card .pixel-card__content {
                                                            position: absolute;
                                                            inset: 0;
                                                            height: 100%;
                                                            transition: opacity .18s linear, visibility .18s linear;
                                                        }

                                                        .benefit-card .pixel-card__content.front {
                                                            opacity: 1;
                                                            visibility: visible;
                                                        }

                                                        .benefit-card .pixel-card__content.back {
                                                            opacity: 0;
                                                            visibility: hidden;
                                                        }

                                                        .benefit-card.is-details .pixel-card__content.front {
                                                            opacity: 0;
                                                            visibility: hidden;
                                                        }

                                                        .benefit-card.is-details .pixel-card__content.back {
                                                            opacity: 1;
                                                            visibility: visible;
                                                        }

                                                        .benefit-card .holo-pixels {
                                                            position: absolute;
                                                            inset: -2px;
                                                            border-radius: 12px;
                                                            pointer-events: none;
                                                            opacity: 0;
                                                            mix-blend-mode: screen;
                                                            background-image: radial-gradient(rgba(255, 136, 0, .35) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 136, 0, .15) 0%, transparent 60%);
                                                            background-size: 4px 4px, 100% 100%;
                                                            filter: blur(.2px);
                                                        }

                                                        .benefit-card.is-opening .holo-pixels {
                                                            animation: pixDisperse .5s ease-out both;
                                                        }

                                                        .benefit-card.is-closing .holo-pixels {
                                                            animation: pixAssemble .5s ease-out both;
                                                        }

                                                        @keyframes pixDisperse {
                                                            0% {
                                                                opacity: .8;
                                                                background-size: 6px 6px, 100% 100%;
                                                                filter: blur(.3px);
                                                                transform: translateY(0) scale(1);
                                                            }

                                                            60% {
                                                                opacity: .4;
                                                                background-size: 12px 12px, 120% 100%;
                                                                filter: blur(.6px);
                                                            }

                                                            100% {
                                                                opacity: 0;
                                                                background-size: 18px 18px, 140% 100%;
                                                                filter: blur(1px);
                                                                transform: translateY(3px) scale(1.02);
                                                            }
                                                        }

                                                        @keyframes pixAssemble {
                                                            0% {
                                                                opacity: 0;
                                                                background-size: 18px 18px, 140% 100%;
                                                                filter: blur(1px);
                                                                transform: translateY(-3px) scale(.98);
                                                            }

                                                            40% {
                                                                opacity: .4;
                                                                background-size: 12px 12px, 115% 100%;
                                                                filter: blur(.6px);
                                                            }

                                                            100% {
                                                                opacity: .55;
                                                                background-size: 4px 4px, 100% 100%;
                                                                filter: blur(.2px);
                                                                transform: translateY(0) scale(1);
                                                            }
                                                        }

                                                        /* Center content (title/description) */
                                                        .benefit-card .service-one__item-modal {
                                                            min-height: 100%;
                                                            display: flex;
                                                            flex-direction: column;
                                                            align-items: center;
                                                            justify-content: center;
                                                            text-align: center;
                                                        }

                                                        .benefit-card .service-one__item-modal__title {
                                                            text-align: center;
                                                            width: 100%;
                                                        }

                                                        /* Only title on front, only description on back */
                                                        .benefit-card .pixel-card__content.front .service-one__item-modal__text {
                                                            display: none;
                                                        }

                                                        .benefit-card .pixel-card__content.back .service-one__item-modal__title {
                                                            display: none;
                                                        }
                                                    </style>
                                                    <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                                                        data-owl-options='{
                                                        "items": 4,
                                                        "margin": 30,
                                                        "smartSpeed": 1200,
                                                        "loop":true,
                                                        "autoplay": true,
                                                        "nav":false,
                                                        "dots":true,
                                                        "navText": ["<span class=\"icon-left-arrow\"></span>","<span class=\"icon-right-arrow\"></span>"],
                                                        "responsive":{
                                                            "0":{
                                                                "items":1,
                                                                "margin": 0
                                                            },
                                                            "600":{
                                                                "items": 2
                                                            },
                                                            "992":{
                                                                "items": 2
                                                            }
                                                        }
                                                        }'>
                                                        @foreach ($service->benefits as $benefit)
                                                            <div class="item">
                                                                <div class="benefit-card pixel-card">
                                                                    <div class="pixel-card__content front">
                                                                        <div class="service-one__item-modal"
                                                                            style="position: relative;">
                                                                            <span class="holo-pixels"
                                                                                aria-hidden="true"></span>
                                                                            <h3 class="service-one__item-modal__title">
                                                                                <a
                                                                                    href="#">{{ $benefit->title }}</a>
                                                                            </h3>
                                                                            <p class="service-one__item-modal__text">
                                                                                &nbsp;</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="pixel-card__content back">
                                                                        <div class="service-one__item-modal"
                                                                            style="position: relative;">
                                                                            <span class="holo-pixels"
                                                                                aria-hidden="true"></span>
                                                                            <h3 class="service-one__item-modal__title"
                                                                                style="display: none;">&nbsp;</h3>
                                                                            <p class="service-one__item-modal__text">
                                                                                {{ $benefit->subtitle }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 wow fadeInRight animated d-none d-lg-block" data-wow-delay="200ms"
                                    style="margin-top: 50px;">
                                    @if ($service->features && $service->features->count())
                                        @foreach ($service->features as $feature)
                                            <div class="why-choose__box">
                                                <div class="why-choose__box__icon">
                                                    <span class="icon-tick"></span>
                                                </div>
                                                <h3 class="why-choose__box__title">{{ $feature->title }}</h3>
                                                <p class="why-choose__box__text">{{ $feature->subtitle }}</p>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-center mt-4">Nenhuma funcionalidade cadastrada para este serviço.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="work-process-one">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="section-title text-center">
                                        <h5 class="section-title__tagline section-title__tagline--has-dots">
                                            nosso processo criativo
                                        </h5>
                                        <h2 class="section-title__title">
                                            Como transformamos ideias em páginas que convertem
                                        </h2>
                                    </div><!-- /.section-title -->
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 wow fadeInUp animated" data-wow-delay="500ms">
                                    <div class="work-process-one__border"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                                        data-owl-options='{
                                            "items": 3,
                                            "margin": 30,
                                            "smartSpeed": 800,
                                            "loop": false,
                                            "autoplay": false,
                                            "dots": true,
                                            "nav": false,
                                            "responsive": {
                                                "0": {"items":1, "margin":16},
                                                "600": {"items":2},
                                                "992": {"items":3},
                                                "1200": {"items":3}
                                            }
                                        }'>
                                        @foreach ($service->processes as $process)
                                            <div class="item">
                                                <div class="work-process-one__item text-center">
                                                    <div class="work-process-one__item__thumb">
                                                        <img src="{{ asset($process->image) }}"
                                                            alt="{{ $process->title }}">
                                                        <div class="work-process-one__item__thumb__number">
                                                            {{ str_pad($process->order, 2, '0', STR_PAD_LEFT) }}
                                                        </div>
                                                    </div>
                                                    <h4 class="work-process-one__item__title">{{ $process->title }}
                                                    </h4>
                                                    <p class="work-process-one__item__text">{{ $process->description }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.work-process-one -->
                    <!-- Call To Action Start -->
                    <div class="cta-one">
                        <div class="container text-center wow fadeInUp animated" data-wow-delay="200ms">
                            <div class="cta-one__author">
                                @php
                                    $cta = $service->ctas->first();
                                    $rawPhone =
                                        optional($cta)->phone ??
                                        (optional(\App\Models\Setting::first())->whatsapp ??
                                            optional(\App\Models\Setting::first())->phone);
                                    $phoneDigits = $rawPhone ? preg_replace('/\D+/', '', $rawPhone) : null;
                                    $waText = 'Olá, gostaria de saber mais sobre ' . $service->name . '!';
                                    $waHref = $phoneDigits
                                        ? 'https://wa.me/' . $phoneDigits . '?text=' . urlencode($waText)
                                        : null;
                                @endphp

                                @if ($waHref)
                                    <a href="{{ $waHref }}" target="_blank"
                                        class="cta-one__icon cta-one__icon--center" rel="noopener">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                @endif
                            </div><!-- /.cta-author -->

                            <div class="section-title">
                                <h5 class="section-title__tagline section-title">vamos tirar sua
                                    ideia do papel</h5>
                                <h2 class="section-title__title">
                                    {{ optional($cta)->title ?? 'Fale com nossa equipe' }}
                                </h2>
                            </div><!-- /.section-title -->
                        </div>
                    </div>
                    <!-- Call To Action End -->

                    <!-- Call To Action End -->
                </div>
            </div>
        </div>
    </div>
</section>
