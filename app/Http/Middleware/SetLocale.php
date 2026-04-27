<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', $request->cookie('app_locale', $request->getPreferredLanguage(['es', 'en'])));

        if (! in_array($locale, ['es', 'en'], true)) {
            $locale = 'es';
        }

        app()->setLocale($locale);
        session()->put('locale', $locale);

        $response = $next($request);

        $response->headers->setCookie(cookie()->forever('app_locale', $locale));

        return $response;
    }
}
