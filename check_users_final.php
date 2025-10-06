<?php

echo "👥 === REVISIÓN FINAL DE USUARIOS === 👥\n\n";

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
        echo "🌐 Tipo: " . $user->user_type->value . "\n";
        echo "🔐 Admin: " . ($user->is_admin ? 'SÍ' : 'NO') . "\n";
        echo "🔑 Super Admin: " . ($user->is_super_admin ? 'SÍ' : 'NO') . "\n";
        echo "👨‍🏫 Profesor: " . ($user->is_professor ? 'SÍ' : 'NO') . "\n";
        
        // Acceso al gimnasio
        $hasGymAccess = $user->is_admin || $user->is_super_admin || $user->is_professor;
        echo "🏋️ Acceso Gimnasio: " . ($hasGymAccess ? '✅ SÍ' : '❌ NO') . "\n";
        
        // Si es API y tiene acceso al gimnasio
        $isApiWithGym = $user->user_type->value == 'api' && $hasGymAccess;
        echo "🎯 API + Gimnasio: " . ($isApiWithGym ? '✅ SÍ' : '❌ NO') . "\n";
        
        echo str_repeat("-", 50) . "\n\n";
    }
    
    // Resumen detallado
    echo str_repeat("=", 60) . "\n";
    echo "ANÁLISIS DETALLADO\n";
    echo str_repeat("=", 60) . "\n\n";
    
    $apiUsers = $users->filter(function($user) {
        return $user->user_type->value == 'api';
    });
    
    $localUsers = $users->filter(function($user) {
        return $user->user_type->value == 'local';
    });
    
    $adminUsers = $users->filter(function($user) {
        return $user->is_admin;
    });
    
    $superAdminUsers = $users->filter(function($user) {
        return $user->is_super_admin;
    });
    
    $professorUsers = $users->filter(function($user) {
        return $user->is_professor;
    });
    
    $gymUsers = $users->filter(function($user) {
        return $user->is_admin || $user->is_super_admin || $user->is_professor;
    });
    
    $apiGymUsers = $users->filter(function($user) {
        return $user->user_type->value == 'api' && ($user->is_admin || $user->is_super_admin || $user->is_professor);
    });
    
    $studentUsers = $users->filter(function($user) {
        return !$user->is_admin && !$user->is_super_admin && !$user->is_professor;
    });
    
    echo "📊 POR TIPO DE USUARIO:\n";
    echo "🌐 API: " . $apiUsers->count() . " usuarios\n";
    echo "🏠 Local: " . $localUsers->count() . " usuarios\n\n";
    
    echo "🔐 POR ROLES:\n";
    echo "👨‍💼 Administradores: " . $adminUsers->count() . "\n";
    echo "🔑 Super Administradores: " . $superAdminUsers->count() . "\n";
    echo "👨‍🏫 Profesores: " . $professorUsers->count() . "\n";
    echo "👨‍🎓 Estudiantes: " . $studentUsers->count() . "\n\n";
    
    echo "🏋️ ACCESO AL GIMNASIO:\n";
    echo "✅ Con acceso: " . $gymUsers->count() . " usuarios\n";
    echo "❌ Sin acceso: " . $studentUsers->count() . " usuarios\n\n";
    
    echo "🎯 USUARIOS API CON ACCESO AL GIMNASIO: " . $apiGymUsers->count() . "\n";
    
    if ($apiGymUsers->count() > 0) {
        echo "\n✅ LISTADO:\n";
        foreach ($apiGymUsers as $user) {
            $roles = [];
            if ($user->is_super_admin) $roles[] = 'Super Admin';
            if ($user->is_admin) $roles[] = 'Admin';
            if ($user->is_professor) $roles[] = 'Profesor';
            
            echo "• {$user->name}\n";
            echo "  📧 {$user->email}\n";
            echo "  🆔 DNI: {$user->dni}\n";
            echo "  🔐 Roles: " . implode(', ', $roles) . "\n\n";
        }
    } else {
        echo "\n❌ NO HAY USUARIOS API CON ACCESO AL GIMNASIO\n\n";
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "USUARIOS PARA TESTING DE APP MÓVIL\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "👨‍🎓 ESTUDIANTES (para testing app móvil):\n";
    foreach ($studentUsers as $user) {
        echo "• {$user->name} - DNI: {$user->dni} - Tipo: {$user->user_type->value}\n";
    }
    
    if ($studentUsers->count() == 0) {
        echo "❌ NO HAY ESTUDIANTES PARA TESTING\n";
    }
    
    echo "\n🔑 CREDENCIALES DE TESTING:\n";
    $maria = $users->where('email', 'maria.garcia@villamitre.com')->first();
    if ($maria) {
        echo "👤 María García\n";
        echo "  🆔 DNI: {$maria->dni}\n";
        echo "  🔑 Password: maria123\n";
        echo "  🌐 Tipo: {$maria->user_type->value}\n";
        echo "  🏋️ Acceso gimnasio: " . ($maria->is_admin || $maria->is_professor ? 'SÍ' : 'NO') . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "RECOMENDACIONES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    if ($apiGymUsers->count() == 0) {
        echo "⚠️ PROBLEMA: No hay usuarios API con acceso al gimnasio\n";
        echo "💡 SOLUCIÓN: Necesitas promover un usuario a API y darle permisos de profesor/admin\n\n";
    }
    
    if ($gymUsers->count() == 0) {
        echo "⚠️ PROBLEMA: No hay usuarios con acceso al gimnasio\n";
        echo "💡 SOLUCIÓN: Necesitas crear usuarios admin/profesor\n\n";
    }
    
    if ($studentUsers->count() == 0) {
        echo "⚠️ PROBLEMA: No hay estudiantes para testing\n";
        echo "💡 SOLUCIÓN: Necesitas usuarios sin permisos admin para testing de app móvil\n\n";
    }
    
    echo "🚀 ESTADO ACTUAL: ";
    if ($apiGymUsers->count() > 0 && $studentUsers->count() > 0) {
        echo "✅ LISTO PARA DESARROLLO COMPLETO\n";
    } elseif ($gymUsers->count() > 0 && $studentUsers->count() > 0) {
        echo "⚠️ LISTO PARA APP MÓVIL, FALTA API PARA ADMIN\n";
    } else {
        echo "❌ NECESITA CONFIGURACIÓN ADICIONAL\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
