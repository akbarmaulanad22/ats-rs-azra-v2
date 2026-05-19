<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOfferingLetterRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class OfferingLetterController extends Controller
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
        private readonly EmailNotificationService $emailNotificationService,
    ) {}

    public function send(SendOfferingLetterRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageOffering', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages', 'offeringLetter']);

        $offeringStage = $application->stages->firstWhere('key', 'surat_penawaran');
        abort_if(! $offeringStage, 404);

        if (! $offeringStage->status->isAdvanceable()) {
            return back()->withErrors(['offering' => 'Surat penawaran sudah pernah dikirim.']);
        }

        $tanggalMulaiFormatted = Carbon::parse($request->input('tanggal_mulai'))->translatedFormat('d F Y');

        $offering = $application->offeringLetter()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'jabatan_ditawarkan' => $request->input('jabatan_ditawarkan'),
                'gaji' => $request->input('gaji'),
                'tanggal_mulai' => $request->input('tanggal_mulai'),
                'catatan' => $request->input('catatan'),
            ]
        );

        try {
            $this->emailNotificationService->dispatch('surat_penawaran', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'jabatan_ditawarkan' => $offering->jabatan_ditawarkan,
                'gaji' => $offering->gaji,
                'tanggal_mulai' => $tanggalMulaiFormatted,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        $offering->update(['sent_at' => now()]);

        try {
            $this->pipelineService->advance($application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['offering' => $e->getMessage()]);
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Surat penawaran berhasil dikirim ke kandidat.');
    }
}
