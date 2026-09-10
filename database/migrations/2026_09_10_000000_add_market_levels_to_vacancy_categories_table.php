<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancy_categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('market_level')->nullable()->after('level');
            $table->unsignedInteger('market_salary_median')->nullable()->after('market_level');
            $table->unsignedSmallInteger('market_salary_sample_size')->default(0)->after('market_salary_median');
            $table->index('market_level');
        });
    }

    public function down(): void
    {
        Schema::table('vacancy_categories', function (Blueprint $table) {
            $table->dropIndex(['market_level']);
            $table->dropColumn(['market_level', 'market_salary_median', 'market_salary_sample_size']);
        });
    }
};
