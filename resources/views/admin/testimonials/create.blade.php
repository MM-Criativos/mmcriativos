@php
    $title = 'Depoimentos';
    $subTitle = 'Crie um novo depoimento';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.testimonials.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <select id="client_id" name="client_id"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                                    <option value="">Selecione...</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                            {{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contato</label>
                                <select id="contact_id" name="contact_id"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                                    <option value="">Selecione um cliente primeiro...</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Título (opcional)</label>
                                <input type="text" name="title" value="{{ old('title') }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Depoimento</label>
                                <textarea name="testimonial" rows="5"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md" required>{{ old('testimonial') }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md">Criar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const clientSelect = document.getElementById('client_id');
        const contactSelect = document.getElementById('contact_id');

        async function loadContacts(clientId, preselect = null) {
            contactSelect.innerHTML = '<option>Carregando...</option>';
            try {
                const res = await fetch(`{{ url('/admin/clients') }}/${clientId}/contacts/select`);
                const data = await res.json();
                contactSelect.innerHTML = '<option value="">Selecione...</option>';
                data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    if (preselect && String(preselect) === String(c.id)) opt.selected = true;
                    contactSelect.appendChild(opt);
                });
            } catch (e) {
                contactSelect.innerHTML = '<option>Erro ao carregar contatos</option>';
            }
        }

        clientSelect?.addEventListener('change', (e) => {
            const id = e.target.value;
            if (id) loadContacts(id);
            else contactSelect.innerHTML = '<option value="">Selecione um cliente primeiro...</option>';
        });

        // Restore old selection if validation failed
        @if (old('client_id'))
            loadContacts({{ (int) old('client_id') }}, {{ (int) old('contact_id') ?: 'null' }});
        @endif
    </script>
@endsection
