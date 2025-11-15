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
        Schema::create('activity_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', [
                'emoji_choice',
                'text_copy_timed',
                'shape_copy_canvas',
                'trace_the_path',
                'dot_to_dot',
                'find_the_different_one',
                'simple_puzzle_drag',
                'whats_missing',
                'listen_and_type',
                'unscramble_the_word'
            ]);
            $table->text('prompt_text');
            $table->json('content_data')->nullable(); // URLs, coordinates, audio files, etc.
            $table->json('options')->nullable(); // Multiple choice options, correct answers, etc.
            $table->integer('max_points')->default(100);
            $table->integer('time_limit_seconds')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_items');
    }
};
