<?php

namespace App\Services;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationPipelineService
{
    public function __construct(private readonly EmailNotificationService $emailNotificationService) {}

    /**
     * Advance the application to the next pipeline stage.
     *
     * @throws \RuntimeException when no active stage exists or already at last stage
     */
    public function advance(Application $application): void
    {
        $application->load(['stages', 'candidate', 'vacancy']);

        $currentStage = $application->stages->first(
            fn ($s) => $s->status->isAdvanceable()
        );

        if (! $currentStage) {
            throw new \RuntimeException('Tidak ada tahap aktif yang dapat dilanjutkan.');
        }

        $nextStage = $application->stages->first(
            fn ($s) => $s->position > $currentStage->position && $s->status === ApplicationStageStatus::Pending
        );

        DB::transaction(function () use ($currentStage, $nextStage): void {
            $currentStage->update(['status' => ApplicationStageStatus::Selesai]);

            if ($nextStage) {
                $nextStage->update(['status' => ApplicationStageStatus::Aktif]);
            }
        });

        try {
            $this->emailNotificationService->dispatch('transisi_tahap', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'link_status' => route('karier.lamaran.konfirmasi', $application->token),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Fail/reject the application at the current stage.
     *
     * @throws \RuntimeException when no active stage exists
     */
    public function fail(Application $application): void
    {
        $application->load(['stages', 'candidate', 'vacancy']);

        $currentStage = $application->stages->first(
            fn ($s) => $s->status->isAdvanceable()
        );

        if (! $currentStage) {
            throw new \RuntimeException('Tidak ada tahap aktif yang dapat digagalkan.');
        }

        $currentStage->update(['status' => ApplicationStageStatus::Gagal]);

        try {
            $this->emailNotificationService->dispatch('kandidat_ditolak', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
