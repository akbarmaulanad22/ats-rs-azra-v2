<?php

namespace Database\Factories;

use App\Models\MbtiResult;
use App\Models\MbtiSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MbtiResult>
 */
class MbtiResultFactory extends Factory
{
    public function definition(): array
    {
        $skorE = fake()->numberBetween(5, 13);
        $skorI = 18 - $skorE;
        $skorS = fake()->numberBetween(5, 10);
        $skorN = 17 - $skorS;
        $skorT = fake()->numberBetween(5, 13);
        $skorF = 18 - $skorT;
        $skorJ = fake()->numberBetween(5, 10);
        $skorP = 17 - $skorJ;

        $tipe = ($skorE >= $skorI ? 'E' : 'I')
            .($skorS >= $skorN ? 'S' : 'N')
            .($skorT >= $skorF ? 'T' : 'F')
            .($skorJ >= $skorP ? 'J' : 'P');

        return [
            'mbti_submission_id' => MbtiSubmission::factory()->submitted(),
            'skor_e' => $skorE,
            'skor_i' => $skorI,
            'skor_s' => $skorS,
            'skor_n' => $skorN,
            'skor_t' => $skorT,
            'skor_f' => $skorF,
            'skor_j' => $skorJ,
            'skor_p' => $skorP,
            'tipe' => $tipe,
            'kekuatan_ei' => (int) round(abs($skorE - $skorI) / 18 * 100),
            'kekuatan_sn' => (int) round(abs($skorS - $skorN) / 17 * 100),
            'kekuatan_tf' => (int) round(abs($skorT - $skorF) / 18 * 100),
            'kekuatan_jp' => (int) round(abs($skorJ - $skorP) / 17 * 100),
        ];
    }
}
