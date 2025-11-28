@php
    $title = 'Equipe';
    $subTitle = 'Membros da equipe MM Criativos';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
                <div
                    class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-gradient-to-r from-orange-500 to-transparent py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                    <div class="flex items-center flex-wrap gap-2">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-0">Membros</h3>
                    </div>
                    <a href="{{ route('admin.classes.index') }}"
                        class="btn btn-mmcriativos text-sm btn-sm px-3 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-duotone fa-solid fa-eye icon-project"></i>
                        Ver classes
                    </a>
                </div>
                <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nome</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            E-mail</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($users as $user)
                                        <tr>
                                            <td class="px-4 py-3">{{ $user->name }}</td>
                                            <td class="px-4 py-3">{{ $user->email }}</td>
                                            <td class="px-4 py-3">
                                                <form method="POST" action="{{ route('admin.team.role', $user) }}"
                                                    class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="role"
                                                        class="bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md text-sm"
                                                        onchange="this.form.submit()">
                                                        <option value="user" @selected($user->role === 'user')>Usuário</option>
                                                        <option value="admin" @selected($user->role === 'admin')>Administrador
                                                        </option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    @if (!$user->is_approved)
                                                        <form method="POST"
                                                            action="{{ route('admin.team.approve', $user) }}"
                                                            class="inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button
                                                                class="inline-flex items-center gap-1 px-3 py-2 bg-green-800 text-white rounded text-xs hover:bg-green-700">
                                                                <i class="fa-solid fa-check"></i> Aprovar
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span
                                                            class="text-xs text-white bg-green-800 px-3 py-2 rounded">Aprovado</span>
                                                    @endif

                                                    @if (auth()->user()->role === 'admin' && auth()->id() !== $user->id)
                                                        <form method="POST"
                                                            action="{{ route('admin.team.destroy', $user) }}" class="inline"
                                                            onsubmit="return confirm('Excluir este usuário?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button
                                                                class="inline-flex items-center gap-1 px-3 py-2 bg-red-600 text-white rounded text-xs hover:bg-red-700"><i
                                                                    class="fa-regular fa-trash"></i> Excluir</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('admin.team.edit', $user) }}"
                                                        class="inline-flex items-center gap-1 px-3 py-2 btn-mmcriativos rounded-md">
                                                        <i class="fa-duotone fa-pen-to-square icon-project"></i> Editar
                                                    </a>
                                                </div>
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
    </div>
@endsection
