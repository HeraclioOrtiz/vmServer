<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('gym_exercises');
echo "Columnas en gym_exercises:\n";
foreach($columns as $col) {
    echo "- {$col}\n";
}

echo "\n¿Existe exercise_type? " . (in_array('exercise_type', $columns) ? 'SÍ' : 'NO') . "\n";
