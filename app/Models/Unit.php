<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    protected $fillable = ['nama'];

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
