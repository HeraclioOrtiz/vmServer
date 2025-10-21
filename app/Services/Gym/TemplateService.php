<?php

namespace App\Services\Gym;

use App\Models\Gym\DailyTemplate;
use App\Models\Gym\WeeklyTemplate;
use App\Models\Gym\Exercise;
use App\Services\Core\AuditService;
use App\Services\Core\CacheService;
use App\Utils\QueryFilterBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    public function __construct(
        private AuditService $auditService,
        private CacheService $cacheService
    ) {}

    // ==================== PLANTILLAS DIARIAS ====================

    /**
     * Obtener plantillas diarias con filtros
     */
    public function getFilteredDailyTemplates(array $filters, int $perPage = 20, array $includes = []): LengthAwarePaginator
    {
        // Generar clave de cache basada en filtros
        $cacheKey = $this->generateCacheKey('daily_templates', $filters, $perPage, $includes);
        
        // Para consultas simples sin filtros específicos, usar cache
        if ($this->shouldUseCache($filters)) {
            return $this->cacheService->rememberStats($cacheKey, function () use ($filters, $perPage, $includes) {
                return $this->buildDailyTemplatesQuery($filters, $perPage, $includes);
            });
        }
        
        // Para consultas complejas o con filtros específicos, no usar cache
        return $this->buildDailyTemplatesQuery($filters, $perPage, $includes);
    }

    /**
     * Construir query de plantillas diarias
     */
    private function buildDailyTemplatesQuery(array $filters, int $perPage, array $includes): LengthAwarePaginator
    {
        $query = DailyTemplate::query();

        // Aplicar todos los filtros
        $this->applyDailyTemplateFilters($query, $filters);

        // Cargar relaciones si se especifican
        if (!empty($includes)) {
            $query->with($includes);
        }

        // Aplicar ordenamiento dinámico
        $this->applyDynamicSorting($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Determinar si se debe usar cache
     */
    private function shouldUseCache(array $filters): bool
    {
        // No usar cache si hay filtros específicos de búsqueda
        if (!empty($filters['search']) || !empty($filters['q'])) {
            return false;
        }

        // No usar cache si hay filtros complejos
        $complexFilters = ['target_muscle_groups', 'equipment_needed', 'tags'];
        foreach ($complexFilters as $filter) {
            if (!empty($filters[$filter])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generar clave de cache
     */
    private function generateCacheKey(string $type, array $filters, int $perPage, array $includes): string
    {
        $keyData = [
            'type' => $type,
            'filters' => array_filter($filters), // Solo filtros con valores
            'per_page' => $perPage,
            'includes' => $includes,
        ];
        
        return 'templates_' . md5(serialize($keyData));
    }

    /**
     * Aplicar filtros a la query de plantillas diarias
     */
    private function applyDailyTemplateFilters($query, array $filters): void
    {
        // Filtro por búsqueda (title, tags) - soporte para 'search' o 'q'
        $searchTerm = $filters['search'] ?? $filters['q'] ?? null;
        QueryFilterBuilder::applySearch($query, $searchTerm, ['title', 'tags_json']);

        // Filtro por objetivo/goal (soporte para 'primary_goal' o 'goal')
        QueryFilterBuilder::applyWithAliases($query, $filters, ['primary_goal', 'goal'], 'goal');

        // Filtro por dificultad/nivel (soporte para 'difficulty' o 'level')
        QueryFilterBuilder::applyWithAliases($query, $filters, ['difficulty', 'level'], 'level');

        // Filtro por grupos musculares (JSON array)
        QueryFilterBuilder::applyJsonContains($query, $filters['target_muscle_groups'] ?? null, 'tags');

        // Filtro por equipamiento (JSON array)
        QueryFilterBuilder::applyJsonContains($query, $filters['equipment_needed'] ?? null, 'tags');

        // Filtro por tags (JSON array)
        QueryFilterBuilder::applyJsonContains($query, $filters['tags'] ?? null, 'tags');

        // Filtro por preset (boolean)
        QueryFilterBuilder::applyBoolean($query, $filters['is_preset'] ?? null, 'is_preset');

        // Filtro por duración (range)
        QueryFilterBuilder::applyRange(
            $query,
            $filters['duration_min'] ?? null,
            $filters['duration_max'] ?? null,
            'estimated_duration_min'
        );
    }

    /**
     * Aplicar ordenamiento dinámico
     */
    private function applyDynamicSorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Orden especial por defecto: presets primero, luego por fecha
        if ($sortBy === 'created_at' && $sortDirection === 'desc') {
            $query->orderByDesc('is_preset')->orderByDesc('created_at')->orderBy('title');
        } else {
            // Uso de QueryFilterBuilder para ordenamiento estándar
            QueryFilterBuilder::applySorting(
                $query,
                $filters,
                ['created_at', 'updated_at', 'title', 'goal', 'level', 'estimated_duration_min', 'is_preset'],
                'created_at',
                'desc'
            );
        }
    }

    /**
     * Crear plantilla diaria
     */
    public function createDailyTemplate(array $data, $user): DailyTemplate
    {
        return DB::transaction(function () use ($data, $user) {
            // Crear plantilla
            $template = DailyTemplate::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'difficulty_level' => $data['difficulty_level'],
                'estimated_duration' => $data['estimated_duration'],
                'target_muscle_groups' => $data['target_muscle_groups'],
                'equipment_needed' => $data['equipment_needed'] ?? [],
                'is_preset' => $data['is_preset'] ?? false,
                'is_public' => $data['is_public'] ?? false,
                'tags' => $data['tags'] ?? [],
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // Agregar ejercicios
            if (!empty($data['exercises'])) {
                $this->addExercisesToDailyTemplate($template, $data['exercises']);
            }

            // Auditoría
            $this->auditService->log(
                user: $user,
                action: 'create_template',
                resourceType: 'daily_template',
                resourceId: $template->id,
                details: [
                    'template_title' => $template->title,
                    'category' => $template->category,
                    'exercises_count' => count($data['exercises'] ?? []),
                ],
                severity: 'low',
                category: 'gym'
            );

            $this->clearTemplateCache();
            return $template->load(['exercises.sets', 'exercises.exercise']);
        });
    }

    /**
     * Actualizar plantilla diaria
     */
    public function updateDailyTemplate(DailyTemplate $template, array $data, $user): DailyTemplate
    {
        return DB::transaction(function () use ($template, $data, $user) {
            $oldValues = $template->toArray();

            // Actualizar plantilla
            $template->update([
                'title' => $data['title'] ?? $template->title,
                'description' => $data['description'] ?? $template->description,
                'category' => $data['category'] ?? $template->category,
                'difficulty_level' => $data['difficulty_level'] ?? $template->difficulty_level,
                'estimated_duration' => $data['estimated_duration'] ?? $template->estimated_duration,
                'target_muscle_groups' => $data['target_muscle_groups'] ?? $template->target_muscle_groups,
                'equipment_needed' => $data['equipment_needed'] ?? $template->equipment_needed,
                'is_preset' => $data['is_preset'] ?? $template->is_preset,
                'is_public' => $data['is_public'] ?? $template->is_public,
                'tags' => $data['tags'] ?? $template->tags,
                'notes' => $data['notes'] ?? $template->notes,
            ]);

            // Actualizar ejercicios si se proporcionan
            if (isset($data['exercises'])) {
                // Eliminar ejercicios existentes
                $template->exercises()->delete();
                // Agregar nuevos ejercicios
                $this->addExercisesToDailyTemplate($template, $data['exercises']);
            }

            // Auditoría
            $this->auditService->log(
                user: $user,
                action: 'update',
                resourceType: 'daily_template',
                resourceId: $template->id,
                details: [
                    'template_title' => $template->title,
                    'changes' => array_keys($data),
                ],
                oldValues: $oldValues,
                newValues: $template->fresh()->toArray(),
                severity: 'low',
                category: 'gym'
            );

            $this->clearTemplateCache();
            return $template->load(['exercises.sets', 'exercises.exercise']);
        });
    }

    /**
     * Duplicar plantilla diaria
     */
    public function duplicateDailyTemplate(DailyTemplate $template, $user): DailyTemplate
    {
        return DB::transaction(function () use ($template, $user) {
            // Duplicar plantilla
            $duplicated = $template->replicate();
            $duplicated->title = $template->title . ' (Copia)';
            $duplicated->is_preset = false;
            $duplicated->created_by = $user->id;
            $duplicated->save();

            // Duplicar ejercicios y sets
            foreach ($template->exercises as $templateExercise) {
                $newTemplateExercise = $templateExercise->replicate();
                $newTemplateExercise->daily_template_id = $duplicated->id;
                $newTemplateExercise->save();

                foreach ($templateExercise->sets as $set) {
                    $newSet = $set->replicate();
                    $newSet->template_exercise_id = $newTemplateExercise->id;
                    $newSet->save();
                }
            }

            // Auditoría
            $this->auditService->log(
                user: $user,
                action: 'duplicate',
                resourceType: 'daily_template',
                resourceId: $duplicated->id,
                details: [
                    'original_template' => $template->title,
                    'new_template' => $duplicated->title,
                    'original_id' => $template->id,
                ],
                severity: 'low',
                category: 'gym'
            );

            return $duplicated->load(['exercises.sets', 'exercises.exercise']);
        });
    }

    // ==================== PLANTILLAS SEMANALES ====================

    /**
     * Obtener plantillas semanales con filtros
     */
    public function getFilteredWeeklyTemplates(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = WeeklyTemplate::query();

        // Filtro por búsqueda
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }

        // Filtros específicos
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['difficulty_level'])) {
            $query->where('difficulty_level', $filters['difficulty_level']);
        }

        if (!empty($filters['target_goals'])) {
            $goals = is_array($filters['target_goals']) 
                ? $filters['target_goals'] 
                : [$filters['target_goals']];
            
            foreach ($goals as $goal) {
                $query->whereJsonContains('target_goals', $goal);
            }
        }

        if (isset($filters['is_preset'])) {
            $query->where('is_preset', $filters['is_preset']);
        }

        // Ordenamiento
        $query->orderByDesc('is_preset')->orderBy('title');

        return $query->paginate($perPage);
    }

    /**
     * Crear plantilla semanal
     */
    public function createWeeklyTemplate(array $data, $user): WeeklyTemplate
    {
        return DB::transaction(function () use ($data, $user) {
            // Crear plantilla
            $template = WeeklyTemplate::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'difficulty_level' => $data['difficulty_level'],
                'weeks_duration' => $data['weeks_duration'],
                'sessions_per_week' => $data['sessions_per_week'],
                'target_goals' => $data['target_goals'],
                'target_muscle_groups' => $data['target_muscle_groups'],
                'equipment_needed' => $data['equipment_needed'] ?? [],
                'is_preset' => $data['is_preset'] ?? false,
                'is_public' => $data['is_public'] ?? false,
                'tags' => $data['tags'] ?? [],
                'notes' => $data['notes'] ?? null,
                'progression' => $data['progression'] ?? null,
                'created_by' => $user->id,
            ]);

            // Agregar días
            if (!empty($data['days'])) {
                $this->addDaysToWeeklyTemplate($template, $data['days']);
            }

            // Auditoría
            $this->auditService->log(
                user: $user,
                action: 'create_template',
                resourceType: 'weekly_template',
                resourceId: $template->id,
                details: [
                    'template_title' => $template->title,
                    'category' => $template->category,
                    'sessions_per_week' => $template->sessions_per_week,
                ],
                severity: 'low',
                category: 'gym'
            );

            $this->clearTemplateCache();
            return $template->load('days');
        });
    }

    /**
     * Obtener estadísticas de plantillas
     */
    public function getTemplateStats(): array
    {
        return $this->cacheService->rememberStats('template_stats', function () {
            return [
                'daily_templates' => [
                    'total' => DailyTemplate::count(),
                    'presets' => DailyTemplate::where('is_preset', true)->count(),
                    'public' => DailyTemplate::where('is_public', true)->count(),
                    'by_category' => DailyTemplate::select('category', DB::raw('count(*) as count'))
                        ->groupBy('category')
                        ->pluck('count', 'category')
                        ->toArray(),
                ],
                'weekly_templates' => [
                    'total' => WeeklyTemplate::count(),
                    'presets' => WeeklyTemplate::where('is_preset', true)->count(),
                    'public' => WeeklyTemplate::where('is_public', true)->count(),
                    'by_category' => WeeklyTemplate::select('category', DB::raw('count(*) as count'))
                        ->groupBy('category')
                        ->pluck('count', 'category')
                        ->toArray(),
                ],
                'most_used_daily' => $this->getMostUsedDailyTemplates(5),
                'most_used_weekly' => $this->getMostUsedWeeklyTemplates(5),
            ];
        });
    }

    /**
     * Obtener plantillas diarias más utilizadas
     */
    public function getMostUsedDailyTemplates(int $limit = 10): Collection
    {
        return $this->cacheService->rememberList("most_used_daily_templates_{$limit}", function () use ($limit) {
            return DailyTemplate::select('daily_templates.*', DB::raw('COUNT(daily_assignments.id) as usage_count'))
                ->leftJoin('daily_assignments', 'daily_templates.id', '=', 'daily_assignments.daily_template_id')
                ->groupBy('daily_templates.id')
                ->orderByDesc('usage_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtener plantillas semanales más utilizadas
     */
    public function getMostUsedWeeklyTemplates(int $limit = 10): Collection
    {
        return $this->cacheService->rememberList("most_used_weekly_templates_{$limit}", function () use ($limit) {
            return WeeklyTemplate::select('weekly_templates.*', DB::raw('COUNT(weekly_assignments.id) as usage_count'))
                ->leftJoin('weekly_assignments', 'weekly_templates.id', '=', 'weekly_assignments.source_id')
                ->where('weekly_assignments.source_type', 'template')
                ->groupBy('weekly_templates.id')
                ->orderByDesc('usage_count')
                ->limit($limit)
                ->get();
        });
    }

    // ==================== MÉTODOS PRIVADOS ====================

    /**
     * Agregar ejercicios a plantilla diaria
     */
    private function addExercisesToDailyTemplate(DailyTemplate $template, array $exercises): void
    {
        foreach ($exercises as $exerciseData) {
            $templateExercise = $template->exercises()->create([
                'exercise_id' => $exerciseData['exercise_id'],
                'order' => $exerciseData['order'],
                'rest_seconds' => $exerciseData['rest_seconds'] ?? null,
                'notes' => $exerciseData['notes'] ?? null,
            ]);

            // Agregar sets
            if (!empty($exerciseData['sets'])) {
                foreach ($exerciseData['sets'] as $setData) {
                    $templateExercise->sets()->create([
                        'set_number' => $setData['set_number'],
                        'reps' => $setData['reps'] ?? null,
                        'weight' => $setData['weight'] ?? null,
                        'duration_seconds' => $setData['duration_seconds'] ?? null,
                        'distance_meters' => $setData['distance_meters'] ?? null,
                        'rest_seconds' => $setData['rest_seconds'] ?? null,
                        'notes' => $setData['notes'] ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Agregar días a plantilla semanal
     */
    private function addDaysToWeeklyTemplate(WeeklyTemplate $template, array $days): void
    {
        foreach ($days as $dayData) {
            $template->days()->create([
                'day_of_week' => $dayData['day_of_week'],
                'daily_template_id' => $dayData['daily_template_id'],
                'is_rest_day' => $dayData['is_rest_day'] ?? false,
                'notes' => $dayData['notes'] ?? null,
                'order' => $dayData['order'] ?? null,
            ]);
        }
    }

    /**
     * Limpiar cache relacionado con plantillas
     */
    private function clearTemplateCache(): void
    {
        // Build array of all cache keys to clear
        $keys = ['template_stats'];

        // Add most used template keys (various possible limits)
        for ($i = 1; $i <= 20; $i++) {
            $keys[] = "most_used_daily_templates_{$i}";
            $keys[] = "most_used_weekly_templates_{$i}";
        }

        // Clear all keys at once
        $this->cacheService->forget($keys);

        // Limpiar cache de consultas filtradas
        $this->clearFilteredTemplatesCache();
    }

    /**
     * Limpiar cache de consultas filtradas (driver-agnostic)
     */
    private function clearFilteredTemplatesCache(): void
    {
        // Use driver-agnostic pattern clearing
        $this->cacheService->clearByPattern('*templates_*');
    }

    /**
     * Obtener plantillas populares (con cache optimizado)
     */
    public function getPopularDailyTemplates(int $limit = 10): Collection
    {
        return $this->cacheService->rememberFilters("popular_daily_templates_{$limit}", function () use ($limit) {
            return DailyTemplate::where('is_preset', true)
                ->with(['exercises.exercise'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtener plantillas recientes (con cache)
     */
    public function getRecentDailyTemplates(int $limit = 5): Collection
    {
        return $this->cacheService->rememberList("recent_daily_templates_{$limit}", function () use ($limit) {
            return DailyTemplate::with(['exercises.exercise'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }
}
