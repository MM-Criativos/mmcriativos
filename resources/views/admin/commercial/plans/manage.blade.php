@extends('layouts.app')

@php
    $title = 'Clientes';
    $subTitle = 'Editar cliente';
@endphp

@section('content')
    <div class="grid grid-cols-1">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.commercial.plans.index') }}" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                    <select name="category" class="bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md"
                        onchange="this.form.submit()">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected($cat === $category)>{{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <form method="POST" action="{{ route('admin.layout.price.update') }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    @forelse ($plans as $plan)
                        <div class="bg-[#f5f5f5] dark:!bg-[#262626] rounded-md p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-600">Serviço</label>
                                    <div class="mt-1 font-semibold  !text-orange-500">{{ $plan->service->name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600">Preço</label>
                                    <input type="text" name="plans[{{ $plan->id }}][price]"
                                        value="{{ $plan->price }}"
                                        class="mt-1 w-full bg-white dark:!bg-black border-gray-300 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600">Descrição</label>
                                    <input type="text" name="plans[{{ $plan->id }}][description]"
                                        value="{{ $plan->description }}"
                                        class="mt-1 w-full bg-white dark:!bg-black border-gray-300 rounded-md" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm text-gray-600 mb-1">Vantagens</label>
                                <div id="adv-{{ $plan->id }}" class="space-y-2">
                                    @forelse ($plan->advantages as $adv)
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="advantages[{{ $plan->id }}][]"
                                                value="{{ $adv->title }}"
                                                class="flex-1 bg-white dark:!bg-black border-gray-300 rounded-md" />
                                            <button type="button" class="px-3 py-2 rounded-md btn-mmcriativos"
                                                onclick="this.parentElement.remove()">Remover</button>
                                        </div>
                                    @empty
                                        <input type="text" name="advantages[{{ $plan->id }}][]"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md"
                                            placeholder="Adicionar vantagem" />
                                    @endforelse
                                </div>

                                <button type="button" class="px-3 py-2 rounded-md btn-mmcriativos mt-3"
                                    onclick="addAdv('{{ $plan->id }}')">+ Adicionar vantagem</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600">Nenhum plano nesta categoria.</p>
                    @endforelse

                    <div class="flex justify-center">
                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-md btn-mmcriativos">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    function addAdv(id) {
        const wrap = document.getElementById('adv-' + id);
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <input type="text" name="advantages[${id}][]" class="flex-1 bg-white dark:!bg-black border-gray-300 rounded-md" placeholder="Adicionar vantagem" />
            <button type="button"
                class="px-3 py-2 rounded-md btn-mmcriativos"
                onclick="this.parentElement.remove()">Remover</button>`;
        wrap.appendChild(div);
    }
</script>
