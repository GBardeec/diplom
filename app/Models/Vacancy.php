<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'vacancy_category_id',
        'href',
        'title',
        'is_marked',
        'remote_work',
        'employment',
        'qualification_id',
        'published_at',
        'published_title',
        'archived',
        'hidden',
        'vacancy_id',
    ];

    protected $casts = [
        'is_marked' => 'boolean',
        'remote_work' => 'boolean',
        'published_at' => 'datetime',
        'archived' => 'boolean',
        'hidden' => 'boolean',
    ];

    /**
     * Связь с квалификацией (принадлежит)
     */
    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class);
    }

    /**
     * Связь с зарплатой (один к одному или один ко многим)
     * В вашей миграции - один ко многим, но typically это one-to-one
     */
    public function salary(): HasOne
    {
        return $this->hasOne(Salary::class);
    }

    /**
     * Связь с локациями (многие ко многим)
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_vacancy')
            ->withTimestamps();
    }

    /**
     * Связь с навыками (многие ко многим)
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_vacancy')
            ->withTimestamps();
    }

    /**
     * Связь с дивизионами (многие ко многим)
     */
    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'division_vacancy')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(VacancyCategory::class, 'vacancy_category_id');
    }
}
