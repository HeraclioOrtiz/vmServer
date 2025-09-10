<?php

require_once 'bootstrap/app.php';

use App\Models\User;

// Buscar usuario por DNI
$user = User::where('dni', '59964604')->first();

if ($user) {
    echo "=== DATOS DEL USUARIO ===\n";
    echo "ID: " . $user->id . "\n";
    echo "DNI: " . $user->dni . "\n";
    echo "Name: " . $user->name . "\n";
    echo "User Type: " . $user->user_type->value . "\n";
    echo "Socio ID: " . ($user->socio_id ?? 'NULL') . "\n";
    echo "Foto URL: " . ($user->foto_url ?? 'NULL') . "\n";
    echo "Semaforo: " . ($user->semaforo ?? 'NULL') . "\n";
    echo "Saldo: " . ($user->saldo ?? 'NULL') . "\n";
    echo "Deuda: " . ($user->deuda ?? 'NULL') . "\n";
    echo "Barcode: " . ($user->barcode ?? 'NULL') . "\n";
    echo "Nacionalidad: " . ($user->nacionalidad ?? 'NULL') . "\n";
    echo "Nacimiento: " . ($user->nacimiento ?? 'NULL') . "\n";
    echo "Updated At: " . $user->updated_at . "\n";
} else {
    echo "Usuario no encontrado\n";
}
