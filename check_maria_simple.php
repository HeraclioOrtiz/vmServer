<?php

echo "🔍 === VERIFICACIÓN SIMPLE DE MARÍA GARCÍA === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    // Buscar usuario
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    if (!$maria) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$maria->name}\n\n";
    
    echo "📊 INFORMACIÓN BÁSICA:\n";
    echo "ID: {$maria->id}\n";
    echo "Nombre: {$maria->name}\n";
    echo "Email: {$maria->email}\n";
    echo "DNI: {$maria->dni}\n";
    echo "Estado: {$maria->account_status}\n";
    echo "Es profesor: " . ($maria->is_professor ? 'Sí' : 'No') . "\n";
    echo "Es admin: " . ($maria->is_admin ? 'Sí' : 'No') . "\n";
    echo "Email verificado: " . ($maria->email_verified_at ? 'Sí' : 'No') . "\n";
    
    echo "\n🧪 PROBANDO LOGIN:\n";
    
    // Probar login
    $credentials = ['dni' => '33333333', 'password' => 'estudiante123'];
    
    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        echo "✅ Login directo exitoso\n";
        
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Probar creación de token
        try {
            $token = $user->createToken('test-token');
            echo "✅ Token creado exitosamente\n";
            echo "Token: " . substr($token->plainTextToken, 0, 30) . "...\n";
        } catch (Exception $e) {
            echo "❌ Error creando token: " . $e->getMessage() . "\n";
        }
        
        \Illuminate\Support\Facades\Auth::logout();
    } else {
        echo "❌ Error en login directo\n";
    }
    
    echo "\n🔗 PROBANDO API:\n";
    
    // Función simple para requests
    function testApi($url, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['status' => $httpCode, 'data' => json_decode($response, true)];
    }
    
    // Probar login API
    $loginData = ['dni' => '33333333', 'password' => 'estudiante123'];
    $result = testApi('http://127.0.0.1:8000/api/auth/login', $loginData);
    
    echo "Login API Status: {$result['status']}\n";
    
    if ($result['status'] === 200 && isset($result['data']['data']['token'])) {
        echo "✅ Login API exitoso\n";
        $token = $result['data']['data']['token'];
        echo "Token obtenido: " . substr($token, 0, 30) . "...\n";
        
        // Probar endpoint protegido
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/student/my-templates');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Plantillas Status: {$httpCode}\n";
        
        if ($httpCode === 200) {
            echo "✅ Acceso a plantillas exitoso\n";
            $data = json_decode($response, true);
            $count = count($data['data']['templates'] ?? []);
            echo "Plantillas encontradas: {$count}\n";
        } else {
            echo "❌ Error accediendo a plantillas\n";
        }
        
    } else {
        echo "❌ Login API falló\n";
        if (isset($result['data']['message'])) {
            echo "Mensaje: {$result['data']['message']}\n";
        }
    }
    
    echo "\n🎯 RESUMEN:\n";
    echo "Usuario: {$maria->name}\n";
    echo "Estado: {$maria->account_status}\n";
    echo "Login directo: " . (\Illuminate\Support\Facades\Auth::attempt($credentials) ? '✅' : '❌') . "\n";
    echo "Login API: " . ($result['status'] === 200 ? '✅' : '❌') . "\n";
    echo "Tokens: " . (method_exists($maria, 'createToken') ? '✅' : '❌') . "\n";
    
    if ($result['status'] === 200) {
        echo "\n🎉 MARÍA GARCÍA ESTÁ LISTA PARA API\n";
    } else {
        echo "\n⚠️  NECESITA CONFIGURACIÓN ADICIONAL\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
