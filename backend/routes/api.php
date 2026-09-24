<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Table\TableController;
use App\Http\Controllers\User\UserController;

// ─── Auth (walay token needed) ───────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Mga route nga need authentication ───────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Mga kategorya sa menu
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Mga produkto sa menu
    Route::get('/products/trashed', [ProductController::class, 'trashed']);
    Route::patch('/products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('/products/{id}/force', [ProductController::class, 'forceDelete']);
    Route::apiResource('products', ProductController::class);
    Route::patch('/products/{id}/availability', [ProductController::class, 'toggleAvailability']);
    Route::post('/products/{id}/image', [ProductController::class, 'uploadImage']);

    // Mga mesa
    Route::get('/tables', [TableController::class, 'index']);
    Route::post('/tables', [TableController::class, 'store']);
    Route::patch('/tables/{id}/status', [TableController::class, 'updateStatus']);

    // Mga order
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status/{status}', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Mga report (manager/admin lang)
    Route::middleware('role:manager,admin')->prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'dailySales']);
        Route::get('/monthly', [ReportController::class, 'monthlySales']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
    });

    // User management (admin lang)
    Route::middleware('role:admin')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::patch('/{id}/toggle-active', [UserController::class, 'toggleActive']);
    });

    // Password change — bisan kinsa nga naka-login
    Route::patch('/profile/password', [UserController::class, 'changePassword']);
});
