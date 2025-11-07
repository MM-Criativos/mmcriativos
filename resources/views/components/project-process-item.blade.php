@props([
    'titulo' => 'Wireframes e Estrutura',
    'icone' => 'icon-idea',
    'imagem' => 'assets/images/feature/feature-1.jpg',
    'descricao' => 'Organizacao visual e hierarquia do conteudo.',
    'categoria' => 'wireframes',
    'slides' => [],
    'etapa' => null,
    'processId' => null,
])

<div class="feature-one__item-wrapper wow fadeInUp animated" data-wow-delay="200ms">
    <div class="feature-one__item" data-category="{{ $categoria }}">
        <!-- Imagem -->
        <div class="feature-one__item__img">
            <img src="{{ asset($imagem) }}" alt="{{ $titulo }}">
        </div>

        <!-- Conteudo principal -->
        <div class="feature-one__item__content">
            <h4 class="feature-one__item__content--title">{{ $titulo }}</h4>
            @php
                $iconClasses = $icone;
                if (is_string($iconClasses) && strpos($iconClasses, '<') !== false) {
                    if (preg_match('/class\s*=\s*\"([^\"]+)\"/i', $iconClasses, $m)) {
                        $iconClasses = trim($m[1]);
                    } else {
                        $iconClasses = trim(strip_tags($iconClasses));
                    }
                }
                $iconClasses = trim($iconClasses) ?: 'icon-idea';
            @endphp
            <div class="feature-one__item__content--icon"><span class="{{ $iconClasses }}"></span></div>
        </div>

        <!-- Conteudo ao hover -->
        <div class="feature-one__item__hover-content">
            <h4 class="feature-one__item__hover-content--title">{{ $titulo }}</h4>
            <p class="feature-one__item__hover-content--text">{{ $descricao }}</p>

            <button class="feature-one__item__hover-content__btn open-process-modal" data-category="{{ $categoria }}"
                data-etapa="{{ $etapa ?? $categoria }}" data-slides='@json($slides)'
                data-descricao="{{ $descricao }}"
                @if ($processId) data-process-id="{{ $processId }}" @endif>
                Ver Processo <span class="icon-down-right"></span>
            </button>
        </div>
    </div>
</div>
