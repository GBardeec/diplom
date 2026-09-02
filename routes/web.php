<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HierarchyController;
use App\Http\Controllers\RecommendationController;

// Главная страница
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Страница с формой анализа компетенций
Route::get('/competency-analysis', function () {
    return redirect()->route('recommendations.index');
})->name('competency.analysis');

// Иерархическая структура
Route::get('/hierarchy-structure', [HierarchyController::class, 'index'])->name('hierarchy.structure');

Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
Route::get('/recommendations/{reportUuid}', [RecommendationController::class, 'show'])->name('recommendations.show');
