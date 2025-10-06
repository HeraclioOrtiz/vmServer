<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyTemplate extends Model
{
    use HasFactory;

    protected $table = 'gym_daily_templates';

    protected $fillable = [
        'created_by',
        'title',
        'goal',
        'estimated_duration_min',
        'level',
        'tags',
        'is_preset',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_preset' => 'boolean',
    ];

    public function exercises(): HasMany
    {
        return $this->hasMany(DailyTemplateExercise::class, 'daily_template_id')
            ->orderBy('display_order');
    }
}
