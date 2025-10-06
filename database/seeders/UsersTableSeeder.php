<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla de usuarios (sin truncate por foreign keys)
        \Illuminate\Support\Facades\DB::table('personal_access_tokens')->delete();
        User::query()->delete();
        
        echo "🌱 Creando usuarios de prueba...\n\n";
        
        // 1. Usuario ADMIN
        $admin = User::create([
            'name' => 'Admin Villa Mitre',
            'email' => 'admin@villamitre.com',
            'dni' => '11111111',
            'password' => Hash::make('admin123'),
            'user_type' => 'local',
            'is_admin' => true,
            'account_status' => 'active',
        ]);
        echo "✅ 1. Admin creado: {$admin->name} (DNI: {$admin->dni})\n";
        
        // 2. Usuario PROFESOR
        $professor = User::create([
            'name' => 'Profesor Juan Pérez',
            'email' => 'profesor@villamitre.com',
            'dni' => '22222222',
            'password' => Hash::make('profesor123'),
            'user_type' => 'api',
            'is_professor' => true,
            'professor_since' => now(),
            'account_status' => 'active',
        ]);
        echo "✅ 2. Profesor creado: {$professor->name} (DNI: {$professor->dni})\n";
        
        // 3. Usuario LOCAL (socio regular sin acceso API)
        $local = User::create([
            'name' => 'Usuario Local',
            'email' => 'local@villamitre.com',
            'dni' => '33333333',
            'password' => Hash::make('local123'),
            'user_type' => 'local',
            'is_professor' => false,
            'is_admin' => false,
            'account_status' => 'active',
        ]);
        echo "✅ 3. Usuario Local creado: {$local->name} (DNI: {$local->dni})\n";
        
        // 4. Usuario API (sin acceso a gimnasio)
        $api = User::create([
            'name' => 'Usuario API Regular',
            'email' => 'api@villamitre.com',
            'dni' => '44444444',
            'password' => Hash::make('api123'),
            'user_type' => 'api',
            'student_gym' => false,
            'is_professor' => false,
            'is_admin' => false,
            'account_status' => 'active',
        ]);
        echo "✅ 4. Usuario API Regular creado: {$api->name} (DNI: {$api->dni}) - SIN acceso gimnasio\n";
        
        // 5. Usuario API CON ACCESO AL GIMNASIO (María García)
        $gymStudent = User::create([
            'name' => 'María García',
            'email' => 'maria.garcia@villamitre.com',
            'dni' => '55555555',
            'password' => Hash::make('estudiante123'),
            'user_type' => 'api',
            'student_gym' => true,
            'student_gym_since' => now(),
            'is_professor' => false,
            'is_admin' => false,
            'account_status' => 'active',
        ]);
        echo "✅ 5. Estudiante Gimnasio creado: {$gymStudent->name} (DNI: {$gymStudent->dni}) - CON acceso gimnasio\n";
        
        echo "\n🎉 Total de usuarios creados: 5\n";
        echo "\n📋 RESUMEN:\n";
        echo "  1. Admin: admin@villamitre.com / admin123\n";
        echo "  2. Profesor: profesor@villamitre.com / profesor123\n";
        echo "  3. Usuario Local: local@villamitre.com / local123\n";
        echo "  4. API Regular: api@villamitre.com / api123\n";
        echo "  5. API Gimnasio: maria.garcia@villamitre.com / estudiante123\n";
    }
}
