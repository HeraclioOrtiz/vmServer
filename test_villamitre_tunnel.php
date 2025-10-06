<?php

echo "🌐 === TESTING VILLAMITRE LOCALTUNNEL === 🌐\n\n";

$baseUrl = 'https://villamitre.loca.lt';

echo "🎯 Base URL: {$baseUrl}\n";
echo "📡 Testing conectividad...\n\n";

// Test 1: Conectividad básica
echo "TEST 1: Conectividad básica\n";
echo str_repeat("-", 40) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Error de conexión: {$error}\n";
} else {
    echo "✅ HTTP Status: {$httpCode}\n";
    if ($httpCode == 200) {
        echo "✅ Túnel funcionando correctamente\n";
    } else {
        echo "⚠️ Túnel responde pero con status {$httpCode}\n";
    }
}

echo "\n";

// Test 2: Login API
echo "TEST 2: Login API\n";
echo str_repeat("-", 40) . "\n";

$loginUrl = $baseUrl . '/api/login';
$loginData = json_encode([
    'email' => 'maria.garcia@villamitre.com',
    'password' => 'maria123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Error en login: {$error}\n";
} else {
    echo "📊 HTTP Status: {$httpCode}\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['token'])) {
            echo "✅ Login exitoso!\n";
            echo "👤 Usuario: {$data['user']['name']}\n";
            echo "🔑 Token obtenido: " . substr($data['token'], 0, 20) . "...\n";
            
            // Test 3: Endpoint con token
            echo "\nTEST 3: Endpoint con autenticación\n";
            echo str_repeat("-", 40) . "\n";
            
            $templatesUrl = $baseUrl . '/api/student/my-templates';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $templatesUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $data['token'],
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response3 = curl_exec($ch);
            $httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error3 = curl_error($ch);
            curl_close($ch);
            
            if ($error3) {
                echo "❌ Error en templates: {$error3}\n";
            } else {
                echo "📊 HTTP Status: {$httpCode3}\n";
                
                if ($httpCode3 == 200) {
                    $data3 = json_decode($response3, true);
                    echo "✅ Templates obtenidos exitosamente!\n";
                    echo "👨‍🏫 Profesor: {$data3['data']['professor']['name']}\n";
                    echo "📋 Templates: " . count($data3['data']['templates']) . "\n";
                } else {
                    echo "❌ Error obteniendo templates\n";
                    echo "Response: " . substr($response3, 0, 200) . "...\n";
                }
            }
            
        } else {
            echo "❌ Login falló - respuesta inválida\n";
            echo "Response: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "❌ Login falló con status {$httpCode}\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 RESUMEN FINAL\n";
echo str_repeat("=", 50) . "\n";

echo "🌐 URL Base: {$baseUrl}\n";
echo "📱 Para app móvil: {$baseUrl}/api\n";
echo "👤 Usuario prueba: maria.garcia@villamitre.com\n";
echo "🔑 Password: maria123\n";

echo "\n✅ ENDPOINTS PRINCIPALES:\n";
echo "• POST {$baseUrl}/api/login\n";
echo "• GET {$baseUrl}/api/student/my-templates\n";
echo "• GET {$baseUrl}/api/student/template/{id}/details\n";
echo "• GET {$baseUrl}/api/student/my-weekly-calendar\n";
echo "• POST {$baseUrl}/api/student/progress/{id}/complete\n";

echo "\n🚀 LISTO PARA APP MÓVIL!\n";
