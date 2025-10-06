<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 === CREANDO CONFIGURACIÓN DE PRUEBA === 🔧\n\n";

try {
    // Crear configuración de prueba
    $setting = App\Models\SystemSetting::create([
        'key' => 'test_setting',
        'value' => 'test_value',
        'category' => 'testing',
        'description' => 'Configuración de prueba para testing',
        'is_public' => false
    ]);
    
    echo "✅ Configuración creada:\n";
    echo "   ID: {$setting->id}\n";
    echo "   Key: {$setting->key}\n";
    echo "   Value: {$setting->value}\n";
    
    // Verificar que se puede leer
    $retrieved = App\Models\SystemSetting::where('key', 'test_setting')->first();
    if ($retrieved) {
        echo "✅ Configuración verificada en BD\n";
    } else {
        echo "❌ No se pudo verificar la configuración\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
