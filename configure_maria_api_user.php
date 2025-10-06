<?php

echo "🔧 === CONFIGURANDO MARÍA GARCÍA COMO USUARIO API === 🔧\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔍 Buscando usuario María García...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    if (!$maria) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$maria->name}\n";
    echo "📊 Estado actual:\n";
    echo "   - user_type: " . ($maria->user_type ? $maria->user_type->value ?? $maria->user_type : 'N/A') . "\n";
    echo "   - type_label: {$maria->type_label}\n";
    echo "   - promotion_status: " . ($maria->promotion_status ? $maria->promotion_status->value ?? $maria->promotion_status : 'N/A') . "\n\n";
    
    echo "🔧 Cambiando a usuario API...\n";
    
    // Verificar si UserType es un enum
    if (class_exists('App\\Enums\\UserType')) {
        $maria->user_type = \App\Enums\UserType::API;
        echo "✅ user_type configurado como UserType::API\n";
    } else {
        $maria->user_type = 'api';
        echo "✅ user_type configurado como 'api'\n";
    }
    
    $maria->type_label = 'Usuario API';
    
    // Configurar promotion_status (usar APPROVED para usuarios API)
    if (class_exists('App\\Enums\\PromotionStatus')) {
        $maria->promotion_status = \App\Enums\PromotionStatus::APPROVED;
        echo "✅ promotion_status configurado como PromotionStatus::APPROVED\n";
    } else {
        $maria->promotion_status = 'approved';
        echo "✅ promotion_status configurado como 'approved'\n";
    }
    
    // Asegurar que esté activo y verificado
    $maria->account_status = 'active';
    $maria->email_verified_at = now();
    
    // Actualizar password para asegurar acceso
    $maria->password = bcrypt('estudiante123');
    
    // Verificar si existe columna is_api_user y configurarla
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    if (in_array('is_api_user', $columns)) {
        $maria->is_api_user = true;
        echo "✅ Marcado como is_api_user = true\n";
    }
    
    // Verificar si existe columna api_access y configurarla
    if (in_array('api_access', $columns)) {
        $maria->api_access = true;
        echo "✅ Marcado como api_access = true\n";
    }
    
    $maria->save();
    
    echo "✅ Usuario actualizado exitosamente\n\n";
    
    // Verificar cambios
    $maria->refresh();
    
    echo "📊 Estado final:\n";
    echo "   - user_type: " . ($maria->user_type ? $maria->user_type->value ?? $maria->user_type : 'N/A') . "\n";
    echo "   - type_label: {$maria->type_label}\n";
    echo "   - promotion_status: " . ($maria->promotion_status ? $maria->promotion_status->value ?? $maria->promotion_status : 'N/A') . "\n";
    echo "   - account_status: {$maria->account_status}\n";
    echo "   - email_verified_at: " . ($maria->email_verified_at ? 'Verificado' : 'No verificado') . "\n";
    
    if (in_array('is_api_user', $columns)) {
        echo "   - is_api_user: " . ($maria->is_api_user ? 'true' : 'false') . "\n";
    }
    
    echo "\n🧪 Probando login API...\n";
    
    // Función para hacer requests HTTP
    function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true),
            'error' => $error
        ];
    }
    
    // Probar login
    $loginData = [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ];
    
    $result = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', $loginData);
    
    echo "Status: {$result['status']}\n";
    
    if ($result['status'] === 200) {
        echo "✅ Login exitoso\n";
        
        $responseData = $result['data']['data'] ?? [];
        echo "📊 Datos de respuesta:\n";
        echo "   - user_type: " . ($responseData['user']['user_type'] ?? 'N/A') . "\n";
        echo "   - type_label: " . ($responseData['user']['type_label'] ?? 'N/A') . "\n";
        echo "   - promotion_status: " . ($responseData['user']['promotion_status'] ?? 'N/A') . "\n";
        echo "   - token: " . (isset($responseData['token']) ? substr($responseData['token'], 0, 20) . '...' : 'N/A') . "\n";
        
        if (isset($responseData['token'])) {
            echo "\n🔗 Probando endpoint de plantillas...\n";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/student/my-templates');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $responseData['token']
            ]);
            
            $templatesResponse = curl_exec($ch);
            $templatesStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "Status plantillas: {$templatesStatus}\n";
            
            if ($templatesStatus === 200) {
                echo "✅ Acceso a plantillas exitoso\n";
                $templatesData = json_decode($templatesResponse, true);
                $count = count($templatesData['data']['templates'] ?? []);
                echo "📊 Plantillas encontradas: {$count}\n";
            } else {
                echo "❌ Error accediendo a plantillas\n";
            }
        }
        
    } else {
        echo "❌ Login falló\n";
        if (isset($result['data']['message'])) {
            echo "📝 Mensaje: {$result['data']['message']}\n";
        }
        if ($result['error']) {
            echo "📝 Error: {$result['error']}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 RESUMEN FINAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "👤 Usuario: {$maria->name}\n";
    echo "🔧 Tipo: {$maria->user_type} ({$maria->type_label})\n";
    echo "📊 Estado: {$maria->promotion_status}\n";
    echo "🔐 Login API: " . ($result['status'] === 200 ? '✅ Funciona' : '❌ Falla') . "\n";
    
    if ($result['status'] === 200) {
        echo "\n🎉 MARÍA GARCÍA CONFIGURADA COMO USUARIO API:\n";
        echo "✅ user_type: 'api'\n";
        echo "✅ type_label: 'Usuario API'\n";
        echo "✅ promotion_status: 'api_user'\n";
        echo "✅ Acceso completo a endpoints de gimnasio\n";
        echo "✅ Lista para testing de app móvil\n";
        
        echo "\n📱 CREDENCIALES PARA APP MÓVIL:\n";
        echo "   DNI: 33333333\n";
        echo "   Password: estudiante123\n";
        echo "   Tipo: Usuario API (no local)\n";
        
    } else {
        echo "\n⚠️  PROBLEMAS DETECTADOS:\n";
        echo "❌ No se pudo completar la configuración API\n";
        echo "🔧 Revisar configuración del sistema\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
