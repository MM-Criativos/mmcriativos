<script>
(function(){
  // Build maps from DB so slugs are always current
  const SERVICE_DB = @json(\App\Models\Service::query()->orderBy('order')->orderBy('name')->pluck('name','slug'));
  const SKILL_DB   = @json(\App\Models\Skill::query()->orderBy('id')->orderBy('name')->pluck('name','slug'));

  function applyDbSelect(type, slug){
    try {
      const sel = document.getElementById('holoSelect');
      if (!sel) return;
      const map = (type === 'services') ? SERVICE_DB : (type === 'skills' ? SKILL_DB : null);
      if (!map) { sel.style.display = 'none'; return; }
      sel.style.display = '';
      // Rebuild options from DB map
      sel.innerHTML = Object.entries(map).map(([value,label]) => `<option value="${value}">${label}</option>`).join('');
      if (slug && sel.querySelector(`option[value="${slug}"]`)) sel.value = slug;
      const titleEl = document.querySelector('#holoModal .holo-title');
      if (titleEl && slug) titleEl.textContent = map[slug] || slug;
    } catch (e) {}
  }

  // Wrap openContentModal to re-populate the select with DB data
  const origOpen = window.openContentModal;
  window.openContentModal = function(type, slug, heading){
    if (typeof origOpen === 'function') origOpen(type, slug, heading);
    // After original logic runs, apply DB-driven options/label
    setTimeout(() => applyDbSelect(type, slug), 0);
  };

  // Ensure the title reflects the DB label on manual changes too
  document.addEventListener('change', function(e){
    if (e.target && e.target.id === 'holoSelect') {
      const val = e.target.value;
      const map = (val in SERVICE_DB) ? SERVICE_DB : SKILL_DB;
      const titleEl = document.querySelector('#holoModal .holo-title');
      if (titleEl) titleEl.textContent = map[val] || val;
    }
  }, true);
})();
</script>
