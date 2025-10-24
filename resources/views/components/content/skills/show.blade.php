<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div>
</div>
<section class="page-header">
    <div class="page-header__bg"
        style="background-image: url('{{ $skill->cover ? asset($skill->cover) : asset('assets/images/backgrounds/page-header-bg.jpg') }}');">
    </div>
    <div class="page-header__overlay"></div>
    <div class="container">
        <h2 class="page-header__title">{{ $skill->name }}</h2>
        <p class="about-one__content__text-one">{!! nl2br(e($skill->description)) !!}</p>

    </div>
    <!-- /.page-header -->
</section>
<section class="about-one">

    <div class="row">
        <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
            <div class="award-one__item">
                <div class="award-one__item__year">2012</div><!-- /.award-year -->
                <h4 class="award-one__item__title">Best developers</h4><!-- /.award-title -->
                <p class="award-one__item__text">Quisqu tell us risus sid adpis viera bibe um urna.</p>
                <!-- /.award-text -->
                <div class="award-one__item__thumb">
                    <img src="assets/images/resources/award-icon.png" alt="ogency">
                </div><!-- /.award-image -->
            </div><!-- /.award-item -->
            <div class="award-one__item">
                <div class="award-one__item__year">2018</div><!-- /.award-year -->
                <h4 class="award-one__item__title">Quality design</h4><!-- /.award-title -->
                <p class="award-one__item__text">Quisqu tell us risus sid adpis viera bibe um urna.</p>
                <!-- /.award-text -->
                <div class="award-one__item__thumb">
                    <img src="assets/images/resources/award-icon.png" alt="ogency">
                </div><!-- /.award-image -->
            </div><!-- /.award-item -->
        </div>
        <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="500ms">
            <div class="award-one__trophy"><img src="assets/images/resources/award.png" alt="ogency"></div>
            <!-- /.Trophy-image -->
        </div>
        <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="300ms">
            <div class="award-one__item award-one__item--ml">
                <div class="award-one__item__year">2015</div><!-- /.award-year -->
                <h4 class="award-one__item__title">Marketing expert</h4><!-- /.award-title -->
                <p class="award-one__item__text">Quisqu tell us risus sid adpis viera bibe um urna.</p>
                <!-- /.award-text -->
                <div class="award-one__item__thumb">
                    <img src="assets/images/resources/award-icon.png" alt="ogency">
                </div><!-- /.award-image -->
            </div><!-- /.award-item -->
            <div class="award-one__item award-one__item--ml">
                <div class="award-one__item__year">2020</div><!-- /.award-year -->
                <h4 class="award-one__item__title">Client choice</h4><!-- /.award-title -->
                <p class="award-one__item__text">Quisqu tell us risus sid adpis viera bibe um urna.</p>
                <!-- /.award-text -->
                <div class="award-one__item__thumb">
                    <img src="assets/images/resources/award-icon.png" alt="ogency">
                </div><!-- /.award-image -->
            </div><!-- /.award-item -->
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
                    <h5 class="section-title__tagline section-title__tagline--has-dots">what we’re offering</h5>
                    <h2 class="section-title__title">Services we’re providing<br> to our customers</h2>
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
