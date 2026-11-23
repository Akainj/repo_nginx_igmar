<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireFreshLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(['remember' => false]);
        $request->session()->forget(['login.id', 'login.remember']);

        return $next($request);
    }
}
