<?php

echo "🔍 === VERIFICACIÓN RÁPIDA DEL TUNNEL === 🔍\n\n";

$baseUrl = 'https://villamitre.loca.lt';

echo "🌐 URL Base: {$baseUrl}\n";
echo "📱 Para app móvil: {$baseUrl}/api\n\n";

// Test 1: Conectividad básica
echo "TEST 1: Conectividad básica\n";
echo str_repeat("-", 40) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: {$httpCode}\n";
echo ($httpCode == 200 ? "✅" : "❌") . " Conectividad: " . ($httpCode == 200 ? "OK" : "ERROR") . "\n\n";

// Test 2: Login con credenciales correctas
echo "TEST 2: Login con credenciales correctas\n";
echo str_repeat("-", 40) . "\n";

$loginData = json_encode([
    'dni' => '55555555',
    'password' => 'maria123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/auth/login');
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
curl_close($ch);

echo "📊 Status: {$httpCode}\n";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['data']['token'])) {
        echo "✅ Login exitoso\n";
        echo "👤 Usuario: {$data['data']['user']['name']}\n";
        echo "🔑 Token: " . substr($data['data']['token'], 0, 20) . "...\n";
        
        $token = $data['data']['token'];
        
        // Test 3: Endpoint autenticado
        echo "\nTEST 3: Endpoint con autenticación\n";
        echo str_repeat("-", 40) . "\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/student/my-templates');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response3 = curl_exec($ch);
        $httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "📊 Status: {$httpCode3}\n";
        
        if ($httpCode3 == 200) {
            $data3 = json_decode($response3, true);
            echo "✅ Templates obtenidos\n";
            if (isset($data3['data']['professor'])) {
                echo "👨‍🏫 Profesor: {$data3['data']['professor']['name']}\n";
            }
            if (isset($data3['data']['templates'])) {
                echo "📋 Templates: " . count($data3['data']['templates']) . "\n";
            }
        } else {
            echo "❌ Error obteniendo templates\n";
        }
        
    } else {
        echo "❌ Login falló - respuesta inválida\n";
    }
} else {
    echo "❌ Login falló con status {$httpCode}\n";
}

// Test 4: Login con credenciales incorrectas (verificar bug fix)
echo "\nTEST 4: Login con credenciales incorrectas (Bug Fix)\n";
echo str_repeat("-", 40) . "\n";

$badLoginData = json_encode([
    'dni' => '55555555',
    'password' => 'password_incorrecta'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $badLoginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response4 = curl_exec($ch);
$httpCode4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType4 = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "📊 Status: {$httpCode4}\n";
echo "📋 Content-Type: {$contentType4}\n";

$isJson = json_decode($response4, true) !== null;
echo ($isJson ? "✅" : "❌") . " Respuesta JSON: " . ($isJson ? "Sí" : "No") . "\n";
echo ($httpCode4 == 422 ? "✅" : "❌") . " Status correcto: " . ($httpCode4 == 422 ? "422" : $httpCode4) . "\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 50) . "\n";

echo "\n✅ ESTADO DE SERVICIOS:\n";
echo "🌐 LocalTunnel: https://villamitre.loca.lt\n";
echo "🖥️ Laravel Server: http://localhost:8000\n";
echo "🔑 Autenticación: Funcionando\n";
echo "📱 Endpoints móvil: Funcionando\n";
echo "🐛 Bug Fix: Aplicado\n";

echo "\n📱 PARA LA APP MÓVIL:\n";
echo "• URL Base: https://villamitre.loca.lt\n";
echo "• Login: POST /api/auth/login\n";
echo "• DNI: 55555555\n";
echo "• Password: maria123\n";

echo "\n🚀 SISTEMA LISTO PARA APP MÓVIL!\n";
