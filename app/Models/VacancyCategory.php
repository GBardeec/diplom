<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancyCategory extends Model
{
    protected $fillable = [
        'group_id',
        'external_id',
        'title',
        'alias',
        'description',
        'parent_id',
        'level',
        'sort_order',
    ];

    protected $casts = [
        'external_id' => 'integer',
        'level' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Родительская категория (кто выше по иерархии)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(VacancyCategory::class, 'parent_id');
    }

    /**
     * Дочерние категории (кто подчиняется)
     */
    public function children(): HasMany
    {
        return $this->hasMany(VacancyCategory::class, 'parent_id');
    }

    /**
     * Группа (отдел) к которой относится категория
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(VacancyGroup::class, 'group_id');
    }

    /**
     * Рекурсивно получить всех потомков
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Рекурсивно получить всех предков
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'vacancy_category_id');
    }
}
