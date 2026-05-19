<?php

namespace App\Models;

use Database\Factories\InterviewTemplateItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewTemplateItem extends Model
{
    /** @use HasFactory<InterviewTemplateItemFactory> */
    use HasFactory;

    protected $fillable = [
        'interview_template_id',
        'teks',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InterviewTemplate::class, 'interview_template_id');
    }
}
