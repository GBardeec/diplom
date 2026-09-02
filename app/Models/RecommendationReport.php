<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationReport extends Model
{
    protected $fillable = ['report_uuid', 'session_id', 'input_data', 'view_count', 'last_viewed_at'];

    protected function casts(): array
    {
        return ['input_data' => 'array', 'last_viewed_at' => 'datetime'];
    }
}
