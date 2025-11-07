<style>
    @media (max-width: 768px) {

        /* Centraliza o card inteiro apenas no mobile */
        .pixel-card {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* Centraliza o conteúdo interno de frente e verso */
        .pixel-card__content .service-one__item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 320px;
            padding: 20px;
        }

        /* Ícone centralizado */
        .service-one__item__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        /* Centraliza título e texto */
        .service-one__item__title,
        .service-one__item__text {
            text-align: center;
        }

        /* Centraliza o botão (Explorar / Voltar) */
        .service-one__item__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 12px;
            text-align: center;
        }

        /* Dá um leve equilíbrio no verso */
        .pixel-card__content.back .service-one__item__text {
            line-height: 1.5;
            max-width: 90%;
        }
    }
</style>

<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div>
</div>
<section class="page-header">
    @php
        $cover = $skill->cover;
        $isVideo =
            $cover &&
            \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                '.mp4',
                '.webm',
                '.ogg',
                '.mov',
            ]);
    @endphp
    @if ($cover)
        @if ($isVideo)
            <video class="page-header__bg" autoplay muted loop playsinline preload="metadata"
                style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85;z-index:1;">
                <source src="{{ asset($cover) }}">
            </video>
        @else
            <div class="page-header__bg" style="background-image: url('{{ asset($cover) }}');"></div>
        @endif
    @else
        <div class="page-header__bg"
            style="background-image: url('{{ asset('assets/images/backgrounds/page-header-bg.jpg') }}');"></div>
    @endif
    <div class="page-header__overlay"></div>
    <div class="container">
        <h2 class="page-header__title">{{ $skill->name }}</h2>
    </div>
    <!-- /.page-header -->
