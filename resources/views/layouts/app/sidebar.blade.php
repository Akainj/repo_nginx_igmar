<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    @php
        $hostname = gethostname();
        $amber = preg_match('/([0-9])[^0-9]*$/', $hostname ?: '', $digit) && (int) $digit[1] % 2 === 1;
    @endphp
    <body class="olimpo" data-node-accent="{{ $amber ? 'amber' : 'green' }}">
        <div class="olimpo-shell">
            <aside class="olimpo-sidebar">
                <a class="olimpo-brand" href="{{ route('dashboard') }}" wire:navigate>
                    <span class="olimpo-mark" aria-hidden="true">Ω</span>
                    <span>Olimpo <small>Cyber</small></span>
                </a>

                <nav class="olimpo-nav" aria-label="Navegación principal">
                    <a href="{{ route('dashboard') }}" wire:navigate
                        @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        <flux:icon.layout-grid class="size-4" /> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" wire:navigate
                        @if (request()->is('settings/*')) aria-current="{{ request()->routeIs('profile.edit') ? 'page' : 'location' }}" @endif>
                        <flux:icon.cog class="size-4" /> Settings
                    </a>
                </nav>

                <div class="olimpo-account">
                    <span class="olimpo-initials" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                    <div class="olimpo-account-details">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->email }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" data-test="logout-button" aria-label="Cerrar sesión" title="Cerrar sesión">
                            <flux:icon.arrow-right-start-on-rectangle class="size-5" />
                        </button>
                    </form>
                </div>
            </aside>

            {{ $slot }}
        </div>
        @fluxScripts
    </body>
</html>
