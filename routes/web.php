<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\ShopOwnerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ShopOwner\ShopProductController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\LegalPageController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('storefront.home');
Route::get('/privacy-policy', [LegalPageController::class, 'privacy'])->name('storefront.privacy');
Route::get('/terms-and-conditions', [LegalPageController::class, 'terms'])->name('storefront.terms');
Route::get('/shops/{shop}/{product}', StorefrontProductController::class)->name('storefront.products.show');
Route::get('/cart', [CartController::class, 'index'])->name('storefront.cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('storefront.cart.store');
Route::patch('/cart/{shopProduct}', [CartController::class, 'update'])->name('storefront.cart.update');
Route::delete('/cart/{shopProduct}', [CartController::class, 'destroy'])->name('storefront.cart.destroy');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin|shop-owner'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function (): void {
        Route::resource('shops', AdminShopController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('products', AdminProductController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/shop-owners', [ShopOwnerController::class, 'index'])->name('shop-owners.index');
        Route::post('/shop-owners', [ShopOwnerController::class, 'store'])->name('shop-owners.store');
    });

Route::prefix('shop')
    ->name('shop-owner.')
    ->middleware(['auth', 'role:shop-owner'])
    ->group(function (): void {
        Route::get('/products', [ShopProductController::class, 'index'])->name('products.index');
        Route::post('/products/catalog', [ShopProductController::class, 'storeFromCatalog'])->name('products.attach');
        Route::post('/products', [ShopProductController::class, 'storeNewProduct'])->name('products.store');
        Route::patch('/products/{shopProduct}', [ShopProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{shopProduct}', [ShopProductController::class, 'destroy'])->name('products.destroy');
    });
