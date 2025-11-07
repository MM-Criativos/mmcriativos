<style>
    .page-header__title {
        text-align: center;
        display: block;
        width: 100vw;
        /* pega a tela inteira */
        position: relative;
        left: 50%;
        transform: translateX(-50%);
        margin: 0;
        font-size: clamp(2rem, 5vw, 3.5rem);
        line-height: 1.2;
    }

    .page-header__bg {
        margin-top: -20px;
    }

    .section-title {
        margin-top: 40px ! important;
    }

    .service-one {
        padding: 30px 0 50px;
    }

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

    .project-info-card {
        position: relative;
        z-index: 5;
        /* garante que fique acima do vídeo */
        margin-top: -80px;
        /* sobe o card sobre o header */
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
    }

    .project-info-card__inner {
        background: #111;
        border-radius: 8px;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.5);
        width: 80%;
        overflow: hidden;
    }

    .project-info-card__bar {
        height: 5px;
        background-color: #ff8800;
    }

    .project-info-card__content {
        display: flex;
        justify-content: space-between;
        /* espaçamento dinâmico */
        align-items: flex-start;
        flex-wrap: wrap;
        padding: 25px 40px;
        text-align: center;
        gap: 15px 20px;
        /* controle de respiro entre linhas (mobile/tablet) */
    }

    /* Cada bloco de informação */
    .project-info-card__content>div {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 160px;
        /* garante equilíbrio */
        flex: 1;
        /* distribui dinamicamente o espaço */
    }

    /* Label acima do valor */
    .project-info-card__content .label {
        font-size: 0.8rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }

    .project-info-card__content .value {
        font-weight: 700;
        font-size: 1rem;
        color: #fff;
        word-break: break-word;
        text-align: center;
    }

    .project_details {
        padding: 20px 0px 20px !important;
    }

    /* Link dentro do valor */
    .value-link {
        color: #fff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .value-link:hover {
        color: #ff8800;
    }

    /* Tablets */
    @media (max-width: 992px) {
        .project-info-card__content {
            justify-content: space-around;
            padding: 25px 20px;
        }

        .project-info-card__content>div {
            min-width: 200px;
            flex: 0 1 45%;
            /* duas por linha */
        }
    }

    /* Mobile */
    @media (max-width: 600px) {
        .project-info-card__content {
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px 10px;
        }

        .project-info-card__content>div {
            flex: 1 1 100%;
            min-width: unset;
        }
    }

    .project-details {
        padding: 20px 0 20px !important;
    }

    .project-details__content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        max-width: 900px;
        margin: 0 auto;
    }
</style>

<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
<section class="page-header">
    @php
        $cover = $project->cover;
        $isVideo =
            $cover &&
            \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                '.mp4',
                '.webm',
                '.ogg',
                '.mov',
            ]);
    @endphp

    @if ($cover)
        @if ($isVideo)
            <video class="page-header__bg" autoplay muted loop playsinline preload="metadata"
                style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85;z-index:1;">
                <source src="{{ asset($cover) }}">
            </video>
        @else
            <div class="page-header__bg" style="background-image: url('{{ asset($cover) }}');"></div>
        @endif
    @endif

    <div class="page-header__overlay"></div>

    <div class="container" style="position: relative; z-index: 2;">
        <h2 class="page-header__title">{{-- {{ $project->name }} --}}</h2>
    </div>
</section>

