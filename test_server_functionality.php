<?php

echo "🧪 === TESTING COMPLETO DEL SERVIDOR === 🧪\n\n";

// Test 1: Servidor local
echo "1️⃣ TESTING SERVIDOR LOCAL (http://localhost:8000):\n";
echo str_repeat("=", 60) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode == 200) {
    echo "✅ Servidor local: FUNCIONANDO\n";
    echo "📊 Respuesta: " . substr($response, 0, 100) . "...\n";
} else {
    echo "❌ Servidor local: ERROR (HTTP {$httpCode})\n";
}

echo "\n";

// Test 2: LocalTunnel
echo "2️⃣ TESTING LOCALTUNNEL (https://villamitre.loca.lt):\n";
echo str_repeat("=", 60) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode == 200) {
    echo "✅ LocalTunnel: FUNCIONANDO\n";
    echo "📊 Respuesta: " . substr($response, 0, 100) . "...\n";
} else {
    echo "❌ LocalTunnel: ERROR (HTTP {$httpCode})\n";
    if ($response === false) {
        echo "🔍 Posible causa: Túnel desconectado o bloqueado\n";
    }
}

echo "\n";

// Test 3: Login de estudiante
echo "3️⃣ TESTING LOGIN ESTUDIANTE (María García):\n";
echo str_repeat("=", 60) . "\n";

$loginData = json_encode([
    'dni' => '55555555',
    'password' => 'maria123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode == 200) {
    $loginResult = json_decode($response, true);
    if (isset($loginResult['data']['token'])) {
        echo "✅ Login estudiante: EXITOSO\n";
        echo "👤 Usuario: {$loginResult['data']['user']['name']}\n";
        $token = $loginResult['data']['token'];
    } else {
        echo "❌ Login estudiante: ERROR - Sin token\n";
        $token = null;
    }
} else {
    echo "❌ Login estudiante: ERROR (HTTP {$httpCode})\n";
    $token = null;
}

echo "\n";

// Test 4: Login de profesor
echo "4️⃣ TESTING LOGIN PROFESOR (Juan Pérez):\n";
echo str_repeat("=", 60) . "\n";

$professorLoginData = json_encode([
    'dni' => '22222222',
    'password' => 'profesor123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $professorLoginData);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode == 200) {
    $professorLoginResult = json_decode($response, true);
    if (isset($professorLoginResult['data']['token'])) {
        echo "✅ Login profesor: EXITOSO\n";
        echo "👨‍🏫 Usuario: {$professorLoginResult['data']['user']['name']}\n";
        $professorToken = $professorLoginResult['data']['token'];
    } else {
        echo "❌ Login profesor: ERROR - Sin token\n";
        $professorToken = null;
    }
} else {
    echo "❌ Login profesor: ERROR (HTTP {$httpCode})\n";
    $professorToken = null;
}

echo "\n";

// Test 5: API de plantillas (si el login funcionó)
if ($token) {
    echo "5️⃣ TESTING API PLANTILLAS ESTUDIANTE:\n";
    echo str_repeat("=", 60) . "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/student/my-templates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response !== false && $httpCode == 200) {
        $templatesData = json_decode($response, true);
        $templateCount = isset($templatesData['data']['templates']) ? count($templatesData['data']['templates']) : 0;
        echo "✅ API plantillas: FUNCIONANDO\n";
        echo "📋 Plantillas encontradas: {$templateCount}\n";
    } else {
        echo "❌ API plantillas: ERROR (HTTP {$httpCode})\n";
    }
} else {
    echo "5️⃣ TESTING API PLANTILLAS: OMITIDO (sin token)\n";
}

echo "\n";

// Resumen final
echo "📊 RESUMEN FINAL:\n";
echo str_repeat("=", 60) . "\n";
echo "🌐 URL Pública: https://villamitre.loca.lt\n";
echo "🏠 URL Local: http://localhost:8000\n";
echo "👤 Estudiante: DNI 55555555, Password maria123\n";
echo "👨‍🏫 Profesor: DNI 22222222, Password profesor123\n";
echo "\n🎯 Estado del sistema: " . ($token && $professorToken ? "✅ COMPLETAMENTE FUNCIONAL" : "⚠️ REVISAR CONEXIONES") . "\n";
