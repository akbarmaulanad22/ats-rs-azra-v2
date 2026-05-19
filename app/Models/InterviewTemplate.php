<?php

namespace App\Models;

use App\Enums\InterviewTemplateType;
use Database\Factories\InterviewTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewTemplate extends Model
{
    /** @use HasFactory<InterviewTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => InterviewTemplateType::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InterviewTemplateItem::class)->orderBy('urutan');
    }
}
