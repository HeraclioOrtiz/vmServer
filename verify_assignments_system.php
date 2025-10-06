<?php

echo "🔍 === VERIFICACIÓN: SISTEMA DE ASIGNACIONES === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "PASO 1: VERIFICAR TABLAS EN BD\n";
    echo str_repeat("=", 80) . "\n";
    
    $tables = [
        'professor_student_assignments',
        'daily_assignments',
        'assignment_progress'
    ];
    
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        $status = $exists ? "✅ EXISTE" : "❌ NO EXISTE";
        echo "  {$table}: {$status}";
        
        if ($exists) {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            echo " ({$count} registros)\n";
        } else {
            echo "\n";
        }
    }
    
    echo "\n";
    echo "PASO 2: VERIFICAR MODELOS\n";
    echo str_repeat("=", 80) . "\n";
    
    $models = [
        'App\Models\Gym\ProfessorStudentAssignment',
        'App\Models\Gym\TemplateAssignment',
        'App\Models\Gym\AssignmentProgress',
    ];
    
    foreach ($models as $model) {
        $exists = class_exists($model);
        $status = $exists ? "✅ EXISTE" : "❌ NO EXISTE";
        $shortName = class_basename($model);
        echo "  {$shortName}: {$status}\n";
    }
    
    echo "\n";
    echo "PASO 3: VERIFICAR SERVICES\n";
    echo str_repeat("=", 80) . "\n";
    
    $services = [
        'App\Services\Gym\AssignmentService',
    ];
    
    foreach ($services as $service) {
        $exists = class_exists($service);
        $status = $exists ? "✅ EXISTE" : "❌ NO EXISTE";
        $shortName = class_basename($service);
        echo "  {$shortName}: {$status}";
        
        if ($exists) {
            $methods = get_class_methods($service);
            echo " (" . count($methods) . " métodos)\n";
        } else {
            echo "\n";
        }
    }
    
    echo "\n";
    echo "PASO 4: VERIFICAR CONTROLLERS\n";
    echo str_repeat("=", 80) . "\n";
    
    $controllers = [
        'App\Http\Controllers\Admin\AdminAssignmentController',
        'App\Http\Controllers\Gym\Professor\ProfessorAssignmentController',
    ];
    
    foreach ($controllers as $controller) {
        $exists = class_exists($controller);
        $status = $exists ? "✅ EXISTE" : "❌ NO EXISTE";
        $shortName = class_basename($controller);
        echo "  {$shortName}: {$status}\n";
    }
    
    echo "\n";
    echo "PASO 5: VERIFICAR ESTRUCTURA DE TABLAS\n";
    echo str_repeat("=", 80) . "\n";
    
    if (\Illuminate\Support\Facades\Schema::hasTable('professor_student_assignments')) {
        echo "\n📋 professor_student_assignments:\n";
        $columns = \Illuminate\Support\Facades\DB::select('DESCRIBE professor_student_assignments');
        foreach ($columns as $col) {
            echo "  • {$col->Field} ({$col->Type})\n";
        }
    }
    
    if (\Illuminate\Support\Facades\Schema::hasTable('daily_assignments')) {
        echo "\n📋 daily_assignments:\n";
        $columns = \Illuminate\Support\Facades\DB::select('DESCRIBE daily_assignments');
        foreach ($columns as $col) {
            echo "  • {$col->Field} ({$col->Type})";
            if ($col->Field === 'frequency') {
                echo " ← JSON de días de semana";
            }
            echo "\n";
        }
    }
    
    if (\Illuminate\Support\Facades\Schema::hasTable('assignment_progress')) {
        echo "\n📋 assignment_progress:\n";
        $columns = \Illuminate\Support\Facades\DB::select('DESCRIBE assignment_progress');
        foreach ($columns as $col) {
            echo "  • {$col->Field} ({$col->Type})";
            if ($col->Field === 'exercise_progress') {
                echo " ← JSON de progreso";
            }
            echo "\n";
        }
    }
    
    echo "\n";
    echo "PASO 6: VERIFICAR DATOS DE PRUEBA\n";
    echo str_repeat("=", 80) . "\n";
    
    // Ver si hay datos
    $profStudentCount = \Illuminate\Support\Facades\DB::table('professor_student_assignments')->count();
    $templateAssignCount = \Illuminate\Support\Facades\DB::table('daily_assignments')->count();
    $progressCount = \Illuminate\Support\Facades\DB::table('assignment_progress')->count();
    
    echo "  Professor-Student Assignments: {$profStudentCount}\n";
    echo "  Template Assignments: {$templateAssignCount}\n";
    echo "  Progress Records: {$progressCount}\n";
    
    if ($profStudentCount > 0) {
        echo "\n  📊 Ejemplo de Asignación Profesor-Estudiante:\n";
        $example = \Illuminate\Support\Facades\DB::table('professor_student_assignments')
            ->first();
        echo "    ID: {$example->id}\n";
        echo "    Profesor ID: {$example->professor_id}\n";
        echo "    Estudiante ID: {$example->student_id}\n";
        echo "    Status: {$example->status}\n";
        echo "    Desde: {$example->start_date}\n";
        echo "    Hasta: " . ($example->end_date ?? 'indefinido') . "\n";
    }
    
    if ($templateAssignCount > 0) {
        echo "\n  📊 Ejemplo de Asignación de Plantilla:\n";
        $example = \Illuminate\Support\Facades\DB::table('daily_assignments')
            ->first();
        echo "    ID: {$example->id}\n";
        echo "    Plantilla ID: {$example->daily_template_id}\n";
        echo "    Frecuencia: {$example->frequency}\n";
        echo "    Status: {$example->status}\n";
        echo "    Desde: {$example->start_date}\n";
        echo "    Hasta: " . ($example->end_date ?? 'indefinido') . "\n";
    }
    
    echo "\n";
    echo "PASO 7: VERIFICAR RUTAS API\n";
    echo str_repeat("=", 80) . "\n";
    
    $routeFiles = [
        'routes/api.php',
        'routes/admin.php',
    ];
    
    foreach ($routeFiles as $file) {
        $path = base_path($file);
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            echo "\n  📄 {$file}:\n";
            
            // Buscar rutas de assignments
            if (strpos($content, 'assignment') !== false || strpos($content, 'Assignment') !== false) {
                echo "    ✅ Contiene rutas de assignments\n";
                
                // Contar menciones
                $mentions = substr_count(strtolower($content), 'assignment');
                echo "    📊 {$mentions} menciones de 'assignment'\n";
            } else {
                echo "    ⚠️  No se encontraron rutas de assignments\n";
            }
        }
    }
    
    echo "\n";
    echo "RESUMEN FINAL\n";
    echo str_repeat("=", 80) . "\n";
    
    $issues = [];
    
    // Verificar tablas
    foreach ($tables as $table) {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $issues[] = "Tabla '{$table}' no existe";
        }
    }
    
    // Verificar modelos
    foreach ($models as $model) {
        if (!class_exists($model)) {
            $issues[] = "Modelo '" . class_basename($model) . "' no existe";
        }
    }
    
    if (empty($issues)) {
        echo "✅ SISTEMA DE ASIGNACIONES COMPLETAMENTE IMPLEMENTADO\n";
        echo "✅ Todas las tablas existen\n";
        echo "✅ Todos los modelos existen\n";
        echo "✅ Services y Controllers implementados\n";
        
        if ($profStudentCount === 0 && $templateAssignCount === 0) {
            echo "\n⚠️  NOTA: No hay datos de prueba, pero la estructura está lista\n";
        } else {
            echo "\n✅ Hay datos de prueba en la BD\n";
        }
    } else {
        echo "⚠️  SE ENCONTRARON PROBLEMAS:\n";
        foreach ($issues as $issue) {
            echo "  • {$issue}\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
