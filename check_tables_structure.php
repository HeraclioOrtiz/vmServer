<?php

echo "🔍 === VERIFICANDO ESTRUCTURA DE TABLAS === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "📋 Listando todas las tablas de la BD...\n";
    
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    
    $gymTables = [];
    $allTables = [];
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        $allTables[] = $tableName;
        
        if (strpos($tableName, 'gym') !== false || strpos($tableName, 'template') !== false || strpos($tableName, 'assignment') !== false) {
            $gymTables[] = $tableName;
        }
    }
    
    echo "📊 Total tablas: " . count($allTables) . "\n";
    echo "🏋️ Tablas relacionadas con gym/templates/assignments:\n";
    
    foreach ($gymTables as $table) {
        echo "   - {$table}\n";
    }
    
    echo "\n🔍 Verificando tablas específicas...\n";
    
    $criticalTables = [
        'gym_daily_templates',
        'gym_daily_template_exercises', 
        'gym_daily_template_exercise_sets',
        'template_assignments',
        'professor_student_assignments'
    ];
    
    foreach ($criticalTables as $table) {
        if (in_array($table, $allTables)) {
            echo "✅ {$table}: Existe\n";
            
            // Mostrar columnas
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            echo "   Columnas: " . implode(', ', $columns) . "\n";
            
            // Contar registros
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "   Registros: {$count}\n\n";
            
        } else {
            echo "❌ {$table}: NO EXISTE\n\n";
        }
    }
    
    echo "🔍 Verificando datos de María García...\n";
    
    // Verificar usuario
    $maria = \App\Models\User::where('dni', '33333333')->first();
    if ($maria) {
        echo "✅ María García encontrada (ID: {$maria->id})\n";
        
        // Verificar asignaciones profesor-estudiante
        if (in_array('professor_student_assignments', $allTables)) {
            $profAssignments = \Illuminate\Support\Facades\DB::table('professor_student_assignments')
                ->where('student_id', $maria->id)
                ->get();
            
            echo "📊 Asignaciones profesor-estudiante: " . $profAssignments->count() . "\n";
            
            foreach ($profAssignments as $assignment) {
                echo "   - ID: {$assignment->id}, Profesor: {$assignment->professor_id}, Estado: {$assignment->status}\n";
                
                // Verificar template assignments
                if (in_array('template_assignments', $allTables)) {
                    $templateAssignments = \Illuminate\Support\Facades\DB::table('template_assignments')
                        ->where('professor_student_assignment_id', $assignment->id)
                        ->get();
                    
                    echo "     Plantillas asignadas: " . $templateAssignments->count() . "\n";
                    
                    foreach ($templateAssignments as $templateAssignment) {
                        echo "       - Template ID: {$templateAssignment->daily_template_id}\n";
                        
                        // Verificar ejercicios de la plantilla
                        if (in_array('gym_daily_template_exercises', $allTables)) {
                            $exercises = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises')
                                ->where('daily_template_id', $templateAssignment->daily_template_id)
                                ->get();
                            
                            echo "         Ejercicios: " . $exercises->count() . "\n";
                            
                            foreach ($exercises as $exercise) {
                                // Verificar series
                                if (in_array('gym_daily_template_exercise_sets', $allTables)) {
                                    $sets = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercise_sets')
                                        ->where('daily_template_exercise_id', $exercise->id)
                                        ->get();
                                    
                                    echo "           Ejercicio {$exercise->id}: " . $sets->count() . " series\n";
                                    
                                    foreach ($sets as $set) {
                                        echo "             Serie {$set->set_number}: reps={$set->reps}, weight={$set->weight}, duration={$set->duration}\n";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        echo "❌ María García no encontrada\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
