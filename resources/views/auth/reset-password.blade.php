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
                        <label for="password_confirmation" class="mm-form-label">Confirmar nova senha</label>
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
