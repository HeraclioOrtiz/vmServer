<?php

echo "🔍 === VERIFICANDO ESTRUCTURA DE TABLA DE SERIES === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "📋 Verificando tabla gym_daily_template_sets...\n";
    
    if (\Illuminate\Support\Facades\Schema::hasTable('gym_daily_template_sets')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('gym_daily_template_sets');
        echo "✅ Tabla existe\n";
        echo "📊 Columnas:\n";
        
        foreach ($columns as $i => $column) {
            echo "   " . ($i + 1) . ". {$column}\n";
        }
        
        // Obtener algunos registros de ejemplo
        $sampleSets = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')
            ->limit(5)
            ->get();
        
        echo "\n📊 Registros de ejemplo (" . $sampleSets->count() . "):\n";
        
        foreach ($sampleSets as $i => $set) {
            echo "   Registro " . ($i + 1) . ":\n";
            foreach ($set as $key => $value) {
                echo "      {$key}: " . ($value ?? 'NULL') . "\n";
            }
            echo "\n";
        }
        
    } else {
        echo "❌ Tabla gym_daily_template_sets no existe\n";
        
        // Buscar tablas similares
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        echo "🔍 Buscando tablas similares...\n";
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            if (strpos($tableName, 'set') !== false || strpos($tableName, 'serie') !== false) {
                echo "   - {$tableName}\n";
            }
        }
    }
    
    echo "\n🔍 Verificando otras tablas relacionadas...\n";
    
    $relatedTables = [
        'gym_assigned_sets',
        'gym_daily_template_exercise_sets',
        'template_exercise_sets'
    ];
    
    foreach ($relatedTables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "✅ {$table}: Existe\n";
            
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            echo "   Columnas: " . implode(', ', $columns) . "\n";
            
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "   Registros: {$count}\n\n";
            
        } else {
            echo "❌ {$table}: No existe\n";
        }
    }
    
    echo "🔍 Verificando datos de María García en detalle...\n";
    
    // Buscar las asignaciones de María García paso a paso
    $maria = \App\Models\User::where('dni', '33333333')->first();
    echo "👤 María García ID: {$maria->id}\n";
    
    // Buscar asignación profesor-estudiante
    $profAssignment = \Illuminate\Support\Facades\DB::table('professor_student_assignments')
        ->where('student_id', $maria->id)
        ->first();
    
    if ($profAssignment) {
        echo "✅ Asignación profesor-estudiante: ID {$profAssignment->id}\n";
        
        // Buscar daily assignments
        $dailyAssignments = \Illuminate\Support\Facades\DB::table('daily_assignments')
            ->where('professor_student_assignment_id', $profAssignment->id)
            ->get();
        
        echo "📋 Daily assignments: " . $dailyAssignments->count() . "\n";
        
        foreach ($dailyAssignments as $assignment) {
            echo "   Assignment ID: {$assignment->id}, Template ID: {$assignment->daily_template_id}\n";
            
            // Buscar ejercicios de la plantilla
            $exercises = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises')
                ->where('daily_template_id', $assignment->daily_template_id)
                ->get();
            
            echo "     Ejercicios: " . $exercises->count() . "\n";
            
            foreach ($exercises as $exercise) {
                echo "       Ejercicio ID: {$exercise->id}, Exercise ID: {$exercise->exercise_id}\n";
                
                // Buscar series - probar diferentes nombres de tabla
                $possibleSetsTables = [
                    'gym_daily_template_sets',
                    'gym_assigned_sets',
                    'gym_daily_template_exercise_sets'
                ];
                
                foreach ($possibleSetsTables as $setsTable) {
                    if (\Illuminate\Support\Facades\Schema::hasTable($setsTable)) {
                        $sets = \Illuminate\Support\Facades\DB::table($setsTable)
                            ->where('daily_template_exercise_id', $exercise->id)
                            ->get();
                        
                        if ($sets->count() > 0) {
                            echo "         Series en {$setsTable}: " . $sets->count() . "\n";
                            
                            foreach ($sets->take(2) as $set) {
                                echo "           Set: ";
                                foreach ($set as $key => $value) {
                                    if (in_array($key, ['set_number', 'reps', 'repetitions', 'weight', 'peso', 'duration', 'duracion'])) {
                                        echo "{$key}={$value} ";
                                    }
                                }
                                echo "\n";
                            }
                            break;
                        }
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
