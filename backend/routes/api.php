<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Categories & Collections (Write)
        Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class)->except(['index', 'show']);
        Route::apiResource('collections', \App\Http\Controllers\Api\CollectionController::class)->except(['index', 'show']);
        
        // Products (Write)
        Route::get('/products/export', [\App\Http\Controllers\Api\ProductController::class, 'export']);
        Route::post('/products/import', [\App\Http\Controllers\Api\ProductController::class, 'import']);
        Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class)->except(['index', 'show']);
        Route::post('/upload', [\App\Http\Controllers\Api\UploadController::class, 'store']);

        // Customer
        Route::get('/customer/addresses', [\App\Http\Controllers\Api\CustomerController::class, 'addresses']);
        Route::post('/customer/addresses', [\App\Http\Controllers\Api\CustomerController::class, 'storeAddress']);

        // Orders
        Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class)->except(['update', 'destroy']);
    });

    // Public Read-Only Routes
    Route::get('products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::get('categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('categories/{category}', [\App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::get('collections', [\App\Http\Controllers\Api\CollectionController::class, 'index']);
    Route::get('collections/{collection}', [\App\Http\Controllers\Api\CollectionController::class, 'show']);
});
