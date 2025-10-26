@extends('layout.layout')

@section('content')
    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>

    <div class="preloader">
        <div class="preloader__image" style="background-image: url(assets/images/mmcriativossite.png);"></div>
    </div>
    <!-- /.preloader -->
    <div class="page-wrapper">
        @include('partials.menu')

        @include('components.slider')

        {{-- @include('components.line') --}}

        @include('components.highlights')

        @include('components.service')

        @include('components.aboutus')

        @include('components.services')

        <!-- Sliding Text Start-->
        <section class="slider-text-one">
            <div class="slider-text-one__animate-text">
                <span>Design <span>com</span> propósito.&nbsp;</span>
                <span>Código <span>com</span> alma.&nbsp;</span>
                <span>Design <span>com</span> propósito.&nbsp;</span>
                <span>Código <span>com</span> alma.&nbsp;</span>
            </div>
        </section>
        <!-- Sliding Text Start-->

        @include('components.skills')

        @include('components.projects')

        @include('components.pricing')

        @include('components.former')

        <footer class="main-footer" style="background-image: url(assets/images/backgrounds/footer-bg-1.png);">
            <div class="container">
                <div class="main-footer__top wow fadeInUp animated" data-wow-delay="100ms">
                    <a href="index.html" class="main-footer__logo">
                        <img src="assets/images/footer-logo.png" alt="ogency" width="55" height="55">
                    </a><!-- /.footer-logo -->
                    <div class="main-footer__social">
                        <a href="https://twitter.com/"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.facebook.com/"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.pinterest.com/"><i class="fab fa-pinterest-p"></i></a>
                        <a href="https://www.instagram.com/"><i class="fab fa-instagram"></i></a>
                    </div><!-- /.footer-social -->
                </div><!-- footer-top -->
                <div class="row">
                    <div class="col-lg-8 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
                        <div class="main-footer__about">
                            <p class="footer-widget__text">Let’s start working together</p>
                            <a href="mailto:help@company.com">help@company.com</a>
                        </div><!-- /.footer-widget -->
                    </div>
                    <div class="col-lg-2 col-md-3 wow fadeInUp animated" data-wow-delay="300ms">
                        <div class="main-footer__navmenu">
                            <ul>
                                <li><a href="about.html">About</a></li>
                                <li><a href="team.html">Meet the Team</a></li>
                                <li><a href="blog-grid-right.html">News & Media</a></li>
                                <li><a href="projects.html">Our Projects</a></li>
                                <li><a href="contact.html">Contact</a></li>
                            </ul><!-- /.list-unstyled -->
                        </div><!-- /.footer-widget -->
                    </div>
                    <div class="col-lg-2 col-md-3 wow fadeInUp animated" data-wow-delay="400ms">
                        <div class="main-footer__navmenu">
                            <ul>
                                <li><a href="faq.html">Our Faqs</a></li>
                                <li><a href="gallery.html">Gallery</a></li>
                                <li><a href="services.html">Services</a></li>
                                <li><a href="contact.html">Help</a></li>
                            </ul><!-- /.list-unstyled -->
                        </div><!-- /.footer-widget -->
                    </div>
                </div><!-- /.row -->
                <p class="main-footer__copyright wow fadeInUp animated" data-wow-delay="500ms">© Copyright <span
                        class="dynamic-year"></span><!-- /.dynamic-year --> by <a href="index.html">Ogency
                        Template</a></p>
            </div><!-- /.container -->
        </footer><!-- /.main-footer -->

    </div><!-- /.page-wrapper -->


    <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <!-- /.mobile-nav__overlay -->
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
            <div class="logo-box">
                <a href="index.html" aria-label="logo image"><img src="assets/images/logo-light.png" width="36"
                        alt="ogency" /></a>
            </div>
            <!-- /.logo-box -->
            <div class="mobile-nav__container"></div>
            <!-- /.mobile-nav__container -->
            <ul class="mobile-nav__contact list-unstyled">
                <li>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:needhelp@packageName__.com">needhelp@finlon.com</a>
                </li>
                <li>
                    <i class="icon-phone-call"></i>
                    <a href="tel:666-888-0000">666 888 0000</a>
                </li>
            </ul><!-- /.mobile-nav__contact -->
            <div class="mobile-nav__social">
                <a href="https://twitter.com/"><i class="fab fa-twitter"></i></a>
                <a href="https://www.facebook.com/"><i class="fab fa-facebook"></i></a>
                <a href="https://www.pinterest.com/"><i class="fab fa-pinterest-p"></i></a>
                <a href="https://www.instagram.com/"><i class="fab fa-instagram"></i></a>
            </div><!-- /.mobile-nav__social -->
        </div>
        <!-- /.mobile-nav__content -->
    </div>
    <!-- /.mobile-nav__wrapper -->


    <div class="search-popup">
        <div class="search-popup__overlay search-toggler"></div>
        <!-- /.search-popup__overlay -->
        <div class="search-popup__content">
            <form action="#">
                <label for="search" class="sr-only">search here</label><!-- /.sr-only -->
                <input type="text" id="search" placeholder="Search Here..." />
                <button type="submit" aria-label="search submit" class="ogency-btn">
                    <i class="icon-magnifying-glass"></i>
                </button>
            </form>
        </div>
        <!-- /.search-popup__content -->
    </div>
    <!-- /.search-popup -->

    <!-- back-to-top-start -->
    <a href="#" class="scroll-top">
        <svg class="scroll-top__circle" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </a>
    <!-- back-to-top-end -->

    @include('components.holo-modal')
@endsection
