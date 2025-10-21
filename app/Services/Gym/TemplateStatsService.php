<?php

namespace App\Services\Gym;

use App\Models\Gym\DailyTemplate;
use App\Models\Gym\WeeklyTemplate;
use App\Services\Core\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Template Statistics Service
 *
 * Handles statistics and reporting for templates
 */
class TemplateStatsService
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Get comprehensive template statistics
     */
    public function getStats(): array
    {
        return $this->cacheService->rememberStats('template_stats', function () {
            return [
                'daily_templates' => $this->getDailyTemplateStats(),
                'weekly_templates' => $this->getWeeklyTemplateStats(),
                'most_used_daily' => $this->getMostUsedDailyTemplates(5),
                'most_used_weekly' => $this->getMostUsedWeeklyTemplates(5),
            ];
        });
    }

    /**
     * Get most used daily templates
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
     * Get most used weekly templates
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

    /**
     * Clear statistics cache
     */
    public function clearCache(): void
    {
        $this->cacheService->forget('template_stats');

        // Clear most used caches
        for ($i = 1; $i <= 20; $i++) {
            $this->cacheService->forget("most_used_daily_templates_{$i}");
            $this->cacheService->forget("most_used_weekly_templates_{$i}");
        }
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Get daily template statistics
     */
    private function getDailyTemplateStats(): array
    {
        return [
            'total' => DailyTemplate::count(),
            'presets' => DailyTemplate::where('is_preset', true)->count(),
            'public' => DailyTemplate::where('is_public', true)->count(),
            'by_goal' => DailyTemplate::select('goal', DB::raw('count(*) as count'))
                ->whereNotNull('goal')
                ->groupBy('goal')
                ->pluck('count', 'goal')
                ->toArray(),
            'by_level' => DailyTemplate::select('level', DB::raw('count(*) as count'))
                ->whereNotNull('level')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray(),
        ];
    }

    /**
     * Get weekly template statistics
     */
    private function getWeeklyTemplateStats(): array
    {
        return [
            'total' => WeeklyTemplate::count(),
            'presets' => WeeklyTemplate::where('is_preset', true)->count(),
            'public' => WeeklyTemplate::where('is_public', true)->count(),
            'by_category' => WeeklyTemplate::select('category', DB::raw('count(*) as count'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_difficulty' => WeeklyTemplate::select('difficulty_level', DB::raw('count(*) as count'))
                ->whereNotNull('difficulty_level')
                ->groupBy('difficulty_level')
                ->pluck('count', 'difficulty_level')
                ->toArray(),
        ];
    }
}
