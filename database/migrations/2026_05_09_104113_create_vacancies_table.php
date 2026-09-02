<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique();
            $table->string('href');
            $table->string('title');
            $table->boolean('is_marked')->default(false);
            $table->boolean('remote_work')->default(false);
            $table->string('employment')->nullable();
            $table->foreignId('qualification_id')->constrained()->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->string('published_title')->nullable();
            $table->boolean('archived')->default(false);
            $table->boolean('hidden')->default(false);
            $table->timestamps();

            $table->index('qualification_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
