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
            background: linear-gradient(180deg, rgba(0, 0, 0, .40), rgba(0, 0, 0, .60));
            z-index: 2;
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
            .video-hero__content { min-height: 92vh; }
        }
    </style>
    <div class="video-hero">
        <div class="video-hero__media">
            <video autoplay muted loop playsinline poster="{{ asset('assets/video/MMConnect.mp4') }}">
                <source src="{{ asset($video ?? 'assets/video/MMConnect.mp4') }}" type="video/mp4">
            </video>
        </div>
        <div class="container video-hero__content">
            <div class="row w-100">
                <div class="col-xl-12">
                    <div class="main-slider__two__content text-center">
                        <!-- Conteúdo opcional: pode inserir call to action aqui se quiser -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- social start -->
    <div class="main-slider__socails">
        <a href="https://twitter.com/"><i class="fab fa-twitter"></i></a>
        <a href="https://www.facebook.com/"><i class="fab fa-facebook"></i></a>
        <a href="https://www.pinterest.com/"><i class="fab fa-pinterest-p"></i></a>
        <a href="https://www.instagram.com/"><i class="fab fa-instagram"></i></a>
    </div>
    <!-- social end -->
    <!-- phone start -->
    <div class="main-slider__phone"><a href="tel:+926668880000">+92 666 888 0000</a></div>
    <!-- phone end -->
</section>
<!--Main Slider End-->
