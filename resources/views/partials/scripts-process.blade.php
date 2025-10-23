<script>
// Puxa imagens do project_process (data-process-id ou data-processo-id) e atualiza o modal
document.addEventListener('click', async function (ev) {
    const btn = ev.target.closest('.open-process-modal, .open-process-modal-fetch');
    if (!btn) return;
    const id = btn.getAttribute('data-process-id') || btn.getAttribute('data-processo-id');
    if (!id) return;

    ev.preventDefault();
    if (ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    ev.stopPropagation();

    try {
        const res = await fetch(`/modal-process/${id}`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        const modal = document.getElementById('processModal');
        if (!modal) return;
        const imgEl = modal.querySelector('#processModalImage');
        const titleEl = modal.querySelector('#processModalTitle');
        const etapaEl = modal.querySelector('#processModalEtapa');
        const descEl = modal.querySelector('#processModalDescricao');
        const solEl = modal.querySelector('#processModalSolucao');
        const projectEl = modal.querySelector('#processModalProject');
        const carouselNode = modal.querySelector('#processModalCarousel');

        if (projectEl) projectEl.textContent = (data.project && data.project.name) ? data.project.name : 'Projeto';

        const images = Array.isArray(data.images) ? data.images : [];
        const first = images[0] || {};
        if (imgEl && first.src) imgEl.src = first.src;
        if (titleEl) titleEl.textContent = first.title || (data.process && data.process.name) || 'Detalhes do Processo';
        if (etapaEl) etapaEl.textContent = 'Etapa: ' + ((data.process && data.process.name) || '');
        if (descEl) descEl.textContent = first.description || (data.description || '');
        if (solEl) solEl.innerHTML = '<strong>Solução aplicada:</strong> ' + (first.solution || '');

        // Monta o carrossel de thumbs
        if (carouselNode) {
            const html = images.map((it, i) => `
                <div class="item${i === 0 ? ' is-active' : ''}">
                    <img src="${it.src}" alt="thumb ${i + 1}" data-idx="${i}" loading="lazy">
                </div>
            `).join('');

            // Limpa listeners antigos clonando o nó ANTES de (re)inicializar o Owl
            const parent = carouselNode.parentNode;
            let container = carouselNode;
            if (parent) {
                const fresh = carouselNode.cloneNode(false); // sem filhos
                fresh.id = carouselNode.id; // preserva id
                parent.replaceChild(fresh, carouselNode);
                container = fresh;
            }

            // Destroi instâncias anteriores do Owl, se existirem (por segurança)
            const $ = window.jQuery || window.$;
            if ($ && $.fn && $.fn.owlCarousel) {
                try { $(container).trigger('destroy.owl.carousel'); } catch(_) {}
                $(container).removeClass('owl-loaded');
                // Substitui conteúdo
                container.innerHTML = html;
                // Recria o carrossel
                $(container).owlCarousel({
                    items: Math.min(4, Math.max(2, images.length)),
                    margin: 12,
                    smartSpeed: 500,
                    loop: images.length > 4,
                    autoplay: false,
                    nav: false,
                    dots: true,
                    responsive: {
                        0: { items: Math.min(2, images.length) },
                        600: { items: Math.min(3, images.length) },
                        992: { items: Math.min(4, images.length) }
                    }
                });
            } else {
                // Sem jQuery/Owl: fallback simples (grid)
                container.innerHTML = html;
            }

            // Clique nos thumbs para atualizar imagem e textos
            const onThumb = function(e){
                const img = e.target.closest('img[data-idx]');
                if (!img) return;
                const idx = parseInt(img.getAttribute('data-idx')) || 0;
                const sl = images[idx] || {};
                if (imgEl && sl.src) imgEl.src = sl.src;
                if (titleEl) titleEl.textContent = sl.title || '';
                if (descEl) descEl.textContent = sl.description || '';
                if (solEl) solEl.innerHTML = '<strong>Solução aplicada:</strong> ' + (sl.solution || '');
                // Marcar ativo
                container.querySelectorAll('.item').forEach(el => el.classList.remove('is-active'));
                img.closest('.item')?.classList.add('is-active');
            };

            container.addEventListener('click', onThumb, { passive: true });
        }

        // Abrir modal
        modal.style.display = 'flex';
        const scroller = document.querySelector('#holoModal .holo-body');
        if (scroller) scroller.classList.add('submodal-open');
        const overlay = modal.querySelector('.process-modal__overlay');
        const closeBtn = modal.querySelector('.process-modal__close');
        const close = () => { modal.style.display = 'none'; scroller && scroller.classList.remove('submodal-open'); };
        overlay && overlay.addEventListener('click', close, { once: true });
        closeBtn && closeBtn.addEventListener('click', close, { once: true });
    } catch (e) {
        console.error('Falha ao carregar processo', e);
    }
}, true);
</script>
