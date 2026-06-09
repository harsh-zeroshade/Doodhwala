<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('doodhwala.default_locale', 'en'));

        if (! in_array($locale, config('doodhwala.supported_locales', ['en', 'hi']), true)) {
            $locale = config('doodhwala.default_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
