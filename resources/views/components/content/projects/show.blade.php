<style>
    .feature-one__item__img {
        width: 100%;
        height: 280px;
        /* altura padrão do card */
        border-radius: 8px;
        overflow: hidden;
        background-color: #e5e5e5;
        /* fundo padrão */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* 🔸 deixa o <img> sempre preencher todo o container */
    .feature-one__item__img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }

    /* 🔸 se não tiver imagem, usa estilo de placeholder */
    .feature-one__item__img img[src*="placeholder"],
    .feature-one__item__img img[src$=".jpg"]:not([src*="storage"]) {
        object-fit: contain;
        background-color: #e5e5e5;
        color: #000;
        font-size: 16px;
        opacity: 0.8;
    }

    /* === Equalizar altura dos cards de Skills no carrossel === */
    #project-skills .ogency-owl__carousel .owl-stage {
        display: flex !important;
        /* linhas flexíveis para igualar altura */
        align-items: stretch !important;
        /* todos com altura do mais alto */
    }

    #project-skills .ogency-owl__carousel .owl-item {
        display: flex !important;
        /* permite o card ocupar 100% da altura */
    }

    #project-skills .service-one__item {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    #project-skills .service-one__item__title {
        margin-bottom: 10px;
    }

    #project-skills .service-one__item__text {
        flex: 1 1 auto;
    }
</style>

<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<section class="page-header">
    <div class="page-header__bg" style="background-image: url('{{ asset($project->cover) }}');"></div>
    <!-- /.page-header__bg -->
    <div class="page-header__overlay"></div>
    <!-- /.page-header__bg -->
    <div class="container">
        <h2 class="page-header__title">{{ $project->name }}</h2><!-- /.page-title -->
    </div><!-- /.container -->
