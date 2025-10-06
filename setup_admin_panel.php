<?php

echo "=== SETUP ADMIN PANEL VILLA MITRE ===\n\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('artisan')) {
    echo "❌ ERROR: Ejecutar desde el directorio raíz del proyecto Laravel\n";
    exit(1);
}

echo "🔧 Iniciando configuración del Admin Panel...\n\n";

// 1. Verificar conexión a base de datos
echo "1️⃣ VERIFICANDO CONEXIÓN A BASE DE DATOS...\n";
$output = shell_exec('php artisan migrate:status 2>&1');
if (strpos($output, 'could not be made') !== false || strpos($output, 'refused') !== false) {
    echo "❌ ERROR: No se puede conectar a la base de datos\n";
    echo "   Asegúrate de que MySQL esté ejecutándose\n";
    echo "   Verifica la configuración en .env\n\n";
    echo "Configuración actual:\n";
    echo "DB_HOST=" . env('DB_HOST', 'localhost') . "\n";
    echo "DB_PORT=" . env('DB_PORT', '3306') . "\n";
    echo "DB_DATABASE=" . env('DB_DATABASE', 'villa_mitre_gym') . "\n";
    echo "DB_USERNAME=" . env('DB_USERNAME', 'root') . "\n\n";
    exit(1);
} else {
    echo "✅ Conexión a base de datos OK\n\n";
}

// 2. Ejecutar migraciones
echo "2️⃣ EJECUTANDO MIGRACIONES...\n";
$output = shell_exec('php artisan migrate:fresh --force 2>&1');
echo $output;
if (strpos($output, 'Migrated:') !== false) {
    echo "✅ Migraciones ejecutadas correctamente\n\n";
} else {
    echo "❌ ERROR en migraciones\n\n";
    exit(1);
}

// 3. Ejecutar seeders
echo "3️⃣ CREANDO USUARIOS DE PRUEBA...\n";
$output = shell_exec('php artisan db:seed --force 2>&1');
echo $output;
if (strpos($output, 'seeded successfully') !== false || strpos($output, 'Seeded:') !== false) {
    echo "✅ Usuarios creados correctamente\n\n";
} else {
    echo "⚠️ Posible error en seeders, pero continuando...\n\n";
}

// 4. Limpiar cache
echo "4️⃣ LIMPIANDO CACHE...\n";
shell_exec('php artisan cache:clear');
shell_exec('php artisan route:clear');
shell_exec('php artisan config:clear');
echo "✅ Cache limpiado\n\n";

// 5. Verificar rutas
echo "5️⃣ VERIFICANDO RUTAS ADMIN...\n";
$output = shell_exec('php artisan route:list --name=admin 2>&1');
$routeCount = substr_count($output, 'admin.');
if ($routeCount > 0) {
    echo "✅ {$routeCount} rutas admin registradas\n\n";
} else {
    echo "❌ ERROR: No se encontraron rutas admin\n\n";
    exit(1);
}

// 6. Mostrar credenciales
echo "6️⃣ CREDENCIALES DE ACCESO:\n";
echo "┌─────────────────────────────────────────────────────────┐\n";
echo "│                    👨‍💼 ADMINISTRADOR                      │\n";
echo "│ Email: admin@villamitre.com                             │\n";
echo "│ DNI: 11111111                                           │\n";
echo "│ Password: admin123                                      │\n";
echo "│ Acceso: Panel Admin + Panel Gimnasio                   │\n";
echo "├─────────────────────────────────────────────────────────┤\n";
echo "│                     👨‍🏫 PROFESOR                          │\n";
echo "│ Email: profesor@villamitre.com                          │\n";
echo "│ DNI: 22222222                                           │\n";
echo "│ Password: profesor123                                   │\n";
echo "│ Acceso: Solo Panel Gimnasio                            │\n";
echo "└─────────────────────────────────────────────────────────┘\n\n";

// 7. Endpoints de prueba
echo "7️⃣ ENDPOINTS PARA PROBAR:\n";
echo "🔐 Login:\n";
echo "   POST /api/auth/login\n";
echo "   Body: {\"dni\": \"11111111\", \"password\": \"admin123\"}\n\n";

echo "👥 Panel Admin (requiere token admin):\n";
echo "   GET  /api/admin/users\n";
echo "   GET  /api/admin/professors\n";
echo "   GET  /api/admin/audit\n\n";

echo "🏋️ Panel Gimnasio (requiere token profesor/admin):\n";
echo "   GET  /api/admin/gym/exercises\n";
echo "   GET  /api/admin/gym/daily-templates\n";
echo "   GET  /api/admin/gym/weekly-templates\n\n";

// 8. Comandos útiles
echo "8️⃣ COMANDOS ÚTILES:\n";
echo "📊 Ver usuarios: php artisan tinker → User::all(['name','email','dni','is_admin','is_professor'])\n";
echo "🛣️ Ver rutas: php artisan route:list --name=admin\n";
echo "🧪 Ejecutar tests: php artisan test tests/Feature/AdminPanelTest.php\n";
echo "🚀 Servidor: php artisan serve\n\n";

echo "🎉 SETUP COMPLETADO!\n";
echo "El Admin Panel está listo para usar.\n";
echo "Inicia el servidor con: php artisan serve\n";
echo "Luego prueba el login en: http://localhost:8000/api/auth/login\n\n";
