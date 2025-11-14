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
        Schema::table('provider_profiles', function (Blueprint $table) {
            // Professional details
            $table->string('title')->nullable()->after('user_id'); // Dr., Prof., etc.
            $table->string('license_number')->nullable()->after('title');
            $table->text('qualifications')->nullable()->after('bio'); // Degrees, certifications
            $table->text('languages')->nullable()->after('qualifications'); // JSON array of languages
            
            // Contact information
            $table->string('phone')->nullable()->after('languages');
            $table->string('office_address')->nullable()->after('phone');
            $table->string('clinic_name')->nullable()->after('office_address');
            
            // Ratings and reviews
            $table->decimal('rating', 3, 2)->default(0.00)->after('clinic_name'); // Average rating
            $table->integer('total_reviews')->default(0)->after('rating');
            $table->integer('total_patients')->default(0)->after('total_reviews');
            
            // Consultation details
            $table->decimal('consultation_fee', 8, 2)->nullable()->after('slot_duration');
            $table->integer('advance_booking_days')->default(30)->after('consultation_fee'); // How far in advance can book
            $table->text('services_offered')->nullable()->after('advance_booking_days'); // JSON array
            
            // Additional info
            $table->text('education')->nullable()->after('services_offered'); // Education history
            $table->text('awards')->nullable()->after('education'); // Awards and recognitions
            $table->string('website')->nullable()->after('awards');
            $table->json('social_links')->nullable()->after('website'); // LinkedIn, Twitter, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'license_number',
                'qualifications',
                'languages',
                'phone',
                'office_address',
                'clinic_name',
                'rating',
                'total_reviews',
                'total_patients',
                'consultation_fee',
                'advance_booking_days',
                'services_offered',
                'education',
                'awards',
                'website',
                'social_links',
            ]);
        });
    }
};
