<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed inicial para producción
     * 
     * Crea usuarios mínimos necesarios para comenzar:
     * - Administrador principal
     * - Profesor de prueba
     * - Estudiante de prueba
     * 
     * Ejecutar con: php artisan db:seed --class=ProductionSeeder --force
     */
    public function run(): void
    {
        echo "🚀 Iniciando seeder de producción...\n\n";

        // 1. ADMINISTRADOR PRINCIPAL
        $admin = User::create([
            'name' => 'Admin Villa Mitre',
            'email' => 'admin@villamitre.com',
            'dni' => '11111111',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'is_super_admin' => true,
            'is_professor' => false,
            'is_active' => true,
            'phone' => '1234567890',
            'address' => 'Villa Mitre',
            'birth_date' => '1980-01-01',
        ]);
        echo "✅ Admin creado: {$admin->name} (ID: {$admin->id})\n";
        echo "   📧 Email: {$admin->email}\n";
        echo "   🆔 DNI: {$admin->dni}\n";
        echo "   🔑 Password: admin123\n\n";

        // 2. PROFESOR DE PRUEBA
        $profesor = User::create([
            'name' => 'Profesor Juan Pérez',
            'email' => 'profesor@villamitre.com',
            'dni' => '22222222',
            'password' => Hash::make('profesor123'),
            'is_admin' => false,
            'is_super_admin' => false,
            'is_professor' => true,
            'is_active' => true,
            'phone' => '1234567891',
            'address' => 'Villa Mitre',
            'birth_date' => '1985-01-01',
        ]);
        echo "✅ Profesor creado: {$profesor->name} (ID: {$profesor->id})\n";
        echo "   📧 Email: {$profesor->email}\n";
        echo "   🆔 DNI: {$profesor->dni}\n";
        echo "   🔑 Password: profesor123\n\n";

        // 3. ESTUDIANTE DE PRUEBA
        $estudiante = User::create([
            'name' => 'María García',
            'email' => 'maria.garcia@villamitre.com',
            'dni' => '55555555',
            'password' => Hash::make('maria123'),
            'is_admin' => false,
            'is_super_admin' => false,
            'is_professor' => false,
            'is_active' => true,
            'phone' => '1234567892',
            'address' => 'Villa Mitre',
            'birth_date' => '1995-01-01',
        ]);
        echo "✅ Estudiante creado: {$estudiante->name} (ID: {$estudiante->id})\n";
        echo "   📧 Email: {$estudiante->email}\n";
        echo "   🆔 DNI: {$estudiante->dni}\n";
        echo "   🔑 Password: maria123\n\n";

        echo "🎉 Seeder de producción completado!\n\n";
        echo "📋 CREDENCIALES INICIALES:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "👑 ADMINISTRADOR:\n";
        echo "   Email: admin@villamitre.com\n";
        echo "   DNI: 11111111\n";
        echo "   Password: admin123\n\n";
        echo "👨‍🏫 PROFESOR:\n";
        echo "   Email: profesor@villamitre.com\n";
        echo "   DNI: 22222222\n";
        echo "   Password: profesor123\n\n";
        echo "👤 ESTUDIANTE DE PRUEBA:\n";
        echo "   Email: maria.garcia@villamitre.com\n";
        echo "   DNI: 55555555\n";
        echo "   Password: maria123\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "⚠️  IMPORTANTE: Cambiar las contraseñas después del primer login!\n";
    }
}
