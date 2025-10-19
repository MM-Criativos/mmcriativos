<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<section class="page-header">
    <div class="page-header__bg__landing-page"></div>
    <!-- /.page-header__bg -->
    <div class="page-header__overlay"></div>
    <!-- /.page-header__bg -->
    <div class="container">
        <h2 class="page-header__title">Portfolio</h2><!-- /.page-title -->
    </div><!-- /.container -->
</section><!-- /.page-header -->
<!-- Projects Details Start -->
<section class="project-details">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-7 wow fadeInLeft animated" data-wow-delay="300ms">
                <div class="project-details__content">
                    <!-- 🟠 Resumo do Projeto -->
                    <h3 class="project-details__content__title">Resumo do Projeto</h3>
                    <p class="project-details__content__text">
                        <!-- Texto dinâmico: descrição geral do projeto -->
                        {{ $projeto->resumo ?? '' }}
                    </p>

                    <!-- 🔵 Desafios -->
                    <h4 class="project-details__content__subtitle">Desafios do Projeto</h4>
                    <ul class="project-details__content__list list-unstyled">
                        @foreach ($projeto->desafios ?? [] as $desafio)
                            <li>
                                <span class="fa fa-exclamation-circle"></span>
                                {{ $desafio }}
                            </li>
                        @endforeach
                    </ul>

                    <!-- 🟢 Soluções -->
                    <h4 class="project-details__content__subtitle">Soluções Propostas</h4>
                    <ul class="project-details__content__list list-unstyled">
                        @foreach ($projeto->solucoes ?? [] as $solucao)
                            <li>
                                <span class="fa fa-lightbulb"></span>
                                {{ $solucao }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5 wow fadeInRight animated" data-wow-delay="400ms">
                <div class="project-details__right">
                    <ul class="project-details__info-list list-unstyled">
                        <li>
                            <span>Cliente:</span>
                            {{ $projeto->cliente_nome ?? '—' }}
                        </li>
                        <li>
                            <span>Data de Entrega:</span>
                            {{ $projeto->data_entrega ? $projeto->data_entrega->format('d/m/Y') : '—' }}
                        </li>
                        <li>
                            <span>Serviço:</span>
                            {{ $projeto->servico ?? '—' }}
                        </li>
                        <li>
                            <span>Localização:</span>
                            {{ $projeto->localizacao ?? '—' }}
                        </li>
                    </ul>

                    @if (!empty($projeto->redes_sociais))
                        <div class="project-details__socials">
                            @foreach ($projeto->redes_sociais as $rede => $url)
                                @if ($url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener">
                                        <i class="fab fa-{{ $rede }}"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>


        </div><!-- /.project-details-content -->
        <h3 class="project-details__content__title">Product development</h3>
        <div class="gallery-page gallery-page__padding">
            <div class="container">
                <div class="gallery-page__carousel ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                    data-owl-options='{
                    "items": 4,
                    "margin": 10,
                    "smartSpeed": 700,
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
                            "items": 3
                        },
                        "1200":{
                            "items": 4
                        }
                    }
                    }'>
                    <!-- gallery-item-start -->
                    <div class="item">
                        <div class="gallery-page__single">
                            <img src="assets/images/gallery/gallery-1.jpg" alt="ogency">
                            <div class="gallery-page__icon">
                                <a class="img-popup" href="assets/images/gallery/gallery-1.jpg"><span
                                        class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                    <!-- gallery-item-end -->
                    <!-- gallery-item-start -->
                    <div class="item">
                        <div class="gallery-page__single">
                            <img src="assets/images/gallery/gallery-3.jpg" alt="ogency">
                            <div class="gallery-page__icon">
                                <a class="img-popup" href="assets/images/gallery/gallery-3.jpg"><span
                                        class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                    <!-- gallery-item-end -->
                    <!-- gallery-item-start -->
                    <div class="item">
                        <div class="gallery-page__single">
                            <img src="assets/images/gallery/gallery-4.jpg" alt="ogency">
                            <div class="gallery-page__icon">
                                <a class="img-popup" href="assets/images/gallery/gallery-4.jpg"><span
                                        class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                    <!-- gallery-item-end -->
                    <!-- gallery-item-start -->
                    <div class="item">
                        <div class="gallery-page__single">
                            <img src="assets/images/gallery/gallery-5.jpg" alt="ogency">
                            <div class="gallery-page__icon">
                                <a class="img-popup" href="assets/images/gallery/gallery-5.jpg"><span
                                        class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                    <!-- gallery-item-end -->
                    <!-- gallery-item-start -->
                    <div class="item">
                        <div class="gallery-page__single">
                            <img src="assets/images/gallery/gallery-6.jpg" alt="ogency">
                            <div class="gallery-page__icon">
                                <a class="img-popup" href="assets/images/gallery/gallery-6.jpg"><span
                                        class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                    <!-- gallery-item-end -->
                    <!-- gallery-item-start -->
                    <div class="item">
                        <div class="gallery-page__single">
                            <img src="assets/images/gallery/gallery-9.jpg" alt="ogency">
                            <div class="gallery-page__icon">
                                <a class="img-popup" href="assets/images/gallery/gallery-9.jpg"><span
                                        class="icon-plus"></span></a>
                            </div>
                        </div>
                    </div>
                    <!-- gallery-item-end -->
                </div>
            </div>
        </div>
        <!-- Call To Action Start -->
        <div class="cta-two">
            <div class="cta-two__bg" style="background-image: url(assets/images/backgrounds/cta-bg-2.jpg);"></div>
            <div class="section-title text-center" style="margin-bottom: 40px; margin-top: 0px !important;">
                <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
                <h2 class="section-title__title">Conheça alguns dos trabalhos<br> que deram vida a grandes ideias
                </h2>
            </div><!-- /.project-section title -->
            <div class="container">

                <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                    data-owl-options='{
                    "items": 4,
                    "margin": 30,
                    "smartSpeed": 700,
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
                            "items": 4
                        }
                    }
                    }'>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-digital-services"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="website-development.html">Website development</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="website-development.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-graphic-design"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="graphic-designing.html">Graphic designing</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="graphic-designing.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-technology"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="digital-marketing.html">Digital marketing</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="digital-marketing.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-mobile-app"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="apps-development.html">Apps development</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="apps-development.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-digital-services"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="website-development.html">Website development</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="website-development.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-graphic-design"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="graphic-designing.html">Graphic designing</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="graphic-designing.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-technology"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="digital-marketing.html">Digital marketing</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="digital-marketing.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="item">
                        <div class="service-one__item">
                            <div class="service-one__item__icon">
                                <span class="icon-mobile-app"></span>
                            </div><!-- /.service-icon -->
                            <h3 class="service-one__item__title">
                                <a href="apps-development.html">Apps development</a>
                            </h3><!-- /.service-title -->
                            <p class="service-one__item__text">Providing the solutions for your all business</p>
                            <!-- /.service-content -->
                            <a class="service-one__item__btn" href="apps-development.html">Read More<span
                                    class="icon-down-right"></span></a><!-- /.service-read-more -->
                        </div><!-- /.service-card-one -->
                    </div>
                </div>
            </div>
        </div>
        <!-- Call To Action End -->
        <div class="video-one">
            <div class="section-title text-center" style="margin-bottom: 40px; margin-top: 40px !important;">
                <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
                <h2 class="section-title__title">Conheça alguns dos trabalhos<br> que deram vida a grandes ideias
                </h2>
            </div><!-- /.project-section title -->
            <div class="container">
                <div class="video-one__banner wow fadeInUp animated animated" data-wow-delay="100ms">
                    <img src="assets/images/backgrounds/video-bg-1-1.jpg" alt="ogency">
                    <div class="video-one__banner__shape wow fadeInRight animated animated" data-wow-delay="300ms">
                        <img src="assets/images/backgrounds/video-bg-shape-1-1.png" alt="ogency">
                    </div>
                    <!-- curved-circle start-->
                    <div class="video-one__banner__curved-circle-box wow fadeInUp animated animated"
                        data-wow-delay="400ms">
                        <div class="curved-circle">
                            <span class="curved-circle-item">
                                Watch&emsp;Our&emsp;agency&emsp;portfolio&emsp;Video
                            </span>
                        </div>
                        <!-- video btn start -->
                        <a href="https://www.youtube.com/watch?v=Get7rqXYrbQ" class="video-popup">
                            <span class="fa fa-play"></span>
                        </a>
                        <!-- video btn end -->
                    </div>
                    <!-- curved-circle end-->
                </div>
            </div>
        </div>
        <div class="section-title text-center wow fadeInUp animated" data-wow-delay="400ms">
            <h5 class="section-title__tagline section-title__tagline--has-dots">our work showcase</h5>
            <h2 class="section-title__title">Explore similar portfolio<br> you might like it</h2>
        </div><!-- related-project-section-title -->
        <div class="row">
            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
                <div class="project-two__item">
                    <div class="project-two__item__image"><img src="assets/images/project/project-2-1.jpg"
                            alt="ogency"></div><!-- /.project-image -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats"><a href="projects.html">Digital</a>, <a
                                href="projects.html">Agency</a></p><!-- /.project-category -->
                        <h3 class="project-two__item__content__title"><a href="project-details.html">Asus
                                marketing</a>
                        </h3><!-- /.project-title -->
                    </div>
                </div><!-- /.project-item-two -->
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="300ms">
                <div class="project-two__item">
                    <div class="project-two__item__image"><img src="assets/images/project/project-2-4.jpg"
                            alt="ogency"></div><!-- /.project-image -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats"><a href="projects.html">Digital</a>, <a
                                href="projects.html">Agency</a></p><!-- /.project-category -->
                        <h3 class="project-two__item__content__title"><a href="project-details.html">Asus
                                marketing</a>
                        </h3><!-- /.project-title -->
                    </div>
                </div><!-- /.project-item-two -->
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="400ms">
                <div class="project-two__item">
                    <div class="project-two__item__image"><img src="assets/images/project/project-2-5.jpg"
                            alt="ogency"></div><!-- /.project-image -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats"><a href="projects.html">Digital</a>, <a
                                href="projects.html">Agency</a></p><!-- /.project-category -->
                        <h3 class="project-two__item__content__title"><a href="project-details.html">Asus
                                marketing</a>
                        </h3><!-- /.project-title -->
                    </div>
                </div><!-- /.project-item-two -->
            </div>
        </div>
    </div>
</section>
<!-- Projects Details End -->
