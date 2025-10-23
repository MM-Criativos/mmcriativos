<style>
    .process-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: flex-start; /* abre no topo do painel */
        justify-content: center;
        pointer-events: none;
        padding: 18px; /* respiro para o conteúdo */
    }
    /* Dentro do holo-modal, fixa ao viewport do painel */
    .holo-content .process-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
    }
    /* Desativa rolagem do painel quando submodal estiver aberto */
    .holo-body.submodal-open { overflow: hidden !important; }
    .process-modal__overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
        pointer-events: all;
    }
    .process-modal__content {
        position: relative;
        width: min(960px, 92vw);
        max-height: none;
        overflow: visible; /* altura conforme conteúdo */
        background: rgba(17, 17, 17, 0.95);
        border: 1px solid rgba(255, 136, 0, 0.35);
        border-radius: 12px;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
        padding: 14px;
        pointer-events: all;
    }
    .process-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 4px 2px 10px;
        border-bottom: 1px solid rgba(255,136,0,.25);
        color: #fff;
    }
    .process-modal__title { font-size: 1.05rem; color: #fff; margin: 0; }
    /* Desktop/tablet layout: imagem à esquerda, infos à direita, carrossel abaixo */
    @media (min-width: 992px) {
        .process-modal__content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-template-areas:
                'head head'
                'media info'
                'thumbs thumbs';
            gap: 16px;
            overflow: visible;
            max-height: none;
        }
        .process-modal__header { grid-area: head; }
        .process-modal__image { grid-area: media; }
        .process-modal__info { grid-area: info; overflow: auto; }
        .process-modal__carousel { grid-area: thumbs; }
    }
    .process-modal__close {
        background: transparent;
        border: none;
        color: #ff8800;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }
    .process-modal__image { align-self: start; }
    #processModalImage {
        display: block;
        width: 100%;
        aspect-ratio: 16/10;
        height: auto;
        object-fit: cover;
        background: #2a2a2a; /* placeholder caso a imagem tarde a carregar */
        border-radius: 8px;
    }
    #processModalCarousel .item img { width: 100%; height: auto; border-radius: 8px; display: block; }
    .process-modal__info { color: #eee; margin-top: 10px; }
    .process-modal__carousel { margin-top: 6px; }
    .process-modal__info h4 { color: #fff; margin-bottom: 8px; }
    #processModalCarousel .item.is-active img { outline: 2px solid #ff8800; outline-offset: 2px; }
</style>

<div id="processModal" class="process-modal" style="display: none;">
    <div class="process-modal__overlay"></div>

    <div class="process-modal__content">
        <div class="process-modal__header">
            <h4 id="processModalProject" class="process-modal__title">Projeto Nome</h4>
            <button class="process-modal__close" aria-label="Fechar">&times;</button>
        </div>

        <!-- Imagem principal -->
        <div class="process-modal__image">
            <img src="assets/images/feature/feature-1.jpg" alt="Imagem destaque" id="processModalImage">
        </div>

        <!-- Informações -->
        <div class="process-modal__info">
            <h4 id="processModalTitle">Título da Imagem</h4>
            <p class="process-modal__etapa" id="processModalEtapa">Etapa: Wireframes e Estrutura</p>
            <p class="process-modal__descricao" id="processModalDescricao">
                Descrição da imagem e do processo.
            </p>
            <p class="process-modal__solucao" id="processModalSolucao">
                <strong>Solução aplicada:</strong> texto dinâmico.
            </p>
        </div>

        <!-- Carrossel -->
        <div class="process-modal__carousel owl-carousel" id="processModalCarousel">
            {{-- Itens carregados dinamicamente via JS --}}
        </div>
    </div>
</div>
