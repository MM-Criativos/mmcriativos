@php
    $testimonials = \App\Models\ClientTestimonial::query()
        ->with(['client:id,name', 'contact:id,name,photo,role'])
        ->visible()
        ->latest('id')
        ->take(10)
        ->get();
@endphp

@if ($testimonials->count() > 2)
    <section class="testimonial-three">
        <div class="container">
            <div class="section-title">
                <h5 class="section-title__tagline section-title__tagline--has-dots">
                    A voz de quem confia na gente
                </h5>
                <h2 class="section-title__title">
                    O que dizem sobre a MM Criativos
                </h2>
            </div>

            <div class="testimonial-three__carousel ogency-owl__carousel owl-theme owl-carousel"
                data-owl-options='{
                    "items": 2,
                    "margin": 30,
                    "smartSpeed": 700,
                    "loop": true,
                    "autoplay": true,
                    "nav": true,
                    "dots": false,
                    "navText": ["<span class=\"icon-left-arrow\"></span>", "<span class=\"icon-right-arrow\"></span>"],
                    "responsive": { "0": {"items":1,"margin":0}, "992":{"items":2} }
                }'>
                @foreach ($testimonials as $t)
                    <div class="testimonial-three__item">
                        <div class="testimonial-three__item__border"></div>
                        <h3 class="testimonial-three__item__name" style="margin-top: -20px;">
                            {{ $t->contact->name ?? 'Cliente' }}
                        </h3>
                        <div class="testimonial-three__item__meta__reviews"
                            style="margin-top: -10px; margin-bottom: 10px;">
                            {{ $t->contact->role ?? '' }}
                            <span style="color: #ff8800; font-weight: 800;">
                                {{ $t->client->name ?? '' }}
                            </span>
                        </div>
                        <div class="testimonial-three__item__quote">
                            {{ $t->testimonial }}
                        </div>
                        <div class="testimonial-three__item__meta">
                            <div class="testimonial-three__item__meta__thumb">
                                <img src="{{ $t->photo_url }}" alt="{{ $t->contact->name ?? 'Cliente' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
