<script>
(function(){
  const canHover = window.matchMedia && window.matchMedia('(hover:hover) and (pointer:fine)').matches;

  function setCardHeight(card){
    try {
      const fronts = card.querySelectorAll('.pixel-card__content');
      let h = 0;
      fronts.forEach(face => {
        const inner = face.querySelector('.service-one__item-modal');
        if (inner) h = Math.max(h, inner.offsetHeight);
      });
      if (h) card.style.height = h + 'px';
    } catch(e) {}
  }

  function pauseOwl(card, ms){
    try {
      if (window.jQuery) {
        const $owl = window.jQuery(card).closest('.owl-carousel');
        if ($owl && $owl.length) {
          $owl.trigger('stop.owl.autoplay');
          setTimeout(()=>{ try{ $owl.trigger('play.owl.autoplay',[3000]); }catch(e){} }, ms||900);
        }
      }
    } catch(e) {}
  }

  function benefitGlitchSwap(card, toDetails){
    const current = card.querySelector('.pixel-card__content.' + (toDetails ? 'front' : 'back'));
    const next = card.querySelector('.pixel-card__content.' + (toDetails ? 'back' : 'front'));
    if (!current || !next) { card.classList.toggle('is-details', !!toDetails); return; }

    const ttlSel = '.service-one__item-modal__title';
    const descSel = '.service-one__item-modal__text';

    const ttl  = current.querySelector(ttlSel);
    const desc = current.querySelector(descSel);
    const nextTitle = next.querySelector(ttlSel);
    const nextDesc  = next.querySelector(descSel);

    let targets = [];
    if (toDetails) targets.push(ttl); else targets.push(desc);
    targets = targets.filter(Boolean);

    card.classList.add(toDetails ? 'is-opening' : 'is-closing');

    if (window.gsap && targets.length){
      const tl = gsap.timeline({ defaults: { ease: 'power1.inOut' } });
      tl.to(targets, {
        duration: 0.12,
        x: 4, y: -1, skewX: 14,
        filter: 'brightness(1.6) contrast(1.3) saturate(1.3)',
        textShadow: '2px 0 0 rgba(255,136,0,.9), -2px 0 0 rgba(0,255,255,.6)',
        letterSpacing: 0.6,
        repeat: 1, yoyo: true
      }, 0)
      .to(targets, {
        duration: 0.10,
        x: -3, y: 1, skewY: -8,
        filter: 'brightness(1.4) contrast(1.2) saturate(1.2)',
        textShadow: '1px 0 0 rgba(255,136,0,.7), -1px 0 0 rgba(0,255,255,.5)',
        repeat: 1, yoyo: true
      }, '>-0.04')
      .add(() => { card.classList.toggle('is-details', !!toDetails); })
      .fromTo([toDetails ? nextDesc : nextTitle].filter(Boolean), { opacity: 0, x: -3 }, { opacity: 1, x: 0, duration: 0.18 }, '>-0.02')
      .to([toDetails ? nextDesc : nextTitle].filter(Boolean), { duration: 0.09, x: 2, skewX: 10, repeat: 1, yoyo: true }, '>-0.02')
      .to([nextTitle || nextDesc].filter(Boolean), { duration: 0.10, filter: 'brightness(1.2)', repeat: 1, yoyo: true }, '<')
      .to([targets, nextTitle, nextDesc].flat().filter(Boolean), { textShadow: '0 0 0 rgba(0,0,0,0)', filter: 'none', letterSpacing: 0, duration: 0.08 }, '>-0.02')
      .add(()=>{ card.classList.remove('is-opening','is-closing'); }, '>-0.01');
    } else {
      card.classList.toggle('is-details', !!toDetails);
      setTimeout(()=> card.classList.remove('is-opening','is-closing'), 300);
    }
  }

  function isInside(target, container){
    try { return container && target && container.contains(target); } catch(_) { return false; }
  }

  // Desktop hover (use mouseover/mouseout to emulate true enter/leave)
  if (canHover){
    document.addEventListener('mouseover', function(ev){
      const card = ev.target.closest('.benefit-card');
      if (!card) return;
      setCardHeight(card);
      const rel = ev.relatedTarget;
      if (isInside(rel, card)) return; // ignore moves inside the same card
      benefitGlitchSwap(card, true);
    }, true);

    document.addEventListener('mouseout', function(ev){
      const card = ev.target.closest('.benefit-card');
      if (!card) return;
      setCardHeight(card);
      const rel = ev.relatedTarget;
      if (isInside(rel, card)) return; // ignore moves inside the same card
      benefitGlitchSwap(card, false);
    }, true);
  }

  // Mobile click (toggle)
  document.addEventListener('click', function (ev) {
    const card = ev.target.closest('.benefit-card');
    if (!card) return;
    const noHover = !(window.matchMedia && window.matchMedia('(hover:hover) and (pointer:fine)').matches);
    if (!noHover) return;
    const a = ev.target.closest('a');
    if (a) ev.preventDefault();
    pauseOwl(card, 900);
    setCardHeight(card);
    const open = !card.classList.contains('is-details');
    benefitGlitchSwap(card, open);
  }, true);

  // Initial and responsive sizing
  function recalcAll(){
    document.querySelectorAll('.benefit-card').forEach(setCardHeight);
  }
  recalcAll();
  window.addEventListener('resize', recalcAll);

  try {
    if (window.jQuery) {
      window.jQuery('.service-page__carousel-modal .owl-carousel')
        .on('initialized.owl.carousel resized.owl.carousel refreshed.owl.carousel changed.owl.carousel', recalcAll);
      // In case carousel initializes after this script
      setTimeout(recalcAll, 200);
      setTimeout(recalcAll, 800);
    }
  } catch(e) {}
})();
</script>
