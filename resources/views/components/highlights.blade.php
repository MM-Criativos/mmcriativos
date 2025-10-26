<section class="about-one" style="padding: 100px;">

    <div class="row">
        <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
            <div class="award-one__item">
            </div><!-- /.award-item -->
            <div class="award-one__item">
            </div><!-- /.award-item -->
        </div>
        <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="500ms">

            <!-- 🔸 Video invisível carregado -->
            <video id="mmVideo" src="{{ asset('assets/video/mobileMM.mp4') }}" preload="auto" muted playsinline
                autoplay loop
                style="width:100%; height:auto; border-radius:12px; display:block; background:#000;"></video>
        </div>

        <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="300ms">
            <div class="award-one__item award-one__item--ml">
            </div><!-- /.award-image -->
        </div><!-- /.award-item -->
        <div class="award-one__item award-one__item--ml">
        </div><!-- /.award-item -->
    </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const vid = document.getElementById('mmVideo');
        if (!vid) return;
        const p = vid.play();
        if (p && p.catch) p.catch(() => {});
    });
</script>
