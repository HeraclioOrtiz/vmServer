<?php

echo "🔍 === COMPARACIÓN LOCALHOST vs TUNNEL === 🔍\n\n";

$localUrl = 'http://localhost:8000';
$tunnelUrl = 'https://villamitre.loca.lt';

$loginData = json_encode([
    'dni' => '55555555',
    'password' => 'maria123'
]);

// Test localhost
echo "TEST 1: LOCALHOST\n";
echo str_repeat("-", 30) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $localUrl . '/api/test/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error1 = curl_error($ch);
curl_close($ch);

if ($error1) {
    echo "❌ Error localhost: {$error1}\n";
} else {
    echo "📊 HTTP Status: {$httpCode1}\n";
    if ($httpCode1 == 200) {
        echo "✅ Localhost funcionando\n";
        $data1 = json_decode($response1, true);
        if (isset($data1['user'])) {
            echo "👤 Usuario: {$data1['user']['name']}\n";
        }
    } else {
        echo "❌ Localhost error: " . substr($response1, 0, 100) . "...\n";
    }
}

echo "\n";

// Test tunnel
echo "TEST 2: TUNNEL\n";
echo str_repeat("-", 30) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tunnelUrl . '/api/test/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch);
curl_close($ch);

if ($error2) {
    echo "❌ Error tunnel: {$error2}\n";
} else {
    echo "📊 HTTP Status: {$httpCode2}\n";
    if ($httpCode2 == 200) {
        echo "✅ Tunnel funcionando\n";
        $data2 = json_decode($response2, true);
        if (isset($data2['user'])) {
            echo "👤 Usuario: {$data2['user']['name']}\n";
        }
    } else {
        echo "❌ Tunnel error: " . substr($response2, 0, 200) . "...\n";
    }
}

echo "\n" . str_repeat("=", 40) . "\n";
echo "DIAGNÓSTICO\n";
echo str_repeat("=", 40) . "\n";

if ($httpCode1 == 200 && $httpCode2 != 200) {
    echo "🔍 PROBLEMA: Localhost funciona pero tunnel no\n";
    echo "💡 POSIBLES CAUSAS:\n";
    echo "• Configuración CORS\n";
    echo "• Headers del tunnel\n";
    echo "• Timeout del tunnel\n";
    echo "• Configuración de Laravel\n";
} elseif ($httpCode1 == 200 && $httpCode2 == 200) {
    echo "✅ AMBOS FUNCIONAN CORRECTAMENTE\n";
} else {
    echo "❌ PROBLEMA EN AMBOS\n";
}

echo "\n🎯 URLs FINALES:\n";
echo "• Local: {$localUrl}/api\n";
echo "• Tunnel: {$tunnelUrl}/api\n";
