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
                        Crie uma nova senha para acessar o painel com segurança.
                        Escolha uma senha forte e fácil de lembrar.
                    </p>
                </div>
            </section>

            {{-- Lado formulário --}}
            <section class="mm-auth-form">

                <div class="mm-auth-header">
                    <p class="mm-auth-form-eyebrow">Nova senha</p>
                    <h2 class="mm-auth-form-title">Redefinir senha</h2>
                    <p class="mm-auth-form-subtitle">
                        Informe seu e-mail e defina uma nova senha para continuar.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mm-alert mm-alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" class="mm-form">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mm-form-group">
                        <label for="email" class="mm-form-label">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus
                            class="mm-form-input">
                    </div>

                    <div class="mm-form-group">
                        <label for="password" class="mm-form-label">Nova senha</label>
                        <input id="password" name="password" type="password" required class="mm-form-input">
                    </div>

                    <div class="mm-form-group">
                        <label for="password_confirmation" class="mm-form-label">Confirmar nova senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="mm-form-input">
                    </div>

                    <button type="submit" class="mm-btn mm-btn-primary">
                        Redefinir senha
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
