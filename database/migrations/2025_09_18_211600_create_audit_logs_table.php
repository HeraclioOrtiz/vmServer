<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Usuario que realizó la acción
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Información de la acción
            $table->string('action'); // login, create, update, delete, assign_role, etc.
            $table->string('resource_type'); // user, assignment, template, etc.
            $table->unsignedBigInteger('resource_id')->nullable();
            
            // Detalles de la acción
            $table->json('details')->nullable(); // Datos adicionales de la acción
            $table->json('old_values')->nullable(); // Valores anteriores (para updates)
            $table->json('new_values')->nullable(); // Valores nuevos (para updates)
            
            // Información de la sesión
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            
            // Categorización
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->string('category')->default('general'); // auth, user_management, gym, system
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index(['severity', 'created_at']);
            $table->index(['category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
