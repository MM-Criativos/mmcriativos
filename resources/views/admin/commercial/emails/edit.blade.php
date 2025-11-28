@php
    $title = 'Templates';
    $subTitle = 'Edite os templates de e-mails';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
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
                    <form method="POST"
                        action="{{ $template->exists ? route('admin.commercial.email-templates.update', $template) : route('admin.commercial.email-templates.store') }}"
                        class="space-y-4">
                        @csrf
                        @if ($template->exists)
                            @method('PUT')
                        @endif
                        @include('admin.commercial.emails._form', ['template' => $template])
                        <div class="flex justify-center gap-2">
                            <button class="px-5 py-3 btn-mmcriativos rounded-md">Salvar</button>
                            <a href="{{ route('admin.commercial.email-templates.index') }}"
                                class="px-5 py-3 rounded-md bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
