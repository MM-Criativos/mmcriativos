<style>
    :root {
        --holo-header-h: 64px;
    }

    .holo-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        isolation: isolate;
        contain: layout paint;
        pointer-events: none;
        overflow-x: hidden; /* evita scroll horizontal no overlay */
    }

    .holo-modal.active {
        display: flex;
        pointer-events: all;
    }

    .holo-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(6px) brightness(0.8);
    }

    /* Full-screen page overlay panel */
    .holo-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: min(1200px, 96vw);
        height: 90vh;
        padding: 0;
        border-radius: 16px;
        overflow: hidden;
        z-index: 2;
        color: #fff;
        transform-origin: center;
        backdrop-filter: blur(10px) saturate(160%);
        background: rgba(15, 15, 15, 0.5);
        border: 1px solid rgba(255, 136, 0, 0.35);
        box-shadow: 0 0 25px rgba(255, 136, 0, 0.5), inset 0 0 30px rgba(255, 136, 0, 0.15);
        animation: hologramPulse 4s ease-in-out infinite;
    }

    .holo-header {
        position: sticky;
        top: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: var(--holo-header-h);
        padding: 0 20px;
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(8px) saturate(160%);
        border-bottom: 1px solid rgba(255, 136, 0, 0.25);
        z-index: 3;
    }

    .holo-title {
        font-size: 1.1rem;
        color: #ff8800;
        text-shadow: 0 0 12px rgba(255, 136, 0, 0.5);
        margin: 0;
    }

    .holo-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .holo-select {
        appearance: none;
        background: rgba(30, 30, 30, 0.7);
        color: #eee;
        border: 1px solid rgba(255, 136, 0, 0.35);
        padding: 8px 12px;
        border-radius: 10px;
        outline: none;
    }

    .close-btn {
        background: none;
        border: none;
        color: #ff8800;
        font-size: 1.6rem;
        line-height: 1;
        cursor: pointer;
        transition: .2s;
    }

    .close-btn:hover {
        color: #00ffff;
    }

    .holo-body {
        position: relative;
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden; /* somente scroll vertical dentro do modal */
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }

    .holo-text {
        color: #dddddd;
        line-height: 1.7;
    }

    .holo-dynamic {
        color: #e8e8e8;
        line-height: 1.6;
        margin-bottom: .5rem;
    }
    /* Evita que conteúdos injetados estourem a largura */
    .holo-body img,
    .holo-body video,
    .holo-body canvas {
        max-width: 100%;
        height: auto;
    }
    /* Garante visibilidade de elementos com WOW dentro do modal */
    .holo-content .wow { visibility: visible !important; }

    .holo-content::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image: radial-gradient(rgba(255, 136, 0, 0.4) 1px, transparent 1px), radial-gradient(rgba(0, 200, 255, 0.2) 1px, transparent 1px);
        background-size: 3px 3px, 5px 5px;
        animation: particleDrift 8s linear infinite;
        mix-blend-mode: screen;
        opacity: 0.25;
    }

    /* Ajustes do hero dentro do modal (mantém visual original) */
    .holo-content .page-header {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .holo-content .page-header__bg__landing-page {
        background-size: cover; /* preencher toda a largura do painel */
        background-position: center top;
        min-height: 520px;
        height: 100%;
    }
    .holo-content .page-header .container {
        min-height: 520px;            /* altura do bloco para centralizar o título */
        display: flex;
        align-items: center;          /* título na altura do centro da imagem */
        justify-content: flex-start;  /* alinhado à esquerda */
        padding-bottom: 0;            /* sem empurrar para baixo */
        text-align: left;
    }
    @media (max-width: 767px) {
        .holo-content .page-header__bg__landing-page { min-height: 420px; }
        .holo-content .page-header .container { min-height: 420px; }
    }

    @keyframes particleDrift {
        0% {
            background-position: 0 0, 0 0;
            opacity: .15
        }

        50% {
            background-position: 50px 100px, -60px 80px;
            opacity: .35
        }

        100% {
            background-position: 0 0, 0 0;
            opacity: .15
        }
    }

    .holo-content::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image: linear-gradient(90deg, transparent 0%, rgba(255, 136, 0, 0.15) 50%, transparent 100%);
        opacity: .35;
        animation: holoSweep 3.5s linear infinite;
    }

    @keyframes holoSweep {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .scanline {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: rgba(255, 136, 0, 0.5);
        box-shadow: 0 0 10px rgba(255, 136, 0, 0.7);
        animation: scanMove 3s linear infinite;
        z-index: 2;
    }

    @keyframes scanMove {
        0% {
            top: 0;
            opacity: .1
        }

        50% {
            top: 100%;
            opacity: 1
        }

        100% {
            top: 0;
            opacity: .1
        }
    }

    @keyframes hologramPulse {

        0%,
        100% {
            box-shadow: 0 0 25px rgba(255, 136, 0, 0.5), inset 0 0 30px rgba(255, 136, 0, 0.15)
        }

        50% {
            box-shadow: 0 0 50px rgba(255, 136, 0, 0.8), inset 0 0 40px rgba(255, 136, 0, 0.25)
        }
    }

    #holoParticles {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
        filter: blur(.4px) brightness(1.3);
    }
</style>

<div id="holoModal" class="holo-modal">
    <div class="holo-backdrop" onclick="closeHoloModal()"></div>
    <canvas id="holoParticles"></canvas>
    <div class="holo-content">
        <div class="scanline"></div>
        <div class="holo-header">
            <h2 class="holo-title">Interface Ativada</h2>
            <div class="holo-controls">
                <select id="holoSelect" class="holo-select" style="display:none"></select>
                <button class="close-btn" onclick="closeHoloModal()" aria-label="Fechar">&times;</button>
            </div>
        </div>
        <div class="holo-body">
            <div id="holoDynamic" class="holo-dynamic"></div>
            <p class="holo-text">Este é o novo modal holográfico da <strong>MM Criativos</strong>. Conteúdo de exemplo.
            </p>
        </div>
    </div>
</div>
