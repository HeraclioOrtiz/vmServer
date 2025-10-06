<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$maria = \App\Models\User::where('email', 'maria.garcia@villamitre.com')->first();

if ($maria) {
    echo "👤 Usuario: {$maria->name}\n";
    echo "📧 Email: {$maria->email}\n";
    echo "🆔 DNI: {$maria->dni}\n";
    echo "🔑 Password hash: " . substr($maria->password, 0, 20) . "...\n";
} else {
    echo "❌ Usuario no encontrado\n";
}
