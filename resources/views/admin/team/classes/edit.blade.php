@php
    $title = 'Classes';
    $subTitle = 'Defina a classe';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.classes.update', $classe) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Classe</label>
                                <input type="text" name="classe" value="{{ old('classe', $classe->classe) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Hierarquia</label>
                                <select name="hierarquia"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                    <option value="1" @selected(old('hierarquia', $classe->hierarquia) == 1)>1 — Primária</option>
                                    <option value="2" @selected(old('hierarquia', $classe->hierarquia) == 2)>2 — Secundária</option>
                                    <option value="3" @selected(old('hierarquia', $classe->hierarquia) == 3)>3 — Final</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" rows="5"
                                class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                placeholder="Descrição da classe">{{ old('description', $classe->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Skills (separe por vírgula ou quebra de
                                linha)</label>
                            @php $skillsValue = is_array($classe->skills) ? implode(', ', $classe->skills) : (string) $classe->skills; @endphp
                            <textarea name="skills" rows="3"
                                class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                placeholder="Ex.: HTML, CSS, Laravel">{{ old('skills', $skillsValue) }}</textarea>
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
@endsection
