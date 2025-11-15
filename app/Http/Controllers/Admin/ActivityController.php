<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index()
    {
        $activities = Activity::with('activityItems')
            ->withCount('activityItems', 'activityAttempts', 'completedAttempts')
            ->orderBy('order')
            ->paginate(15);

        return Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create()
    {
        return Inertia::render('Admin/Activities/Create', [
            'difficultyLevels' => ['beginner', 'intermediate', 'advanced'],
        ]);
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration_minutes' => 'required|integer|min:1|max:300',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'min_age' => 'nullable|integer|min:1|max:100',
            'max_age' => 'nullable|integer|min:1|max:100',
        ]);

        $activity = Activity::create($validated);

        return redirect()->route('admin.activities.edit', $activity->id)
            ->with('success', 'Activity created successfully!');
    }

    /**
     * Show the form for editing the specified activity.
     */
    public function edit(Activity $activity)
    {
        $activity->load('activityItems');

        return Inertia::render('Admin/Activities/Edit', [
            'activity' => $activity,
            'difficultyLevels' => ['beginner', 'intermediate', 'advanced'],
            'itemTypes' => [
                'emoji_choice' => 'Emoji Choice',
                'text_copy_timed' => 'Text Copy (Timed)',
                'shape_copy_canvas' => 'Shape Copy Canvas',
                'trace_the_path' => 'Trace the Path',
                'dot_to_dot' => 'Dot to Dot',
                'find_the_different_one' => 'Find the Different One',
                'simple_puzzle_drag' => 'Simple Puzzle (Drag)',
                'whats_missing' => "What's Missing",
                'listen_and_type' => 'Listen and Type',
                'unscramble_the_word' => 'Unscramble the Word',
            ],
        ]);
    }

    /**
     * Update the specified activity in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration_minutes' => 'required|integer|min:1|max:300',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'min_age' => 'nullable|integer|min:1|max:100',
            'max_age' => 'nullable|integer|min:1|max:100',
        ]);

        $activity->update($validated);

        return back()->with('success', 'Activity updated successfully!');
    }

    /**
     * Add a new item to an activity.
     */
    public function storeItem(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'item_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'max_points' => 'required|integer|min:1|max:1000',
            'time_limit_seconds' => 'nullable|integer|min:5|max:3600',
            'options' => 'nullable|array',
        ]);

        $validated['order'] = $validated['order'] ?? ($activity->activityItems()->count() + 1);

        $item = $activity->activityItems()->create($validated);

        return back()->with('success', 'Task added successfully!');
    }

    /**
     * Update an activity item.
     */
    public function updateItem(Request $request, Activity $activity, ActivityItem $item)
    {
        $validated = $request->validate([
            'item_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'max_points' => 'required|integer|min:1|max:1000',
            'time_limit_seconds' => 'nullable|integer|min:5|max:3600',
            'options' => 'nullable|array',
        ]);

        $item->update($validated);

        return back()->with('success', 'Task updated successfully!');
    }

    /**
     * Delete an activity item.
     */
    public function destroyItem(Request $request, Activity $activity, ActivityItem $item)
    {
        $item->delete();

        return back()->with('success', 'Task deleted successfully!');
    }

    /**
     * Delete the specified activity.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('admin.activities.index')
            ->with('success', 'Activity deleted successfully!');
    }
}
