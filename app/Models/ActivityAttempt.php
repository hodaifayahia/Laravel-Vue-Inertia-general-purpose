<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityAttempt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'guest_session_id',
        'activity_id',
        'child_id',
        'final_score',
        'consultation_needed',
        'status',
        'started_at',
        'completed_at',
        'admin_notes',
    ];

    protected $casts = [
        'final_score' => 'integer',
        'consultation_needed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who made this attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the activity for this attempt.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the child for this attempt.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * Get the results for this attempt.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Mark the attempt as completed.
     */
    public function markAsCompleted(int $finalScore, bool $consultationNeeded = false): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_score' => $finalScore,
            'consultation_needed' => $consultationNeeded,
        ]);
    }

    /**
     * Check if this is a guest attempt.
     */
    public function isGuest(): bool
    {
        return !is_null($this->guest_session_id) && is_null($this->user_id);
    }

    /**
     * Link guest attempt to a user after signup.
     */
    public function linkToUser(int $userId): void
    {
        $this->update([
            'user_id' => $userId,
            'guest_session_id' => null,
        ]);
    }

    /**
     * Get duration in seconds.
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageAttribute(): ?float
    {
        $totalItems = $this->activity->activityItems()->count();
        if ($totalItems === 0) {
            return null;
        }
        $completedItems = $this->results()->count();
        return ($completedItems / $totalItems) * 100;
    }
}
