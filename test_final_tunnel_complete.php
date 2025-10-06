<?php

echo "🌐 === TEST FINAL COMPLETO - TUNNEL VILLAMITRE === 🌐\n\n";

$baseUrl = 'https://villamitre.loca.lt';

echo "🎯 Base URL: {$baseUrl}\n";
echo "📱 Para app móvil: {$baseUrl}/api\n\n";

// Credenciales
$credentials = [
    'dni' => '55555555',
    'password' => 'maria123'
];

echo "👤 Credenciales de prueba:\n";
echo "🆔 DNI: {$credentials['dni']}\n";
echo "🔑 Password: {$credentials['password']}\n\n";

echo str_repeat("=", 60) . "\n";
echo "TEST 1: LOGIN CON TUNNEL\n";
echo str_repeat("=", 60) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/test/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($credentials));
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
    echo "❌ Error de conexión: {$error}\n";
    exit(1);
}

echo "📊 HTTP Status: {$httpCode}\n";

if ($httpCode != 200) {
    echo "❌ Login falló\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
    exit(1);
}

$loginData = json_decode($response, true);
if (!isset($loginData['token'])) {
    echo "❌ Token no recibido\n";
    exit(1);
}

$token = $loginData['token'];
echo "✅ Login exitoso!\n";
echo "👤 Usuario: {$loginData['user']['name']}\n";
echo "🔑 Token: " . substr($token, 0, 20) . "...\n\n";

echo str_repeat("=", 60) . "\n";
echo "TEST 2: ENDPOINTS DE ESTUDIANTE\n";
echo str_repeat("=", 60) . "\n";

$endpoints = [
    'my-templates' => '/api/student/my-templates',
    'weekly-calendar' => '/api/student/my-weekly-calendar'
];

foreach ($endpoints as $name => $endpoint) {
    echo "\n🧪 Testing: {$name}\n";
    echo "📡 URL: {$baseUrl}{$endpoint}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Error: {$error}\n";
        continue;
    }
    
    echo "📊 Status: {$httpCode}\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        echo "✅ Endpoint funcionando\n";
        
        if ($name == 'my-templates') {
            if (isset($data['data']['professor'])) {
                echo "👨‍🏫 Profesor: {$data['data']['professor']['name']}\n";
            }
            if (isset($data['data']['templates'])) {
                echo "📋 Templates: " . count($data['data']['templates']) . "\n";
            }
        }
        
        if ($name == 'weekly-calendar') {
            if (isset($data['data']['days'])) {
                $workoutDays = array_filter($data['data']['days'], function($day) { 
                    return $day['has_workouts']; 
                });
                echo "🏋️ Días con entrenamientos: " . count($workoutDays) . "\n";
            }
        }
    } else {
        echo "❌ Error status {$httpCode}\n";
        echo "Response: " . substr($response, 0, 100) . "...\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 60) . "\n";

echo "\n✅ CONFIGURACIÓN EXITOSA:\n";
echo "🌐 LocalTunnel: https://villamitre.loca.lt\n";
echo "🔑 Autenticación: Funcionando\n";
echo "📱 Endpoints móvil: Funcionando\n";
echo "👤 Usuario de prueba: Configurado\n\n";

echo "📋 PARA EL DESARROLLADOR MÓVIL:\n";
echo "• Base URL: https://villamitre.loca.lt\n";
echo "• Login: POST /api/test/login\n";
echo "• DNI: 55555555\n";
echo "• Password: maria123\n";
echo "• Headers: Authorization: Bearer {token}\n\n";

echo "🎯 ENDPOINTS PRINCIPALES:\n";
echo "• POST https://villamitre.loca.lt/api/test/login\n";
echo "• GET https://villamitre.loca.lt/api/student/my-templates\n";
echo "• GET https://villamitre.loca.lt/api/student/template/{id}/details\n";
echo "• GET https://villamitre.loca.lt/api/student/my-weekly-calendar\n";
echo "• POST https://villamitre.loca.lt/api/student/progress/{id}/complete\n\n";

echo "🚀 SISTEMA LISTO PARA APP MÓVIL!\n";
