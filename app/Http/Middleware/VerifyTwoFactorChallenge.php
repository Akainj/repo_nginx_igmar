<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifyTwoFactorChallenge
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('login.id');

        if ($userId === null) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required_without:recovery_code', 'nullable', 'digits:6'],
            'recovery_code' => ['required_without:code', 'nullable', 'string', 'max:32'],
        ]);

        return DB::transaction(function () use ($request, $next, $userId): Response {
            $user = User::query()->lockForUpdate()->find($userId);

            if (! $user?->hasEnabledTwoFactorAuthentication()) {
                $request->session()->forget(['login.id', 'login.remember']);

                return redirect()->route('login');
            }

            $request->session()->forget('login.remember');
            $response = $next($request);

            if ($request->user()?->getAuthIdentifier() === $user->getAuthIdentifier()) {
                $request->session()->put('two_factor.user_id', $user->getAuthIdentifier());
            }

            return $response;
        });
    }
}
