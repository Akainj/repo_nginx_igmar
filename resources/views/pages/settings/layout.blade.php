<div class="olimpo-settings">
    <nav class="olimpo-nav olimpo-settings-nav" aria-label="{{ __('Settings') }}">
        @foreach (['profile.edit' => 'Profile', 'user-password.edit' => 'Password'] as $route => $label)
            <a href="{{ route($route) }}" wire:navigate @if (request()->routeIs($route)) aria-current="page" @endif>{{ __($label) }}</a>
        @endforeach
        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <a href="{{ route('two-factor.show') }}" wire:navigate @if (request()->routeIs('two-factor.show')) aria-current="page" @endif>{{ __('Two-Factor Auth') }}</a>
        @endif
        <a href="{{ route('appearance.edit') }}" wire:navigate @if (request()->routeIs('appearance.edit')) aria-current="page" @endif>{{ __('Appearance') }}</a>
    </nav>

    <section class="olimpo-panel olimpo-settings-panel">
        <h2>{{ $heading ?? '' }}</h2>
        <p>{{ $subheading ?? '' }}</p>
        <div class="olimpo-settings-form">
            {{ $slot }}
        </div>
    </section>
</div>
