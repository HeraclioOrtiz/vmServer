<?php

echo "🔍 === PLANTILLAS ASIGNADAS A MARÍA GARCÍA === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use App\Models\Gym\AssignmentProgress;

try {
    echo "👤 PASO 1: Buscando estudiante María García...\n";
    
    $student = User::where('name', 'LIKE', '%María García%')->first();
    
    if (!$student) {
        echo "❌ ERROR: No se encontró estudiante María García\n";
        echo "🔍 Buscando estudiantes similares...\n";
        
        $similarStudents = User::where('name', 'LIKE', '%María%')
                              ->orWhere('name', 'LIKE', '%Garcia%')
                              ->orWhere('name', 'LIKE', '%García%')
                              ->get();
        
        if ($similarStudents->count() > 0) {
            echo "📋 Estudiantes encontrados:\n";
            foreach ($similarStudents as $s) {
                echo "   - {$s->name} (ID: {$s->id}, DNI: {$s->dni})\n";
            }
        }
        exit(1);
    }
    
    echo "✅ Estudiante encontrada:\n";
    echo "   Nombre: {$student->name}\n";
    echo "   ID: {$student->id}\n";
    echo "   DNI: {$student->dni}\n";
    echo "   Email: {$student->email}\n\n";
    
    echo "🔗 PASO 2: Verificando asignación profesor-estudiante...\n";
    
    $professorAssignment = ProfessorStudentAssignment::where('student_id', $student->id)
                                                     ->where('status', 'active')
                                                     ->with(['professor'])
                                                     ->first();
    
    if (!$professorAssignment) {
        echo "❌ ERROR: La estudiante no está asignada a ningún profesor\n";
        exit(1);
    }
    
    echo "✅ Asignación encontrada:\n";
    echo "   Profesor: {$professorAssignment->professor->name}\n";
    echo "   Profesor DNI: {$professorAssignment->professor->dni}\n";
    echo "   Asignada desde: {$professorAssignment->start_date}\n";
    echo "   Estado: {$professorAssignment->status}\n\n";
    
    echo "📋 PASO 3: Buscando plantillas asignadas...\n";
    
    $templateAssignments = TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)
                                             ->with(['dailyTemplate'])
                                             ->orderBy('created_at', 'desc')
                                             ->get();
    
    echo "📊 Total asignaciones de plantillas: {$templateAssignments->count()}\n\n";
    
    if ($templateAssignments->count() === 0) {
        echo "⚠️  NO HAY PLANTILLAS ASIGNADAS\n";
        echo "📝 La estudiante María García no tiene plantillas diarias asignadas\n";
        echo "🎯 El profesor puede asignarle plantillas usando el endpoint:\n";
        echo "   POST /api/professor/assign-template\n\n";
        
        echo "📋 PLANTILLAS DISPONIBLES PARA ASIGNAR:\n";
        $availableTemplates = \App\Models\Gym\DailyTemplate::with('exercises.exercise')
                                                           ->take(5)
                                                           ->get();
        
        foreach ($availableTemplates as $template) {
            echo "   - {$template->name} ({$template->exercises->count()} ejercicios)\n";
        }
        
        exit(0);
    }
    
    echo "📋 PLANTILLAS ASIGNADAS:\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($templateAssignments as $index => $assignment) {
        echo "📌 ASIGNACIÓN #" . ($index + 1) . ":\n";
        echo "   📝 Plantilla: {$assignment->dailyTemplate->name}\n";
        echo "   🆔 ID Plantilla: {$assignment->daily_template_id}\n";
        echo "   📅 Asignada desde: {$assignment->start_date}\n";
        echo "   📊 Estado: {$assignment->status}\n";
        echo "   📆 Frecuencia: " . implode(', ', $assignment->frequency ?? []) . "\n";
        echo "   📝 Notas profesor: " . ($assignment->professor_notes ?: 'Sin notas') . "\n";
        
        // Información de la plantilla
        $template = $assignment->dailyTemplate;
        echo "   🏋️ Ejercicios en plantilla: {$template->exercises->count()}\n";
        
        if ($template->exercises->count() > 0) {
            echo "   📋 Lista de ejercicios:\n";
            foreach ($template->exercises->take(3) as $templateExercise) {
                $exercise = $templateExercise->exercise;
                echo "      - {$exercise->name} ({$templateExercise->sets->count()} series)\n";
            }
            if ($template->exercises->count() > 3) {
                echo "      ... y " . ($template->exercises->count() - 3) . " ejercicios más\n";
            }
        }
        
        echo "\n" . str_repeat("-", 80) . "\n";
    }
    
    echo "\n📊 PASO 4: Verificando progreso de entrenamientos...\n";
    
    $progressRecords = AssignmentProgress::whereIn('template_assignment_id', $templateAssignments->pluck('id'))
                                        ->orderBy('scheduled_date', 'desc')
                                        ->take(10)
                                        ->get();
    
    echo "📈 Registros de progreso: {$progressRecords->count()}\n";
    
    if ($progressRecords->count() > 0) {
        echo "📋 Últimas sesiones programadas:\n";
        foreach ($progressRecords->take(5) as $progress) {
            echo "   - {$progress->scheduled_date}: {$progress->status}\n";
        }
    }
    
    echo "\n🎯 RESUMEN PARA MARÍA GARCÍA:\n";
    echo "   👤 Estudiante: {$student->name}\n";
    echo "   👨‍🏫 Profesor asignado: {$professorAssignment->professor->name}\n";
    echo "   📋 Plantillas asignadas: {$templateAssignments->count()}\n";
    echo "   📈 Sesiones de progreso: {$progressRecords->count()}\n";
    
    $activeTemplates = $templateAssignments->where('status', 'active')->count();
    echo "   ✅ Plantillas activas: {$activeTemplates}\n";
    
    if ($activeTemplates === 0) {
        echo "   ⚠️  ESTADO: Sin plantillas activas\n";
        echo "   🎯 ACCIÓN: Profesor debe asignar plantillas\n";
    } else {
        echo "   ✅ ESTADO: Plantillas activas disponibles\n";
        echo "   🎯 ACCIÓN: Estudiante puede seguir entrenamientos\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    exit(1);
}
