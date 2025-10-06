<?php

echo "👥 === REVISIÓN DE USUARIOS Y ACCESO AL GIMNASIO === 👥\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Consultando usuarios en la base de datos...\n\n";

$users = \App\Models\User::all();

echo "📊 TOTAL DE USUARIOS: " . $users->count() . "\n\n";

echo str_repeat("=", 80) . "\n";
echo "LISTADO COMPLETO DE USUARIOS\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($users as $user) {
    echo "👤 USUARIO #{$user->id}\n";
    echo str_repeat("-", 50) . "\n";
    echo "📝 Nombre: {$user->name}\n";
    echo "📧 Email: {$user->email}\n";
    echo "🆔 DNI: {$user->dni}\n";
    echo "📱 Teléfono: " . ($user->phone ?? 'No especificado') . "\n";
    echo "🏠 Dirección: " . ($user->address ?? 'No especificada') . "\n";
    
    // Tipo de usuario
    echo "👥 Tipo de usuario: {$user->user_type->value}\n";
    
    // Roles y permisos
    echo "🔐 Es Admin: " . ($user->is_admin ? 'SÍ' : 'NO') . "\n";
    echo "🔐 Es Super Admin: " . ($user->is_super_admin ? 'SÍ' : 'NO') . "\n";
    echo "👨‍🏫 Es Profesor: " . ($user->is_professor ? 'SÍ' : 'NO') . "\n";
    
    // Estado
    echo "📊 Estado: {$user->account_status->value}\n";
    echo "✅ Verificado: " . ($user->email_verified_at ? 'SÍ' : 'NO') . "\n";
    
    // Fechas
    echo "📅 Creado: {$user->created_at}\n";
    echo "🔄 Actualizado: {$user->updated_at}\n";
    
    // Verificar si tiene acceso al gimnasio
    $hasGymAccess = $user->is_admin || $user->is_super_admin || $user->is_professor;
    echo "🏋️ ACCESO AL GIMNASIO: " . ($hasGymAccess ? '✅ SÍ' : '❌ NO') . "\n";
    
    // Si es de tipo API, mostrar información adicional
    if ($user->user_type->value === 'api') {
        echo "🌐 USUARIO API: ✅ SÍ\n";
        echo "🔄 Necesita refresh: " . ($user->needsRefresh() ? 'SÍ' : 'NO') . "\n";
    } else {
        echo "🌐 USUARIO API: ❌ NO (es local)\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "RESUMEN POR CATEGORÍAS\n";
echo str_repeat("=", 80) . "\n\n";

// Usuarios por tipo
$apiUsers = $users->where('user_type', \App\Enums\UserType::API);
$localUsers = $users->where('user_type', \App\Enums\UserType::LOCAL);

echo "📊 USUARIOS POR TIPO:\n";
echo "🌐 API: " . $apiUsers->count() . " usuarios\n";
echo "🏠 LOCAL: " . $localUsers->count() . " usuarios\n\n";

// Usuarios con acceso al gimnasio
$adminUsers = $users->where('is_admin', true);
$superAdminUsers = $users->where('is_super_admin', true);
$professorUsers = $users->where('is_professor', true);
$gymUsers = $users->filter(function($user) {
    return $user->is_admin || $user->is_super_admin || $user->is_professor;
});

echo "🏋️ USUARIOS CON ACCESO AL GIMNASIO:\n";
echo "👨‍💼 Administradores: " . $adminUsers->count() . "\n";
echo "🔑 Super Administradores: " . $superAdminUsers->count() . "\n";
echo "👨‍🏫 Profesores: " . $professorUsers->count() . "\n";
echo "🏋️ TOTAL CON ACCESO: " . $gymUsers->count() . " usuarios\n\n";

// Usuarios por estado
$activeUsers = $users->where('account_status', \App\Enums\AccountStatus::ACTIVE);
$inactiveUsers = $users->where('account_status', \App\Enums\AccountStatus::INACTIVE);
$suspendedUsers = $users->where('account_status', \App\Enums\AccountStatus::SUSPENDED);

echo "📊 USUARIOS POR ESTADO:\n";
echo "✅ Activos: " . $activeUsers->count() . "\n";
echo "⏸️ Inactivos: " . $inactiveUsers->count() . "\n";
echo "🚫 Suspendidos: " . $suspendedUsers->count() . "\n\n";

echo str_repeat("=", 80) . "\n";
echo "USUARIOS API CON ACCESO AL GIMNASIO\n";
echo str_repeat("=", 80) . "\n\n";

$apiGymUsers = $users->filter(function($user) {
    return $user->user_type->value === 'api' && 
           ($user->is_admin || $user->is_super_admin || $user->is_professor);
});

if ($apiGymUsers->count() > 0) {
    echo "🎯 USUARIOS API CON ACCESO AL GIMNASIO: " . $apiGymUsers->count() . "\n\n";
    
    foreach ($apiGymUsers as $user) {
        echo "👤 {$user->name} (ID: {$user->id})\n";
        echo "   📧 {$user->email}\n";
        echo "   🆔 DNI: {$user->dni}\n";
        echo "   🔐 Roles: ";
        $roles = [];
        if ($user->is_super_admin) $roles[] = 'Super Admin';
        if ($user->is_admin) $roles[] = 'Admin';
        if ($user->is_professor) $roles[] = 'Profesor';
        echo implode(', ', $roles) . "\n";
        echo "   📊 Estado: {$user->account_status->value}\n\n";
    }
} else {
    echo "❌ NO HAY USUARIOS API CON ACCESO AL GIMNASIO\n\n";
}

echo str_repeat("=", 80) . "\n";
echo "USUARIOS ESTUDIANTES (SIN ACCESO ADMIN)\n";
echo str_repeat("=", 80) . "\n\n";

$studentUsers = $users->filter(function($user) {
    return !$user->is_admin && !$user->is_super_admin && !$user->is_professor;
});

echo "👨‍🎓 ESTUDIANTES: " . $studentUsers->count() . "\n\n";

foreach ($studentUsers as $user) {
    echo "👤 {$user->name} (ID: {$user->id})\n";
    echo "   📧 {$user->email}\n";
    echo "   🆔 DNI: {$user->dni}\n";
    echo "   🌐 Tipo: {$user->user_type->value}\n";
    echo "   📊 Estado: {$user->account_status->value}\n\n";
}

echo str_repeat("=", 80) . "\n";
echo "RECOMENDACIONES\n";
echo str_repeat("=", 80) . "\n\n";

echo "📋 PARA EL DESARROLLO:\n\n";

if ($gymUsers->count() === 0) {
    echo "⚠️ NO HAY USUARIOS CON ACCESO AL GIMNASIO\n";
    echo "💡 Necesitas crear o promover usuarios para acceso admin/profesor\n\n";
}

if ($apiGymUsers->count() === 0) {
    echo "⚠️ NO HAY USUARIOS API CON ACCESO AL GIMNASIO\n";
    echo "💡 Los usuarios API pueden crear/modificar ejercicios y plantillas\n\n";
}

if ($studentUsers->count() === 0) {
    echo "⚠️ NO HAY USUARIOS ESTUDIANTES\n";
    echo "💡 Necesitas usuarios estudiantes para testing de la app móvil\n\n";
}

echo "🎯 USUARIOS RECOMENDADOS PARA TESTING:\n";
echo "• 1 Super Admin (acceso completo)\n";
echo "• 1-2 Profesores (crear plantillas y asignar)\n";
echo "• 2-3 Estudiantes (testing app móvil)\n\n";

echo "🔑 CREDENCIALES DE TESTING ACTUALES:\n";
$maria = $users->where('email', 'maria.garcia@villamitre.com')->first();
if ($maria) {
    echo "👤 María García (Estudiante)\n";
    echo "   🆔 DNI: {$maria->dni}\n";
    echo "   🔑 Password: maria123\n";
    echo "   🏋️ Acceso gimnasio: " . ($maria->is_admin || $maria->is_professor ? 'SÍ' : 'NO') . "\n\n";
}

echo "🚀 SISTEMA LISTO PARA: " . ($gymUsers->count() > 0 && $studentUsers->count() > 0 ? 'TESTING COMPLETO' : 'CONFIGURACIÓN ADICIONAL') . "\n";
