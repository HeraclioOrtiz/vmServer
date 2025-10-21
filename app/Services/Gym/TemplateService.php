<?php

namespace App\Services\Gym;

use App\Models\Gym\DailyTemplate;
use App\Models\Gym\WeeklyTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Template Service Facade
 *
 * Delegates operations to specialized services:
 * - DailyTemplateService: Daily template CRUD
 * - WeeklyTemplateService: Weekly template CRUD
 * - TemplateStatsService: Statistics and reporting
 *
 * This facade maintains backward compatibility while providing
 * better separation of concerns.
 */
class TemplateService
{
    public function __construct(
        private DailyTemplateService $dailyTemplateService,
        private WeeklyTemplateService $weeklyTemplateService,
        private TemplateStatsService $templateStatsService
    ) {}

    // ==================== DAILY TEMPLATES ====================

    /**
     * Get filtered daily templates
     */
    public function getFilteredDailyTemplates(array $filters, int $perPage = 20, array $includes = []): LengthAwarePaginator
    {
        return $this->dailyTemplateService->getFiltered($filters, $perPage, $includes);
    }

    /**
     * Create a daily template
     */
    public function createDailyTemplate(array $data, $user): DailyTemplate
    {
        return $this->dailyTemplateService->create($data, $user);
    }

    /**
     * Update a daily template
     */
    public function updateDailyTemplate(DailyTemplate $template, array $data, $user): DailyTemplate
    {
        return $this->dailyTemplateService->update($template, $data, $user);
    }

    /**
     * Duplicate a daily template
     */
    public function duplicateDailyTemplate(DailyTemplate $template, $user): DailyTemplate
    {
        return $this->dailyTemplateService->duplicate($template, $user);
    }

    /**
     * Get popular daily templates
     */
    public function getPopularDailyTemplates(int $limit = 10): Collection
    {
        return $this->dailyTemplateService->getPopular($limit);
    }

    /**
     * Get recent daily templates
     */
    public function getRecentDailyTemplates(int $limit = 5): Collection
    {
        return $this->dailyTemplateService->getRecent($limit);
    }

    // ==================== WEEKLY TEMPLATES ====================

    /**
     * Get filtered weekly templates
     */
    public function getFilteredWeeklyTemplates(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->weeklyTemplateService->getFiltered($filters, $perPage);
    }

    /**
     * Create a weekly template
     */
    public function createWeeklyTemplate(array $data, $user): WeeklyTemplate
    {
        return $this->weeklyTemplateService->create($data, $user);
    }

    /**
     * Update a weekly template
     */
    public function updateWeeklyTemplate(WeeklyTemplate $template, array $data, $user): WeeklyTemplate
    {
        return $this->weeklyTemplateService->update($template, $data, $user);
    }

    /**
     * Delete a weekly template
     */
    public function deleteWeeklyTemplate(WeeklyTemplate $template, $user): void
    {
        $this->weeklyTemplateService->delete($template, $user);
    }

    // ==================== STATISTICS ====================

    /**
     * Get template statistics
     */
    public function getTemplateStats(): array
    {
        return $this->templateStatsService->getStats();
    }

    /**
     * Get most used daily templates
     */
    public function getMostUsedDailyTemplates(int $limit = 10): Collection
    {
        return $this->templateStatsService->getMostUsedDailyTemplates($limit);
    }

    /**
     * Get most used weekly templates
     */
    public function getMostUsedWeeklyTemplates(int $limit = 10): Collection
    {
        return $this->templateStatsService->getMostUsedWeeklyTemplates($limit);
    }
}
