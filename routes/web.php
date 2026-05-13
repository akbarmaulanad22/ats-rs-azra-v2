<?php

use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login')
);

Route::middleware('auth')->group(function () {
    Route::get('/ubah-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ubah-password', [PasswordChangeController::class, 'update'])->name('password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
