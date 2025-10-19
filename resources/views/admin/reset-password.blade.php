@extends('layout.layout')

@section('content')
<div class="container" style="max-width:560px; padding: 60px 15px;">
    <h2 class="mb-4">Definir nova senha</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ old('email', $email) }}">

        <div class="mb-3">
            <label class="form-label">Nova senha</label>
            <input type="password" name="password" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar senha</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">Atualizar senha</button>
            <a href="{{ route('admin.login') }}">Voltar ao login</a>
        </div>
    </form>
</div>
@endsection

