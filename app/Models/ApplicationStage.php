<?php

namespace App\Models;

use App\Enums\ApplicationStageStatus;
use Database\Factories\ApplicationStageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStage extends Model
{
    /** @use HasFactory<ApplicationStageFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'position',
        'key',
        'nama',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStageStatus::class,
            'position' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
