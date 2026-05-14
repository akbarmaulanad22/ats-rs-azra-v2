<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkflowTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login')
);

Route::middleware('auth')->group(function () {
    Route::get('/ubah-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ubah-password', [PasswordChangeController::class, 'update'])->name('password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('karyawan', EmployeeController::class)->parameters(['karyawan' => 'employee']);

    Route::get('/akun/karyawan-tersedia', [AccountController::class, 'availableEmployees'])->name('akun.karyawan-tersedia');
    Route::resource('akun', AccountController::class)
        ->parameters(['akun' => 'user'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('/akun/{user}/toggle-aktif', [AccountController::class, 'toggleAktif'])->name('akun.toggle-aktif');

    Route::resource('template-alur', WorkflowTemplateController::class)
        ->parameters(['template-alur' => 'templateAlur'])
        ->except(['show']);
});
