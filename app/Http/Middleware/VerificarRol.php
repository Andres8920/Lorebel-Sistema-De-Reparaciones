<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Restringe una ruta a uno o más roles. Uso: ->middleware('rol:administrador,recepcion')
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! $request->user() || ! $request->user()->tieneRol(...$roles)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
