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
            .video-hero__content {
                min-height: 92vh;
            }
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
    @php
        $setting = \App\Models\Setting::query()->first();
        $socials = [
            'instagram' => ['icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
            'x' => ['icon' => 'fa-brands fa-x-twitter', 'label' => 'X'],
            'whatsapp' => ['icon' => 'fa-brands fa-whatsapp', 'label' => 'WhatsApp'],
            'linkedin' => ['icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
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
    <!-- phone start -->
    <div class="main-slider__phone"><a class="glass-pill" href="tel:+926668880000"><i class="fa-brands fa-github"></i>
            <span>+92 666 888 0000</span></a></div>
    <!-- phone end -->
</section>
<!--Main Slider End-->

<style>
    .diferencials {
        background-color: #000;
        padding: 50px;
    }
</style>

<section class="diferencials">
    <div class="container">
        <div>
            <div class="col-md-12">
                <div class="section-title">
                    <h2 class="section-title__title">Explore our best recently<br> completed projects</h2>
                </div><!-- section-title -->
            </div>
            <div class="col-md-12">
                <div class="section-title">
                    <h2 class="section-title__title">Explore our best recently<br> completed projects</h2>
                </div><!-- section-title -->
            </div>
            <div class="col-md-12">
                <div class="section-title">
                    <h2 class="section-title__title">Explore our best recently<br> completed projects</h2>
                </div><!-- section-title -->
            </div>
        </div>
    </div>
</section>
