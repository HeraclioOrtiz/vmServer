<?php

echo "🔍 === VERIFICACIÓN: BASE DE DATOS GIMNASIO LIMPIA === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "PASO 1: VERIFICAR EXISTENCIA DE TABLAS\n";
    echo str_repeat("=", 70) . "\n";
    
    $tables = [
        'gym_exercises',
        'gym_daily_templates',
        'gym_daily_template_exercises',
        'gym_daily_template_sets'
    ];
    
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        $status = $exists ? "✅ EXISTE" : "❌ NO EXISTE";
        echo "  {$table}: {$status}\n";
    }
    
    echo "\n";
    echo "PASO 2: CONTAR REGISTROS EN CADA TABLA\n";
    echo str_repeat("=", 70) . "\n";
    
    $exercises = \Illuminate\Support\Facades\DB::table('gym_exercises')->count();
    $templates = \Illuminate\Support\Facades\DB::table('gym_daily_templates')->count();
    $templateExercises = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises')->count();
    $sets = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets')->count();
    
    echo "  gym_exercises: {$exercises} registros\n";
    echo "  gym_daily_templates: {$templates} registros\n";
    echo "  gym_daily_template_exercises: {$templateExercises} registros\n";
    echo "  gym_daily_template_sets: {$sets} registros\n";
    
    echo "\n";
    echo "PASO 3: VERIFICAR ESTRUCTURA DE COLUMNAS (gym_exercises)\n";
    echo str_repeat("=", 70) . "\n";
    
    $exerciseColumns = \Illuminate\Support\Facades\DB::select('DESCRIBE gym_exercises');
    
    $expectedColumns = [
        'id', 'name', 'description', 'muscle_groups', 'target_muscle_groups',
        'movement_pattern', 'equipment', 'difficulty_level', 'tags', 
        'instructions', 'created_at', 'updated_at'
    ];
    
    $actualColumns = array_map(fn($col) => $col->Field, $exerciseColumns);
    
    echo "  Columnas esperadas vs actuales:\n";
    foreach ($expectedColumns as $expected) {
        $exists = in_array($expected, $actualColumns);
        $status = $exists ? "✅" : "❌";
        echo "    {$status} {$expected}\n";
    }
    
    // Verificar columnas que NO deberían existir
    $forbiddenColumns = ['tempo', 'muscle_group', 'difficulty'];
    echo "\n  Columnas que NO deberían existir:\n";
    foreach ($forbiddenColumns as $forbidden) {
        $exists = in_array($forbidden, $actualColumns);
        $status = $exists ? "❌ EXISTE (PROBLEMA)" : "✅ NO EXISTE (OK)";
        echo "    {$status} {$forbidden}\n";
    }
    
    echo "\n";
    echo "PASO 4: VERIFICAR ESTRUCTURA DE COLUMNAS (gym_daily_template_sets)\n";
    echo str_repeat("=", 70) . "\n";
    
    $setColumns = \Illuminate\Support\Facades\DB::select('DESCRIBE gym_daily_template_sets');
    $setColumnNames = array_map(fn($col) => $col->Field, $setColumns);
    
    $expectedSetColumns = [
        'id', 'daily_template_exercise_id', 'set_number', 'reps_min', 
        'reps_max', 'rest_seconds', 'rpe_target', 'notes', 
        'created_at', 'updated_at'
    ];
    
    echo "  Columnas esperadas:\n";
    foreach ($expectedSetColumns as $expected) {
        $exists = in_array($expected, $setColumnNames);
        $status = $exists ? "✅" : "❌";
        echo "    {$status} {$expected}\n";
    }
    
    // Verificar que tempo NO exista
    $tempoExists = in_array('tempo', $setColumnNames);
    $tempoStatus = $tempoExists ? "❌ EXISTE (PROBLEMA)" : "✅ NO EXISTE (OK)";
    echo "\n  Columna 'tempo':\n";
    echo "    {$tempoStatus}\n";
    
    echo "\n";
    echo "PASO 5: VERIFICAR INTEGRIDAD REFERENCIAL\n";
    echo str_repeat("=", 70) . "\n";
    
    // Verificar que no haya template_exercises sin exercise_id válido
    $orphanTemplateExercises = \Illuminate\Support\Facades\DB::table('gym_daily_template_exercises as dte')
        ->leftJoin('gym_exercises as e', 'dte.exercise_id', '=', 'e.id')
        ->whereNull('e.id')
        ->whereNotNull('dte.exercise_id')
        ->count();
    
    echo "  Template exercises huérfanos (sin ejercicio válido): {$orphanTemplateExercises}\n";
    
    // Verificar que no haya sets sin template_exercise válido
    $orphanSets = \Illuminate\Support\Facades\DB::table('gym_daily_template_sets as s')
        ->leftJoin('gym_daily_template_exercises as dte', 's.daily_template_exercise_id', '=', 'dte.id')
        ->whereNull('dte.id')
        ->count();
    
    echo "  Sets huérfanos (sin template_exercise válido): {$orphanSets}\n";
    
    // Verificar que no haya templates sin created_by válido
    $templatesWithInvalidUser = \Illuminate\Support\Facades\DB::table('gym_daily_templates as t')
        ->leftJoin('users as u', 't.created_by', '=', 'u.id')
        ->whereNull('u.id')
        ->whereNotNull('t.created_by')
        ->count();
    
    echo "  Templates con created_by inválido: {$templatesWithInvalidUser}\n";
    
    echo "\n";
    echo "PASO 6: VERIFICAR DATOS DE EJERCICIOS (si existen)\n";
    echo str_repeat("=", 70) . "\n";
    
    if ($exercises > 0) {
        $exerciseSample = \Illuminate\Support\Facades\DB::table('gym_exercises')
            ->limit(3)
            ->get();
        
        foreach ($exerciseSample as $i => $ex) {
            echo "\n  Ejercicio " . ($i + 1) . ":\n";
            echo "    ID: {$ex->id}\n";
            echo "    Nombre: {$ex->name}\n";
            echo "    Description: " . (isset($ex->description) ? (strlen($ex->description) > 50 ? substr($ex->description, 0, 50) . "..." : $ex->description) : "NULL") . "\n";
            
            if (isset($ex->muscle_groups)) {
                $muscleGroups = is_string($ex->muscle_groups) ? json_decode($ex->muscle_groups) : $ex->muscle_groups;
                echo "    Muscle Groups: " . (is_array($muscleGroups) ? implode(", ", $muscleGroups) : "NULL") . "\n";
            } else {
                echo "    Muscle Groups: Campo no existe\n";
            }
            
            if (isset($ex->target_muscle_groups)) {
                $targetGroups = is_string($ex->target_muscle_groups) ? json_decode($ex->target_muscle_groups) : $ex->target_muscle_groups;
                echo "    Target Muscle Groups: " . (is_array($targetGroups) ? implode(", ", $targetGroups) : "NULL") . "\n";
            } else {
                echo "    Target Muscle Groups: Campo no existe\n";
            }
            
            echo "    Difficulty Level: " . (isset($ex->difficulty_level) ? $ex->difficulty_level : "Campo no existe") . "\n";
            
            // Verificar campos viejos que no deberían existir
            if (isset($ex->tempo)) {
                echo "    ⚠️  PROBLEMA: Campo 'tempo' existe con valor: {$ex->tempo}\n";
            }
            if (isset($ex->muscle_group)) {
                echo "    ⚠️  PROBLEMA: Campo 'muscle_group' (singular) existe con valor: {$ex->muscle_group}\n";
            }
        }
    } else {
        echo "  No hay ejercicios en la BD (tabla vacía)\n";
    }
    
    echo "\n\n";
    echo "PASO 7: RESUMEN FINAL\n";
    echo str_repeat("=", 70) . "\n";
    
    $issues = [];
    
    if (in_array('tempo', $actualColumns)) {
        $issues[] = "Campo 'tempo' existe en gym_exercises";
    }
    if (in_array('muscle_group', $actualColumns)) {
        $issues[] = "Campo 'muscle_group' (singular) existe en gym_exercises";
    }
    if (in_array('difficulty', $actualColumns)) {
        $issues[] = "Campo 'difficulty' existe en gym_exercises";
    }
    if ($tempoExists) {
        $issues[] = "Campo 'tempo' existe en gym_daily_template_sets";
    }
    if ($orphanTemplateExercises > 0) {
        $issues[] = "{$orphanTemplateExercises} template exercises huérfanos";
    }
    if ($orphanSets > 0) {
        $issues[] = "{$orphanSets} sets huérfanos";
    }
    if ($templatesWithInvalidUser > 0) {
        $issues[] = "{$templatesWithInvalidUser} templates con created_by inválido";
    }
    
    if (empty($issues)) {
        echo "✅ BASE DE DATOS LIMPIA Y CONSISTENTE\n";
        echo "✅ Todas las tablas tienen la estructura correcta\n";
        echo "✅ No hay campos obsoletos\n";
        echo "✅ No hay datos huérfanos\n";
        echo "✅ Integridad referencial correcta\n\n";
        
        echo "📊 ESTADÍSTICAS:\n";
        echo "  • Ejercicios: {$exercises}\n";
        echo "  • Plantillas: {$templates}\n";
        echo "  • Ejercicios en plantillas: {$templateExercises}\n";
        echo "  • Sets totales: {$sets}\n";
    } else {
        echo "⚠️  SE ENCONTRARON PROBLEMAS:\n";
        foreach ($issues as $issue) {
            echo "  • {$issue}\n";
        }
        
        echo "\n";
        echo "🔧 RECOMENDACIÓN:\n";
        echo "  Se necesita ejecutar las migraciones correctamente o limpiar la BD\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
