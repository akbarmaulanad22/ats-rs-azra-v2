<?php

namespace App\Models;

use App\Enums\DiscDimension;
use Database\Factories\DiscQuestionWordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscQuestionWord extends Model
{
    /** @use HasFactory<DiscQuestionWordFactory> */
    use HasFactory;

    protected $fillable = ['disc_question_id', 'teks', 'dimensi'];

    protected function casts(): array
    {
        return [
            'dimensi' => DiscDimension::class,
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(DiscQuestion::class, 'disc_question_id');
    }
}
