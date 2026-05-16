<?php

namespace App\Http\Controllers;

use App\Models\InterviewCriteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InterviewCriteriaController extends Controller
{
    private const STAGE_KEYS = [
        'wawancara_kepala_unit' => 'Wawancara Kepala Unit',
        'wawancara_manajer_hr' => 'Wawancara Manajer HR',
        'wawancara_direktur' => 'Wawancara Direktur',
    ];

    public function index(): View
    {
        Gate::authorize('viewAny', InterviewCriteria::class);

        $criteria = InterviewCriteria::orderBy('stage_key')
            ->orderBy('urutan')
            ->get()
            ->groupBy('stage_key');

        return view('interview-criteria.index', [
            'criteria' => $criteria,
            'stageKeys' => self::STAGE_KEYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', InterviewCriteria::class);

        $validated = $request->validate([
            'stage_key' => ['required', 'in:'.implode(',', array_keys(self::STAGE_KEYS))],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $maxUrutan = InterviewCriteria::where('stage_key', $validated['stage_key'])->max('urutan') ?? 0;

        InterviewCriteria::create([
            'stage_key' => $validated['stage_key'],
            'nama' => $validated['nama'],
            'urutan' => $maxUrutan + 1,
        ]);

        return redirect()->route('kriteria-wawancara.index')
            ->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function update(Request $request, InterviewCriteria $kriteria_wawancara): RedirectResponse
    {
        Gate::authorize('update', $kriteria_wawancara);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $kriteria_wawancara->update($validated);

        return redirect()->route('kriteria-wawancara.index')
            ->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function destroy(InterviewCriteria $kriteria_wawancara): RedirectResponse
    {
        Gate::authorize('delete', $kriteria_wawancara);

        $kriteria_wawancara->delete();

        return redirect()->route('kriteria-wawancara.index')
            ->with('success', 'Kriteria berhasil dihapus.');
    }
}
