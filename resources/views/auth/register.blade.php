@extends('layout')
@section('title', 'Crear una cuenta')
@section('content')
    <form method="post" action="{{ route('register.store') }}">
        @csrf
        <label>Nombre
            <input name="name" autocomplete="name" maxlength="255" value="{{ old('name') }}" required>
        </label>
        <label>Correo electrónico
            <input type="email" name="email" autocomplete="username" value="{{ old('email') }}" required>
        </label>
        <label>Contraseña (mínimo 12 caracteres)
            <input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" required>
        </label>
        <label>Confirma la contraseña
            <input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="72" required>
        </label>
        <button type="submit">Registrarme</button>
    </form>
    <p><a href="{{ route('login') }}">Ya tengo una cuenta</a></p>
@endsection
