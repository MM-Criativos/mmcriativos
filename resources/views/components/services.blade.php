<style>
    /* 🔹 Seção Serviços - Mobile */
    .service-two-mobile {
        background: #0a0a0a;
        color: #fff;
        padding: 60px 0;
    }

    .service-two-mobile .section-title__tagline {
        color: #ff8800;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .service-two-mobile .section-title__title {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.4;
    }

    .service-two-mobile__item {
        background: #111;
        border-radius: 14px;
        padding: 40px 25px;
        border: 1px solid rgba(255, 136, 0, 0.2);
        box-shadow: 0 0 10px rgba(255, 136, 0, 0.15);
        transition: all 0.3s ease;
    }

    .service-two-mobile__item:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 136, 0, 0.4);
    }

    .service-two-mobile__icon i {
        font-size: 50px;
        color: #ff8800;
    }

    .service-two-mobile__title {
        font-size: 18px;
        font-weight: 600;
        color: #fff;
        margin: 14px 0 10px;
    }

    .service-two-mobile__text {
        font-size: 14px;
        color: #ccc;
        line-height: 1.6;
        margin-bottom: 22px;
    }

    .service-two-mobile__btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ff8800;
        font-weight: 500;
        font-size: 15px;
        text-decoration: none;
        transition: 0.3s ease;
    }

    .service-two-mobile__btn:hover {
        color: #ffa73b;
        transform: translateX(3px);
    }
</style>

<!-- 🔸 VERSÃO DESKTOP/TABLET -->
<section class="service-two d-none d-md-block">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline section-title__tagline--has-dots">nossos serviços</h5>
                    <h2 class="section-title__title">Soluções digitais que <br> impulsionam seu negócio</h2>
                </div>
            </div>
        </div>

        <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
            data-owl-options='{
                "items": 4,
                "margin": 30,
                "smartSpeed": 700,
                "loop": true,
                "autoplay": true,
                "nav": false,
                "dots": true,
                "responsive": {
                    "0": { "items": 1, "margin": 0 },
                    "600": { "items": 2 },
                    "992": { "items": 3 }
                }
            }'>
            @php($services = \App\Models\Service::query()->orderBy('order')->orderBy('name')->get())
            @foreach ($services as $service)
                <div class="item">
                    <div class="service-two__item">
                        <div class="service-two__item__shape"
                            style="background-image: url(assets/images/backgrounds/service-shape-2.png);"></div>

                        <div class="service-two__item__inner">
                            <div class="service-two__item__hover"
                                style="background-image: url({{ asset($service->thumb ?: ($service->cover ?: 'assets/images/service/services-2-1.jpg')) }});">
                            </div>

                            <div class="service-two__item__icon">
                                @if ($service->icon)
                                    <i class="{{ $service->icon }}"></i>
                                @else
                                    <i class="fa-light fa-cubes"></i>
                                @endif
                            </div>

                            <h3 class="service-two__item__title">
                                <a href="javascript:void(0)"
                                    onclick="openContentModal('services','{{ $service->slug }}','{{ addslashes($service->name) }}')">
                                    {{ $service->name }}
                                </a>
                            </h3>

                            <p class="service-two__item__text">
                                {{ \Illuminate\Support\Str::limit(strip_tags($service->description), 100) }}
                            </p>

                            <a class="service-two__item__btn" href="javascript:void(0)"
                                onclick="openContentModal('services','{{ $service->slug }}','{{ addslashes($service->name) }}')">
                                <span class="icon-right-arrow"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 🔹 VERSÃO MOBILE -->
<section class="service-two-mobile d-block d-md-none text-center">
    <div class="container">
        <div class="section-title mb-4">
            <h5 class="section-title__tagline section-title__tagline--has-dots">nossos serviços</h5>
            <h2 class="section-title__title">Soluções digitais que impulsionam seu negócio</h2>
        </div>

        <div class="ogency-owl__carousel owl-theme owl-carousel"
            data-owl-options='{
                "items": 1,
                "margin": 20,
                "loop": true,
                "autoplay": true,
                "dots": true,
                "smartSpeed": 700
            }'>
            @foreach ($services as $service)
                <div class="item">
                    <div class="service-two-mobile__item">
                        <div class="service-two-mobile__icon mb-3">
                            @if ($service->icon)
                                <i class="{{ $service->icon }}"></i>
                            @else
                                <i class="fa-light fa-cubes"></i>
                            @endif
                        </div>
                        <h3 class="service-two-mobile__title">{{ $service->name }}</h3>
                        <p class="service-two-mobile__text">
                            {{ \Illuminate\Support\Str::limit(strip_tags($service->description), 120) }}
                        </p>
                        <a href="javascript:void(0)" class="service-two-mobile__btn"
                            onclick="openContentModal('services','{{ $service->slug }}','{{ addslashes($service->name) }}')">
                            Explorar <span class="icon-down-right"></span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
