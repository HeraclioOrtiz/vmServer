<?php

echo "🔍 === VERIFICACIÓN DE ESTUDIANTES DEL PROFESOR 22222222 === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;

try {
    echo "👨‍🏫 PASO 1: Buscando profesor...\n";
    
    $professor = User::where('dni', '22222222')->first();
    
    if (!$professor) {
        echo "❌ ERROR: No se encontró profesor con DNI 22222222\n";
        exit(1);
    }
    
    echo "✅ Profesor encontrado:\n";
    echo "   Nombre: {$professor->name}\n";
    echo "   ID: {$professor->id}\n";
    echo "   DNI: {$professor->dni}\n";
    echo "   Email: {$professor->email}\n";
    echo "   Es profesor: " . ($professor->is_professor ? 'Sí' : 'No') . "\n\n";
    
    echo "🎓 PASO 2: Obteniendo estudiantes asignados...\n";
    
    $assignments = ProfessorStudentAssignment::where('professor_id', $professor->id)
                                            ->where('status', 'active')
                                            ->with(['student', 'assignedBy'])
                                            ->orderBy('created_at', 'desc')
                                            ->get();
    
    echo "📊 Total estudiantes asignados: {$assignments->count()}\n\n";
    
    if ($assignments->count() === 0) {
        echo "⚠️  No hay estudiantes asignados a este profesor\n";
        exit(0);
    }
    
    echo "📋 LISTA DETALLADA DE ESTUDIANTES:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($assignments as $index => $assignment) {
        echo "📌 ESTUDIANTE #" . ($index + 1) . ":\n";
        echo "   👤 Nombre: {$assignment->student->name}\n";
        echo "   🆔 ID: {$assignment->student->id}\n";
        echo "   📄 DNI: {$assignment->student->dni}\n";
        echo "   📧 Email: {$assignment->student->email}\n";
        echo "   📅 Asignado desde: {$assignment->start_date}\n";
        echo "   📊 Estado: {$assignment->status}\n";
        echo "   👑 Asignado por: {$assignment->assignedBy->name}\n";
        echo "   📝 Notas admin: " . ($assignment->admin_notes ?: 'Sin notas') . "\n";
        
        // Verificar plantillas asignadas
        $templateAssignments = TemplateAssignment::where('professor_student_assignment_id', $assignment->id)
                                                 ->where('status', 'active')
                                                 ->with('dailyTemplate')
                                                 ->get();
        
        echo "   📋 Plantillas asignadas: {$templateAssignments->count()}\n";
        
        if ($templateAssignments->count() > 0) {
            echo "      Plantillas activas:\n";
            foreach ($templateAssignments as $templateAssignment) {
                echo "      - {$templateAssignment->dailyTemplate->name} (desde {$templateAssignment->start_date})\n";
            }
        }
        
        echo "\n" . str_repeat("-", 80) . "\n";
    }
    
    echo "\n📊 RESUMEN ESTADÍSTICO:\n";
    
    $studentsWithTemplates = TemplateAssignment::whereIn('professor_student_assignment_id', $assignments->pluck('id'))
                                               ->where('status', 'active')
                                               ->distinct('professor_student_assignment_id')
                                               ->count();
    
    $totalTemplateAssignments = TemplateAssignment::whereIn('professor_student_assignment_id', $assignments->pluck('id'))
                                                  ->where('status', 'active')
                                                  ->count();
    
    echo "   🎓 Total estudiantes: {$assignments->count()}\n";
    echo "   📋 Estudiantes con plantillas: {$studentsWithTemplates}\n";
    echo "   ⚠️  Estudiantes sin plantillas: " . ($assignments->count() - $studentsWithTemplates) . "\n";
    echo "   📝 Total asignaciones de plantillas: {$totalTemplateAssignments}\n";
    
    $assignmentRate = $assignments->count() > 0 ? round(($studentsWithTemplates / $assignments->count()) * 100, 1) : 0;
    echo "   📈 Tasa de asignación de plantillas: {$assignmentRate}%\n";
    
    echo "\n🎯 ESTADO DEL PROFESOR:\n";
    echo "   ✅ Estudiantes asignados: {$assignments->count()}\n";
    echo "   📋 Plantillas activas: {$totalTemplateAssignments}\n";
    echo "   🎯 Listo para asignar más plantillas: " . ($assignments->count() - $studentsWithTemplates) . " estudiantes\n";
    
    echo "\n🚀 VERIFICACIÓN COMPLETADA EXITOSAMENTE\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    exit(1);
}
