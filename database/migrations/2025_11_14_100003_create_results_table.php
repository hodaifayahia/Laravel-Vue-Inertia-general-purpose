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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_item_id')->constrained()->cascadeOnDelete();
            $table->json('result_data'); // Flexible storage: emoji, text, drawing data, coordinates, etc.
            $table->integer('points_awarded')->default(0);
            $table->integer('time_taken_ms')->nullable(); // Milliseconds taken to complete
            $table->boolean('is_correct')->nullable(); // For items with right/wrong answers
            $table->timestamps();
            
            $table->index('activity_attempt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