</section>
<section class="about-one d-none d-lg-block">
    <div class="container">
        <div class="row align-items-center">
            <!-- Imagem -->
            <div class="col-lg-6">
                <div class="about-one__thumb wow fadeInLeft animated" data-wow-delay="300ms">
                    <div class="about-one__thumb__round--top"></div>
                    <div class="about-one__thumb__img">
                        @php($info = optional($skill->info))
                        @php($img = $info->image ? asset($info->image) : asset('assets/images/resources/about-1-1.jpg'))
                        <img src="{{ $img }}" alt="{{ $skill->name }}">
                    </div>
                    <div class="about-one__thumb__round--bottom"></div>
                </div>
            </div>

            <!-- Texto -->
            <div class="col-lg-6">
                <div class="about-one__content">
                    <div class="section-title">
                        <h5 class="section-title__tagline section-title__tagline--has-dots">
                            {{ $info->subtitle ?? '' }}
                        </h5>
                        <h2 class="section-title__title">
                            {{ $info->title ?? '' }}
                        </h2>
                    </div>
                    <p class="about-one__content__text-one">
                        {!! isset($info->description) ? nl2br(e($info->description)) : '' !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-one d-block d-lg-none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="about-one__content text-center">
                    <div class="section-title mb-3">
                        <h5 class="section-title__tagline section-title__tagline--has-dots">
                            {{ $info->subtitle ?? '' }}
                        </h5>
                        <h2 class="section-title__title">
                            {{ $info->title ?? '' }}
                        </h2>
                    </div>

                    <!-- Imagem vem após o título -->
                    <div class="about-one__thumb wow fadeInUp animated mb-4" data-wow-delay="200ms">
                        <div class="about-one__thumb__img">
                            @php($info = optional($skill->info))
                            @php($img = $info->image ? asset($info->image) : asset('assets/images/resources/about-1-1.jpg'))
                            <img src="{{ $img }}" alt="{{ $skill->name }}" class="img-fluid rounded">
                        </div>
                    </div>

                    <!-- Descrição -->
                    <p class="about-one__content__text-one">
                        {!! isset($info->description) ? nl2br(e($info->description)) : '' !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="service-one">
    <div class="container">
        <style>
            /* Pixel swap card (sem flip 3D) */
            .pixel-card {
                position: relative;
                min-height: 320px;
            }

            .pixel-card__content {
                position: absolute;
                inset: 0;
                height: 100%;
                transition: opacity .18s linear, visibility .18s linear;
            }

            .pixel-card__content.front {
                opacity: 1;
                visibility: visible;
            }

            .pixel-card__content.back {
                opacity: 0;
                visibility: hidden;
            }

            .pixel-card.is-details .pixel-card__content.front {
                opacity: 0;
                visibility: hidden;
            }

            .pixel-card.is-details .pixel-card__content.back {
                opacity: 1;
                visibility: visible;
            }

            .pixel-card .service-one__item {
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                border-radius: 14px;
            }

            .pixel-card .service-one__item__title {
                margin-bottom: 8px;
            }

            .pixel-card .holo-pixels {
                position: absolute;
                inset: -2px;
                border-radius: 14px;
                pointer-events: none;
                opacity: 0;
                mix-blend-mode: screen;
                background-image: radial-gradient(rgba(255, 136, 0, .35) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 136, 0, .15) 0%, transparent 60%);
                background-size: 4px 4px, 100% 100%;
                filter: blur(.2px);
            }

            /* Efeitos de desmontar/montar em pixels */
            .pixel-card.is-opening .holo-pixels {
                animation: pixDisperse .5s ease-out both;
            }

            .pixel-card.is-closing .holo-pixels {
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

            /* GSAP swarm particles (overlay) */
            .px-swarm {
                position: absolute;
                inset: 0;
                pointer-events: none;
                overflow: hidden;
                border-radius: 14px;
                z-index: 3;
                will-change: transform;
            }

            .px-swarm .px {
                position: absolute;
                width: 3px;
                height: 3px;
                background: rgba(255, 136, 0, .95);
                box-shadow: 0 0 10px rgba(255, 136, 0, 1), 0 0 18px rgba(255, 136, 0, .8);
                border-radius: 1px;
                opacity: 0;
                mix-blend-mode: screen;
                will-change: transform, opacity, filter;
            }

            /* Mosaic grid overlay built from a snapshot of the face */
            .px-grid {
                position: absolute;
                inset: 0;
                pointer-events: none;
                border-radius: 14px;
                overflow: hidden;
            }

            .px-tile {
                position: absolute;
                will-change: transform, opacity, filter;
                background-repeat: no-repeat;
                mix-blend-mode: screen;
                filter: brightness(1) saturate(1);
                border-radius: 1px;
            }
        </style>
        <div class="row">
            <div class="col-md-12">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline section-title__tagline--has-dots">
                        tecnologia, performance e propósito
                    </h5>
                    <h2 class="section-title__title">
                        Nossas competências moldam resultados reais
                    </h2>

                </div><!-- section-title -->
            </div>
        </div>
        <!-- Grid (desktop/tablet): 1 card por competência da skill atual -->
        <div class="row d-none d-md-flex">
            @forelse($skill->competencies as $index => $comp)
                @php($compIcon = trim($comp->icon_class ?: ($comp->icon ?: $skill->icon_class ?? 'icon-digital-services')))
                <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="{{ ($index + 1) * 100 }}ms">
                    <div class="pixel-card" style="max-height: 200px; margin-bottom:30px;">
                        <!-- Frente -->
                        <div class="pixel-card__content front">
                            <div class="service-one__item" style="margin-bottom:30px; position:relative;">
                                <span class="holo-pixels" aria-hidden="true"></span>
                                <div class="service-one__item__icon"><span class="{{ $compIcon }}"></span></div>
                                <h3 class="service-one__item__title" style="max-height: 20px;"><a
                                        href="javascript:void(0)">{{ $comp->competency }}</a></h3>
                                <p class="service-one__item__text"style="min-height: 110px;"></p>
                                <a class="service-one__item__btn js-details-open" style="max-heigh: 20px;"
                                    href="javascript:void(0)">Explorar
                                    <span class="icon-down-right"></span></a>
                            </div>
                        </div>
                        <!-- Verso (detalhe) -->
                        <div class="pixel-card__content back">
                            <div class="service-one__item" style="margin-bottom:30px; position:relative;">
                                <span class="holo-pixels" aria-hidden="true"></span>
                                <div class="service-one__item__icon"><span class="{{ $compIcon }}"></span></div>
                                <!-- sem título no verso; apenas descrição -->
                                <p class="service-one__item__text" style="min-height: 130px;">
                                    {{ \Illuminate\Support\Str::limit((string) ($comp->description ?? ''), 180) ?: 'Em breve mais detalhes.' }}
                                </p>
                                <a class="service-one__item__btn js-details-close" href="javascript:void(0)">Voltar
                                    <span class="icon-left-arrow"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p>Sem competências cadastradas.</p>
                </div>
            @endforelse
        </div>

        <!-- Carousel (mobile): 1 card por competência -->
        <div class="d-md-none">
            <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                data-owl-options='{
                "items": 1,
                "margin": 10,
                "smartSpeed": 700,
                "loop": true,
                "autoplay": true,
                "nav": false,
                "dots": true
            }'>
                @forelse($skill->competencies as $comp)
                    @php($compIcon = trim($comp->icon_class ?: ($comp->icon ?: $skill->icon_class ?? 'icon-digital-services')))
                    <div class="item">
                        <div class="pixel-card">
                            <div class="pixel-card__content front">
                                <div class="service-one__item" style="position: relative;">
                                    <span class="holo-pixels" aria-hidden="true"></span>
                                    <div class="service-one__item__icon"><span class="{{ $compIcon }}"></span>
                                    </div>
                                    <h3 class="service-one__item__title"><a
                                            href="javascript:void(0)">{{ $comp->competency }}</a></h3>
                                    <p class="service-one__item__text">&nbsp;</p>
                                    <a class="service-one__item__btn js-details-open"
                                        href="javascript:void(0)">Explorar
                                        <span class="icon-down-right"></span></a>
                                </div>
                            </div>
                            <div class="pixel-card__content back">
                                <div class="service-one__item" style="position: relative;">
                                    <span class="holo-pixels" aria-hidden="true"></span>
                                    <div class="service-one__item__icon"><span class="{{ $compIcon }}"></span>
                                    </div>
                                    <p class="service-one__item__text">
                                        {{ \Illuminate\Support\Str::limit((string) ($comp->description ?? ''), 180) ?: 'Em breve mais detalhes.' }}
                                    </p>
                                    <a class="service-one__item__btn js-details-close"
                                        href="javascript:void(0)">Voltar
                                        <span class="icon-left-arrow"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="item">
                        <p class="text-center">Nenhuma habilidade cadastrada.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
