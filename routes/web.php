<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\ShopPageController;
use App\Http\Controllers\GuestProductController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DeliveryLocationController;
use App\Http\Controllers\DeliveryAreaController;

Route::inertia('/welcome', 'Welcome')->name('welcome-page');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
    
require __DIR__.'/settings.php';
    
Route::get('/', [HomePageController::class, 'index'])->name('home');
Route::get('/shop', [ShopPageController::class, 'index'])->name('shop-page.index');
Route::get('/product-details/{product:slug}', [GuestProductController::class, 'index'])->name('product-details.index');

Route::middleware(['auth', 'verified', 'role:super_admin,admin'])->group(function () {
    Route::prefix('users')
        ->name('users.')
        ->controller(UserController::class)
        ->group(function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    Route::prefix('brands')
        ->name('brands.')
        ->controller(BrandController::class)
        ->group( function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{brand:uuid}/edit', 'edit')->name('edit');
        Route::put('/{brand:uuid}', 'update')->name('update');
        Route::delete('/{brand:uuid}', 'destroy')->name('destroy');
    });

    Route::prefix('product-categories')
        ->name('product-categories.')
        ->controller(ProductCategoryController::class)
        ->group( function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{product_category:uuid}/edit', 'edit')->name('edit');
        Route::put('/{product_category:uuid}', 'update')->name('update');
        Route::delete('/{product_category:uuid}', 'destroy')->name('destroy');
    });

    Route::prefix('products')
        ->name('products.')
        ->controller(ProductController::class)
        ->group( function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{product:uuid}/edit', 'edit')->name('edit');
        Route::put('/{product:uuid}', 'update')->name('update');
        Route::post('/{product:uuid}/toggle-attribute', 'toggleAttribute')->name('toggle-attribute');
        Route::delete('/{product:uuid}', 'destroy')->name('destroy');
    });

    Route::prefix('delivery-locations')
        ->name('delivery-locations.')
        ->controller(DeliveryLocationController::class)
        ->group(function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{delivery_location:uuid}/show', 'show')->name('show');
        Route::get('/{delivery_location:uuid}/edit', 'edit')->name('edit');
        Route::put('/{delivery_location:uuid}', 'update')->name('update');
        Route::delete('/{delivery_location:uuid}', 'destroy')->name('destroy');
    });

    Route::prefix('delivery-areas')
        ->name('delivery-areas.')
        ->controller(DeliveryAreaController::class)
        ->group(function()
    {
        Route::get('/{delivery_location:uuid}/create', 'create')->name('create');
        Route::post('/{delivery_location:uuid}/', 'store')->name('store');
        Route::get('/{delivery_location:uuid}/{delivery_area:uuid}/edit', 'edit')->name('edit');
        Route::put('/{delivery_location:uuid}/{delivery_area:uuid}', 'update')->name('update');
        Route::delete('/{delivery_area:uuid}', 'destroy')->name('destroy');
    });
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::prefix('users')
        ->name('users.')
        ->controller(UserController::class)
        ->group(function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });
});