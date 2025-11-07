<!-- Why Choose Start -->
<section class="why-choose">
    <div class="container" style="margin-top: 80px;">
        <div class="row">
            <div class="col-lg-5 wow fadeInLeft animated" data-wow-delay="200ms">
                <div class="about-one__content">
                    <!-- about content start-->
                    <div class="section-title">
                        <h5 class="section-title__tagline section-title__tagline--has-dots">
                            {{ $about->subtitle ?? 'Subtítulo' }}
                        </h5>
                        <h2 class="section-title__title">{{ $about->title ?? 'Título' }}</h2>
                    </div><!-- section-title -->
                    <div class="about-one__content__text-one mb-2">
                        {!! $about->description ?? '<p>Descrição.</p>' !!}
                    </div>
                </div><!-- about content end-->
            </div>
            <div class="col-lg-7">
                <div class="why-choose__image">
                    <div class="why-choose__image__shape wow fadeIn animated" data-wow-delay="200ms">
                        <img src="assets/images/resources/why-choose-1-1.png" alt="ogency">
                    </div>
                    <div class="why-choose__image__author wow fadeInRight animated" data-wow-delay="300ms">
                        <img src="{{ isset($about->photo) ? asset($about->photo) : asset('assets/images/resources/marcusm.jpg') }}"
                            alt="Sobre nós">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Why Choose End -->
