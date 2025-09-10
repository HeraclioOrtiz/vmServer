<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\User;
use App\Http\Resources\UserResource;

// Buscar usuario por DNI
$user = User::where('dni', '59964604')->first();

if ($user) {
    echo "=== DEBUG FOTO_URL ACCESS ===\n";
    echo "Direct access: " . ($user->foto_url ?? 'NULL') . "\n";
    echo "getAttribute: " . ($user->getAttribute('foto_url') ?? 'NULL') . "\n";
    echo "getOriginal: " . ($user->getOriginal('foto_url') ?? 'NULL') . "\n";
    echo "getRawOriginal: " . ($user->getRawOriginal('foto_url') ?? 'NULL') . "\n";
    
    echo "\n=== RESOURCE SERIALIZATION ===\n";
    $resource = new UserResource($user);
    $array = $resource->toArray(request());
    echo "Resource foto_url: " . ($array['foto_url'] ?? 'NULL') . "\n";
    
    echo "\n=== ALL ATTRIBUTES ===\n";
    $attributes = $user->getAttributes();
    foreach ($attributes as $key => $value) {
        if (strpos($key, 'foto') !== false) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
    }
} else {
    echo "Usuario no encontrado\n";
}