<!-- 🔶 Section de conteúdo -->
<section class="project-details">
    <div class="container">

        <!-- 🟠 Card de informações flutuando -->
        <div class="project-info-card">
            <div class="project-info-card__inner">
                <div class="project-info-card__bar"></div>
                <div class="project-info-card__content row text-center justify-content-center align-items-center">
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <h6 class="label">Cliente</h6>
                        <p class="value">{{ $project->client->name }}</p>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <h6 class="label">Setor</h6>
                        <p class="value">{{ $project->client->sector }}</p>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <h6 class="label">Serviço</h6>
                        <p class="value">{{ $project->service->name }}</p>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <h6 class="label">Website</h6>
                        <p class="value">
                            @php
                                $displayUrl = preg_replace('/^(https?:\/\/)?(www\.)?/i', '', $project->client->website);
                            @endphp
                            <a href="{{ $project->client->website }}" target="_blank" rel="noopener" class="value-link">
                                {{ $displayUrl }}
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <!-- 🔸 Conteúdo principal -->
        <div class="wow fadeInUp animated mt-5" data-wow-delay="200ms">
            <div class="project-details__content">
                <div class="section-title text-center" style="margin-top: 80px !important;">
                    <h5 class="section-title__tagline section-title__tagline--has-dots">Da ideia ao código</h5>

                    <h3 class="project-details__content__title">Resumo do projeto</h3>
                    <p>{{ $project->summary }}</p>
                </div><!-- /.project-section title -->

            </div>
        </div>

        <!-- Challenge Start -->
        <section class="service-one @@extraClassName">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title text-center">
                            <h5 class="section-title__tagline section-title__tagline--has-dots">Quais problemas
                                precisamos resolver</h5>
                            <h2 class="section-title__title">Os desafios do projeto</h2>
                        </div><!-- section-title -->
                    </div>
                </div>
                <style>
                    /* Pixel swap card (sem flip 3D) para Challenges */
                    .pixel-card {
                        position: relative;
                        min-height: 320px;
                    }

                    .pixel-card__content {
                        position: absolute;
                        inset: 0;
                        height: 100%;
                        transition: opacity .18s linear, visibility .18s linear;
                    }

                    .pixel-card__content.front {
                        opacity: 1;
                        visibility: visible;
                    }

                    .pixel-card__content.back {
                        opacity: 0;
                        visibility: hidden;
                    }

                    .pixel-card.is-details .pixel-card__content.front {
                        opacity: 0;
                        visibility: hidden;
                    }

                    .pixel-card.is-details .pixel-card__content.back {
                        opacity: 1;
                        visibility: visible;
                    }

                    .pixel-card .service-one__item {
                        height: 100%;
                        display: flex;
                        flex-direction: column;
                        justify-content: flex-start;
                        border-radius: 14px;
                    }

                    .pixel-card .service-one__item__title {
                        margin-bottom: 8px;
                    }

                    .pixel-card .holo-pixels {
                        position: absolute;
                        inset: -2px;
                        border-radius: 14px;
                        pointer-events: none;
                        opacity: 0;
                        mix-blend-mode: screen;
                        background-image: radial-gradient(rgba(255, 136, 0, .35) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 136, 0, .15) 0%, transparent 60%);
                        background-size: 4px 4px, 100% 100%;
                        filter: blur(.2px);
                    }

                    /* Efeitos de desmontar/montar em pixels (opcional, usado se adicionar classes via JS) */
                    .pixel-card.is-opening .holo-pixels {
                        animation: pixDisperse .5s ease-out both;
                    }

                    .pixel-card.is-closing .holo-pixels {
                        animation: pixAssemble .5s ease-out both;
                    }

                    @keyframes pixDisperse {
                        0% {
                            opacity: .8;
                            background-size: 6px 6px, 100% 100%;
                            filter: blur(.3px);
                            transform: translateY(0) scale(1);
                        }

                        60% {
                            opacity: .4;
                            background-size: 12px 12px, 120% 100%;
                            filter: blur(.6px);
                        }

                        100% {
                            opacity: 0;
                            background-size: 18px 18px, 140% 100%;
                            filter: blur(1px);
                            transform: translateY(3px) scale(1.02);
                        }
                    }

                    @keyframes pixAssemble {
                        0% {
                            opacity: 0;
                            background-size: 18px 18px, 140% 100%;
                            filter: blur(1px);
                            transform: translateY(-3px) scale(.98);
                        }

                        40% {
                            opacity: .4;
                            background-size: 12px 12px, 115% 100%;
                            filter: blur(.6px);
                        }

                        100% {
                            opacity: .55;
                            background-size: 4px 4px, 100% 100%;
                            filter: blur(.2px);
                            transform: translateY(0) scale(1);
                        }
                    }
                </style>

                <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                    data-owl-options='{
                        "items": 3,
                        "margin": 30,
                        "smartSpeed": 700,
                        "loop": false,
                        "autoplay": false,
                        "dots": true,
                        "responsive": {
                            "0": { "items": 1, "margin": 10 },
                            "576": { "items": 1, "margin": 15 },
                            "768": { "items": 2, "margin": 20 },
                            "1200": { "items": 3, "margin": 30 }
                        }
                    }'>
                    @forelse(($project->challenges ?? []) as $challenge)
                        <div class="item wow fadeInUp animated" data-wow-delay="{{ $loop->iteration * 100 }}ms">
                            <div class="pixel-card" style="max-height: 200px; margin-bottom:30px;">
                                <div class="pixel-card__content front">
                                    <div class="service-one__item" style="margin-bottom:30px; position:relative;">
                                        <span class="holo-pixels" aria-hidden="true"></span>
                                        <div class="service-one__item__icon">
                                            <span class="fa fa-exclamation-circle" style="color:#ff8800;"></span>
                                        </div>
                                        <h3 class="service-one__item__title" style="max-height: 20px;">
                                            <a href="javascript:void(0)">{{ $challenge->title }}</a>
                                        </h3>
                                        <p class="service-one__item__text" style="min-height: 110px;">&nbsp;</p>
                                        <a class="service-one__item__btn js-details-open" style="max-height: 20px;"
                                            href="javascript:void(0)">Explorar
                                            <span class="icon-down-right"></span></a>
                                    </div>
                                </div>
                                <div class="pixel-card__content back">
                                    <div class="service-one__item" style="margin-bottom:30px; position:relative;">
                                        <span class="holo-pixels" aria-hidden="true"></span>
                                        <div class="service-one__item__icon">
                                            <span class="fa fa-exclamation-circle" style="color:#ff8800;"></span>
                                        </div>
                                        <p class="service-one__item__text" style="min-height: 130px;">
                                            {{ \Illuminate\Support\Str::limit((string) ($challenge->description ?? ''), 180) ?: 'Em breve mais detalhes.' }}
                                        </p>
                                        <a class="service-one__item__btn js-details-close"
                                            href="javascript:void(0)">Voltar
                                            <span class="icon-left-arrow"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="item">
                            <p class="text-center">Nenhum desafio cadastrado.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Solutions Start -->
        <section class="service-one @@extraClassName">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title text-center">
                            <h5 class="section-title__tagline section-title__tagline--has-dots">
                                Como transformamos desafios em resultados
                            </h5>
                            <h2 class="section-title__title">
                                As soluções do projeto
                            </h2>
                        </div><!-- section-title -->

                    </div>
                </div>
                <div class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                    data-owl-options='{
                        "items": 3,
                        "margin": 30,
                        "smartSpeed": 700,
                        "loop": false,
                        "autoplay": false,
                        "dots": true,
                        "responsive": {
                            "0": { "items": 1, "margin": 10 },
                            "576": { "items": 1, "margin": 15 },
                            "768": { "items": 2, "margin": 20 },
                            "1200": { "items": 3, "margin": 30 }
                        }
                    }'>
                    @forelse(($project->solutions ?? []) as $solution)
                        <div class="item wow fadeInUp animated" data-wow-delay="{{ $loop->iteration * 100 }}ms">
                            <div class="pixel-card" style="max-height: 200px; margin-bottom:30px;">
                                <div class="pixel-card__content front">
                                    <div class="service-one__item" style="margin-bottom:30px; position:relative;">
                                        <span class="holo-pixels" aria-hidden="true"></span>
                                        <div class="service-one__item__icon">
                                            <span class="fa fa-exclamation-circle" style="color:#ff8800;"></span>
                                        </div>
                                        <h3 class="service-one__item__title" style="max-height: 20px;">
                                            <a href="javascript:void(0)">{{ $solution->title }}</a>
                                        </h3>
                                        <p class="service-one__item__text" style="min-height: 110px;">&nbsp;</p>
                                        <a class="service-one__item__btn js-details-open" style="max-height: 20px;"
                                            href="javascript:void(0)">Explorar
                                            <span class="icon-down-right"></span></a>
                                    </div>
                                </div>
                                <div class="pixel-card__content back">
                                    <div class="service-one__item" style="margin-bottom:30px; position:relative;">
                                        <span class="holo-pixels" aria-hidden="true"></span>
                                        <div class="service-one__item__icon">
                                            <span class="fa fa-exclamation-circle" style="color:#ff8800;"></span>
                                        </div>
                                        <p class="service-one__item__text" style="min-height: 130px;">
                                            {{ \Illuminate\Support\Str::limit((string) ($solution->description ?? ''), 180) ?: 'Em breve mais detalhes.' }}
                                        </p>
                                        <a class="service-one__item__btn js-details-close"
                                            href="javascript:void(0)">Voltar
                                            <span class="icon-left-arrow"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="item">
                            <p class="text-center">Nenhuma solução cadastrada.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</section>


