<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationPipelineController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\CandidateExportController;
use App\Http\Controllers\CandidateStatusController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CvScreeningController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscTestController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\InterviewScheduleController;
use App\Http\Controllers\InterviewTemplateController;
use App\Http\Controllers\MbtiTestController;
use App\Http\Controllers\McuController;
use App\Http\Controllers\McuScheduleController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\OfferingLetterController;
use App\Http\Controllers\OfferingResponseController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\QuestionBankTemplateController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestReviewController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\VacancyInterviewTemplateController;
use App\Http\Controllers\VacancyPipelineController;
use App\Http\Controllers\VacancyTestController;
use App\Http\Controllers\WorkflowTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login')
);

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

Route::get('/penawaran/{offering}/terima', [OfferingResponseController::class, 'showAcceptForm'])->name('offering.accept')->middleware('signed');
Route::post('/penawaran/{offering}/terima', [OfferingResponseController::class, 'accept'])->name('offering.accept.submit')->middleware('signed');
Route::get('/penawaran/{offering}/tolak', [OfferingResponseController::class, 'showRejectForm'])->name('offering.reject')->middleware('signed');
Route::post('/penawaran/{offering}/tolak', [OfferingResponseController::class, 'reject'])->name('offering.reject.submit')->middleware('signed');

Route::middleware('auth')->group(function () {
    Route::get('/ubah-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ubah-password', [PasswordChangeController::class, 'update'])->name('password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('karyawan', EmployeeController::class)->parameters(['karyawan' => 'employee']);
    Route::resource('unit', UnitController::class)->except(['show']);

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
    Route::get('/lowongan/{lowongan}/pipeline', [VacancyPipelineController::class, 'index'])->name('lowongan.pipeline');
    Route::get('/lowongan/{lowongan}/pipeline/{application}', [VacancyPipelineController::class, 'showApplication'])->scopeBindings()->name('lowongan.pipeline.show');
    Route::get('/lowongan/{lowongan}/pipeline/{application}/export-pdf', [VacancyPipelineController::class, 'exportPdf'])->scopeBindings()->name('lowongan.pipeline.show.export-pdf');
    Route::post('/lowongan/{lowongan}/lamaran/{application}/lanjut', [ApplicationPipelineController::class, 'advance'])->scopeBindings()->name('lowongan.lamaran.lanjut');
    Route::post('/lowongan/{lowongan}/lamaran/{application}/gagal', [ApplicationPipelineController::class, 'fail'])->scopeBindings()->name('lowongan.lamaran.gagal');

    Route::post('/lowongan/{lowongan}/skrining/{application}/keputusan', [CvScreeningController::class, 'decide'])->scopeBindings()->name('lowongan.skrining.keputusan');

    Route::get('/pengaturan/template-email', [EmailTemplateController::class, 'index'])->name('template-email.index');
    Route::get('/pengaturan/template-email/{templateEmail}/edit', [EmailTemplateController::class, 'edit'])->name('template-email.edit');
    Route::put('/pengaturan/template-email/{templateEmail}', [EmailTemplateController::class, 'update'])->name('template-email.update');

    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');

    Route::resource('template-bank-soal', QuestionBankTemplateController::class)
        ->parameters(['template-bank-soal' => 'templateBankSoal'])
        ->except(['show']);

    Route::resource('template-wawancara', InterviewTemplateController::class)
        ->parameters(['template-wawancara' => 'templateWawancara'])
        ->except(['show']);

    Route::get('/lowongan/{lowongan}/tes', [VacancyTestController::class, 'show'])->name('lowongan.tes.show');
    Route::post('/lowongan/{lowongan}/tes', [VacancyTestController::class, 'save'])->name('lowongan.tes.save');

    Route::post('/lowongan/{lowongan}/tes/ulasan/jawaban/{answer}/skor', [TestReviewController::class, 'scoreEssay'])->name('lowongan.tes.ulasan.skor');
    Route::post('/lowongan/{lowongan}/tes/ulasan/{submission}/keputusan', [TestReviewController::class, 'decide'])->name('lowongan.tes.ulasan.keputusan');

    Route::get('/lowongan/{lowongan}/template-wawancara', [VacancyInterviewTemplateController::class, 'show'])->name('lowongan.template-wawancara.show');
    Route::post('/lowongan/{lowongan}/template-wawancara', [VacancyInterviewTemplateController::class, 'save'])->name('lowongan.template-wawancara.save');

    Route::post('/lowongan/{lowongan}/wawancara/{application}/keputusan', [InterviewController::class, 'decide'])->scopeBindings()->name('lowongan.wawancara.keputusan');
    Route::post('/lowongan/{lowongan}/wawancara/{application}/jadwal', [InterviewScheduleController::class, 'store'])->scopeBindings()->name('lowongan.wawancara.jadwal');
    Route::put('/lowongan/{lowongan}/wawancara/{application}/jadwal', [InterviewScheduleController::class, 'update'])->scopeBindings()->name('lowongan.wawancara.jadwal.update');

    Route::post('/lowongan/{lowongan}/surat-penawaran/{application}/kirim', [OfferingLetterController::class, 'send'])->scopeBindings()->name('lowongan.surat-penawaran.kirim');

    Route::post('/lowongan/{lowongan}/mcu/{application}/jadwal', [McuScheduleController::class, 'store'])->scopeBindings()->name('lowongan.mcu.jadwal');
    Route::post('/lowongan/{lowongan}/mcu/{application}/keputusan', [McuController::class, 'store'])->scopeBindings()->name('lowongan.mcu.keputusan');

    Route::post('/lowongan/{lowongan}/onboarding/{application}/undangan', [OnboardingController::class, 'sendInvitation'])->scopeBindings()->name('lowongan.onboarding.undangan');
    Route::post('/lowongan/{lowongan}/onboarding/{application}/selesai', [OnboardingController::class, 'complete'])->scopeBindings()->name('lowongan.onboarding.selesai');

    Route::get('/lowongan/{lowongan}/export', [CandidateExportController::class, 'list'])->name('lowongan.export.list');
    Route::get('/lowongan/{lowongan}/kandidat/{application}/pdf', [CandidateExportController::class, 'profile'])->scopeBindings()->name('lowongan.kandidat.pdf');
});
