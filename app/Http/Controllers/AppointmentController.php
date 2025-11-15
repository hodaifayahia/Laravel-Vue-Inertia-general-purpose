<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    /**
     * Display appointments for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasPermissionTo('manage bookings');
        $isProvider = $user->hasPermissionTo('book-sys') && $user->providerProfile;
        $isPatient = !$isProvider && !$isAdmin;

        $query = Appointment::query();
        
        // Apply filtering based on user role
        if ($isAdmin) {
            // Admin can see all appointments
            $query->with([
                'user:id,name,email,avatar',
                'child:id,name,date_of_birth,gender',
                'providerProfile.user:id,name,email,avatar',
                'providerProfile.specialization',
                'providerProfile.city:id,name_ar,name_en'
            ]);
        } elseif ($isProvider) {
            // Provider sees only their appointments
            $query->where('provider_profile_id', $user->providerProfile->id)
                ->with(['user:id,name,email,avatar', 'child:id,name,date_of_birth,gender']);
        } else {
            // Patient sees only their appointments
            $query->where('user_id', $user->id)
                ->with(['providerProfile.user:id,name,email,avatar', 'providerProfile.specialization', 'child:id,name,date_of_birth,gender']);
        }

        // Apply status filter if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply date filter if provided
        if ($request->has('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }

        // Apply specialization filter (for admin only)
        if ($isAdmin && $request->has('specialization') && $request->specialization !== 'all') {
            $query->whereHas('providerProfile.specialization', function ($q) use ($request) {
                $q->where('slug', $request->specialization);
            });
        }

        // Apply city filter (for admin only)
        if ($isAdmin && $request->has('city') && $request->city !== 'all') {
            $query->whereHas('providerProfile.city', function ($q) use ($request) {
                $q->where('id', $request->city);
            });
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm, $isAdmin, $isProvider) {
                if ($isAdmin) {
                    // Search in patient name, provider name, notes
                    $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('providerProfile.user', function ($providerQuery) use ($searchTerm) {
                        $providerQuery->where('name', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhere('notes', 'like', '%' . $searchTerm . '%');
                } elseif ($isProvider) {
                    // Provider searches in patient name, email, notes
                    $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhere('notes', 'like', '%' . $searchTerm . '%');
                } else {
                    // Patient searches in provider name, specialization, notes
                    $q->whereHas('providerProfile.user', function ($providerQuery) use ($searchTerm) {
                        $providerQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('providerProfile.specialization', function ($specQuery) use ($searchTerm) {
                        $specQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhere('notes', 'like', '%' . $searchTerm . '%');
                }
            });
        }

        $appointments = $query
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        // Get filter options for admin view
        $specializations = [];
        $cities = [];
        $provinces = [];
        $allCities = [];
        
        if ($isAdmin) {
            $specializations = \App\Models\Specialization::where('is_active', true)
                ->pluck('name', 'slug')
                ->toArray();
            $cities = \App\Models\City::orderBy('name_ar')
                ->pluck('name_ar', 'id')
                ->toArray();
        }
        
        // For any user who can book (patient, admin, provider), provide provinces and cities for booking modal
        // This allows all users to use the booking modal
        $provinces = \App\Models\Province::orderBy('name_ar')->get();
        $allCities = \App\Models\City::orderBy('name_ar')->get();

        // Calculate statistics
        $statsQuery = Appointment::query();
        
        // Apply same role-based filtering for statistics
        if ($isAdmin) {
            // Admin sees all appointments
        } elseif ($isProvider) {
            // Provider sees only their appointments
            $statsQuery->where('provider_profile_id', $user->providerProfile->id);
        } else {
            // Patient sees only their appointments
            $statsQuery->where('user_id', $user->id);
        }

        $statistics = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $statsQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $statsQuery)->where('status', 'cancelled')->count(),
            'no_show' => (clone $statsQuery)->where('status', 'no_show')->count(),
            'today' => (clone $statsQuery)->whereDate('appointment_date', today())->count(),
            'this_week' => (clone $statsQuery)->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $statsQuery)->whereYear('appointment_date', now()->year)->whereMonth('appointment_date', now()->month)->count(),
        ];

        return Inertia::render('Dashboard/Bookings/Appointments/Index', [
            'appointments' => $appointments,
            'isProvider' => $isProvider,
            'isAdmin' => $isAdmin,
            'isPatient' => $isPatient,
            'specializations' => $specializations,
            'cities' => $cities,
            'provinces' => $provinces,
            'allCities' => $allCities,
            'statistics' => $statistics,
            'filters' => [
                'status' => $request->query('status', 'all'),
                'date_from' => $request->query('date_from', ''),
                'date_to' => $request->query('date_to', ''),
                'specialization' => $request->query('specialization', 'all'),
                'city' => $request->query('city', 'all'),
                'search' => $request->query('search', ''),
            ]
        ]);
    }

    /**
     * Show the booking form.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('can-book')) {
            abort(403, 'You do not have permission to book appointments.');
        }

        $provinces = \App\Models\Province::orderBy('name_ar')->get();
        $cities = \App\Models\City::orderBy('name_ar')->get();

        return Inertia::render('Dashboard/Bookings/BookEnhanced', [
            'provinces' => $provinces,
            'cities' => $cities,
            'success' => session('success', false),
        ]);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('can-book')) {
            abort(403, 'You do not have permission to book appointments.');
        }

        \Log::info('Appointment booking request:', $request->all());

        $validated = $request->validate([
            'child_id' => 'nullable|exists:children,id',
            'provider_profile_id' => 'required|exists:provider_profiles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
        ]);

        \Log::info('Validated data:', $validated);

        // Check if provider is available
        $provider = ProviderProfile::findOrFail($validated['provider_profile_id']);
        
        if (!$provider->is_available) {
            return response()->json([
                'error' => 'This provider is currently unavailable.',
                'message' => 'This provider is currently unavailable.'
            ], 422);
        }

        // Check for conflicting appointments
        $date = \Carbon\Carbon::parse($validated['appointment_date']);
        $dayOfWeek = $date->dayOfWeek;

        $existingAppointment = Appointment::where('provider_profile_id', $provider->id)
            ->where('appointment_date', $validated['appointment_date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($existingAppointment) {
            return response()->json([
                'error' => 'This time slot is already booked.',
                'message' => 'This time slot is already booked.'
            ], 422);
        }

        $appointmentData = [
            'provider_profile_id' => $validated['provider_profile_id'],
            'user_id' => auth()->id(),
            'child_id' => $validated['child_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ];

        \Log::info('Creating appointment with:', $appointmentData);

        $appointment = Appointment::create($appointmentData);

        \Log::info('Appointment created:', $appointment->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $user = auth()->user();
        $isAdmin = $user->hasPermissionTo('manage bookings');

        // Only provider or admin can update status
        if (!$isAdmin && (!$user->providerProfile || $appointment->provider_profile_id !== $user->providerProfile->id)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed,no_show',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Appointment status updated successfully!');
    }

    /**
     * Cancel appointment (by patient).
     */
    public function cancel(Appointment $appointment)
    {
        $user = auth()->user();
        $isAdmin = $user->hasPermissionTo('manage bookings');

        // Only the patient or admin can cancel appointment
        if (!$isAdmin && $appointment->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Can only cancel if status is pending or confirmed
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot cancel this appointment.'
            ]);
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Appointment cancelled successfully!');
    }

    /**
     * Delete appointment (by admin only).
     */
    public function destroy(Appointment $appointment)
    {
        $user = auth()->user();

        // Only admin can delete appointments
        if (!$user->hasPermissionTo('manage bookings')) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->delete();

        return redirect()->back()->with('success', 'Appointment deleted successfully!');
    }

    /**
     * Show appointment details.
     */
    public function show(Appointment $appointment)
    {
        $user = auth()->user();
        $isAdmin = $user->hasPermissionTo('manage bookings');
        $isProvider = $user->hasPermissionTo('book-sys') && $user->providerProfile;

        // User can only view their own appointments or appointments they are providing
        if ($appointment->user_id !== $user->id &&
            (!$isProvider || $appointment->provider_profile_id !== $user->providerProfile->id) &&
            !$isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->load([
            'user:id,name,email,avatar',
            'child:id,name,date_of_birth,gender,medical_notes',
            'providerProfile' => function ($query) {
                $query->with('user:id,name,email,avatar,phone')
                      ->with('specialization')
                      ->select('id', 'user_id', 'specialization_id', 'years_experience', 'rating', 'total_reviews', 'consultation_fee', 'phone');
            }
        ]);

        return Inertia::render('Dashboard/Bookings/Appointments/Show', [
            'appointment' => $appointment,
            'isProvider' => $isProvider && $appointment->provider_profile_id === $user->providerProfile->id,
            'isAdmin' => $isAdmin,
        ]);
    }
}
