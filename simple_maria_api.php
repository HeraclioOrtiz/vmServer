<?php

echo "🔧 === CONFIGURACIÓN SIMPLE MARÍA GARCÍA API === 🔧\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔍 Buscando usuario María García...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    if (!$maria) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$maria->name}\n";
    echo "📊 Estado actual: user_type = " . ($maria->user_type->value ?? 'N/A') . "\n\n";
    
    echo "🔧 Actualizando a usuario API...\n";
    
    // Usar DB directamente para evitar problemas con enums
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $maria->id)
        ->update([
            'user_type' => 'api',
            'type_label' => 'Usuario API',
            'promotion_status' => 'approved',
            'account_status' => 'active',
            'email_verified_at' => now(),
            'password' => bcrypt('estudiante123'),
            'updated_at' => now()
        ]);
    
    echo "✅ Usuario actualizado via DB directa\n\n";
    
    // Recargar usuario
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    echo "📊 Estado final:\n";
    echo "   - user_type: " . ($maria->user_type->value ?? 'N/A') . "\n";
    echo "   - type_label: {$maria->type_label}\n";
    echo "   - promotion_status: " . ($maria->promotion_status->value ?? 'N/A') . "\n";
    echo "   - account_status: {$maria->account_status}\n\n";
    
    echo "🧪 Probando login API...\n";
    
    // Función simple para requests
    function testLogin() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/auth/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'dni' => '33333333',
            'password' => 'estudiante123'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true),
            'error' => $error
        ];
    }
    
    $result = testLogin();
    
    echo "Status: {$result['status']}\n";
    
    if ($result['status'] === 200) {
        echo "✅ Login API exitoso\n";
        
        $userData = $result['data']['data']['user'] ?? [];
        echo "📊 Datos de respuesta:\n";
        echo "   - user_type: " . ($userData['user_type'] ?? 'N/A') . "\n";
        echo "   - type_label: " . ($userData['type_label'] ?? 'N/A') . "\n";
        echo "   - promotion_status: " . ($userData['promotion_status'] ?? 'N/A') . "\n";
        
        if (isset($result['data']['data']['token'])) {
            echo "   - token: " . substr($result['data']['data']['token'], 0, 20) . "...\n";
        }
        
    } else {
        echo "❌ Login API falló\n";
        if (isset($result['data']['message'])) {
            echo "📝 Mensaje: {$result['data']['message']}\n";
        }
        if ($result['error']) {
            echo "📝 Error cURL: {$result['error']}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 RESUMEN:\n";
    echo str_repeat("=", 50) . "\n";
    
    echo "👤 Usuario: {$maria->name}\n";
    echo "🔧 Tipo: " . ($maria->user_type->value ?? 'N/A') . " ({$maria->type_label})\n";
    echo "📊 Estado: " . ($maria->promotion_status->value ?? 'N/A') . "\n";
    echo "🔐 Login API: " . ($result['status'] === 200 ? '✅ Funciona' : '❌ Falla') . "\n";
    
    if ($result['status'] === 200) {
        $userData = $result['data']['data']['user'] ?? [];
        $userType = $userData['user_type'] ?? 'N/A';
        
        if ($userType === 'api') {
            echo "\n🎉 ÉXITO: MARÍA GARCÍA ES AHORA USUARIO API\n";
            echo "✅ user_type: 'api'\n";
            echo "✅ type_label: 'Usuario API'\n";
            echo "✅ promotion_status: 'approved'\n";
            echo "✅ Lista para testing de gimnasios\n";
            
            echo "\n📱 CREDENCIALES PARA APP MÓVIL:\n";
            echo "   DNI: 33333333\n";
            echo "   Password: estudiante123\n";
            echo "   Tipo: Usuario API (NO local)\n";
            
        } else {
            echo "\n⚠️  ADVERTENCIA: Sigue apareciendo como tipo '{$userType}'\n";
        }
    } else {
        echo "\n❌ PROBLEMA: No se pudo completar la configuración\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
