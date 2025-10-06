<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentProgress extends Model
{
    protected $table = 'assignment_progress';

    protected $fillable = [
        'daily_assignment_id',
        'scheduled_date',
        'status',
        'completed_at',
        'exercise_progress',
        'student_notes',
        'professor_feedback',
        'overall_rating'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'exercise_progress' => 'array',
        'overall_rating' => 'decimal:1',
    ];

    // Relaciones
    public function templateAssignment(): BelongsTo
    {
        return $this->belongsTo(TemplateAssignment::class, 'daily_assignment_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('scheduled_date', $date);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Métodos auxiliares
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && $this->scheduled_date->isPast();
    }

    public function markAsCompleted(array $exerciseProgress = [], ?string $studentNotes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'exercise_progress' => $exerciseProgress,
            'student_notes' => $studentNotes,
        ]);
    }

    public function addProfessorFeedback(string $feedback, ?float $rating = null): void
    {
        $this->update([
            'professor_feedback' => $feedback,
            'overall_rating' => $rating,
        ]);
    }
}
