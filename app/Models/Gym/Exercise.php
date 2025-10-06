<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $table = 'gym_exercises';

    protected $fillable = [
        'name',
        'description',
        'muscle_groups',
        'target_muscle_groups',
        'movement_pattern',
        'equipment',
        'difficulty_level',
        'tags',
        'instructions',
    ];

    protected $casts = [
        'muscle_groups' => 'array',
        'target_muscle_groups' => 'array',
        'tags' => 'array',
    ];

    /**
     * Relación con ejercicios de plantillas diarias
     */
    public function dailyTemplateExercises()
    {
        return $this->hasMany(DailyTemplateExercise::class, 'exercise_id');
    }
}
