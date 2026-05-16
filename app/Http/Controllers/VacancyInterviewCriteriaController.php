<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use App\Models\VacancyInterviewCriteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyInterviewCriteriaController extends Controller
{
    private const STAGE_KEYS = [
        'wawancara_kepala_unit' => 'Wawancara Kepala Unit',
        'wawancara_manajer_hr' => 'Wawancara Manajer HR',
        'wawancara_direktur' => 'Wawancara Direktur',
    ];

    public function show(Vacancy $lowongan): View
    {
        Gate::authorize('manageInterviewCriteria', $lowongan);

        $criteria = $lowongan->interviewCriteria()
            ->orderBy('stage_key')
            ->orderBy('urutan')
            ->get()
            ->groupBy('stage_key');

        return view('vacancy-interview-criteria.show', [
            'lowongan' => $lowongan,
            'criteria' => $criteria,
            'stageKeys' => self::STAGE_KEYS,
        ]);
    }

    public function save(Request $request, Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('manageInterviewCriteria', $lowongan);

        $validated = $request->validate([
            'criteria' => ['required', 'array'],
            'criteria.*' => ['array'],
            'criteria.*.*.nama' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($lowongan, $validated): void {
            $lowongan->interviewCriteria()->delete();

            foreach ($validated['criteria'] as $stageKey => $items) {
                if (! array_key_exists($stageKey, self::STAGE_KEYS)) {
                    continue;
                }

                foreach (array_values($items) as $urutan => $item) {
                    VacancyInterviewCriteria::create([
                        'vacancy_id' => $lowongan->id,
                        'stage_key' => $stageKey,
                        'nama' => $item['nama'],
                        'urutan' => $urutan + 1,
                    ]);
                }
            }
        });

        return redirect()->route('lowongan.kriteria-wawancara.show', $lowongan)
            ->with('success', 'Kriteria wawancara berhasil disimpan.');
    }
}
