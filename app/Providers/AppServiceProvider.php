<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\CustomerServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\Contracts\FavoriteServiceInterface;
use App\Services\Auth\AuthService;
use App\Services\CustomerService;
use App\Services\ProductService;
use App\Services\FavoriteService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {        
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(FavoriteServiceInterface::class, FavoriteService::class);
    }

    public function boot(): void
    {
    }
}