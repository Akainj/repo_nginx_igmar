<?php

use App\Http\Controllers\TwoFactorEnrollmentController;
use App\Http\Middleware\RequireFreshLogin;
use App\Http\Middleware\VerifyTwoFactorChallenge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['throttle:login', RequireFreshLogin::class])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware(['throttle:5,1', RequireFreshLogin::class])->name('register.store');
    Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
        ->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware(['throttle:two-factor', VerifyTwoFactorChallenge::class])
        ->name('two-factor.login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/configurar-verificacion', [TwoFactorEnrollmentController::class, 'show'])
        ->name('two-factor.enroll');
    Route::post('/configurar-verificacion', [TwoFactorEnrollmentController::class, 'confirm'])
        ->middleware('throttle:enrollment')->name('two-factor.enroll.confirm');

    Route::get('/', function (Request $request) {
        if (! $request->user()->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.enroll');
        }

        if ($request->session()->get('two_factor.user_id') !== $request->user()->getAuthIdentifier()) {
            Auth::logoutCurrentDevice();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return response()->view('home', ['hostname' => gethostname()])
            ->header('Cache-Control', 'no-store');
    })->name('home');
});
