<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTemplateSet extends Model
{
    use HasFactory;

    protected $table = 'gym_daily_template_sets';

    protected $fillable = [
        'daily_template_exercise_id',
        'set_number',
        'reps_min',
        'reps_max',
        'weight_min',
        'weight_max',
        'weight_target',
        'rest_seconds',
        'rpe_target',
        'notes',
    ];

    protected $casts = [
        'rpe_target' => 'float',
        'weight_min' => 'float',
        'weight_max' => 'float',
        'weight_target' => 'float',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(DailyTemplateExercise::class, 'daily_template_exercise_id');
    }
}
