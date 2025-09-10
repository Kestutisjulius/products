<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectApiBrowser
{
    
    public function handle(Request $request, Closure $next): Response
    {
         
        if ($request->is('api/*')) {
            // Jei NE API klientas (t.y. nori HTML) ir tai GET/HEAD – nukreipiam
            $wantsJson = $request->expectsJson()
                || str_contains($request->header('Accept', ''), 'application/json');

            if (!$wantsJson && in_array($request->method(), ['GET', 'HEAD'])) {
                return redirect()->route('api.info');
            }
        }

        return $next($request);
    }
}
