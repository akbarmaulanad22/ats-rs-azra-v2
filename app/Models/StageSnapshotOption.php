<?php

namespace App\Models;

use Database\Factories\StageSnapshotOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageSnapshotOption extends Model
{
    /** @use HasFactory<StageSnapshotOptionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'stage_snapshot_question_id',
        'teks_opsi',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(StageSnapshotQuestion::class, 'stage_snapshot_question_id');
    }
}
