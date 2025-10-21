<?php

namespace App\Services\Gym;

use App\Models\Gym\DailyTemplate;
use App\Services\Core\AuditService;
use App\Services\Core\CacheService;
use App\Utils\QueryFilterBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Daily Template Service
 *
 * Handles CRUD operations for daily workout templates
 */
class DailyTemplateService
{
    public function __construct(
        private AuditService $auditService,
        private CacheService $cacheService
    ) {}

    /**
     * Get filtered daily templates with caching
     */
    public function getFiltered(array $filters, int $perPage = 20, array $includes = []): LengthAwarePaginator
    {
        // Generate cache key
        $cacheKey = $this->generateCacheKey($filters, $perPage, $includes);

        // Use cache for simple queries
        if ($this->shouldUseCache($filters)) {
            return $this->cacheService->rememberStats($cacheKey, function () use ($filters, $perPage, $includes) {
                return $this->buildQuery($filters, $perPage, $includes);
            });
        }

        // No cache for complex queries
        return $this->buildQuery($filters, $perPage, $includes);
    }

    /**
     * Create a new daily template
     */
    public function create(array $data, $user): DailyTemplate
    {
        return DB::transaction(function () use ($data, $user) {
            $template = DailyTemplate::create([
                'title' => $data['title'],
                'goal' => $data['goal'] ?? null,
                'level' => $data['level'] ?? 'intermediate',
                'estimated_duration_min' => $data['estimated_duration_min'] ?? null,
                'is_preset' => $data['is_preset'] ?? false,
                'is_public' => $data['is_public'] ?? false,
                'tags' => $data['tags'] ?? [],
                'created_by' => $user->id,
            ]);

            // Audit log
            $this->auditService->log(
                action: 'create',
                resourceType: 'daily_template',
                resourceId: $template->id,
                details: [
                    'title' => $template->title,
                    'goal' => $template->goal,
                    'level' => $template->level,
                ],
                severity: 'low',
                category: 'gym'
            );

            // Clear cache
            $this->clearCache();

            return $template->load('exercises.exercise');
        });
    }

    /**
     * Update a daily template
     */
    public function update(DailyTemplate $template, array $data, $user): DailyTemplate
    {
        return DB::transaction(function () use ($template, $data, $user) {
            $oldValues = $template->toArray();

            $template->update([
                'title' => $data['title'] ?? $template->title,
                'goal' => $data['goal'] ?? $template->goal,
                'level' => $data['level'] ?? $template->level,
                'estimated_duration_min' => $data['estimated_duration_min'] ?? $template->estimated_duration_min,
                'is_preset' => $data['is_preset'] ?? $template->is_preset,
                'is_public' => $data['is_public'] ?? $template->is_public,
                'tags' => $data['tags'] ?? $template->tags,
            ]);

            // Audit log
            $this->auditService->log(
                action: 'update',
                resourceType: 'daily_template',
                resourceId: $template->id,
                details: [
                    'title' => $template->title,
                    'changes' => array_keys($data),
                ],
                oldValues: $oldValues,
                newValues: $template->fresh()->toArray(),
                severity: 'low',
                category: 'gym'
            );

            // Clear cache
            $this->clearCache();

            return $template->load('exercises.exercise');
        });
    }

    /**
     * Duplicate a daily template
     */
    public function duplicate(DailyTemplate $template, $user): DailyTemplate
    {
        return DB::transaction(function () use ($template, $user) {
            $duplicated = $template->replicate();
            $duplicated->title = $template->title . ' (Copia)';
            $duplicated->created_by = $user->id;
            $duplicated->is_preset = false;
            $duplicated->save();

            // Duplicate exercises
            foreach ($template->exercises as $exercise) {
                $duplicatedExercise = $exercise->replicate();
                $duplicatedExercise->daily_template_id = $duplicated->id;
                $duplicatedExercise->save();

                // Duplicate sets
                foreach ($exercise->sets as $set) {
                    $duplicatedSet = $set->replicate();
                    $duplicatedSet->template_exercise_id = $duplicatedExercise->id;
                    $duplicatedSet->save();
                }
            }

            // Audit log
            $this->auditService->log(
                action: 'duplicate',
                resourceType: 'daily_template',
                resourceId: $duplicated->id,
                details: [
                    'original_title' => $template->title,
                    'new_title' => $duplicated->title,
                    'original_id' => $template->id,
                ],
                severity: 'low',
                category: 'gym'
            );

            return $duplicated->load('exercises.exercise');
        });
    }

    /**
     * Get popular daily templates (presets)
     */
    public function getPopular(int $limit = 10): \Illuminate\Database\Eloquent\Collection
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
     * Get recent daily templates
     */
    public function getRecent(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->cacheService->rememberList("recent_daily_templates_{$limit}", function () use ($limit) {
            return DailyTemplate::with(['exercises.exercise'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Build query with filters
     */
    private function buildQuery(array $filters, int $perPage, array $includes): LengthAwarePaginator
    {
        $query = DailyTemplate::query();

        $this->applyFilters($query, $filters);

        if (!empty($includes)) {
            $query->with($includes);
        }

        $this->applySorting($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        $searchTerm = $filters['search'] ?? $filters['q'] ?? null;
        QueryFilterBuilder::applySearch($query, $searchTerm, ['title', 'tags_json']);

        QueryFilterBuilder::applyWithAliases($query, $filters, ['primary_goal', 'goal'], 'goal');
        QueryFilterBuilder::applyWithAliases($query, $filters, ['difficulty', 'level'], 'level');

        QueryFilterBuilder::applyJsonContains($query, $filters['target_muscle_groups'] ?? null, 'tags');
        QueryFilterBuilder::applyJsonContains($query, $filters['equipment_needed'] ?? null, 'tags');
        QueryFilterBuilder::applyJsonContains($query, $filters['tags'] ?? null, 'tags');

        QueryFilterBuilder::applyBoolean($query, $filters['is_preset'] ?? null, 'is_preset');

        QueryFilterBuilder::applyRange(
            $query,
            $filters['duration_min'] ?? null,
            $filters['duration_max'] ?? null,
            'estimated_duration_min'
        );
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Special default order: presets first, then by date
        if ($sortBy === 'created_at' && $sortDirection === 'desc') {
            $query->orderByDesc('is_preset')->orderByDesc('created_at')->orderBy('title');
        } else {
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
     * Determine if cache should be used
     */
    private function shouldUseCache(array $filters): bool
    {
        if (!empty($filters['search']) || !empty($filters['q'])) {
            return false;
        }

        $complexFilters = ['target_muscle_groups', 'equipment_needed', 'tags'];
        foreach ($complexFilters as $filter) {
            if (!empty($filters[$filter])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey(array $filters, int $perPage, array $includes): string
    {
        $keyData = [
            'type' => 'daily_templates',
            'filters' => array_filter($filters),
            'per_page' => $perPage,
            'includes' => $includes,
        ];

        return 'templates_' . md5(serialize($keyData));
    }

    /**
     * Clear template cache
     */
    private function clearCache(): void
    {
        $this->cacheService->clearByPattern('*daily_templates*');
        $this->cacheService->clearByPattern('*popular_daily*');
        $this->cacheService->clearByPattern('*recent_daily*');
    }
}
