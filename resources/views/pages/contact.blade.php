<style>
    /* 🔧 Remove espaço branco no final da página (mobile) */
    .page-wrapper {
        overflow: hidden;
    }

    /* 🔸 Layout exclusivo para mobile */
    .contact-info-mobile {
        background-color: #0a0a0a;
        color: #fff;
        padding: 40px 0 0;
        /* tiramos o padding-bottom que causava o espaço branco */
    }

    .contact-info-mobile__wrapper {
        max-width: 360px;
        margin: 0 auto;
        padding-bottom: 30px;
        /* padding suave interno para respiro visual */
    }

    /* 🔶 Itens */
    .contact-info-mobile__item {
        text-align: center;
        margin-bottom: 45px;
    }

    /* 🔶 Ícones */
    .contact-info-mobile__icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 75px;
        height: 75px;
        margin: 0 auto 20px auto;
        background: rgba(255, 136, 0, 0.08);
        border: 1px solid rgba(255, 136, 0, 0.4);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .contact-info-mobile__icon span {
        font-size: 38px;
        color: #ff8800;
    }

    /* 🔶 Título */
    .contact-info-mobile__title {
        font-weight: 700;
        font-size: 17px;
        color: #fff;
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* 🔶 Texto */
    .contact-info-mobile__text {
        color: #fff;
        font-size: 15px;
        line-height: 1.8;
        margin: 0;
    }

    /* Links brancos */
    .contact-info-mobile__text a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
    }

    .contact-info-mobile__text a:hover {
        color: #ff8800;
    }

    /* 🔶 Separador */
    .contact-info-mobile__item+.contact-info-mobile__item {
        border-top: 1px solid rgba(255, 136, 0, 0.15);
        padding-top: 45px;
    }

    /* 🔶 Equilíbrio entre blocos de texto */
    .contact-info-mobile__text span,
    .contact-info-mobile__text div,
    .contact-info-mobile__text a {
        display: block;
        margin-bottom: 10px;
    }

    .contact-info-mobile__text div:last-child,
    .contact-info-mobile__text span:last-child {
        margin-bottom: 0;
    }

    /* 🔶 Ajuste fino no último bloco (Funcionamento) */
    .contact-info-mobile__item:last-child .contact-info-mobile__text div {
        margin-bottom: 6px;
        /* reduz leve diferença entre as linhas */
    }
</style>

@extends('layout.layout')

