<style>
    .competencies-modal { position: fixed; inset: 0; z-index: 10000; display: none; align-items: center; justify-content: center; pointer-events: none; padding: 18px; }
    .holo-content .competencies-modal { position: fixed; inset: 0; z-index: 100000; }
    .holo-body.submodal-open { overflow: hidden !important; }
    .competencies-modal__overlay { position: absolute; inset: 0; background: rgba(0,0,0,.75); backdrop-filter: blur(4px); pointer-events: all; }
    .competencies-modal__content { position: relative; width: min(720px, 92vw); background: rgba(17,17,17,.95); color: #eee; border: 1px solid rgba(255,136,0,.35); border-radius: 12px; box-shadow: 0 0 30px rgba(0,0,0,.6); padding: 16px; pointer-events: all; }
    .competencies-modal__header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,136,0,.25); }
    .competencies-modal__title { margin: 0; font-size: 1.05rem; color: #fff; }
    .competencies-modal__close { background: transparent; border: none; color: #ff8800; font-size: 22px; line-height: 1; cursor: pointer; }
    .competencies-modal__body { padding-top: 10px; max-height: 60vh; overflow: auto; }
    .competencies-list { margin: 0; padding-left: 18px; }
    .competencies-list li { margin: 6px 0; }
</style>

<div id="competenciesModal" class="competencies-modal" style="display:none">
    <div class="competencies-modal__overlay"></div>
    <div class="competencies-modal__content">
        <div class="competencies-modal__header">
            <h4 id="competenciesModalTitle" class="competencies-modal__title">Competências</h4>
            <button class="competencies-modal__close" aria-label="Fechar">&times;</button>
        </div>
        <div class="competencies-modal__body">
            <ul id="competenciesList" class="competencies-list"></ul>
        </div>
    </div>
    
</div>

