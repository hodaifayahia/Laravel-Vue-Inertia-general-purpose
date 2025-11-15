<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'estimated_duration_minutes',
        'is_active',
        'order',
        'difficulty_level',
        'min_age',
        'max_age',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'estimated_duration_minutes' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    /**
     * Get the activity items for this activity.
     */
    public function activityItems(): HasMany
    {
        return $this->hasMany(ActivityItem::class)->orderBy('order');
    }

    /**
     * Get the attempts for this activity.
     */
    public function activityAttempts(): HasMany
    {
        return $this->hasMany(ActivityAttempt::class);
    }

    /**
     * Get completed attempts for this activity.
     */
    public function completedAttempts(): HasMany
    {
        return $this->hasMany(ActivityAttempt::class)->where('status', 'completed');
    }

    /**
     * Scope to get only active activities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
