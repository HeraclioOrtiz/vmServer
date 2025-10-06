<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyAssignment extends Model
{
    use HasFactory;

    protected $table = 'gym_daily_assignments';

    protected $fillable = [
        'weekly_assignment_id',
        'weekday',
        'date',
        'title',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function weeklyAssignment(): BelongsTo
    {
        return $this->belongsTo(WeeklyAssignment::class, 'weekly_assignment_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(AssignedExercise::class, 'daily_assignment_id')->orderBy('display_order');
    }
}
