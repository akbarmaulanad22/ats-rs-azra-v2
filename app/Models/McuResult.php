<?php

namespace App\Models;

use App\Enums\McuStatus;
use Database\Factories\McuResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McuResult extends Model
{
    /** @use HasFactory<McuResultFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'application_stage_id',
        'reviewer_id',
        'keputusan',
        'dokumen_path',
        'catatan',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'keputusan' => McuStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationStage(): BelongsTo
    {
        return $this->belongsTo(ApplicationStage::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
