@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

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
                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ route('admin.processes.update', $process) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        @include('admin.processes._form', ['process' => $process])

                        <div class="flex justify-center gap-3">
                            <button class="inline-flex items-center gap-2 px-5 py-3 rounded-md btn-mmcriativos">
                                <i class="fa-duotone fa-solid fa-arrow-rotate-right icon-project"></i>
                                <span>Salvar alterações</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
