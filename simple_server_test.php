<?php

echo "🧪 === TEST RÁPIDO DE CONECTIVIDAD === 🧪\n\n";

// Test servidor local
echo "1️⃣ SERVIDOR LOCAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode >= 200 && $httpCode < 400) {
    echo "✅ Servidor local: FUNCIONANDO (HTTP {$httpCode})\n";
} else {
    echo "❌ Servidor local: ERROR (HTTP {$httpCode})\n";
}

// Test LocalTunnel
echo "\n2️⃣ LOCALTUNNEL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode >= 200 && $httpCode < 400) {
    echo "✅ LocalTunnel: FUNCIONANDO (HTTP {$httpCode})\n";
} else {
    echo "❌ LocalTunnel: ERROR (HTTP {$httpCode})\n";
}

// Test login directo
echo "\n3️⃣ TEST LOGIN:\n";
$loginData = json_encode(['dni' => '55555555', 'password' => 'maria123']);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode == 200) {
    echo "✅ Login API: FUNCIONANDO\n";
} else {
    echo "❌ Login API: ERROR (HTTP {$httpCode})\n";
}

echo "\n🎯 ESTADO FINAL:\n";
echo "🌐 URL: https://villamitre.loca.lt\n";
echo "👤 Estudiante: DNI 55555555 / maria123\n";
echo "👨‍🏫 Profesor: DNI 22222222 / profesor123\n";
