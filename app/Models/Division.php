<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'href',
    ];

    /**
     * Связь с вакансиями (многие ко многим)
     */
    public function vacancies(): BelongsToMany
    {
        return $this->belongsToMany(Vacancy::class, 'division_vacancy')
            ->withTimestamps();
    }
}
