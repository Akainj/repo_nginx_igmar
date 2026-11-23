<x-layouts::app :title="__('Dashboard')">
    <header class="olimpo-heading">
        <h1>Panel de control</h1>
        <p>Comprueba qué servidor respondió a esta solicitud.</p>
    </header>

    <section class="olimpo-terminal" aria-labelledby="node-label">
        <div class="olimpo-terminal-bar">
            <h2 id="node-label">Servidor que atendió la petición</h2>
            <span class="olimpo-response">Solicitud atendida</span>
        </div>
        <div class="olimpo-terminal-body">
            <span class="olimpo-prompt" aria-hidden="true">&gt;_</span>
            <code>{{ gethostname() ?: 'No disponible' }}</code>
        </div>
        <div class="olimpo-meander" aria-hidden="true"></div>
    </section>

    <div class="olimpo-cards">
        <section class="olimpo-panel">
            <flux:icon.user class="size-5" />
            <h2>Sesión activa</h2>
            <p>{{ auth()->user()->name }}</p>
        </section>
        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <section class="olimpo-panel">
                <flux:icon.shield-check class="size-5" />
                <h2>Autenticación en dos pasos</h2>
                <p>{{ auth()->user()->hasEnabledTwoFactorAuthentication() ? 'Activada' : 'Desactivada' }}</p>
                <a href="{{ route('two-factor.show') }}" wire:navigate>Configurar 2FA</a>
            </section>
        @endif
    </div>
</x-layouts::app>
