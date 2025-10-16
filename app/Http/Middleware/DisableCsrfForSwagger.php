<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfForSwagger
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('docs*') || $request->is('api/docs*')) {
            config(['session.driver' => 'array']);
        }
        
        return $next($request);
    }
}