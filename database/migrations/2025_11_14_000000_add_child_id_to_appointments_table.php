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
            if (!Schema::hasColumn('appointments', 'child_id')) {
                $table->foreignId('child_id')
                    ->nullable()
                    ->constrained('children')
                    ->onDelete('set null')
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
                $table->dropForeignKeyForAppointments('child_id');
                $table->dropColumn('child_id');
            }
        });
    }
};
