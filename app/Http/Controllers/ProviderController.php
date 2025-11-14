<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProviderController extends Controller
{
    /**
     * Get providers filtered by city and specialization.
     */
    public function getProvidersByCityAndSpecialization(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'specialization' => 'nullable|string',
        ]);

        $query = ProviderProfile::with(['user', 'specialization', 'city', 'province'])
            ->where('city_id', $request->city_id)
            ->where('is_available', true);

        // Filter by specialization slug/name
        if ($request->specialization === 'dysgraphia') {
            // Find the Dysgraphia specialization and filter by its ID
            $dysgraphiaSpec = Specialization::where('slug', 'dysgraphia')->first();
            if ($dysgraphiaSpec) {
                $query->where('specialization_id', $dysgraphiaSpec->id);
            }
        }

        $providers = $query->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'user' => [
                    'id' => $profile->user->id,
                    'name' => $profile->user->name,
                    'email' => $profile->user->email,
                    'avatar' => $profile->user->avatar ?? null,
                ],
                'title' => $profile->title ?? 'Dr.',
                'specialization' => $profile->specialization->name ?? 'N/A',
                'bio' => $profile->bio,
                'years_experience' => $profile->years_experience,
                'slot_duration' => $profile->slot_duration,
                'consultation_fee' => $profile->consultation_fee,
                'rating' => $profile->rating,
                'total_reviews' => $profile->total_reviews,
                'total_patients' => $profile->total_patients ?? 0,
                'qualifications' => $profile->qualifications,
                'languages' => $profile->languages,
                'city' => [
                    'id' => $profile->city->id,
                    'name_ar' => $profile->city->name_ar,
                    'name_en' => $profile->city->name_en,
                ],
                'province' => [
                    'id' => $profile->province->id,
                    'name_ar' => $profile->province->name_ar,
                    'name_en' => $profile->province->name_en,
                ],
            ];
        });

        return response()->json($providers);
    }

    /**
     * Get available dates for a provider in a specific month.
     */
    public function getAvailableDates(Request $request, $providerId)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $provider = ProviderProfile::with(['schedules', 'availability', 'appointments'])
            ->findOrFail($providerId);

        $month = $request->month;
        $year = $request->year;
        
        // Get first and last day of the month
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        
        $availableDates = [];
        
        // Iterate through each day of the month
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->dayOfWeek;
            
            // Check if there's a specific availability override for this date
            $availabilityOverride = $provider->availability()
                ->where('date', $dateString)
                ->first();
            
            $hasSlots = false;
            
            if ($availabilityOverride) {
                // If there's an override, check if it's available
                if ($availabilityOverride->is_available) {
                    // Check if there are actual time slots available
                    $slots = $provider->getTimeSlotsForDate($dateString);
                    $hasSlots = count(array_filter($slots, fn($slot) => $slot['is_available'])) > 0;
                }
            } else {
                // Check default schedule for this day of week
                $schedule = $provider->schedules()
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_available', true)
                    ->first();
                
                if ($schedule) {
                    // Check if there are actual time slots available
                    $slots = $provider->getTimeSlotsForDate($dateString);
                    $hasSlots = count(array_filter($slots, fn($slot) => $slot['is_available'])) > 0;
                }
            }
            
            $availableDates[] = [
                'date' => $dateString,
                'has_slots' => $hasSlots,
            ];
            
            $currentDate->addDay();
        }
        
        return response()->json([
            'dates' => $availableDates,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(Request $request, $providerId)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $provider = ProviderProfile::with(['schedules', 'availability', 'appointments'])
            ->findOrFail($providerId);

        $date = $request->date;
        
        // Get time slots using the model's method
        $slots = $provider->getTimeSlotsForDate($date);
        
        // Filter only available slots
        $availableSlots = array_filter($slots, fn($slot) => $slot['is_available']);
        
        // Re-index the array
        $availableSlots = array_values($availableSlots);
        
        return response()->json([
            'date' => $date,
            'slots' => $availableSlots,
            'total_slots' => count($slots),
            'available_slots' => count($availableSlots),
        ]);
    }
}
