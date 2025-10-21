<?php

namespace App\Http\Controllers\Gym\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\StoreExerciseRequest;
use App\Http\Requests\Gym\UpdateExerciseRequest;
use App\Models\Gym\Exercise;
use App\Services\Gym\ExerciseService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExerciseController extends Controller
{
    public function __construct(
        private ExerciseService $exerciseService
    ) {}
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'muscle_groups', 'target_muscle_groups',
            'equipment', 'difficulty_level', 'movement_pattern',
            'tags', 'sort_by', 'sort_direction'
        ]);
        
        $perPage = min($request->integer('per_page', 20), 100);
        
        $exercises = $this->exerciseService->getFilteredExercises($filters, $perPage);
        
        return response()->json($exercises);
    }

    public function store(StoreExerciseRequest $request)
    {
        $exercise = $this->exerciseService->createExercise($request->validated(), $request->user());
        return response()->json($exercise, 201);
    }

    public function show(Exercise $exercise)
    {
        return response()->json($exercise);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        $exercise = $this->exerciseService->updateExercise($exercise, $request->validated(), $request->user());
        return response()->json($exercise);
    }

    public function destroy(Exercise $exercise)
    {
        $this->exerciseService->deleteExercise($exercise, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio eliminado correctamente'
        ], 200);
    }

    public function checkDependencies(Exercise $exercise)
    {
        $dependencies = $this->exerciseService->checkExerciseDependencies($exercise);
        return response()->json($dependencies);
    }

    public function forceDestroy(Exercise $exercise)
    {
        $result = $this->exerciseService->forceDeleteExercise($exercise, auth()->user());
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
                'details' => $result['details'] ?? null
            ], $result['status_code']);
        }
        
        $response = ['message' => $result['message']];
        if (isset($result['warning'])) {
            $response['warning'] = $result['warning'];
        }
        
        return response()->json($response, $result['status_code']);
    }

    public function duplicate(Exercise $exercise)
    {
        $duplicated = $this->exerciseService->duplicateExercise($exercise, auth()->user());
        return response()->json($duplicated, 201);
    }
}
