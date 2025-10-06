<?php

echo "📊 === ESTADO FINAL DE MARÍA GARCÍA === 📊\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use Illuminate\Support\Facades\DB;

try {
    echo "👤 ESTUDIANTE: MARÍA GARCÍA\n";
    echo str_repeat("=", 50) . "\n";
    
    $maria = User::where('name', 'LIKE', '%María García%')->first();
    echo "Nombre: {$maria->name}\n";
    echo "ID: {$maria->id}\n";
    echo "DNI: {$maria->dni}\n";
    echo "Email: {$maria->email}\n\n";
    
    echo "🔗 ASIGNACIÓN PROFESOR-ESTUDIANTE:\n";
    echo str_repeat("=", 50) . "\n";
    
    $professorAssignment = ProfessorStudentAssignment::where('student_id', $maria->id)
                                                     ->with('professor')
                                                     ->first();
    
    echo "ID Asignación: {$professorAssignment->id}\n";
    echo "Profesor: {$professorAssignment->professor->name}\n";
    echo "Estado: {$professorAssignment->status}\n";
    echo "Desde: {$professorAssignment->start_date}\n\n";
    
    echo "📋 PLANTILLAS ASIGNADAS:\n";
    echo str_repeat("=", 50) . "\n";
    
    // Consulta directa a la BD
    $assignments = DB::table('daily_assignments')
                    ->where('professor_student_assignment_id', $professorAssignment->id)
                    ->join('gym_daily_templates', 'daily_assignments.daily_template_id', '=', 'gym_daily_templates.id')
                    ->select('daily_assignments.*', 'gym_daily_templates.title as template_title')
                    ->orderBy('daily_assignments.created_at', 'desc')
                    ->get();
    
    echo "Total plantillas asignadas: {$assignments->count()}\n\n";
    
    if ($assignments->count() > 0) {
        foreach ($assignments as $index => $assignment) {
            echo "📌 PLANTILLA #" . ($index + 1) . ":\n";
            echo "   ID: {$assignment->id}\n";
            echo "   Plantilla: {$assignment->template_title}\n";
            echo "   Template ID: {$assignment->daily_template_id}\n";
            echo "   Estado: {$assignment->status}\n";
            echo "   Inicio: {$assignment->start_date}\n";
            echo "   Fin: {$assignment->end_date}\n";
            echo "   Frecuencia: {$assignment->frequency}\n";
            echo "   Notas: " . ($assignment->professor_notes ?: 'Sin notas') . "\n";
            echo "   Creado: {$assignment->created_at}\n";
            echo "\n";
        }
    } else {
        echo "⚠️  No hay plantillas asignadas\n";
    }
    
    echo "🎯 VERIFICACIÓN API:\n";
    echo str_repeat("=", 50) . "\n";
    
    // Verificar usando el modelo
    $modelAssignments = TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)->count();
    echo "Via modelo TemplateAssignment: {$modelAssignments}\n";
    
    // Verificar usando relación
    $relationAssignments = $professorAssignment->templateAssignments()->count();
    echo "Via relación: {$relationAssignments}\n";
    
    echo "\n🎊 RESUMEN:\n";
    echo str_repeat("=", 50) . "\n";
    
    if ($assignments->count() > 0) {
        echo "✅ ÉXITO: María García tiene {$assignments->count()} plantillas asignadas\n";
        echo "✅ Sistema funcionando correctamente\n";
        echo "✅ Frontend debería mostrar las plantillas\n";
    } else {
        echo "❌ PROBLEMA: María García no tiene plantillas asignadas\n";
        echo "⚠️  Verificar proceso de asignación\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
