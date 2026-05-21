<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Http\Requests\StoreInterviewScheduleRequest;
use App\Http\Requests\UpdateInterviewScheduleRequest;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use App\Notifications\WawancaraDijadwalkan;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
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

        if ($stage->key === 'wawancara_user') {
            $interviewer = User::find($request->interviewer_id);

            if (! $interviewer || $interviewer->employee?->unit !== $application->vacancy->unit->nama) {
                return back()->withErrors(['interviewer_id' => 'Pewawancara harus berasal dari unit yang sama dengan lowongan.']);
            }

            $stage->update([
                'jadwal' => $request->input('jadwal'),
                'lokasi' => $request->input('lokasi'),
                'interviewer_id' => $interviewer->id,
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

            Notification::send([$interviewer], new WawancaraDijadwalkan($application, $stage));
        } else {
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
        $newInterviewerId = $request->input('interviewer_id') ? (int) $request->input('interviewer_id') : $oldInterviewerId;

        if ($newInterviewerId !== $oldInterviewerId) {
            $newInterviewer = User::find($newInterviewerId);

            if (! $newInterviewer || $newInterviewer->employee?->unit !== $application->vacancy->unit->nama) {
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

        if ($scheduleChanged) {
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
}
