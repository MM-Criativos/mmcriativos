@extends('layouts.app')

@php
    $title = 'Projetos';
    $subTitle = 'Criar novo projeto';
@endphp

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

                    <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug') }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    placeholder="opcional">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <select name="client_id"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md">
                                    <option value="">Selecione...</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Serviço</label>
                                <select name="service_id"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:bg-[#262626] border-gray-300 rounded-md">
                                    <option value="">Selecione...</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                            {{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit" class="inline-flex items-center px-6 py-4 rounded btn-mmcriativos">
                                Criar Projeto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
