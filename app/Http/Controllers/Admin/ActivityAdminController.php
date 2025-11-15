<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityItem;
use App\Models\ActivityAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityAdminController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(): Response
    {
        $activities = Activity::withCount(['activityItems', 'activityAttempts'])
            ->ordered()
            ->get();

        return Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Activities/Create');
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'min_age' => 'nullable|integer|min:3',
            'max_age' => 'nullable|integer|min:3',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $activity = Activity::create($validated);

        return redirect()->route('admin.activities.edit', $activity)
            ->with('success', 'Activity created successfully!');
    }

    /**
     * Show the form for editing the specified activity.
     */
    public function edit(Activity $activity): Response
    {
        $activity->load(['activityItems' => function ($query) {
            $query->ordered();
        }]);

        $itemTypes = [
            'emoji_choice' => 'Emoji Choice (Self-Report)',
            'text_copy_timed' => 'Timed Typing Test',
            'shape_copy_canvas' => 'Shape Drawing',
            'trace_the_path' => 'Path Tracing',
            'dot_to_dot' => 'Connect the Dots',
            'find_the_different_one' => 'Find the Different One',
            'simple_puzzle_drag' => 'Simple Puzzle',
            'whats_missing' => 'What\'s Missing',
            'listen_and_type' => 'Listen and Type',
            'unscramble_the_word' => 'Unscramble the Word',
        ];

        return Inertia::render('Admin/Activities/Edit', [
            'activity' => $activity,
            'itemTypes' => $itemTypes,
        ]);
    }

    /**
     * Update the specified activity.
     */
    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'min_age' => 'nullable|integer|min:3',
            'max_age' => 'nullable|integer|min:3',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $activity->update($validated);

        return redirect()->back()->with('success', 'Activity updated successfully!');
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('admin.activities.index')
            ->with('success', 'Activity deleted successfully!');
    }

    /**
     * Store a new activity item.
     */
    public function storeItem(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:emoji_choice,text_copy_timed,shape_copy_canvas,trace_the_path,dot_to_dot,find_the_different_one,simple_puzzle_drag,whats_missing,listen_and_type,unscramble_the_word',
            'prompt_text' => 'required|string',
            'content_data' => 'nullable|array',
            'options' => 'nullable|array',
            'max_points' => 'required|integer|min:0',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'order' => 'integer',
        ]);

        $validated['activity_id'] = $activity->id;

        ActivityItem::create($validated);

        return redirect()->back()->with('success', 'Activity item added successfully!');
    }

    /**
     * Update the specified activity item.
     */
    public function updateItem(Request $request, ActivityItem $item)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:emoji_choice,text_copy_timed,shape_copy_canvas,trace_the_path,dot_to_dot,find_the_different_one,simple_puzzle_drag,whats_missing,listen_and_type,unscramble_the_word',
            'prompt_text' => 'required|string',
            'content_data' => 'nullable|array',
            'options' => 'nullable|array',
            'max_points' => 'required|integer|min:0',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'order' => 'integer',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Activity item updated successfully!');
    }

    /**
     * Remove the specified activity item.
     */
    public function destroyItem(ActivityItem $item)
    {
        $activityId = $item->activity_id;
        $item->delete();

        return redirect()->route('admin.activities.edit', $activityId)
            ->with('success', 'Activity item deleted successfully!');
    }

    /**
     * Show activity attempts/results.
     */
    public function attempts(): Response
    {
        $attempts = ActivityAttempt::with(['activity', 'user', 'child'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Activities/Attempts', [
            'attempts' => $attempts,
        ]);
    }

    /**
     * Show detailed results for an attempt.
     */
    public function showAttempt(ActivityAttempt $attempt): Response
    {
        $attempt->load([
            'activity.activityItems',
            'results.activityItem',
            'user',
            'child',
        ]);

        return Inertia::render('Admin/Activities/AttemptDetail', [
            'attempt' => $attempt,
        ]);
    }

    /**
     * Update admin notes for an attempt.
     */
    public function updateAttemptNotes(Request $request, ActivityAttempt $attempt)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
            'consultation_needed' => 'boolean',
        ]);

        $attempt->update($validated);

        return redirect()->back()->with('success', 'Notes updated successfully!');
    }
}
