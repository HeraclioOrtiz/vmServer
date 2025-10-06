<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧹 === LIMPIEZA DE PLANTILLAS DIARIAS === 🧹\n\n";

// PASO 1: Eliminar plantillas "(Copia)" rotas
echo "PASO 1: Eliminando plantillas '(Copia)' rotas...\n";

$brokenCopies = \App\Models\Gym\DailyTemplate::where('title', ' (Copia)')
    ->whereNull('goal')
    ->whereNull('level')
    ->whereNull('estimated_duration_min')
    ->get();

echo "📊 Plantillas rotas encontradas: " . $brokenCopies->count() . "\n";

foreach ($brokenCopies as $template) {
    echo "  - Eliminando ID {$template->id}: '{$template->title}'\n";
    $template->delete();
}

echo "✅ Plantillas rotas eliminadas\n\n";

// PASO 2: Eliminar plantillas de testing E2E
echo "PASO 2: Eliminando plantillas de testing E2E...\n";

$testingTemplates = \App\Models\Gym\DailyTemplate::where(function($query) {
    $query->where('title', 'like', '%E2E%')
          ->orWhere('title', 'like', '%Test%')
          ->orWhere('title', 'like', '%test%')
          ->orWhere('title', 'like', '%Rutina Test%')
          ->orWhere('title', 'like', '%Plantilla Test%');
})->where('is_preset', false)->get();

echo "📊 Plantillas de testing encontradas: " . $testingTemplates->count() . "\n";

foreach ($testingTemplates as $template) {
    echo "  - Eliminando ID {$template->id}: '{$template->title}'\n";
    $template->delete();
}

echo "✅ Plantillas de testing eliminadas\n\n";

// PASO 3: Reportar plantillas sin ejercicios
echo "PASO 3: Reportando plantillas sin ejercicios...\n";

$emptyTemplates = \App\Models\Gym\DailyTemplate::doesntHave('exercises')->get();

echo "📊 Plantillas sin ejercicios: " . $emptyTemplates->count() . "\n";

foreach ($emptyTemplates->take(10) as $template) {
    echo "  - ID {$template->id}: '{$template->title}' (preset: " . ($template->is_preset ? 'SÍ' : 'NO') . ")\n";
}

if ($emptyTemplates->count() > 10) {
    echo "  ... y " . ($emptyTemplates->count() - 10) . " más\n";
}

echo "⚠️  Plantillas sin ejercicios mantenidas (pueden ser borradores)\n\n";

// PASO 4: Estadísticas finales
echo "PASO 4: Estadísticas después de limpieza...\n";

$totalTemplates = \App\Models\Gym\DailyTemplate::count();
$presetTemplates = \App\Models\Gym\DailyTemplate::where('is_preset', true)->count();
$userTemplates = \App\Models\Gym\DailyTemplate::where('is_preset', false)->count();
$templatesWithExercises = \App\Models\Gym\DailyTemplate::has('exercises')->count();

echo "📊 ESTADÍSTICAS FINALES:\n";
echo "  - Total plantillas: {$totalTemplates}\n";
echo "  - Plantillas preset: {$presetTemplates}\n";
echo "  - Plantillas de usuario: {$userTemplates}\n";
echo "  - Plantillas con ejercicios: {$templatesWithExercises}\n";
echo "  - Plantillas vacías: " . ($totalTemplates - $templatesWithExercises) . "\n";

echo "\n🎉 LIMPIEZA COMPLETADA\n";
echo "✅ Base de datos más limpia y organizada\n";
echo "✅ Plantillas basura eliminadas\n";
echo "✅ Solo datos útiles mantenidos\n";
