<!-- Service Start -->
<section class="service-one">
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
                "navText": ["<span class=\"icon-left-arrow\"></span>","<span class=\"icon-right-arrow\"></span>"],
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
                        <p class="service-one__item__text">{{ \Illuminate\Support\Str::limit(strip_tags($skill->description), 100) }}</p>
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
