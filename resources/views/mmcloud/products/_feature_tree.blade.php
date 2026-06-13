{{--
  Partial: árvore de features para create/edit
  Variáveis:
    $catalog     — resposta de /feature-catalog: { core: [], features: { key: {label, parent} } }
    $enabledKeys — array de keys já habilitadas ([] no create)
--}}
@php
    $allFeatures  = $catalog['features'] ?? [];
    $usedAsParent = array_unique(array_filter(array_column(array_values($allFeatures), 'parent')));

    $groups      = [];
    $standalones = [];
    foreach ($allFeatures as $key => $meta) {
        if (($meta['parent'] ?? null) !== null) {
            continue;
        }
        if (in_array($key, $usedAsParent, true)) {
            $groups[$key] = $meta;
        } else {
            $standalones[$key] = $meta;
        }
    }
@endphp

<div class="space-y-3">

    {{-- Itens standalone (sem filhos: units, policies, agent, integrations…) --}}
    @if($standalones)
        <div class="border border-neutral-200 dark:border-neutral-700 rounded-xl p-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-neutral-500 mb-3">Módulos individuais</p>
            <div class="flex flex-wrap gap-3">
                @foreach($standalones as $key => $meta)
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               name="features[]"
                               value="{{ $key }}"
                               class="w-4 h-4 rounded accent-[#ff8800]"
                               {{ in_array($key, $enabledKeys) ? 'checked' : '' }}>
                        <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $meta['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Grupos com filhos --}}
    @foreach($groups as $groupKey => $groupMeta)
        @php
            $children = array_filter($allFeatures, fn($m) => ($m['parent'] ?? null) === $groupKey);
            $checkedCount = count(array_intersect(array_keys($children), $enabledKeys));
        @endphp
        <div class="border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3 bg-neutral-50 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
                <input type="checkbox"
                       class="group-master w-4 h-4 rounded accent-[#ff8800]"
                       data-group="{{ $groupKey }}"
                       {{ $checkedCount === count($children) && count($children) > 0 ? 'checked' : '' }}>
                <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $groupMeta['label'] }}</span>
                <span class="ml-auto text-xs text-neutral-400 group-count-{{ $groupKey }}">
                    {{ $checkedCount }}/{{ count($children) }}
                </span>
            </div>
            <div class="flex flex-wrap gap-3 px-4 py-3">
                @foreach($children as $key => $meta)
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               name="features[]"
                               value="{{ $key }}"
                               data-parent="{{ $groupKey }}"
                               class="w-4 h-4 rounded accent-[#ff8800]"
                               {{ in_array($key, $enabledKeys) ? 'checked' : '' }}>
                        <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $meta['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

</div>

@once
@push('scripts')
<script>
document.querySelectorAll('.group-master').forEach(function(master) {
    var group = master.dataset.group;

    function getChildren() {
        return Array.from(document.querySelectorAll('input[data-parent="' + group + '"]'));
    }

    function syncMaster() {
        var children = getChildren();
        var checked  = children.filter(function(c) { return c.checked; }).length;
        master.indeterminate = checked > 0 && checked < children.length;
        master.checked       = checked === children.length && children.length > 0;

        var counter = document.querySelector('.group-count-' + group);
        if (counter) counter.textContent = checked + '/' + children.length;
    }

    master.addEventListener('change', function() {
        getChildren().forEach(function(c) { c.checked = master.checked; });
        syncMaster();
    });

    getChildren().forEach(function(c) {
        c.addEventListener('change', syncMaster);
    });

    syncMaster();
});
</script>
@endpush
@endonce
