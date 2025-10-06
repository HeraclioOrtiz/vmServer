<?php

echo "🎯 === ASIGNACIÓN MASIVA DE ESTUDIANTES AL PROFESOR === 🎯\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Services\Gym\AssignmentService;
use Illuminate\Support\Facades\DB;

try {
    echo "🔍 PASO 1: Buscando profesor con DNI 22222222...\n";
    
    $professor = User::where('dni', '22222222')->first();
    
    if (!$professor) {
        echo "❌ ERROR: No se encontró profesor con DNI 22222222\n";
        exit(1);
    }
    
    if (!$professor->is_professor) {
        echo "❌ ERROR: El usuario con DNI 22222222 no es profesor\n";
        echo "   is_professor: " . ($professor->is_professor ? 'true' : 'false') . "\n";
        exit(1);
    }
    
    echo "✅ Profesor encontrado: {$professor->name} (ID: {$professor->id})\n";
    echo "   Email: {$professor->email}\n";
    echo "   Es profesor: " . ($professor->is_professor ? 'Sí' : 'No') . "\n\n";
    
    echo "🔍 PASO 2: Buscando estudiantes disponibles...\n";
    
    // Buscar todos los estudiantes (no profesores, no admins)
    $students = User::where('is_professor', false)
                   ->where('is_admin', false)
                   ->where('account_status', 'active')
                   ->get();
    
    echo "📊 Total estudiantes encontrados: {$students->count()}\n";
    
    if ($students->count() === 0) {
        echo "⚠️  No hay estudiantes disponibles para asignar\n";
        exit(0);
    }
    
    // Mostrar algunos estudiantes de ejemplo
    echo "   Ejemplos de estudiantes:\n";
    foreach ($students->take(5) as $student) {
        echo "   - {$student->name} (ID: {$student->id}, DNI: {$student->dni})\n";
    }
    if ($students->count() > 5) {
        echo "   ... y " . ($students->count() - 5) . " más\n";
    }
    echo "\n";
    
    echo "🔍 PASO 3: Verificando asignaciones existentes...\n";
    
    $existingAssignments = ProfessorStudentAssignment::where('professor_id', $professor->id)
                                                    ->where('status', 'active')
                                                    ->get();
    
    echo "📊 Asignaciones activas existentes: {$existingAssignments->count()}\n";
    
    if ($existingAssignments->count() > 0) {
        echo "   Estudiantes ya asignados:\n";
        foreach ($existingAssignments as $assignment) {
            $student = User::find($assignment->student_id);
            echo "   - {$student->name} (ID: {$student->id})\n";
        }
        echo "\n";
    }
    
    // Filtrar estudiantes que ya están asignados a este profesor
    $assignedStudentIds = $existingAssignments->pluck('student_id')->toArray();
    $unassignedStudents = $students->whereNotIn('id', $assignedStudentIds);
    
    echo "📊 Estudiantes sin asignar a este profesor: {$unassignedStudents->count()}\n\n";
    
    if ($unassignedStudents->count() === 0) {
        echo "✅ Todos los estudiantes ya están asignados a este profesor\n";
        echo "🎉 PROCESO COMPLETADO - NO HAY NADA QUE HACER\n";
        exit(0);
    }
    
    echo "🚀 PASO 4: Iniciando asignación masiva...\n";
    
    $assignmentService = new AssignmentService();
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    // Buscar un admin para usar como 'assigned_by'
    $admin = User::where('is_admin', true)->first();
    if (!$admin) {
        echo "⚠️  No se encontró admin, usando el primer usuario como assigned_by\n";
        $admin = User::first();
    }
    
    echo "👑 Asignaciones serán creadas por: {$admin->name} (ID: {$admin->id})\n\n";
    
    DB::beginTransaction();
    
    try {
        foreach ($unassignedStudents as $student) {
            try {
                echo "📝 Asignando: {$student->name} (ID: {$student->id})... ";
                
                $assignmentData = [
                    'professor_id' => $professor->id,
                    'student_id' => $student->id,
                    'assigned_by' => $admin->id,
                    'start_date' => now()->toDateString(),
                    'admin_notes' => 'Asignación masiva automática - Script de inicialización'
                ];
                
                $assignment = $assignmentService->assignStudentToProfessor($assignmentData);
                
                echo "✅ ÉXITO (ID: {$assignment->id})\n";
                $successCount++;
                
            } catch (Exception $e) {
                echo "❌ ERROR: {$e->getMessage()}\n";
                $errorCount++;
                $errors[] = [
                    'student' => $student->name,
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        DB::commit();
        
    } catch (Exception $e) {
        DB::rollBack();
        echo "❌ ERROR CRÍTICO: {$e->getMessage()}\n";
        echo "🔄 Transacción revertida\n";
        exit(1);
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "🎊 RESUMEN DE ASIGNACIÓN MASIVA\n\n";
    
    echo "👨‍🏫 PROFESOR:\n";
    echo "   Nombre: {$professor->name}\n";
    echo "   DNI: {$professor->dni}\n";
    echo "   ID: {$professor->id}\n\n";
    
    echo "📊 RESULTADOS:\n";
    echo "   ✅ Asignaciones exitosas: {$successCount}\n";
    echo "   ❌ Errores: {$errorCount}\n";
    echo "   📋 Total estudiantes procesados: " . ($successCount + $errorCount) . "\n\n";
    
    if ($errorCount > 0) {
        echo "❌ ERRORES DETALLADOS:\n";
        foreach ($errors as $error) {
            echo "   - {$error['student']} (ID: {$error['student_id']}): {$error['error']}\n";
        }
        echo "\n";
    }
    
    // Verificar estado final
    echo "🔍 VERIFICACIÓN FINAL:\n";
    $finalAssignments = ProfessorStudentAssignment::where('professor_id', $professor->id)
                                                  ->where('status', 'active')
                                                  ->with('student')
                                                  ->get();
    
    echo "   📊 Total asignaciones activas: {$finalAssignments->count()}\n";
    echo "   📋 Estudiantes asignados:\n";
    
    foreach ($finalAssignments as $assignment) {
        echo "      - {$assignment->student->name} (desde {$assignment->start_date})\n";
    }
    
    echo "\n🎯 ESTADÍSTICAS GENERALES:\n";
    $totalStudents = User::where('is_professor', false)->where('is_admin', false)->count();
    $assignedStudents = ProfessorStudentAssignment::where('status', 'active')->distinct('student_id')->count();
    $unassignedStudents = $totalStudents - $assignedStudents;
    
    echo "   👥 Total estudiantes en sistema: {$totalStudents}\n";
    echo "   ✅ Estudiantes asignados (global): {$assignedStudents}\n";
    echo "   ⚠️  Estudiantes sin asignar (global): {$unassignedStudents}\n";
    
    $assignmentRate = $totalStudents > 0 ? round(($assignedStudents / $totalStudents) * 100, 1) : 0;
    echo "   📈 Tasa de asignación: {$assignmentRate}%\n";
    
    echo "\n🎉 PROCESO DE ASIGNACIÓN MASIVA COMPLETADO\n";
    
    if ($successCount > 0) {
        echo "✅ ÉXITO: {$successCount} estudiantes asignados correctamente al profesor {$professor->name}\n";
    }
    
    if ($errorCount > 0) {
        echo "⚠️  ADVERTENCIA: {$errorCount} asignaciones fallaron - revisar errores arriba\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO EN EL SCRIPT: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n🚀 Script completado exitosamente\n";
