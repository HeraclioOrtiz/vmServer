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
        Schema::create('professor_student_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // admin
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['professor_id', 'status'], 'idx_professor_status');
            $table->index(['student_id', 'status'], 'idx_student_status');
            $table->index(['start_date', 'end_date'], 'idx_date_range');
            
            // Constraint: Un estudiante solo puede tener una asignación activa
            $table->unique(['student_id', 'status'], 'unique_active_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_student_assignments');
    }
};
