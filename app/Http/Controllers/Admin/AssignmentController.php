<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Gym\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignmentService
    ) {}

    /**
     * Lista todas las asignaciones profesor-estudiante
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $assignments = $this->assignmentService->getAllProfessorStudentAssignments(
                $this->buildFilters($request)
            );

            return response()->json($assignments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener asignaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva asignación profesor-estudiante
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'professor_id' => 'required|exists:users,id',
                'student_id' => 'required|exists:users,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
                'admin_notes' => 'nullable|string|max:1000'
            ]);

            $validated['assigned_by'] = auth()->id();

            $assignment = $this->assignmentService->assignStudentToProfessor($validated);

            return response()->json([
                'message' => 'Estudiante asignado exitosamente',
                'data' => $assignment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear asignación',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mostrar asignación específica
     */
    public function show($id): JsonResponse
    {
        try {
            $assignment = \App\Models\Gym\ProfessorStudentAssignment::with([
                'professor',
                'student', 
                'assignedBy',
                'templateAssignments.dailyTemplate',
                'templateAssignments.progress'
            ])->findOrFail($id);

            return response()->json($assignment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Asignación no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar asignación
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'end_date' => 'nullable|date|after:start_date',
                'admin_notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|in:active,paused,completed,cancelled'
            ]);

            $assignment = \App\Models\Gym\ProfessorStudentAssignment::findOrFail($id);
            $assignment->update($validated);

            return response()->json([
                'message' => 'Asignación actualizada exitosamente',
                'data' => $assignment->fresh(['professor', 'student', 'assignedBy'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar asignación',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Pausar asignación
     */
    public function pause($id): JsonResponse
    {
        try {
            $assignment = $this->assignmentService->pauseProfessorStudentAssignment($id);

            return response()->json([
                'message' => 'Asignación pausada exitosamente',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al pausar asignación',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Reactivar asignación
     */
    public function reactivate($id): JsonResponse
    {
        try {
            $assignment = $this->assignmentService->reactivateProfessorStudentAssignment($id);

            return response()->json([
                'message' => 'Asignación reactivada exitosamente',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al reactivar asignación',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Completar asignación
     */
    public function complete($id): JsonResponse
    {
        try {
            $assignment = $this->assignmentService->completeProfessorStudentAssignment($id);

            return response()->json([
                'message' => 'Asignación completada exitosamente',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al completar asignación',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener estudiantes de un profesor específico
     */
    public function professorStudents($professorId): JsonResponse
    {
        try {
            $students = $this->assignmentService->getProfessorStudents($professorId);

            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estudiantes del profesor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estudiantes sin asignar
     */
    public function unassignedStudents(): JsonResponse
    {
        try {
            $students = $this->assignmentService->getUnassignedStudents();

            return response()->json([
                'data' => $students,
                'count' => $students->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estudiantes sin asignar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas generales
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->assignmentService->getGeneralStats();

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Construir filtros desde request
     */
    private function buildFilters(Request $request): array
    {
        return array_filter([
            'professor_id' => $request->integer('professor_id') ?: null,
            'student_id' => $request->integer('student_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ]);
    }
}
