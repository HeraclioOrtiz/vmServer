<?php

namespace App\Services\Gym;

use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use App\Models\Gym\AssignmentProgress;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AssignmentService
{
    /**
     * ADMIN FUNCTIONS - Gestión de asignaciones profesor-estudiante
     */

    /**
     * Asignar estudiante(s) a un profesor
     */
    public function assignStudentToProfessor(array $data): ProfessorStudentAssignment
    {
        return DB::transaction(function () use ($data) {
            // Validar que el estudiante no esté ya asignado activamente
            $existingAssignment = ProfessorStudentAssignment::where('student_id', $data['student_id'])
                ->where('status', 'active')
                ->first();

            if ($existingAssignment) {
                throw new \Exception("El estudiante ya está asignado al profesor {$existingAssignment->professor->name}");
            }

            // Validar que el profesor existe y tiene rol correcto
            $professor = User::find($data['professor_id']);
            if (!$professor || !$professor->is_professor) {
                throw new \Exception('Profesor no válido');
            }

            // Validar que el estudiante existe y tiene rol correcto
            $student = User::find($data['student_id']);
            if (!$student || $student->is_professor || $student->is_admin) {
                throw new \Exception('Estudiante no válido');
            }

            // Crear asignación
            $assignment = ProfessorStudentAssignment::create($data);

            // TODO: Enviar notificación al profesor
            // $this->notifyProfessorNewStudent($assignment);

            return $assignment->load(['professor', 'student', 'assignedBy']);
        });
    }

    /**
     * Obtener todas las asignaciones profesor-estudiante (Admin)
     */
    public function getAllProfessorStudentAssignments(array $filters = []): LengthAwarePaginator
    {
        $query = ProfessorStudentAssignment::with(['professor', 'student', 'assignedBy']);

        // Aplicar filtros
        if (!empty($filters['professor_id'])) {
            $query->where('professor_id', $filters['professor_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->whereHas('professor', function($subQ) use ($filters) {
                    $subQ->where('name', 'like', "%{$filters['search']}%")
                         ->orWhere('email', 'like', "%{$filters['search']}%");
                })->orWhereHas('student', function($subQ) use ($filters) {
                    $subQ->where('name', 'like', "%{$filters['search']}%")
                         ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Obtener estudiantes de un profesor específico
     */
    public function getProfessorStudents($professorId, array $filters = []): LengthAwarePaginator
    {
        $query = ProfessorStudentAssignment::with(['student', 'assignedBy', 'templateAssignments.dailyTemplate'])
            ->where('professor_id', $professorId);

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Obtener estudiantes sin asignar
     */
    public function getUnassignedStudents(): Collection
    {
        $assignedStudentIds = ProfessorStudentAssignment::where('status', 'active')
            ->pluck('student_id');

        return User::where('is_professor', false)
            ->where('is_admin', false)
            ->whereNotIn('id', $assignedStudentIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * PROFESSOR FUNCTIONS - Gestión de plantillas para estudiantes
     */

    /**
     * Asignar plantilla a estudiante (Profesor)
     */
    public function assignTemplateToStudent(array $data): TemplateAssignment
    {
        return DB::transaction(function () use ($data) {
            // Validar que el profesor tenga el estudiante asignado
            $professorStudentAssignment = ProfessorStudentAssignment::find($data['professor_student_assignment_id']);
            
            if (!$professorStudentAssignment || $professorStudentAssignment->status !== 'active') {
                throw new \Exception('Asignación profesor-estudiante no válida o inactiva');
            }

            // Validar que el profesor autenticado sea el dueño de la asignación
            if ($professorStudentAssignment->professor_id !== auth()->id()) {
                throw new \Exception('No tienes permisos para asignar plantillas a este estudiante');
            }

            // Crear asignación de plantilla
            $assignment = TemplateAssignment::create($data);

            // Generar progreso inicial basado en frecuencia
            $this->generateProgressSchedule($assignment);

            return $assignment->load(['dailyTemplate', 'professorStudentAssignment.student']);
        });
    }

    /**
     * Generar cronograma de progreso basado en frecuencia
     */
    private function generateProgressSchedule(TemplateAssignment $assignment): void
    {
        $startDate = $assignment->start_date;
        $endDate = $assignment->end_date ?? Carbon::parse($startDate)->addWeeks(4); // 4 semanas por defecto
        $frequency = $assignment->frequency; // [1,3,5] = Lun, Mie, Vie (0=Dom, 1=Lun, etc.)

        $currentDate = Carbon::parse($startDate);
        $progressEntries = [];

        while ($currentDate <= $endDate) {
            // Verificar si el día actual está en la frecuencia
            if (in_array($currentDate->dayOfWeek, $frequency)) {
                $progressEntries[] = [
                    'daily_assignment_id' => $assignment->id,
                    'scheduled_date' => $currentDate->format('Y-m-d'),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $currentDate->addDay();
        }

        // Insertar en lotes para mejor performance
        if (!empty($progressEntries)) {
            AssignmentProgress::insert($progressEntries);
        }
    }

    /**
     * Obtener asignaciones de plantillas de un estudiante específico
     */
    public function getStudentTemplateAssignments($studentId, array $filters = []): Collection
    {
        $query = TemplateAssignment::with([
            'dailyTemplate.exercises.exercise',
            'professorStudentAssignment.professor',
            'progress'
        ])->forStudent($studentId);

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['active_only'])) {
            $query->active();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * PROGRESS FUNCTIONS - Gestión de progreso
     */

    /**
     * Marcar sesión como completada
     */
    public function markSessionCompleted($progressId, array $data): AssignmentProgress
    {
        $progress = AssignmentProgress::findOrFail($progressId);

        // Validar que el estudiante autenticado sea el dueño
        $studentId = $progress->templateAssignment->professorStudentAssignment->student_id;
        if ($studentId !== auth()->id()) {
            throw new \Exception('No tienes permisos para marcar esta sesión');
        }

        $progress->markAsCompleted(
            $data['exercise_progress'] ?? [],
            $data['student_notes'] ?? null
        );

        return $progress->fresh();
    }

    /**
     * Agregar feedback del profesor
     */
    public function addProfessorFeedback($progressId, string $feedback, ?float $rating = null): AssignmentProgress
    {
        $progress = AssignmentProgress::findOrFail($progressId);

        // Validar que el profesor autenticado sea el dueño
        $professorId = $progress->templateAssignment->professorStudentAssignment->professor_id;
        if ($professorId !== auth()->id()) {
            throw new \Exception('No tienes permisos para dar feedback a esta sesión');
        }

        $progress->addProfessorFeedback($feedback, $rating);

        return $progress->fresh();
    }

    /**
     * STATISTICS FUNCTIONS - Métricas y reportes
     */

    /**
     * Obtener estadísticas del profesor
     */
    public function getProfessorStats($professorId): array
    {
        $totalStudents = ProfessorStudentAssignment::forProfessor($professorId)->active()->count();
        
        $totalAssignments = TemplateAssignment::forProfessor($professorId)->active()->count();
        
        $completedSessions = AssignmentProgress::whereHas('templateAssignment', function($q) use ($professorId) {
            $q->forProfessor($professorId);
        })->completed()->count();
        
        $pendingSessions = AssignmentProgress::whereHas('templateAssignment', function($q) use ($professorId) {
            $q->forProfessor($professorId);
        })->pending()->count();

        return [
            'total_students' => $totalStudents,
            'total_assignments' => $totalAssignments,
            'completed_sessions' => $completedSessions,
            'pending_sessions' => $pendingSessions,
            'completion_rate' => $completedSessions + $pendingSessions > 0 
                ? round(($completedSessions / ($completedSessions + $pendingSessions)) * 100, 1)
                : 0
        ];
    }

    /**
     * Obtener estadísticas generales (Admin)
     */
    public function getGeneralStats(): array
    {
        $totalProfessors = User::where('is_professor', true)->count();
        $totalStudents = User::where('is_professor', false)->where('is_admin', false)->count();
        $activeAssignments = ProfessorStudentAssignment::active()->count();
        $unassignedStudents = $this->getUnassignedStudents()->count();

        return [
            'total_professors' => $totalProfessors,
            'total_students' => $totalStudents,
            'active_assignments' => $activeAssignments,
            'unassigned_students' => $unassignedStudents,
            'assignment_rate' => $totalStudents > 0 
                ? round(($activeAssignments / $totalStudents) * 100, 1)
                : 0
        ];
    }

    /**
     * UTILITY FUNCTIONS - Funciones auxiliares
     */

    /**
     * Pausar asignación profesor-estudiante
     */
    public function pauseProfessorStudentAssignment($assignmentId): ProfessorStudentAssignment
    {
        $assignment = ProfessorStudentAssignment::findOrFail($assignmentId);
        $assignment->update(['status' => 'paused']);

        // Pausar también todas las asignaciones de plantillas activas
        $assignment->templateAssignments()->active()->update(['status' => 'paused']);

        return $assignment->fresh();
    }

    /**
     * Reactivar asignación profesor-estudiante
     */
    public function reactivateProfessorStudentAssignment($assignmentId): ProfessorStudentAssignment
    {
        $assignment = ProfessorStudentAssignment::findOrFail($assignmentId);
        $assignment->update(['status' => 'active']);

        return $assignment->fresh();
    }

    /**
     * Completar asignación profesor-estudiante
     */
    public function completeProfessorStudentAssignment($assignmentId): ProfessorStudentAssignment
    {
        $assignment = ProfessorStudentAssignment::findOrFail($assignmentId);
        $assignment->update([
            'status' => 'completed',
            'end_date' => now()->toDateString()
        ]);

        // Completar también todas las asignaciones de plantillas activas
        $assignment->templateAssignments()->active()->update(['status' => 'completed']);

        return $assignment->fresh();
    }
}
