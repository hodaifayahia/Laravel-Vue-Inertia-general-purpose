<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\ProviderAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ProviderAvailabilityController extends Controller
{
    /**
     * Display the availability management page
     */
    public function index()
    {
        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return redirect()->route('dashboard')
                ->with('error', 'You need to create a provider profile first.');
        }

        // Get availability for the next 3 months
        $startDate = now()->startOfMonth();
        $endDate = now()->addMonths(3)->endOfMonth();

        $availability = ProviderAvailability::where('provider_profile_id', $provider->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return Inertia::render('Provider/Availability/Index', [
            'provider' => $provider,
            'availability' => $availability,
            'defaultSchedule' => [
                'monday' => $provider->working_hours['monday'] ?? null,
                'tuesday' => $provider->working_hours['tuesday'] ?? null,
                'wednesday' => $provider->working_hours['wednesday'] ?? null,
                'thursday' => $provider->working_hours['thursday'] ?? null,
                'friday' => $provider->working_hours['friday'] ?? null,
                'saturday' => $provider->working_hours['saturday'] ?? null,
                'sunday' => $provider->working_hours['sunday'] ?? null,
            ],
        ]);
    }

    /**
     * Store or update availability for specific dates (Working Date Range)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return back()->withErrors(['message' => 'Provider profile not found.']);
        }

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = Carbon::parse($validated['to_date']);

        $created = 0;
        $updated = 0;
        $currentDate = $fromDate->copy();

        while ($currentDate <= $toDate) {
            // Get the day of week to check if provider works on this day
            $dayOfWeek = $currentDate->dayOfWeek;
            
            // Check if provider has a schedule for this day
            $schedule = $provider->schedules()->where('day_of_week', $dayOfWeek)
                ->where('is_available', true)
                ->first();
            
            if ($schedule) {
                // Only create availability if provider works on this day
                $availability = ProviderAvailability::updateOrCreate(
                    [
                        'provider_profile_id' => $provider->id,
                        'date' => $currentDate->format('Y-m-d'),
                    ],
                    [
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'is_available' => true,
                        'reason' => null,
                    ]
                );

                if ($availability->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $currentDate->addDay();
        }

        $message = "Successfully set availability for {$created} new working day(s)";
        if ($updated > 0) {
            $message .= " and updated {$updated} existing day(s)";
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk set availability for a date range
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_available' => 'required|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return back()->withErrors(['message' => 'Provider profile not found.']);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $daysOfWeek = $validated['days_of_week'];

        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            if (in_array($currentDate->dayOfWeek, $daysOfWeek)) {
                $dates[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        $created = 0;
        $updated = 0;

        foreach ($dates as $date) {
            $availability = ProviderAvailability::updateOrCreate(
                [
                    'provider_profile_id' => $provider->id,
                    'date' => $date,
                ],
                [
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'is_available' => $validated['is_available'],
                    'reason' => $validated['reason'] ?? null,
                ]
            );

            if ($availability->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $message = "Successfully set availability for {$created} new date(s)";
        if ($updated > 0) {
            $message .= " and updated {$updated} existing date(s)";
        }

        return back()->with('success', $message);
    }

    /**
     * Delete availability for specific dates or ID
     */
    public function destroy(Request $request, $id = null)
    {
        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return back()->withErrors(['message' => 'Provider profile not found.']);
        }

        // If ID is provided in URL, delete that specific record
        if ($id) {
            $deleted = ProviderAvailability::where('provider_profile_id', $provider->id)
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return back()->with('success', "Availability record deleted successfully");
            } else {
                return back()->withErrors(['message' => 'Record not found or already deleted']);
            }
        }

        // Otherwise, expect dates array in request
        $validated = $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'required|date',
        ]);

        $deleted = ProviderAvailability::where('provider_profile_id', $provider->id)
            ->whereIn('date', $validated['dates'])
            ->delete();

        return back()->with('success', "Removed availability for {$deleted} date(s)");
    }

    /**
     * Get availability for a specific month (API endpoint)
     */
    public function getMonthAvailability(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2024',
            'month' => 'required|integer|between:1,12',
        ]);

        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return response()->json(['error' => 'Provider profile not found'], 404);
        }

        $startDate = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $availability = ProviderAvailability::where('provider_profile_id', $provider->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        return response()->json([
            'availability' => $availability,
        ]);
    }

    /**
     * Store monthly availability pattern
     */
    public function storeMonthlyPattern(Request $request)
    {
        $validated = $request->validate([
            'months' => 'required|array|min:1',
            'months.*' => 'required|integer|between:1,12',
        ]);

        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return back()->withErrors(['message' => 'Provider profile not found.']);
        }

        // Store monthly pattern in provider profile (you may need to add a JSON column for this)
        // For now, we'll create availability entries for the first day of each selected month
        $year = now()->year;
        $created = 0;

        foreach ($validated['months'] as $month) {
            $date = Carbon::create($year, $month, 1)->format('Y-m-d');
            
            $availability = ProviderAvailability::updateOrCreate(
                [
                    'provider_profile_id' => $provider->id,
                    'date' => $date,
                ],
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'is_available' => true,
                    'reason' => 'Available - Monthly Pattern',
                ]
            );

            if ($availability->wasRecentlyCreated) {
                $created++;
            }
        }

        return back()->with('success', "Monthly availability pattern saved for {$created} month(s)");
    }

    /**
     * Store excluded dates
     */
    public function storeExcludedDates(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $provider = auth()->user()->providerProfile;

        if (!$provider) {
            return back()->withErrors(['message' => 'Provider profile not found.']);
        }

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = isset($validated['to_date']) && $validated['to_date'] 
            ? Carbon::parse($validated['to_date']) 
            : $fromDate->copy();

        $created = 0;
        $currentDate = $fromDate->copy();

        while ($currentDate <= $toDate) {
            $availability = ProviderAvailability::updateOrCreate(
                [
                    'provider_profile_id' => $provider->id,
                    'date' => $currentDate->format('Y-m-d'),
                ],
                [
                    'is_available' => false,
                    'reason' => $validated['reason'] ?? 'Excluded',
                    'start_time' => '00:00',
                    'end_time' => '00:00',
                ]
            );

            if ($availability->wasRecentlyCreated) {
                $created++;
            }

            $currentDate->addDay();
        }

        $dayCount = $toDate->diffInDays($fromDate) + 1;
        return back()->with('success', "Excluded {$dayCount} date(s) from your availability");
    }

}
