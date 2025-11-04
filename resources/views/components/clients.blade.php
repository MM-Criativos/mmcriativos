@php
    $clientLogos = \App\Models\Client::query()
        ->whereNotNull('logo')
        ->orderBy('id')
        ->get(['id', 'logo']);
@endphp

@if ($clientLogos->count() > 6)
    <!-- Clients Start -->
    <div class="client-carousel client-carousel--about">
        <div class="container">
            <h5 class="client-carousel__tilte"><span>Clientes que confiam em nosso trabalho</span></h5>
            <div class="client-carousel__one ogency-owl__carousel owl-theme owl-carousel"
                data-owl-options='{
                    "items": 5,
                    "margin": 120,
                    "smartSpeed": 700,
                    "loop":true,
                    "autoplay": 6000,
                    "nav":false,
                    "dots":false,
                    "navText": ["<span class=\"fa fa-angle-left\"></span>","<span class=\"fa fa-angle-right\"></span>"],
                    "responsive":{
                        "0":{"items":1,"margin":0},
                        "360":{"items":2,"margin":0},
                        "575":{"items":3,"margin":0},
                        "768":{"items":4,"margin":0},
                        "992":{"items":5,"margin":0},
                        "1200":{"items":5,"margin":120}
                    }
                }'>
                @foreach ($clientLogos as $c)
                    <div class="client-carousel__one__item">
                        <img src="{{ asset('storage/' . $c->logo) }}" alt="{{ 'Logo ' . $c->id }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
