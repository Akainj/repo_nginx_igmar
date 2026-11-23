<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
</head>
<body>
    <main class="panel">
        <header class="brand">
            <img src="{{ asset('images/escudo.svg') }}" width="40" height="40" alt="">
            <span>{{ config('app.name') }}</span>
        </header>
        <h1>@yield('title')</h1>
        @php($messages = array_merge($errors->all(), $errors->getBag('confirmTwoFactorAuthentication')->all()))
        @if ($messages !== [])
            <ul class="errors" role="alert">
                @foreach ($messages as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        @yield('content')
        @auth
            <form class="logout" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="secondary" type="submit">Cerrar sesión</button>
            </form>
        @endauth
    </main>
</body>
</html>
