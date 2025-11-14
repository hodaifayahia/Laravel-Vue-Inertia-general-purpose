<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderProfile extends Model
{
    protected $fillable = [
        'user_id',
        'specialization_id',
        'bio',
        'years_experience',
        'slot_duration',
        'is_available',
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
        'province_id',
        'city_id',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'years_experience' => 'integer',
        'slot_duration' => 'integer',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'total_patients' => 'integer',
        'consultation_fee' => 'decimal:2',
        'advance_booking_days' => 'integer',
        'social_links' => 'array',
    ];

    /**
     * Get the user that owns the provider profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specialization for this provider.
     */
    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * Get the province for this provider.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the city for this provider.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the schedules for this provider.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ProviderSchedule::class);
    }

    /**
     * Get the appointments for this provider.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the availability records for this provider.
     */
    public function availability(): HasMany
    {
        return $this->hasMany(ProviderAvailability::class);
    }

    /**
     * Scope a query to only include available providers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Get available dates for this provider between two dates.
     */
    public function getAvailableDatesBetween($startDate, $endDate)
    {
        return $this->availability()
            ->where('is_available', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Check if provider is available on a specific date.
     */
    public function isAvailableOn($date)
    {
        $availability = $this->availability()->where('date', $date)->first();
        
        if ($availability) {
            return $availability->is_available;
        }
        
        // Check default schedule for the day of week
        $dayOfWeek = date('w', strtotime($date));
        $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();
        
        return $schedule && $schedule->is_available;
    }

    /**
     * Get time slots for a specific date.
     */
    public function getTimeSlotsForDate($date)
    {
        // Check for specific availability override
        $availability = $this->availability()->where('date', $date)->first();
        
        if ($availability && !$availability->is_available) {
            return [];
        }
        
        // Get times from availability or default schedule
        if ($availability && $availability->start_time && $availability->end_time) {
            $startTime = $availability->start_time;
            $endTime = $availability->end_time;
        } else {
            // Use default schedule
            $dayOfWeek = date('w', strtotime($date));
            $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->where('is_available', true)->first();
            
            if (!$schedule) {
                return [];
            }
            
            $startTime = $schedule->start_time;
            $endTime = $schedule->end_time;
        }
        
        // Generate time slots
        $slots = [];
        $current = strtotime($startTime);
        $end = strtotime($endTime);
        $slotDuration = $this->slot_duration * 60; // Convert to seconds
        
        while ($current < $end) {
            $slotStart = date('H:i:s', $current);
            $slotEnd = date('H:i:s', $current + $slotDuration);
            
            // Check if slot is already booked
            $isBooked = $this->appointments()
                ->where('appointment_date', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($current, $slotDuration) {
                    $slotStart = date('H:i:s', $current);
                    $slotEnd = date('H:i:s', $current + $slotDuration);
                    
                    $query->where(function ($q) use ($slotStart, $slotEnd) {
                        // Check for overlapping time ranges
                        $q->where('start_time', '<', $slotEnd)
                          ->where('end_time', '>', $slotStart);
                    });
                })
                ->exists();
            
            $slots[] = [
                'start_time' => date('H:i', $current),
                'end_time' => date('H:i', $current + $slotDuration),
                'is_available' => !$isBooked,
            ];
            
            $current += $slotDuration;
        }
        
        return $slots;
    }
}
