@extends('layout.layout')

@section('content')
<div class="container" style="max-width:560px; padding: 60px 15px;">
    <h2 class="mb-4">Recuperar senha</h2>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">Enviar link de redefinição</button>
            <a href="{{ route('admin.login') }}">Voltar ao login</a>
        </div>
    </form>
</div>
@endsection

