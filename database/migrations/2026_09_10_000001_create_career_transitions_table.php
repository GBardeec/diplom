<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('career_transitions')) {
            return;
        }

        Schema::create('career_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_category_id')->constrained('vacancy_categories')->cascadeOnDelete();
            $table->foreignId('to_category_id')->constrained('vacancy_categories')->cascadeOnDelete();
            $table->decimal('skills_similarity', 5, 4);
            $table->timestamps();
            $table->unique(['from_category_id', 'to_category_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('career_transitions');
    }
};
