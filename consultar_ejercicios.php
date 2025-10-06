<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🏋️ === EJERCICIOS EN LA BASE DE DATOS === 🏋️\n\n";

$totalEjercicios = App\Models\Gym\Exercise::count();
echo "📊 Total de ejercicios: {$totalEjercicios}\n\n";

echo "📋 PRIMEROS 20 EJERCICIOS:\n";
echo str_repeat("=", 80) . "\n";

App\Models\Gym\Exercise::take(20)->get()->each(function($ejercicio) {
    echo "ID: {$ejercicio->id}\n";
    echo "Nombre: {$ejercicio->name}\n";
    echo "Descripción: " . substr($ejercicio->description ?? 'Sin descripción', 0, 60) . "...\n";
    echo "Grupo Muscular: {$ejercicio->muscle_group}\n";
    echo "Equipamiento: {$ejercicio->equipment}\n";
    echo "Dificultad: {$ejercicio->difficulty}\n";
    echo "Creado: {$ejercicio->created_at}\n";
    echo str_repeat("-", 50) . "\n";
});

echo "\n📊 RESUMEN POR GRUPO MUSCULAR:\n";
echo str_repeat("=", 50) . "\n";

$grupos = App\Models\Gym\Exercise::selectRaw('muscle_group, COUNT(*) as total')
    ->groupBy('muscle_group')
    ->orderBy('total', 'desc')
    ->get();

foreach($grupos as $grupo) {
    echo "{$grupo->muscle_group}: {$grupo->total} ejercicios\n";
}

echo "\n📊 RESUMEN POR DIFICULTAD:\n";
echo str_repeat("=", 50) . "\n";

$dificultades = App\Models\Gym\Exercise::selectRaw('difficulty, COUNT(*) as total')
    ->groupBy('difficulty')
    ->orderBy('total', 'desc')
    ->get();

foreach($dificultades as $dificultad) {
    echo "{$dificultad->difficulty}: {$dificultad->total} ejercicios\n";
}

echo "\n✅ Consulta completada\n";
