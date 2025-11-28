@php
    $title = 'Serviços Extras';
    $subTitle = 'Crie serviços adicionais';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.commercial.extras.store') }}" class="space-y-4">
                        @csrf
                        @include('admin.commercial.extras._form', ['extra' => $extra])
                        <div class="flex justify-center mt-5 gap-2">
                            <button class="px-5 py-3 rounded-md btn-mmcriativos">Salvar</button>
                            <a href="{{ route('admin.commercial.extras.index') }}"
                                class="px-5 py-3 rounded-md bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
