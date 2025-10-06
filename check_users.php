<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICACIÓN DE USUARIOS ===\n\n";

try {
    $users = App\Models\User::all(['name', 'email', 'dni', 'is_admin', 'is_professor']);
    
    echo "Total usuarios: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "👤 " . $user->name . "\n";
        echo "   Email: " . $user->email . "\n";
        echo "   DNI: " . $user->dni . "\n";
        echo "   Admin: " . ($user->is_admin ? 'Sí' : 'No') . "\n";
        echo "   Profesor: " . ($user->is_professor ? 'Sí' : 'No') . "\n";
        echo "\n";
    }
    
    // Verificar usuario específico
    $admin = App\Models\User::where('dni', '11111111')->first();
    if ($admin) {
        echo "✅ Usuario admin encontrado: " . $admin->name . "\n";
    } else {
        echo "❌ Usuario admin NO encontrado con DNI 11111111\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
