<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Orçamentos</h2>
            <a href="{{ route('admin.commercial.budgets.create') }}"
               class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                Novo Orçamento
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.commercial._tabs')
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded">
                                <option value="">Todos</option>
                                @foreach (['draft'=>'Rascunho','sent'=>'Enviado','opened'=>'Aberto','accepted'=>'Aceito','declined'=>'Recusado','expired'=>'Expirado'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Cliente</label>
                            <select name="client_id" class="w-full border-gray-300 rounded">
                                <option value="">Todos</option>
                                @foreach ($clients as $c)
                                    <option value="{{ $c->id }}" @selected((string)request('client_id')===(string)$c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Serviço</label>
                            <select name="service_id" class="w-full border-gray-300 rounded">
                                <option value="">Todos</option>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}" @selected((string)request('service_id')===(string)$s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3 flex gap-2">
                            <button class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 dark:bg-orange-600 dark:hover:bg-orange-500">Filtrar</button>
                            <a href="{{ route('admin.commercial.budgets.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-500 dark:hover:bg-gray-400">Limpar</a>
                        </div>
                    </form>

                    @if ($budgets->isEmpty())
                        <p class="text-gray-600">Nenhum orçamento encontrado.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                <tr class="text-left text-gray-600 text-sm border-b">
                                    <th class="py-2 pr-4">#</th>
                                    <th class="py-2 pr-4">Cliente</th>
                                    <th class="py-2 pr-4">Serviço</th>
                                    <th class="py-2 pr-4">Plano</th>
                                    <th class="py-2 pr-4">Total</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($budgets as $b)
                                    <tr class="border-b">
                                        <td class="py-3 pr-4 text-gray-600">{{ $b->id }}</td>
                                        <td class="py-3 pr-4">{{ $b->client_name }}</td>
                                        <td class="py-3 pr-4">{{ $b->service->name ?? '-' }}</td>
                                        <td class="py-3 pr-4">{{ $b->plan->category ?? '-' }}</td>
                                        @php($sym = ['BRL'=>'R$','USD'=>'$','EUR'=>'€'][$b->currency] ?? $b->currency)
                                        @php($baseGrand = (float)$b->total_one_time + ((float)$b->total_monthly * 12) + (float)$b->total_yearly)
                                        @php($displayTotal = (float) optional($b->selectedPayment)->total_with_interest ?: $baseGrand)
                                        <td class="py-3 pr-4">{{ $sym }} {{ number_format($displayTotal, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4">{!! $b->statusBadge() !!}</td>
                                        <td class="py-3 pr-4 text-sm flex gap-3">
                                            <a href="{{ route('admin.commercial.budgets.edit', $b) }}" class="inline-flex items-center px-3 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Editar</a>
                                            <a href="{{ route('admin.commercial.budgets.preview', $b) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-500 dark:hover:bg-gray-400">Preview</a>
                                            <form method="POST" action="{{ route('admin.commercial.budgets.destroy', $b) }}" onsubmit="return confirm('Excluir este orçamento? Esta ação não pode ser desfeita.');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $budgets->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
