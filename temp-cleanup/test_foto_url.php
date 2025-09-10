<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Services\SociosApi;

// Crear instancia del servicio
$sociosApi = new SociosApi();

// Probar construcción de URL
$socioId = '43675';
$fotoUrl = $sociosApi->buildFotoUrl($socioId);

echo "=== TEST FOTO URL ===\n";
echo "Socio ID: " . $socioId . "\n";
echo "Foto URL: " . ($fotoUrl ?? 'NULL') . "\n";

// Verificar configuración
$config = config('services.socios');
echo "\n=== CONFIGURACIÓN ===\n";
echo "IMG_BASE: " . ($config['img_base'] ?? 'NULL') . "\n";
echo "BASE: " . ($config['base'] ?? 'NULL') . "\n";
echo "LOGIN: " . ($config['login'] ?? 'NULL') . "\n";
