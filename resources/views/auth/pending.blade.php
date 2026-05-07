@extends('layouts.guest')

@section('content')
    <div class="mm-auth-container">

        <div class="mm-auth-card">

            {{-- Lado institucional --}}
            <section class="mm-auth-side">
                <div class="mm-auth-side-content">
                    <p class="mm-auth-eyebrow">Acesso em análise</p>

                    <h1 class="mm-auth-title">MM Criativos</h1>

                    <p class="mm-auth-description">
                        Sua solicitação de acesso foi recebida.
                        Um administrador irá revisar suas informações antes de liberar o acesso ao painel.
                    </p>
                </div>
            </section>

            {{-- Lado informativo --}}
            <section class="mm-auth-form" style="text-align: center;">

                <div class="mm-auth-header">
                    <div class="mm-auth-icon">
                        <i data-lucide="hourglass" style="width:26px;height:26px;stroke:#ff8800;stroke-width:2;"></i>
                    </div>

                    <h2 class="mm-auth-form-title">Aguarde a liberação</h2>

                    <p class="mm-auth-form-subtitle">
                        Assim que sua conta for aprovada por um administrador,
                        você poderá entrar normalmente na plataforma.
                    </p>
                    <p class="mm-auth-form-subtitle" style="margin-top:12px;">
                        Você será redirecionado ao login em <strong id="pendingTimer">10</strong> segundos.
                    </p>
                </div>

                <div class="mm-auth-footer">
                    <a href="{{ route('login') }}" class="mm-btn mm-btn-secondary">
                        Voltar ao login
                    </a>
                </div>

            </section>

        </div>

    </div>

    <script>
        (() => {
            const loginUrl = "{{ route('login') }}";
            const el = document.getElementById('pendingTimer');
            let seconds = 10;

            const interval = setInterval(() => {
                seconds = Math.max(0, seconds - 1);
                if (el) el.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = loginUrl;
                }
            }, 1000);
        })();
    </script>
@endsection
