@extends('layouts.guest')

@section('content')
    <div class="mm-auth-container">

        <div class="mm-auth-card">

            {{-- Lado institucional --}}
            <section class="mm-auth-side">
                <div class="mm-auth-side-content">
                    <p class="mm-auth-eyebrow">Área administrativa</p>

                    <h1 class="mm-auth-title">MM Criativos</h1>
                    <h1 class="mm-auth-subtitle">Painel interno</h1>

                    <p class="mm-auth-description">
                        Gerencie projetos, clientes, tarefas e contratos em um único painel simples e organizado.
                        Aqui você visualiza tudo o que está acontecendo na equipe, em tempo real.
                    </p>
                </div>
            </section>

            {{-- Lado formulário --}}
            <section class="mm-auth-form">

                <div class="mm-auth-header">
                    <p class="mm-auth-form-eyebrow">Entrar</p>
                    <h2 class="mm-auth-form-title">Acesse seu painel</h2>
                    <p class="mm-auth-form-subtitle">
                        Use o e-mail cadastrado no sistema.
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

                <form method="POST" action="{{ route('login') }}" class="mm-form">
                    @csrf

                    <div class="mm-form-group">
                        <label for="email" class="mm-form-label">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="mm-form-input">
                    </div>

                    <div class="mm-form-group">
                        <label for="password" class="mm-form-label">Senha</label>
                        <input id="password" name="password" type="password" required class="mm-form-input">
                    </div>

                    <div class="mm-form-row">
                        <label class="mm-checkbox">
                            <input type="checkbox" name="remember">
                            <span class="mm-checkbox-box"></span>
                            <span class="mm-checkbox-label">Lembrar de mim</span>
                        </label>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="mm-link">
                                Criar conta
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="mm-btn mm-btn-primary">
                        Entrar
                    </button>

                    @if (Route::has('password.request'))
                        <div class="mm-auth-forgot">
                            <a href="{{ route('password.request') }}" class="mm-link mm-link-muted">
                                Esqueceu sua senha?
                            </a>
                        </div>
                    @endif

                </form>

            </section>

        </div>

    </div>
@endsection
