<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Http\Requests\UploadMcuDocumentRequest;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateMcuController extends Controller
{
    public function show(string $token): View
    {
        $application = Application::where('token', $token)
            ->with(['candidate', 'vacancy', 'stages', 'mcuResult'])
            ->firstOrFail();

        $mcuStage = $application->stages->firstWhere('key', 'mcu');

        abort_if(! $mcuStage || $mcuStage->status !== ApplicationStageStatus::Aktif, 404);

        return view('candidate.mcu-upload', compact('application', 'mcuStage'));
    }

    public function upload(UploadMcuDocumentRequest $request, string $token): RedirectResponse
    {
        $application = Application::where('token', $token)
            ->with(['stages', 'mcuResult'])
            ->firstOrFail();

        $mcuStage = $application->stages->firstWhere('key', 'mcu');

        abort_if(! $mcuStage || $mcuStage->status !== ApplicationStageStatus::Aktif, 404);

        $path = $request->file('dokumen')->store('mcu-documents', 'public');

        $mcuResult = $application->mcuResult()->firstOrNew(['application_id' => $application->id]);

        if ($mcuResult->dokumen_path) {
            Storage::disk('public')->delete($mcuResult->dokumen_path);
        }

        $mcuResult->fill(['dokumen_path' => $path])->save();

        return redirect()
            ->route('kandidat.mcu.upload', $token)
            ->with('success', 'Dokumen MCU berhasil diunggah. Tim HR akan segera meninjau dokumen Anda.');
    }
}
