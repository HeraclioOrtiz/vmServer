<?php

echo "🔍 === VERIFICANDO ESTADO DEL SERVIDOR === 🔍\n\n";

function checkEndpoint($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'error' => $error,
        'response' => $response
    ];
}

// Verificar endpoint principal
echo "1. Verificando endpoint principal...\n";
$mainCheck = checkEndpoint('http://127.0.0.1:8000');
echo "   Status: {$mainCheck['status']}\n";
if ($mainCheck['error']) {
    echo "   Error: {$mainCheck['error']}\n";
} else {
    echo "   ✅ Servidor respondiendo\n";
}

echo "\n";

// Verificar endpoint de API
echo "2. Verificando endpoint de API...\n";
$apiCheck = checkEndpoint('http://127.0.0.1:8000/api');
echo "   Status: {$apiCheck['status']}\n";
if ($apiCheck['error']) {
    echo "   Error: {$apiCheck['error']}\n";
} else {
    echo "   ✅ API respondiendo\n";
}

echo "\n";

// Verificar endpoint de plantillas (sin auth)
echo "3. Verificando endpoint de plantillas...\n";
$templatesCheck = checkEndpoint('http://127.0.0.1:8000/api/admin/gym/daily-templates');
echo "   Status: {$templatesCheck['status']}\n";
if ($templatesCheck['error']) {
    echo "   Error: {$templatesCheck['error']}\n";
} elseif ($templatesCheck['status'] == 401) {
    echo "   ✅ Endpoint funciona (requiere autenticación)\n";
} elseif ($templatesCheck['status'] == 200) {
    echo "   ✅ Endpoint funciona y responde\n";
} else {
    echo "   ⚠️  Endpoint responde con status inesperado\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

if ($mainCheck['status'] > 0 && $apiCheck['status'] > 0) {
    echo "🎉 SERVIDOR FUNCIONANDO CORRECTAMENTE\n";
    echo "✅ Puerto 8000 activo y respondiendo\n";
    echo "✅ API endpoints accesibles\n";
    echo "✅ Listo para recibir requests del frontend\n";
} else {
    echo "❌ PROBLEMA CON EL SERVIDOR\n";
    echo "⚠️  Verificar que Laravel esté corriendo\n";
    echo "💡 Ejecutar: php artisan serve --host=127.0.0.1 --port=8000\n";
}

echo "\n📋 URL del servidor: http://127.0.0.1:8000\n";
