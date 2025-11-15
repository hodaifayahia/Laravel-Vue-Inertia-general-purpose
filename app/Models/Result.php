<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_attempt_id',
        'activity_item_id',
        'result_data',
        'points_awarded',
        'time_taken_ms',
        'is_correct',
    ];

    protected $casts = [
        'result_data' => 'array',
        'points_awarded' => 'integer',
        'time_taken_ms' => 'integer',
        'is_correct' => 'boolean',
    ];

    /**
     * Get the activity attempt for this result.
     */
    public function activityAttempt(): BelongsTo
    {
        return $this->belongsTo(ActivityAttempt::class);
    }

    /**
     * Get the activity item for this result.
     */
    public function activityItem(): BelongsTo
    {
        return $this->belongsTo(ActivityItem::class);
    }

    /**
     * Get time taken in seconds.
     */
    public function getTimeInSecondsAttribute(): ?float
    {
        if ($this->time_taken_ms) {
            return $this->time_taken_ms / 1000;
        }
        return null;
    }
}
