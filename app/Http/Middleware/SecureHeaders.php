<?php
namespace App\Http\Middleware;

use Closure;

class SecureHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Basic hardening headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()');

        // Example CSP — adjust for your app (disallow inline scripts/styles where possible)
        // $csp = "default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self';";
        $csp = "default-src 'self';font-src 'self' https://fonts.googleapis.com/css?family=Open+Sans:400,600&display=swap;img-src 'self' data: http:;connect-src 'self';object-src 'none';base-uri 'self';form-action 'self';frame-ancestors 'self';";

        // If you need CDNs, add them: script-src 'self' https://cdn.example.com
        // $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