@section('content')
    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>

    @include('components.preloader')
    <!-- /.preloader -->
    <div class="page-wrapper">
        @include('partials.menu')


        <div class="stricky-header stricked-menu main-menu">
            <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
        </div><!-- /.stricky-header -->
        <section class="page-header">
            @php $cover = $about->cover ?? null; @endphp
            <div class="page-header__bg" style="background-image: url(assets/images/backgrounds/contact.jpg);">
            </div>
            <div class="page-header__overlay"></div>

            <div class="container text-center">
                <h2 class="page-header__title" style="text-align: center; margin: 0 auto;">Contato</h2>
            </div>

        </section>
        <!--Contact Start-->
        <section class="contact-two">
            <div class="container wow fadeInUp animated" data-wow-delay="300ms">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline section-title__tagline--has-dots">
                        Toda grande ideia começa com uma conversa
                    </h5>
                    <h2 class="section-title__title">Vamos falar sobre<br> o seu projeto</h2>
                </div><!-- section-title -->

                <div class="contact-one__left text-center">
                    <div class="contact-one__form-box">
                        <form action="{{ route('contact.send') }}" method="POST" class="contact-one__form" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <input type="text" placeholder="Seu nome" name="name"
                                            value="{{ old('name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <input type="email" placeholder="E-mail" name="email"
                                            value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <input type="text" placeholder="WhatsApp" name="whatsapp"
                                            value="{{ old('whatsapp') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <select class="selectpicker" name="service">
                                            <option value="" selected>Selecione o Serviço</option>
                                            <option value="Landing Page">Landing Page</option>
                                            <option value="Site Single Page">Site Single Page</option>
                                            <option value="Site Multipage">Site Multipage</option>
                                            <option value="Portal">Portal</option>
                                            <option value="Sistema Empresarial">Sistema Empresarial</option>
                                            <option value="SaaS">System as a Service (SaaS)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="contact-one__input-box text-message-box">
                                        <textarea name="message" placeholder="Escreva sua mensagem" required>{{ old('message') }}</textarea>
                                    </div>

                                    <!-- 🔒 reCAPTCHA v3 invisível -->
                                    <input type="hidden" name="g-recaptcha-response" id="recaptcha_token_two">

                                    <div class="contact-one__btn-box">
                                        <button type="submit" class="ogency-btn">Enviar Mensagem</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Mensagens de Erro -->
                        @if ($errors->any())
                            <div class="mt-3 p-3 bg-red-100 text-red-700 rounded">
                                <ul class="m-0 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Mensagem de Sucesso -->
                        @if (session('status'))
                            <div class="mt-3 p-3 bg-green-100 text-green-800 rounded">
                                {{ session('status') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        <!--Contact End-->

        <!-- Contact Info (Desktop/Tablet) -->
        <section class="contact-info d-none d-md-block" style="margin-bottom: 100px;">
            <div class="container">
                <div class="contact-info__wrapper" style="padding: 40px 0;">
                    <div class="row justify-content-center align-items-center g-5">

                        <!-- Contato -->
                        <div class="col-xl-5 col-md-6">
                            <div class="contact-info__item text-center text-md-start">
                                <div class="contact-info__item__icon"><span class="icon-phone"></span></div>
                                <h3 class="contact-info__item__title">Contato</h3>
                                @php
                                    $email = $setting->email_contact ?? null;
                                    $phone = $setting->phone ?? null;
                                @endphp
                                <p class="contact-info__item__text mb-0">
                                    @if ($email)
                                        <a href="mailto:{{ $email }}"
                                            class="d-inline-block me-2">{{ $email }}</a><br>
                                    @endif
                                    @if ($phone)
                                        <span class="d-inline-block">{{ $phone }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Funcionamento -->
                        <div class="col-xl-5 col-md-6">
                            <div class="contact-info__item text-center text-md-start">
                                <div class="contact-info__item__icon"><span class="icon-schedule"></span></div>
                                <h3 class="contact-info__item__title">Funcionamento</h3>
                                <p class="contact-info__item__text mb-0">
                                    @foreach ($businessHours ?? [] as $bh)
                                        @php
                                            $open = $bh->open_time
                                                ? \Carbon\Carbon::createFromTimeString($bh->open_time)->format('H:i')
                                                : '--:--';
                                            $close = $bh->close_time
                                                ? \Carbon\Carbon::createFromTimeString($bh->close_time)->format('H:i')
                                                : '--:--';
                                        @endphp
                                        {{ $bh->days }}:
                                        {{ $bh->is_closed ? 'Fechado' : "$open às $close" }}<br>
                                    @endforeach
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Info (Mobile) -->
        <section class="contact-info-mobile d-block d-md-none">
            <div class="container">
                <div class="contact-info-mobile__wrapper" style="padding: 20px 0;">

                    <!-- Contato -->
                    <div class="contact-info-mobile__item mb-5">
                        <div class="contact-info-mobile__icon">
                            <span class="icon-phone"></span>
                        </div>
                        <h3 class="contact-info-mobile__title">Contato</h3>
                        @php
                            $email = $setting->email_contact ?? null;
                            $phone = $setting->phone ?? null;
                        @endphp
                        <p class="contact-info-mobile__text">
                            @if ($email)
                                <a href="mailto:{{ $email }}" class="d-block">{{ $email }}</a>
                            @endif
                            @if ($phone)
                                <span class="d-block">{{ $phone }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Funcionamento -->
                    <div class="contact-info-mobile__item">
                        <div class="contact-info-mobile__icon">
                            <span class="icon-schedule"></span>
                        </div>
                        <h3 class="contact-info-mobile__title">Funcionamento</h3>
                        <p class="contact-info-mobile__text">
                            @php
                                $weekdays = collect($businessHours ?? [])->filter(
                                    fn($bh) => !str_contains(strtolower($bh->days), 'sab') &&
                                        !str_contains(strtolower($bh->days), 'dom'),
                                );
                                $weekend = collect($businessHours ?? [])->filter(
                                    fn($bh) => str_contains(strtolower($bh->days), 'sab') ||
                                        str_contains(strtolower($bh->days), 'dom'),
                                );
                            @endphp

                            @foreach ($weekdays as $bh)
                                @php
                                    $open = $bh->open_time
                                        ? \Carbon\Carbon::createFromTimeString($bh->open_time)->format('H:i')
                                        : '--:--';
                                    $close = $bh->close_time
                                        ? \Carbon\Carbon::createFromTimeString($bh->close_time)->format('H:i')
                                        : '--:--';
                                @endphp
                                {{ $bh->days }}: {{ $open }} às {{ $close }}
                            @endforeach

                        <div class="mt-2">
                            @foreach ($weekend as $bh)
                                {{ $bh->days }}:
                                {{ $bh->is_closed ? 'Fechado' : $bh->open_time . ' às ' . $bh->close_time }}
                            @endforeach
                        </div>
                        </p>
                    </div>

                </div>
            </div>
        </section>



        <!--Google Map End-->
        @include('partials.bottom')

    </div><!-- /.page-wrapper -->
@endsection

<!-- 🧠 Google reCAPTCHA v3 -->
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {
                        action: 'contact'
                    })
                    .then(function(token) {
                        const recaptchaInput = document.getElementById('recaptcha_token_two');
                        if (recaptchaInput) recaptchaInput.value = token;
                    });
            });
        }
    });
</script>
