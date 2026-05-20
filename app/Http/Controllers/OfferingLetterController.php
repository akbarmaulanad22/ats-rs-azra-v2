<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOfferingLetterRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class OfferingLetterController extends Controller
{
    public function __construct(
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

        if ($application->offeringLetter?->sent_at) {
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
                'status' => 'pending',
            ]
        );

        $offering->update(['sent_at' => now()]);

        $expiry = now()->addDays(7);

        $acceptUrl = URL::temporarySignedRoute('offering.accept', $expiry, [
            'offering' => $offering->id,
        ]);

        $rejectUrl = URL::temporarySignedRoute('offering.reject', $expiry, [
            'offering' => $offering->id,
        ]);

        try {
            $this->emailNotificationService->dispatch('surat_penawaran', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'jabatan_ditawarkan' => $offering->jabatan_ditawarkan,
                'gaji' => $offering->gaji,
                'tanggal_mulai' => $tanggalMulaiFormatted,
                'link_terima' => $acceptUrl,
                'link_tolak' => $rejectUrl,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Surat penawaran berhasil dikirim ke kandidat.');
    }
}
