<?php

namespace App\Http\Controllers;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\JenisPendidikan;
use App\Enums\StatusPerkawinan;
use App\Enums\TingkatKemampuanBahasa;
use App\Enums\VacancyStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $service) {}

    public function create(Vacancy $vacancy): View
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
                && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $vacancy->load('unit');

        return view('career.apply', [
            'vacancy' => $vacancy,
            'jenisKelaminOptions' => JenisKelamin::cases(),
            'statusPerkawinanOptions' => StatusPerkawinan::cases(),
            'golonganDarahOptions' => GolonganDarah::cases(),
            'jenisPendidikanOptions' => JenisPendidikan::cases(),
            'tingkatBahasaOptions' => TingkatKemampuanBahasa::cases(),
        ]);
    }

    public function store(StoreApplicationRequest $request, Vacancy $vacancy): RedirectResponse
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
                && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $vacancy->load('workflowTemplateSnapshot.stages');

        try {
            $application = $this->service->store($request, $vacancy);
        } catch (UniqueConstraintViolationException) {
            return back()->withErrors([
                'email' => 'Anda sudah pernah melamar lowongan ini.',
            ])->withInput();
        }

        return redirect()->route('karier.lamaran.konfirmasi', ['token' => $application->token]);
    }

    public function confirmation(string $token): View
    {
        $application = Application::where('token', $token)
            ->with(['candidate', 'vacancy.unit', 'stages'])
            ->firstOrFail();

        return view('career.confirmation', compact('application'));
    }
}
