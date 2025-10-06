<?php

echo "📋 === EJERCICIOS CREADOS === 📋\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $exercises = \App\Models\Gym\Exercise::all();
    
    echo "Total de ejercicios: " . $exercises->count() . "\n";
    echo str_repeat("=", 80) . "\n\n";
    
    foreach ($exercises as $i => $exercise) {
        echo "EJERCICIO " . ($i + 1) . "\n";
        echo str_repeat("-", 80) . "\n";
        echo "ID: {$exercise->id}\n";
        echo "Nombre: {$exercise->name}\n";
        echo "\nDescripción:\n{$exercise->description}\n";
        
        echo "\nGrupos Musculares:\n";
        if (is_array($exercise->muscle_groups)) {
            foreach ($exercise->muscle_groups as $mg) {
                echo "  • {$mg}\n";
            }
        }
        
        echo "\nMúsculos Objetivo:\n";
        if (is_array($exercise->target_muscle_groups)) {
            foreach ($exercise->target_muscle_groups as $tmg) {
                echo "  • {$tmg}\n";
            }
        }
        
        echo "\nPatrón de Movimiento: {$exercise->movement_pattern}\n";
        echo "Equipamiento: {$exercise->equipment}\n";
        echo "Nivel de Dificultad: {$exercise->difficulty_level}\n";
        
        echo "\nTags:\n";
        if (is_array($exercise->tags)) {
            foreach ($exercise->tags as $tag) {
                echo "  • {$tag}\n";
            }
        }
        
        echo "\nInstrucciones:\n{$exercise->instructions}\n";
        
        echo "\nFecha de creación: {$exercise->created_at}\n";
        echo str_repeat("=", 80) . "\n\n";
    }
    
    echo "✅ Todos los ejercicios mostrados correctamente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
