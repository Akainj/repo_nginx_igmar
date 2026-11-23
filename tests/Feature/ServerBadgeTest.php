<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

test('the server badge is visible on public pages', function (string $route) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee('Atendido por: '.gethostname());
})->with(['home', 'login', 'register', 'password.request']);

test('the official settings activate two factor and login challenges for it', function () {
    $authenticator = new class extends Google2FA
    {
        public int $step = 0;

        public function getTimestamp()
        {
            return parent::getTimestamp() + $this->step;
        }
    };

    $this->app->instance(Google2FA::class, $authenticator);
    $user = User::factory()->create();
    $badge = 'Atendido por: '.gethostname();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($badge);

    $this->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('two-factor.show'))
        ->assertOk()
        ->assertSee($badge);

    $setup = Livewire::test('pages::settings.two-factor')
        ->call('enable')
        ->assertSet('twoFactorEnabled', false);

    expect($setup->get('qrCodeSvg'))->toContain('<svg');
    $secret = $setup->get('manualSetupKey');

    $setup->call('showVerificationIfNecessary')
        ->set('code', $authenticator->getCurrentOtp($secret))
        ->call('confirmTwoFactor')
        ->assertSet('twoFactorEnabled', true);

    expect($user->fresh()->recoveryCodes())->toHaveCount(8);

    $this->post(route('logout'));
    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    $this->get(route('two-factor.login'))->assertOk()->assertSee($badge);

    $authenticator->step++;

    $this->post(route('two-factor.login.store'), [
        'code' => $authenticator->getCurrentOtp($secret),
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});
