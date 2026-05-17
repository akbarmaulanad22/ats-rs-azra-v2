<?php

namespace App\Models;

use App\Enums\MbtiPole;
use Database\Factories\MbtiQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MbtiQuestion extends Model
{
    /** @use HasFactory<MbtiQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'urutan',
        'dikotomi',
        'pernyataan_a',
        'kutub_a',
        'pernyataan_b',
    ];

    protected function casts(): array
    {
        return [
            'kutub_a' => MbtiPole::class,
        ];
    }

    public function kutubB(): MbtiPole
    {
        return $this->kutub_a->opposite();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MbtiAnswer::class);
    }
}
