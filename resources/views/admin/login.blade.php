@extends('layout.layout')

@section('content')
<div class="container" style="max-width:560px; padding: 60px 15px;">
    <h2 class="mb-4">Acesso Administrativo</h2>

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

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Lembrar-me</label>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">Entrar</button>
            <a href="{{ route('admin.password.request') }}">Esqueci minha senha</a>
        </div>
    </form>
</div>
@endsection

