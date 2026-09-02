<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('external_id');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_groups');
    }
};
