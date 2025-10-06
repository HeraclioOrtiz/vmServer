<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG LOGIN PROCESO ===\n\n";

$dni = '11111111';
$password = 'admin123';

try {
    echo "1. Buscando usuario con DNI: $dni\n";
    $user = App\Models\User::where('dni', $dni)->first();
    
    if (!$user) {
        echo "❌ Usuario NO encontrado en base de datos\n";
        
        // Verificar todos los DNIs
        echo "\nDNIs disponibles en la base de datos:\n";
        $users = App\Models\User::all(['dni', 'name']);
        foreach ($users as $u) {
            echo "- DNI: '{$u->dni}' (Usuario: {$u->name})\n";
        }
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   DNI: '{$user->dni}'\n";
    echo "   User Type: " . ($user->user_type->value ?? $user->user_type) . "\n";
    echo "   Is Admin: " . ($user->is_admin ? 'Sí' : 'No') . "\n";
    echo "   Account Status: {$user->account_status}\n\n";
    
    echo "2. Verificando password...\n";
    if (Hash::check($password, $user->password)) {
        echo "✅ Password correcto\n\n";
        
        echo "3. Creando token...\n";
        $token = $user->createToken('test-token')->plainTextToken;
        echo "✅ Token creado: " . substr($token, 0, 20) . "...\n\n";
        
        echo "4. Simulando respuesta de login:\n";
        $response = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'dni' => $user->dni,
                'is_admin' => $user->is_admin,
                'is_professor' => $user->is_professor,
            ],
            'token' => $token
        ];
        
        echo "✅ Login simulado exitoso:\n";
        echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
        
    } else {
        echo "❌ Password incorrecto\n";
        echo "   Password hash en BD: " . substr($user->password, 0, 20) . "...\n";
        echo "   Password probado: $password\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante debug: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== DEBUG COMPLETADO ===\n";
