<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');           // ссылка на vacancy_groups
            $table->unsignedInteger('external_id');           // ID из API Хабр Карьеры
            $table->string('title');
            $table->string('alias')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('level')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('group_id');
            $table->index('external_id');
            $table->index('parent_id');
            $table->index('level');

            $table->foreign('group_id')
                ->references('id')
                ->on('vacancy_groups')
                ->onDelete('cascade');

            $table->foreign('parent_id')
                ->references('id')
                ->on('vacancy_categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_categories');
    }
};
