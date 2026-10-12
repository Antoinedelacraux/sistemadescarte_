<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->canAccessModule($modulo)) {
            abort(403, "No cuentas con autorización para acceder al módulo de {$modulo}.");
        }

        return $next($request);
    }
}
