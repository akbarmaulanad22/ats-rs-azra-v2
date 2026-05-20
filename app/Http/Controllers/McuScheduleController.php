<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMcuScheduleRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\EmailNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class McuScheduleController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emailNotificationService) {}

    public function store(StoreMcuScheduleRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageMcu', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages']);

        $mcuStage = $application->stages->firstWhere('key', 'mcu');

        if (! $mcuStage || ! $mcuStage->status->isAdvanceable()) {
            return back()->withErrors(['jadwal' => 'Tidak ada tahap MCU aktif untuk kandidat ini.']);
        }

        if ($mcuStage->jadwal) {
            return back()->withErrors(['jadwal' => 'MCU sudah dijadwalkan sebelumnya.']);
        }

        $mcuStage->update([
            'jadwal' => $request->input('jadwal'),
            'lokasi' => $request->input('lokasi'),
        ]);

        $mcuStage->refresh();

        try {
            $this->emailNotificationService->dispatch('instruksi_mcu', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'jadwal_mcu' => $mcuStage->jadwal->translatedFormat('l, d F Y H:i'),
                'lokasi_mcu' => $mcuStage->lokasi,
                'link_status' => route('karier.lamaran.status', $application->token),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'MCU berhasil dijadwalkan dan notifikasi terkirim.');
    }
}