<!-- Projects Details Start -->
<section class="project-details">
    <div class="container">
        <div class="section-title text-center" style="margin-bottom: 80px; margin-top: 80px !important;">
            <h5 class="section-title__tagline section-title__tagline--has-dots">Da ideia ao código</h5>
            <h2 class="section-title__title">
                <span>Como transformamos conceitos em experiências digitais</span>
            </h2>
        </div><!-- /.project-section title -->

        <!-- ====== Versão Desktop / Tablet ====== -->
        <div class="feature-one d-none d-md-block">
            <div class="container">
                <div id="project-processes-carousel"
                    class="ogency-owl__dots ogency-owl__carousel owl-theme owl-carousel"
                    data-owl-options='{
                        "items": 3,
                        "margin": 24,
                        "smartSpeed": 600,
                        "loop": false,
                        "autoplay": false,
                        "nav": false,
                        "dots": true,
                        "responsive": {
                            "0": { "items": 1 },
                            "768": { "items": 2 },
                            "1024": { "items": 3 }
                        }
                    }'>
                    @foreach (($project->projectProcesses ?? collect())->sortBy(fn($pp) => $pp->order ?? PHP_INT_MAX) as $pp)
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
                            <x-project-process-item :titulo="$proc?->name ?? 'Etapa'" :icone="($proc?->icon_class ?: $proc?->icon) ?? 'icon-idea'" :imagem="$image"
                                :descricao="$pp->description ?? ''" :categoria="$proc?->slug ?? 'proc-' . $pp->id" :slides="$slides" :etapa="$proc?->name ?? null"
                                :process-id="$pp->id" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <style>
            #project-processes-carousel .item {
                padding: 0 12px;
            }

            #project-processes-carousel .feature-one__item-wrapper {
                width: 100%;
            }

            #project-processes-carousel .feature-one__item {
                height: 100%;
                width: 100%;
            }
        </style>

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
                                        data-descricao="{{ $pp->description }}"
                                        data-process-id="{{ $pp->id }}">
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

        <!-- Skills Start -->
        <div class="cta-two" id="project-skills">
            <div class="cta-two__bg"
                style="background-image: url('{{ $project->skill_cover ? asset($project->skill_cover) : asset('assets/images/backgrounds/cta-bg-2.jpg') }}');">
            </div>
            <div class="section-title text-center" style="margin-bottom: 40px; margin-top: 0px !important;">
                <h5 class="section-title__tagline section-title__tagline--has-dots">
                    Tecnologias e práticas utilizadas
                </h5>
                <h2 class="section-title__title">
                    As habilidades que impulsionaram<br> o desenvolvimento do projeto
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
                                $project->loadMissing([
                                    'tasks.skill',
                                    'tasks.competency',
                                    'taskItems.skill',
                                    'taskItems.competency',
                                ]);

                                $taskGroups = $project->tasks
                                    ->filter(fn($task) => $task->skill)
                                    ->groupBy('skill_id');

                                $itemGroups = $project->taskItems
                                    ->filter(fn($item) => $item->skill)
                                    ->groupBy('skill_id');

                                $skillIds = $taskGroups->keys()->merge($itemGroups->keys())->unique()->values();

                                $projectSkillGroups = $skillIds
                                    ->map(function ($skillId) use ($taskGroups, $itemGroups) {
                                        $tasks = $taskGroups->get($skillId, collect());
                                        $items = $itemGroups->get($skillId, collect());

                                        $skillModel = optional($tasks->first()?->skill ?? $items->first()?->skill);

                                        $competencies = $tasks
                                            ->map(fn($task) => optional($task->competency)->competency)
                                            ->merge($items->map(fn($item) => optional($item->competency)->competency))
                                            ->filter()
                                            ->unique()
                                            ->values();

                                        return [
                                            'id' => $skillModel?->id,
                                            'name' => $skillModel?->name ?? 'Skill',
                                            'icon' => $skillModel?->icon ?? 'icon-idea',
                                            'cover' => $skillModel?->cover ?? null,
                                            'description' =>
                                                $skillModel?->description ?? 'Competências associadas à habilidade.',
                                            'competencies' => $competencies,
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
                                            data-skill="{{ $sg['name'] }}"
                                            data-comps='@json($sg['competencies'])'>
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
                        <h5 class="section-title__tagline section-title__tagline--has-dots">
                            Onde a criação ganha vida
                        </h5>
                        <h2 class="section-title__title">
                            Assista ao resultado final do projeto
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
                                    Veja&emsp;o&emsp;resultado&emsp;do&emsp;nosso&emsp;projeto!
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
    </div>
</section>
<!-- Projects Details End -->
