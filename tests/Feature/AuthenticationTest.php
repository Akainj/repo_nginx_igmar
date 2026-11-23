<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_registration_enrollment_and_login_require_two_factors(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->artisan('migrate')->assertSuccessful();
        }

        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $authenticator = new class extends Google2FA
        {
            public int $step = 0;

            public function getTimestamp()
            {
                return parent::getTimestamp() + $this->step;
            }
        };
        $this->app->instance(Google2FA::class, $authenticator);

        $email = Str::uuid().'@example.com';
        $password = Str::random(24);

        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('register'))->assertOk();

        $this->post(route('register.store'), [
            'name' => 'Estudiante de prueba',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertRedirect(route('home'));

        $this->get('/')->assertRedirect(route('two-factor.enroll'));
        $setup = $this->get(route('two-factor.enroll'))->assertOk()->assertSee('<svg', false);
        $secret = decrypt(auth()->user()->fresh()->two_factor_secret);
        $setup->assertSee($secret);
        $firstCode = $authenticator->getCurrentOtp($secret);

        $this->from(route('two-factor.enroll'))->post(route('two-factor.enroll.confirm'), [
            'code' => str_pad((string) (((int) $firstCode + 1) % 1000000), 6, '0', STR_PAD_LEFT),
        ])->assertSessionHasErrorsIn('confirmTwoFactorAuthentication', ['code']);
        $this->get('/')->assertRedirect(route('two-factor.enroll'));

        $result = $this->post(route('two-factor.enroll.confirm'), ['code' => $firstCode])->assertOk();
        $recovery = auth()->user()->fresh()->recoveryCodes()[0];
        $result->assertSee($recovery);
        $this->get('/')->assertOk()->assertSee('Estudiante de prueba')->assertSee(gethostname());
        $this->get(route('two-factor.enroll'))->assertRedirect(route('home'));

        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login.store'), compact('email', 'password'))
            ->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('two-factor.login'))->assertOk();
        $this->from(route('two-factor.login'))->post(route('two-factor.login.store'), [
            'code' => $firstCode,
        ])->assertSessionHasErrors('code');
        $this->assertGuest();

        $authenticator->step++;
        $this->post(route('two-factor.login.store'), [
            'code' => $authenticator->getCurrentOtp($secret),
        ])->assertRedirect(route('home'));
        $this->get('/')->assertOk();

        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login.store'), compact('email', 'password'))
            ->assertRedirect(route('two-factor.login'));
        $this->post(route('two-factor.login.store'), ['recovery_code' => $recovery])
            ->assertRedirect(route('home'));
        $this->get('/')->assertOk();

        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login.store'), compact('email', 'password'))
            ->assertRedirect(route('two-factor.login'));
        $this->from(route('two-factor.login'))->post(route('two-factor.login.store'), [
            'recovery_code' => $recovery,
        ])->assertSessionHasErrors('recovery_code');
        $this->assertGuest();
    }
}
