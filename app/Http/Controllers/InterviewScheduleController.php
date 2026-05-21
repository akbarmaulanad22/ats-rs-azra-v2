<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Http\Requests\StoreInterviewScheduleRequest;
use App\Http\Requests\UpdateInterviewScheduleRequest;
use App\Logging\LogContext;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\User;
use App\Models\Vacancy;
use App\Notifications\WawancaraDijadwalkan;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class InterviewScheduleController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emailNotificationService) {}

    public function store(StoreInterviewScheduleRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('scheduleInterview', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy.unit', 'stages']);

        $wawancaraStages = ['wawancara_user', 'wawancara_manajer_hr', 'wawancara_direktur'];

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

        $updateData = [
            'jadwal' => $request->input('jadwal'),
            'lokasi' => $request->input('lokasi'),
        ];

        if ($stage->key === 'wawancara_user') {
            $interviewer = User::find($request->interviewer_id);

            if (! $interviewer || $interviewer->employee?->unit_id !== $application->vacancy->unit_id) {
                return back()->withErrors(['interviewer_id' => 'Pewawancara harus berasal dari unit yang sama dengan lowongan.']);
            }

            $updateData['interviewer_id'] = $interviewer->id;
        }

        $stage->update($updateData);
        $stage->refresh();

        Log::notice('Interview scheduled', array_merge(LogContext::make(), [
            'application_id' => $application->id,
            'stage_key' => $stage->key,
            'stage_id' => $stage->id,
            'jadwal' => $stage->jadwal?->toIso8601String(),
            'lokasi' => $stage->lokasi,
            'interviewer_id' => $stage->interviewer_id,
        ]));

        $this->notifyCandidateScheduled($application, $stage);

        if ($stage->key === 'wawancara_user') {
            Notification::send([$interviewer], new WawancaraDijadwalkan($application, $stage));
        } else {
            $interviewerRole = match ($stage->key) {
                'wawancara_manajer_hr' => Role::HrManager,
                'wawancara_direktur' => Role::Director,
                default => null,
            };

            if ($interviewerRole) {
                $interviewers = User::where('role', $interviewerRole)->where('is_active', true)->get();
                Notification::send($interviewers, new WawancaraDijadwalkan($application, $stage));
            }
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Wawancara berhasil dijadwalkan dan notifikasi terkirim.');
    }

    public function update(UpdateInterviewScheduleRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('scheduleInterview', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy.unit', 'stages']);

        $stage = $application->stages
            ->where('key', 'wawancara_user')
            ->where('status', ApplicationStageStatus::Aktif)
            ->first();

        if (! $stage || ! $stage->jadwal) {
            return back()->withErrors(['jadwal' => 'Belum ada jadwal wawancara yang dapat diubah.']);
        }

        $oldLokasi = $stage->lokasi;
        $oldInterviewerId = $stage->interviewer_id;

        $newJadwal = $request->input('jadwal');
        $newLokasi = $request->input('lokasi');
        $newInterviewerId = $request->integer('interviewer_id') ?: $oldInterviewerId;

        if ($newInterviewerId !== $oldInterviewerId) {
            $newInterviewer = User::find($newInterviewerId);

            if (! $newInterviewer || $newInterviewer->employee?->unit_id !== $application->vacancy->unit_id) {
                return back()->withErrors(['interviewer_id' => 'Pewawancara harus berasal dari unit yang sama dengan lowongan.']);
            }
        }

        $scheduleChanged = ! Carbon::parse($newJadwal)->eq($stage->jadwal) || $newLokasi !== $oldLokasi;
        $interviewerChanged = $newInterviewerId !== $oldInterviewerId;

        $stage->update([
            'jadwal' => $newJadwal,
            'lokasi' => $newLokasi,
            'interviewer_id' => $newInterviewerId,
        ]);

        $stage->refresh();

        Log::notice('Interview schedule updated', array_merge(LogContext::make(), [
            'application_id' => $application->id,
            'stage_id' => $stage->id,
            'schedule_changed' => $scheduleChanged,
            'interviewer_changed' => $interviewerChanged,
            'jadwal' => $stage->jadwal?->toIso8601String(),
            'lokasi' => $stage->lokasi,
            'interviewer_id' => $newInterviewerId,
        ]));

        if ($scheduleChanged) {
            $this->notifyCandidateScheduled($application, $stage);
        }

        if ($interviewerChanged || $scheduleChanged) {
            $notifyInterviewer = User::find($newInterviewerId);

            if ($notifyInterviewer) {
                Notification::send([$notifyInterviewer], new WawancaraDijadwalkan($application, $stage));
            }
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Jadwal wawancara berhasil diperbarui.');
    }

    private function notifyCandidateScheduled(Application $application, ApplicationStage $stage): void
    {
        try {
            $this->emailNotificationService->dispatch('wawancara_dijadwalkan', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'tanggal_interview' => $stage->jadwal->translatedFormat('l, d F Y H:i'),
                'lokasi_interview' => $stage->lokasi,
            ]);

            Log::info('Interview schedule email sent to candidate', array_merge(LogContext::make(), [
                'application_id' => $application->id,
                'candidate_id' => $application->candidate->id,
                'stage_key' => $stage->key,
            ]));
        } catch (\Throwable $e) {
            Log::error('Failed to send interview schedule email', array_merge(LogContext::make(), [
                'application_id' => $application->id,
                'candidate_id' => $application->candidate->id,
                'error' => $e->getMessage(),
            ]));

            report($e);
        }
    }
}
