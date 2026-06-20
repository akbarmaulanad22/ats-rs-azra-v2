<?php

namespace App\Http\Controllers;

use App\Enums\JobTemplateStatus;
use App\Models\Candidate;
use App\Models\Vacancy;
use App\Services\CallbackCandidateFinder;
use App\Services\EmailNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CallbackController extends Controller
{
    public function __construct(
        private readonly CallbackCandidateFinder $finder,
        private readonly EmailNotificationService $emailNotificationService,
    ) {}

    public function index(Request $request, Vacancy $lowongan): View|RedirectResponse
    {
        Gate::authorize('callback', $lowongan);

        if (! $lowongan->isOpenForApplications()) {
            return $this->routeToOpenPeriod($lowongan);
        }

        $includeScreening = $request->boolean('screening');
        $rows = $this->finder->forVacancy($lowongan, $includeScreening);

        return view('callback.index', [
            'lowongan' => $lowongan,
            'rows' => $rows,
            'includeScreening' => $includeScreening,
        ]);
    }

    public function invite(Request $request, Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('callback', $lowongan);

        if (! $lowongan->isOpenForApplications()) {
            return back()->withErrors([
                'callback' => 'Lowongan sudah ditutup. Terbitkan periode baru sebelum memanggil kembali kandidat.',
            ]);
        }

        $validated = $request->validate([
            'candidate_ids' => ['required', 'array'],
            'candidate_ids.*' => ['integer', 'exists:candidates,id'],
        ]);

        $candidateIds = array_values(array_unique($validated['candidate_ids']));
        $candidates = Candidate::query()->whereIn('id', $candidateIds)->get();

        foreach ($candidates as $candidate) {
            $lowongan->callbackInvites()->updateOrCreate(
                ['candidate_id' => $candidate->id],
                ['invited_by' => $request->user()->id, 'invited_at' => now()],
            );

            $this->emailNotificationService->dispatch('undangan_panggil_kembali', $candidate->email, [
                'nama_kandidat' => $candidate->nama_lengkap,
                'judul_lowongan' => $lowongan->judul_posisi,
                'link_lamar' => route('karier.lamar', $lowongan),
            ]);
        }

        return back()->with('status', 'Undangan panggil kembali terkirim ke '.$candidates->count().' kandidat.');
    }

    /**
     * The target Vacancy is closed/expired. Send candidates to an open period:
     * reuse an existing open sibling, else route HR to publish the next period.
     */
    private function routeToOpenPeriod(Vacancy $lowongan): RedirectResponse
    {
        $openSibling = Vacancy::query()
            ->openForApplications()
            ->where('job_template_id', $lowongan->job_template_id)
            ->whereKeyNot($lowongan->id)
            ->first();

        if ($openSibling) {
            return redirect()->route('callback.index', $openSibling);
        }

        $jobTemplate = $lowongan->jobTemplate;

        if ($jobTemplate->status === JobTemplateStatus::Archived) {
            return redirect()->route('lowongan.index')->withErrors([
                'callback' => 'Template lowongan diarsipkan. Aktifkan kembali template untuk menerbitkan periode baru dan memanggil kandidat.',
            ]);
        }

        return redirect()->route('template-lowongan.terbitkan.form', [$jobTemplate, 'callback' => 1]);
    }
}
