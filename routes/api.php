<?php

use App\Http\Controllers\Api\RecommendationReportController;
use Illuminate\Support\Facades\Route;

Route::post('/recommendations', [RecommendationReportController::class, 'store']);
Route::get('/recommendations/{reportUuid}', [RecommendationReportController::class, 'show']);
