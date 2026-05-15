<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSocialMediaAccount extends Model
{
    protected $fillable = ['platform', 'link'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
