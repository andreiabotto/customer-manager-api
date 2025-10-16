<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{

    protected $except = [
        'docs',
        'docs/*',
        'api/docs',
        'api/docs/*',
        'api/api-docs.json',
        'sanctum/csrf-cookie'
    ];
}