<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    /**
     * Redirige al asistente de configuración inicial mientras no exista
     * el registro de settings (primer uso de la aplicación).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('setup.*') && Setting::current() === null) {
            return redirect()->route('setup.create');
        }

        if ($request->routeIs('setup.*') && Setting::current() !== null) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
