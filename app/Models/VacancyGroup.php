<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancyGroup extends Model
{
    protected $fillable = [
        'external_id',
        'title',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'external_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Категории, принадлежащие этой группе
     */
    public function categories(): HasMany
    {
        return $this->hasMany(VacancyCategory::class, 'group_id');
    }
}
