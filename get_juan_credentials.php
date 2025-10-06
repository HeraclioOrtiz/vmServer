<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 BUSCANDO CREDENCIALES DE JUAN PÉREZ...\n\n";

// Buscar por email
$juan = \App\Models\User::where('email', 'profesor@villamitre.com')->first();

if ($juan) {
    echo "✅ PROFESOR ENCONTRADO:\n";
    echo "👨‍🏫 Nombre: {$juan->name}\n";
    echo "📧 Email: {$juan->email}\n";
    echo "🆔 DNI: {$juan->dni}\n";
    echo "🔢 ID: {$juan->id}\n";
    echo "👔 Es profesor: " . ($juan->is_professor ? 'SÍ' : 'NO') . "\n";
    echo "👑 Es admin: " . ($juan->is_admin ? 'SÍ' : 'NO') . "\n";
} else {
    echo "❌ Profesor no encontrado con email profesor@villamitre.com\n";
    
    // Buscar por nombre
    echo "\n🔍 Buscando por nombre...\n";
    $juanByName = \App\Models\User::where('name', 'like', '%Juan%')->get();
    
    if ($juanByName->count() > 0) {
        echo "📋 Usuarios con 'Juan' en el nombre:\n";
        foreach ($juanByName as $user) {
            echo "- {$user->name} (Email: {$user->email}, DNI: {$user->dni})\n";
        }
    }
}

// Verificar si necesitamos la contraseña (normalmente está hasheada)
echo "\n⚠️  NOTA SOBRE CONTRASEÑA:\n";
echo "Las contraseñas están hasheadas en la BD por seguridad.\n";
echo "Contraseñas típicas del sistema:\n";
echo "- Profesores: profesor123\n";
echo "- Estudiantes: [nombre]123 (ej: maria123)\n";
echo "- Admin: admin123\n";
