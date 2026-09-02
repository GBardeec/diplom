<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'from',
        'to',
        'currency',
        'formatted',
    ];

    protected $casts = [
        'from' => 'integer',
        'to' => 'integer',
    ];

    /**
     * Связь с вакансией (принадлежит)
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }
}
