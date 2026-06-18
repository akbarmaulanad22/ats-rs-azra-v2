<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
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
            $query->where('nama_lengkap', 'ilike', "%{$search}%");
        }

        $candidates = $query->orderByDesc('talent_pool_flagged_at')->paginate(15)->withQueryString();

        return view('talent-pool.index', compact('candidates'));
    }

    public function store(Request $request, Candidate $candidate): RedirectResponse
    {
        Gate::authorize('flagTalentPool', $candidate);

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
