@php
    $seconds = max(1, (int) floor(($redirectDelay ?? 5000) / 1000));
@endphp

<x-guest-layout>
    <x-slot name="title">MM Criativos | Briefing inicial - Confirmação</x-slot>

    <div class="w-full max-w-none py-10 px-8">
        <div class="max-w-3xl mx-auto bg-white shadow-md sm:rounded-lg p-8 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fa-solid fa-check text-green-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Seu formulário foi enviado!</h1>
                <p class="text-gray-700 leading-relaxed">
                    {{ $message ?? 'Seu formulário foi preenchido com sucesso! Muito obrigado por preencher, em breve entraremos em contato com você!' }}
                </p>
                <p class="text-sm text-gray-500">
                    Você será redirecionado automaticamente em {{ $seconds }} {{ \Illuminate\Support\Str::plural('segundo', $seconds) }}.
                </p>
                <a href="{{ $redirectUrl }}" class="inline-flex items-center gap-2 px-6 py-3 bg-orange-600 text-white rounded border border-transparent font-semibold text-sm uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid transition">
                    <i class="fa-solid fa-arrow-right"></i>
                    <span>Ir agora</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        window.setTimeout(function () {
            window.location.href = @json($redirectUrl);
        }, {{ $redirectDelay ?? 5000 }});
    </script>
</x-guest-layout>
