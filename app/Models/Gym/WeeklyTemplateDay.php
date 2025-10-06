<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTemplateDay extends Model
{
    use HasFactory;

    protected $table = 'gym_weekly_template_days';

    protected $fillable = [
        'weekly_template_id',
        'weekday',
        'daily_template_id',
    ];

    public function weeklyTemplate(): BelongsTo
    {
        return $this->belongsTo(WeeklyTemplate::class, 'weekly_template_id');
    }

    public function dailyTemplate(): BelongsTo
    {
        return $this->belongsTo(DailyTemplate::class, 'daily_template_id');
    }
}
