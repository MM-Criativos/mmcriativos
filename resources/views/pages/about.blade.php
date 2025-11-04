<style>
    .page-header {
        position: relative;
        overflow: hidden;
        height: 600px;
        /* ajuste conforme o layout */
        display: flex;
        align-items: flex-end;
        justify-content: center;
        text-align: center;
        padding-bottom: 80px;
        /* distância do título em relação ao overlay */
    }

    /* 🔹 Imagem de fundo */
    .page-header__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        z-index: 1;
    }

    .page-header__bg::after {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(30, 30, 30, 0.55);
        /* camada cinza sobre a imagem */
        mix-blend-mode: multiply;
        /* mistura os tons */
        z-index: 2;
        pointer-events: none;
    }

    /* 🔶 Overlay */
    .page-header__overlay {
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 220px;
        background: url('../images/backgrounds/page-header-bg-overlay.png') bottom center / cover no-repeat;
        z-index: 2;
        pointer-events: none;
    }

    /* 🔸 Título */
    .page-header__title {
        position: relative;
        z-index: 3;
        color: #fff;
        font-weight: 700;
        font-size: 48px;
        letter-spacing: -0.5px;
        text-align: center;
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
            <div class="page-header__bg"
                style="background-image: url('{{ $cover ? asset($cover) : asset('assets/images/backgrounds/aboutus.jpg') }}');">
            </div>
            <div class="page-header__overlay"></div>

            <div class="container text-center">
                <h2 class="page-header__title" style="text-align: center; margin: 0 auto">Sobre Nós</h2>
            </div>
        </section>

        <!-- About Start -->
        @include('components.aboutus', ['about' => $about ?? null])

        <!-- About End -->
        <!-- Sliding Text Start-->
        <section class="slider-text-one">
            <div class="slider-text-one__animate-text">
                <span>Seu negócio <span>funcionando</span> no digital&nbsp;<span>&#8226</span></span>
                <span>Seu negócio <span>funcionando</span> no digital&nbsp;<span>&#8226</span></span>
            </div>
        </section>
        <!-- Sliding Text Start-->
        <!-- Team Start -->
        @include('components.staff')

        @include('components.clients')
        @include('partials.bottom')

    </div>
@endsection
