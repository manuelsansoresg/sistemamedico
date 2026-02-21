<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDoctorStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('doctor')) {
            // Si el estatus es pendiente o rechazada, bloquear acceso
            if (in_array($user->estatus_cedula, ['pendiente', 'rechazada'])) {

                // Permitir acceso a la ruta de logout y a la página de aviso
                if ($request->routeIs('logout') || $request->routeIs('doctor.verification.notice')) {
                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Tu cuenta requiere validación de cédula.'], 403);
                }

                return redirect()->route('doctor.verification.notice');
            }
        }

        return $next($request);
    }
}
