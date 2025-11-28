@php
    $title = 'Equipe';
    $subTitle = 'Membros da equipe MM Criativos';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:!bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.team.update', $user) }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-2">Informações do Usuário</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="4"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    placeholder="Breve bio do integrante">{{ old('description', $user->description) }}</textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cargo</label>
                                <input type="text" name="cargo" value="{{ old('cargo', $user->cargo) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    placeholder="Ex.: Desenvolvedor, Designer...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="photo" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'user-photo')">
                                    @php $photo = $user->photo ? asset($user->photo) : null; @endphp
                                    @if ($photo)
                                        <img id="preview-user-photo" src="{{ $photo }}" alt="Foto"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-user-photo"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Foto
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-2">Redes Sociais</h3>
                        <div class="space-y-4">
                            @foreach ($socialMedias as $media)
                                @php
                                    $existing =
                                        optional($user->socialMedias->firstWhere('id', $media->id))->pivot->url ?? '';
                                @endphp
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 items-center">
                                    <div class="text-sm text-gray-700">
                                        <i class="{{ $media->icon }} !text-orange-500 mr-1"></i>
                                        {{ $media->name }}
                                    </div>
                                    <div class="md:col-span-3">
                                        <input type="url" name="socials[{{ $media->id }}]"
                                            value="{{ old('socials.' . $media->id, $existing) }}" placeholder="https://..."
                                            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="my-6">
                        <h3 class="text-lg font-semibold mb-2">Classes</h3>
                        <div id="classBadges" class="space-y-4 mb-2">
                            @php
                                $selected = $user->classes->pluck('id')->all();
                                $grouped = $classes->groupBy('hierarquia');
                            @endphp

                            @foreach ([1, 2, 3] as $level)
                                @php $items = $grouped->get($level, collect()); @endphp
                                @if ($items->isNotEmpty())
                                    <div>
                                        <div class="text-sm font-semibold text-gray-600 mb-2">Hierarquia
                                            {{ $level }}</div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($items as $c)
                                                @php $isSel = in_array($c->id, $selected); @endphp
                                                <button type="button" data-id="{{ $c->id }}"
                                                    class="px-3 py-1 rounded-full border text-xs {{ $isSel ? 'bg-orange-600 text-white border-orange-600' : 'bg-gray-100 hover:bg-gray-200' }}">
                                                    {{ $c->classe }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div id="classInputs">
                            @foreach ($user->classes as $c)
                                <input type="hidden" name="classes[]" value="{{ $c->id }}">
                            @endforeach
                        </div>

                        <div class="flex justify-center mt-6">
                            <button type="submit" class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(evt, id) {
            const file = evt.target.files?.[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const el = document.getElementById('preview-user-photo');
            el.outerHTML =
                `<img id=\"preview-user-photo\" src=\"${url}\" class=\"w-40 h-40 object-cover rounded border border-gray-200\" />`;
        }
        // Classes badges toggle
        (function() {
            const badges = document.getElementById('classBadges');
            const inputs = document.getElementById('classInputs');

            function sync() {
                inputs.innerHTML = '';
                badges.querySelectorAll('button[data-selected="true"]').forEach(btn => {
                    const id = btn.getAttribute('data-id');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'classes[]';
                    input.value = id;
                    inputs.appendChild(input);
                });
            }
            badges?.querySelectorAll('button[data-id]')?.forEach(btn => {
                const isActive = btn.classList.contains('bg-orange-500');
                btn.dataset.selected = isActive ? 'true' : 'false';
                btn.addEventListener('click', () => {
                    const sel = btn.dataset.selected === 'true';
                    btn.dataset.selected = String(!sel);
                    btn.className = 'px-3 py-1 rounded-full border text-xs ' + (!sel ?
                        'bg-orange-500 text-white border-orange-500' :
                        'bg-gray-100 hover:bg-gray-200');
                    sync();
                });
            });
            sync();
        })();
    </script>
@endsection
