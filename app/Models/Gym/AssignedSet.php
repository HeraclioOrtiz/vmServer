<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignedSet extends Model
{
    use HasFactory;

    protected $table = 'gym_assigned_sets';

    protected $fillable = [
        'assigned_exercise_id',
        'set_number',
        'reps_min',
        'reps_max',
        'weight_min',
        'weight_max',
        'weight_target',
        'rest_seconds',
        'tempo',
        'rpe_target',
        'notes',
    ];

    protected $casts = [
        'rpe_target' => 'float',
        'weight_min' => 'float',
        'weight_max' => 'float',
        'weight_target' => 'float',
    ];

    public function assignedExercise(): BelongsTo
    {
        return $this->belongsTo(AssignedExercise::class, 'assigned_exercise_id');
    }
}
