<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'activity_id',
        'item_type',
        'prompt_text',
        'content_data',
        'options',
        'max_points',
        'time_limit_seconds',
        'order',
    ];

    protected $casts = [
        'content_data' => 'array',
        'options' => 'array',
        'max_points' => 'integer',
        'time_limit_seconds' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the activity that owns this item.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the results for this item.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Scope to order by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get item type display name.
     */
    public function getItemTypeNameAttribute(): string
    {
        return match($this->item_type) {
            'emoji_choice' => 'Emoji Choice',
            'text_copy_timed' => 'Timed Typing',
            'shape_copy_canvas' => 'Shape Drawing',
            'trace_the_path' => 'Path Tracing',
            'dot_to_dot' => 'Connect the Dots',
            'find_the_different_one' => 'Find the Different',
            'simple_puzzle_drag' => 'Puzzle',
            'whats_missing' => 'What\'s Missing',
            'listen_and_type' => 'Listen and Type',
            'unscramble_the_word' => 'Unscramble Word',
            default => 'Unknown',
        };
    }
}
