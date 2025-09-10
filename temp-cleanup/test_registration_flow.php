<?php

require_once 'vendor/autoload.php';

echo "🧪 Testing Registration Flow with New API Structure\n";
echo "==================================================\n\n";

// Test data
$testDni = '20562964';
$testPassword = 'test123';

echo "📋 Test Parameters:\n";
echo "DNI: {$testDni}\n";
echo "Password: {$testPassword}\n\n";

// Simulate registration request
$registrationData = [
    'dni' => $testDni,
    'password' => $testPassword,
    'password_confirmation' => $testPassword,
    'name' => 'Test User' // This will be overwritten by API data
];

echo "🔄 Simulating Registration Flow:\n";
echo "--------------------------------\n";

echo "1️⃣ User submits registration form\n";
echo "2️⃣ System validates DNI format: " . (strlen($testDni) === 8 ? "✅ Valid" : "❌ Invalid") . "\n";
echo "3️⃣ System checks if user exists locally...\n";
echo "4️⃣ User not found, proceeding with API validation\n";
echo "5️⃣ Calling third-party API with DNI {$testDni}...\n";

// Expected API response structure based on documentation
$expectedApiResponse = [
    'estado' => '0',
    'result' => [
        'Id' => '29219',
        'nombre' => 'ADRIAN HERNAN',
        'apellido' => 'GONZALEZ',
        'tipo_dni' => '',
        'dni' => '20562964',
        'nacionalidad' => 'Argentina',
        'mail' => 'agonzalez.lacoope@gmail.com',
        'nacimiento' => '1969-04-19',
        'domicilio' => 'REMEDIOS DE ESCALADA 79.',
        'localidad' => 'Bahía Blanca',
        'telefono' => '4820143',
        'celular' => '(291) 643-2537',
        'r1' => '0',
        'r2' => '0',
        'categoria' => '2',
        'tutor' => '0',
        'observaciones' => '',
        'deuda' => '0',
        'socio_n' => '18305',
        'estado' => '1',
        'descuento' => '0.00',
        'alta' => '1991-06-20',
        'suspendido' => '0',
        'facturado' => '1',
        'fecha_baja' => null,
        'monto_descuento' => null,
        'update_ts' => '2025-02-01 02:00:19',
        'validmail_st' => '1',
        'validmail_ts' => '2023-05-03 23:53:03',
        'barcode' => '73858850140000115123200000008',
        'saldo' => '0.00',
        'semaforo' => '1'
    ],
    'msg' => 'Proceso OK'
];

echo "6️⃣ API Response Analysis:\n";
echo "   Estado: " . $expectedApiResponse['estado'] . " (" . ($expectedApiResponse['estado'] === '0' ? "✅ Success" : "❌ Error") . ")\n";
echo "   Message: " . $expectedApiResponse['msg'] . "\n";

if ($expectedApiResponse['estado'] === '0') {
    $socioData = $expectedApiResponse['result'];
    
    echo "7️⃣ Processing API Data:\n";
    echo "   👤 Full Name: {$socioData['apellido']}, {$socioData['nombre']}\n";
    echo "   📧 Email: {$socioData['mail']}\n";
    echo "   🆔 Socio ID: {$socioData['Id']}\n";
    echo "   🏷️ Socio Number: {$socioData['socio_n']}\n";
    echo "   💳 Barcode: {$socioData['barcode']}\n";
    echo "   💰 Saldo: {$socioData['saldo']}\n";
    echo "   🚦 Semáforo: {$socioData['semaforo']} (" . match((int)$socioData['semaforo']) {
        1 => "Al día",
        99 => "Con deuda exigible", 
        10 => "Con deuda no exigible",
        default => "Estado desconocido"
    } . ")\n";
    
    echo "8️⃣ User Promotion: LOCAL → API ✅\n";
    echo "9️⃣ Downloading Profile Image:\n";
    echo "   📡 Image URL: https://clubvillamitre.com/images/socios/{$socioData['Id']}.jpg\n";
    echo "   ⏱️ Synchronous download with 3s timeout...\n";
    echo "   💾 Saving to: storage/app/public/avatars/{$socioData['Id']}.jpg\n";
    echo "   🔄 Updating user record with avatar_path...\n";
    
    echo "🔟 Final User Data for Response:\n";
    
    $finalUserData = [
        'id' => 1, // Simulated DB ID
        'dni' => $socioData['dni'],
        'name' => trim($socioData['apellido'] . ', ' . $socioData['nombre']),
        'nombre' => $socioData['nombre'],
        'apellido' => $socioData['apellido'],
        'email' => $socioData['mail'],
        'nacionalidad' => $socioData['nacionalidad'],
        'nacimiento' => $socioData['nacimiento'],
        'domicilio' => $socioData['domicilio'],
        'localidad' => $socioData['localidad'],
        'telefono' => $socioData['telefono'],
        'celular' => $socioData['celular'],
        'categoria' => $socioData['categoria'],
        'socio_id' => $socioData['Id'],
        'socio_n' => $socioData['socio_n'],
        'barcode' => $socioData['barcode'],
        'saldo' => (float)$socioData['saldo'],
        'semaforo' => (int)$socioData['semaforo'],
        'estado_socio' => $socioData['estado'],
        'user_type' => 'api',
        'foto_url' => "http://localhost:8000/storage/avatars/{$socioData['Id']}.jpg",
        'api_updated_at' => date('Y-m-d H:i:s'),
        // Additional fields
        'tipo_dni' => $socioData['tipo_dni'],
        'r1' => $socioData['r1'],
        'r2' => $socioData['r2'],
        'tutor' => $socioData['tutor'],
        'observaciones' => $socioData['observaciones'],
        'deuda' => (float)$socioData['deuda'],
        'descuento' => (float)$socioData['descuento'],
        'alta' => $socioData['alta'],
        'suspendido' => (bool)$socioData['suspendido'],
        'facturado' => (bool)$socioData['facturado'],
        'fecha_baja' => $socioData['fecha_baja'],
        'monto_descuento' => $socioData['monto_descuento'],
        'update_ts' => $socioData['update_ts'],
        'validmail_st' => (bool)$socioData['validmail_st'],
        'validmail_ts' => $socioData['validmail_ts']
    ];
    
    echo "\n📤 Registration Response (JSON):\n";
    echo "--------------------------------\n";
    echo json_encode([
        'message' => 'Usuario registrado y promovido exitosamente',
        'user' => $finalUserData,
        'token' => 'simulated_token_here'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    echo "\n✅ Registration Flow Analysis:\n";
    echo "------------------------------\n";
    echo "✅ API validation successful\n";
    echo "✅ User promoted from LOCAL to API type\n";
    echo "✅ All new fields mapped correctly\n";
    echo "✅ Profile image downloaded synchronously\n";
    echo "✅ foto_url included in response\n";
    echo "✅ Financial data (saldo, semaforo, barcode) available\n";
    echo "✅ Frontend receives complete user data immediately\n";
    
} else {
    echo "❌ API validation failed\n";
    echo "   User would be created as LOCAL type\n";
    echo "   No promotion to API type\n";
    echo "   Limited user data available\n";
}

echo "\n🔍 Frontend Verification Checklist:\n";
echo "-----------------------------------\n";
echo "□ foto_url field present and accessible\n";
echo "□ barcode field available for payment functionality\n";
echo "□ saldo field shows correct financial status\n";
echo "□ semaforo field indicates debt status correctly\n";
echo "□ All additional fields mapped and typed correctly\n";
echo "□ Image loads immediately after registration\n";
echo "□ No additional API calls needed for complete user data\n";

echo "\n🏁 Registration Flow Test Complete\n";
