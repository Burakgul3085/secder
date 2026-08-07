<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const ALLOWED = ['tr', 'en', 'ar'];
    private const DEFAULT  = 'tr';
    private const COOKIE   = 'bkd_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(self::COOKIE)
            ?? $request->session()->get(self::COOKIE)
            ?? self::DEFAULT;

        if (! in_array($locale, self::ALLOWED, true)) {
            $locale = self::DEFAULT;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
