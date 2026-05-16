<?php

namespace App\Models;

use App\Enums\DiscDimension;
use Database\Factories\DiscResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscResult extends Model
{
    /** @use HasFactory<DiscResultFactory> */
    use HasFactory;

    protected $fillable = [
        'disc_submission_id',
        'skor_d',
        'skor_i',
        'skor_s',
        'skor_c',
        'tipe_primer',
        'tipe_sekunder',
    ];

    protected function casts(): array
    {
        return [
            'skor_d' => 'integer',
            'skor_i' => 'integer',
            'skor_s' => 'integer',
            'skor_c' => 'integer',
            'tipe_primer' => DiscDimension::class,
            'tipe_sekunder' => DiscDimension::class,
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(DiscSubmission::class, 'disc_submission_id');
    }

    public function profilRingkasan(): string
    {
        return 'Tipe '.$this->tipe_primer->value.', Sekunder '.$this->tipe_sekunder->value;
    }
}
