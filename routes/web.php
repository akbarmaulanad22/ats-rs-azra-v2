<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationPipelineController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\VacancyPipelineController;
use App\Http\Controllers\WorkflowTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login')
);

Route::get('/karier', [CareerController::class, 'index'])->name('karier.index');
Route::get('/karier/{vacancy}', [CareerController::class, 'show'])->name('karier.show');
Route::get('/karier/{vacancy}/lamar', [ApplicationController::class, 'create'])->name('karier.lamar');
Route::post('/karier/{vacancy}/lamar', [ApplicationController::class, 'store'])->name('karier.lamar.store')->middleware('throttle:5,1');
Route::get('/karier/lamaran/{token}', [ApplicationController::class, 'confirmation'])->name('karier.lamaran.konfirmasi');

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

    Route::resource('lowongan', VacancyController::class)
        ->parameters(['lowongan' => 'lowongan'])
        ->except(['show']);
    Route::get('/lowongan/{lowongan}/pipeline', [VacancyPipelineController::class, 'show'])->name('lowongan.pipeline');
    Route::post('/lowongan/{lowongan}/lamaran/{application}/lanjut', [ApplicationPipelineController::class, 'advance'])->name('lowongan.lamaran.lanjut');
    Route::post('/lowongan/{lowongan}/lamaran/{application}/gagal', [ApplicationPipelineController::class, 'fail'])->name('lowongan.lamaran.gagal');

    Route::get('/pengaturan/template-email', [EmailTemplateController::class, 'index'])->name('template-email.index');
    Route::get('/pengaturan/template-email/{templateEmail}/edit', [EmailTemplateController::class, 'edit'])->name('template-email.edit');
    Route::put('/pengaturan/template-email/{templateEmail}', [EmailTemplateController::class, 'update'])->name('template-email.update');
});
