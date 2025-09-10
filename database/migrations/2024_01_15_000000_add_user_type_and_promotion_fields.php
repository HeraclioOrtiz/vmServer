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
            // Agregar columna DNI primero
            $table->string('dni', 8)->unique()->after('id');
            
            // Tipo de usuario: local o api
            $table->enum('user_type', ['local', 'api'])->default('api')->after('dni');
            
            // Campos para promoción
            $table->enum('promotion_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('user_type');
            $table->timestamp('promoted_at')->nullable()->after('promotion_status');
            
            // Campo phone para usuarios locales
            $table->string('phone')->nullable()->after('promoted_at');
            
            // Hacer email opcional (requerido solo para usuarios locales)
            $table->string('email')->nullable()->change();
            
            // Agregar índices para performance
            $table->index(['dni', 'user_type'], 'idx_dni_type');
            $table->index('promotion_status', 'idx_promotion_status');
            $table->index('user_type', 'idx_user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_dni_type');
            $table->dropIndex('idx_promotion_status');
            $table->dropIndex('idx_user_type');
            
            $table->dropColumn(['dni', 'user_type', 'promotion_status', 'promoted_at', 'phone']);
            
            // Restaurar email como requerido
            $table->string('email')->nullable(false)->change();
        });
    }
};
