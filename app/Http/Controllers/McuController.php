<?php

namespace App\Http\Controllers;

use App\Enums\McuStatus;
use App\Http\Requests\StoreMcuResultRequest;
use App\Models\Application;
use App\Models\McuResult;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class McuController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function store(StoreMcuResultRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageMcu', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load('stages');

        $mcuStage = $application->stages->firstWhere('key', 'mcu');
        abort_if(! $mcuStage, 404);

        if (! $mcuStage->status->isAdvanceable()) {
            return back()->withErrors(['mcu' => 'MCU sudah selesai diproses.']);
        }

        if ($mcuStage->mcuResult()->exists()) {
            return back()->withErrors(['mcu' => 'Hasil MCU sudah direkam sebelumnya.']);
        }

        $keputusan = McuStatus::from($request->input('keputusan'));
        $catatan = $request->input('catatan');

        $path = null;
        if ($request->hasFile('dokumen')) {
            $path = $request->file('dokumen')->store('mcu-documents', 'public');
        }

        try {
            DB::transaction(function () use ($mcuStage, $application, $request, $keputusan, $catatan, $path): void {
                McuResult::create([
                    'application_id' => $application->id,
                    'application_stage_id' => $mcuStage->id,
                    'reviewer_id' => $request->user()->id,
                    'keputusan' => $keputusan,
                    'dokumen_path' => $path,
                    'catatan' => $catatan,
                    'submitted_at' => now(),
                ]);

                match ($keputusan) {
                    McuStatus::Lulus => $this->pipelineService->advance($application),
                    McuStatus::TidakLulus => $this->pipelineService->fail($application),
                    McuStatus::Ditangguhkan => $this->pipelineService->reserve($application),
                };
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            return back()->withErrors(['mcu' => $e->getMessage()]);
        }

        $label = match ($keputusan) {
            McuStatus::Lulus => 'lulus MCU dan dilanjutkan ke tahap onboarding',
            McuStatus::TidakLulus => 'tidak lulus MCU dan telah ditolak',
            McuStatus::Ditangguhkan => 'ditangguhkan pada tahap MCU',
        };

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', "Kandidat {$label}.");
    }
}
