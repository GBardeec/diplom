<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->foreignId('vacancy_category_id')
                ->nullable()
                ->after('external_id')
                ->constrained('vacancy_categories')
                ->nullOnDelete();

            $table->index('vacancy_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropForeign(['vacancy_category_id']);
            $table->dropColumn('vacancy_category_id');
        });
    }
};
