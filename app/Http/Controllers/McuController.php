<?php

namespace App\Http\Controllers;

use App\Enums\McuStatus;
use App\Http\Requests\UpdateMcuStatusRequest;
use App\Http\Requests\UploadMcuDocumentRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class McuController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function updateStatus(UpdateMcuStatusRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageMcu', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages', 'mcuResult']);

        $mcuStage = $application->stages->firstWhere('key', 'mcu');
        abort_if(! $mcuStage, 404);

        if (! $mcuStage->status->isAdvanceable()) {
            return back()->withErrors(['mcu' => 'MCU sudah selesai diproses.']);
        }

        $status = McuStatus::from($request->input('status'));

        $application->mcuResult()->updateOrCreate(
            ['application_id' => $application->id],
            ['status' => $status, 'catatan' => $request->input('catatan')]
        );

        if ($status->isPassed()) {
            try {
                $this->pipelineService->advance($application);
            } catch (\RuntimeException $e) {
                return back()->withErrors(['mcu' => $e->getMessage()]);
            }

            return redirect()
                ->route('lowongan.pipeline', $lowongan)
                ->with('success', 'Kandidat lulus MCU dan dilanjutkan ke tahap onboarding.');
        }

        if ($status->isFailed()) {
            try {
                $this->pipelineService->fail($application);
            } catch (\RuntimeException $e) {
                return back()->withErrors(['mcu' => $e->getMessage()]);
            }

            return redirect()
                ->route('lowongan.pipeline', $lowongan)
                ->with('success', 'Kandidat tidak lulus MCU dan telah ditolak.');
        }

        return redirect()
            ->route('lowongan.pipeline.show', [$lowongan, $application])
            ->with('success', 'Status MCU berhasil diperbarui.');
    }

    public function uploadDocument(UploadMcuDocumentRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageMcu', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['stages', 'mcuResult']);

        $mcuStage = $application->stages->firstWhere('key', 'mcu');
        abort_if(! $mcuStage, 404);

        if (! $mcuStage->status->isAdvanceable()) {
            return back()->withErrors(['dokumen' => 'MCU sudah selesai diproses.']);
        }

        $path = $request->file('dokumen')->store('mcu-documents', 'public');

        DB::transaction(function () use ($application, $path): void {
            $mcuResult = $application->mcuResult()->lockForUpdate()->firstOrNew(['application_id' => $application->id]);

            if ($mcuResult->dokumen_path) {
                Storage::disk('public')->delete($mcuResult->dokumen_path);
            }

            $mcuResult->fill(['dokumen_path' => $path])->save();
        });

        return redirect()
            ->route('lowongan.pipeline.show', [$lowongan, $application])
            ->with('success', 'Dokumen MCU berhasil diunggah.');
    }
}
