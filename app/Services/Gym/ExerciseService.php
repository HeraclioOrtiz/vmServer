<?php

namespace App\Services\Gym;

use App\Models\Gym\Exercise;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Exercise Service Facade
 *
 * Delegates operations to specialized services:
 * - ExerciseCrudService: Exercise CRUD operations
 * - ExerciseStatsService: Statistics and reporting
 *
 * This facade maintains backward compatibility while providing
 * better separation of concerns.
 */
class ExerciseService
{
    public function __construct(
        private ExerciseCrudService $crudService,
        private ExerciseStatsService $statsService
    ) {}

    // ==================== CRUD OPERATIONS ====================

    /**
     * Get filtered exercises
     */
    public function getFilteredExercises(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->crudService->getFiltered($filters, $perPage);
    }

    /**
     * Create a new exercise
     */
    public function createExercise(array $data, $user): Exercise
    {
        return $this->crudService->create($data, $user);
    }

    /**
     * Update an exercise
     */
    public function updateExercise(Exercise $exercise, array $data, $user): Exercise
    {
        return $this->crudService->update($exercise, $data, $user);
    }

    /**
     * Duplicate an exercise
     */
    public function duplicateExercise(Exercise $exercise, $user): Exercise
    {
        return $this->crudService->duplicate($exercise, $user);
    }

    /**
     * Delete an exercise
     */
    public function deleteExercise(Exercise $exercise, $user): Exercise
    {
        return $this->crudService->delete($exercise, $user);
    }

    /**
     * Force delete an exercise
     */
    public function forceDeleteExercise(Exercise $exercise, $user): array
    {
        return $this->crudService->forceDelete($exercise, $user);
    }

    /**
     * Check exercise dependencies
     */
    public function checkExerciseDependencies(Exercise $exercise): array
    {
        return $this->crudService->checkDependencies($exercise);
    }

    // ==================== STATISTICS ====================

    /**
     * Get exercise statistics
     */
    public function getExerciseStats(): array
    {
        return $this->statsService->getStats();
    }

    /**
     * Get most used exercises
     */
    public function getMostUsedExercises(int $limit = 10): Collection
    {
        return $this->statsService->getMostUsed($limit);
    }

    /**
     * Get filter options
     */
    public function getFilterOptions(): array
    {
        return $this->statsService->getFilterOptions();
    }
}
