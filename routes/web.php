<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomePageController;

Route::inertia('/welcome', 'Welcome')->name('welcome-page');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    });
    
require __DIR__.'/settings.php';
    
Route::get('/', [HomePageController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified', 'role:super_admin'])->group(function () {
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