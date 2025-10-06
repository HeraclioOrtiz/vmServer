<?php

echo "🔄 === REFRESCANDO SEEDERS CON CAMPOS DE PESO === 🔄\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🗑️ Limpiando datos existentes...\n";
    
    // Limpiar en orden correcto (por las foreign keys)
    \App\Models\Gym\DailyTemplateSet::query()->delete();
    echo "  ✅ Sets eliminados\n";
    
    \App\Models\Gym\DailyTemplateExercise::query()->delete();
    echo "  ✅ Ejercicios de plantillas eliminados\n";
    
    \App\Models\Gym\DailyTemplate::query()->delete();
    echo "  ✅ Plantillas eliminadas\n";
    
    echo "\n📦 Ejecutando seeders actualizados...\n\n";
    
    // Ejecutar el seeder actualizado
    $seeder = new \Database\Seeders\GymDailyTemplatesSeeder();
    $seeder->run();
    
    echo "\n✅ PROCESO COMPLETADO\n\n";
    
    // Verificar resultados
    echo "📊 VERIFICACIÓN DE RESULTADOS:\n";
    echo str_repeat("=", 50) . "\n";
    
    $templates = \App\Models\Gym\DailyTemplate::with(['exercises.exercise', 'exercises.sets'])->get();
    
    echo "📋 Plantillas creadas: " . $templates->count() . "\n\n";
    
    foreach ($templates as $template) {
        echo "🏋️ {$template->title}\n";
        echo "   📊 Ejercicios: " . $template->exercises->count() . "\n";
        
        $totalSets = 0;
        $setsWithWeight = 0;
        
        foreach ($template->exercises as $exercise) {
            $totalSets += $exercise->sets->count();
            $setsWithWeight += $exercise->sets->whereNotNull('weight_target')->count();
        }
        
        echo "   📊 Total sets: {$totalSets}\n";
        echo "   🏋️ Sets con peso: {$setsWithWeight} (" . round($setsWithWeight/$totalSets*100, 1) . "%)\n";
        
        if ($setsWithWeight > 0) {
            $avgWeight = $exercise->sets->whereNotNull('weight_target')->avg('weight_target');
            echo "   ⚖️ Peso promedio objetivo: " . round($avgWeight, 1) . "kg\n";
        }
        
        echo "\n";
    }
    
    echo str_repeat("=", 50) . "\n";
    echo "🎯 ESTADO FINAL:\n";
    
    if ($setsWithWeight == $totalSets) {
        echo "✅ ÉXITO: Todos los sets tienen campos de peso\n";
        echo "🚀 LISTO PARA APP MÓVIL\n";
    } else {
        echo "⚠️ PROBLEMA: Algunos sets no tienen campos de peso\n";
        echo "❌ NECESITA REVISIÓN\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
