@extends('layouts.app')

@php
    $title = 'Clientes';
    $subTitle = 'Contatos';
    $script = '<script>
        $(".delete-btn").on("click", function() {
            $(this).closest(".user-grid-card").addClass("hidden")
        });
    </script>';
@endphp

@section('content')
    <div class="grid grid-cols-1 mt-5">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
            <div
                class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-gradient-to-r from-orange-500 to-transparent py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-2">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-0">Contatos de
                        {{ $client->name }}</h3>
                    <span class="text-secondary-light text-sm">{{ $contacts->count() }} contato(s)</span>
                </div>
                <a href="{{ route('admin.clients.contacts.create', $client) }}"
                    class="btn btn-mmcriativos text-sm btn-sm px-3 py-3 rounded-lg flex items-center gap-2">
                    <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                    Novo contato
                </a>
            </div>
            <div class="card-body p-6">
                @if ($contacts->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">Nenhum contato cadastrado ainda.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach ($contacts as $contact)
                            <div class="user-grid-card">
                                <div
                                    class="bg-[#f5f5f5] dark:bg-[#262626] relative border border-neutral-200 dark:border-neutral-600 rounded-2xl overflow-hidden">
                                    <img src="{{ asset('admin/images/user-grid/user-grid-bg1.png') }}" alt=""
                                        class="w-full object-fit-cover">

                                    <div class="absolute top-4 start-4">
                                        @if ($contact->is_primary)
                                            <span
                                                class="inline-flex border-2 items-center gap-2 px-3 py-3 rounded-md bg-white dark:bg-black border-[#ff8800]">
                                                <i class="fa-duotone fa-solid fa-crown icon-project"></i></span>
                                        @endif
                                    </div>

                                    <div class="dropdown absolute top-0 end-0 me-4 mt-4">
                                        <button data-dropdown-toggle="dropdown-{{ $contact->id }}"
                                            class="flex px-4 py-2.5 btn-mmcriativos rounded-md" type="button">
                                            <i class="fa-duotone fa-solid fa-colon icon-project"></i>
                                        </button>

                                        <div id="dropdown-{{ $contact->id }}"
                                            class="z-10 hidden bg-white dark:bg-black divide-y divide-gray-100 rounded-lg shadow-lg border border-neutral-100 dark:border-neutral-600 w-44">
                                            <ul class="p-2 text-sm text-gray-700 dark:text-gray-200">
                                                <li>
                                                    <a href="{{ route('admin.contacts.edit', $contact) }}"
                                                        class="w-full text-start px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-[#262626] rounded dark:hover:text-white flex items-center gap-2">
                                                        <i class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                                                        Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <form method="POST"
                                                        action="{{ route('admin.contacts.destroy', $contact) }}"
                                                        onsubmit="return confirm('Tem certeza que deseja apagar este contato?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="delete-btn w-full text-start px-4 py-2.5 bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 gap-2">
                                                            <i class="fa-regular fa-trash"></i>
                                                            Apagar
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pe-6 pb-4 ps-6 text-center mt--50">
                                        <img src="{{ $contact->photo ? asset($contact->photo) : asset('assets/images/user-grid/user-grid-img1.png') }}"
                                            alt="{{ $contact->name }}"
                                            class="w-[120px] h-[120px] ms-auto me-auto -mt-[70px] rounded-full object-fit-cover mb-5"
                                            draggable="false">
                                        <div class="flex items-center justify-center gap-2 mt-1.5 flex-wrap">
                                            <h6 class="text-lg mb-0">{{ $contact->name }}</h6>

                                        </div>

                                        @if ($contact->email)
                                            <span class="text-secondary-light mb-4 block">{{ $contact->email }}</span>
                                        @endif

                                        <div
                                            class="center-border bg-white dark:bg-black relative rounded-lg p-3 flex items-center gap-4 before:absolute before:w-px before:h-full before:z-[1] before:bg-[#f5f5f5] dark:before:bg-[#262626] before:start-1/2">
                                            <div class="text-center w-1/2">
                                                <h6 class="text-base mb-0">{{ $contact->role ?: 'Sem cargo' }}</h6>
                                                <span class="!text-orange-500 text-sm mb-0">Cargo</span>
                                            </div>
                                            <div class="text-center w-1/2">
                                                <h6 class="text-base mb-0">{{ $contact->phone ?: 'Sem telefone' }}
                                                </h6>
                                                <span class="!text-orange-500 text-sm mb-0">Telefone</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
