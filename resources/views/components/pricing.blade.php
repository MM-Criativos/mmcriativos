<!-- Pricing Start -->
<section class="pricing-one" id="planos">
    @php
        $plans = \App\Models\Plan::with(['service','advantages'])->orderBy('price')->get();
        $byCat = $plans->groupBy('category');
        $setting = \App\Models\Setting::query()->first();

        function wa_link($setting, $serviceName) {
            $base = preg_replace('/\D+/', '', (string)optional($setting)->whatsapp ?: '5511958469546');
            $msg = 'Olá, eu gostaria de entender melhor como funciona a elaboração de ' . $serviceName . '.';
            return 'https://wa.me/' . $base . '?text=' . urlencode($msg);
        }

        function label_for_service($name) {
            return (stripos($name,'Sistema') !== false || stripos($name,'SaaS') !== false)
                ? 'Tudo do anterior <i class="fa-solid fa-circle-plus"></i>'
                : 'Inclui:';
        }
    @endphp
    <div class="container" style="padding-bottom: 60px;">
        <div class="section-title text-center">
            <h5 class="section-title__tagline section-title__tagline--has-dots">nossos planos</h5>
            <h2 class="section-title__title">
                Escolha o tipo de projeto que<br> se encaixa no seu momento
            </h2>
        </div>

        <div class="pricing-one__main-tab-box tabs-box">
            <ul class="tab-buttons list-unstyled">
                <li data-tab="#presenca" class="tab-btn active-btn"><span>PRESENÇA DIGITAL</span></li>
                <li data-tab="#solucoes" class="tab-btn"><span>SOLUÇÕES INTELIGENTES</span></li>
            </ul>

            <div class="tabs-content">

                <!-- Presença Digital -->
                <div class="tab active-tab" id="presenca">
                    <div class="row">
                        @foreach (($byCat['Presença Digital'] ?? collect()) as $i => $plan)
                            <div class="col-lg-4 col-md-6 fadeInUp animated" data-wow-delay="{{ 100 + ($i*100) }}ms">
                                <div class="pricing-one__item text-center" style="height:100%;">
                                    <h4 class="pricing-one__item__title">{{ strtoupper($plan->service->name) }}</h4>
                                    <p class="pricing-one__item__subtitle" style="color:var(--ogency-base);font-weight:600;">A partir de</p>
                                    <h3 class="pricing-one__item__price">{{ $plan->formatted_price }}</h3>
                                    <p class="pricing-one__item__desc">{{ $plan->description }}</p>
                                    <h5 class="pricing-one__item__list-title">{!! label_for_service($plan->service->name) !!}</h5>
                                    <ul class="pricing-one__item__list">
                                        @foreach ($plan->advantages as $adv)
                                            <li><span class="fa fa-check"></span>{{ $adv->title }}</li>
                                        @endforeach
                                    </ul>
                                    <a href="{{ wa_link($setting, $plan->service->name) }}" class="ogency-btn" target="_blank" rel="noopener">Entenda melhor<span class="icon-down-right"></span></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Soluções Inteligentes -->
                <div class="tab" id="solucoes">
                    <div class="row">
                        @foreach (($byCat['Soluções Inteligentes'] ?? collect()) as $i => $plan)
                            <div class="col-lg-4 col-md-6 fadeInUp animated" data-wow-delay="{{ 100 + ($i*100) }}ms">
                                <div class="pricing-one__item text-center" style="height:100%;">
                                    <h4 class="pricing-one__item__title">{{ strtoupper($plan->service->name) }}</h4>
                                    <p class="pricing-one__item__subtitle" style="color:var(--ogency-base);font-weight:600;">A partir de</p>
                                    <h3 class="pricing-one__item__price">{{ $plan->formatted_price }}</h3>
                                    <p class="pricing-one__item__desc">{{ $plan->description }}</p>
                                    <h5 class="pricing-one__item__list-title">{!! label_for_service($plan->service->name) !!}</h5>
                                    <ul class="pricing-one__item__list">
                                        @foreach ($plan->advantages as $adv)
                                            <li><span class="fa fa-check"></span>{{ $adv->title }}</li>
                                        @endforeach
                                    </ul>
                                    <a href="{{ wa_link($setting, $plan->service->name) }}" class="ogency-btn" target="_blank" rel="noopener">Entenda melhor<span class="icon-down-right"></span></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- Pricing End -->
