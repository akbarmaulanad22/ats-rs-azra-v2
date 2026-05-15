<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Enums\VacancyStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function create(Vacancy $vacancy): View
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
                && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $vacancy->load('unit');

        return view('career.apply', compact('vacancy'));
    }

    public function store(StoreApplicationRequest $request, Vacancy $vacancy): RedirectResponse|View
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
                && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $vacancy->load('workflowTemplateSnapshot.stages');

        $cvPath = $request->file('cv')->storeAs(
            'cv',
            Str::random(40).'.pdf',
            'local',
        );

        try {
            $application = DB::transaction(function () use ($request, $vacancy, $cvPath): Application {
                $candidate = Candidate::firstOrCreate(
                    ['email' => $request->validated('email')],
                    [
                        'nama_lengkap' => $request->validated('nama_lengkap'),
                        'no_telepon' => $request->validated('no_telepon'),
                    ],
                );

                $application = Application::create([
                    'candidate_id' => $candidate->id,
                    'vacancy_id' => $vacancy->id,
                    'token' => Str::uuid()->toString(),
                    'cv_path' => $cvPath,
                ]);

                $stages = $vacancy->workflowTemplateSnapshot->stages;

                $stagesData = $stages->map(function ($stage, $index) use ($application): array {
                    return [
                        'application_id' => $application->id,
                        'position' => $stage->position,
                        'key' => $stage->key,
                        'nama' => $stage->nama,
                        'status' => $index === 0
                            ? ApplicationStageStatus::Selesai->value
                            : ($index === 1
                                ? ApplicationStageStatus::Aktif->value
                                : ApplicationStageStatus::Pending->value),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                $application->stages()->insert($stagesData);

                return $application;
            });
        } catch (UniqueConstraintViolationException) {
            Storage::disk('local')->delete($cvPath);

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
