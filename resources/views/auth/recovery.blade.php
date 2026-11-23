@extends('layout')
@section('title', 'Guarda tus códigos de recuperación')
@section('content')
    <p>La verificación ya está activa. Guarda estos códigos en un lugar seguro. Cada uno sirve una sola vez y esta lista se muestra al activar la verificación.</p>
    <ul class="codes">
        @foreach ($codes as $code)
            <li><code>{{ $code }}</code></li>
        @endforeach
    </ul>
    <a class="button" href="{{ route('home') }}">Ir al inicio</a>
@endsection
