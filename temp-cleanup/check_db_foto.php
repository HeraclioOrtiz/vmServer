<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// Consultar directamente la base de datos
$user = DB::table('users')->where('dni', '59964604')->first();

if ($user) {
    echo "=== DATOS EN BASE DE DATOS ===\n";
    echo "ID: " . $user->id . "\n";
    echo "DNI: " . $user->dni . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Socio ID: " . ($user->socio_id ?? 'NULL') . "\n";
    echo "Foto URL: " . ($user->foto_url ?? 'NULL') . "\n";
    echo "Updated At: " . $user->updated_at . "\n";
} else {
    echo "Usuario no encontrado en BD\n";
}
