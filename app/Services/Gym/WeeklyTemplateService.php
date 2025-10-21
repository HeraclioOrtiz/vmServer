<?php

namespace App\Services\Gym;

use App\Models\Gym\WeeklyTemplate;
use App\Services\Core\AuditService;
use App\Services\Core\CacheService;
use App\Utils\QueryFilterBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Weekly Template Service
 *
 * Handles CRUD operations for weekly workout templates
 */
class WeeklyTemplateService
{
    public function __construct(
        private AuditService $auditService,
        private CacheService $cacheService
    ) {}

    /**
     * Get filtered weekly templates
     */
    public function getFiltered(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = WeeklyTemplate::query();

        $this->applyFilters($query, $filters);

        // Default sorting: presets first, then by title
        $query->orderByDesc('is_preset')->orderBy('title');

        return $query->paginate($perPage);
    }

    /**
     * Create a new weekly template
     */
    public function create(array $data, $user): WeeklyTemplate
    {
        return DB::transaction(function () use ($data, $user) {
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

            // Add days if provided
            if (!empty($data['days'])) {
                $this->addDays($template, $data['days']);
            }

            // Audit log
            $this->auditService->log(
                action: 'create',
                resourceType: 'weekly_template',
                resourceId: $template->id,
                details: [
                    'title' => $template->title,
                    'category' => $template->category,
                    'sessions_per_week' => $template->sessions_per_week,
                ],
                severity: 'low',
                category: 'gym'
            );

            $this->clearCache();

            return $template->load('days');
        });
    }

    /**
     * Update a weekly template
     */
    public function update(WeeklyTemplate $template, array $data, $user): WeeklyTemplate
    {
        return DB::transaction(function () use ($template, $data, $user) {
            $oldValues = $template->toArray();

            $template->update([
                'title' => $data['title'] ?? $template->title,
                'description' => $data['description'] ?? $template->description,
                'category' => $data['category'] ?? $template->category,
                'difficulty_level' => $data['difficulty_level'] ?? $template->difficulty_level,
                'weeks_duration' => $data['weeks_duration'] ?? $template->weeks_duration,
                'sessions_per_week' => $data['sessions_per_week'] ?? $template->sessions_per_week,
                'target_goals' => $data['target_goals'] ?? $template->target_goals,
                'target_muscle_groups' => $data['target_muscle_groups'] ?? $template->target_muscle_groups,
                'equipment_needed' => $data['equipment_needed'] ?? $template->equipment_needed,
                'is_preset' => $data['is_preset'] ?? $template->is_preset,
                'is_public' => $data['is_public'] ?? $template->is_public,
                'tags' => $data['tags'] ?? $template->tags,
                'notes' => $data['notes'] ?? $template->notes,
                'progression' => $data['progression'] ?? $template->progression,
            ]);

            // Update days if provided
            if (isset($data['days'])) {
                // Remove old days
                $template->days()->delete();
                // Add new days
                $this->addDays($template, $data['days']);
            }

            // Audit log
            $this->auditService->log(
                action: 'update',
                resourceType: 'weekly_template',
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

            $this->clearCache();

            return $template->load('days');
        });
    }

    /**
     * Delete a weekly template
     */
    public function delete(WeeklyTemplate $template, $user): void
    {
        DB::transaction(function () use ($template, $user) {
            // Audit log
            $this->auditService->log(
                action: 'delete',
                resourceType: 'weekly_template',
                resourceId: $template->id,
                details: [
                    'title' => $template->title,
                ],
                severity: 'medium',
                category: 'gym'
            );

            $template->delete();

            $this->clearCache();
        });
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        // Search filter (title, description, tags)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }

        // Category filter
        QueryFilterBuilder::applyExact($query, $filters['category'] ?? null, 'category');

        // Difficulty level filter
        QueryFilterBuilder::applyExact($query, $filters['difficulty_level'] ?? null, 'difficulty_level');

        // Target goals filter (JSON array)
        QueryFilterBuilder::applyJsonContains($query, $filters['target_goals'] ?? null, 'target_goals');

        // Preset filter
        QueryFilterBuilder::applyBoolean($query, $filters['is_preset'] ?? null, 'is_preset');
    }

    /**
     * Add days to weekly template
     */
    private function addDays(WeeklyTemplate $template, array $days): void
    {
        foreach ($days as $dayData) {
            $template->days()->create([
                'day_number' => $dayData['day_number'],
                'daily_template_id' => $dayData['daily_template_id'] ?? null,
                'is_rest_day' => $dayData['is_rest_day'] ?? false,
                'notes' => $dayData['notes'] ?? null,
            ]);
        }
    }

    /**
     * Clear template cache
     */
    private function clearCache(): void
    {
        $this->cacheService->clearByPattern('*weekly_templates*');
        $this->cacheService->clearByPattern('*most_used_weekly*');
        $this->cacheService->forget('template_stats');
    }
}
