<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Enums\OfferingLetterStatus;
use App\Enums\Role;
use App\Models\OfferingLetter;
use App\Models\User;
use App\Notifications\PenawaranDirespon;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class OfferingResponseController extends Controller
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
    ) {}

    public function accept(Request $request, OfferingLetter $offering): View
    {
        if ($offering->isResponded()) {
            return view('offering.already-responded', [
                'offering' => $offering->load('application.vacancy'),
            ]);
        }

        if ($request->isMethod('GET')) {
            return view('offering.accept-form', [
                'offering' => $offering->load('application.vacancy', 'application.candidate'),
            ]);
        }

        $offering->update([
            'status' => OfferingLetterStatus::Accepted,
            'responded_at' => now(),
        ]);

        $offering->load('application.candidate', 'application.vacancy', 'application.stages');

        try {
            $this->pipelineService->advance($offering->application);
        } catch (\Throwable $e) {
            report($e);
        }

        $hrAdmins = User::where('role', Role::HrAdmin)->where('is_active', true)->get();
        Notification::send($hrAdmins, new PenawaranDirespon($offering));

        return view('offering.accepted', [
            'offering' => $offering,
        ]);
    }

    public function reject(Request $request, OfferingLetter $offering): View
    {
        if ($offering->isResponded()) {
            return view('offering.already-responded', [
                'offering' => $offering->load('application.vacancy'),
            ]);
        }

        if ($request->isMethod('GET')) {
            return view('offering.reject-form', [
                'offering' => $offering->load('application.vacancy', 'application.candidate'),
            ]);
        }

        $offering->update([
            'status' => OfferingLetterStatus::Rejected,
            'responded_at' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        $offering->load('application.candidate', 'application.vacancy', 'application.stages');

        $application = $offering->application;
        $stages = $application->stages()->orderBy('position')->get();
        $offeringStage = $stages->firstWhere('key', 'surat_penawaran');
        $offeringStage?->update(['status' => ApplicationStageStatus::Gagal]);

        $hrAdmins = User::where('role', Role::HrAdmin)->where('is_active', true)->get();
        Notification::send($hrAdmins, new PenawaranDirespon($offering));

        return view('offering.rejected', [
            'offering' => $offering,
        ]);
    }
}
