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
                        <div class="mm-password-field">
                            <input id="password" name="password" type="password" required
                                class="mm-form-input mm-form-input-password">
                            <button type="button" class="mm-password-toggle" data-toggle-password="password"
                                aria-label="Mostrar senha" aria-controls="password" aria-pressed="false">
                                <svg class="mm-password-icon mm-password-icon-show" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M1 12s4-8 11-8s11 8 11 8s-4 8-11 8s-11-8-11-8" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg class="mm-password-icon mm-password-icon-hide" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M17.94 17.94A10.95 10.95 0 0 1 12 20c-7 0-11-8-11-8a21.76 21.76 0 0 1 5.06-6.94" />
                                    <path d="M9.9 4.24A10.93 10.93 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.17 4.69" />
                                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                    <path d="M1 1l22 22" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="mm-form-group">
                        <label for="password_confirmation" class="mm-form-label">Confirmar senha</label>
                        <div class="mm-password-field">
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="mm-form-input mm-form-input-password">
                            <button type="button" class="mm-password-toggle" data-toggle-password="password_confirmation"
                                aria-label="Mostrar senha" aria-controls="password_confirmation" aria-pressed="false">
                                <svg class="mm-password-icon mm-password-icon-show" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M1 12s4-8 11-8s11 8 11 8s-4 8-11 8s-11-8-11-8" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg class="mm-password-icon mm-password-icon-hide" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M17.94 17.94A10.95 10.95 0 0 1 12 20c-7 0-11-8-11-8a21.76 21.76 0 0 1 5.06-6.94" />
                                    <path d="M9.9 4.24A10.93 10.93 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.17 4.69" />
                                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                    <path d="M1 1l22 22" />
                                </svg>
                            </button>
                        </div>
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
