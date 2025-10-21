@props([
    'titulo' => 'Wireframes e Estrutura',
    'icone' => 'icon-idea',
    'imagem' => 'assets/images/feature/feature-1.jpg',
    'descricao' => 'Organização visual e hierarquia do conteúdo.',
    'categoria' => 'wireframes',
])

<div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
    <div class="feature-one__item" data-category="{{ $categoria }}">
        <!-- Imagem -->
        <div class="feature-one__item__img">
            <img src="{{ asset($imagem) }}" alt="{{ $titulo }}">
        </div>

        <!-- Conteúdo principal -->
        <div class="feature-one__item__content">
            <h4 class="feature-one__item__content--title">{{ $titulo }}</h4>
            <div class="feature-one__item__content--icon"><span class="{{ $icone }}"></span></div>
        </div>

        <!-- Conteúdo ao hover -->
        <div class="feature-one__item__hover-content">
            <h4 class="feature-one__item__hover-content--title">{{ $titulo }}</h4>
            <p class="feature-one__item__hover-content--text">{{ $descricao }}</p>

            <button class="feature-one__item__hover-content__btn open-process-modal"
                data-category="{{ $categoria }}">
                Ver Processo <span class="icon-down-right"></span>
            </button>
        </div>
    </div>
</div>
