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
        Schema::create('assignment_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_assignment_id')->constrained()->onDelete('cascade');
            $table->date('scheduled_date');
            $table->enum('status', ['pending', 'completed', 'skipped', 'cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->json('exercise_progress')->nullable(); // progreso por ejercicio
            $table->text('student_notes')->nullable();
            $table->text('professor_feedback')->nullable();
            $table->decimal('overall_rating', 2, 1)->nullable(); // 1.0 - 5.0
            $table->timestamps();
            
            // Índices para performance
            $table->index(['daily_assignment_id', 'scheduled_date'], 'idx_assignment_date');
            $table->index(['status', 'scheduled_date'], 'idx_status_date');
            $table->index(['scheduled_date'], 'idx_scheduled_date');
            
            // Constraint: Una fecha por asignación
            $table->unique(['daily_assignment_id', 'scheduled_date'], 'unique_assignment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_progress');
    }
};
