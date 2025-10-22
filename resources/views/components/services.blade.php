<section class="service-two ">
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
            "navText": ["<span class=\"icon-left-arrow\"></span>","<span class=\"icon-right-arrow\"></span>"],
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
                        <div class="service-two__item__shape" style="background-image: url(assets/images/backgrounds/service-shape-2.png);"></div>

                        <div class="service-two__item__inner">
                            <div class="service-two__item__hover" style="background-image: url({{ asset($service->thumb ?: ($service->cover ?: 'assets/images/service/services-2-1.jpg')) }});"></div>

                            <div class="service-two__item__icon">
                                @if ($service->icon)
                                    <i class="{{ $service->icon }}"></i>
                                @else
                                    <i class="fa-light fa-cubes"></i>
                                @endif
                            </div>

                            <h3 class="service-two__item__title">
                                <a href="javascript:void(0)" onclick="openContentModal('services','{{ $service->slug }}','{{ addslashes($service->name) }}')">{{ $service->name }}</a>
                            </h3>

                            <p class="service-two__item__text">{{ \Illuminate\Support\Str::limit(strip_tags($service->description), 100) }}</p>

                            <a class="service-two__item__btn" href="javascript:void(0)" onclick="openContentModal('services','{{ $service->slug }}','{{ addslashes($service->name) }}')">
                                <span class="icon-right-arrow"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
