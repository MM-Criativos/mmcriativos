<script>
// Override SERVICE_TITLES and SKILL_TITLES with DB slugs -> names
(function(){
  try {
    const svc = @json(\App\Models\Service::query()->orderBy('order')->orderBy('name')->pluck('name','slug'));
    if (typeof SERVICE_TITLES === 'object') {
      try {
        Object.keys(SERVICE_TITLES).forEach(k => { delete SERVICE_TITLES[k]; });
        Object.assign(SERVICE_TITLES, svc);
      } catch (_) { window.SERVICE_TITLES = svc; }
    } else {
      window.SERVICE_TITLES = svc;
    }
  } catch (e) {}

  try {
    const skl = @json(\App\Models\Skill::query()->orderBy('id')->orderBy('name')->pluck('name','slug'));
    if (typeof SKILL_TITLES === 'object') {
      try {
        Object.keys(SKILL_TITLES).forEach(k => { delete SKILL_TITLES[k]; });
        Object.assign(SKILL_TITLES, skl);
      } catch (_) { window.SKILL_TITLES = skl; }
    } else {
      window.SKILL_TITLES = skl;
    }
  } catch (e) {}
})();
</script>
