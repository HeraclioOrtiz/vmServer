<?php

echo "🏋️ === AGREGANDO DATOS DE PESO A SETS === 🏋️\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Obtener todas las series que no tienen peso asignado
    $sets = \App\Models\Gym\DailyTemplateSet::whereNull('weight_target')->get();
    
    echo "📊 Sets sin peso: " . $sets->count() . "\n\n";
    
    $updated = 0;
    
    foreach ($sets as $set) {
        // Obtener el ejercicio asociado
        $exercise = $set->exercise->exercise ?? null;
        
        if (!$exercise) {
            continue;
        }
        
        echo "🏋️ Procesando: {$exercise->name}\n";
        echo "   Set #{$set->set_number} - Reps: {$set->reps_min}-{$set->reps_max} - RPE: {$set->rpe_target}\n";
        
        // Asignar pesos basados en el tipo de ejercicio y RPE
        $weightData = calculateWeightForExercise($exercise, $set);
        
        if ($weightData) {
            $set->update([
                'weight_min' => $weightData['min'],
                'weight_max' => $weightData['max'],
                'weight_target' => $weightData['target']
            ]);
            
            echo "   ✅ Peso asignado: {$weightData['min']}-{$weightData['max']}kg (objetivo: {$weightData['target']}kg)\n";
            $updated++;
        } else {
            echo "   ⚠️ No se pudo calcular peso para este ejercicio\n";
        }
        
        echo "\n";
    }
    
    echo "✅ PROCESO COMPLETADO\n";
    echo "📊 Sets actualizados: {$updated}\n";
    echo "📊 Total procesados: " . $sets->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

function calculateWeightForExercise($exercise, $set) {
    $exerciseName = strtolower($exercise->name);
    $rpe = $set->rpe_target ?? 7.0;
    $repsMin = $set->reps_min ?? 8;
    
    // Pesos base aproximados para diferentes ejercicios (para principiantes/intermedios)
    $baseWeights = [
        // Ejercicios principales
        'sentadilla' => ['min' => 40, 'max' => 80],
        'squat' => ['min' => 40, 'max' => 80],
        'peso muerto' => ['min' => 50, 'max' => 100],
        'deadlift' => ['min' => 50, 'max' => 100],
        'press banca' => ['min' => 30, 'max' => 70],
        'bench press' => ['min' => 30, 'max' => 70],
        'press militar' => ['min' => 20, 'max' => 50],
        'overhead press' => ['min' => 20, 'max' => 50],
        
        // Ejercicios de tracción
        'dominada' => ['min' => 0, 'max' => 20], // peso corporal + adicional
        'pull up' => ['min' => 0, 'max' => 20],
        'remo' => ['min' => 25, 'max' => 60],
        'row' => ['min' => 25, 'max' => 60],
        
        // Ejercicios de empuje
        'press inclinado' => ['min' => 25, 'max' => 60],
        'incline press' => ['min' => 25, 'max' => 60],
        'press declinado' => ['min' => 30, 'max' => 70],
        'decline press' => ['min' => 30, 'max' => 70],
        
        // Ejercicios de piernas
        'prensa' => ['min' => 60, 'max' => 150],
        'leg press' => ['min' => 60, 'max' => 150],
        'extensión' => ['min' => 15, 'max' => 40],
        'extension' => ['min' => 15, 'max' => 40],
        'curl' => ['min' => 10, 'max' => 30],
        
        // Ejercicios de brazos
        'curl bíceps' => ['min' => 8, 'max' => 25],
        'bicep curl' => ['min' => 8, 'max' => 25],
        'tríceps' => ['min' => 10, 'max' => 30],
        'tricep' => ['min' => 10, 'max' => 30],
        
        // Por defecto para ejercicios no identificados
        'default' => ['min' => 10, 'max' => 30]
    ];
    
    // Buscar el ejercicio en la lista
    $weights = null;
    foreach ($baseWeights as $key => $value) {
        if (strpos($exerciseName, $key) !== false) {
            $weights = $value;
            break;
        }
    }
    
    // Si no se encuentra, usar valores por defecto
    if (!$weights) {
        $weights = $baseWeights['default'];
    }
    
    // Ajustar según RPE y reps
    $rpeMultiplier = 1.0;
    if ($rpe >= 9.0) {
        $rpeMultiplier = 1.2; // Más peso para RPE alto
    } elseif ($rpe >= 8.0) {
        $rpeMultiplier = 1.1;
    } elseif ($rpe <= 6.0) {
        $rpeMultiplier = 0.8; // Menos peso para RPE bajo
    }
    
    // Ajustar según reps (más reps = menos peso)
    $repMultiplier = 1.0;
    if ($repsMin >= 15) {
        $repMultiplier = 0.7;
    } elseif ($repsMin >= 12) {
        $repMultiplier = 0.8;
    } elseif ($repsMin >= 8) {
        $repMultiplier = 0.9;
    } elseif ($repsMin <= 5) {
        $repMultiplier = 1.2;
    }
    
    $finalMultiplier = $rpeMultiplier * $repMultiplier;
    
    $minWeight = round($weights['min'] * $finalMultiplier, 1);
    $maxWeight = round($weights['max'] * $finalMultiplier, 1);
    $targetWeight = round(($minWeight + $maxWeight) / 2, 1);
    
    return [
        'min' => $minWeight,
        'max' => $maxWeight,
        'target' => $targetWeight
    ];
}
