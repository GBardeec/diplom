<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $table = 'skills';

    protected $fillable = [
        'external_id',
        'title',
        'alias',
        'href',
    ];

    protected $casts = [
        'external_id' => 'integer',
    ];

    public function vacancies(): BelongsToMany
    {
        return $this->belongsToMany(Vacancy::class, 'skill_vacancy')
            ->withTimestamps();
    }
}
