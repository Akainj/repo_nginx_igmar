@extends('layout')
@section('title', 'Verifica tu identidad')
@section('content')
    <form method="post" action="{{ route('two-factor.login.store') }}">
        @csrf
        <label>Código de 6 dígitos de tu autenticador
            <input name="code" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
        </label>
        <button type="submit">Iniciar sesión</button>
    </form>
    <details>
        <summary>Usar un código de recuperación</summary>
        <form method="post" action="{{ route('two-factor.login.store') }}">
            @csrf
            <label>Código de recuperación
                <input name="recovery_code" autocomplete="off" required>
            </label>
            <button type="submit">Verificar código</button>
        </form>
    </details>
    <p><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
@endsection
