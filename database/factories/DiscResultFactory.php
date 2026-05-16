<?php

namespace Database\Factories;

use App\Enums\DiscDimension;
use App\Models\DiscResult;
use App\Models\DiscSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscResult>
 */
class DiscResultFactory extends Factory
{
    public function definition(): array
    {
        $scores = [7, 5, 4, 5];
        shuffle($scores);
        $dimensions = [DiscDimension::D, DiscDimension::I, DiscDimension::S, DiscDimension::C];
        $indexed = array_combine(array_map(fn ($d) => $d->value, $dimensions), $scores);
        arsort($indexed);
        $sorted = array_keys($indexed);

        return [
            'disc_submission_id' => DiscSubmission::factory(),
            'skor_d' => $indexed['D'],
            'skor_i' => $indexed['I'],
            'skor_s' => $indexed['S'],
            'skor_c' => $indexed['C'],
            'tipe_primer' => $sorted[0],
            'tipe_sekunder' => $sorted[1],
        ];
    }
}
