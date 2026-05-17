<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStageStatus;
use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Console\Command;

class AutoRejectReservedKandidat extends Command
{
    protected $signature = 'pipeline:auto-reject-reserved';

    protected $description = 'Tolak otomatis semua kandidat berstatus Ditangguhkan pada lowongan yang telah melewati tenggat lamaran';

    public function __construct(private readonly ApplicationPipelineService $pipelineService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $lowonganList = Vacancy::query()
            ->whereIn('status', [VacancyStatus::Published, VacancyStatus::Closed])
            ->where('tenggat_lamaran', '<', now()->toDateString())
            ->whereHas('applications', fn ($q) => $q->whereHas('stages', fn ($sq) => $sq->where('status', ApplicationStageStatus::Reserved)))
            ->with(['applications' => fn ($q) => $q->whereHas('stages', fn ($sq) => $sq->where('status', ApplicationStageStatus::Reserved))])
            ->get();

        if ($lowonganList->isEmpty()) {
            $this->info('Tidak ada kandidat yang perlu ditolak otomatis.');

            return Command::SUCCESS;
        }

        $totalTolak = 0;
        $totalLowongan = 0;

        foreach ($lowonganList as $lowongan) {
            $ditolak = 0;

            foreach ($lowongan->applications as $lamaran) {
                try {
                    $this->pipelineService->fail($lamaran);
                    $ditolak++;
                } catch (\RuntimeException $e) {
                    report($e);
                }
            }

            if ($ditolak > 0) {
                $totalLowongan++;
                $totalTolak += $ditolak;
            }
        }

        $this->info("Auto-reject: {$totalTolak} kandidat dari {$totalLowongan} lowongan.");

        return Command::SUCCESS;
    }
}
