<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDoctorStatus
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('doctor')) {
            if (in_array($user->estatus_cedula, ['pendiente', 'rechazada'])) {
                if ($request->routeIs('logout') || $request->routeIs('doctor.verification.notice')) {
                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => __('doctor.verification.required')], 403);
                }

                return redirect()->route('doctor.verification.notice');
            }

            if (! $this->subscriptionService->hasActivePackage($user)) {
                if ($request->routeIs('logout') || $request->routeIs('doctor.verification.notice') || $request->routeIs('compras.*') || $request->routeIs('suscripciones.*')) {
                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => __('subscriptions.errors.package_expired')], 403);
                }

                return redirect()->route('compras.index')->with('error', __('subscriptions.errors.package_expired'));
            }
        }

        return $next($request);
    }
}
