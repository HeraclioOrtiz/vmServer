<?php

echo "👥 === REVISIÓN SIMPLE DE USUARIOS === 👥\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $users = \App\Models\User::all();
    
    echo "📊 TOTAL DE USUARIOS: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "👤 USUARIO #{$user->id}\n";
        echo "📝 Nombre: {$user->name}\n";
        echo "📧 Email: {$user->email}\n";
        echo "🆔 DNI: {$user->dni}\n";
        
        // Verificar tipo de usuario
        echo "🌐 Tipo: " . $user->user_type->value . "\n";
        
        // Verificar roles
        echo "🔐 Admin: " . ($user->is_admin ? 'SÍ' : 'NO') . "\n";
        echo "🔑 Super Admin: " . ($user->is_super_admin ? 'SÍ' : 'NO') . "\n";
        echo "👨‍🏫 Profesor: " . ($user->is_professor ? 'SÍ' : 'NO') . "\n";
        
        // Estado
        echo "📊 Estado: " . $user->account_status->value . "\n";
        
        // Acceso al gimnasio
        $hasGymAccess = $user->is_admin || $user->is_super_admin || $user->is_professor;
        echo "🏋️ Acceso Gimnasio: " . ($hasGymAccess ? '✅ SÍ' : '❌ NO') . "\n";
        
        echo str_repeat("-", 50) . "\n\n";
    }
    
    // Resumen
    echo "📋 RESUMEN:\n";
    
    $apiUsers = $users->filter(function($user) {
        return $user->user_type->value == 'api';
    });
    
    $gymUsers = $users->filter(function($user) {
        return $user->is_admin || $user->is_super_admin || $user->is_professor;
    });
    
    $apiGymUsers = $users->filter(function($user) {
        return $user->user_type->value == 'api' && ($user->is_admin || $user->is_super_admin || $user->is_professor);
    });
    
    echo "🌐 Usuarios API: " . $apiUsers->count() . "\n";
    echo "🏋️ Usuarios con acceso gimnasio: " . $gymUsers->count() . "\n";
    echo "🎯 Usuarios API con acceso gimnasio: " . $apiGymUsers->count() . "\n\n";
    
    if ($apiGymUsers->count() > 0) {
        echo "✅ USUARIOS API CON ACCESO AL GIMNASIO:\n";
        foreach ($apiGymUsers as $user) {
            echo "• {$user->name} ({$user->email}) - DNI: {$user->dni}\n";
        }
    } else {
        echo "❌ NO HAY USUARIOS API CON ACCESO AL GIMNASIO\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
