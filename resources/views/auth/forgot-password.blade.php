@extends('layouts.guest')

@section('content')
    <div class="mm-auth-container">

        <div class="mm-auth-card">

            {{-- Lado institucional --}}
            <section class="mm-auth-side">
                <div class="mm-auth-side-content">
                    <p class="mm-auth-eyebrow">Recuperação de acesso</p>

                    <h1 class="mm-auth-title">MM Criativos</h1>

                    <p class="mm-auth-description">
                        Informe o e-mail utilizado no cadastro.
                        Enviaremos um link para você redefinir sua senha com segurança.
                    </p>
                </div>
            </section>

            {{-- Lado formulário --}}
            <section class="mm-auth-form">

                <div class="mm-auth-header">
                    <p class="mm-auth-form-eyebrow">Esqueci minha senha</p>
                    <h2 class="mm-auth-form-title">Redefinir senha</h2>
                    <p class="mm-auth-form-subtitle">
                        Você receberá um e-mail com as instruções para criar uma nova senha.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mm-alert mm-alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mm-alert mm-alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mm-form">
                    @csrf

                    <div class="mm-form-group">
                        <label for="email" class="mm-form-label">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="mm-form-input">
                    </div>

                    <button type="submit" class="mm-btn mm-btn-primary">
                        Enviar link de recuperação
                    </button>
                </form>

                <div class="mm-auth-footer">
                    <a href="{{ route('login') }}" class="mm-link mm-link-muted">
                        Voltar para o login
                    </a>
                </div>

            </section>

        </div>

    </div>
@endsection
