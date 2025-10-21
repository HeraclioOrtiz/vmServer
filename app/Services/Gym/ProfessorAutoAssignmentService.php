<?php

namespace App\Services\Gym;

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Services\Core\AuditService;
use Illuminate\Support\Facades\Log;

/**
 * ⚠️ TEMPORARY SERVICE - WILL BE REMOVED
 *
 * This service handles automatic assignment of students to a default professor
 * during user promotion from local to API type.
 *
 * Purpose:
 * - Auto-assigns promoted students to a professor configured via GYM_DEFAULT_PROFESSOR_DNI
 * - Provides temporary functionality until manual professor assignment UI is implemented
 * - Controlled by GYM_AUTO_ASSIGN_STUDENTS config flag
 *
 * Configuration:
 * - config('gym.auto_assign_students') - Enable/disable auto-assignment (default: false)
 * - config('gym.default_professor_dni') - DNI of default professor for auto-assignment
 *
 * TODO: Remove this service when manual professor assignment is implemented by admin
 *
 * @deprecated This service is temporary and will be removed in future versions
 */
class ProfessorAutoAssignmentService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * ⚠️ TEMPORAL: Asigna automáticamente el estudiante al profesor predeterminado
     *
     * This method automatically assigns a student to the default professor configured
     * in the system. It performs several checks before assignment:
     * - Verifies if auto-assignment is enabled (GYM_AUTO_ASSIGN_STUDENTS)
     * - Validates default professor DNI is configured (GYM_DEFAULT_PROFESSOR_DNI)
     * - Checks if professor exists and has is_professor = true
     * - Verifies assignment doesn't already exist
     *
     * If any check fails, the method logs a warning and returns gracefully without
     * throwing exceptions to prevent blocking the user promotion process.
     *
     * @deprecated This method is temporary and will be removed when manual assignment is implemented
     *
     * @param User $student The student user to be assigned to default professor
     * @return void Does not return a value; logs success/failure
     */
    public function assignStudentToDefaultProfessor(User $student): void
    {
        try {
            // Verificar si la auto-asignación está habilitada
            if (!config('gym.auto_assign_students', false)) {
                Log::info('Auto-asignación de estudiantes deshabilitada', [
                    'student_dni' => $student->dni,
                ]);
                return;
            }

            // Obtener DNI del profesor desde configuración
            $professorDni = config('gym.default_professor_dni');

            if (!$professorDni) {
                Log::warning('DNI de profesor predeterminado no configurado', [
                    'student_dni' => $student->dni,
                    'config_key' => 'GYM_DEFAULT_PROFESSOR_DNI',
                ]);
                return;
            }

            // Buscar profesor por DNI configurado
            $professor = User::where('dni', $professorDni)
                ->where('is_professor', true)
                ->first();

            if (!$professor) {
                Log::warning('Profesor predeterminado no encontrado para asignación automática', [
                    'student_dni' => $student->dni,
                    'professor_dni' => $professorDni,
                ]);
                return;
            }

            // Verificar si ya existe la asignación
            $existingAssignment = ProfessorStudentAssignment::where('professor_id', $professor->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingAssignment) {
                Log::info('Asignación profesor-estudiante ya existe', [
                    'student_dni' => $student->dni,
                    'professor_dni' => $professor->dni,
                ]);
                return;
            }

            // Crear asignación
            ProfessorStudentAssignment::create([
                'professor_id' => $professor->id,
                'student_id' => $student->id,
                'assigned_by' => $professor->id, // Auto-asignado por el sistema
                'start_date' => now()->toDateString(),
                'status' => 'active',
                'notes' => '⚠️ ASIGNACIÓN AUTOMÁTICA TEMPORAL - Generada por sistema durante promoción de usuario',
            ]);

            Log::info('Usuario asignado automáticamente al profesor predeterminado', [
                'student_id' => $student->id,
                'student_dni' => $student->dni,
                'professor_id' => $professor->id,
                'professor_dni' => $professor->dni,
            ]);

            // Log de auditoría
            $this->auditService->log(
                action: 'auto_assign_student_to_professor',
                resourceType: 'professor_student_assignment',
                resourceId: $student->id,
                details: [
                    'student_id' => $student->id,
                    'professor_id' => $professor->id,
                    'automatic' => true,
                    'temporary_feature' => true,
                ],
                severity: 'low',
                category: 'gym_management'
            );

        } catch (\Exception $e) {
            Log::error('Error en asignación automática de profesor', [
                'student_dni' => $student->dni,
                'error' => $e->getMessage(),
            ]);
            // No lanzar excepción para no bloquear la promoción
        }
    }
}
