<?php

namespace App\Services\Gym;

use App\Models\Gym\Exercise;
use App\Services\Core\AuditService;
use App\Services\Core\CacheService;
use App\Utils\QueryFilterBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExerciseService
{
    public function __construct(
        private AuditService $auditService,
        private CacheService $cacheService
    ) {}

    /**
     * Obtener ejercicios con filtros avanzados
     */
    public function getFilteredExercises(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Exercise::query();

        // Filtro por búsqueda de texto (name, description, instructions, tags)
        QueryFilterBuilder::applySearch(
            $query,
            $filters['search'] ?? null,
            ['name', 'description', 'instructions', 'tags_json']
        );

        // Filtro por grupos musculares (JSON array)
        QueryFilterBuilder::applyJsonContains(
            $query,
            $filters['muscle_groups'] ?? null,
            'muscle_groups'
        );

        // Filtro por músculos objetivo (JSON array)
        QueryFilterBuilder::applyJsonContains(
            $query,
            $filters['target_muscle_groups'] ?? null,
            'target_muscle_groups'
        );

        // Filtro por nivel de dificultad (supports single or array)
        QueryFilterBuilder::applyWhereIn(
            $query,
            $filters['difficulty_level'] ?? null,
            'difficulty_level'
        );

        // Filtro por equipamiento (LIKE search)
        QueryFilterBuilder::applyLike(
            $query,
            $filters['equipment'] ?? null,
            'equipment'
        );

        // Filtro por patrón de movimiento (LIKE search)
        QueryFilterBuilder::applyLike(
            $query,
            $filters['movement_pattern'] ?? null,
            'movement_pattern'
        );

        // Filtro por tags (JSON array)
        QueryFilterBuilder::applyJsonContains(
            $query,
            $filters['tags'] ?? null,
            'tags'
        );

        // Ordenamiento dinámico
        QueryFilterBuilder::applySorting(
            $query,
            $filters,
            ['name', 'difficulty_level', 'movement_pattern', 'created_at', 'updated_at'],
            'name',
            'asc'
        );

        return $query->paginate($perPage);
    }

    /**
     * Crear un nuevo ejercicio
     */
    public function createExercise(array $data, $user): Exercise
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

            // Auditoría
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

            // Limpiar cache
            $this->clearExerciseCache();

            return $exercise;
        });
    }

    /**
     * Actualizar un ejercicio
     */
    public function updateExercise(Exercise $exercise, array $data, $user): Exercise
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

            // Auditoría
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

            // Limpiar cache
            $this->clearExerciseCache();

            return $exercise;
        });
    }

    /**
     * Verificar dependencias de un ejercicio
     */
    public function checkExerciseDependencies(Exercise $exercise): array
    {
        $dependencies = [
            'daily_templates' => $exercise->dailyTemplateExercises()->count(),
            // Agregar otras dependencias futuras aquí
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

    /**
     * Eliminar un ejercicio con validación de dependencias
     */
    public function deleteExercise(Exercise $exercise, $user): array
    {
        // Verificar dependencias antes de intentar eliminar
        $dependencies = $this->checkExerciseDependencies($exercise);
        
        if (!$dependencies['can_delete']) {
            return [
                'success' => false,
                'error' => 'EXERCISE_IN_USE',
                'message' => 'No se puede eliminar el ejercicio porque está siendo usado en plantillas de entrenamiento.',
                'details' => [
                    'templates_count' => $dependencies['dependencies']['daily_templates'],
                    'exercise_id' => $exercise->id,
                    'exercise_name' => $exercise->name
                ],
                'status_code' => 422
            ];
        }

        return DB::transaction(function () use ($exercise, $user) {
            try {
                // Auditoría antes de eliminar
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

                // Limpiar cache
                $this->clearExerciseCache();

                return [
                    'success' => true,
                    'message' => 'Ejercicio eliminado correctamente',
                    'status_code' => 200
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => 'DELETE_FAILED',
                    'message' => 'Error al eliminar el ejercicio',
                    'details' => [
                        'exercise_id' => $exercise->id,
                        'exercise_name' => $exercise->name
                    ],
                    'status_code' => 500
                ];
            }
        });
    }

    /**
     * Eliminación forzada de ejercicio (solo para admins)
     * Elimina el ejercicio y TODAS las plantillas que lo usan (desasignándolas automáticamente)
     */
    public function forceDeleteExercise(Exercise $exercise, $user): array
    {
        // Verificar permisos
        if (!$user->is_admin) {
            return [
                'success' => false,
                'error' => 'INSUFFICIENT_PERMISSIONS',
                'message' => 'No tienes permisos para realizar eliminación forzada',
                'status_code' => 403
            ];
        }

        return DB::transaction(function () use ($exercise, $user) {
            try {
                // Obtener IDs de plantillas afectadas ANTES de eliminar
                $affectedTemplateIds = $exercise->dailyTemplateExercises()
                    ->pluck('daily_template_id')
                    ->unique()
                    ->toArray();
                
                $templatesCount = count($affectedTemplateIds);
                
                // Auditoría antes de eliminar
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

                // Eliminar las plantillas completas (cascade eliminará asignaciones)
                if ($templatesCount > 0) {
                    \App\Models\Gym\DailyTemplate::whereIn('id', $affectedTemplateIds)->delete();
                }
                
                // Luego eliminar el ejercicio
                $exercise->delete();

                // Limpiar cache
                $this->clearExerciseCache();

                return [
                    'success' => true,
                    'message' => "Ejercicio eliminado correctamente. Se eliminaron {$templatesCount} plantilla(s) y sus asignaciones.",
                    'warning' => $templatesCount > 0 ? "Esta acción eliminó {$templatesCount} plantilla(s) que usaban este ejercicio y las desasignó de todos los estudiantes." : null,
                    'deleted_templates_count' => $templatesCount,
                    'status_code' => 200
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => 'FORCE_DELETE_FAILED',
                    'message' => 'Error al realizar eliminación forzada',
                    'details' => [
                        'exercise_id' => $exercise->id,
                        'exercise_name' => $exercise->name,
                        'error_message' => $e->getMessage()
                    ],
                    'status_code' => 500
                ];
            }
        });
    }

    /**
     * Duplicar un ejercicio
     */
    public function duplicateExercise(Exercise $exercise, $user): Exercise
    {
        return DB::transaction(function () use ($exercise, $user) {
            $duplicated = $exercise->replicate();
            $duplicated->name = $exercise->name . ' (Copia)';
            $duplicated->created_by = $user->id;
            $duplicated->save();

            // Auditoría
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
     * Obtener estadísticas de ejercicios
     */
    public function getExerciseStats(): array
    {
        return $this->cacheService->rememberStats('exercise_stats', function () {
            return [
                'total_exercises' => Exercise::count(),
                'active_exercises' => Exercise::where('is_active', true)->count(),
                'by_category' => Exercise::select('category', DB::raw('count(*) as count'))
                    ->where('is_active', true)
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray(),
                'by_difficulty' => Exercise::select('difficulty_level', DB::raw('count(*) as count'))
                    ->where('is_active', true)
                    ->groupBy('difficulty_level')
                    ->pluck('count', 'difficulty_level')
                    ->toArray(),
                'most_used' => $this->getMostUsedExercises(5),
                'recent_additions' => Exercise::where('created_at', '>=', now()->subDays(30))->count(),
            ];
        });
    }

    /**
     * Obtener ejercicios más utilizados
     */
    public function getMostUsedExercises(int $limit = 10): Collection
    {
        return $this->cacheService->rememberList("most_used_exercises_{$limit}", function () use ($limit) {
            return Exercise::select('exercises.*', DB::raw('COUNT(template_exercises.id) as usage_count'))
                ->leftJoin('template_exercises', 'exercises.id', '=', 'template_exercises.exercise_id')
                ->where('exercises.is_active', true)
                ->groupBy('exercises.id')
                ->orderByDesc('usage_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtener opciones de filtros
     */
    public function getFilterOptions(): array
    {
        return $this->cacheService->rememberFilters('exercise_filter_options', function () {
            $exercises = Exercise::where('is_active', true)->get();
            
            return [
                'categories' => $exercises->pluck('category')->unique()->sort()->values()->toArray(),
                'muscle_groups' => $exercises->pluck('muscle_groups')->flatten()->unique()->sort()->values()->toArray(),
                'equipment' => $exercises->pluck('equipment')->flatten()->unique()->filter()->sort()->values()->toArray(),
                'difficulty_levels' => [1, 2, 3, 4, 5],
                'tags' => $exercises->pluck('tags')->flatten()->unique()->filter()->sort()->values()->toArray(),
            ];
        });
    }

    /**
     * Obtener conteo de uso de un ejercicio
     */
    private function getExerciseUsageCount(Exercise $exercise): int
    {
        return $exercise->dailyTemplateExercises()->count();
    }

    /**
     * Limpiar cache relacionado con ejercicios
     */
    private function clearExerciseCache(): void
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
