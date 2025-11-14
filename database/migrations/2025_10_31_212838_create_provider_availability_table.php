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
        Schema::create('provider_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Specific date
            $table->time('start_time')->nullable(); // If null, uses default schedule
            $table->time('end_time')->nullable(); // If null, uses default schedule
            $table->boolean('is_available')->default(true); // true = available, false = blocked/holiday
            $table->string('reason')->nullable(); // Reason for unavailability (holiday, conference, etc.)
            $table->timestamps();

            // Indexes
            $table->unique(['provider_profile_id', 'date']);
            $table->index(['provider_profile_id', 'date', 'is_available'], 'provider_avail_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_availability');
    }
};
