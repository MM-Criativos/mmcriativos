<style>
    /* 🔸 Versão Mobile */
    .main-footer__top--mobile {
        background-color: #0a0a0a;
        padding: 40px 0 30px;
    }

    .footer-mobile__logo img {
        width: 70px;
        height: 70px;
        margin: 0 auto;
        display: block;
        transition: transform 0.3s ease;
    }

    .footer-mobile__logo img:hover {
        transform: scale(1.05);
    }

    /* Ícones sociais */
    .footer-mobile__social {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }

    .footer-mobile__icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        background: #000;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .footer-mobile__icon i {
        font-size: 18px;
        color: #fff;
    }

    .footer-mobile__icon:hover {
        background: #ff8800;
    }

    .footer-mobile__icon:hover i {
        color: #000;
    }
</style>

<footer class="main-footer" style="background-image: url('{{ asset('assets/images/backgrounds/footer-bg-1.png') }}');">
    <div class="container">
        <!-- Topo do rodapé -->
        <!-- 🔸 VERSÃO DESKTOP/TABLET -->
        <div class="main-footer__top d-none d-md-flex wow fadeInUp animated" data-wow-delay="100ms">
            <a href="{{ url('/') }}" class="main-footer__logo">
                <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM Criativos" width="55" height="55">
            </a>
            <div class="main-footer__social">
                @php
                    $setting = $setting ?? \App\Models\Setting::query()->first();
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
            </div>
        </div>

        <!-- 🔹 VERSÃO MOBILE -->
        <div class="main-footer__top--mobile d-block d-md-none wow fadeInUp animated" data-wow-delay="100ms">
            <div class="footer-mobile__logo text-center mb-3">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM Criativos" width="70" height="70">
                </a>
            </div>
            <div class="footer-mobile__social text-center">
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
                        <a href="{{ $url }}" target="_blank" aria-label="{{ ucfirst($field) }}"
                            class="footer-mobile__icon">
                            <i class="{{ $meta['icon'] }}"></i>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>


        <!-- Conteúdo do rodapé -->
        <div class="row d-none d-md-flex">
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
