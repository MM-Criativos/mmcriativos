<!-- Project Start -->
<section class="project-two">
    <div class="container">
        <div class="section-title text-center">
            <h5 class="section-title__tagline section-title__tagline--has-dots">nossos projetos</h5>
            <h2 class="section-title__title">Conheça alguns dos trabalhos<br> que deram vida a grandes ideias</h2>
        </div><!-- /.project-section title -->
        <div class="project-two__carousel ogency-owl__carousel owl-theme owl-carousel"
            data-owl-options='{
            "items": 3,
            "margin": 30,
            "smartSpeed": 1200,
            "loop": true,
            "autoplay": true,
            "nav": false,
            "dots": false,
            "navText": ["<span class=\"icon-left-arrow\"></span>","<span class=\"icon-right-arrow\"></span>"],
            "responsive": {
                "0": { "items": 1 },
                "768": { "items": 2 },
                "992": { "items": 3 },
                "1200": { "items": 3 }
            }
            }'>
            @php
                $projects = \App\Models\Project::with(['client', 'service'])
                    ->orderByDesc('finished_at')
                    ->orderBy('name')
                    ->get();
            @endphp
            @foreach ($projects as $project)
                <div class="project-two__item cursor-pointer"
                    onclick="openProjectModal('{{ $project->slug }}','{{ addslashes($project->name) }}')">
                    <div class="project-two__item__image">
                        <img src="{{ asset($project->thumb ?: ($project->cover ?: 'assets/images/project/project-2-1.jpg')) }}"
                            alt="{{ $project->name }}">
                    </div>
                    <div class="project-two__item__content">
                        <p class="project-two__item__content__cats">
                            @if ($project->service)
                                <span>{{ $project->service->name }}</span>
                            @endif
                            @if ($project->client)
                                <span>, {{ $project->client->name }}</span>
                            @endif
                        </p>
                        <h3 class="project-two__item__content__title">
                            <span>{{ $project->name }}</span>
                        </h3>
                    </div>
                </div>
            @endforeach

        </div><!-- /.project-slider -->
    </div>
</section>
<!-- Project End -->
