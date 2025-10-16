<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use OpenApi\Generator;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('customer')->group(function () {
        Route::get('/', [CustomerController::class, 'profile']);
        Route::put('/', [CustomerController::class, 'updateProfile']);
        Route::get('/all', [CustomerController::class, 'index']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
    });

    Route::prefix('favorites')->group(function () {
        Route::post('/', [FavoriteController::class, 'addToFavorites']);
        Route::get('/', [FavoriteController::class, 'getCustomerFavorites']);
        Route::get('/check/{productId}', [FavoriteController::class, 'checkProductInFavorites']);
        Route::delete('/{favoriteId}', [FavoriteController::class, 'removeFromFavorites']);
        Route::delete('/product/{productId}', [FavoriteController::class, 'removeFromFavoritesByProduct']);
    });
});

Route::get('/api-docs.json', function () {
    $openapi = Generator::scan([app_path('Http/Controllers')]);
    return response()->json($openapi->jsonSerialize());
});

Route::get('/docs', function () {
    return view('swagger');
});