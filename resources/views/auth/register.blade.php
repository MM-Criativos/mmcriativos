@extends('layouts.guest')

@section('content')
    <div class="mm-auth-container">

        <div class="mm-auth-card mm-auth-card-reverse">

            {{-- Lado formulário --}}
            <section class="mm-auth-form">

                <div class="mm-auth-header">
                    <p class="mm-auth-form-eyebrow">Criar conta</p>
                    <h2 class="mm-auth-form-title">Solicite acesso à plataforma</h2>
                    <p class="mm-auth-form-subtitle">
                        Preencha seus dados para solicitar acesso ao painel da MM Criativos.
                        Após o cadastro, sua conta passará por aprovação antes de liberar o acesso.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mm-alert mm-alert-warning">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mm-alert mm-alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mm-form">
                    @csrf

                    <div class="mm-form-group">
                        <label for="name" class="mm-form-label">Nome</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                            class="mm-form-input">
                    </div>

                    <div class="mm-form-group">
                        <label for="email" class="mm-form-label">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="mm-form-input">
                    </div>

                    <div class="mm-form-group">
                        <label for="password" class="mm-form-label">Crie uma senha</label>
                        <input id="password" name="password" type="password" required class="mm-form-input">
                    </div>

                    <div class="mm-form-group">
                        <label for="password_confirmation" class="mm-form-label">Confirmar senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="mm-form-input">
                    </div>

                    <div class="mm-form-helper">
                        <strong>Requisitos da senha:</strong>
                        <ul class="mm-password-requirements">
                            <li>Ao menos 8 caracteres</li>
                            <li>Confirme a senha exatamente como digitou acima</li>
                        </ul>
                    </div>

                    <button type="submit" class="mm-btn mm-btn-primary">
                        Solicitar Acesso
                    </button>

                    <div class="mm-form-row mm-form-row-start">
                        <span style="font-size:14px; color:#374151;">
                            Já tem acesso?
                            <a href="{{ route('login') }}" class="mm-link">Entrar</a>
                        </span>
                    </div>
                </form>

            </section>

            {{-- Lado institucional --}}
            <section class="mm-auth-side">
                <div class="mm-auth-side-content">
                    <p class="mm-auth-eyebrow">Você chegará longe</p>

                    <h1 class="mm-auth-title">MM Criativos</h1>
                    <h1 class="mm-auth-subtitle">Painel interno</h1>

                    <div class="mm-auth-security">
                        <p class="mm-auth-security-text">
                            Após o cadastro, sua solicitação será enviada para análise.
                            Assim que um administrador aprovar o acesso, você poderá entrar no painel
                            e acompanhar todas as informações da equipe.
                        </p>
                    </div>
                </div>
            </section>

        </div>

    </div>
@endsection
