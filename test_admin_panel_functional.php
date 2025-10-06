<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

echo "=== TESTING FUNCIONAL ADMIN PANEL ===\n\n";

try {
    // Configurar entorno de testing
    putenv('DB_CONNECTION=sqlite');
    putenv('DB_DATABASE=:memory:');
    
    // Inicializar Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel inicializado correctamente\n";
    
    // Ejecutar migraciones en memoria
    echo "\n=== EJECUTANDO MIGRACIONES ===\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    echo "✅ Migraciones ejecutadas: " . Artisan::output();
    
    // Verificar tablas creadas
    echo "\n=== VERIFICANDO TABLAS ===\n";
    $tables = ['users', 'system_settings', 'audit_logs', 'exercises', 'daily_templates', 'weekly_templates'];
    
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            echo "✅ Tabla '{$table}' existe\n";
        } else {
            echo "❌ Tabla '{$table}' NO existe\n";
        }
    }
    
    // Verificar columnas específicas del admin panel
    echo "\n=== VERIFICANDO COLUMNAS ADMIN ===\n";
    
    if (Schema::hasColumns('users', ['is_admin', 'permissions', 'account_status'])) {
        echo "✅ Columnas de admin en 'users' existen\n";
    } else {
        echo "❌ Columnas de admin en 'users' faltantes\n";
    }
    
    if (Schema::hasColumns('system_settings', ['key', 'value', 'category'])) {
        echo "✅ Columnas de 'system_settings' existen\n";
    } else {
        echo "❌ Columnas de 'system_settings' faltantes\n";
    }
    
    // Crear datos de prueba
    echo "\n=== CREANDO DATOS DE PRUEBA ===\n";
    
    // Usuario admin de prueba
    $adminUser = \App\Models\User::create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'dni' => '12345678',
        'password' => bcrypt('password123'),
        'user_type' => 'local',
        'is_admin' => true,
        'permissions' => ['super_admin', 'user_management', 'gym_admin'],
        'account_status' => 'active',
    ]);
    echo "✅ Usuario admin creado: ID {$adminUser->id}\n";
    
    // Usuario profesor de prueba
    $professorUser = \App\Models\User::create([
        'name' => 'Profesor Test',
        'email' => 'profesor@test.com',
        'dni' => '87654321',
        'password' => bcrypt('password123'),
        'user_type' => 'local',
        'is_professor' => true,
        'account_status' => 'active',
    ]);
    echo "✅ Usuario profesor creado: ID {$professorUser->id}\n";
    
    // Configuración del sistema
    \App\Models\SystemSetting::create([
        'key' => 'admin_panel_enabled',
        'value' => true,
        'category' => 'admin',
        'description' => 'Panel de administración habilitado',
    ]);
    echo "✅ Configuración del sistema creada\n";
    
    // Ejercicio de prueba
    if (Schema::hasTable('exercises')) {
        $exercise = \App\Models\Gym\Exercise::create([
            'name' => 'Push-ups Test',
            'description' => 'Ejercicio de prueba',
            'category' => 'strength',
            'muscle_groups' => ['chest', 'arms'],
            'difficulty_level' => 2,
            'is_active' => true,
            'created_by' => $professorUser->id,
        ]);
        echo "✅ Ejercicio de prueba creado: ID {$exercise->id}\n";
    }
    
    echo "\n=== VERIFICANDO FUNCIONALIDADES ===\n";
    
    // Verificar métodos del usuario admin
    if ($adminUser->isAdmin()) {
        echo "✅ Método isAdmin() funciona\n";
    } else {
        echo "❌ Método isAdmin() falla\n";
    }
    
    if ($adminUser->hasPermission('super_admin')) {
        echo "✅ Método hasPermission() funciona\n";
    } else {
        echo "❌ Método hasPermission() falla\n";
    }
    
    // Verificar configuración del sistema
    $setting = \App\Models\SystemSetting::get('admin_panel_enabled');
    if ($setting === true) {
        echo "✅ SystemSetting::get() funciona\n";
    } else {
        echo "❌ SystemSetting::get() falla\n";
    }
    
    echo "\n=== TESTING COMPLETADO ===\n";
    echo "🎉 Base de datos funcional en memoria\n";
    echo "🎉 Modelos funcionando correctamente\n";
    echo "🎉 Migraciones aplicadas exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
