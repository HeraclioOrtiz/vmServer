<?php

echo "🐛 === TESTING BUG FIX - API AUTH ENDPOINT === 🐛\n\n";

$baseUrl = 'https://villamitre.loca.lt';

echo "🎯 Testing URL: {$baseUrl}/api/auth/login\n";
echo "📋 Objetivo: Verificar que devuelve JSON en lugar de HTML\n\n";

echo str_repeat("=", 70) . "\n";
echo "TEST 1: CREDENCIALES INCORRECTAS (DEBE DEVOLVER JSON 422)\n";
echo str_repeat("=", 70) . "\n";

$invalidCredentials = [
    'dni' => '55555555',
    'password' => 'estudiante123' // Password incorrecta
];

echo "📤 Request:\n";
echo "POST {$baseUrl}/api/auth/login\n";
echo "Content-Type: application/json\n";
echo "Body: " . json_encode($invalidCredentials, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidCredentials));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true); // Incluir headers en respuesta

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Error de conexión: {$error}\n";
    exit(1);
}

// Separar headers y body
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "📊 RESPUESTA RECIBIDA:\n";
echo "Status Code: {$httpCode}\n";
echo "Content-Type: {$contentType}\n\n";

echo "📋 Headers:\n";
echo $headers . "\n";

echo "📄 Body:\n";
echo $body . "\n\n";

// Verificar si es JSON válido
$jsonData = json_decode($body, true);
$isValidJson = json_last_error() === JSON_ERROR_NONE;

echo "✅ VERIFICACIONES:\n";
echo ($httpCode === 422 ? "✅" : "❌") . " Status Code: {$httpCode} (esperado: 422)\n";
echo (strpos($contentType, 'application/json') !== false ? "✅" : "❌") . " Content-Type: {$contentType} (esperado: application/json)\n";
echo ($isValidJson ? "✅" : "❌") . " JSON válido: " . ($isValidJson ? "Sí" : "No") . "\n";

if ($isValidJson) {
    echo ($jsonData['success'] === false ? "✅" : "❌") . " Campo 'success': " . json_encode($jsonData['success']) . " (esperado: false)\n";
    echo (isset($jsonData['message']) ? "✅" : "❌") . " Campo 'message': " . (isset($jsonData['message']) ? "Presente" : "Ausente") . "\n";
    echo (isset($jsonData['errors']) ? "✅" : "❌") . " Campo 'errors': " . (isset($jsonData['errors']) ? "Presente" : "Ausente") . "\n";
}

if (!$isValidJson) {
    echo "\n❌ PROBLEMA: Respuesta no es JSON válido\n";
    echo "Error JSON: " . json_last_error_msg() . "\n";
    echo "Primeros 200 caracteres de respuesta:\n";
    echo substr($body, 0, 200) . "...\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "TEST 2: CREDENCIALES VÁLIDAS (DEBE DEVOLVER JSON 200)\n";
echo str_repeat("=", 70) . "\n";

$validCredentials = [
    'dni' => '55555555',
    'password' => 'maria123' // Password correcta
];

echo "📤 Request:\n";
echo "POST {$baseUrl}/api/auth/login\n";
echo "Content-Type: application/json\n";
echo "Body: " . json_encode($validCredentials, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($validCredentials));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType2 = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error2 = curl_error($ch);
curl_close($ch);

if ($error2) {
    echo "❌ Error de conexión: {$error2}\n";
} else {
    echo "📊 RESPUESTA RECIBIDA:\n";
    echo "Status Code: {$httpCode2}\n";
    echo "Content-Type: {$contentType2}\n\n";
    
    echo "📄 Body:\n";
    echo $response2 . "\n\n";
    
    $jsonData2 = json_decode($response2, true);
    $isValidJson2 = json_last_error() === JSON_ERROR_NONE;
    
    echo "✅ VERIFICACIONES:\n";
    echo ($httpCode2 === 200 ? "✅" : "❌") . " Status Code: {$httpCode2} (esperado: 200)\n";
    echo (strpos($contentType2, 'application/json') !== false ? "✅" : "❌") . " Content-Type: {$contentType2} (esperado: application/json)\n";
    echo ($isValidJson2 ? "✅" : "❌") . " JSON válido: " . ($isValidJson2 ? "Sí" : "No") . "\n";
    
    if ($isValidJson2) {
        echo (isset($jsonData2['data']['token']) ? "✅" : "❌") . " Token presente: " . (isset($jsonData2['data']['token']) ? "Sí" : "No") . "\n";
        echo (isset($jsonData2['data']['user']) ? "✅" : "❌") . " Usuario presente: " . (isset($jsonData2['data']['user']) ? "Sí" : "No") . "\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "TEST 3: DNI FALTANTE (DEBE DEVOLVER JSON 422)\n";
echo str_repeat("=", 70) . "\n";

$missingDni = [
    'password' => 'maria123'
    // DNI faltante
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($missingDni));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response3 = curl_exec($ch);
$httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType3 = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "📊 Status Code: {$httpCode3} (esperado: 422)\n";
echo "📋 Content-Type: {$contentType3}\n";

$jsonData3 = json_decode($response3, true);
$isValidJson3 = json_last_error() === JSON_ERROR_NONE;

echo "✅ JSON válido: " . ($isValidJson3 ? "Sí" : "No") . "\n";

if ($isValidJson3 && isset($jsonData3['errors']['dni'])) {
    echo "✅ Error de validación DNI presente\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 70) . "\n";

$test1Pass = ($httpCode === 422 && $isValidJson && strpos($contentType, 'application/json') !== false);
$test2Pass = ($httpCode2 === 200 && $isValidJson2 && strpos($contentType2, 'application/json') !== false);
$test3Pass = ($httpCode3 === 422 && $isValidJson3 && strpos($contentType3, 'application/json') !== false);

echo "\n📊 RESULTADOS:\n";
echo ($test1Pass ? "✅" : "❌") . " Test 1 - Credenciales incorrectas: " . ($test1Pass ? "PASÓ" : "FALLÓ") . "\n";
echo ($test2Pass ? "✅" : "❌") . " Test 2 - Credenciales válidas: " . ($test2Pass ? "PASÓ" : "FALLÓ") . "\n";
echo ($test3Pass ? "✅" : "❌") . " Test 3 - DNI faltante: " . ($test3Pass ? "PASÓ" : "FALLÓ") . "\n";

$allPass = $test1Pass && $test2Pass && $test3Pass;

echo "\n🎯 ESTADO DEL BUG:\n";
if ($allPass) {
    echo "✅ BUG SOLUCIONADO - Todos los tests pasaron\n";
    echo "✅ El endpoint ahora devuelve JSON correctamente\n";
    echo "✅ Los status codes son correctos\n";
    echo "✅ El Content-Type es application/json\n";
} else {
    echo "❌ BUG AÚN PRESENTE - Algunos tests fallaron\n";
    if (!$test1Pass) echo "❌ Credenciales incorrectas aún devuelve HTML o status incorrecto\n";
    if (!$test2Pass) echo "❌ Login válido tiene problemas\n";
    if (!$test3Pass) echo "❌ Validación de campos tiene problemas\n";
}

echo "\n🚀 LISTO PARA APP MÓVIL: " . ($allPass ? "SÍ" : "NO") . "\n";
