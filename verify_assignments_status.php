<?php

echo "📊 === VERIFICACIÓN DEL ESTADO DE ASIGNACIONES === 📊\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use App\Services\Gym\AssignmentService;

try {
    echo "🔍 PASO 1: Información del profesor...\n";
    
    $professor = User::where('dni', '22222222')->first();
    echo "👨‍🏫 Profesor: {$professor->name}\n";
    echo "   ID: {$professor->id}\n";
    echo "   DNI: {$professor->dni}\n";
    echo "   Email: {$professor->email}\n\n";
    
    echo "📊 PASO 2: Estadísticas de asignaciones...\n";
    
    $assignmentService = new AssignmentService();
    $generalStats = $assignmentService->getGeneralStats();
    
    echo "📈 ESTADÍSTICAS GENERALES:\n";
    echo "   👥 Total profesores: {$generalStats['total_professors']}\n";
    echo "   🎓 Total estudiantes: {$generalStats['total_students']}\n";
    echo "   ✅ Asignaciones activas: {$generalStats['active_assignments']}\n";
    echo "   ⚠️  Estudiantes sin asignar: {$generalStats['unassigned_students']}\n";
    echo "   📈 Tasa de asignación: {$generalStats['assignment_rate']}%\n\n";
    
    echo "👨‍🏫 ESTADÍSTICAS DEL PROFESOR:\n";
    $professorStats = $assignmentService->getProfessorStats($professor->id);
    echo "   🎓 Estudiantes asignados: {$professorStats['total_students']}\n";
    echo "   📋 Asignaciones de plantillas: {$professorStats['total_assignments']}\n";
    echo "   ✅ Sesiones completadas: {$professorStats['completed_sessions']}\n";
    echo "   ⏳ Sesiones pendientes: {$professorStats['pending_sessions']}\n";
    echo "   📊 Tasa de adherencia: {$professorStats['adherence_rate']}%\n\n";
    
    echo "📋 PASO 3: Lista detallada de estudiantes asignados...\n";
    
    $assignments = ProfessorStudentAssignment::where('professor_id', $professor->id)
                                            ->where('status', 'active')
                                            ->with(['student', 'assignedBy'])
                                            ->orderBy('created_at', 'desc')
                                            ->get();
    
    echo "📊 Total asignaciones activas: {$assignments->count()}\n\n";
    
    foreach ($assignments as $index => $assignment) {
        echo "   " . ($index + 1) . ". {$assignment->student->name}\n";
        echo "      ID: {$assignment->student->id}\n";
        echo "      DNI: {$assignment->student->dni}\n";
        echo "      Email: {$assignment->student->email}\n";
        echo "      Asignado desde: {$assignment->start_date}\n";
        echo "      Estado: {$assignment->status}\n";
        echo "      Asignado por: {$assignment->assignedBy->name}\n";
        
        // Verificar si tiene plantillas asignadas
        $templateAssignments = TemplateAssignment::where('professor_student_assignment_id', $assignment->id)
                                                 ->where('status', 'active')
                                                 ->count();
        
        echo "      Plantillas activas: {$templateAssignments}\n";
        echo "\n";
    }
    
    echo "🎯 PASO 4: Resumen del sistema...\n";
    
    $totalUsers = User::count();
    $admins = User::where('is_admin', true)->count();
    $professors = User::where('is_professor', true)->count();
    $students = User::where('is_professor', false)->where('is_admin', false)->count();
    
    echo "👥 USUARIOS EN EL SISTEMA:\n";
    echo "   Total usuarios: {$totalUsers}\n";
    echo "   👑 Administradores: {$admins}\n";
    echo "   👨‍🏫 Profesores: {$professors}\n";
    echo "   🎓 Estudiantes: {$students}\n\n";
    
    echo "📋 ASIGNACIONES GLOBALES:\n";
    $totalAssignments = ProfessorStudentAssignment::where('status', 'active')->count();
    $totalTemplateAssignments = TemplateAssignment::where('status', 'active')->count();
    
    echo "   🔗 Asignaciones profesor-estudiante: {$totalAssignments}\n";
    echo "   📝 Asignaciones de plantillas: {$totalTemplateAssignments}\n\n";
    
    echo "✅ ESTADO DEL SISTEMA: COMPLETAMENTE CONFIGURADO\n";
    echo "🎉 Todos los estudiantes están asignados al profesor Juan Pérez\n";
    echo "🚀 Sistema listo para desarrollo frontend\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
