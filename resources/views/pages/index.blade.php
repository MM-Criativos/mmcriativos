@extends('layout.layout')

@section('content')
    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>

    @include('components.preloader')
    <!-- /.preloader -->
    <div class="page-wrapper">
        @include('partials.menu')

        @include('components.slider')

        @include('components.lines')

        {{--  @include('components.aboutus') --}}

        @include('components.services')

        <!-- Sliding Text Start-->
        <section class="slider-text-one">
            <div class="slider-text-one__animate-text">
                <span>Design <span>com</span> propósito.&nbsp;</span>
                <span>Código <span>com</span> alma.&nbsp;</span>
                <span>Design <span>com</span> propósito.&nbsp;</span>
                <span>Código <span>com</span> alma.&nbsp;</span>
            </div>
        </section>
        <!-- Sliding Text Start-->

        @include('components.skills')

        @include('components.projects')

        @include('components.testimonials')

        @include('components.pricing')

        @include('components.clients')

        @include('components.contact-form')

        @include('partials.bottom')

        @include('components.holo-modal')
    </div>
@endsection
