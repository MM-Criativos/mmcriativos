<style>
    /* 🔹 Estrutura isolada do modal */
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

    /* 🔸 Painel holográfico principal */
    .holo-content {
        position: relative;
        width: 600px;
        max-width: 90%;
        padding: 2rem;
        border-radius: 16px;
        overflow: hidden;
        z-index: 2;
        color: #fff;
        transform-origin: center;
        backdrop-filter: blur(10px) saturate(160%);
        background: rgba(15, 15, 15, 0.4);
        border: 1px solid rgba(255, 136, 0, 0.35);
        box-shadow:
            0 0 25px rgba(255, 136, 0, 0.5),
            inset 0 0 30px rgba(255, 136, 0, 0.15);
        animation: hologramPulse 4s ease-in-out infinite;
    }

    /* 🔶 Simulação de partículas */
    .holo-content::before,
    .holo-content::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image:
            radial-gradient(rgba(255, 136, 0, 0.4) 1px, transparent 1px),
            radial-gradient(rgba(0, 200, 255, 0.2) 1px, transparent 1px);
        background-size: 3px 3px, 5px 5px;
        animation: particleDrift 8s linear infinite;
        mix-blend-mode: screen;
        opacity: 0.3;
    }

    @keyframes particleDrift {
        0% {
            background-position: 0 0, 0 0;
            opacity: 0.2;
        }

        50% {
            background-position: 50px 100px, -60px 80px;
            opacity: 0.5;
        }

        100% {
            background-position: 0 0, 0 0;
            opacity: 0.2;
        }
    }

    /* 🔸 Movimento holográfico lateral */
    .holo-content::before {
        background-image: linear-gradient(90deg,
                transparent 0%, rgba(255, 136, 0, 0.15) 50%, transparent 100%);
        opacity: 0.4;
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

    /* 🔸 Escaneamento vertical */
    .scanline {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: rgba(255, 136, 0, 0.5);
        box-shadow: 0 0 10px rgba(255, 136, 0, 0.7);
        animation: scanMove 3s linear infinite;
    }

    @keyframes scanMove {
        0% {
            top: 0;
            opacity: 0.1;
        }

        50% {
            top: 100%;
            opacity: 1;
        }

        100% {
            top: 0;
            opacity: 0.1;
        }
    }

    /* 🔸 Pulso energético */
    @keyframes hologramPulse {

        0%,
        100% {
            box-shadow: 0 0 25px rgba(255, 136, 0, 0.5), inset 0 0 30px rgba(255, 136, 0, 0.15);
        }

        50% {
            box-shadow: 0 0 50px rgba(255, 136, 0, 0.8), inset 0 0 40px rgba(255, 136, 0, 0.25);
        }
    }

    /* 🔸 Título e texto */
    .holo-title {
        font-size: 1.8rem;
        color: #ff8800;
        text-shadow: 0 0 12px rgba(255, 136, 0, 0.7);
        margin-bottom: 1rem;
    }

    .holo-text {
        color: #dddddd;
        line-height: 1.7;
    }

    /* 🔸 Botão de fechamento */
    .close-btn {
        position: absolute;
        top: 10px;
        right: 14px;
        background: none;
        border: none;
        color: #ff8800;
        font-size: 1.8rem;
        cursor: pointer;
        transition: 0.2s;
        z-index: 10;
    }

    .close-btn:hover {
        color: #00ffff;
    }

    #holoParticles {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
        filter: blur(0.4px) brightness(1.3);
    }
</style>

<div id="holoModal" class="holo-modal">
    <div class="holo-backdrop" onclick="closeHoloModal()"></div>
    <canvas id="holoParticles"></canvas>
    <div class="holo-content"> <!-- painel hologr�fico -->`r`n        <div class="scanline"></div>`r`n        <button class="close-btn" onclick="closeHoloModal()">`r`n            <span>?-</span>`r`n        </button>`r`n`r`n        <h2 class="holo-title">Interface Ativada</h2>
        <p class="holo-text">
            Este é o novo modal holográfico da <strong>MM Criativos</strong> —
            construído em camadas de luz, energia e código. Um painel digital que surge do circuito,
            vibra com glitchs e retorna ao éter quando encerrado.
        </p>
    </div>
</div>


