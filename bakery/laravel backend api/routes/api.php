<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Products\CategoryController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Products\VarietyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')
    ->as('auth.')
    ->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::post('/register', [AuthController::class, 'userCreate']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/logout', [AuthController::class, 'logout']);
            Route::get('/auth_user', [AuthController::class, 'authUser']);
            Route::Put('/update_auth', [AuthController::class, 'updateUser']);
        });
});



Route::get('all_products_list', [ProductController::class, 'index']);
Route::get('all_varieties_list', [VarietyController::class, 'index']);
Route::get('all_categories_list', [CategoryController::class, 'index']);
Route::get('products_by_id/{id}', [ProductController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('create_categories', [CategoryController::class, 'store']);
    Route::get('categories_by_id/{id}', [CategoryController::class, 'show']);
    Route::put('update_categories/{id}', [CategoryController::class, 'update']);
    Route::delete('delete_categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('create_varieties', [VarietyController::class, 'store']);
    Route::get('varieties_by_id/{id}', [VarietyController::class, 'show']);
    Route::put('update_varieties/{id}', [VarietyController::class, 'update']);
    Route::delete('delete_varieties/{id}', [VarietyController::class, 'destroy']);

    Route::post('create_products', [ProductController::class, 'store']);
 
    Route::put('update_products/{id}', [ProductController::class, 'update']);
    Route::delete('delete_products/{id}', [ProductController::class, 'destroy']);

    Route::get('/dashboard_counts', [DashboardController::class, 'getCounts']);
});
