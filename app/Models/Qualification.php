<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    /**
     * Связь с вакансиями (один ко многим)
     */
    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }
}
