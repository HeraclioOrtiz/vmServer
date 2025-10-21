<?php

namespace App\Services\Gym;

use App\Models\Gym\Exercise;
use App\Services\Core\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Exercise Statistics Service
 *
 * Handles statistics and reporting for exercises
 */
class ExerciseStatsService
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Get comprehensive exercise statistics
     */
    public function getStats(): array
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
                'most_used' => $this->getMostUsed(5),
                'recent_additions' => Exercise::where('created_at', '>=', now()->subDays(30))->count(),
            ];
        });
    }

    /**
     * Get most used exercises
     */
    public function getMostUsed(int $limit = 10): Collection
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
     * Get available filter options
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
     * Clear statistics cache
     */
    public function clearCache(): void
    {
        $this->cacheService->forget('exercise_stats');

        // Clear most used caches
        for ($i = 1; $i <= 20; $i++) {
            $this->cacheService->forget("most_used_exercises_{$i}");
        }

        // Clear filter options cache
        $this->cacheService->forget('exercise_filter_options');
    }
}
