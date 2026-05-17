<?php

namespace App\Services;

use App\Enums\MbtiPole;
use App\Models\MbtiResult;
use App\Models\MbtiSubmission;

class MbtiScoringService
{
    /**
     * Scoring: for each answer, increment the pole chosen.
     * Pilihan A → kutub_a of the question; pilihan B → opposite pole.
     * Ties broken by second pole (I, N, F, P) per MBTI convention.
     */
    public function calculate(MbtiSubmission $submission): MbtiResult
    {
        $scores = array_fill_keys(
            array_column(MbtiPole::cases(), 'value'),
            0
        );

        $submission->load('answers.question');

        foreach ($submission->answers as $answer) {
            $pole = $answer->pilihan === 'A'
                ? $answer->question->kutub_a
                : $answer->question->kutubB();

            $scores[$pole->value]++;
        }

        $tipe = $this->resolveType($scores);

        $totalEI = $scores[MbtiPole::E->value] + $scores[MbtiPole::I->value];
        $totalSN = $scores[MbtiPole::S->value] + $scores[MbtiPole::N->value];
        $totalTF = $scores[MbtiPole::T->value] + $scores[MbtiPole::F->value];
        $totalJP = $scores[MbtiPole::J->value] + $scores[MbtiPole::P->value];

        return MbtiResult::create([
            'mbti_submission_id' => $submission->id,
            'skor_e' => $scores[MbtiPole::E->value],
            'skor_i' => $scores[MbtiPole::I->value],
            'skor_s' => $scores[MbtiPole::S->value],
            'skor_n' => $scores[MbtiPole::N->value],
            'skor_t' => $scores[MbtiPole::T->value],
            'skor_f' => $scores[MbtiPole::F->value],
            'skor_j' => $scores[MbtiPole::J->value],
            'skor_p' => $scores[MbtiPole::P->value],
            'tipe' => $tipe,
            'kekuatan_ei' => $totalEI > 0
                ? (int) round(abs($scores[MbtiPole::E->value] - $scores[MbtiPole::I->value]) / $totalEI * 100)
                : 0,
            'kekuatan_sn' => $totalSN > 0
                ? (int) round(abs($scores[MbtiPole::S->value] - $scores[MbtiPole::N->value]) / $totalSN * 100)
                : 0,
            'kekuatan_tf' => $totalTF > 0
                ? (int) round(abs($scores[MbtiPole::T->value] - $scores[MbtiPole::F->value]) / $totalTF * 100)
                : 0,
            'kekuatan_jp' => $totalJP > 0
                ? (int) round(abs($scores[MbtiPole::J->value] - $scores[MbtiPole::P->value]) / $totalJP * 100)
                : 0,
        ]);
    }

    /** @param array<string, int> $scores */
    private function resolveType(array $scores): string
    {
        // Ties go to second pole (I, N, F, P) per MBTI convention
        $ei = $scores[MbtiPole::E->value] > $scores[MbtiPole::I->value] ? 'E' : 'I';
        $sn = $scores[MbtiPole::S->value] > $scores[MbtiPole::N->value] ? 'S' : 'N';
        $tf = $scores[MbtiPole::T->value] > $scores[MbtiPole::F->value] ? 'T' : 'F';
        $jp = $scores[MbtiPole::J->value] > $scores[MbtiPole::P->value] ? 'J' : 'P';

        return $ei.$sn.$tf.$jp;
    }
}
