<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOnboardingInvitationRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
        private readonly EmailNotificationService $emailNotificationService,
    ) {}

    public function sendInvitation(SendOnboardingInvitationRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageOnboarding', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages', 'onboardingResult']);

        $onboardingStage = $application->stages->firstWhere('key', 'onboarding');
        abort_if(! $onboardingStage, 404);

        if (! $onboardingStage->status->isAdvanceable()) {
            return back()->withErrors(['onboarding' => 'Tahap onboarding sudah selesai.']);
        }

        $tanggalBergabungFormatted = Carbon::parse($request->input('tanggal_bergabung'))->translatedFormat('d F Y');

        $onboarding = $application->onboardingResult()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'tanggal_bergabung' => $request->input('tanggal_bergabung'),
                'catatan' => $request->input('catatan'),
                'sent_at' => now(),
            ]
        );

        try {
            $this->emailNotificationService->dispatch('undangan_onboarding', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'tanggal_onboarding' => $tanggalBergabungFormatted,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('lowongan.pipeline.show', [$lowongan, $application])
            ->with('success', 'Undangan onboarding berhasil dikirim ke kandidat.');
    }

    public function complete(Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('manageOnboarding', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages']);

        $onboardingStage = $application->stages->firstWhere('key', 'onboarding');
        abort_if(! $onboardingStage, 404);

        if (! $onboardingStage->status->isAdvanceable()) {
            return back()->withErrors(['onboarding' => 'Tahap onboarding sudah selesai.']);
        }

        try {
            $this->pipelineService->advance($application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['onboarding' => $e->getMessage()]);
        }

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Onboarding kandidat telah diselesaikan.');
    }
}
