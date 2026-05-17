<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationPipelineController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\CandidateMcuController;
use App\Http\Controllers\CandidateStatusController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CvScreeningController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscTestController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\InterviewCriteriaController;
use App\Http\Controllers\MbtiTestController;
use App\Http\Controllers\McuController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\OfferingLetterController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestReviewController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\VacancyInterviewCriteriaController;
use App\Http\Controllers\VacancyPipelineController;
use App\Http\Controllers\VacancyTestController;
use App\Http\Controllers\WorkflowTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login')
);

Route::get('/lamaran/{token}/mcu', [CandidateMcuController::class, 'show'])->name('kandidat.mcu.upload');
Route::post('/lamaran/{token}/mcu', [CandidateMcuController::class, 'upload'])->name('kandidat.mcu.upload.store')->middleware('throttle:10,1');

Route::get('/tes/{token}', [TestController::class, 'show'])->name('tes.show');
Route::post('/tes/{token}', [TestController::class, 'submit'])->name('tes.submit')->middleware('throttle:5,1');

Route::get('/tes-disc/{token}', [DiscTestController::class, 'show'])->name('tes-disc.show');
Route::post('/tes-disc/{token}', [DiscTestController::class, 'submit'])->name('tes-disc.submit')->middleware('throttle:5,1');

Route::get('/tes-mbti/{token}', [MbtiTestController::class, 'show'])->name('tes-mbti.show');
Route::post('/tes-mbti/{token}', [MbtiTestController::class, 'submit'])->name('tes-mbti.submit')->middleware('throttle:5,1');

Route::get('/karier', [CareerController::class, 'index'])->name('karier.index');
Route::get('/karier/{vacancy}', [CareerController::class, 'show'])->name('karier.show');
Route::get('/karier/{vacancy}/lamar', [ApplicationController::class, 'create'])->name('karier.lamar');
Route::post('/karier/{vacancy}/lamar', [ApplicationController::class, 'store'])->name('karier.lamar.store')->middleware('throttle:5,1');
Route::get('/karier/lamaran/{token}', [ApplicationController::class, 'confirmation'])->name('karier.lamaran.konfirmasi');
Route::get('/lamaran/{token}', [CandidateStatusController::class, 'show'])->name('karier.lamaran.status');

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
    Route::post('/lowongan/{lowongan}/lamaran/{application}/lanjut', [ApplicationPipelineController::class, 'advance'])->scopeBindings()->name('lowongan.lamaran.lanjut');
    Route::post('/lowongan/{lowongan}/lamaran/{application}/gagal', [ApplicationPipelineController::class, 'fail'])->scopeBindings()->name('lowongan.lamaran.gagal');

    Route::get('/lowongan/{lowongan}/skrining', [CvScreeningController::class, 'index'])->name('lowongan.skrining.index');
    Route::get('/lowongan/{lowongan}/skrining/{application}', [CvScreeningController::class, 'show'])->scopeBindings()->name('lowongan.skrining.show');
    Route::post('/lowongan/{lowongan}/skrining/{application}/keputusan', [CvScreeningController::class, 'decide'])->scopeBindings()->name('lowongan.skrining.keputusan');

    Route::get('/pengaturan/template-email', [EmailTemplateController::class, 'index'])->name('template-email.index');
    Route::get('/pengaturan/template-email/{templateEmail}/edit', [EmailTemplateController::class, 'edit'])->name('template-email.edit');
    Route::put('/pengaturan/template-email/{templateEmail}', [EmailTemplateController::class, 'update'])->name('template-email.update');

    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');

    Route::resource('bank-soal', QuestionController::class)
        ->parameters(['bank-soal' => 'bankSoal'])
        ->except(['show']);

    Route::get('/lowongan/{lowongan}/tes', [VacancyTestController::class, 'show'])->name('lowongan.tes.show');
    Route::post('/lowongan/{lowongan}/tes', [VacancyTestController::class, 'save'])->name('lowongan.tes.save');

    Route::get('/lowongan/{lowongan}/tes/ulasan', [TestReviewController::class, 'index'])->name('lowongan.tes.ulasan.index');
    Route::get('/lowongan/{lowongan}/tes/ulasan/{submission}', [TestReviewController::class, 'show'])->scopeBindings()->name('lowongan.tes.ulasan.show');
    Route::post('/lowongan/{lowongan}/tes/ulasan/jawaban/{answer}/skor', [TestReviewController::class, 'scoreEssay'])->name('lowongan.tes.ulasan.skor');

    Route::get('/pengaturan/kriteria-wawancara', [InterviewCriteriaController::class, 'index'])->name('kriteria-wawancara.index');
    Route::post('/pengaturan/kriteria-wawancara', [InterviewCriteriaController::class, 'store'])->name('kriteria-wawancara.store');
    Route::put('/pengaturan/kriteria-wawancara/{kriteria_wawancara}', [InterviewCriteriaController::class, 'update'])->name('kriteria-wawancara.update');
    Route::delete('/pengaturan/kriteria-wawancara/{kriteria_wawancara}', [InterviewCriteriaController::class, 'destroy'])->name('kriteria-wawancara.destroy');

    Route::get('/lowongan/{lowongan}/kriteria-wawancara', [VacancyInterviewCriteriaController::class, 'show'])->name('lowongan.kriteria-wawancara.show');
    Route::post('/lowongan/{lowongan}/kriteria-wawancara', [VacancyInterviewCriteriaController::class, 'save'])->name('lowongan.kriteria-wawancara.save');

    Route::get('/lowongan/{lowongan}/wawancara', [InterviewController::class, 'index'])->name('lowongan.wawancara.index');
    Route::get('/lowongan/{lowongan}/wawancara/{application}', [InterviewController::class, 'show'])->scopeBindings()->name('lowongan.wawancara.show');
    Route::post('/lowongan/{lowongan}/wawancara/{application}/keputusan', [InterviewController::class, 'decide'])->scopeBindings()->name('lowongan.wawancara.keputusan');

    Route::get('/lowongan/{lowongan}/surat-penawaran/{application}', [OfferingLetterController::class, 'show'])->scopeBindings()->name('lowongan.surat-penawaran.show');
    Route::post('/lowongan/{lowongan}/surat-penawaran/{application}/kirim', [OfferingLetterController::class, 'send'])->scopeBindings()->name('lowongan.surat-penawaran.kirim');

    Route::get('/lowongan/{lowongan}/mcu/{application}', [McuController::class, 'show'])->scopeBindings()->name('lowongan.mcu.show');
    Route::post('/lowongan/{lowongan}/mcu/{application}/status', [McuController::class, 'updateStatus'])->scopeBindings()->name('lowongan.mcu.status');
    Route::post('/lowongan/{lowongan}/mcu/{application}/dokumen', [McuController::class, 'uploadDocument'])->scopeBindings()->name('lowongan.mcu.dokumen');

    Route::get('/lowongan/{lowongan}/onboarding/{application}', [OnboardingController::class, 'show'])->scopeBindings()->name('lowongan.onboarding.show');
    Route::post('/lowongan/{lowongan}/onboarding/{application}/undangan', [OnboardingController::class, 'sendInvitation'])->scopeBindings()->name('lowongan.onboarding.undangan');
    Route::post('/lowongan/{lowongan}/onboarding/{application}/selesai', [OnboardingController::class, 'complete'])->scopeBindings()->name('lowongan.onboarding.selesai');
});
