<?php

namespace App\Actions;

use App\Enums\ApplicationStageStatus;
use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Unit;
use App\Models\Vacancy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetRecruitmentMetrics
{
    /**
     * @param  array{date_from: ?string, date_to: ?string, unit_id: ?int, vacancy_id: ?int}  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters): array
    {
        $applicationQuery = Application::query()
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('vacancy_id', $id))
            ->when(
                $filters['unit_id'] ?? null,
                fn ($q, $id) => $q->whereHas('vacancy', fn ($vq) => $vq->where('unit_id', $id))
            );

        $applicationIds = $applicationQuery->select('id');

        return [
            'totalApplications' => $applicationQuery->count(),
            'inProcess' => $this->inProcess($applicationIds),
            'accepted' => $this->accepted($applicationIds),
            'openVacancies' => $this->openVacancies($filters),
            ...$this->funnel($applicationIds),
            'timeToHire' => $this->timeToHire($applicationIds),
            'stageRates' => $this->stageRates($applicationIds),
            ...$this->bottlenecks($applicationIds),
            'vacancySummary' => $this->vacancySummary($filters),
            'units' => Unit::select('id', 'nama')->orderBy('nama')->get(),
            'vacancies' => Vacancy::query()
                ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
                ->select('id', 'judul_posisi')
                ->orderBy('judul_posisi')
                ->get(),
        ];
    }

    private function inProcess($applicationIds): int
    {
        return ApplicationStage::whereIn('application_id', $applicationIds)
            ->whereIn('status', [ApplicationStageStatus::Aktif, ApplicationStageStatus::Reserved])
            ->distinct('application_id')
            ->count('application_id');
    }

    private function accepted($applicationIds): int
    {
        return ApplicationStage::whereIn('application_id', $applicationIds)
            ->where('key', 'onboarding')
            ->where('status', ApplicationStageStatus::Selesai)
            ->count();
    }

    private function openVacancies(array $filters): int
    {
        return Vacancy::query()
            ->where('status', VacancyStatus::Published)
            ->where('tenggat_lamaran', '>=', now()->toDateString())
            ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->count();
    }

    /**
     * @return array{funnel: Collection, funnelMax: int}
     */
    private function funnel($applicationIds): array
    {
        $funnel = ApplicationStage::query()
            ->whereIn('application_id', $applicationIds)
            ->where('status', '!=', ApplicationStageStatus::Pending)
            ->select('key', 'nama', DB::raw('COUNT(*) as total'))
            ->groupBy('key', 'nama')
            ->orderByRaw('MIN(position)')
            ->get();

        return [
            'funnel' => $funnel,
            'funnelMax' => $funnel->max('total') ?: 1,
        ];
    }

    private function timeToHire($applicationIds): ?float
    {
        $value = DB::table('application_stages as s')
            ->join('applications as a', 'a.id', '=', 's.application_id')
            ->whereIn('s.application_id', $applicationIds)
            ->where('s.key', 'onboarding')
            ->where('s.status', ApplicationStageStatus::Selesai->value)
            ->selectRaw('AVG('.$this->daysDiff('s.updated_at', 'a.created_at').') as avg_days')
            ->value('avg_days');

        return $value !== null ? (float) $value : null;
    }

    private function stageRates($applicationIds)
    {
        return ApplicationStage::query()
            ->whereIn('application_id', $applicationIds)
            ->where('status', '!=', ApplicationStageStatus::Pending)
            ->select([
                'key',
                'nama',
                DB::raw("SUM(CASE WHEN status = '".ApplicationStageStatus::Selesai->value."' THEN 1 ELSE 0 END) as passed"),
                DB::raw("SUM(CASE WHEN status = '".ApplicationStageStatus::Gagal->value."' THEN 1 ELSE 0 END) as failed"),
                DB::raw("SUM(CASE WHEN status = '".ApplicationStageStatus::Reserved->value."' THEN 1 ELSE 0 END) as reserved_count"),
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('key', 'nama')
            ->orderByRaw('MIN(position)')
            ->get()
            ->filter(fn ($row) => $row->total > 0);
    }

    /**
     * @return array{bottlenecks: Collection, bottleneckMax: float}
     */
    private function bottlenecks($applicationIds): array
    {
        $bottlenecks = DB::table('application_stages as s')
            ->leftJoin('application_stages as prev', function ($join): void {
                $join->on('prev.application_id', '=', 's.application_id')
                    ->whereRaw('prev.position = s.position - 1');
            })
            ->whereIn('s.application_id', $applicationIds)
            ->whereIn('s.status', [ApplicationStageStatus::Selesai->value, ApplicationStageStatus::Gagal->value])
            ->select([
                's.key',
                's.nama',
                DB::raw('ROUND(AVG('.$this->daysDiff('s.updated_at', 'COALESCE(prev.updated_at, s.created_at)').'), 1) as avg_days'),
            ])
            ->groupBy('s.key', 's.nama')
            ->orderByRaw('MIN(s.position)')
            ->get();

        return [
            'bottlenecks' => $bottlenecks,
            'bottleneckMax' => $bottlenecks->max('avg_days') ?: 1,
        ];
    }

    private function vacancySummary(array $filters)
    {
        return Vacancy::query()
            ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->withCount([
                'applications as total_pelamar' => function ($q) use ($filters): void {
                    $q->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                },
                'applications as posisi_terisi' => function ($q) use ($filters): void {
                    $q->whereHas('stages', fn ($sq) => $sq->where('key', 'onboarding')->where('status', ApplicationStageStatus::Selesai))
                        ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                },
            ])
            ->with('unit')
            ->latest()
            ->get();
    }

    private function daysDiff(string $col1, string $col2): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? "EXTRACT(EPOCH FROM ($col1 - $col2)) / 86400"
            : "(julianday($col1) - julianday($col2))";
    }
}
