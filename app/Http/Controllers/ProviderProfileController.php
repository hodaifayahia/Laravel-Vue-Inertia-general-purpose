<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\Specialization;
use App\Models\Province;
use App\Models\City;
use App\Models\ProviderSchedule;
use App\Models\ProviderAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderProfileController extends Controller
{
    /**
     * Display the provider's complete configuration page.
     */
    public function configuration()
    {
        $user = auth()->user();

        if (!$user->hasPermissionTo('book-sys')) {
            abort(403, 'You do not have permission to be a provider.');
        }

        // Load profile with all relationships
        $profile = $user->providerProfile()->with([
            'specialization',
            'province',
            'city',
            'schedules' => function($query) {
                $query->orderBy('day_of_week');
            }
        ])->first();

        // Load all necessary data
        $specializations = Specialization::active()->orderBy('name')->get();
        $provinces = Province::orderBy('name_ar')->get();
        $cities = City::orderBy('name_ar')->get();

        // Load schedules (create default if none exist)
        $schedules = $profile ? $profile->schedules : collect();

        // Load availability records and transform them
        $availabilityRecords = $profile ? ProviderAvailability::where('provider_profile_id', $profile->id)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get() : collect();

        // Transform availability records to include end_date and is_range flags
        $availability = $availabilityRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'date' => $record->date,
                'end_date' => $record->date,
                'start_time' => $record->start_time,
                'end_time' => $record->end_time,
                'is_available' => $record->is_available,
                'is_range' => false,
                'reason' => $record->reason,
            ];
        });

        // Default schedule structure for the week
        $defaultSchedule = [];
        for ($day = 0; $day < 7; $day++) {
            $schedule = $schedules->firstWhere('day_of_week', $day);
            $defaultSchedule[$day] = $schedule ? [
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'is_available' => $schedule->is_available,
            ] : [
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_available' => false,
            ];
        }

        return Inertia::render('Dashboard/Bookings/Provider/Configuration', [
            'profile' => $profile,
            'specializations' => $specializations,
            'provinces' => $provinces,
            'cities' => $cities,
            'schedules' => $schedules,
            'availability' => $availability,
            'defaultSchedule' => $defaultSchedule,
        ]);
    }

    /**
     * Display the provider's profile configuration.
     */
    public function show()
    {
        $user = auth()->user();
        
        if (!$user->hasPermissionTo('book-sys')) {
            abort(403, 'You do not have permission to be a provider.');
        }

        $profile = $user->providerProfile()->with(['specialization', 'schedules' => function($query) {
            $query->orderBy('day_of_week');
        }])->first();

        $specializations = Specialization::active()->orderBy('name')->get();

        return Inertia::render('Dashboard/Bookings/Provider/Profile', [
            'profile' => $profile,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Create or update provider profile.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasPermissionTo('book-sys')) {
            abort(403, 'You do not have permission to be a provider.');
        }

        $validated = $request->validate([
            'specialization_id' => 'required|exists:specializations,id',
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable|exists:cities,id',
            'bio' => 'nullable|string|max:2000',
            'years_experience' => 'required|integer|min:0|max:100',
            'slot_duration' => 'required|integer|in:15,30,45,60',
            'is_available' => 'boolean',
            'title' => 'nullable|string|max:10',
            'license_number' => 'nullable|string|max:50',
            'qualifications' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'office_address' => 'nullable|string|max:500',
            'clinic_name' => 'nullable|string|max:100',
            'consultation_fee' => 'nullable|numeric|min:0|max:999999.99',
            'advance_booking_days' => 'nullable|integer|min:1|max:365',
            'services_offered' => 'nullable|string|max:2000',
            'education' => 'nullable|string|max:2000',
            'awards' => 'nullable|string|max:2000',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|string|max:1000',
        ]);

        $profile = $user->providerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()->back()->with('success', 'Provider profile updated successfully!');
    }

    /**
     * Get providers by specialization.
     */
    public function bySpecialization(Specialization $specialization)
    {
        $providers = ProviderProfile::where('specialization_id', $specialization->id)
            ->where('is_available', true)
            ->with(['user:id,name,email,photo', 'specialization', 'province', 'city'])
            ->get();

        return response()->json($providers);
    }

    /**
     * Get all providers (for admin).
     */
    public function index()
    {
        $this->authorize('manage bookings');

        $providers = ProviderProfile::with(['user', 'specialization'])
            ->withCount('appointments')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Dashboard/Bookings/Providers/Index', [
            'providers' => $providers,
        ]);
    }

    /**
     * Show provider details page.
     */
    public function details(ProviderProfile $provider)
    {
        $provider->load([
            'user:id,name,email,photo',
            'specialization',
            'province',
            'city',
            'schedules' => function($query) {
                $query->where('is_available', true)
                      ->orderBy('day_of_week');
            }
        ]);

        return Inertia::render('Dashboard/Bookings/Provider/Details', [
            'provider' => $provider,
        ]);
    }

    /**
     * Group availability records into date ranges for display
     */
    private function groupAvailabilityIntoRanges($records)
    {
        if ($records->isEmpty()) {
            return collect();
        }

        $ranges = [];
        $currentRange = null;

        foreach ($records as $record) {
            $currentDate = \Carbon\Carbon::parse($record->date);

            // Start a new range if:
            // 1. No current range
            // 2. Availability status changed
            // 3. Dates are not consecutive
            // 4. Time changed (for available dates)
            if (
                !$currentRange ||
                $currentRange['is_available'] !== $record->is_available ||
                $currentRange['end_date']->addDay()->ne($currentDate) ||
                ($record->is_available && (
                    $currentRange['start_time'] !== $record->start_time ||
                    $currentRange['end_time'] !== $record->end_time
                ))
            ) {
                // Save previous range
                if ($currentRange) {
                    $ranges[] = $currentRange;
                }

                // Start new range
                $currentRange = [
                    'id' => $record->id,
                    'start_date' => $currentDate->copy(),
                    'end_date' => $currentDate->copy(),
                    'is_available' => $record->is_available,
                    'start_time' => $record->start_time,
                    'end_time' => $record->end_time,
                    'reason' => $record->reason,
                    'record_ids' => [$record->id], // Track all IDs in this range
                ];
            } else {
                // Extend current range
                $currentRange['end_date'] = $currentDate->copy();
                $currentRange['record_ids'][] = $record->id;
            }
        }

        // Add the last range
        if ($currentRange) {
            $ranges[] = $currentRange;
        }

        return collect($ranges)->map(function ($range) {
            return [
                'id' => $range['id'], // Use first record ID for deletion
                'date' => $range['start_date']->format('Y-m-d'),
                'end_date' => $range['end_date']->format('Y-m-d'),
                'is_range' => $range['start_date']->ne($range['end_date']),
                'is_available' => $range['is_available'],
                'start_time' => $range['start_time'],
                'end_time' => $range['end_time'],
                'reason' => $range['reason'],
                'record_ids' => $range['record_ids'],
            ];
        });
    }
}
