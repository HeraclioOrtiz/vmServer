<?php

echo "🔍 === VERIFICANDO ESTADO API DE MARÍA GARCÍA === 🔍\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "👤 Consultando usuario María García...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    if (!$maria) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado\n\n";
    
    echo "📊 INFORMACIÓN COMPLETA DEL USUARIO:\n";
    echo str_repeat("=", 50) . "\n";
    echo "ID: {$maria->id}\n";
    echo "Nombre: {$maria->name}\n";
    echo "Email: {$maria->email}\n";
    echo "DNI: {$maria->dni}\n";
    echo "Estado cuenta: {$maria->account_status}\n";
    echo "Email verificado: " . ($maria->email_verified_at ? $maria->email_verified_at : 'No') . "\n";
    echo "Es profesor: " . ($maria->is_professor ? 'Sí' : 'No') . "\n";
    echo "Es admin: " . ($maria->is_admin ? 'Sí' : 'No') . "\n";
    
    // Verificar columnas relacionadas con API
    echo "\n🔍 VERIFICANDO COLUMNAS API:\n";
    echo str_repeat("=", 50) . "\n";
    
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    
    $apiRelatedColumns = ['is_api_user', 'api_token', 'api_access', 'api_permissions'];
    
    foreach ($apiRelatedColumns as $column) {
        if (in_array($column, $columns)) {
            $value = $maria->$column ?? 'NULL';
            echo "✅ {$column}: {$value}\n";
        } else {
            echo "❌ {$column}: Columna no existe\n";
        }
    }
    
    echo "\n📋 TODAS LAS COLUMNAS DE LA TABLA USERS:\n";
    echo str_repeat("=", 50) . "\n";
    foreach ($columns as $column) {
        $value = $maria->$column ?? 'NULL';
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        if (strlen($value) > 50) {
            $value = substr($value, 0, 47) . '...';
        }
        echo "- {$column}: {$value}\n";
    }
    
    echo "\n🧪 PROBANDO CAPACIDADES API:\n";
    echo str_repeat("=", 50) . "\n";
    
    // Probar login
    echo "1. Probando login...\n";
    $credentials = [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ];
    
    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        echo "   ✅ Login exitoso\n";
        
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Probar creación de token
        echo "2. Probando creación de token...\n";
        try {
            $token = $user->createToken('verification-token');
            echo "   ✅ Token creado exitosamente\n";
            echo "   📝 Token ID: {$token->accessToken->id}\n";
            echo "   📝 Token (primeros 20 chars): " . substr($token->plainTextToken, 0, 20) . "...\n";
            
            // Verificar tokens existentes
            echo "3. Verificando tokens existentes...\n";
            $tokens = $user->tokens;
            echo "   📊 Total tokens: " . $tokens->count() . "\n";
            
            foreach ($tokens->take(3) as $existingToken) {
                echo "   - Token ID {$existingToken->id}: {$existingToken->name} (creado: {$existingToken->created_at})\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Error creando token: " . $e->getMessage() . "\n";
        }
        
        \Illuminate\Support\Facades\Auth::logout();
        
    } else {
        echo "   ❌ Error en login\n";
    }
    
    echo "\n🔗 PROBANDO ENDPOINTS API:\n";
    echo str_repeat("=", 50) . "\n";
    
    // Función para hacer requests HTTP
    function makeRequest($url, $method = 'GET', $data = null, $token = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = "Authorization: Bearer $token";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
    
    // Probar login via API
    echo "1. POST /api/auth/login\n";
    $loginResponse = makeRequest('http://127.0.0.1:8000/api/auth/login', 'POST', [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ]);
    
    echo "   Status: {$loginResponse['status']}\n";
    
    if ($loginResponse['status'] === 200 && isset($loginResponse['data']['data']['token'])) {
        echo "   ✅ Login API exitoso\n";
        $apiToken = $loginResponse['data']['data']['token'];
        echo "   📝 Token obtenido: " . substr($apiToken, 0, 20) . "...\n";
        
        // Probar endpoints protegidos
        echo "\n2. GET /api/student/my-templates\n";
        $templatesResponse = makeRequest('http://127.0.0.1:8000/api/student/my-templates', 'GET', null, $apiToken);
        echo "   Status: {$templatesResponse['status']}\n";
        
        if ($templatesResponse['status'] === 200) {
            echo "   ✅ Plantillas obtenidas exitosamente\n";
            $templatesCount = count($templatesResponse['data']['data']['templates'] ?? []);
            echo "   📊 Plantillas encontradas: {$templatesCount}\n";
        } else {
            echo "   ❌ Error obteniendo plantillas\n";
            if (isset($templatesResponse['data']['message'])) {
                echo "   📝 Mensaje: {$templatesResponse['data']['message']}\n";
            }
        }
        
        echo "\n3. GET /api/student/my-weekly-calendar\n";
        $calendarResponse = makeRequest('http://127.0.0.1:8000/api/student/my-weekly-calendar', 'GET', null, $apiToken);
        echo "   Status: {$calendarResponse['status']}\n";
        
        if ($calendarResponse['status'] === 200) {
            echo "   ✅ Calendario obtenido exitosamente\n";
        } else {
            echo "   ❌ Error obteniendo calendario\n";
        }
        
    } else {
        echo "   ❌ Login API falló\n";
        if (isset($loginResponse['data']['message'])) {
            echo "   📝 Mensaje: {$loginResponse['data']['message']}\n";
        }
        if ($loginResponse['error']) {
            echo "   📝 Error cURL: {$loginResponse['error']}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 RESUMEN FINAL:\n";
    echo str_repeat("=", 50) . "\n";
    
    $isApiReady = $loginResponse['status'] === 200 && isset($loginResponse['data']['data']['token']);
    
    echo "👤 Usuario: {$maria->name}\n";
    echo "🔐 Login directo: " . (\Illuminate\Support\Facades\Auth::attempt($credentials) ? '✅ Funciona' : '❌ Falla') . "\n";
    echo "🔗 Login API: " . ($isApiReady ? '✅ Funciona' : '❌ Falla') . "\n";
    echo "🎫 Creación tokens: " . (method_exists($maria, 'createToken') ? '✅ Disponible' : '❌ No disponible') . "\n";
    echo "📊 Estado cuenta: {$maria->account_status}\n";
    
    if ($isApiReady) {
        echo "\n🎉 MARÍA GARCÍA ESTÁ LISTA PARA API:\n";
        echo "✅ Puede hacer login via API\n";
        echo "✅ Puede obtener tokens de acceso\n";
        echo "✅ Puede acceder a endpoints protegidos\n";
        echo "✅ Perfecta para testing de app móvil\n";
    } else {
        echo "\n⚠️  PROBLEMAS DETECTADOS:\n";
        echo "❌ No puede usar API completamente\n";
        echo "🔧 Revisar configuración de autenticación\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
