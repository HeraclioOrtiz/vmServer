<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyAssignment extends Model
{
    use HasFactory;

    protected $table = 'gym_weekly_assignments';

    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'source_type',
        'weekly_template_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(DailyAssignment::class, 'weekly_assignment_id')->orderBy('weekday');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WeeklyTemplate::class, 'weekly_template_id');
    }
}
