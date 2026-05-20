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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class OfferingResponseController extends Controller
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
    ) {}

    public function showAcceptForm(OfferingLetter $offering): View
    {
        if ($offering->isResponded()) {
            return view('offering.already-responded', [
                'offering' => $offering->load('application.vacancy'),
            ]);
        }

        return view('offering.accept-form', [
            'offering' => $offering->load('application.vacancy', 'application.candidate'),
        ]);
    }

    public function accept(Request $request, OfferingLetter $offering): View
    {
        if ($offering->isResponded()) {
            return view('offering.already-responded', [
                'offering' => $offering->load('application.vacancy'),
            ]);
        }

        $offering->load('application.candidate', 'application.vacancy', 'application.stages');

        DB::transaction(function () use ($offering): void {
            $offering->update([
                'status' => OfferingLetterStatus::Accepted,
                'responded_at' => now(),
            ]);

            $this->pipelineService->advance($offering->application);
        });

        $hrAdmins = User::where('role', Role::HrAdmin)->where('is_active', true)->get();
        Notification::send($hrAdmins, new PenawaranDirespon($offering));

        return view('offering.accepted', [
            'offering' => $offering,
        ]);
    }

    public function showRejectForm(OfferingLetter $offering): View
    {
        if ($offering->isResponded()) {
            return view('offering.already-responded', [
                'offering' => $offering->load('application.vacancy'),
            ]);
        }

        return view('offering.reject-form', [
            'offering' => $offering->load('application.vacancy', 'application.candidate'),
        ]);
    }

    public function reject(Request $request, OfferingLetter $offering): View
    {
        if ($offering->isResponded()) {
            return view('offering.already-responded', [
                'offering' => $offering->load('application.vacancy'),
            ]);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $offering->load('application.candidate', 'application.vacancy', 'application.stages');

        DB::transaction(function () use ($offering, $request): void {
            $offering->update([
                'status' => OfferingLetterStatus::Rejected,
                'responded_at' => now(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            $application = $offering->application;
            $offeringStage = $application->stages()->where('key', 'surat_penawaran')->first();
            $offeringStage?->update(['status' => ApplicationStageStatus::Gagal]);
        });

        $hrAdmins = User::where('role', Role::HrAdmin)->where('is_active', true)->get();
        Notification::send($hrAdmins, new PenawaranDirespon($offering));

        return view('offering.rejected', [
            'offering' => $offering,
        ]);
    }
}
