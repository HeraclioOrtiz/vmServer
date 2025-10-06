<?php

namespace App\Services\Gym;

use App\Models\Gym\DailyTemplateSet;
use App\Models\Gym\DailyTemplateExercise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SetService
{
    /**
     * Crear sets para un ejercicio de plantilla
     */
    public function createSetsForExercise(DailyTemplateExercise $templateExercise, array $setsData): Collection
    {
        $sets = collect();
        
        foreach ($setsData as $index => $setData) {
            $set = $this->createSet($templateExercise, $setData, $index + 1);
            $sets->push($set);
        }
        
        return $sets;
    }

    /**
     * Crear un set individual
     */
    public function createSet(DailyTemplateExercise $templateExercise, array $data, int $setNumber = 1): DailyTemplateSet
    {
        $validated = $this->validateSetData($data);
        
        return DailyTemplateSet::create([
            'daily_template_exercise_id' => $templateExercise->id,
            'set_number' => $setNumber,
            'reps_min' => $validated['reps_min'] ?? null,
            'reps_max' => $validated['reps_max'] ?? null,
            'weight_min' => $validated['weight_min'] ?? null,
            'weight_max' => $validated['weight_max'] ?? null,
            'weight_target' => $validated['weight_target'] ?? null,
            'rpe_target' => $validated['rpe_target'] ?? null,
            'rest_seconds' => $validated['rest_seconds'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
    }

    /**
     * Actualizar sets de un ejercicio de plantilla
     */
    public function updateSetsForExercise(DailyTemplateExercise $templateExercise, array $setsData): Collection
    {
        return DB::transaction(function () use ($templateExercise, $setsData) {
            // Eliminar sets existentes
            $templateExercise->sets()->delete();
            
            // Crear nuevos sets
            return $this->createSetsForExercise($templateExercise, $setsData);
        });
    }

    /**
     * Actualizar un set específico
     */
    public function updateSet(DailyTemplateSet $set, array $data): DailyTemplateSet
    {
        $validated = $this->validateSetData($data);
        
        $set->update([
            'set_number' => $validated['set_number'] ?? $set->set_number,
            'reps_min' => $validated['reps_min'] ?? $set->reps_min,
            'reps_max' => $validated['reps_max'] ?? $set->reps_max,
            'weight_min' => $validated['weight_min'] ?? $set->weight_min,
            'weight_max' => $validated['weight_max'] ?? $set->weight_max,
            'weight_target' => $validated['weight_target'] ?? $set->weight_target,
            'rpe_target' => $validated['rpe_target'] ?? $set->rpe_target,
            'rest_seconds' => $validated['rest_seconds'] ?? $set->rest_seconds,
            'notes' => $validated['notes'] ?? $set->notes,
        ]);
        
        return $set->fresh();
    }

    /**
     * Eliminar un set
     */
    public function deleteSet(DailyTemplateSet $set): bool
    {
        return $set->delete();
    }

    /**
     * Eliminar todos los sets de un ejercicio
     */
    public function deleteSetsForExercise(DailyTemplateExercise $templateExercise): int
    {
        return $templateExercise->sets()->delete();
    }

    /**
     * Duplicar sets de un ejercicio a otro
     */
    public function duplicateSets(DailyTemplateExercise $sourceExercise, DailyTemplateExercise $targetExercise): Collection
    {
        $sets = collect();
        
        foreach ($sourceExercise->sets as $sourceSet) {
            $duplicated = $this->createSet($targetExercise, [
                'reps_min' => $sourceSet->reps_min,
                'reps_max' => $sourceSet->reps_max,
                'rpe_target' => $sourceSet->rpe_target,
                'rest_seconds' => $sourceSet->rest_seconds,
                'notes' => $sourceSet->notes,
            ], $sourceSet->set_number);
            
            $sets->push($duplicated);
        }
        
        return $sets;
    }

    /**
     * Reordenar sets de un ejercicio
     */
    public function reorderSets(DailyTemplateExercise $templateExercise, array $setIds): bool
    {
        return DB::transaction(function () use ($templateExercise, $setIds) {
            foreach ($setIds as $index => $setId) {
                DailyTemplateSet::where('id', $setId)
                    ->where('daily_template_exercise_id', $templateExercise->id)
                    ->update(['set_number' => $index + 1]);
            }
            return true;
        });
    }

    /**
     * Validar datos de un set
     */
    private function validateSetData(array $data): array
    {
        $validated = [];
        
        // set_number
        if (isset($data['set_number'])) {
            $validated['set_number'] = max(1, (int)$data['set_number']);
        }
        
        // reps_min
        if (isset($data['reps_min'])) {
            $validated['reps_min'] = is_numeric($data['reps_min']) && $data['reps_min'] > 0 
                ? (int)$data['reps_min'] 
                : null;
        }
        
        // reps_max
        if (isset($data['reps_max'])) {
            $validated['reps_max'] = is_numeric($data['reps_max']) && $data['reps_max'] > 0 
                ? (int)$data['reps_max'] 
                : null;
        }
        
        // rpe_target (0-10 con decimales)
        if (isset($data['rpe_target'])) {
            $rpe = (float)$data['rpe_target'];
            $validated['rpe_target'] = $rpe >= 0 && $rpe <= 10 ? $rpe : null;
        }
        
        // rest_seconds
        if (isset($data['rest_seconds'])) {
            $validated['rest_seconds'] = is_numeric($data['rest_seconds']) && $data['rest_seconds'] >= 0 
                ? (int)$data['rest_seconds'] 
                : null;
        }
        
        // weight_min
        if (isset($data['weight_min'])) {
            $validated['weight_min'] = is_numeric($data['weight_min']) && $data['weight_min'] >= 0 
                ? (float)$data['weight_min'] 
                : null;
        }
        
        // weight_max
        if (isset($data['weight_max'])) {
            $validated['weight_max'] = is_numeric($data['weight_max']) && $data['weight_max'] >= 0 
                ? (float)$data['weight_max'] 
                : null;
        }
        
        // weight_target
        if (isset($data['weight_target'])) {
            $validated['weight_target'] = is_numeric($data['weight_target']) && $data['weight_target'] >= 0 
                ? (float)$data['weight_target'] 
                : null;
        }
        
        // notes
        if (isset($data['notes'])) {
            $validated['notes'] = is_string($data['notes']) ? trim($data['notes']) : null;
        }
        
        return $validated;
    }

    /**
     * Obtener estadísticas de sets para un ejercicio
     */
    public function getSetsStats(DailyTemplateExercise $templateExercise): array
    {
        $sets = $templateExercise->sets;
        
        if ($sets->isEmpty()) {
            return [
                'total_sets' => 0,
                'avg_reps' => null,
                'avg_rest' => null,
                'avg_rpe' => null,
            ];
        }
        
        return [
            'total_sets' => $sets->count(),
            'avg_reps' => round(($sets->avg('reps_min') + $sets->avg('reps_max')) / 2, 1),
            'avg_rest' => round($sets->avg('rest_seconds')),
            'avg_rpe' => round($sets->avg('rpe_target'), 1),
            'total_volume' => $sets->sum(function ($set) {
                return ($set->reps_min + $set->reps_max) / 2;
            }),
        ];
    }

    /**
     * Validar consistencia de sets (reps_min <= reps_max)
     */
    public function validateSetConsistency(array $data): array
    {
        $errors = [];
        
        if (isset($data['reps_min']) && isset($data['reps_max'])) {
            if ($data['reps_min'] > $data['reps_max']) {
                $errors[] = 'reps_min no puede ser mayor que reps_max';
            }
        }
        
        if (isset($data['rpe_target'])) {
            if ($data['rpe_target'] < 0 || $data['rpe_target'] > 10) {
                $errors[] = 'rpe_target debe estar entre 0 y 10';
            }
        }
        
        return $errors;
    }

    /**
     * Crear sets predeterminados basados en objetivo
     */
    public function createDefaultSets(DailyTemplateExercise $templateExercise, string $goal = 'strength'): Collection
    {
        $defaults = $this->getDefaultSetsByGoal($goal);
        return $this->createSetsForExercise($templateExercise, $defaults);
    }

    /**
     * Obtener configuración predeterminada de sets según objetivo
     */
    private function getDefaultSetsByGoal(string $goal): array
    {
        return match($goal) {
            'strength' => [
                ['reps_min' => 3, 'reps_max' => 5, 'rpe_target' => 8.5, 'rest_seconds' => 180],
                ['reps_min' => 3, 'reps_max' => 5, 'rpe_target' => 9.0, 'rest_seconds' => 180],
                ['reps_min' => 3, 'reps_max' => 5, 'rpe_target' => 9.5, 'rest_seconds' => 180],
            ],
            'hypertrophy' => [
                ['reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 8.0, 'rest_seconds' => 90],
                ['reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 8.5, 'rest_seconds' => 90],
                ['reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 9.0, 'rest_seconds' => 90],
            ],
            'endurance' => [
                ['reps_min' => 15, 'reps_max' => 20, 'rpe_target' => 7.0, 'rest_seconds' => 60],
                ['reps_min' => 15, 'reps_max' => 20, 'rpe_target' => 7.5, 'rest_seconds' => 60],
                ['reps_min' => 15, 'reps_max' => 20, 'rpe_target' => 8.0, 'rest_seconds' => 60],
            ],
            default => [
                ['reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 7.5, 'rest_seconds' => 90],
                ['reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 8.0, 'rest_seconds' => 90],
                ['reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 8.5, 'rest_seconds' => 90],
            ],
        };
    }
}
