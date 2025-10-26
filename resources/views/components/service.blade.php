<!-- Services (custom grid) Start -->
<section class="gallery-page">
    <style>
        .services-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 24px; }
        .tile { position: relative; overflow: hidden; border-radius: 12px; }
        .tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .tile--sm { aspect-ratio: 378/407; }
        .tile--tall { aspect-ratio: 378/824; }
        .tile--wide { aspect-ratio: 766/407; }
        /* positions (12-col layout) */
        .pos-lt { grid-column: 1 / span 4; grid-row: 1; }
        .pos-ct { grid-column: 5 / span 4; grid-row: 1 / span 2; }
        .pos-rt { grid-column: 9 / span 4; grid-row: 1; }
        .pos-lb { grid-column: 1 / span 4; grid-row: 2; }
        .pos-rb { grid-column: 9 / span 4; grid-row: 2; }
        .pos-wide { grid-column: 1 / span 8; grid-row: 3; }
        .pos-r3 { grid-column: 9 / span 4; grid-row: 3; }
        /* responsive */
        @media (max-width: 1199px) { .services-grid { gap: 20px; } }
        @media (max-width: 991px) {
            .services-grid { grid-template-columns: repeat(6, 1fr); }
            .pos-lt { grid-column: 1 / span 3; grid-row: auto; }
            .pos-ct { grid-column: 4 / span 3; grid-row: auto; }
            .pos-rt { grid-column: 1 / span 3; }
            .pos-lb { grid-column: 4 / span 3; }
            .pos-wide { grid-column: 1 / span 6; }
            .pos-rb, .pos-r3 { grid-column: 1 / span 3; }
        }
        @media (max-width: 575px) {
            .services-grid { grid-template-columns: 1fr; }
            .tile--tall { aspect-ratio: 9/16; }
            .pos-lt, .pos-ct, .pos-rt, .pos-lb, .pos-rb, .pos-wide, .pos-r3 { grid-column: 1 / -1; grid-row: auto; }
        }
    </style>
    <div class="container">
        <div class="services-grid">
            <div class="gallery-page__single tile tile--sm pos-lt">
                <img src="assets/images/gallery/gallery-1.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-1.jpg"><span class="icon-plus"></span></a></div>
            </div>
            <div class="gallery-page__single tile tile--tall pos-ct">
                <img src="assets/images/gallery/gallery-5.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-5.jpg"><span class="icon-plus"></span></a></div>
            </div>
            <div class="gallery-page__single tile tile--sm pos-rt">
                <img src="assets/images/gallery/gallery-2.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-2.jpg"><span class="icon-plus"></span></a></div>
            </div>
            <div class="gallery-page__single tile tile--sm pos-lb">
                <img src="assets/images/gallery/gallery-3.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-3.jpg"><span class="icon-plus"></span></a></div>
            </div>
            <div class="gallery-page__single tile tile--sm pos-rb">
                <img src="assets/images/gallery/gallery-4.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-4.jpg"><span class="icon-plus"></span></a></div>
            </div>
            <div class="gallery-page__single tile tile--wide pos-wide">
                <img src="assets/images/gallery/gallery-8.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-8.jpg"><span class="icon-plus"></span></a></div>
            </div>
            <div class="gallery-page__single tile tile--sm pos-r3">
                <img src="assets/images/gallery/gallery-7.jpg" alt="ogency">
                <div class="gallery-page__icon"><a class="img-popup" href="assets/images/gallery/gallery-7.jpg"><span class="icon-plus"></span></a></div>
            </div>
        </div>
    </div>
</section>
<!-- Services (custom grid) End -->
