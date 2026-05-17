<?php

namespace App\Http\Controllers;

use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Unit;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private function daysDiff(string $col1, string $col2): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? "EXTRACT(EPOCH FROM ($col1 - $col2)) / 86400"
            : "(julianday($col1) - julianday($col2))";
    }

    public function index(Request $request): View
    {
        if (! auth()->user()->isHrAdmin()) {
            return view('dashboard');
        }

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'unit_id' => 'nullable|integer|exists:units,id',
            'vacancy_id' => 'nullable|integer|exists:vacancies,id',
        ]);

        $applicationIds = Application::query()
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('vacancy_id', $id))
            ->when(
                $filters['unit_id'] ?? null,
                fn ($q, $id) => $q->whereHas('vacancy', fn ($vq) => $vq->where('unit_id', $id))
            )
            ->pluck('id');

        $totalApplications = $applicationIds->count();

        $inProcess = ApplicationStage::whereIn('application_id', $applicationIds)
            ->whereIn('status', ['aktif', 'reserved'])
            ->distinct('application_id')
            ->count('application_id');

        $accepted = ApplicationStage::whereIn('application_id', $applicationIds)
            ->where('key', 'onboarding')
            ->where('status', 'selesai')
            ->count();

        $openVacancies = Vacancy::query()
            ->where('status', VacancyStatus::Published)
            ->where('tenggat_lamaran', '>=', now()->toDateString())
            ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->count();

        // Cumulative funnel: how many candidates reached each stage (status != pending)
        $funnel = ApplicationStage::query()
            ->whereIn('application_id', $applicationIds)
            ->where('status', '!=', 'pending')
            ->select('key', 'nama', DB::raw('COUNT(*) as total'))
            ->groupBy('key', 'nama')
            ->orderByRaw('MIN(position)')
            ->get();

        $funnelMax = $funnel->max('total') ?: 1;

        // Average days from application to onboarding completion
        $timeToHire = DB::table('application_stages as s')
            ->join('applications as a', 'a.id', '=', 's.application_id')
            ->whereIn('s.application_id', $applicationIds)
            ->where('s.key', 'onboarding')
            ->where('s.status', 'selesai')
            ->selectRaw('AVG('.$this->daysDiff('s.updated_at', 'a.created_at').') as avg_days')
            ->value('avg_days');

        // Pass / fail / reserved rates per stage
        $stageRates = ApplicationStage::query()
            ->whereIn('application_id', $applicationIds)
            ->where('status', '!=', 'pending')
            ->select([
                'key',
                'nama',
                DB::raw("SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as passed"),
                DB::raw("SUM(CASE WHEN status = 'gagal'   THEN 1 ELSE 0 END) as failed"),
                DB::raw("SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved_count"),
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('key', 'nama')
            ->orderByRaw('MIN(position)')
            ->get()
            ->filter(fn ($row) => $row->total > 0);

        // Stage bottleneck: avg days between consecutive stage completions
        // prev.updated_at = when prior stage completed = when this stage became active
        $bottlenecks = DB::table('application_stages as s')
            ->leftJoin('application_stages as prev', function ($join): void {
                $join->on('prev.application_id', '=', 's.application_id')
                    ->whereRaw('prev.position = s.position - 1');
            })
            ->whereIn('s.application_id', $applicationIds)
            ->whereIn('s.status', ['selesai', 'gagal'])
            ->select([
                's.key',
                's.nama',
                DB::raw('ROUND(AVG('.$this->daysDiff('s.updated_at', 'COALESCE(prev.updated_at, s.created_at)').'), 1) as avg_days'),
            ])
            ->groupBy('s.key', 's.nama')
            ->orderByRaw('MIN(s.position)')
            ->get();

        $bottleneckMax = $bottlenecks->max('avg_days') ?: 1;

        // Vacancy summary
        $vacancySummary = Vacancy::query()
            ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->withCount([
                'applications as total_pelamar' => function ($q) use ($filters): void {
                    $q->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                },
                'applications as posisi_terisi' => function ($q) use ($filters): void {
                    $q->whereHas('stages', fn ($sq) => $sq->where('key', 'onboarding')->where('status', 'selesai'))
                        ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                },
            ])
            ->with('unit')
            ->latest()
            ->get();

        $units = Unit::orderBy('nama')->get();
        $vacancies = Vacancy::orderBy('judul_posisi')->get();

        return view('dashboard', compact(
            'filters',
            'totalApplications',
            'inProcess',
            'accepted',
            'openVacancies',
            'funnel',
            'funnelMax',
            'timeToHire',
            'stageRates',
            'bottlenecks',
            'bottleneckMax',
            'vacancySummary',
            'units',
            'vacancies',
        ));
    }
}
