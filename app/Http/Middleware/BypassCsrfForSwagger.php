<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassCsrfForSwagger
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/docs*') || 
            $request->header('Sec-Fetch-Mode') === 'cors' ||
            str_contains($request->header('User-Agent', ''), 'Swagger')) {
            
            return $next($request);
        }

        if ($request->is('api/*')) {
            return app(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->handle($request, $next);
        }

        return $next($request);
    }
}