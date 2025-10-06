<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "👥 Villa Mitre Server - User Creator\n\n";

function createUser($userData) {
    try {
        // Verificar si el usuario ya existe
        $existingUser = User::where('dni', $userData['dni'])->first();
        if ($existingUser) {
            echo "  ⚠️ Usuario con DNI {$userData['dni']} ya existe: {$existingUser->name}\n";
            return $existingUser;
        }

        // Hashear password
        $userData['password'] = Hash::make($userData['password']);
        
        // Crear usuario
        $user = User::create($userData);
        
        echo "  ✅ Usuario creado: {$user->name} (DNI: {$user->dni})\n";
        return $user;
        
    } catch (Exception $e) {
        echo "  ❌ Error creando usuario: " . $e->getMessage() . "\n";
        return null;
    }
}

echo "🔧 Creando usuarios del sistema...\n\n";

// 1. Usuario Administrador
echo "1️⃣ Creando Usuario Administrador:\n";
$admin = createUser([
    'name' => 'Admin User',
    'email' => 'admin@villamitre.com',
    'dni' => '11111111',
    'password' => 'admin123',
    'user_type' => 'local',
    'is_admin' => true,
    'is_professor' => false,
    'permissions' => [
        'user_management',      // Gestión de usuarios
        'gym_admin',           // Administración del gimnasio
        'system_settings',     // Configuración del sistema
        'reports_access',      // Acceso a reportes
        'audit_logs',          // Logs de auditoría
        'super_admin'          // Permisos de super administrador
    ],
    'account_status' => 'active',
]);

// 2. Usuario Profesor
echo "\n2️⃣ Creando Usuario Profesor:\n";
$profesor = createUser([
    'name' => 'Profesor Juan Pérez',
    'email' => 'profesor@villamitre.com',
    'dni' => '22222222',
    'password' => 'profesor123',
    'user_type' => 'local',
    'is_admin' => false,
    'is_professor' => true,
    'professor_since' => now(),
    'permissions' => [
        'gym_admin',           // Acceso al panel del gimnasio
        'create_templates',    // Crear plantillas
        'assign_routines',     // Asignar rutinas a estudiantes
    ],
    'account_status' => 'active',
]);

// 3. Usuario Estudiante
echo "\n3️⃣ Creando Usuario Estudiante:\n";
$estudiante = createUser([
    'name' => 'Estudiante María García',
    'email' => 'estudiante@villamitre.com',
    'dni' => '33333333',
    'password' => 'estudiante123',
    'user_type' => 'local',
    'is_admin' => false,
    'is_professor' => false,
    'permissions' => [],
    'account_status' => 'active',
]);

// 4. Usuario de Prueba (mantener compatibilidad)
echo "\n4️⃣ Verificando Usuario de Prueba:\n";
$test = createUser([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'dni' => '12345678',
    'password' => 'password123',
    'user_type' => 'local',
    'is_professor' => true,
    'account_status' => 'active',
]);

echo "\n📋 Resumen de Usuarios Creados:\n";
echo "┌─────────────────────────────────────────────────────────────────┐\n";
echo "│ DNI        │ Nombre                 │ Rol           │ Accesos      │\n";
echo "├─────────────────────────────────────────────────────────────────┤\n";
echo "│ 11111111   │ Admin User             │ Administrador │ TODO         │\n";
echo "│ 22222222   │ Profesor Juan Pérez    │ Profesor      │ Gimnasio     │\n";
echo "│ 33333333   │ Estudiante María       │ Estudiante    │ API Móvil    │\n";
echo "│ 12345678   │ Test User              │ Profesor      │ Gimnasio     │\n";
echo "└─────────────────────────────────────────────────────────────────┘\n";

echo "\n🔐 Credenciales de Acceso:\n\n";

echo "👨‍💼 ADMINISTRADOR (Acceso completo):\n";
echo "  DNI: 11111111\n";
echo "  Password: admin123\n";
echo "  Acceso: /api/admin/* (usuarios, profesores, auditoría, gimnasio)\n\n";

echo "👨‍🏫 PROFESOR (Solo gimnasio):\n";
echo "  DNI: 22222222\n";
echo "  Password: profesor123\n";
echo "  Acceso: /api/admin/gym/* (ejercicios, plantillas, asignaciones)\n\n";

echo "👨‍🎓 ESTUDIANTE (Solo móvil):\n";
echo "  DNI: 33333333\n";
echo "  Password: estudiante123\n";
echo "  Acceso: /api/gym/* (rutinas propias)\n\n";

echo "🧪 TEST USER (Compatibilidad):\n";
echo "  DNI: 12345678\n";
echo "  Password: password123\n";
echo "  Acceso: /api/admin/gym/* (para tests)\n\n";

echo "🚀 Ejemplos de Login:\n\n";

echo "# Login como Administrador\n";
echo "curl -X POST http://localhost:8000/api/auth/login \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"dni\":\"11111111\",\"password\":\"admin123\"}'\n\n";

echo "# Login como Profesor\n";
echo "curl -X POST http://localhost:8000/api/auth/login \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"dni\":\"22222222\",\"password\":\"profesor123\"}'\n\n";

echo "# Login como Estudiante\n";
echo "curl -X POST http://localhost:8000/api/auth/login \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"dni\":\"33333333\",\"password\":\"estudiante123\"}'\n\n";

echo "✅ Usuarios creados exitosamente!\n";
echo "🔧 Ejecuta 'php artisan migrate:fresh --seed' para recrear la base de datos con estos usuarios.\n\n";
