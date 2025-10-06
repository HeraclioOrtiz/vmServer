<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Tabla daily_assignments existe: " . (\Illuminate\Support\Facades\Schema::hasTable('daily_assignments') ? 'SÍ' : 'NO') . "\n";

if (\Illuminate\Support\Facades\Schema::hasTable('daily_assignments')) {
    $count = \Illuminate\Support\Facades\DB::table('daily_assignments')->count();
    echo "Registros en daily_assignments: {$count}\n";
}
