<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\ActivityItem;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    /**
     * Get all active activities (public access).
     */
    public function index()
    {
        $activities = Activity::active()
            ->ordered()
            ->withCount('activityItems')
            ->get();

        return response()->json($activities);
    }

    /**
     * Get a single activity details (public access).
     */
    public function show(Activity $activity)
    {
        if (!$activity->is_active) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        $activity->load('activityItems');

        return response()->json($activity);
    }

    /**
     * Start a new activity attempt (guest or authenticated).
     */
    public function start(Request $request, Activity $activity)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'nullable|exists:children,id',
            'guest_session_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate guest session ID if not authenticated
        $guestSessionId = null;
        if (!auth()->check()) {
            $guestSessionId = $request->guest_session_id ?: Str::uuid()->toString();
        }

        $attempt = ActivityAttempt::create([
            'user_id' => auth()->id(),
            'guest_session_id' => $guestSessionId,
            'activity_id' => $activity->id,
            'child_id' => $request->child_id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json($attempt->load('activity'));
    }

    /**
     * Get activity items for an attempt.
     */
    public function getItems(ActivityAttempt $attempt)
    {
        // Verify access
        if (!$this->canAccessAttempt($attempt)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $items = $attempt->activity->activityItems()->ordered()->get();

        return response()->json($items);
    }

    /**
     * Submit a single result for an activity item.
     */
    public function submitResult(Request $request, ActivityAttempt $attempt)
    {
        // Verify access
        if (!$this->canAccessAttempt($attempt)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'activity_item_id' => 'required|exists:activity_items,id',
            'result_data' => 'required|array',
            'time_taken_ms' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = ActivityItem::findOrFail($request->activity_item_id);

        // Calculate points based on item type
        $points = $this->calculatePoints($item, $request->result_data);
        $isCorrect = $this->checkCorrectness($item, $request->result_data);

        $result = Result::create([
            'activity_attempt_id' => $attempt->id,
            'activity_item_id' => $item->id,
            'result_data' => $request->result_data,
            'points_awarded' => $points,
            'time_taken_ms' => $request->time_taken_ms,
            'is_correct' => $isCorrect,
        ]);

        return response()->json($result);
    }

    /**
     * Complete the activity attempt and calculate final score.
     */
    public function complete(Request $request, ActivityAttempt $attempt)
    {
        // Verify access
        if (!$this->canAccessAttempt($attempt)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Calculate final score
        $finalScore = $attempt->results()->sum('points_awarded');
        $totalPossiblePoints = $attempt->activity->activityItems()->sum('max_points');
        
        // Determine if consultation is needed (score below 70%)
        $consultationNeeded = $totalPossiblePoints > 0 
            ? ($finalScore / $totalPossiblePoints) < 0.7 
            : false;

        $attempt->markAsCompleted($finalScore, $consultationNeeded);

        // IMPORTANT: Never return the score to frontend for child-friendly experience
        return response()->json([
            'status' => 'completed',
            'message' => 'Great job! You completed all the activities! 🌟',
        ]);
    }

    /**
     * Calculate points for a result based on item type.
     */
    private function calculatePoints(ActivityItem $item, array $resultData): int
    {
        return match($item->item_type) {
            'emoji_choice' => $this->calculateEmojiPoints($item, $resultData),
            'text_copy_timed' => $this->calculateTypingPoints($item, $resultData),
            'shape_copy_canvas' => $this->calculateDrawingPoints($item, $resultData),
            'trace_the_path' => $this->calculateTracingPoints($item, $resultData),
            'dot_to_dot' => $this->calculateDotToDotPoints($item, $resultData),
            'find_the_different_one' => $this->calculateFindDifferentPoints($item, $resultData),
            'simple_puzzle_drag' => $this->calculatePuzzlePoints($item, $resultData),
            'whats_missing' => $this->calculateWhatsMissingPoints($item, $resultData),
            'listen_and_type' => $this->calculateListenTypePoints($item, $resultData),
            'unscramble_the_word' => $this->calculateUnscramblePoints($item, $resultData),
            default => 0,
        };
    }

    /**
     * Check if result is correct (for applicable item types).
     */
    private function checkCorrectness(ActivityItem $item, array $resultData): ?bool
    {
        return match($item->item_type) {
            'emoji_choice' => isset($item->options['correct']) && $resultData['selected'] === $item->options['correct'],
            'find_the_different_one' => isset($item->options['correct_index']) && $resultData['selected_index'] === $item->options['correct_index'],
            'listen_and_type' => isset($item->options['correct_answer']) && strtolower(trim($resultData['typed_text'] ?? '')) === strtolower($item->options['correct_answer']),
            'unscramble_the_word' => isset($item->options['correct_word']) && strtolower(trim($resultData['word'] ?? '')) === strtolower($item->options['correct_word']),
            default => null,
        };
    }

    // Scoring methods for each game type
    private function calculateEmojiPoints(ActivityItem $item, array $data): int
    {
        // Simple point award for emoji selection (self-report, no wrong answer)
        return $item->max_points;
    }

    private function calculateTypingPoints(ActivityItem $item, array $data): int
    {
        $errors = $data['errors'] ?? 0;
        $maxPoints = $item->max_points;
        
        // Deduct points for errors
        return max(0, $maxPoints - ($errors * 5));
    }

    private function calculateDrawingPoints(ActivityItem $item, array $data): int
    {
        // For drawing, we give full points (manual review by consultant)
        // Time could be a factor
        $timeMs = $data['time_ms'] ?? 0;
        $timeLimit = ($item->time_limit_seconds ?? 60) * 1000;
        
        if ($timeMs > $timeLimit) {
            return (int)($item->max_points * 0.8); // 80% if over time
        }
        
        return $item->max_points;
    }

    private function calculateTracingPoints(ActivityItem $item, array $data): int
    {
        $accuracy = $data['accuracy_percent'] ?? 0;
        
        // Award points based on accuracy
        return (int)(($accuracy / 100) * $item->max_points);
    }

    private function calculateDotToDotPoints(ActivityItem $item, array $data): int
    {
        $wrongTaps = $data['wrong_order_taps'] ?? 0;
        $maxPoints = $item->max_points;
        
        // Deduct points for wrong taps
        return max(0, $maxPoints - ($wrongTaps * 10));
    }

    private function calculateFindDifferentPoints(ActivityItem $item, array $data): int
    {
        $isCorrect = $this->checkCorrectness($item, $data);
        return $isCorrect ? $item->max_points : 0;
    }

    private function calculatePuzzlePoints(ActivityItem $item, array $data): int
    {
        $timeMs = $data['time_ms'] ?? 0;
        $timeLimit = ($item->time_limit_seconds ?? 120) * 1000;
        
        // Base points for completion
        $points = $item->max_points;
        
        // Bonus for speed
        if ($timeMs < $timeLimit / 2) {
            $points = (int)($points * 1.2); // 20% bonus
        }
        
        return min($points, $item->max_points);
    }

    private function calculateWhatsMissingPoints(ActivityItem $item, array $data): int
    {
        // Check if tap was in correct area (needs coordinates)
        $correctArea = $item->options['correct_area'] ?? null;
        $tappedX = $data['x'] ?? 0;
        $tappedY = $data['y'] ?? 0;
        
        if ($correctArea) {
            $distance = sqrt(
                pow($tappedX - $correctArea['x'], 2) + 
                pow($tappedY - $correctArea['y'], 2)
            );
            
            $threshold = $correctArea['threshold'] ?? 50;
            
            if ($distance <= $threshold) {
                return $item->max_points;
            }
        }
        
        return (int)($item->max_points * 0.5); // Partial credit
    }

    private function calculateListenTypePoints(ActivityItem $item, array $data): int
    {
        $isCorrect = $this->checkCorrectness($item, $data);
        return $isCorrect ? $item->max_points : 0;
    }

    private function calculateUnscramblePoints(ActivityItem $item, array $data): int
    {
        $isCorrect = $this->checkCorrectness($item, $data);
        $timeMs = $data['time_ms'] ?? 0;
        $timeLimit = ($item->time_limit_seconds ?? 60) * 1000;
        
        if (!$isCorrect) {
            return 0;
        }
        
        // Bonus for speed
        if ($timeMs < $timeLimit / 2) {
            return (int)($item->max_points * 1.1);
        }
        
        return $item->max_points;
    }

    /**
     * Check if current user/guest can access this attempt.
     */
    private function canAccessAttempt(ActivityAttempt $attempt): bool
    {
        if (auth()->check() && $attempt->user_id === auth()->id()) {
            return true;
        }

        // For guests, check guest session ID from request header or body
        $guestSessionId = request()->header('X-Guest-Session-Id') ?? request()->input('guest_session_id');
        if ($guestSessionId && $attempt->guest_session_id === $guestSessionId) {
            return true;
        }

        return false;
    }
}
