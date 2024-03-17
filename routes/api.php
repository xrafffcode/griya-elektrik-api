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

Route::get('web-configuration', [App\Http\Controllers\Api\WebConfigurationController::class, 'index']);

Route::get('banner/read/any', [App\Http\Controllers\Api\BannerController::class, 'index']);

Route::get('branch/read/any', [App\Http\Controllers\Api\BranchController::class, 'index']);
Route::get('branch/read/active', [App\Http\Controllers\Api\BranchController::class, 'getAllActiveBranch']);
Route::get('branch/read/main', [App\Http\Controllers\Api\BranchController::class, 'readMainBranch']);
Route::get('branch/{id}', [App\Http\Controllers\Api\BranchController::class, 'show']);

Route::get('product-category/read/any', [App\Http\Controllers\Api\ProductCategoryController::class, 'index']);
Route::get('product-category/read/root', [App\Http\Controllers\Api\ProductCategoryController::class, 'readRootCategories']);
Route::get('product-category/read/leaf', [App\Http\Controllers\Api\ProductCategoryController::class, 'readLeafCategories']);
Route::get('product-category/read/no-product', [App\Http\Controllers\Api\ProductCategoryController::class, 'readEmptyCategories']);
Route::get('product-category/{id}', [App\Http\Controllers\Api\ProductCategoryController::class, 'show']);

Route::get('product-brand/read/any', [App\Http\Controllers\Api\ProductBrandController::class, 'index']);
Route::get('product-brands/{id}', [App\Http\Controllers\Api\ProductBrandController::class, 'show']);

Route::get('product/read/any', [App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('product/read/active', [App\Http\Controllers\Api\ProductController::class, 'readAllActiveProducts']);
Route::get('product/read/active-featured', [App\Http\Controllers\Api\ProductController::class, 'readAllActiveAndFeaturedProducts']);
Route::get('product/{id}', [App\Http\Controllers\Api\ProductController::class, 'show']);
Route::get('product/slug/{slug}', [App\Http\Controllers\Api\ProductController::class, 'readProductBySlug']);

Route::get('client/read/any', [App\Http\Controllers\Api\ClientController::class, 'index']);
Route::get('client/{id}', [App\Http\Controllers\Api\ClientController::class, 'show']);

Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('web-configuration', [App\Http\Controllers\Api\WebConfigurationController::class, 'update']);

    Route::post('branch', [App\Http\Controllers\Api\BranchController::class, 'store']);
    Route::post('branch/{id}', [App\Http\Controllers\Api\BranchController::class, 'update']);
    Route::post('branch/{id}/main', [App\Http\Controllers\Api\BranchController::class, 'updateMainBranch']);
    Route::post('branch/{id}/active', [App\Http\Controllers\Api\BranchController::class, 'updateActiveBranch']);
    Route::delete('branch/{id}', [App\Http\Controllers\Api\BranchController::class, 'destroy']);
    Route::delete('branch-image/{id}', [App\Http\Controllers\Api\BranchImageController::class, 'destroy']);

    route::post('banner', [App\Http\Controllers\Api\BannerController::class, 'store']);
    route::post('banner/{id}', [App\Http\Controllers\Api\BannerController::class, 'update']);
    route::delete('banner/{id}', [App\Http\Controllers\Api\BannerController::class, 'destroy']);

    Route::post('product-category', [App\Http\Controllers\Api\ProductCategoryController::class, 'store']);
    Route::post('product-category/{id}', [App\Http\Controllers\Api\ProductCategoryController::class, 'update']);
    Route::delete('product-category/{id}', [App\Http\Controllers\Api\ProductCategoryController::class, 'destroy']);

    Route::post('product-brand', [App\Http\Controllers\Api\ProductBrandController::class, 'store']);
    Route::post('product-brand/{id}', [App\Http\Controllers\Api\ProductBrandController::class, 'update']);
    Route::delete('product-brand/{id}', [App\Http\Controllers\Api\ProductBrandController::class, 'destroy']);

    Route::post('product', [App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::post('product/{id}', [App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::post('product/{id}/active', [App\Http\Controllers\Api\ProductController::class, 'updateActiveProduct']);
    Route::post('product/{id}/featured', [App\Http\Controllers\Api\ProductController::class, 'updateFeaturedProduct']);
    Route::delete('product/{id}', [App\Http\Controllers\Api\ProductController::class, 'destroy']);
    route::delete('product/image/{id}', [App\Http\Controllers\Api\ProductController::class, 'deleteImage']);
    Route::post('client', [App\Http\Controllers\Api\ClientController::class, 'store']);
    Route::post('client/{id}', [App\Http\Controllers\Api\ClientController::class, 'update']);
    Route::delete('client/{id}', [App\Http\Controllers\Api\ClientController::class, 'destroy']);

    Route::get('me', [App\Http\Controllers\Api\AuthController::class, 'me']);
});
