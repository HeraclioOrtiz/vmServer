<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyTemplate extends Model
{
    use HasFactory;

    protected $table = 'gym_weekly_templates';

    protected $fillable = [
        'created_by',
        'title',
        'goal',
        'split',
        'days_per_week',
        'tags',
        'is_preset',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_preset' => 'boolean',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(WeeklyTemplateDay::class, 'weekly_template_id')->orderBy('weekday');
    }
}
