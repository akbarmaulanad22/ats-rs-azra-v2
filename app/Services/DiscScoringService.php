<?php

namespace App\Services;

use App\Enums\DiscDimension;
use App\Models\DiscResult;
use App\Models\DiscSubmission;

class DiscScoringService
{
    /**
     * Calculate DiSC scores from submitted answers and persist a DiscResult.
     *
     * Scoring method: count "most" selections per dimension.
     * Primary type = dimension with highest M count.
     * Secondary type = dimension with second highest M count.
     */
    public function calculate(DiscSubmission $submission): DiscResult
    {
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

        arsort($scores);
        $keys = array_keys($scores);

        return DiscResult::create([
            'disc_submission_id' => $submission->id,
            'skor_d' => $scores[DiscDimension::D->value],
            'skor_i' => $scores[DiscDimension::I->value],
            'skor_s' => $scores[DiscDimension::S->value],
            'skor_c' => $scores[DiscDimension::C->value],
            'tipe_primer' => $keys[0],
            'tipe_sekunder' => $keys[1],
        ]);
    }
}
