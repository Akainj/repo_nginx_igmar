<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class TwoFactorEnrollmentController extends Controller
{
    public function show(Request $request, EnableTwoFactorAuthentication $enable): Response|RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $enable): User {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->getAuthIdentifier());

            if (! $user->hasEnabledTwoFactorAuthentication()) {
                $enable($user);
            }

            return $user;
        });

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('home');
        }

        return response()->view('auth.enroll', [
            'qr' => $user->twoFactorQrCodeSvg(),
            'secret' => decrypt($user->two_factor_secret),
        ])->header('Cache-Control', 'no-store');
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): Response|RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $codes = DB::transaction(function () use ($request, $confirm, $validated): ?array {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->getAuthIdentifier());

            if ($user->hasEnabledTwoFactorAuthentication()) {
                return null;
            }

            $confirm($user, $validated['code']);

            return $user->recoveryCodes();
        });

        if ($codes === null) {
            return redirect()->route('home');
        }

        $request->user()->refresh();
        $request->session()->regenerate();
        $request->session()->put('two_factor.user_id', $request->user()->getAuthIdentifier());

        return response()->view('auth.recovery', ['codes' => $codes])
            ->header('Cache-Control', 'no-store');
    }
}
