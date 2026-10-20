<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * az / ru / en. A ?lang= in the address chooses (and is remembered for a
 * year); otherwise the remembered choice; otherwise Azerbaijani. The
 * browser's language is not guessed from: many phones in Baku are set to
 * English or Russian, and the house speaks Azerbaijani first.
 */
class SetLocale
{
    public const LOCALES = ['az' => 'AZ', 'ru' => 'RU', 'en' => 'EN'];

    public function handle(Request $request, Closure $next): Response
    {
        $asked = $request->query('lang');
        $locale = match (true) {
            isset(self::LOCALES[$asked]) => $asked,
            isset(self::LOCALES[$request->cookie('lang')]) => $request->cookie('lang'),
            default => 'az',
        };

        app()->setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        $response = $next($request);

        if (isset(self::LOCALES[$asked])) {
            $response->headers->setCookie(cookie('lang', $asked, 60 * 24 * 365));
        }

        return $response;
    }
}
