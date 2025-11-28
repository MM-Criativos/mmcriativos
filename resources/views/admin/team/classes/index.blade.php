@php
    $title = 'Classes';
    $subTitle = 'Qual sua função na MM Criativos';
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
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-orange-500 uppercase tracking-wider">
                                        #</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-orange-500 uppercase tracking-wider">
                                        Classe</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-orange-500 uppercase tracking-wider">
                                        Hierarquia</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-orange-500 uppercase tracking-wider">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($classes as $c)
                                    <tr>
                                        <td class="px-4 py-3">{{ $c->id }}</td>
                                        <td class="px-4 py-3">{{ $c->classe }}</td>
                                        <td class="px-4 py-3">{{ $c->hierarquia }}</td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.classes.edit', $c) }}"
                                                class="inline-flex items-center gap-1 px-3 py-2 btn-mmcriativos rounded-md">
                                                <i class="fa-duotone fa-pen-to-square icon-project mr-2"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
