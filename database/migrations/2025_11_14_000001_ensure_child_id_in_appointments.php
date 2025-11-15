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
        Schema::table('appointments', function (Blueprint $table) {
            // Add child_id column if it doesn't exist
            if (!Schema::hasColumn('appointments', 'child_id')) {
                $table->foreignId('child_id')
                    ->nullable()
                    ->constrained('children')
                    ->cascadeOnDelete()
                    ->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'child_id')) {
                $table->dropConstrainedForeignId('child_id');
            }
        });
    }
};
