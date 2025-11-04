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
                    <h5 class="section-title__tagline section-title__tagline--has-dots">Toda grande ideia começa com uma
                        conversa</h5>
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
                                        <textarea name="message" placeholder="Write Comment" required>{{ old('message') }}</textarea>
                                    </div>
                                    <div class="contact-one__btn-box">
                                        <button type="submit" class="ogency-btn">Enviar Mensagem</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @if ($errors->any())
                            <div class="mt-3 p-3 bg-red-100 text-red-700 rounded">
                                <ul class="m-0 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

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

        <!--Contact Info Start-->
        <section class="contact-info" style="margin-bottom: 100px;">
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
                                        <div class="d-inline-block">{{ $phone }}</div>
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
                                    @forelse(($businessHours ?? collect()) as $bh)
                                        @php
                                            $open = $bh->open_time
                                                ? \Illuminate\Support\Carbon::createFromTimeString(
                                                    $bh->open_time,
                                                )->format('H:i')
                                                : null;
                                            $close = $bh->close_time
                                                ? \Illuminate\Support\Carbon::createFromTimeString(
                                                    $bh->close_time,
                                                )->format('H:i')
                                                : null;
                                        @endphp
                                        @if ($bh->is_closed)
                                            {{ $bh->days }}: Fechado<br>
                                        @else
                                            {{ $bh->days }}: {{ $open ?? '--:--' }} às {{ $close ?? '--:--' }}<br>
                                        @endif
                                    @empty
                                        —
                                    @endforelse
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
        <!--Contact Info End-->

        <!--Google Map End-->
        @include('partials.bottom')

    </div><!-- /.page-wrapper -->
@endsection
