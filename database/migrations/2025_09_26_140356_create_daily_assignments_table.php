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
        Schema::create('daily_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_student_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('daily_template_id')->constrained('gym_daily_templates')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // profesor
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('frequency'); // días de la semana [0,1,2,3,4,5,6]
            $table->text('professor_notes')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['professor_student_assignment_id', 'status'], 'idx_assignment_status');
            $table->index(['daily_template_id'], 'idx_template');
            $table->index(['start_date', 'end_date'], 'idx_assignment_dates');
            $table->index(['assigned_by'], 'idx_assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_assignments');
    }
};
