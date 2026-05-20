<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Http\Requests\StoreInterviewScheduleRequest;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use App\Notifications\WawancaraDijadwalkan;
use App\Services\EmailNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class InterviewScheduleController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emailNotificationService) {}

    public function store(StoreInterviewScheduleRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('scheduleInterview', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages']);

        $wawancaraStages = ['wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur'];

        $stage = $application->stages
            ->whereIn('key', $wawancaraStages)
            ->where('status', ApplicationStageStatus::Aktif)
            ->first();

        if (! $stage) {
            return back()->withErrors(['jadwal' => 'Tidak ada tahap wawancara aktif untuk kandidat ini.']);
        }

        if ($stage->jadwal) {
            return back()->withErrors(['jadwal' => 'Wawancara sudah dijadwalkan sebelumnya.']);
        }

        $stage->update([
            'jadwal' => $request->input('jadwal'),
            'lokasi' => $request->input('lokasi'),
        ]);

        $stage->refresh();

        try {
            $this->emailNotificationService->dispatch('wawancara_dijadwalkan', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'tanggal_interview' => $stage->jadwal->translatedFormat('l, d F Y H:i'),
                'lokasi_interview' => $stage->lokasi,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        $interviewerRole = match ($stage->key) {
            'wawancara_kepala_unit' => Role::UnitHead,
            'wawancara_manajer_hr' => Role::HrManager,
            'wawancara_direktur' => Role::Director,
            default => null,
        };

        if ($interviewerRole) {
            $interviewers = User::where('role', $interviewerRole)->where('is_active', true)->get();

            Notification::send($interviewers, new WawancaraDijadwalkan($application, $stage));
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Wawancara berhasil dijadwalkan dan notifikasi terkirim.');
    }
}
