<?php

echo "🔍 === VERIFICANDO TABLAS DE ASIGNACIONES === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Verificar si las tablas existen
    $tables = [
        'professor_student_assignments',
        'template_assignments', 
        'assignment_progress'
    ];
    
    foreach ($tables as $table) {
        try {
            $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
            echo "📋 Tabla '{$table}': " . ($exists ? "✅ EXISTE" : "❌ NO EXISTE") . "\n";
            
            if ($exists) {
                $count = \Illuminate\Support\Facades\DB::table($table)->count();
                echo "   📊 Registros: {$count}\n";
            }
        } catch (Exception $e) {
            echo "📋 Tabla '{$table}': ❌ ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🔍 VERIFICANDO MODELOS:\n";
    
    // Verificar modelos
    $models = [
        'ProfessorStudentAssignment' => \App\Models\Gym\ProfessorStudentAssignment::class,
        'TemplateAssignment' => \App\Models\Gym\TemplateAssignment::class,
        'AssignmentProgress' => \App\Models\Gym\AssignmentProgress::class,
    ];
    
    foreach ($models as $name => $class) {
        try {
            if (class_exists($class)) {
                echo "📦 Modelo '{$name}': ✅ EXISTE\n";
                $instance = new $class();
                echo "   📋 Tabla: {$instance->getTable()}\n";
            } else {
                echo "📦 Modelo '{$name}': ❌ NO EXISTE\n";
            }
        } catch (Exception $e) {
            echo "📦 Modelo '{$name}': ❌ ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🔍 VERIFICANDO MIGRACIONES:\n";
    
    // Buscar migraciones relacionadas
    $migrationFiles = glob('database/migrations/*assignment*.php');
    
    if (empty($migrationFiles)) {
        echo "❌ No se encontraron migraciones de asignaciones\n";
    } else {
        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            echo "📄 {$filename}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
