@props(['extra'])

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-gray-600 mb-1">Nome</label>
        <input name="name" value="{{ old('name', $extra->name ?? '') }}" class="w-full border-gray-300 rounded" required />
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Categoria</label>
        <input name="category" value="{{ old('category', $extra->category ?? '') }}" class="w-full border-gray-300 rounded" />
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm text-gray-600 mb-1">Descrição</label>
        <textarea name="description" rows="3" class="w-full border-gray-300 rounded">{{ old('description', $extra->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Preço</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $extra->price ?? 0) }}" class="w-full border-gray-300 rounded" required />
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Desconto padrão</label>
        <input type="number" step="0.01" name="default_discount" value="{{ old('default_discount', $extra->default_discount ?? 0) }}" class="w-full border-gray-300 rounded" />
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Tipo de preço</label>
        <select name="price_type" class="w-full border-gray-300 rounded">
            @foreach (['fixed'=>'Fixo','percent'=>'Percentual'] as $k=>$v)
                <option value="{{ $k }}" @selected(old('price_type', $extra->price_type ?? 'fixed')===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Período</label>
        <select name="billing_period" class="w-full border-gray-300 rounded">
            @foreach (['one_time'=>'Único','monthly'=>'Mensal','yearly'=>'Anual'] as $k=>$v)
                <option value="{{ $k }}" @selected(old('billing_period', $extra->billing_period ?? 'one_time')===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm text-gray-600 mb-1">Ordenação</label>
        <input type="number" name="sort" value="{{ old('sort', $extra->sort ?? 0) }}" class="w-full border-gray-300 rounded" />
    </div>
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $extra->is_active ?? true)) />
        <span class="text-sm text-gray-700">Ativo</span>
    </div>
</div>

