<footer class="main-footer" style="background-image: url('{{ asset('assets/images/backgrounds/footer-bg-1.png') }}');">
    <div class="container">
        <!-- Topo do rodapé -->
        <div class="main-footer__top wow fadeInUp animated" data-wow-delay="100ms">
            <a href="{{ url('/') }}" class="main-footer__logo">
                <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM Criativos" width="55" height="55">
            </a>
            <div class="main-footer__social">
                @php $setting = $setting ?? \App\Models\Setting::query()->first(); @endphp
                @php
                    $socials = [
                        'instagram' => ['icon' => 'fab fa-instagram'],
                        'whatsapp' => ['icon' => 'fab fa-whatsapp'],
                        'linkedin' => ['icon' => 'fab fa-linkedin-in'],
                        'behance' => ['icon' => 'fab fa-behance'],
                        'github' => ['icon' => 'fab fa-github'],
                    ];
                @endphp
                @foreach ($socials as $field => $meta)
                    @php
                        if ($field === 'whatsapp') {
                            $wa = optional($setting)->whatsapp;
                            $url =
                                is_string($wa) && preg_match('/^https?:\/\//i', $wa)
                                    ? $wa
                                    : ($wa
                                        ? 'https://wa.me/' . preg_replace('/\D+/', '', $wa)
                                        : null);
                        } else {
                            $url = optional($setting)->{$field};
                        }
                    @endphp
                    @if (!empty($url))
                        <a href="{{ $url }}" target="_blank" aria-label="{{ ucfirst($field) }}">
                            <i class="{{ $meta['icon'] }}"></i>
                        </a>
                    @endif
                @endforeach
            </div><!-- /.footer-social -->
        </div><!-- /.footer-top -->

        <!-- Conteúdo do rodapé -->
        <div class="row">
            <div class="col-lg-8 col-md-6 wow fadeInUp animated" data-wow-delay="200ms">
                <div class="main-footer__about">
                    <p class="footer-widget__text">Transformamos ideias em presença digital.</p>
                    <a href="mailto:{{ optional($setting)->email_contact }}">
                        {{ optional($setting)->email_contact ?? 'contato@mmcriativos.com.br' }}
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 wow fadeInUp animated" data-wow-delay="300ms">
                <div class="main-footer__navmenu text-lg-end text-md-start">
                    <ul>
                        <li><a href="{{ url('/') }}">Início</a></li>
                        <li><a href="{{ route('about') }}">Sobre</a></li>
                        <li><a href="{{ route('contact') }}">Contato</a></li>
                        <li><a href="#">Trabalhe Conosco</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                    </ul>
                </div>
            </div>
        </div><!-- /.row -->

        <!-- Copyright -->
        <p class="main-footer__copyright wow fadeInUp animated text-center mt-4" data-wow-delay="500ms">
            © <span class="dynamic-year"></span> MM Criativos. Todos os direitos reservados.
        </p>
    </div><!-- /.container -->
</footer><!-- /.main-footer -->

<!-- back-to-top-start -->
<a href="#" class="scroll-top">
    <svg class="scroll-top__circle" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
    </svg>
</a>
<!-- back-to-top-end -->
