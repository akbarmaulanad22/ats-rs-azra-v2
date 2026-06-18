<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TalentPoolController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewTalentPool', Candidate::class);

        $query = Candidate::inTalentPool()
            ->with(['talentPoolFlaggedBy', 'applications.vacancy.unit']);

        if ($search = $request->query('search')) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nama_lengkap', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $candidates = $query->orderByDesc('talent_pool_flagged_at')->paginate(15)->withQueryString();

        return view('talent-pool.index', compact('candidates'));
    }

    public function show(Candidate $candidate): View
    {
        Gate::authorize('viewTalentPool', Candidate::class);

        $candidate->load([
            'talentPoolFlaggedBy',
            'formalEducations',
            'informalEducations',
            'workExperiences',
            'organizationExperiences',
            'siblings',
            'spouses',
            'children',
            'languageSkills',
            'achievements',
            'applications' => fn ($q) => $q->latest(),
            'applications.vacancy.unit',
            'applications.stages',
            'applications.socialMediaAccounts',
            'applications.references',
            'applications.discSubmission.result',
            'applications.mbtiSubmission.result',
        ]);

        $application = $candidate->applications->first();

        abort_if($application === null, 404);

        return view('talent-pool.show', compact('candidate', 'application'));
    }

    public function store(Request $request, Candidate $candidate): RedirectResponse
    {
        Gate::authorize('flagTalentPool', $candidate);

        $candidate->loadMissing('applications.stages');

        abort_unless($candidate->hasReservedApplication(), 403);

        if ($candidate->isInTalentPool()) {
            return back()->with('status', 'Kandidat sudah ada di Kandidat Potensial.');
        }

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ]);

        $candidate->update([
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $request->user()->id,
            'talent_pool_reason' => $validated['alasan'],
        ]);

        return back()->with('status', 'Kandidat ditandai sebagai Kandidat Potensial.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        Gate::authorize('unflagTalentPool', $candidate);

        $candidate->update([
            'talent_pool_flagged_at' => null,
            'talent_pool_flagged_by' => null,
            'talent_pool_reason' => null,
        ]);

        return back()->with('status', 'Kandidat dihapus dari Kandidat Potensial.');
    }
}
