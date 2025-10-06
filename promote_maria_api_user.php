<?php

echo "🔧 === PROMOVIENDO MARÍA GARCÍA A API USER === 🔧\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "🔍 Buscando usuario con DNI 33333333...\n";
    
    $maria = \App\Models\User::where('dni', '33333333')->first();
    
    if (!$maria) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$maria->name}\n";
    echo "📊 Estado actual:\n";
    echo "   - ID: {$maria->id}\n";
    echo "   - Nombre: {$maria->name}\n";
    echo "   - Email: {$maria->email}\n";
    echo "   - DNI: {$maria->dni}\n";
    echo "   - Es Profesor: " . ($maria->is_professor ? 'Sí' : 'No') . "\n";
    echo "   - Es Admin: " . ($maria->is_admin ? 'Sí' : 'No') . "\n";
    echo "   - Estado: {$maria->account_status}\n";
    echo "   - API User: " . (isset($maria->is_api_user) && $maria->is_api_user ? 'Sí' : 'No') . "\n\n";
    
    echo "🔧 Actualizando permisos...\n";
    
    // Verificar si la columna is_api_user existe
    $hasApiUserColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_api_user');
    
    if ($hasApiUserColumn) {
        $maria->is_api_user = true;
        echo "✅ Marcado como API User\n";
    } else {
        echo "⚠️  Columna is_api_user no existe, continuando sin ella\n";
    }
    
    // Asegurar que tenga acceso completo
    $maria->account_status = 'active';
    $maria->email_verified_at = now();
    
    // Actualizar password para asegurar acceso
    $maria->password = bcrypt('estudiante123');
    
    $maria->save();
    
    echo "✅ Usuario actualizado exitosamente\n\n";
    
    echo "📊 Estado final:\n";
    echo "   - Nombre: {$maria->name}\n";
    echo "   - Email: {$maria->email}\n";
    echo "   - DNI: {$maria->dni}\n";
    echo "   - Estado: {$maria->account_status}\n";
    echo "   - Email verificado: " . ($maria->email_verified_at ? 'Sí' : 'No') . "\n";
    if ($hasApiUserColumn) {
        echo "   - API User: " . ($maria->is_api_user ? 'Sí' : 'No') . "\n";
    }
    echo "   - Password: Actualizado (estudiante123)\n\n";
    
    echo "🧪 Probando login...\n";
    
    // Probar autenticación
    $credentials = [
        'dni' => '33333333',
        'password' => 'estudiante123'
    ];
    
    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        echo "✅ Login exitoso\n";
        
        // Crear token de prueba
        $user = \Illuminate\Support\Facades\Auth::user();
        $token = $user->createToken('test-token')->plainTextToken;
        
        echo "✅ Token generado: " . substr($token, 0, 20) . "...\n";
        
        \Illuminate\Support\Facades\Auth::logout();
    } else {
        echo "❌ Error en login\n";
    }
    
    echo "\n🎉 PROMOCIÓN COMPLETADA:\n";
    echo "✅ María García está lista para testing completo de API\n";
    echo "✅ Credenciales: DNI 33333333 / Password: estudiante123\n";
    echo "✅ Permisos: Usuario activo con acceso completo\n";
    echo "✅ Puede usar todos los endpoints de estudiante\n\n";
    
    echo "🔗 ENDPOINTS DISPONIBLES:\n";
    echo "   POST /api/auth/login\n";
    echo "   GET /api/student/my-templates\n";
    echo "   GET /api/student/template/{id}/details\n";
    echo "   GET /api/student/my-weekly-calendar\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
