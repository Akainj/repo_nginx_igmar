@extends('layout')
@section('title', 'Configura la verificación en dos pasos')
@section('content')
    <p>Escanea el QR con Google Authenticator o añade la clave manualmente. Ingresa el código generado para activar la verificación.</p>
    <div class="qr" role="img" aria-label="Código QR de configuración">{!! $qr !!}</div>
    <p>Clave manual <code class="secret">{{ $secret }}</code></p>
    <form method="post" action="{{ route('two-factor.enroll.confirm') }}">
        @csrf
        <label>Código de 6 dígitos
            <input name="code" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
        </label>
        <button type="submit">Activar verificación</button>
    </form>
@endsection
