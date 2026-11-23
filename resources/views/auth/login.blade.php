@extends('layout')
@section('title', 'Iniciar sesión')
@section('content')
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <label>Correo electrónico
            <input type="email" name="email" autocomplete="username" value="{{ old('email') }}" required>
        </label>
        <label>Contraseña
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button type="submit">Continuar</button>
    </form>
    <p><a href="{{ route('register') }}">Crear una cuenta</a></p>
@endsection
