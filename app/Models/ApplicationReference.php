<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReference extends Model
{
    protected $fillable = [
        'application_id',
        'nama_karyawan',
        'hubungan',
        'keterangan',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
