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
        'status',
        'dokumen_path',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => McuStatus::class,
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
