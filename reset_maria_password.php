<?php

echo "🔑 === RESETEAR PASSWORD DE MARÍA === 🔑\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();

if ($maria) {
    echo "👤 Usuario encontrado: {$maria->name}\n";
    echo "🆔 DNI: {$maria->dni}\n";
    
    // Resetear password a 'maria123'
    $maria->password = \Illuminate\Support\Facades\Hash::make('maria123');
    $maria->save();
    
    echo "✅ Password reseteado a: maria123\n\n";
    
    // Test login
    echo "🧪 Testing login...\n";
    
    $loginData = [
        'dni' => $maria->dni,
        'password' => 'maria123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/test/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 HTTP Status: {$httpCode}\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        echo "✅ Login exitoso!\n";
        echo "🔑 Token: " . substr($data['token'], 0, 20) . "...\n";
    } else {
        echo "❌ Login falló\n";
        echo "Response: {$response}\n";
    }
    
} else {
    echo "❌ Usuario no encontrado\n";
}
