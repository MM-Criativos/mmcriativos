<style>
    .navbar-header {
        margin-top: -20px !important;
    }
</style>

@extends('layouts.app')

@php
    $title = 'Clientes';
    $subTitle = 'Editar cliente';
@endphp

@section('content')
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

                <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf
                    <div class="flex flex-col items-center justify-center text-center">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>

                        <div class="relative group cursor-pointer w-40 h-40">
                            <input type="file" name="logo" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                onchange="previewImage(event, 'client-logo-new')">

                            <div id="preview-client-logo-new"
                                class="flex items-center justify-center w-40 h-40 border border-dashed  border-gray-300 rounded bg-[#f5f5f5] dark:!bg-[#262626] text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                <i class="fa-regular fa-image text-base mr-1"></i> Logo
                            </div>
                        </div>
                    </div>


                    <script>
                        function previewImage(event, id) {
                            const reader = new FileReader();
                            const preview = document.getElementById(`preview-${id}`);
                            reader.onload = () => {
                                if (preview.tagName === 'IMG') {
                                    preview.src = reader.result;
                                } else {
                                    preview.outerHTML = `
                <img id="preview-${id}"
                     src="${reader.result}"
                     class="w-40 h-40 object-cover rounded border border-gray-200" />`;
                                }
                            };
                            reader.readAsDataURL(event.target.files[0]);
                        }
                    </script>
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
                            <label class="block text-sm font-medium text-gray-700">Website</label>
                            <input type="text" name="website" value="{{ old('website') }}"
                                class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                placeholder="https://...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Setor</label>
                            <input type="text" name="sector" value="{{ old('sector') }}"
                                class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                placeholder="ex: Tecnologia, Saúde">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="description" rows="3"
                            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">{{ old('description') }}</textarea>
                    </div>


                    <div class="flex justify-center">
                        <button type="submit" class="inline-flex items-center px-6 py-4 rounded-md btn-mmcriativos">Criar
                            Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