</section><!-- /.page-header -->
<!-- Projects Details Start -->
<section class="project-details">
    <div class="container">
        <div class="row">
            <!-- 🟠 Resumo do Projeto (coluna completa) -->
            <div class="col-12 wow fadeInUp animated" data-wow-delay="200ms">
                <div class="project-details__content">
                    <h3 class="project-details__content__title">Resumo do projeto</h3>
                    <p class="project-details__content__text">
                        {{ $project->summary }}
                    </p>
                </div>
            </div>

            <!-- 🔵 Desafios e Soluções (lado esquerdo) -->
            <div class="col-xl-8 col-lg-7 wow fadeInLeft animated" data-wow-delay="300ms">
                <div class="project-details__content">

                    <!-- Desafios -->
                    <h4 class="project-details__content__subtitle mt-4">Desafios do Projeto</h4><br>
                    <ul class="project-details__content__list list-unstyled">
                        @foreach ($project->challenges ?? [] as $challenge)
                            <li>
                                <span class="fa fa-exclamation-circle" style="color: #ff8800;"></span>
                                <span style="margin-left: 5px; font-weight: 800">{{ $challenge->title }}</span><br>
                                {{ $challenge->description }}<br><br>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Soluções -->
                    <h4 class="project-details__content__subtitle">Soluções Propostas</h4><br>
                    <ul class="project-details__content__list list-unstyled">
                        @foreach ($project->solutions ?? [] as $solution)
                            <li>
                                <span class="fa fa-lightbulb" style="color: #ff8800;"></span>
                                <span style="margin-left: 5px; font-weight: 800">{{ $solution->title }}</span><br>
                                {{ $solution->description }}<br><br>
                            </li>
                        @endforeach
                    </ul>

                </div>
            </div>

            <!-- 🧩 Detalhes do Projeto (lado direito) -->
            <div class="col-xl-4 col-lg-5 wow fadeInRight animated" data-wow-delay="400ms">
                <div class="project-details__right">
                    <ul class="project-details__info-list list-unstyled">
                        <li><span>Cliente:</span> {{ $project->client->name }}</li>
                        <li><span>Setor:</span> {{ $project->client->sector }}</li>
                        <li><span>Serviço:</span> {{ $project->service->name }}</li>
                        <li><span>Website:</span> {{ $project->client->website }}</li>
                    </ul>

                    @isset($clientSocials)
                        @if ($clientSocials->isNotEmpty())
                            <div class="project-details__socials">
                                @foreach ($clientSocials as $sm)
                                    <a href="{{ $sm['url'] }}" target="_blank" rel="noopener">
                                        <i class="{{ $sm['icon'] ?? 'fa-brands fa-link' }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endisset
                </div>
            </div>
        </div>


        <div class="section-title text-center" style="margin-bottom: 80px; margin-top: 80px !important;">
            <h5 class="section-title__tagline section-title__tagline--has-dots">Da ideia ao código</h5>
            <h2 class="section-title__title">
                <span>Como transformamos conceitos em experiências digitais</span>
            </h2>
        </div><!-- /.project-section title -->

        <!-- ====== Versão Desktop / Tablet ====== -->
        <div class="feature-one d-none d-md-block">
            <div class="container">
                <div class="row">
                    @foreach ($project->projectProcesses ?? [] as $pp)
                        @php
                            $proc = $pp->process;
                            $firstImg = optional($pp->images->sortBy('order')->first())->image;
                            $image = $firstImg
                                ? asset($firstImg)
                                : asset('assets/images/feature/placeholder-370x280.jpg');
                            $slides = ($pp->images ?? collect())
                                ->sortBy('order')
                                ->map(function ($img) use ($proc) {
                                    return [
                                        'src' => asset($img->image),
                                        'title' => $img->title ?: $proc?->name ?? 'Etapa',
                                        'desc' => $img->description,
                                        'solution' => $img->solution,
                                    ];
                                })
                                ->values()
                                ->all();
                        @endphp

                        <x-project-process-item :titulo="$proc?->name ?? 'Etapa'" :icone="($proc?->icon_class ?: $proc?->icon) ?? 'icon-idea'" :imagem="$image" :descricao="$pp->description ?? ''"
                            :categoria="$proc?->slug ?? 'proc-' . $pp->id" :slides="$slides" :etapa="$proc?->name ?? null" :process-id="$pp->id" />
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ====== Versão Mobile (Carrossel) ====== -->
        <div class="gallery-page gallery-page__padding d-block d-md-none">
            <div class="container">
                <div class="gallery-page__carousel ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                    data-owl-options='{
                "items": 1,
                "margin": 10,
                "smartSpeed": 700,
                "loop": true,
                "autoplay": true,
                "nav": false,
                "dots": true
            }'>

                    @foreach ($project->projectProcesses ?? [] as $pp)
                        @php
                            $proc = $pp->process;
                            $firstImg = optional($pp->images->sortBy('order')->first())->image;
                            $image = $firstImg
                                ? asset($firstImg)
                                : asset('assets/images/feature/placeholder-370x280.jpg');
                            $slides = ($pp->images ?? collect())
                                ->sortBy('order')
                                ->map(function ($img) use ($proc) {
                                    return [
                                        'src' => asset($img->image),
                                        'title' => $img->title ?: $proc?->name ?? 'Etapa',
                                        'desc' => $img->description,
                                        'solution' => $img->solution,
                                    ];
                                })
                                ->values()
                                ->all();
                        @endphp

                        <div class="item">
                            <div class="feature-one__item">
                                <!-- imagem como background cover -->
                                <div class="feature-one__item__img"
                                    style="background-image: url('{{ $image }}');">
                                </div>

                                <div class="feature-one__item__content">
                                    <h4 class="feature-one__item__content--title">{{ $proc?->name ?? 'Etapa' }}</h4>
                                    @php
                                        $iconClasses = ($proc?->icon_class ?: $proc?->icon) ?? '';
                                        if (is_string($iconClasses) && strpos($iconClasses, '<') !== false) {
                                            if (preg_match('/class\s*=\s*\"([^\"]+)\"/i', $iconClasses, $m)) {
                                                $iconClasses = trim($m[1]);
                                            } else {
                                                $iconClasses = trim(strip_tags($iconClasses));
                                            }
                                        }
                                        $iconClasses = $iconClasses ?: 'icon-idea';
                                    @endphp
                                    <div class="feature-one__item__content--icon">
                                        <span class="{{ $iconClasses }}"></span>
                                    </div>
                                </div>

                                <div class="text-center mt-2">
                                    <button class="feature-one__item__hover-content__btn open-process-modal"
                                        data-category="{{ $proc?->slug ?? 'proc-' . $pp->id }}"
                                        data-etapa="{{ $proc?->name ?? '' }}"
                                        data-slides='@json($slides)'
                                        data-descricao="{{ $pp->description }}" data-process-id="{{ $pp->id }}">
                                        Ver Processo <span class="icon-down-right"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        <!-- ====== Modal Global (Processos) ====== -->
        <x-process-modal />
        <!-- ====== Modal Global (Competências) ====== -->
        <x-competencies-modal />

        <!-- Call To Action Start -->
        <div class="cta-two" id="project-skills">
            <div class="cta-two__bg"
                style="background-image: url('{{ $project->skill_cover ? asset($project->skill_cover) : asset('assets/images/backgrounds/cta-bg-2.jpg') }}');">
            </div>
            <div class="section-title text-center" style="margin-bottom: 40px; margin-top: 0px !important;">
                <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
                <h2 class="section-title__title">Conheça alguns dos trabalhos<br> que deram vida a grandes ideias
                </h2>
            </div><!-- /.project-section title -->
            <div class="container">

                <div class="service-page__carousel" id="project-skills">
                    <div class="container">
                        <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                            data-owl-options='{
                                "items": 4,
                                "margin": 30,
                                "smartSpeed": 700,
                                "loop": true,
                                "autoplay": true,
                                "nav": false,
                                "dots": true,
                                "navText": ["<span class=\"icon-left-arrow\"></span>", "<span class=\"icon-right-arrow\"></span>"],
                                "responsive": {
                                    "0": { "items": 1, "margin": 0 },
                                    "600": { "items": 2 },
                                    "992": { "items": 4 }
                                }
                            }'>

                            @php
                                $projectSkillGroups = $project->skillLinks
                                    ->groupBy('skill_id')
                                    ->map(function ($items) {
                                        $skill = optional($items->first())->skill;
                                        return [
                                            'id' => $skill?->id,
                                            'name' => $skill?->name ?? 'Skill',
                                            'icon' => $skill?->icon ?? 'icon-idea',
                                            'cover' => $skill?->cover ?? null,
                                            'description' =>
                                                $skill?->description ?? 'Competências associadas à habilidade.',
                                            'competencies' => $items
                                                ->map(fn($it) => optional($it->competency)->competency)
                                                ->filter()
                                                ->values(),
                                        ];
                                    })
                                    ->values();
                            @endphp

                            @forelse ($projectSkillGroups as $sg)
                                @php
                                    $image = $sg['cover']
                                        ? asset($sg['cover'])
                                        : asset('assets/images/feature/placeholder-370x280.jpg');
                                @endphp

                                <div class="item">
                                    <div class="service-one__item">
                                        <!-- ícone -->
                                        <div class="service-one__item__icon">
                                            <span class="{{ $sg['icon'] }}"></span>
                                        </div>

                                        <!-- título -->
                                        <h3 class="service-one__item__title">
                                            <a href="javascript:void(0)">{{ $sg['name'] }}</a>
                                        </h3>

                                        <!-- botão -->
                                        <a href="javascript:void(0)"
                                            class="service-one__item__btn open-competencies-modal"
                                            data-skill="{{ $sg['name'] }}" data-comps='@json($sg['competencies'])'>
                                            Ver Competências <span class="icon-down-right"></span>
                                        </a>

                                    </div>
                                </div>
                            @empty
                                <div class="item">
                                    <div class="service-one__item">
                                        <div class="service-one__item__icon">
                                            <span class="icon-idea"></span>
                                        </div>
                                        <h3 class="service-one__item__title">
                                            <a href="javascript:void(0)">Skills</a>
                                        </h3>
                                        <p class="service-one__item__text">Nenhuma habilidade vinculada ao projeto.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Call To Action End -->
        @if (!empty($project->video))
            <div class="video-one">

                <div class="container">
                    <div class="section-title text-center" style="margin-bottom: 10px; margin-top: 10px !important;">
                        <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
                        <h2 class="section-title__title">Conheça alguns dos trabalhos<br> que deram vida a grandes
                            ideias
                        </h2>
                    </div><!-- /.project-section title -->
                    <div class="video-one__banner wow fadeInUp animated animated" data-wow-delay="100ms">
                        <img src="assets/images/backgrounds/video-bg-1-1.jpg" alt="ogency">
                        <div class="video-one__banner__shape wow fadeInRight animated animated"
                            data-wow-delay="300ms">
                            <img src="assets/images/backgrounds/video-bg-shape-1-1.png" alt="ogency">
                        </div>
                        <!-- curved-circle start-->
                        <div class="video-one__banner__curved-circle-box wow fadeInUp animated animated"
                            data-wow-delay="400ms">
                            <div class="curved-circle">
                                <span class="curved-circle-item">
                                    Watch&emsp;Our&emsp;agency&emsp;portfolio&emsp;Video
                                </span>
                            </div>
                            <!-- video btn start -->
                            <a href="{{ $project->video }}" class="video-popup">
                                <span class="fa fa-play"></span>
                            </a>
                            <!-- video btn end -->
                        </div>
                        <!-- curved-circle end-->
                    </div>
                </div>
            </div>
        @endif
        <div class="section-title text-center wow fadeInUp animated" data-wow-delay="400ms">
            <h5 class="section-title__tagline section-title__tagline--has-dots">our work showcase</h5>
            <h2 class="section-title__title">Explore similar portfolio<br> you might like it</h2>
        </div><!-- related-project-section-title -->
        <div class="row">
            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
                <div class="project-two__item">
                    <div class="project-two__item__image"><img src="assets/images/project/project-2-1.jpg"
                            alt="ogency"></div><!-- /.project-image -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats"><a href="projects.html">Digital</a>, <a
                                href="projects.html">Agency</a></p><!-- /.project-category -->
                        <h3 class="project-two__item__content__title"><a href="project-details.html">Asus
                                marketing</a>
                        </h3><!-- /.project-title -->
                    </div>
                </div><!-- /.project-item-two -->
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="300ms">
                <div class="project-two__item">
                    <div class="project-two__item__image"><img src="assets/images/project/project-2-4.jpg"
                            alt="ogency"></div><!-- /.project-image -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats"><a href="projects.html">Digital</a>, <a
                                href="projects.html">Agency</a></p><!-- /.project-category -->
                        <h3 class="project-two__item__content__title"><a href="project-details.html">Asus
                                marketing</a>
                        </h3><!-- /.project-title -->
                    </div>
                </div><!-- /.project-item-two -->
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="400ms">
                <div class="project-two__item">
                    <div class="project-two__item__image"><img src="assets/images/project/project-2-5.jpg"
                            alt="ogency"></div><!-- /.project-image -->
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats"><a href="projects.html">Digital</a>, <a
                                href="projects.html">Agency</a></p><!-- /.project-category -->
                        <h3 class="project-two__item__content__title"><a href="project-details.html">Asus
                                marketing</a>
                        </h3><!-- /.project-title -->
                    </div>
                </div><!-- /.project-item-two -->
            </div>
        </div>
    </div>
</section>
<!-- Projects Details End -->
