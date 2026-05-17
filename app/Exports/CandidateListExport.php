<?php

namespace App\Exports;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\Vacancy;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CandidateListExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  array{stage?: ?string, status?: ?string, search?: ?string}  $filters
     */
    public function __construct(
        private readonly Vacancy $vacancy,
        private readonly array $filters = [],
    ) {}

    public function query()
    {
        $query = Application::with(['candidate', 'stages', 'testSubmission'])
            ->where('vacancy_id', $this->vacancy->id);

        if (! empty($this->filters['stage'])) {
            $stage = $this->filters['stage'];
            $query->whereHas('stages', fn ($q) => $q->where('key', $stage)->where('status', '!=', ApplicationStageStatus::Pending->value));
        }

        if (! empty($this->filters['status'])) {
            $status = $this->filters['status'];
            $query->whereHas('stages', fn ($q) => $q->where('status', $status)
                ->whereRaw('position = (select max(position) from application_stages where application_id = applications.id and status != ?)', [ApplicationStageStatus::Pending->value])
            );
        }

        if (! empty($this->filters['search'])) {
            $search = '%'.mb_strtolower($this->filters['search']).'%';
            $query->whereHas('candidate', fn ($q) => $q
                ->where(fn ($sub) => $sub
                    ->whereRaw('lower(nama_lengkap) like ?', [$search])
                    ->orWhereRaw('lower(email) like ?', [$search])
                )
            );
        }

        return $query;
    }

    /**
     * @return array<string>
     */
    public function headings(): array
    {
        return [
            'Nama Kandidat',
            'Email',
            'No. Telepon',
            'Tanggal Melamar',
            'Tahap Saat Ini',
            'Status',
            'Skor Tes Kompetensi',
        ];
    }

    /**
     * @param  Application  $application
     * @return array<mixed>
     */
    public function map($application): array
    {
        $currentStage = $application->currentStage();
        $testSubmission = $application->testSubmission;

        return [
            $application->candidate->nama_lengkap,
            $application->candidate->email,
            $application->candidate->no_telepon,
            $application->created_at->format('d/m/Y'),
            $currentStage?->nama ?? '-',
            $currentStage?->status->label() ?? '-',
            $testSubmission?->submitted_at ? ($testSubmission->total_skor ?? '-') : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
