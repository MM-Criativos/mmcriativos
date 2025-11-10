<style>
    /* 🔹 Versão Mobile do formulário */
    .contact-one-mobile {
        background: #0a0a0a;
        padding: 60px 0;
        color: #fff;
    }

    .contact-one-mobile .section-title__tagline {
        color: #ff8800;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .contact-one-mobile .section-title__title {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 25px;
    }

    .contact-one-mobile__form {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
    }

    .contact-one-mobile__input-box {
        width: 100%;
        max-width: 360px;
    }

    .contact-one-mobile__input-box input,
    .contact-one-mobile__input-box select,
    .contact-one-mobile__input-box textarea {
        width: 100%;
        padding: 14px 16px;
        border: none;
        border-radius: 6px;
        background: #111;
        color: #fff;
        font-size: 15px;
        outline: none;
    }

    .contact-one-mobile__input-box select {
        color: #999;
    }

    .contact-one-mobile__input-box textarea {
        min-height: 120px;
        resize: none;
    }

    .contact-one-mobile__btn-box {
        margin-top: 15px;
    }

    .contact-one-mobile__btn-box .ogency-btn {
        background: #ff8800;
        border: none;
        color: #000;
        font-weight: 600;
        font-size: 16px;
        padding: 14px 28px;
        border-radius: 6px;
        width: 100%;
        max-width: 360px;
        transition: all 0.3s ease;
    }

    .contact-one-mobile__btn-box .ogency-btn:hover {
        background: #ffa73b;
    }
</style>

<!-- 🔸 VERSÃO DESKTOP/TABLET -->
<section class="contact-one d-none d-md-block">
    <div class="contact-one__bg" style="background-image: url(assets/images/backgrounds/contact-bg-1.png);"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 wow fadeInLeft animated" data-wow-delay="200ms">
                <div class="contact-one__left">
                    <div class="section-title">
                        <h5 class="section-title__tagline section-title__tagline--has-dots">
                            Toda grande ideia começa com uma conversa
                        </h5>
                        <h2 class="section-title__title">Vamos falar sobre o seu projeto</h2>
                    </div>

                    <div class="contact-one__form-box">
                        <form action="{{ route('contact.send') }}" method="POST" class="contact-one__form" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <input type="text" placeholder="Seu nome" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <input type="email" placeholder="E-mail" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <input type="text" placeholder="WhatsApp" name="whatsapp">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-one__input-box">
                                        <select class="selectpicker" name="service">
                                            <option value="" selected>Selecione o serviço</option>
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
                                        <textarea name="message" placeholder="Escreva sua mensagem" required></textarea>
                                    </div>

                                    <!-- 🔒 reCAPTCHA token invisível -->
                                    <input type="hidden" name="g-recaptcha-response" id="recaptcha_token_desktop">

                                    <div class="contact-one__btn-box">
                                        <button type="submit" class="ogency-btn">Enviar mensagem</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ogency-stretch-element-inside-column">
                    <div class="ogency-stretch__image wow slideInRight animated" data-wow-delay="400ms">
                        <img src="assets/images/resources/contact-1.jpg" alt="ogency">
                        <div class="ogency-stretch__image__angle-top"></div>
                        <div class="ogency-stretch__image__angle-middle"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 🔹 VERSÃO MOBILE -->
<section class="contact-one-mobile d-block d-md-none text-center">
    <div class="contact-one-mobile__bg"></div>
    <div class="container">
        <div class="wow fadeInUp animated" data-wow-delay="200ms">
            <div class="section-title mb-4">
                <h5 class="section-title__tagline section-title__tagline--has-dots">
                    Toda grande ideia começa com uma conversa
                </h5>
                <h2 class="section-title__title">Vamos falar sobre o seu projeto</h2>
            </div>

            <form action="{{ route('contact.send') }}" method="POST" class="contact-one-mobile__form" novalidate>
                @csrf
                <div class="contact-one-mobile__input-box">
                    <input type="text" name="name" placeholder="Seu nome" required>
                </div>
                <div class="contact-one-mobile__input-box">
                    <input type="email" name="email" placeholder="E-mail" required>
                </div>
                <div class="contact-one-mobile__input-box">
                    <input type="text" name="whatsapp" placeholder="WhatsApp">
                </div>
                <div class="contact-one-mobile__input-box">
                    <select name="service" required>
                        <option value="">Selecione o serviço</option>
                        <option value="Landing Page">Landing Page</option>
                        <option value="Site Single Page">Site Single Page</option>
                        <option value="Site Multipage">Site Multipage</option>
                        <option value="Portal">Portal</option>
                        <option value="Sistema Empresarial">Sistema Empresarial</option>
                        <option value="SaaS">System as a Service (SaaS)</option>
                    </select>
                </div>
                <div class="contact-one-mobile__input-box">
                    <textarea name="message" placeholder="Escreva sua mensagem" required></textarea>
                </div>

                <!-- 🔒 reCAPTCHA token invisível -->
                <input type="hidden" name="g-recaptcha-response" id="recaptcha_token_mobile">

                <div class="contact-one-mobile__btn-box">
                    <button type="submit" class="ogency-btn">Enviar mensagem</button>
                </div>
            </form>
        </div>
    </div>
</section>

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
                        const desktop = document.getElementById('recaptcha_token_desktop');
                        const mobile = document.getElementById('recaptcha_token_mobile');
                        if (desktop) desktop.value = token;
                        if (mobile) mobile.value = token;
                    });
            });
        }
    });
</script>
