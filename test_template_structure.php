<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 === ANÁLISIS DE ESTRUCTURA DE PLANTILLAS DIARIAS === 🔍\n\n";

// TEST 1: Verificar plantilla con ejercicios
echo "TEST 1: Verificar estructura completa de plantilla\n";

$template = \App\Models\Gym\DailyTemplate::with(['exercises.exercise', 'exercises.sets'])->first();

if ($template) {
    echo "✅ Plantilla encontrada: {$template->title}\n";
    echo "📊 ID: {$template->id}\n";
    echo "📊 Objetivo: {$template->goal}\n";
    echo "📊 Nivel: {$template->level}\n";
    echo "📊 Duración: {$template->estimated_duration_min} min\n";
    echo "📊 Es preset: " . ($template->is_preset ? 'SÍ' : 'NO') . "\n";
    
    echo "\n📋 EJERCICIOS EN LA PLANTILLA:\n";
    
    if ($template->exercises && $template->exercises->count() > 0) {
        foreach ($template->exercises as $index => $templateExercise) {
            echo "  " . ($index + 1) . ". Ejercicio ID: {$templateExercise->exercise_id}\n";
            
            if ($templateExercise->exercise) {
                echo "     Nombre: {$templateExercise->exercise->name}\n";
                echo "     Grupo muscular: {$templateExercise->exercise->muscle_group}\n";
                echo "     Equipamiento: {$templateExercise->exercise->equipment}\n";
            } else {
                echo "     ❌ PROBLEMA: Ejercicio no encontrado en BD\n";
            }
            
            echo "     Orden: {$templateExercise->order}\n";
            echo "     Notas: " . ($templateExercise->notes ?? 'Sin notas') . "\n";
            
            if ($templateExercise->sets && $templateExercise->sets->count() > 0) {
                echo "     📊 SERIES:\n";
                foreach ($templateExercise->sets as $set) {
                    echo "       Serie {$set->set_number}: ";
                    echo "{$set->reps_min}-{$set->reps_max} reps, ";
                    echo "descanso {$set->rest_seconds}s\n";
                }
            } else {
                echo "     ⚠️  Sin series configuradas\n";
            }
            echo "\n";
        }
    } else {
        echo "  ❌ PROBLEMA CRÍTICO: Plantilla sin ejercicios\n";
    }
    
} else {
    echo "❌ No se encontraron plantillas en la BD\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 2: Estadísticas generales
echo "TEST 2: Estadísticas del sistema\n";

$totalTemplates = \App\Models\Gym\DailyTemplate::count();
$totalTemplateExercises = \App\Models\Gym\DailyTemplateExercise::count();
$totalTemplateSets = \App\Models\Gym\DailyTemplateSet::count();
$totalExercises = \App\Models\Gym\Exercise::count();

echo "📊 Total plantillas diarias: {$totalTemplates}\n";
echo "📊 Total ejercicios en plantillas: {$totalTemplateExercises}\n";
echo "📊 Total series en plantillas: {$totalTemplateSets}\n";
echo "📊 Total ejercicios disponibles: {$totalExercises}\n";

// Verificar plantillas vacías
$emptyTemplates = \App\Models\Gym\DailyTemplate::doesntHave('exercises')->count();
echo "⚠️  Plantillas sin ejercicios: {$emptyTemplates}\n";

// Verificar ejercicios huérfanos
$orphanExercises = \App\Models\Gym\DailyTemplateExercise::whereDoesntHave('exercise')->count();
echo "❌ Ejercicios huérfanos (referencia rota): {$orphanExercises}\n";

echo "\n" . str_repeat("-", 60) . "\n\n";

// TEST 3: Verificar API response completa
echo "TEST 3: Verificar respuesta de API con relaciones\n";

function makeRequest($method, $endpoint, $token, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    
    $body = substr($response, $headerSize);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($body, true)
    ];
}

// Obtener token
$professorLogin = makeRequest('POST', '/test/login', null, ['dni' => '22222222', 'password' => 'profesor123']);
$professorToken = $professorLogin['data']['token'] ?? null;

if ($professorToken) {
    // Obtener una plantilla específica con relaciones
    $templateResponse = makeRequest('GET', '/admin/gym/daily-templates/1', $professorToken);
    
    if ($templateResponse['status'] == 200) {
        $apiTemplate = $templateResponse['data'];
        echo "✅ API responde correctamente\n";
        echo "📊 Plantilla: {$apiTemplate['title']}\n";
        
        if (isset($apiTemplate['exercises']) && is_array($apiTemplate['exercises'])) {
            echo "📊 Ejercicios en API: " . count($apiTemplate['exercises']) . "\n";
            
            if (count($apiTemplate['exercises']) > 0) {
                $firstExercise = $apiTemplate['exercises'][0];
                echo "📋 Primer ejercicio:\n";
                echo "  - ID template exercise: {$firstExercise['id']}\n";
                echo "  - Exercise ID: {$firstExercise['exercise_id']}\n";
                
                if (isset($firstExercise['exercise'])) {
                    echo "  - Nombre: {$firstExercise['exercise']['name']}\n";
                    echo "  - ✅ RELACIÓN CORRECTA: Ejercicio cargado\n";
                } else {
                    echo "  - ❌ PROBLEMA: Ejercicio no cargado en API\n";
                }
                
                if (isset($firstExercise['sets']) && is_array($firstExercise['sets'])) {
                    echo "  - Series: " . count($firstExercise['sets']) . "\n";
                    echo "  - ✅ SERIES CARGADAS\n";
                } else {
                    echo "  - ❌ PROBLEMA: Series no cargadas\n";
                }
            }
        } else {
            echo "❌ PROBLEMA CRÍTICO: API no devuelve ejercicios\n";
        }
    } else {
        echo "❌ Error en API: {$templateResponse['status']}\n";
    }
} else {
    echo "❌ No se pudo obtener token\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 ANÁLISIS COMPLETADO\n";

// Conclusión
echo "\n📋 RESUMEN:\n";
if ($totalTemplateExercises > 0 && $orphanExercises == 0) {
    echo "✅ SISTEMA CORRECTO: Plantillas contienen ejercicios del sistema\n";
    echo "✅ RELACIONES INTACTAS: Sin referencias rotas\n";
    echo "✅ DISEÑO VÁLIDO: Plantillas = Recetas con ejercicios existentes\n";
} else {
    echo "❌ PROBLEMA DETECTADO: Revisar estructura de datos\n";
}
