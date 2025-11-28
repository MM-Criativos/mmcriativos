@php
    $title = 'Meu Perfil';
    $subTitle = 'Edite suas informações';
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
                    <form id="settingsForm" method="POST" action="{{ route('admin.settings.store') }}" class="space-y-8">
                        @csrf

                        <div>
                            <h3 class="text-lg font-semibold mb-2">Endereço</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CEP</label>
                                    <input type="text" name="address_zipcode"
                                        value="{{ old('address_zipcode', optional($setting)->address_zipcode) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                        placeholder="00000-000">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Rua</label>
                                    <input type="text" name="address_street"
                                        value="{{ old('address_street', optional($setting)->address_street) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Número</label>
                                    <input type="text" name="address_number"
                                        value="{{ old('address_number', optional($setting)->address_number) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Complemento</label>
                                    <input type="text" name="address_complement"
                                        value="{{ old('address_complement', optional($setting)->address_complement) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bairro</label>
                                    <input type="text" name="address_neighborhood"
                                        value="{{ old('address_neighborhood', optional($setting)->address_neighborhood) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cidade</label>
                                    <input type="text" name="address_city"
                                        value="{{ old('address_city', optional($setting)->address_city) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                                    <input type="text" name="address_state"
                                        value="{{ old('address_state', optional($setting)->address_state) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">País</label>
                                    <input type="text" name="address_country"
                                        value="{{ old('address_country', optional($setting)->address_country ?? 'Brasil') }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-2">Contato</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                    <input type="text" name="phone"
                                        value="{{ old('phone', optional($setting)->phone) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">E-mail Suporte</label>
                                    <input type="email" name="email_support"
                                        value="{{ old('email_support', optional($setting)->email_support) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">E-mail Contato</label>
                                    <input type="email" name="email_contact"
                                        value="{{ old('email_contact', optional($setting)->email_contact) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">E-mail Comercial</label>
                                    <input type="email" name="email_commercial"
                                        value="{{ old('email_commercial', optional($setting)->email_commercial) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mt-10 mb-2">Redes Sociais</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @php
                                    // Mapeamento da rede → ícone FontAwesome
                                    $socialIcons = [
                                        'facebook' => 'fa-brands fa-facebook-f',
                                        'instagram' => 'fa-brands fa-instagram',
                                        'tiktok' => 'fa-brands fa-tiktok',
                                        'x' => 'fa-brands fa-x-twitter',
                                        'linkedin' => 'fa-brands fa-linkedin-in',
                                        'youtube' => 'fa-brands fa-youtube',
                                        'behance' => 'fa-brands fa-behance',
                                        'dribbble' => 'fa-brands fa-dribbble',
                                        'github' => 'fa-brands fa-github',
                                        'whatsapp' => 'fa-brands fa-whatsapp',
                                    ];
                                @endphp

                                @foreach (array_keys($socialIcons) as $field)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 capitalize">
                                            <i
                                                class="{{ $socialIcons[$field] }} text-orange-500 mr-2"></i>{{ ucfirst($field) }}
                                        </label>
                                        <input type="text" name="{{ $field }}"
                                            value="{{ old($field, optional($setting)->{$field}) }}"
                                            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                            placeholder="https://{{ $field }}.com/...">
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="flex justify-center">
                            <button class="inline-flex items-center px-6 py-3 btn-mmcriativos rounded-md">Salvar
                                Configurações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Salvar via AJAX (sem recarregar)
        document.getElementById('settingsForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });
            if (res.ok) {
                const toast = document.createElement('div');
                toast.textContent = 'Configurações salvas';
                toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            }
        });
    </script>
@endsection
