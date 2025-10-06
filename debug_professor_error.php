<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 === DEBUG PROFESSOR CONTROLLER ERROR === 🔍\n\n";

try {
    echo "1. Verificando usuarios profesores en BD:\n";
    $professors = App\Models\User::where('is_professor', true)->get(['id', 'name', 'email']);
    echo "   Profesores encontrados: " . $professors->count() . "\n";
    
    foreach ($professors as $prof) {
        echo "   - ID: {$prof->id}, Nombre: {$prof->name}\n";
    }
    
    echo "\n2. Probando ProfessorManagementService directamente:\n";
    $service = app(App\Services\Admin\ProfessorManagementService::class);
    
    echo "   Llamando getFilteredProfessors([])...\n";
    $result = $service->getFilteredProfessors([]);
    echo "   Tipo de resultado: " . gettype($result) . "\n";
    echo "   Clase: " . get_class($result) . "\n";
    echo "   Count: " . $result->count() . "\n";
    
    echo "\n3. Probando transformProfessorsWithStats...\n";
    $transformed = $service->transformProfessorsWithStats($result);
    echo "   Transformación exitosa: " . ($transformed ? 'Sí' : 'No') . "\n";
    echo "   Count transformado: " . $transformed->count() . "\n";
    
    echo "\n✅ Service funciona correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error encontrado:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
