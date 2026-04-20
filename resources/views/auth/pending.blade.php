<x-guest-layout>
    <div class="fixed inset-0 flex items-center justify-center bg-black/40 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 text-center">
            <div class="mx-auto mb-3 w-12 h-12 flex items-center justify-center rounded-full bg-orange-100 text-orange-600">
                <i data-lucide="hourglass"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Aguarde a liberação de um administrador</h2>
            <p class="text-sm text-gray-600">Sua conta foi criada, mas ainda não foi aprovada. Você será redirecionado ao site em instantes.</p>
            <div class="mt-5">
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Ir agora</a>
            </div>
        </div>
    </div>

    <script>
        setTimeout(() => { window.location.href = '{{ route('home') }}'; }, 2500);
    </script>
</x-guest-layout>

