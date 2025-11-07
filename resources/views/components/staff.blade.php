<!-- Team Details -->
@php
    /**
     * Torna o componente resiliente: se $user não vier do controller,
     * tenta pegar o primeiro usuário aprovado.
     */
    if (!isset($user)) {
        $user = \App\Models\User::where('is_approved', true)->orderBy('id')->first();
    }
    $photoUrl = $user?->photo ? asset($user->photo) : asset('assets/images/team/team-details.jpg');
@endphp

@if ($user)
    <section class="team-details">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 wow fadeInUp animated" data-wow-delay="300ms">
                    <div class="team-details__image">
                        <img src="{{ $photoUrl }}" alt="{{ $user->name }}">
                    </div><!-- /.team-image -->
                </div>
                <div class="col-lg-6 wow fadeInUp animated" data-wow-delay="400ms">
                    <div class="team-details__content">
                        <h3 class="team-details__title">{{ $user->name }}</h3>
                        <span class="team-details__designation">{{ $user->cargo ?? 'Equipe MM Criativos' }}</span>

                        <!-- Redes sociais dinâmicas -->
                        <div class="team-details__social">
                            @foreach ($user->socialMedias as $media)
                                @if (!empty($media->pivot->url))
                                    <a href="{{ $media->pivot->url }}" target="_blank" title="{{ $media->name }}">
                                        <i class="{{ $media->icon }}" style="padding: 15px 0;"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        <!-- Descrição -->
                        <p class="team-details__text">
                            {{ $user->description ?? 'Unindo design, código e propósito, desenvolvemos experiências digitais que transformam ideias em presença real.' }}
                        </p>

                        <!-- Competências -->
                        <h4 class="team-details__heading">Principais competências</h4>
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
                                        @foreach ($user->classes as $uc)
                                            <div class="item">
                                                <div class="benefit-card pixel-card">
                                                    <div class="pixel-card__content front">
                                                        <div class="service-one__item-modal"
                                                            style="position: relative;">
                                                            <span class="holo-pixels" aria-hidden="true"></span>
                                                            <h3 class="service-one__item-modal__title">
                                                                <a href="#">{{ $uc->classe }}</a>
                                                            </h3>
                                                            <p class="service-one__item-modal__text">&nbsp;</p>
                                                        </div>
                                                    </div>
                                                    <div class="pixel-card__content back">
                                                        <div class="service-one__item-modal"
                                                            style="position: relative;">
                                                            <span class="holo-pixels" aria-hidden="true"></span>
                                                            <h3 class="service-one__item-modal__title"
                                                                style="display: none;">&nbsp;</h3>
                                                            <p class="service-one__item-modal__text">
                                                                {{ $uc->description }}</p>
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
            </div>
        </div>
    </section>
@endif
