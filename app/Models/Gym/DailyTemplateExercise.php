<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyTemplateExercise extends Model
{
    use HasFactory;

    protected $table = 'gym_daily_template_exercises';

    protected $fillable = [
        'daily_template_id',
        'exercise_id',
        'display_order',
        'notes',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DailyTemplate::class, 'daily_template_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(DailyTemplateSet::class, 'daily_template_exercise_id')
            ->orderBy('set_number');
    }
}
