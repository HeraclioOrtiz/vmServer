<?php

echo "📚 Villa Mitre Server - Documentation Validator\n\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('docs')) {
    echo "❌ Error: Ejecutar desde el directorio raíz del proyecto\n";
    exit(1);
}

echo "🔍 Validando estructura de documentación...\n\n";

// Estructura esperada
$expectedStructure = [
    'docs/README.md' => 'Índice principal de documentación',
    'docs/CHANGELOG.md' => 'Historial de cambios',
    'docs/DOCUMENTATION-SUMMARY.md' => 'Resumen de documentación',
    'docs/INDEX.md' => 'Índice alternativo',
    
    // API
    'docs/api/API-DOCUMENTATION.md' => 'Documentación completa de API',
    'docs/api/mobile-contracts.md' => 'Contratos de API móvil',
    
    // Arquitectura
    'docs/architecture/SERVICES-ARCHITECTURE.md' => 'Arquitectura de servicios',
    
    // Gimnasio
    'docs/gym/GYM-DOCUMENTATION.md' => 'Sistema completo del gimnasio',
    'docs/gym/GYM-BUSINESS-RULES.md' => 'Reglas de negocio del gimnasio',
    'docs/gym/ADMIN-PANEL-GUIDE.md' => 'Guía del panel de profesores',
    'docs/gym/MOBILE-API-GUIDE.md' => 'API móvil para estudiantes',
    
    // Testing
    'docs/testing/TESTING-GUIDE-MAIN.md' => 'Guía principal de testing',
    
    // Admin Panel
    'docs/admin-panel/GYM-PANEL-SPECS.md' => 'Especificaciones del panel de gimnasio',
];

$results = [
    'found' => 0,
    'missing' => 0,
    'errors' => []
];

echo "1️⃣ Verificando archivos principales:\n";
foreach ($expectedStructure as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "  ✅ $file ($size bytes) - $description\n";
        $results['found']++;
    } else {
        echo "  ❌ $file - FALTANTE - $description\n";
        $results['missing']++;
        $results['errors'][] = "Archivo faltante: $file";
    }
}

echo "\n2️⃣ Verificando enlaces en README principal:\n";
$readmeContent = file_get_contents('README.md');
$docLinks = [
    'docs/api/API-DOCUMENTATION.md',
    'docs/architecture/SERVICES-ARCHITECTURE.md', 
    'docs/testing/TESTING-GUIDE-MAIN.md',
    'docs/gym/GYM-DOCUMENTATION.md',
    'docs/gym/GYM-BUSINESS-RULES.md'
];

foreach ($docLinks as $link) {
    if (strpos($readmeContent, $link) !== false) {
        echo "  ✅ Enlace encontrado: $link\n";
    } else {
        echo "  ⚠️ Enlace no encontrado en README: $link\n";
        $results['errors'][] = "Enlace faltante en README: $link";
    }
}

echo "\n3️⃣ Verificando enlaces en docs/README.md:\n";
$docsReadmeContent = file_get_contents('docs/README.md');
$internalLinks = [
    'api/API-DOCUMENTATION.md',
    'architecture/SERVICES-ARCHITECTURE.md',
    'testing/TESTING-GUIDE-MAIN.md',
    'gym/GYM-DOCUMENTATION.md',
    'gym/GYM-BUSINESS-RULES.md'
];

foreach ($internalLinks as $link) {
    if (strpos($docsReadmeContent, $link) !== false) {
        echo "  ✅ Enlace interno encontrado: $link\n";
    } else {
        echo "  ⚠️ Enlace interno no encontrado: $link\n";
        $results['errors'][] = "Enlace interno faltante: $link";
    }
}

echo "\n4️⃣ Verificando archivos duplicados:\n";
$duplicateChecks = [
    'TESTING-GUIDE' => ['docs/testing/TESTING-GUIDE-MAIN.md', 'docs/testing/TESTING-GUIDE.md'],
];

foreach ($duplicateChecks as $name => $files) {
    echo "  🔍 Verificando duplicados de $name:\n";
    foreach ($files as $file) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "    - $file ($size bytes)\n";
        }
    }
}

echo "\n5️⃣ Verificando estructura de carpetas:\n";
$requiredDirs = [
    'docs/api',
    'docs/architecture', 
    'docs/gym',
    'docs/testing',
    'docs/admin-panel'
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        $fileCount = count(glob($dir . '/*.md'));
        echo "  ✅ $dir ($fileCount archivos .md)\n";
    } else {
        echo "  ❌ $dir - FALTANTE\n";
        $results['errors'][] = "Directorio faltante: $dir";
    }
}

echo "\n6️⃣ Verificando coherencia de contenido:\n";

// Verificar que GYM-DOCUMENTATION.md tiene contenido coherente
if (file_exists('docs/gym/GYM-DOCUMENTATION.md')) {
    $gymContent = file_get_contents('docs/gym/GYM-DOCUMENTATION.md');
    
    $gymChecks = [
        'ExerciseController' => 'Referencia al controlador de ejercicios',
        'WeeklyAssignmentService' => 'Referencia al servicio de asignaciones',
        '/api/admin/gym/exercises' => 'Endpoint de ejercicios',
        '/api/gym/my-week' => 'Endpoint móvil de rutina semanal'
    ];
    
    foreach ($gymChecks as $check => $description) {
        if (strpos($gymContent, $check) !== false) {
            echo "  ✅ $description encontrada\n";
        } else {
            echo "  ⚠️ $description no encontrada\n";
        }
    }
}

echo "\n📊 Resumen de Validación:\n";
echo "  ✅ Archivos encontrados: {$results['found']}\n";
echo "  ❌ Archivos faltantes: {$results['missing']}\n";
echo "  ⚠️ Errores detectados: " . count($results['errors']) . "\n";

if (!empty($results['errors'])) {
    echo "\n🚨 Errores Detectados:\n";
    foreach ($results['errors'] as $error) {
        echo "  - $error\n";
    }
}

echo "\n📋 Recomendaciones:\n";
echo "  1. Verificar que todos los enlaces funcionen correctamente\n";
echo "  2. Revisar archivos duplicados y consolidar si es necesario\n";
echo "  3. Mantener coherencia entre documentación e implementación\n";
echo "  4. Actualizar enlaces cuando se muevan archivos\n";

if (count($results['errors']) === 0) {
    echo "\n🎉 Validación completada exitosamente!\n";
    echo "📚 La documentación está bien organizada y coherente.\n";
    exit(0);
} else {
    echo "\n⚠️ Se encontraron algunos problemas que requieren atención.\n";
    exit(1);
}
