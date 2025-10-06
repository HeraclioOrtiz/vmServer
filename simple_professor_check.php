<?php

echo "👨‍🏫 === PROFESORES Y ESTUDIANTES === 👨‍🏫\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // 1. Buscar profesores
    echo "🔍 PROFESORES DISPONIBLES:\n";
    echo str_repeat("=", 50) . "\n";
    
    $professors = \App\Models\User::where('is_professor', true)
        ->orWhere('is_admin', true)
        ->get();
    
    foreach ($professors as $professor) {
        echo "👨‍🏫 {$professor->name}\n";
        echo "   📧 {$professor->email}\n";
        echo "   🆔 ID: {$professor->id}\n";
        echo "   👔 " . ($professor->is_admin ? 'Admin' : 'Profesor') . "\n\n";
    }
    
    // 2. Buscar asignaciones
    echo "🔗 ASIGNACIONES PROFESOR-ESTUDIANTE:\n";
    echo str_repeat("=", 50) . "\n";
    
    $assignments = \App\Models\Gym\ProfessorStudentAssignment::with(['professor', 'student'])->get();
    
    echo "📊 Total asignaciones: " . $assignments->count() . "\n\n";
    
    foreach ($assignments as $assignment) {
        echo "🔗 Asignación ID: {$assignment->id}\n";
        echo "   👨‍🏫 Profesor: {$assignment->professor->name}\n";
        echo "   👤 Estudiante: {$assignment->student->name}\n";
        echo "   📧 Email estudiante: {$assignment->student->email}\n";
        echo "   🆔 DNI estudiante: {$assignment->student->dni}\n";
        echo "   📅 Asignado: " . $assignment->assigned_at->format('d/m/Y H:i') . "\n\n";
    }
    
    // 3. Plantillas asignadas
    echo "📋 PLANTILLAS ASIGNADAS:\n";
    echo str_repeat("=", 50) . "\n";
    
    $templateAssignments = \App\Models\Gym\TemplateAssignment::with(['dailyTemplate', 'professorStudentAssignment.student', 'professorStudentAssignment.professor'])->get();
    
    echo "📊 Total plantillas asignadas: " . $templateAssignments->count() . "\n\n";
    
    foreach ($templateAssignments as $templateAssignment) {
        echo "📋 Plantilla: {$templateAssignment->dailyTemplate->title}\n";
        echo "   👨‍🏫 Profesor: {$templateAssignment->professorStudentAssignment->professor->name}\n";
        echo "   👤 Estudiante: {$templateAssignment->professorStudentAssignment->student->name}\n";
        echo "   📅 Estado: {$templateAssignment->status}\n";
        echo "   🗓️ Desde: " . $templateAssignment->start_date->format('d/m/Y') . "\n";
        if ($templateAssignment->end_date) {
            echo "   🗓️ Hasta: " . $templateAssignment->end_date->format('d/m/Y') . "\n";
        }
        echo "\n";
    }
    
    // 4. Resumen
    echo "📊 RESUMEN:\n";
    echo str_repeat("=", 50) . "\n";
    echo "👨‍🏫 Profesores: " . $professors->count() . "\n";
    echo "🔗 Asignaciones: " . $assignments->count() . "\n";
    echo "📋 Plantillas asignadas: " . $templateAssignments->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
