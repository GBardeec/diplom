<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('from')->nullable();
            $table->unsignedInteger('to')->nullable();
            $table->string('currency')->nullable();
            $table->string('formatted')->nullable();
            $table->timestamps();

            $table->index('vacancy_id');
            $table->index(['from', 'to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
