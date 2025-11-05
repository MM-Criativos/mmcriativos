<x-guest-layout>
    <x-slot name="title">MM Criativos | Briefing inicial - Percepção</x-slot>
    <div class="w-full max-w-none py-10 px-8">

        <h1 class="text-2xl font-semibold text-gray-900 mb-2">Régua de Percepção</h1>
        <p class="text-gray-600 mb-6">Conte para a gente o estilo que melhor representa seu projeto.</p>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        @include('public.briefing._scale_form', ['project' => $project, 'action' => $action])
    </div>
</x-guest-layout>
