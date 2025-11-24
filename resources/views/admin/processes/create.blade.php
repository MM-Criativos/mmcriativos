@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.processes.store') }}" class="space-y-6">
                        @csrf
                        @include('admin.processes._form', ['process' => $process])

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.processes.index') }}"
                                class="inline-flex items-center px-5 py-3 rounded-md text-sm bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500">
                                Cancelar
                            </a>
                            <button class="inline-flex items-center gap-2 px-5 py-3 rounded-md btn-mmcriativos">
                                <i class="fa-solid fa-plus"></i>
                                <span>Criar Processo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
