<?php

echo "🏋️ === TESTING CAMPOS DE PESO EN API === 🏋️\n\n";

$baseUrl = 'https://villamitre.loca.lt';

// Login primero
echo "🔑 Haciendo login...\n";
$loginData = json_encode([
    'dni' => '55555555',
    'password' => 'maria123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo "❌ Error en login: {$httpCode}\n";
    exit(1);
}

$loginResult = json_decode($response, true);
$token = $loginResult['data']['token'];
echo "✅ Login exitoso\n\n";

// Obtener plantillas
echo "📋 Obteniendo plantillas...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/student/my-templates');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo "❌ Error obteniendo plantillas: {$httpCode}\n";
    exit(1);
}

$templatesResult = json_decode($response, true);
$templates = $templatesResult['data']['templates'];

if (empty($templates)) {
    echo "❌ No hay plantillas asignadas\n";
    exit(1);
}

$templateId = $templates[0]['id'];
echo "✅ Plantilla encontrada: ID {$templateId}\n\n";

// Obtener detalles de plantilla
echo "🔍 Obteniendo detalles de plantilla...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/student/template/' . $templateId . '/details');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo "❌ Error obteniendo detalles: {$httpCode}\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
    exit(1);
}

$detailsResult = json_decode($response, true);
echo "✅ Detalles obtenidos\n\n";

// Analizar estructura de sets
echo str_repeat("=", 70) . "\n";
echo "ANÁLISIS DE ESTRUCTURA DE SETS\n";
echo str_repeat("=", 70) . "\n\n";

$exercises = $detailsResult['data']['exercises'];

foreach ($exercises as $exerciseIndex => $exerciseData) {
    $exercise = $exerciseData['exercise'];
    $sets = $exerciseData['sets'];
    
    echo "🏋️ EJERCICIO #" . ($exerciseIndex + 1) . ": {$exercise['name']}\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($sets as $setIndex => $set) {
        echo "📊 SET #" . ($setIndex + 1) . ":\n";
        
        // Campos existentes
        echo "   🔢 Reps: {$set['reps_min']}-{$set['reps_max']}\n";
        echo "   ⏱️ Descanso: {$set['rest_seconds']}s\n";
        echo "   💪 RPE: {$set['rpe_target']}\n";
        
        // Verificar campos de peso
        $hasWeightMin = isset($set['weight_min']);
        $hasWeightMax = isset($set['weight_max']);
        $hasWeightTarget = isset($set['weight_target']);
        
        echo "   🏋️ Peso mín: " . ($hasWeightMin ? $set['weight_min'] . 'kg' : '❌ NO PRESENTE') . "\n";
        echo "   🏋️ Peso máx: " . ($hasWeightMax ? $set['weight_max'] . 'kg' : '❌ NO PRESENTE') . "\n";
        echo "   🎯 Peso objetivo: " . ($hasWeightTarget ? $set['weight_target'] . 'kg' : '❌ NO PRESENTE') . "\n";
        
        if ($set['notes']) {
            echo "   📝 Notas: {$set['notes']}\n";
        }
        
        echo "\n";
    }
    
    echo "\n";
}

// Resumen de campos de peso
echo str_repeat("=", 70) . "\n";
echo "RESUMEN DE CAMPOS DE PESO\n";
echo str_repeat("=", 70) . "\n\n";

$totalSets = 0;
$setsWithWeightMin = 0;
$setsWithWeightMax = 0;
$setsWithWeightTarget = 0;

foreach ($exercises as $exerciseData) {
    foreach ($exerciseData['sets'] as $set) {
        $totalSets++;
        if (isset($set['weight_min']) && $set['weight_min'] !== null) $setsWithWeightMin++;
        if (isset($set['weight_max']) && $set['weight_max'] !== null) $setsWithWeightMax++;
        if (isset($set['weight_target']) && $set['weight_target'] !== null) $setsWithWeightTarget++;
    }
}

echo "📊 ESTADÍSTICAS:\n";
echo "• Total sets: {$totalSets}\n";
echo "• Sets con weight_min: {$setsWithWeightMin} (" . round($setsWithWeightMin/$totalSets*100, 1) . "%)\n";
echo "• Sets con weight_max: {$setsWithWeightMax} (" . round($setsWithWeightMax/$totalSets*100, 1) . "%)\n";
echo "• Sets con weight_target: {$setsWithWeightTarget} (" . round($setsWithWeightTarget/$totalSets*100, 1) . "%)\n\n";

if ($setsWithWeightTarget == $totalSets) {
    echo "✅ ÉXITO: Todos los sets tienen campos de peso\n";
} else {
    echo "⚠️ PROBLEMA: Algunos sets no tienen campos de peso\n";
}

echo "\n🎯 ESTRUCTURA ESPERADA PARA APP MÓVIL:\n";
echo "{\n";
echo "  \"sets\": [\n";
echo "    {\n";
echo "      \"reps_min\": 8,\n";
echo "      \"reps_max\": 10,\n";
echo "      \"weight_min\": 36.0,\n";
echo "      \"weight_max\": 72.0,\n";
echo "      \"weight_target\": 54.0,\n";
echo "      \"rest_seconds\": 120,\n";
echo "      \"rpe_target\": 7.5\n";
echo "    }\n";
echo "  ]\n";
echo "}\n\n";

echo "🚀 ESTADO: " . ($setsWithWeightTarget == $totalSets ? "LISTO PARA APP MÓVIL" : "NECESITA CORRECCIÓN") . "\n";
