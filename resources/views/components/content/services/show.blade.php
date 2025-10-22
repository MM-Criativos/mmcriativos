<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url('{{ asset($service->cover) }}');"></div>
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
                                                                <div class="service-one__item-modal">
                                                                    <h3 class="service-one__item-modal__title">
                                                                        <a href="#">{{ $benefit->title }}</a>
                                                                    </h3>
                                                                    <p class="service-one__item-modal__text">
                                                                        {{ $benefit->subtitle }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 wow fadeInRight animated" data-wow-delay="200ms"
                                    style="margin-top: 50px;">
                                    @if ($service->features && $service->features->count())
                                        @foreach ($service->features as $feature)
                                            <div class="why-choose__box">
                                                <div class="why-choose__box__icon">
                                                    <span class="icon-tick"></span>
                                                </div>
                                                <h3 class="why-choose__box__title">{{ $feature->title }}</h3>
                                                <p class="why-choose__box__text">{{ $feature->subtitle }}</p>
                                            </div><!-- /.why-choose__box -->
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
                                <div class="cta-one__author--wrap">
                                    @php
                                        $cta = $service->ctas->first();
                                        $ctaImg = optional($cta)->image;
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
                                    @if ($ctaImg)
                                        <img src="{{ asset($ctaImg) }}" alt="MM Criativos">
                                    @endif
                                </div>
                                @if ($waHref)
                                    <a href="{{ $waHref }}" target="_blank" class="cta-one__icon" rel="noopener">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                @endif
                            </div><!-- /.cta-author -->

                            <div class="section-title">
                                <h5 class="section-title__tagline section-title__tagline--has-dots">vamos tirar sua
                                    ideia do papel</h5>
                                <h2 class="section-title__title">
                                    {{ optional($cta)->title ?? 'Fale com nossa equipe' }}

                                </h2>
                            </div><!-- /.section-title -->
                        </div>
                    </div>
                    <!-- Call To Action End -->
                </div>
            </div>
        </div>
    </div>
</section>
