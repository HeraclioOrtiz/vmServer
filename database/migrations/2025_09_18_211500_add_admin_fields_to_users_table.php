<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos de administración
            $table->boolean('is_admin')->default(false)->after('is_professor');
            $table->json('permissions')->nullable()->after('is_admin');
            $table->text('admin_notes')->nullable()->after('permissions');
            
            // Estado de la cuenta
            $table->enum('account_status', ['active', 'suspended', 'pending'])->default('active')->after('admin_notes');
            
            // Configuración de acceso
            $table->timestamp('professor_since')->nullable()->after('account_status');
            $table->integer('session_timeout')->default(480)->after('professor_since'); // minutos
            $table->json('allowed_ips')->nullable()->after('session_timeout');
            
            // Índices para optimizar consultas
            $table->index('is_admin');
            $table->index('account_status');
            $table->index(['is_professor', 'account_status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_is_admin_index']);
            $table->dropIndex(['users_account_status_index']);
            $table->dropIndex(['users_is_professor_account_status_index']);
            
            $table->dropColumn([
                'is_admin',
                'permissions',
                'admin_notes',
                'account_status',
                'professor_since',
                'session_timeout',
                'allowed_ips'
            ]);
        });
    }
};
