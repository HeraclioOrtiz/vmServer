<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 === DEBUG PROFUNDO PROFESSOR ERROR === 🔍\n\n";

try {
    echo "1. Verificando modelo User y campos:\n";
    $professor = App\Models\User::where('is_professor', true)->first();
    
    if ($professor) {
        echo "   ✅ Profesor encontrado: {$professor->name}\n";
        echo "   ID: {$professor->id}\n";
        echo "   Email: {$professor->email}\n";
        
        // Verificar campos específicos que causan problemas
        echo "\n2. Verificando campos problemáticos:\n";
        echo "   display_name: " . ($professor->display_name ?? 'NULL') . "\n";
        echo "   avatar_url: " . ($professor->avatar_url ?? 'NULL') . "\n";
        echo "   professor_since: " . ($professor->professor_since ?? 'NULL') . "\n";
        echo "   admin_notes: " . ($professor->admin_notes ?? 'NULL') . "\n";
        
        // Verificar relación tokens
        echo "\n3. Verificando relación tokens:\n";
        $tokensCount = $professor->tokens()->count();
        echo "   Tokens count: $tokensCount\n";
        
        if ($tokensCount > 0) {
            $lastToken = $professor->tokens()->latest()->first();
            echo "   Último token ID: {$lastToken->id}\n";
            echo "   Last used: " . ($lastToken->last_used_at ?? 'NULL') . "\n";
        }
        
        // Verificar método getProfessorStats
        echo "\n4. Verificando getProfessorStats():\n";
        $stats = $professor->getProfessorStats();
        echo "   Stats obtenidas: " . json_encode($stats) . "\n";
        
        // Probar extractSpecialties directamente
        echo "\n5. Probando extractSpecialties():\n";
        $service = app(App\Services\Admin\ProfessorManagementService::class);
        
        // Usar reflexión para acceder al método privado
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('extractSpecialties');
        $method->setAccessible(true);
        
        $specialties = $method->invoke($service, $professor->admin_notes ?? null);
        echo "   Specialties: " . json_encode($specialties) . "\n";
        
        echo "\n✅ Todos los campos verificados sin errores\n";
        
    } else {
        echo "   ❌ No se encontró ningún profesor\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error encontrado:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Línea específica del error:\n";
    
    // Mostrar contexto del error
    $file = file($e->getFile());
    $line = $e->getLine();
    for ($i = max(0, $line - 3); $i < min(count($file), $line + 2); $i++) {
        $marker = ($i + 1 == $line) ? '>>>' : '   ';
        echo "   $marker " . ($i + 1) . ": " . trim($file[$i]) . "\n";
    }
}
