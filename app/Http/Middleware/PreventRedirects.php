<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventRedirects
{
    public function handle(Request $request, Closure $next)
    {
        // Força Accept: application/json
        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);
        
        // Se for um redirect, converte para JSON response
        if ($response->getStatusCode() === 302) {
            return response()->json([
                'error' => 'Redirect prevented',
                'message' => 'This should be an API response'
            ], 400);
        }
        
        return $response;
    }
}