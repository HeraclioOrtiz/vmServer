<?php

echo "📋 === PLANTILLAS DIARIAS CREADAS === 📋\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $templates = \App\Models\Gym\DailyTemplate::with([
        'exercises.exercise',
        'exercises.sets'
    ])->get();
    
    echo "Total de plantillas: " . $templates->count() . "\n";
    echo str_repeat("=", 90) . "\n\n";
    
    foreach ($templates as $i => $template) {
        echo "PLANTILLA " . ($i + 1) . "\n";
        echo str_repeat("-", 90) . "\n";
        echo "ID: {$template->id}\n";
        echo "Título: {$template->title}\n";
        echo "Goal: {$template->goal}\n";
        echo "Nivel: {$template->level}\n";
        echo "Duración estimada: {$template->estimated_duration_min} minutos\n";
        echo "Tags: " . implode(", ", $template->tags ?? []) . "\n";
        echo "Preset: " . ($template->is_preset ? 'Sí' : 'No') . "\n";
        
        echo "\nEJERCICIOS (" . $template->exercises->count() . "):\n";
        
        foreach ($template->exercises as $j => $te) {
            echo "\n  " . ($j + 1) . ". {$te->exercise->name} (Orden: {$te->display_order})\n";
            
            if ($te->notes) {
                echo "     Notas: {$te->notes}\n";
            }
            
            echo "     Grupos musculares: " . implode(", ", $te->exercise->muscle_groups ?? []) . "\n";
            echo "     Patrón: {$te->exercise->movement_pattern}\n";
            echo "     Dificultad: {$te->exercise->difficulty_level}\n";
            
            echo "\n     SETS (" . $te->sets->count() . "):\n";
            
            foreach ($te->sets as $set) {
                $reps = $set->reps_min == $set->reps_max 
                    ? "{$set->reps_min}" 
                    : "{$set->reps_min}-{$set->reps_max}";
                
                echo "       Set {$set->set_number}: {$reps} reps";
                
                if ($set->rpe_target) {
                    echo " @ RPE {$set->rpe_target}";
                }
                
                if ($set->rest_seconds) {
                    echo " | Descanso: {$set->rest_seconds}s";
                }
                
                if ($set->notes) {
                    echo " | {$set->notes}";
                }
                
                echo "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 90) . "\n\n";
    }
    
    // Verificar estructura JSON para API
    echo "📊 ESTRUCTURA JSON PARA API:\n";
    echo str_repeat("=", 90) . "\n";
    
    $firstTemplate = $templates->first();
    if ($firstTemplate) {
        $json = json_encode($firstTemplate->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo substr($json, 0, 1000) . "...\n\n";
    }
    
    echo "✅ Todas las plantillas verificadas correctamente\n";
    
    // Estadísticas
    $totalExercises = 0;
    $totalSets = 0;
    
    foreach ($templates as $t) {
        $totalExercises += $t->exercises->count();
        foreach ($t->exercises as $e) {
            $totalSets += $e->sets->count();
        }
    }
    
    echo "\n📈 ESTADÍSTICAS:\n";
    echo "  • Plantillas creadas: {$templates->count()}\n";
    echo "  • Total ejercicios asignados: {$totalExercises}\n";
    echo "  • Total sets configurados: {$totalSets}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
