<?php

namespace App\Services;

use App\Enums\DiscDimension;
use App\Models\DiscResult;
use App\Models\DiscSubmission;

class DiscScoringService
{
    /**
     * Scoring: count "most" selections per dimension.
     * Primary = highest count; secondary = second highest.
     * Ties broken by D > I > S > C priority order.
     */
    public function calculate(DiscSubmission $submission): DiscResult
    {
        $priorityOrder = [DiscDimension::D, DiscDimension::I, DiscDimension::S, DiscDimension::C];

        $scores = [
            DiscDimension::D->value => 0,
            DiscDimension::I->value => 0,
            DiscDimension::S->value => 0,
            DiscDimension::C->value => 0,
        ];

        $submission->load('answers.mostWord');

        foreach ($submission->answers as $answer) {
            $dim = $answer->mostWord->dimensi->value;
            $scores[$dim]++;
        }

        $sorted = collect($priorityOrder)->sortByDesc(fn (DiscDimension $d) => $scores[$d->value])->values();

        return DiscResult::create([
            'disc_submission_id' => $submission->id,
            'skor_d' => $scores[DiscDimension::D->value],
            'skor_i' => $scores[DiscDimension::I->value],
            'skor_s' => $scores[DiscDimension::S->value],
            'skor_c' => $scores[DiscDimension::C->value],
            'tipe_primer' => $sorted[0]->value,
            'tipe_sekunder' => $sorted[1]->value,
        ]);
    }
}
