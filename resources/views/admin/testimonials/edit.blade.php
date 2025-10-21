<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Depoimento</h2>
            <a href="{{ route('admin.testimonials.index') }}"
                class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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

                    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}"
                        class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <select id="client_id" name="client_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected(old('client_id', $testimonial->client_id) == $client->id)>
                                            {{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contato</label>
                                <select id="contact_id" name="contact_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                                    @foreach ($contacts as $contact)
                                        <option value="{{ $contact->id }}" @selected(old('contact_id', $testimonial->contact_id) == $contact->id)>
                                            {{ $contact->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Título (opcional)</label>
                                <input type="text" name="title" value="{{ old('title', $testimonial->title) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Depoimento</label>
                                <textarea name="testimonial" rows="5" class="mt-1 block w-full border-gray-300 rounded-md" required>{{ old('testimonial', $testimonial->testimonial) }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">Salvar</button>
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
                contactSelect.innerHTML = '';
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
        });
    </script>
</x-app-layout>
