<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('web_configuration', [App\Http\Controllers\Api\WebConfigurationController::class, 'index']);

route::get('banners', [App\Http\Controllers\Api\BannerController::class, 'index']);

Route::get('product-categories', [App\Http\Controllers\Api\ProductCategoryController::class, 'index']);
Route::get('product-categories/{id}', [App\Http\Controllers\Api\ProductCategoryController::class, 'show']);

Route::get('product-brands', [App\Http\Controllers\Api\ProductBrandController::class, 'index']);
Route::get('product-brands/{id}', [App\Http\Controllers\Api\ProductBrandController::class, 'show']);

Route::get('products', [App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('products/{id}', [App\Http\Controllers\Api\ProductController::class, 'show']);

Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('web_configuration', [App\Http\Controllers\Api\WebConfigurationController::class, 'update']);

    route::post('banners', [App\Http\Controllers\Api\BannerController::class, 'store']);
    route::post('banners/{id}', [App\Http\Controllers\Api\BannerController::class, 'update']);
    route::delete('banners/{id}', [App\Http\Controllers\Api\BannerController::class, 'destroy']);

    Route::post('product-categories', [App\Http\Controllers\Api\ProductCategoryController::class, 'store']);
    Route::post('product-categories/{id}', [App\Http\Controllers\Api\ProductCategoryController::class, 'update']);
    Route::delete('product-categories/{id}', [App\Http\Controllers\Api\ProductCategoryController::class, 'destroy']);

    Route::post('product-brands', [App\Http\Controllers\Api\ProductBrandController::class, 'store']);
    Route::post('product-brands/{id}', [App\Http\Controllers\Api\ProductBrandController::class, 'update']);
    Route::delete('product-brands/{id}', [App\Http\Controllers\Api\ProductBrandController::class, 'destroy']);

    Route::post('products', [App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::post('products/{id}', [App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('products/{id}', [App\Http\Controllers\Api\ProductController::class, 'destroy']);
    route::delete('products/{id}/image', [App\Http\Controllers\Api\ProductController::class, 'deleteImage']);
});
