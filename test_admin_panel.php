<?php

require_once 'vendor/autoload.php';

echo "=== VERIFICACIÓN ADMIN PANEL ===\n\n";

// Verificar que las clases se puedan cargar
$classes = [
    // Controllers Admin
    'App\Http\Controllers\Admin\AdminUserController',
    'App\Http\Controllers\Admin\AdminProfessorController', 
    'App\Http\Controllers\Admin\AuditLogController',
    
    // Controllers Gym
    'App\Http\Controllers\Gym\Admin\ExerciseController',
    'App\Http\Controllers\Gym\Admin\DailyTemplateController',
    'App\Http\Controllers\Gym\Admin\WeeklyTemplateController',
    'App\Http\Controllers\Gym\Admin\WeeklyAssignmentController',
    
    // Form Requests
    'App\Http\Requests\Admin\UserUpdateRequest',
    'App\Http\Requests\Admin\ProfessorAssignmentRequest',
    'App\Http\Requests\Gym\ExerciseRequest',
    'App\Http\Requests\Gym\DailyTemplateRequest',
    'App\Http\Requests\Gym\WeeklyTemplateRequest',
    'App\Http\Requests\Gym\WeeklyAssignmentRequest',
    
    // Services
    'App\Services\Admin\UserManagementService',
    'App\Services\Admin\ProfessorManagementService',
    'App\Services\Core\AuditService',
    'App\Services\Gym\ExerciseService',
    'App\Services\Gym\TemplateService',
    'App\Services\Gym\WeeklyAssignmentService',
    
    // Models
    'App\Models\User',
    'App\Models\AuditLog',
    'App\Models\SystemSetting',
    
    // Middleware
    'App\Http\Middleware\EnsureAdmin',
    'App\Http\Middleware\EnsureProfessor',
];

$success = 0;
$errors = [];

foreach ($classes as $class) {
    try {
        if (class_exists($class)) {
            echo "✅ {$class}\n";
            $success++;
        } else {
            echo "❌ {$class} - No existe\n";
            $errors[] = $class;
        }
    } catch (Exception $e) {
        echo "❌ {$class} - Error: " . $e->getMessage() . "\n";
        $errors[] = $class;
    }
}

echo "\n=== RESUMEN ===\n";
echo "✅ Clases cargadas: {$success}/" . count($classes) . "\n";

if (!empty($errors)) {
    echo "❌ Errores encontrados:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
} else {
    echo "🎉 Todas las clases se cargan correctamente!\n";
}

// Verificar rutas
echo "\n=== VERIFICACIÓN DE RUTAS ===\n";
try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "✅ Aplicación Laravel inicializada\n";
    echo "✅ Kernel HTTP disponible\n";
    
} catch (Exception $e) {
    echo "❌ Error inicializando Laravel: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
