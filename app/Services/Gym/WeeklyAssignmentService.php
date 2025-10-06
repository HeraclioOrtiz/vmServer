<?php

namespace App\Services\Gym;

use App\Models\Gym\WeeklyAssignment;
use App\Models\Gym\DailyAssignment;
use App\Models\Gym\AssignedExercise;
use App\Models\Gym\AssignedSet;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WeeklyAssignmentService
{
    /**
     * Obtiene asignaciones con filtros aplicados
     */
    public function getFilteredAssignments(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = WeeklyAssignment::query();

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Aplica filtros a la consulta de asignaciones
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('week_start', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('week_end', '<=', $filters['to']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }
    }

    /**
     * Aplica ordenamiento a la consulta
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'week_start';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedSorts = ['week_start', 'week_end', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * Crea una nueva asignación semanal
     */
    public function createWeeklyAssignment(array $data, User $creator): WeeklyAssignment
    {
        return DB::transaction(function () use ($data, $creator) {
            $assignment = WeeklyAssignment::create([
                'user_id' => $data['user_id'],
                'week_start' => $data['week_start'],
                'week_end' => $data['week_end'],
                'source_type' => $data['source_type'] ?? null,
                'weekly_template_id' => $data['weekly_template_id'] ?? null,
                'created_by' => $creator->id,
                'notes' => $data['notes'] ?? null,
            ]);

            if (!empty($data['days'])) {
                $this->createDailyAssignments($assignment, $data['days']);
            }

            return $assignment;
        });
    }

    /**
     * Crea asignaciones diarias para una asignación semanal
     */
    private function createDailyAssignments(WeeklyAssignment $assignment, array $days): void
    {
        foreach ($days as $dayData) {
            $dailyAssignment = DailyAssignment::create([
                'weekly_assignment_id' => $assignment->id,
                'weekday' => $dayData['weekday'],
                'date' => $dayData['date'],
                'title' => $dayData['title'] ?? null,
                'notes' => $dayData['notes'] ?? null,
            ]);

            if (!empty($dayData['exercises'])) {
                $this->createAssignedExercises($dailyAssignment, $dayData['exercises']);
            }
        }
    }

    /**
     * Crea ejercicios asignados para una asignación diaria
     */
    private function createAssignedExercises(DailyAssignment $dailyAssignment, array $exercises): void
    {
        foreach ($exercises as $index => $exerciseData) {
            $assignedExercise = AssignedExercise::create([
                'daily_assignment_id' => $dailyAssignment->id,
                'exercise_id' => $exerciseData['exercise_id'] ?? null,
                'display_order' => $exerciseData['order'] ?? ($index + 1),
                'name' => $exerciseData['name'],
                'muscle_group' => $exerciseData['muscle_group'] ?? null,
                'equipment' => $exerciseData['equipment'] ?? null,
                'instructions' => $exerciseData['instructions'] ?? null,
                'tempo' => $exerciseData['tempo'] ?? null,
                'notes' => $exerciseData['notes'] ?? null,
            ]);

            if (!empty($exerciseData['sets'])) {
                $this->createAssignedSets($assignedExercise, $exerciseData['sets']);
            }
        }
    }

    /**
     * Crea series asignadas para un ejercicio asignado
     */
    private function createAssignedSets(AssignedExercise $assignedExercise, array $sets): void
    {
        foreach ($sets as $setData) {
            AssignedSet::create([
                'assigned_exercise_id' => $assignedExercise->id,
                'set_number' => $setData['set_number'],
                'reps_min' => $setData['reps_min'] ?? null,
                'reps_max' => $setData['reps_max'] ?? null,
                'weight_min' => $setData['weight_min'] ?? null,
                'weight_max' => $setData['weight_max'] ?? null,
                'weight_target' => $setData['weight_target'] ?? null,
                'rest_seconds' => $setData['rest_seconds'] ?? null,
                'tempo' => $setData['tempo'] ?? null,
                'rpe_target' => $setData['rpe_target'] ?? null,
                'notes' => $setData['notes'] ?? null,
            ]);
        }
    }

    /**
     * Actualiza una asignación semanal
     */
    public function updateWeeklyAssignment(WeeklyAssignment $assignment, array $data): WeeklyAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $assignment->update([
                'week_start' => $data['week_start'] ?? $assignment->week_start,
                'week_end' => $data['week_end'] ?? $assignment->week_end,
                'notes' => $data['notes'] ?? $assignment->notes,
            ]);

            // Si se proporcionan días, reemplazar completamente
            if (isset($data['days'])) {
                // Eliminar asignaciones diarias existentes
                $assignment->days()->delete();
                
                // Crear nuevas asignaciones diarias
                $this->createDailyAssignments($assignment, $data['days']);
            }

            return $assignment->fresh();
        });
    }

    /**
     * Elimina una asignación semanal
     */
    public function deleteWeeklyAssignment(WeeklyAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            // Las asignaciones diarias y ejercicios se eliminan por cascade
            return $assignment->delete();
        });
    }

    /**
     * Obtiene una asignación con todas sus relaciones
     */
    public function getAssignmentWithDetails(WeeklyAssignment $assignment): WeeklyAssignment
    {
        return $assignment->load([
            'user:id,name,dni,email',
            'creator:id,name,dni',
            'weeklyTemplate:id,title',
            'days.exercises.sets'
        ]);
    }

    /**
     * Verifica si hay conflictos de asignación para un usuario
     */
    public function checkAssignmentConflicts(int $userId, string $weekStart, string $weekEnd, ?int $excludeId = null): array
    {
        $query = WeeklyAssignment::where('user_id', $userId)
            ->where(function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('week_start', [$weekStart, $weekEnd])
                  ->orWhereBetween('week_end', [$weekStart, $weekEnd])
                  ->orWhere(function ($subQ) use ($weekStart, $weekEnd) {
                      $subQ->where('week_start', '<=', $weekStart)
                           ->where('week_end', '>=', $weekEnd);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get()->toArray();
    }

    /**
     * Obtiene estadísticas de asignaciones
     */
    public function getAssignmentStats(array $filters = []): array
    {
        $query = WeeklyAssignment::query();
        
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('week_start', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('week_end', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $active = (clone $query)->where('week_end', '>=', now())->count();
        $completed = (clone $query)->where('week_end', '<', now())->count();

        return [
            'total_assignments' => $total,
            'active_assignments' => $active,
            'completed_assignments' => $completed,
            'unique_students' => (clone $query)->distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Duplica una asignación semanal para nuevas fechas
     */
    public function duplicateAssignment(WeeklyAssignment $original, string $newWeekStart, string $newWeekEnd, User $creator): WeeklyAssignment
    {
        return DB::transaction(function () use ($original, $newWeekStart, $newWeekEnd, $creator) {
            $original->load('days.exercises.sets');

            $newAssignment = WeeklyAssignment::create([
                'user_id' => $original->user_id,
                'week_start' => $newWeekStart,
                'week_end' => $newWeekEnd,
                'source_type' => 'duplicated',
                'weekly_template_id' => $original->weekly_template_id,
                'created_by' => $creator->id,
                'notes' => $original->notes . ' (Duplicada)',
            ]);

            foreach ($original->days as $originalDay) {
                $daysDiff = now()->parse($newWeekStart)->diffInDays(now()->parse($original->week_start));
                $newDate = now()->parse($originalDay->date)->addDays($daysDiff)->format('Y-m-d');

                $newDay = DailyAssignment::create([
                    'weekly_assignment_id' => $newAssignment->id,
                    'weekday' => $originalDay->weekday,
                    'date' => $newDate,
                    'title' => $originalDay->title,
                    'notes' => $originalDay->notes,
                ]);

                foreach ($originalDay->exercises as $originalExercise) {
                    $newExercise = AssignedExercise::create([
                        'daily_assignment_id' => $newDay->id,
                        'exercise_id' => $originalExercise->exercise_id,
                        'display_order' => $originalExercise->display_order,
                        'name' => $originalExercise->name,
                        'muscle_group' => $originalExercise->muscle_group,
                        'equipment' => $originalExercise->equipment,
                        'instructions' => $originalExercise->instructions,
                        'tempo' => $originalExercise->tempo,
                        'notes' => $originalExercise->notes,
                    ]);

                    foreach ($originalExercise->sets as $originalSet) {
                        AssignedSet::create([
                            'assigned_exercise_id' => $newExercise->id,
                            'set_number' => $originalSet->set_number,
                            'reps_min' => $originalSet->reps_min,
                            'reps_max' => $originalSet->reps_max,
                            'weight_min' => $originalSet->weight_min,
                            'weight_max' => $originalSet->weight_max,
                            'weight_target' => $originalSet->weight_target,
                            'rest_seconds' => $originalSet->rest_seconds,
                            'tempo' => $originalSet->tempo,
                            'rpe_target' => $originalSet->rpe_target,
                            'notes' => $originalSet->notes,
                        ]);
                    }
                }
            }

            return $newAssignment;
        });
    }
}
