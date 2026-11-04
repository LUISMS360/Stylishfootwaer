<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado y si su rol es 'admin'
        if (!$request->user() || $request->user()->role !== 'admin') {
            // Opción 1: Lanza un error HTTP 403 Forbidden (Acceso no autorizado)
            abort(403, 'Acceso no autorizado.');

            // Opción 2 (Alternativa): Redirigir al inicio o dashboard si no es admin
            // return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}