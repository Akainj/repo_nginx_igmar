@extends('layout')
@section('title', 'Inicio')
@section('content')
    <p>Hola, <strong>{{ auth()->user()->name }}</strong>.</p>
    <dl>
        <dt>Servidor que atendió la petición</dt>
        <dd><code>{{ $hostname ?: 'No disponible' }}</code></dd>
    </dl>
@endsection
