<?php

echo "✅ === VERIFICACIÓN: SOLO 5 USUARIOS === ✅\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

try {
    echo "PASO 1: CONTEO TOTAL DE USUARIOS\n";
    echo str_repeat("=", 60) . "\n";
    
    $totalUsers = \App\Models\User::count();
    echo "Total de usuarios en BD: {$totalUsers}\n\n";
    
    if ($totalUsers !== 5) {
        echo "⚠️  ADVERTENCIA: Se esperaban 5 usuarios, se encontraron {$totalUsers}\n\n";
    } else {
        echo "✅ CORRECTO: Exactamente 5 usuarios\n\n";
    }
    
    echo "PASO 2: LISTADO DE USUARIOS\n";
    echo str_repeat("=", 60) . "\n";
    
    $users = \App\Models\User::orderBy('id')->get();
    
    foreach ($users as $i => $user) {
        echo "\n" . ($i + 1) . ". {$user->name}\n";
        echo "   ID: {$user->id}\n";
        echo "   Email: {$user->email}\n";
        echo "   DNI: {$user->dni}\n";
        $tipo = is_object($user->user_type) ? $user->user_type->value : $user->user_type;
        echo "   Tipo: {$tipo}\n";
        echo "   Admin: " . ($user->is_admin ? 'SÍ' : 'NO') . "\n";
        echo "   Profesor: " . ($user->is_professor ? 'SÍ' : 'NO') . "\n";
        $gymAccess = $user->student_gym == 1 ? 'SÍ' : 'NO';
        echo "   Gimnasio: {$gymAccess}\n";
        echo "   Estado: {$user->account_status}\n";
    }
    
    echo "\n\nPASO 3: VERIFICACIÓN POR TIPO\n";
    echo str_repeat("=", 60) . "\n";
    
    $admin = \App\Models\User::where('is_admin', true)->first();
    $professor = \App\Models\User::where('is_professor', true)->first();
    $local = \App\Models\User::where('user_type', 'local')->where('is_admin', false)->first();
    $apiRegular = \App\Models\User::where('user_type', 'api')->where('student_gym', false)->first();
    $gymStudent = \App\Models\User::where('student_gym', true)->first();
    
    echo "\n✅ TIPOS DE USUARIO:\n";
    echo "  1. Admin: " . ($admin ? "✅ {$admin->name}" : "❌ NO ENCONTRADO") . "\n";
    echo "  2. Profesor: " . ($professor ? "✅ {$professor->name}" : "❌ NO ENCONTRADO") . "\n";
    echo "  3. Local: " . ($local ? "✅ {$local->name}" : "❌ NO ENCONTRADO") . "\n";
    echo "  4. API Regular: " . ($apiRegular ? "✅ {$apiRegular->name}" : "❌ NO ENCONTRADO") . "\n";
    echo "  5. Estudiante Gimnasio: " . ($gymStudent ? "✅ {$gymStudent->name}" : "❌ NO ENCONTRADO") . "\n";
    
    echo "\n\nPASO 4: RESUMEN FINAL\n";
    echo str_repeat("=", 60) . "\n";
    
    $allFound = $admin && $professor && $local && $apiRegular && $gymStudent;
    
    if ($totalUsers === 5 && $allFound) {
        echo "🎉 VERIFICACIÓN EXITOSA\n";
        echo "✅ Exactamente 5 usuarios\n";
        echo "✅ Todos los tipos esperados están presentes\n";
        echo "✅ No hay usuarios adicionales\n\n";
        
        echo "📋 CREDENCIALES:\n";
        echo "  • Admin: admin@villamitre.com / admin123 (DNI: 11111111)\n";
        echo "  • Profesor: profesor@villamitre.com / profesor123 (DNI: 22222222)\n";
        echo "  • Local: local@villamitre.com / local123 (DNI: 33333333)\n";
        echo "  • API Regular: api@villamitre.com / api123 (DNI: 44444444)\n";
        echo "  • María García: maria.garcia@villamitre.com / estudiante123 (DNI: 55555555)\n";
        
    } else {
        echo "❌ VERIFICACIÓN FALLIDA\n";
        if ($totalUsers !== 5) {
            echo "  • Total de usuarios incorrecto: {$totalUsers} (esperado: 5)\n";
        }
        if (!$allFound) {
            echo "  • Algunos tipos de usuario no fueron encontrados\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
