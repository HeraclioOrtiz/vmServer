<?php

echo "🔧 === CONFIGURACIÓN FINAL MARÍA GARCÍA API === 🔧\n\n";

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
    echo "📊 Estado actual:\n";
    echo "   - user_type: " . ($maria->user_type->value ?? $maria->user_type) . "\n";
    echo "   - promotion_status: " . ($maria->promotion_status->value ?? $maria->promotion_status) . "\n\n";
    
    echo "🔧 Actualizando a usuario API...\n";
    
    // Actualizar solo las columnas que existen
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $maria->id)
        ->update([
            'user_type' => 'api',
            'promotion_status' => 'approved',
            'account_status' => 'active',
            'email_verified_at' => now(),
            'password' => bcrypt('estudiante123'),
            'updated_at' => now()
        ]);
    
    echo "✅ Usuario actualizado exitosamente\n\n";
    
    // Recargar usuario
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    echo "📊 Estado final:\n";
    echo "   - user_type: " . ($maria->user_type->value ?? $maria->user_type) . "\n";
    echo "   - promotion_status: " . ($maria->promotion_status->value ?? $maria->promotion_status) . "\n";
    echo "   - account_status: {$maria->account_status}\n\n";
    
    echo "🧪 Probando login API...\n";
    
    // Función para probar login
    function testApiLogin() {
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
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    $result = testApiLogin();
    
    echo "Status: {$result['status']}\n";
    
    if ($result['status'] === 200) {
        echo "✅ Login API exitoso\n";
        
        $userData = $result['data']['data']['user'] ?? [];
        echo "\n📊 Datos de respuesta API:\n";
        echo "   - user_type: " . ($userData['user_type'] ?? 'N/A') . "\n";
        echo "   - promotion_status: " . ($userData['promotion_status'] ?? 'N/A') . "\n";
        echo "   - account_status: " . ($userData['account_status'] ?? 'N/A') . "\n";
        
        if (isset($userData['type_label'])) {
            echo "   - type_label: " . $userData['type_label'] . "\n";
        }
        
        if (isset($result['data']['data']['token'])) {
            echo "   - token: " . substr($result['data']['data']['token'], 0, 25) . "...\n";
        }
        
        // Verificar si ahora es usuario API
        $userType = $userData['user_type'] ?? 'N/A';
        
        if ($userType === 'api') {
            echo "\n🎉 ¡ÉXITO! MARÍA GARCÍA ES AHORA USUARIO API\n";
            echo "✅ user_type: 'api'\n";
            echo "✅ promotion_status: 'approved'\n";
            echo "✅ account_status: 'active'\n";
            echo "✅ Login funcionando correctamente\n";
            echo "✅ Token generado exitosamente\n";
            
            echo "\n📱 LISTO PARA TESTING DE GIMNASIOS:\n";
            echo "   🔐 DNI: 33333333\n";
            echo "   🔐 Password: estudiante123\n";
            echo "   🎯 Tipo: Usuario API (NO local)\n";
            echo "   🏋️ Acceso completo a endpoints de gimnasio\n";
            
        } else {
            echo "\n⚠️  ADVERTENCIA: Sigue apareciendo como '{$userType}'\n";
            echo "🔧 Puede necesitar reiniciar el servidor o limpiar cache\n";
        }
        
    } else {
        echo "❌ Login API falló\n";
        if (isset($result['data']['message'])) {
            echo "📝 Mensaje: {$result['data']['message']}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 RESUMEN FINAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "👤 Usuario: {$maria->name}\n";
    echo "🆔 DNI: {$maria->dni}\n";
    echo "🔧 Tipo BD: " . ($maria->user_type->value ?? $maria->user_type) . "\n";
    echo "📊 Estado BD: " . ($maria->promotion_status->value ?? $maria->promotion_status) . "\n";
    echo "🔐 Login API: " . ($result['status'] === 200 ? '✅ Funciona' : '❌ Falla') . "\n";
    
    if ($result['status'] === 200) {
        $userData = $result['data']['data']['user'] ?? [];
        $apiUserType = $userData['user_type'] ?? 'N/A';
        echo "🌐 Tipo API: {$apiUserType}\n";
        
        if ($apiUserType === 'api') {
            echo "\n🎊 MARÍA GARCÍA CONFIGURADA EXITOSAMENTE COMO USUARIO API\n";
            echo "🚀 Lista para desarrollo y testing de app móvil de gimnasios\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
