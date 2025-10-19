@extends('layout.layout')

@section('content')
<div class="container" style="padding: 60px 15px;">
    <h2 class="mb-3">Bem-vindo ao Admin</h2>
    <p class="mb-4">Você está autenticado.</p>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger">Sair</button>
    </form>
</div>
@endsection

