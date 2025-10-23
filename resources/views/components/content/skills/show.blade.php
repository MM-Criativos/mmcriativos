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
    </div>
    <!-- /.page-header -->
</section>

<section class="services-details">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 wow fadeInUp animated" data-wow-delay="200ms">
                <div class="services-details__content">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="section-title">
                                <h5 class="section-title__tagline section-title__tagline--has-dots">Habilidade</h5>
                                <h2 class="section-title__title">{{ $skill->name }}</h2>
                            </div>
                            <p class="why-choose-two__left--text">{!! nl2br(e($skill->description)) !!}</p>
                        </div>
                        <div class="col-lg-6">
                            <div class="section-title">
                                <h5 class="section-title__tagline section-title__tagline--has-dots">Competências</h5>
                                <h2 class="section-title__title">O que dominamos</h2>
                            </div>
                            <ul class="list-unstyled" style="columns: 2; gap: 24px;">
                                @forelse ($skill->competencies as $comp)
                                    <li style="margin-bottom:8px">{{ $comp->competency }}</li>
                                @empty
                                    <li>Sem competências cadastradas.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-one">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="about-one__thumb wow fadeInLeft animated" data-wow-delay="300ms">
                    <!-- about thumb start -->
                    <div class="about-one__thumb__round--top"></div>
                    <div class="about-one__thumb__img">
                        <img src="assets/images/resources/about-1-1.jpg" alt="ogency">
                    </div>
                    <div class="about-one__thumb__round--bottom"></div>
                </div><!-- about thumb end -->
            </div>
            <div class="col-lg-6">
                <div class="about-one__content">
                    <!-- about content start-->
                    <div class="section-title">
                        <h5 class="section-title__tagline section-title__tagline--has-dots">about the agency
                        </h5>
                        <h2 class="section-title__title">We’re top notch award winning web design agency</h2>
                    </div><!-- section-title -->
                    <p class="about-one__content__text-one">There are many variations of simply free text
                        passages of available but the majority have suffered alteration in some form, by
                        injected hum randomised words which don't slightly.</p>
                    <p class="about-one__content__text-two">Sed ut perspiciatis unde omnis iste natus error
                        voluptatem accusantium doloremque laudantium totam reme aperiam.</p>
                    <a href="about.html" class="ogency-btn">Discover More</a>
                </div><!-- about content end-->
            </div>
        </div>
    </div>
</section>

<section class="service-one">
    <div class="container">
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
                <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="{{ ($index + 1) * 100 }}ms">
                    <div class="service-one__item" style="margin-bottom: 30px;">
                        <div class="service-one__item__icon">
                            @php($compIcon = trim($comp->icon_class ?: ($comp->icon ?: ($skill->icon_class ?? 'icon-digital-services'))))
                            <span class="{{ $compIcon }}"></span>
                        </div>
                        <h3 class="service-one__item__title" style="height: 60px;">
                            <a href="javascript:void(0)">{{ $comp->competency }}</a>
                        </h3>
                        <p class="service-one__item__text" style="min-height: 80px;">&nbsp;</p>
                        <a class="service-one__item__btn" href="javascript:void(0)"
                            onclick='openContentModal("skills", @json($skill->slug), @json($skill->name))'>
                            Explorar <span class="icon-down-right"></span>
                        </a>
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
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                @php($compIcon = trim($comp->icon_class ?: ($comp->icon ?: ($skill->icon_class ?? 'icon-digital-services'))))
                                <span class="{{ $compIcon }}"></span>
                            </div>
                            <h3 class="service-one__item__title">
                                <a href="javascript:void(0)">{{ $comp->competency }}</a>
                            </h3>
                            <p class="service-one__item__text">&nbsp;</p>
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
