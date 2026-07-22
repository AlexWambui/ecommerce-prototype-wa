<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomePageController;

Route::inertia('/welcome', 'Welcome')->name('welcome-page');

Route::get('/', [HomePageController::class, 'index'])->name('home');

Route::get('/users', [UserController::class, 'index'])->name('users.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
