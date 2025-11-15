<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\City;
use App\Models\Province;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PublicApiController extends Controller
{
    /**
     * Get public list of doctors/providers with their details
     * 
     * @return JsonResponse
     */
    public function getDoctors(): JsonResponse
    {
        try {
            // Fetch doctors with related data
            $doctors = User::role('doctor')
                ->with([
                    'providerProfile.specialization',
                    'providerProfile.city.province'
                ])
                ->whereHas('providerProfile')
                ->get()
                ->map(function ($user) {
                    $profile = $user->providerProfile;
                    
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'title' => $profile->title ?? 'Dr.',
                        'specialty' => $profile->specialization?->name ?? 'Dysgraphia Specialist',
                        'bio' => $profile->bio,
                        'photo' => $user->avatar ?? null,
                        'city_name' => $profile->city?->name_en ?? 'N/A',
                        'province_name' => $profile->city?->province?->name_en ?? 'N/A',
                        'years_experience' => $profile->years_experience ?? 0,
                        'consultation_fee' => $profile->consultation_fee,
                        'latitude' => $profile->latitude,
                        'longitude' => $profile->longitude,
                    ];
                });

            return response()->json([
                'success' => true,
                'doctors' => $doctors,
                'total' => $doctors->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch doctors',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get platform statistics
     * 
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_doctors' => User::role('doctor')
                    ->whereHas('providerProfile')
                    ->count(),
                
                'total_cities' => City::whereHas('providerProfiles')->distinct()->count(),
                
                'total_provinces' => Province::whereHas('cities.providerProfiles')->distinct()->count(),
                
                'total_appointments' => Appointment::whereIn('status', ['confirmed', 'completed'])
                    ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'total_doctors' => 0,
                'total_cities' => 0,
                'total_provinces' => 0,
                'total_appointments' => 0,
            ]);
        }
    }

    /**
     * Get doctor details by ID
     * 
     * @param int $doctorId
     * @return JsonResponse
     */
    public function getDoctorDetail($doctorId): JsonResponse
    {
        try {
            $doctor = User::role('doctor')
                ->with([
                    'providerProfile.specialization',
                    'providerProfile.city.province'
                ])
                ->whereHas('providerProfile')
                ->find($doctorId);

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }

            $profile = $doctor->providerProfile;
            
            return response()->json([
                'success' => true,
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'title' => $profile->title ?? 'Dr.',
                    'specialty' => $profile->specialization?->name ?? 'Dysgraphia Specialist',
                    'bio' => $profile->bio,
                    'photo' => $doctor->avatar ?? null,
                    'city_name' => $profile->city?->name_en ?? 'N/A',
                    'province_name' => $profile->city?->province?->name_en ?? 'N/A',
                    'years_experience' => $profile->years_experience ?? 0,
                    'consultation_fee' => $profile->consultation_fee,
                    'latitude' => $profile->latitude,
                    'longitude' => $profile->longitude,
                    'email' => $doctor->email,
                    'phone' => $doctor->phone,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch doctor details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available dates for a doctor
     * 
     * @param int $doctorId
     * @return JsonResponse
     */
    public function getAvailableDates($doctorId): JsonResponse
    {
        try {
            $doctor = User::role('doctor')->find($doctorId);
            
            if (!$doctor || !$doctor->providerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }

            $profile = $doctor->providerProfile;
            
            // Get available dates from the next 7 to 30 days
            $availableDates = [];
            $startDate = now()->addDay();
            $endDate = now()->addDays(30);

            for ($date = $startDate; $date <= $endDate; $date = $date->addDay()) {
                $dayOfWeek = $date->dayOfWeek;
                
                // Check if available on this date
                if ($profile->isAvailableOn($date->toDateString())) {
                    $availableDates[] = [
                        'date' => $date->toDateString(),
                        'day' => $date->format('l'),
                        'display' => $date->format('M d, Y')
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'dates' => $availableDates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available dates',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available time slots for a doctor on a specific date
     * 
     * @param int $doctorId
     * @param string $date
     * @return JsonResponse
     */
    public function getAvailableSlots($doctorId, $date): JsonResponse
    {
        try {
            $doctor = User::role('doctor')->find($doctorId);
            
            if (!$doctor || !$doctor->providerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }

            $profile = $doctor->providerProfile;
            $slots = $profile->getTimeSlotsForDate($date);

            return response()->json([
                'success' => true,
                'date' => $date,
                'slots' => $slots
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available slots',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

