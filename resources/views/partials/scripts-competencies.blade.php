<script>
// Abre modal de competências a partir do botão em cada Skill
document.addEventListener('click', function (ev) {
    const btn = ev.target.closest('.open-competencies-modal');
    if (!btn) return;
    ev.preventDefault();
    if (ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    ev.stopPropagation();

    try {
        const skill = btn.getAttribute('data-skill') || 'Competências';
        const raw = btn.getAttribute('data-comps') || '[]';
        let comps = [];
        try { comps = JSON.parse(raw); } catch(_) {}

        const modal = document.getElementById('competenciesModal');
        if (!modal) return;
        const titleEl = modal.querySelector('#competenciesModalTitle');
        const listEl = modal.querySelector('#competenciesList');
        const overlay = modal.querySelector('.competencies-modal__overlay');
        const closeBtn = modal.querySelector('.competencies-modal__close');

        if (titleEl) titleEl.textContent = skill;
        if (listEl) listEl.innerHTML = (Array.isArray(comps) && comps.length)
            ? comps.map(c => `<li>${c}</li>`).join('')
            : '<li>Sem competências vinculadas</li>';

        // Abrir
        modal.style.display = 'flex';
        const scroller = document.querySelector('#holoModal .holo-body');
        if (scroller) scroller.classList.add('submodal-open');
        const close = () => { modal.style.display = 'none'; scroller && scroller.classList.remove('submodal-open'); };
        overlay && overlay.addEventListener('click', close, { once: true });
        closeBtn && closeBtn.addEventListener('click', close, { once: true });
    } catch (e) {
        console.error('Falha ao abrir competências', e);
    }
}, true);
</script>

