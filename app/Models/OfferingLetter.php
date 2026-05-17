<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferingLetter extends Model
{
    protected $fillable = [
        'application_id',
        'jabatan_ditawarkan',
        'gaji',
        'tanggal_mulai',
        'catatan',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
