<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Índices sugeridos para mejorar búsquedas por socio
            if (!Schema::hasColumn('users', 'socio_id')) {
                // Seguridad: si el esquema no está actualizado, no intentar indexar
                return;
            }
            $table->index('socio_id', 'idx_users_socio_id');

            if (Schema::hasColumn('users', 'barcode')) {
                $table->index('barcode', 'idx_users_barcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices si existen
            try { $table->dropIndex('idx_users_socio_id'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_users_barcode'); } catch (\Throwable $e) {}
        });
    }
};
