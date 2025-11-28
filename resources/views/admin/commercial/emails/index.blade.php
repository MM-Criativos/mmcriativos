@php
    $title = 'Template';
    $subTitle = 'Veja o corpo dos nossos e-mails';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($templates->isEmpty())
                        <p class="text-gray-600">Nenhum template encontrado.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="text-left text-gray-600 text-sm border-b">
                                        <th class="py-2 pr-4">Key</th>
                                        <th class="py-2 pr-4">Nome</th>
                                        <th class="py-2 pr-4">Ativo</th>
                                        <th class="py-2 pr-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($templates as $t)
                                        <tr class="border-b">
                                            <td class="py-3 pr-4">{{ $t->key }}</td>
                                            <td class="py-3 pr-4">{{ $t->name }}</td>
                                            <td class="py-3 pr-4">{{ $t->is_active ? 'Sim' : 'Não' }}</td>
                                            <td class="py-3 pr-4 text-sm flex gap-3">
                                                <a href="{{ route('admin.commercial.email-templates.edit', $t) }}"
                                                    class="inline-flex items-center px-3 py-2 btn-mmcriativos rounded-md"><i
                                                        class="fa-duotone fa-solid fa-pen-to-square icon-project"></i></a>
                                                <a href="{{ route('admin.commercial.email-templates.preview', $t) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center px-3 py-2 btn-mmcriativos rounded-md"><i
                                                        class="fa-duotone fa-solid fa-eye icon-project"></i></a>
                                                <form method="POST"
                                                    action="{{ route('admin.commercial.email-templates.destroy', $t) }}"
                                                    onsubmit="return confirm('Remover template?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="inline-flex items-center px-4 py-3 rounded-md bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500"><i
                                                            class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $templates->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
