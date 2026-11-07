<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers for every web response.
 *
 * The Content Security Policy uses a per-request nonce: Vite and Livewire add
 * it to the tags they render, and the Flux directives receive it explicitly,
 * so no injected <script> can run. Alpine evaluates its x-* expressions with
 * the Function constructor, which needs 'unsafe-eval'; Blade escapes all
 * output, so user data can't smuggle in Alpine attributes.
 * It is skipped in local development, where the Vite dev server injects
 * scripts that a strict policy would block.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Livewire caches rendered @assets (with their nonce) in static state.
        // Reset it per request, or long-running workers (Octane, FrankenPHP
        // worker mode) would serve scripts carrying a previous request's nonce.
        Livewire::flushState();

        $nonce = Vite::useCspNonce();

        /** @var Response $response */
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), browsing-topics=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if (! app()->isLocal()) {
            $headers['Content-Security-Policy'] = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'",
                // Livewire and Flux position popovers with inline style attributes.
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
                "font-src 'self' https://fonts.bunny.net",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
            ]);
        }

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=63072000; includeSubDomains';
        }

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value, replace: false);
        }

        return $response;
    }
}
