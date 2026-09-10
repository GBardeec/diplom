<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\CareerMapService;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_category_vacancy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_category_id')->constrained('vacancy_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['vacancy_id', 'vacancy_category_id']);
            $table->index('vacancy_category_id');
        });

        DB::table('vacancies')
            ->whereNotNull('vacancy_category_id')
            ->orderBy('id')
            ->eachById(function ($vacancy) {
                DB::table('vacancy_category_vacancy')->insert([
                    'vacancy_id' => $vacancy->id,
                    'vacancy_category_id' => $vacancy->vacancy_category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        app(CareerMapService::class)->rebuild();
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_category_vacancy');
    }
};
