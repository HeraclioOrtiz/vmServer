<?php

echo "🧹 === LIMPIEZA COMPLETA DE BASE DE DATOS === 🧹\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "⚠️  ADVERTENCIA: Este script eliminará TODOS los datos de ejercicios, plantillas y asignaciones\n";
    echo "📦 Backup disponible: backup_*_2025-09-30_15-37-18.json\n";
    echo "✅ Usuarios se mantendrán (María García, admins, etc.)\n\n";
    
    echo "🔍 PASO 1: VERIFICAR DATOS ACTUALES\n";
    echo str_repeat("=", 60) . "\n";
    
    // Tablas a limpiar en orden (respetando foreign keys)
    $tablesToClean = [
        'gym_assignment_progress' => 'Progreso de asignaciones',
        'gym_template_assignments' => 'Asignaciones de plantillas',
        'gym_professor_student_assignments' => 'Asignaciones profesor-estudiante',
        'gym_daily_template_sets' => 'Series',
        'gym_daily_template_exercises' => 'Ejercicios en plantillas',
        'gym_daily_templates' => 'Plantillas diarias',
        'gym_exercises' => 'Ejercicios',
        'personal_access_tokens' => 'Tokens de acceso'
    ];
    
    $totalRecords = 0;
    foreach ($tablesToClean as $table => $description) {
        try {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "  {$description}: {$count} registros\n";
            $totalRecords += $count;
        } catch (Exception $e) {
            echo "  {$description}: TABLA NO EXISTE\n";
        }
    }
    
    echo "\n📊 Total de registros a eliminar: {$totalRecords}\n\n";
    
    echo "🗑️  PASO 2: LIMPIEZA DE TABLAS\n";
    echo str_repeat("=", 60) . "\n";
    
    // Desactivar temporalmente las foreign key constraints
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    $eliminados = 0;
    foreach ($tablesToClean as $table => $description) {
        try {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            
            if ($count > 0) {
                \Illuminate\Support\Facades\DB::table($table)->truncate();
                echo "  ✅ {$description}: {$count} registros eliminados\n";
                $eliminados += $count;
            } else {
                echo "  ⚪ {$description}: Ya estaba vacía\n";
            }
        } catch (Exception $e) {
            echo "  ❌ {$description}: Error - " . $e->getMessage() . "\n";
        }
    }
    
    // Reactivar foreign key constraints
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n🔍 PASO 3: VERIFICAR LIMPIEZA\n";
    echo str_repeat("=", 60) . "\n";
    
    $remainingRecords = 0;
    foreach ($tablesToClean as $table => $description) {
        try {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            if ($count > 0) {
                echo "  ⚠️  {$description}: {$count} registros restantes\n";
                $remainingRecords += $count;
            } else {
                echo "  ✅ {$description}: Vacía\n";
            }
        } catch (Exception $e) {
            echo "  ⚪ {$description}: N/A\n";
        }
    }
    
    echo "\n📊 PASO 4: VERIFICAR USUARIOS (CONSERVADOS)\n";
    echo str_repeat("=", 60) . "\n";
    
    $users = \Illuminate\Support\Facades\DB::table('users')
        ->select('id', 'name', 'email', 'user_type')
        ->get();
    
    echo "👥 Usuarios conservados: {$users->count()}\n";
    foreach ($users as $user) {
        echo "  • {$user->name} ({$user->email}) - {$user->user_type}\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 LIMPIEZA COMPLETA FINALIZADA\n";
    echo str_repeat("=", 70) . "\n";
    
    if ($remainingRecords === 0) {
        echo "✅ Base de datos limpiada exitosamente\n";
        echo "✅ {$eliminados} registros eliminados\n";
        echo "✅ Usuarios conservados: {$users->count()}\n";
        echo "✅ Estructura de tablas intacta\n\n";
        echo "🎯 SIGUIENTE PASO: Corrección de estructura de API y BD\n";
    } else {
        echo "⚠️  Advertencia: {$remainingRecords} registros no pudieron ser eliminados\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "\n⚠️  IMPORTANTE: Si hubo error, restaura desde backup\n";
    echo "🔄 Para restaurar: php restore_backup_2025-09-30_15-37-18.php\n";
}
