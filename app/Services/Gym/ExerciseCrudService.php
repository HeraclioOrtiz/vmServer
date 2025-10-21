<?php

namespace App\Services\Gym;

use App\Models\Gym\Exercise;
use App\Services\Core\AuditService;
use App\Services\Core\CacheService;
use App\Utils\QueryFilterBuilder;
use App\Exceptions\Business\ResourceInUseException;
use App\Exceptions\Business\InsufficientPermissionsException;
use App\Exceptions\Database\DatabaseException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Exercise CRUD Service
 *
 * Handles create, read, update, delete operations for exercises
 */
class ExerciseCrudService
{
    public function __construct(
        private AuditService $auditService,
        private CacheService $cacheService
    ) {}

    /**
     * Get filtered exercises with pagination
     */
    public function getFiltered(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Exercise::query();

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Create a new exercise
     */
    public function create(array $data, $user): Exercise
    {
        return DB::transaction(function () use ($data, $user) {
            $exercise = Exercise::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'muscle_groups' => $data['muscle_groups'] ?? null,
                'target_muscle_groups' => $data['target_muscle_groups'] ?? null,
                'movement_pattern' => $data['movement_pattern'] ?? null,
                'equipment' => $data['equipment'] ?? null,
                'difficulty_level' => $data['difficulty_level'] ?? null,
                'tags' => $data['tags'] ?? [],
                'instructions' => $data['instructions'] ?? null,
            ]);

            // Audit log
            $this->auditService->log(
                action: 'create',
                resourceType: 'exercise',
                resourceId: $exercise->id,
                details: [
                    'exercise_name' => $exercise->name,
                    'difficulty_level' => $exercise->difficulty_level,
                    'muscle_groups' => $exercise->muscle_groups,
                ],
                severity: 'low',
                category: 'gym'
            );

            $this->clearCache();

            return $exercise;
        });
    }

    /**
     * Update an exercise
     */
    public function update(Exercise $exercise, array $data, $user): Exercise
    {
        return DB::transaction(function () use ($exercise, $data, $user) {
            $oldValues = $exercise->toArray();

            $exercise->update([
                'name' => $data['name'] ?? $exercise->name,
                'description' => $data['description'] ?? $exercise->description,
                'muscle_groups' => $data['muscle_groups'] ?? $exercise->muscle_groups,
                'target_muscle_groups' => $data['target_muscle_groups'] ?? $exercise->target_muscle_groups,
                'movement_pattern' => $data['movement_pattern'] ?? $exercise->movement_pattern,
                'equipment' => $data['equipment'] ?? $exercise->equipment,
                'difficulty_level' => $data['difficulty_level'] ?? $exercise->difficulty_level,
                'tags' => $data['tags'] ?? $exercise->tags,
                'instructions' => $data['instructions'] ?? $exercise->instructions,
            ]);

            // Audit log
            $this->auditService->log(
                action: 'update',
                resourceType: 'exercise',
                resourceId: $exercise->id,
                details: [
                    'exercise_name' => $exercise->name,
                    'changes' => array_keys($data),
                ],
                oldValues: $oldValues,
                newValues: $exercise->fresh()->toArray(),
                severity: 'low',
                category: 'gym'
            );

            $this->clearCache();

            return $exercise;
        });
    }

    /**
     * Duplicate an exercise
     */
    public function duplicate(Exercise $exercise, $user): Exercise
    {
        return DB::transaction(function () use ($exercise, $user) {
            $duplicated = $exercise->replicate();
            $duplicated->name = $exercise->name . ' (Copia)';
            $duplicated->created_by = $user->id;
            $duplicated->save();

            // Audit log
            $this->auditService->log(
                action: 'duplicate',
                resourceType: 'exercise',
                resourceId: $duplicated->id,
                details: [
                    'original_exercise' => $exercise->name,
                    'new_exercise' => $duplicated->name,
                    'original_id' => $exercise->id,
                ],
                severity: 'low',
                category: 'gym'
            );

            return $duplicated;
        });
    }

    /**
     * Delete an exercise with dependency validation
     *
     * @throws ResourceInUseException
     * @throws DatabaseException
     */
    public function delete(Exercise $exercise, $user): Exercise
    {
        // Check dependencies before attempting to delete
        $dependencies = $this->checkDependencies($exercise);

        if (!$dependencies['can_delete']) {
            throw new ResourceInUseException(
                'No se puede eliminar el ejercicio porque está siendo usado en plantillas de entrenamiento.',
                [
                    'templates_count' => $dependencies['dependencies']['daily_templates'],
                    'exercise_id' => $exercise->id,
                    'exercise_name' => $exercise->name
                ]
            );
        }

        return DB::transaction(function () use ($exercise, $user) {
            try {
                // Audit log before deleting
                $this->auditService->log(
                    action: 'delete',
                    resourceType: 'exercise',
                    resourceId: $exercise->id,
                    details: [
                        'exercise_name' => $exercise->name,
                        'reason' => 'Exercise deleted - no dependencies found',
                    ],
                    severity: 'medium',
                    category: 'gym'
                );

                $exercise->delete();

                $this->clearCache();

                return $exercise;
            } catch (\Exception $e) {
                throw new DatabaseException(
                    'Error al eliminar el ejercicio',
                    [
                        'exercise_id' => $exercise->id,
                        'exercise_name' => $exercise->name
                    ]
                );
            }
        });
    }

    /**
     * Force delete exercise (admin only)
     * Deletes the exercise and ALL templates that use it
     *
     * @throws InsufficientPermissionsException
     * @throws DatabaseException
     */
    public function forceDelete(Exercise $exercise, $user): array
    {
        // Verify permissions
        if (!$user->is_admin) {
            throw new InsufficientPermissionsException(
                'No tienes permisos para realizar eliminación forzada'
            );
        }

        return DB::transaction(function () use ($exercise, $user) {
            try {
                // Get affected template IDs BEFORE deleting
                $affectedTemplateIds = $exercise->dailyTemplateExercises()
                    ->pluck('daily_template_id')
                    ->unique()
                    ->toArray();

                $templatesCount = count($affectedTemplateIds);

                // Audit log before deleting
                $this->auditService->log(
                    action: 'force_delete',
                    resourceType: 'exercise',
                    resourceId: $exercise->id,
                    details: [
                        'exercise_name' => $exercise->name,
                        'reason' => 'Force delete - admin override',
                        'templates_deleted' => $templatesCount,
                        'affected_template_ids' => $affectedTemplateIds
                    ],
                    severity: 'high',
                    category: 'gym'
                );

                // Delete complete templates (cascade will delete assignments)
                if ($templatesCount > 0) {
                    \App\Models\Gym\DailyTemplate::whereIn('id', $affectedTemplateIds)->delete();
                }

                // Then delete the exercise
                $exercise->delete();

                $this->clearCache();

                return [
                    'success' => true,
                    'message' => "Ejercicio eliminado correctamente. Se eliminaron {$templatesCount} plantilla(s) y sus asignaciones.",
                    'warning' => $templatesCount > 0 ? "Esta acción eliminó {$templatesCount} plantilla(s) que usaban este ejercicio y las desasignó de todos los estudiantes." : null,
                    'deleted_templates_count' => $templatesCount,
                ];
            } catch (\Exception $e) {
                throw new DatabaseException(
                    'Error al realizar eliminación forzada',
                    [
                        'exercise_id' => $exercise->id,
                        'exercise_name' => $exercise->name,
                        'error_message' => $e->getMessage()
                    ]
                );
            }
        });
    }

    /**
     * Check exercise dependencies
     */
    public function checkDependencies(Exercise $exercise): array
    {
        $dependencies = [
            'daily_templates' => $exercise->dailyTemplateExercises()->count(),
            // Add other future dependencies here
        ];

        $canDelete = array_sum($dependencies) === 0;

        return [
            'can_delete' => $canDelete,
            'dependencies' => $dependencies,
            'total_references' => array_sum($dependencies),
            'exercise' => [
                'id' => $exercise->id,
                'name' => $exercise->name
            ]
        ];
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        // Text search (name, description, instructions, tags)
        QueryFilterBuilder::applySearch(
            $query,
            $filters['search'] ?? null,
            ['name', 'description', 'instructions', 'tags_json']
        );

        // Muscle groups filter (JSON array)
        QueryFilterBuilder::applyJsonContains(
            $query,
            $filters['muscle_groups'] ?? null,
            'muscle_groups'
        );

        // Target muscle groups filter (JSON array)
        QueryFilterBuilder::applyJsonContains(
            $query,
            $filters['target_muscle_groups'] ?? null,
            'target_muscle_groups'
        );

        // Difficulty level filter (supports single or array)
        QueryFilterBuilder::applyWhereIn(
            $query,
            $filters['difficulty_level'] ?? null,
            'difficulty_level'
        );

        // Equipment filter (LIKE search)
        QueryFilterBuilder::applyLike(
            $query,
            $filters['equipment'] ?? null,
            'equipment'
        );

        // Movement pattern filter (LIKE search)
        QueryFilterBuilder::applyLike(
            $query,
            $filters['movement_pattern'] ?? null,
            'movement_pattern'
        );

        // Tags filter (JSON array)
        QueryFilterBuilder::applyJsonContains(
            $query,
            $filters['tags'] ?? null,
            'tags'
        );
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, array $filters): void
    {
        QueryFilterBuilder::applySorting(
            $query,
            $filters,
            ['name', 'difficulty_level', 'movement_pattern', 'created_at', 'updated_at'],
            'name',
            'asc'
        );
    }

    /**
     * Clear exercise cache
     */
    private function clearCache(): void
    {
        // Build array of all cache keys to clear
        $keys = ['exercise_stats', 'exercise_filter_options'];

        // Add most used exercise keys (various possible limits)
        for ($i = 1; $i <= 20; $i++) {
            $keys[] = "most_used_exercises_{$i}";
        }

        // Clear all keys at once
        $this->cacheService->forget($keys);
    }
}
