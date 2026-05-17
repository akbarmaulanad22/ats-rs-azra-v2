<?php

namespace App\Http\Controllers;

use App\Actions\BuildCandidateProfileData;
use App\Exports\CandidateListExport;
use App\Models\Application;
use App\Models\Vacancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CandidateExportController extends Controller
{
    public function __construct(private readonly BuildCandidateProfileData $buildProfileData) {}

    public function list(Request $request, Vacancy $lowongan): BinaryFileResponse
    {
        Gate::authorize('export', $lowongan);

        $filters = [
            'stage' => $request->query('stage'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
        ];

        $format = $request->query('format', 'xlsx');
        $export = new CandidateListExport($lowongan, $filters);

        $date = now()->format('d-m-Y');
        $slug = str($lowongan->judul_posisi)->slug();

        if ($format === 'csv') {
            return Excel::download($export, "daftar-kandidat-{$slug}-{$date}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, "daftar-kandidat-{$slug}-{$date}.xlsx");
    }

    public function profile(Vacancy $lowongan, Application $application): Response
    {
        Gate::authorize('export', $lowongan);
        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application = $this->buildProfileData->execute($application);

        $pdf = Pdf::loadView('exports.candidate-profile', compact('application', 'lowongan'));
        $pdf->setPaper('a4', 'portrait');

        $date = now()->format('d-m-Y');
        $name = str($application->candidate->nama_lengkap)->slug();

        return $pdf->download("profil-kandidat-{$name}-{$date}.pdf");
    }
}
