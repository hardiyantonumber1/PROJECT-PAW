<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk setiap respons HTTP.
 *
 * Sengaja konservatif: CSP mengizinkan 'unsafe-inline' pada script & style
 * karena tampilan SIAF memakai atribut onclick dan style inline. Yang tetap
 * ditegakkan: tidak boleh memuat script dari domain luar, tidak boleh
 * di-frame situs lain, dan tidak boleh diindeks mesin pencari.
 */
class KeamananHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'SAMEORIGIN',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Permissions-Policy'      => 'geolocation=(), microphone=(), camera=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            // Data aspirasi memuat keluhan pribadi: jangan pernah diindeks.
            'X-Robots-Tag'            => 'noindex, nofollow, noarchive',
            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                "img-src 'self' data:",
                "font-src 'self' https://fonts.gstatic.com data:",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "script-src 'self' 'unsafe-inline'",
                "connect-src 'self'",
            ]),
        ];

        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $nama => $nilai) {
            if (! $response->headers->has($nama)) {
                $response->headers->set($nama, $nilai);
            }
        }

        return $response;
    }
}
