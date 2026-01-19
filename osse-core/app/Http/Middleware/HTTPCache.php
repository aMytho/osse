<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HTTPCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isSuccessful()) {
            $response->headers->set('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
            $response->headers->set('Expires', now()->addDay()->toDateTimeString());
            $response->headers->set('Last-Modified', now()->toDateTimeString());
        }

        return $response;
    }
}
