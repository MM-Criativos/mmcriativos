<style>
    /* 🔹 Seção de Habilidades - Mobile */
    .service-one-mobile {
        background: #0a0a0a;
        padding: 60px 0;
        color: #fff;
    }

    .service-one-mobile .section-title__tagline {
        color: #ff8800;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .service-one-mobile .section-title__title {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.4;
    }

    .service-one-mobile__item {
        background: #111;
        border-radius: 12px;
        padding: 35px 25px;
        border: 1px solid rgba(255, 136, 0, 0.2);
        box-shadow: 0 0 10px rgba(255, 136, 0, 0.1);
        transition: all 0.3s ease;
    }

    .service-one-mobile__item:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 136, 0, 0.4);
    }

    .service-one-mobile__icon i {
        font-size: 48px;
        color: #ff8800;
    }

    .service-one-mobile__title {
        font-size: 18px;
        font-weight: 600;
        margin: 12px 0 8px;
        color: #fff;
    }

    .service-one-mobile__text {
        font-size: 14px;
        line-height: 1.6;
        color: #ccc;
        margin-bottom: 20px;
    }

    .service-one-mobile__btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ff8800;
        font-weight: 500;
        font-size: 15px;
        text-decoration: none;
        transition: 0.3s ease;
    }

    .service-one-mobile__btn:hover {
        color: #ffa73b;
        transform: translateX(3px);
    }
</style>

<!-- 🔸 VERSÃO DESKTOP/TABLET -->
<section class="service-one d-none d-md-block">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline section-title__tagline--has-dots">Do código à criação</h5>
                    <h2 class="section-title__title">
                        Nossas habilidades moldam<br> o futuro das experiências digitais
                    </h2>
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
                    "992": { "items": 4 }
                }
            }'>
            @php $skills = \App\Models\Skill::query()->orderBy('id')->get(); @endphp
            @foreach ($skills as $skill)
                @php $iconClasses = trim($skill->icon_class ?: ($skill->icon ?: 'fa-light fa-code')); @endphp
                <div class="item">
                    <div class="service-one__item">
                        <div class="service-one__item__icon">
                            <i class="{{ $iconClasses }}"></i>
                        </div>
                        <h3 class="service-one__item__title">
                            <a href="javascript:void(0)"
                                onclick='openContentModal("skills", @json($skill->slug), @json($skill->name))'>
                                {{ $skill->name }}
                            </a>
                        </h3>
                        <p class="service-one__item__text">
                            {{ \Illuminate\Support\Str::limit(strip_tags($skill->description), 100) }}</p>
                        <a class="service-one__item__btn" href="javascript:void(0)"
                            onclick='openContentModal("skills", @json($skill->slug), @json($skill->name))'>
                            Explorar <span class="icon-down-right"></span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 🔹 VERSÃO MOBILE -->
<section class="service-one-mobile d-block d-md-none text-center">
    <div class="container">
        <div class="section-title mb-4">
            <h5 class="section-title__tagline section-title__tagline--has-dots">Do código à criação</h5>
            <h2 class="section-title__title">Nossas habilidades moldam o futuro das experiências digitais</h2>
        </div>

        <div class="ogency-owl__carousel owl-theme owl-carousel"
            data-owl-options='{
                "items": 1,
                "margin": 15,
                "loop": true,
                "autoplay": true,
                "dots": true,
                "smartSpeed": 700
            }'>
            @foreach ($skills as $skill)
                @php $iconClasses = trim($skill->icon_class ?: ($skill->icon ?: 'fa-light fa-code')); @endphp
                <div class="item">
                    <div class="service-one-mobile__item">
                        <div class="service-one-mobile__icon mb-3">
                            <i class="{{ $iconClasses }}"></i>
                        </div>
                        <h3 class="service-one-mobile__title">{{ $skill->name }}</h3>
                        <p class="service-one-mobile__text">
                            {{ \Illuminate\Support\Str::limit(strip_tags($skill->description), 120) }}
                        </p>
                        <a href="javascript:void(0)" class="service-one-mobile__btn"
                            onclick='openContentModal("skills", @json($skill->slug), @json($skill->name))'>
                            Explorar <span class="icon-down-right"></span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
