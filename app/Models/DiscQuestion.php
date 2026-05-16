<?php

namespace App\Models;

use Database\Factories\DiscQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscQuestion extends Model
{
    /** @use HasFactory<DiscQuestionFactory> */
    use HasFactory;

    protected $fillable = ['urutan'];

    public function words(): HasMany
    {
        return $this->hasMany(DiscQuestionWord::class);
    }
}
