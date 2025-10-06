<?php

echo "🌐 === TESTING SERVIDOR ONLINE === 🌐\n\n";

try {
    echo "🔍 Probando conexión a servidor online...\n";
    
    // Probar endpoint básico
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 Status: {$httpCode}\n";
    
    if ($httpCode === 200) {
        echo "✅ Servidor online funcionando correctamente\n\n";
        
        echo "🔐 Probando login de María García...\n";
        
        // Probar login
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://villamitre.loca.lt/api/auth/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'dni' => '33333333',
            'password' => 'estudiante123'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $loginResponse = curl_exec($ch);
        $loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "📊 Login Status: {$loginCode}\n";
        
        if ($loginCode === 200) {
            $loginData = json_decode($loginResponse, true);
            echo "✅ Login exitoso online\n";
            echo "👤 Usuario: " . ($loginData['data']['user']['name'] ?? 'N/A') . "\n";
            echo "🔑 Token generado: ✅\n";
            
            echo "\n🎯 URLS PARA PRESENTACIÓN:\n";
            echo "🌐 Servidor: https://villamitre.loca.lt\n";
            echo "🖥️  Panel Admin: https://villamitre.loca.lt/admin\n";
            echo "📱 API Base: https://villamitre.loca.lt/api\n";
            echo "🔐 Login API: https://villamitre.loca.lt/api/auth/login\n";
            
            echo "\n👤 CREDENCIALES MARÍA GARCÍA:\n";
            echo "📧 DNI: 33333333\n";
            echo "🔒 Password: estudiante123\n";
            echo "🎯 Tipo: Usuario API\n";
            
        } else {
            echo "❌ Error en login: {$loginCode}\n";
            echo "📝 Respuesta: {$loginResponse}\n";
        }
        
    } else {
        echo "❌ Error conectando al servidor: {$httpCode}\n";
        echo "📝 Respuesta: {$response}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 ESTADO FINAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    if ($httpCode === 200 && isset($loginCode) && $loginCode === 200) {
        echo "🎉 SERVIDOR ONLINE LISTO PARA PRESENTACIÓN\n";
        echo "✅ LocalTunnel funcionando\n";
        echo "✅ Laravel respondiendo\n";
        echo "✅ API de autenticación operativa\n";
        echo "✅ María García puede hacer login\n";
        
        echo "\n📱 PARA APP MÓVIL:\n";
        echo "   Cambiar URL base a: https://villamitre.loca.lt\n";
        
        echo "\n🖥️  PARA PANEL ADMIN:\n";
        echo "   Acceder desde: https://villamitre.loca.lt/admin\n";
        
        echo "\n⚠️  IMPORTANTE:\n";
        echo "   - Mantener ambas terminales abiertas\n";
        echo "   - Si se desconecta, ejecutar: lt --port 8000 --subdomain villamitre\n";
        
    } else {
        echo "❌ PROBLEMAS DETECTADOS\n";
        echo "🔧 Revisar configuración\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
