<?php

echo "👨‍🏫 === VERIFICACIÓN DE PROFESORES Y ESTUDIANTES ASIGNADOS === 👨‍🏫\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🔍 BUSCANDO PROFESORES DISPONIBLES...\n";
    echo str_repeat("=", 60) . "\n";
    
    // Buscar todos los profesores
    $professors = \App\Models\User::where('is_professor', true)
        ->orWhere('is_admin', true)
        ->get();
    
    echo "📊 Total de profesores encontrados: " . $professors->count() . "\n\n";
    
    foreach ($professors as $professor) {
        echo "👨‍🏫 PROFESOR: {$professor->name}\n";
        echo "   📧 Email: {$professor->email}\n";
        echo "   🆔 ID: {$professor->id}\n";
        echo "   👔 Tipo: " . ($professor->is_admin ? 'Administrador' : 'Profesor') . "\n";
        
        // Buscar estudiantes asignados a este profesor
        $assignments = \App\Models\Gym\ProfessorStudentAssignment::where('professor_id', $professor->id)
            ->with('student')
            ->get();
        
        echo "   👥 Estudiantes asignados: " . $assignments->count() . "\n";
        
        if ($assignments->count() > 0) {
            echo "   📋 LISTA DE ESTUDIANTES:\n";
            foreach ($assignments as $assignment) {
                $student = $assignment->student;
                echo "      • {$student->name} (ID: {$student->id})\n";
                echo "        📧 {$student->email}\n";
                echo "        🆔 DNI: {$student->dni}\n";
                echo "        📅 Asignado: " . $assignment->assigned_at->format('d/m/Y') . "\n";
                
                // Verificar plantillas asignadas
                $templateAssignments = \App\Models\Gym\TemplateAssignment::whereHas('professorStudentAssignment', function($query) use ($assignment) {
                    $query->where('id', $assignment->id);
                })->with('dailyTemplate')->get();
                
                echo "        📋 Plantillas asignadas: " . $templateAssignments->count() . "\n";
                
                if ($templateAssignments->count() > 0) {
                    foreach ($templateAssignments as $templateAssignment) {
                        echo "           - {$templateAssignment->dailyTemplate->title} ({$templateAssignment->status})\n";
                    }
                }
                echo "\n";
            }
        } else {
            echo "   ⚠️  Sin estudiantes asignados\n";
        }
        
        echo str_repeat("-", 50) . "\n\n";
    }
    
    // Resumen general
    echo "📊 RESUMEN GENERAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    $totalStudents = \App\Models\User::where('is_professor', false)
        ->where('is_admin', false)
        ->count();
    
    $assignedStudents = \App\Models\Gym\ProfessorStudentAssignment::distinct('student_id')->count();
    $unassignedStudents = $totalStudents - $assignedStudents;
    
    echo "👥 Total estudiantes en sistema: {$totalStudents}\n";
    echo "✅ Estudiantes asignados: {$assignedStudents}\n";
    echo "❌ Estudiantes sin asignar: {$unassignedStudents}\n";
    
    $totalTemplateAssignments = \App\Models\Gym\TemplateAssignment::count();
    echo "📋 Total plantillas asignadas: {$totalTemplateAssignments}\n";
    
    echo "\n🎯 RECOMENDACIONES:\n";
    if ($unassignedStudents > 0) {
        echo "⚠️  Hay {$unassignedStudents} estudiantes sin asignar a profesores\n";
    }
    
    if ($professors->count() == 0) {
        echo "❌ No hay profesores en el sistema\n";
    } elseif ($assignedStudents == 0) {
        echo "⚠️  Los profesores no tienen estudiantes asignados\n";
    } else {
        echo "✅ Sistema de asignaciones funcionando correctamente\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
