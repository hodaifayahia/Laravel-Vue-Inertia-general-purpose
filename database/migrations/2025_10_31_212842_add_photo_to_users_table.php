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
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('email'); // Profile photo path
            $table->string('phone')->nullable()->after('photo'); // Phone number
            $table->text('bio')->nullable()->after('phone'); // User bio
            $table->date('date_of_birth')->nullable()->after('bio'); // Date of birth
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'phone', 'bio', 'date_of_birth', 'gender']);
        });
    }
};
