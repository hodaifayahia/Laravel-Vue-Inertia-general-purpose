<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('estimated_duration_minutes')->default(10);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->string('difficulty_level')->default('beginner'); // beginner, intermediate, advanced
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
