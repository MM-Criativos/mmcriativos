<script>
// Glitch simples: só no título e botão (Explorar ↔ Voltar)
(function(){
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

  function glitchSwap(card, toDetails){
    const current = card.querySelector('.pixel-card__content.' + (toDetails ? 'front' : 'back'));
    const next = card.querySelector('.pixel-card__content.' + (toDetails ? 'back' : 'front'));
    if (!current || !next) { card.classList.toggle('is-details', !!toDetails); return; }

    const icon = current.querySelector('.service-one__item__icon');
    const ttl  = current.querySelector('.service-one__item__title');
    const desc = current.querySelector('.service-one__item__text');
    const btn  = current.querySelector('.service-one__item__btn');
    const nextBtn = next.querySelector('.service-one__item__btn');
    const nextTitle = next.querySelector('.service-one__item__title');
    const nextDesc  = next.querySelector('.service-one__item__text');

    let targets = [icon, btn];
    if (toDetails) targets.push(ttl); else targets.push(desc);
    targets = targets.filter(Boolean);

    if (window.gsap && targets.length){
      const tl = gsap.timeline({ defaults: { ease: 'power1.inOut' } });
      // Pulso 1: deslocamento mais forte + brilho (inclui ícone)
      tl.to(targets, {
        duration: 0.12,
        x: 4, y: -1, skewX: 14,
        filter: 'brightness(1.6) contrast(1.3) saturate(1.3)',
        textShadow: '2px 0 0 rgba(255,136,0,.9), -2px 0 0 rgba(0,255,255,.6)',
        letterSpacing: 0.6,
        repeat: 1, yoyo: true
      }, 0)
      // Pulso 2: contra movimento curto
      .to(targets, {
        duration: 0.10,
        x: -3, y: 1, skewY: -8,
        filter: 'brightness(1.4) contrast(1.2) saturate(1.2)',
        textShadow: '1px 0 0 rgba(255,136,0,.7), -1px 0 0 rgba(0,255,255,.5)',
        repeat: 1, yoyo: true
      }, '>-0.04')
      // Troca de face
      .add(() => { card.classList.toggle('is-details', !!toDetails); })
      // Aparecer novo botão com micro-glitch mais visível
      .fromTo(nextBtn, { opacity: 0, x: -3, y: 0 }, { opacity: 1, x: 0, duration: 0.18 }, '>-0.02')
      .to(nextBtn, { duration: 0.09, x: 2, skewX: 10, repeat: 1, yoyo: true }, '>-0.02')
      // Breve realce também no título/descrição da nova face
      .to([nextTitle || nextDesc].filter(Boolean), { duration: 0.10, filter: 'brightness(1.2)', repeat: 1, yoyo: true }, '<')
      // Limpeza do shadow/jitter
      .to([targets, nextBtn], { textShadow: '0 0 0 rgba(0,0,0,0)', filter: 'none', letterSpacing: 0, duration: 0.08 }, '>-0.02');
    } else {
      card.classList.toggle('is-details', !!toDetails);
    }
  }

  document.addEventListener('click', function (ev) {
    const openBtn = ev.target.closest('.js-details-open');
    const closeBtn = ev.target.closest('.js-details-close');
    if (!openBtn && !closeBtn) return;
    const card = ev.target.closest('.pixel-card');
    if (!card) return;
    ev.preventDefault();
    pauseOwl(card, 900);
    glitchSwap(card, !!openBtn);
  }, true);
})();
</script>
