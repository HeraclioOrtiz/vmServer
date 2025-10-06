<?php

echo "🔍 === DEBUGGING COMPLETO DE ASIGNACIONES === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use App\Models\Gym\AssignmentProgress;
use Illuminate\Support\Facades\DB;

try {
    echo "👤 PASO 1: Verificando María García...\n";
    
    $maria = User::where('name', 'LIKE', '%María García%')->first();
    echo "✅ María García encontrada: ID {$maria->id}, DNI {$maria->dni}\n\n";
    
    echo "🔗 PASO 2: Verificando asignación profesor-estudiante...\n";
    
    $professorAssignment = ProfessorStudentAssignment::where('student_id', $maria->id)
                                                     ->where('status', 'active')
                                                     ->first();
    
    if (!$professorAssignment) {
        echo "❌ ERROR: No hay asignación profesor-estudiante\n";
        exit(1);
    }
    
    echo "✅ Asignación profesor-estudiante encontrada:\n";
    echo "   ID: {$professorAssignment->id}\n";
    echo "   Profesor ID: {$professorAssignment->professor_id}\n";
    echo "   Estudiante ID: {$professorAssignment->student_id}\n";
    echo "   Estado: {$professorAssignment->status}\n\n";
    
    echo "📋 PASO 3: Verificando tabla daily_assignments directamente...\n";
    
    $directQuery = DB::table('daily_assignments')
                    ->where('professor_student_assignment_id', $professorAssignment->id)
                    ->get();
    
    echo "📊 Registros en daily_assignments: {$directQuery->count()}\n";
    
    if ($directQuery->count() > 0) {
        echo "✅ ENCONTRADOS registros en daily_assignments:\n";
        foreach ($directQuery as $assignment) {
            echo "   - ID: {$assignment->id}\n";
            echo "     Template ID: {$assignment->daily_template_id}\n";
            echo "     Start Date: {$assignment->start_date}\n";
            echo "     End Date: {$assignment->end_date}\n";
            echo "     Status: {$assignment->status}\n";
            echo "     Frequency: {$assignment->frequency}\n";
            echo "     Notes: {$assignment->professor_notes}\n";
            echo "     Created: {$assignment->created_at}\n\n";
        }
    } else {
        echo "❌ NO hay registros en daily_assignments\n\n";
    }
    
    echo "🔍 PASO 4: Verificando con modelo TemplateAssignment...\n";
    
    $templateAssignments = TemplateAssignment::where('professor_student_assignment_id', $professorAssignment->id)
                                             ->get();
    
    echo "📊 Registros via modelo TemplateAssignment: {$templateAssignments->count()}\n";
    
    if ($templateAssignments->count() > 0) {
        echo "✅ ENCONTRADOS via modelo:\n";
        foreach ($templateAssignments as $assignment) {
            echo "   - ID: {$assignment->id}\n";
            echo "     Template ID: {$assignment->daily_template_id}\n";
            echo "     Status: {$assignment->status}\n";
            echo "     Created: {$assignment->created_at}\n\n";
        }
    } else {
        echo "❌ NO encontrados via modelo TemplateAssignment\n\n";
    }
    
    echo "🔍 PASO 5: Verificando todas las asignaciones de plantillas en el sistema...\n";
    
    $allTemplateAssignments = TemplateAssignment::with(['dailyTemplate', 'professorStudentAssignment.student'])
                                                ->get();
    
    echo "📊 Total asignaciones de plantillas en sistema: {$allTemplateAssignments->count()}\n";
    
    if ($allTemplateAssignments->count() > 0) {
        echo "📋 TODAS las asignaciones:\n";
        foreach ($allTemplateAssignments as $assignment) {
            $studentName = $assignment->professorStudentAssignment->student->name ?? 'N/A';
            $templateTitle = $assignment->dailyTemplate->title ?? 'N/A';
            echo "   - Estudiante: {$studentName}\n";
            echo "     Plantilla: {$templateTitle}\n";
            echo "     Status: {$assignment->status}\n";
            echo "     Created: {$assignment->created_at}\n\n";
        }
    }
    
    echo "🔍 PASO 6: Verificando estructura de relaciones...\n";
    
    // Verificar relación en ProfessorStudentAssignment
    $assignmentWithTemplates = ProfessorStudentAssignment::with('templateAssignments')
                                                         ->where('student_id', $maria->id)
                                                         ->first();
    
    if ($assignmentWithTemplates && $assignmentWithTemplates->templateAssignments) {
        echo "✅ Relación templateAssignments funciona:\n";
        echo "   Plantillas via relación: {$assignmentWithTemplates->templateAssignments->count()}\n";
    } else {
        echo "❌ Relación templateAssignments no funciona o está vacía\n";
    }
    
    echo "\n🔍 PASO 7: Verificando el último registro creado...\n";
    
    $lastAssignment = DB::table('daily_assignments')
                       ->orderBy('created_at', 'desc')
                       ->first();
    
    if ($lastAssignment) {
        echo "✅ Último registro en daily_assignments:\n";
        echo "   ID: {$lastAssignment->id}\n";
        echo "   Professor-Student Assignment ID: {$lastAssignment->professor_student_assignment_id}\n";
        echo "   Template ID: {$lastAssignment->daily_template_id}\n";
        echo "   Created: {$lastAssignment->created_at}\n";
        
        // Verificar si corresponde a María
        $correspondingPSA = ProfessorStudentAssignment::find($lastAssignment->professor_student_assignment_id);
        if ($correspondingPSA && $correspondingPSA->student_id == $maria->id) {
            echo "   ✅ ESTE REGISTRO ES DE MARÍA GARCÍA\n";
        } else {
            echo "   ⚠️  Este registro NO es de María García\n";
        }
    }
    
    echo "\n🎯 RESUMEN DEL DEBUGGING:\n";
    echo "   📊 Registros directos en BD: {$directQuery->count()}\n";
    echo "   📊 Registros via modelo: {$templateAssignments->count()}\n";
    echo "   📊 Total en sistema: {$allTemplateAssignments->count()}\n";
    
    if ($directQuery->count() > 0 && $templateAssignments->count() === 0) {
        echo "   🚨 PROBLEMA: Hay datos en BD pero el modelo no los encuentra\n";
    } elseif ($directQuery->count() === 0) {
        echo "   🚨 PROBLEMA: No hay datos en la BD\n";
    } else {
        echo "   ✅ TODO FUNCIONA CORRECTAMENTE\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
