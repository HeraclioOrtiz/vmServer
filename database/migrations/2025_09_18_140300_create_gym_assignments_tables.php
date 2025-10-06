<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asignaciones semanales a alumnos (instancias)
        Schema::create('gym_weekly_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // alumno
            $table->date('week_start'); // lunes de la semana
            $table->date('week_end');   // domingo de la semana
            $table->string('source_type')->nullable(); // from_weekly_template|manual|assistant
            $table->foreignId('weekly_template_id')->nullable()->constrained('gym_weekly_templates')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // profesor
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id','week_start'], 'uniq_gwa_user_week');
        });

        // Días de la asignación semanal (snapshot u overrides)
        Schema::create('gym_daily_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_assignment_id')->constrained('gym_weekly_assignments')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1=Lunes ... 7=Domingo
            $table->date('date'); // fecha concreta del día
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['weekly_assignment_id','weekday'], 'uniq_gda_assignment_weekday');
        });

        // Ejercicios asignados por día (snapshot + referencia opcional a ejercicio catálogo)
        Schema::create('gym_assigned_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_assignment_id')->constrained('gym_daily_assignments')->cascadeOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained('gym_exercises')->nullOnDelete();
            $table->unsignedSmallInteger('display_order')->default(1);
            // Snapshot básicos para inmutabilidad
            $table->string('name');
            $table->string('muscle_group')->nullable();
            $table->string('equipment')->nullable();
            $table->text('instructions')->nullable();
            $table->string('tempo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['daily_assignment_id','display_order'], 'idx_gae_day_order');
        });

        // Series prescritas del ejercicio asignado
        Schema::create('gym_assigned_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_exercise_id')->constrained('gym_assigned_exercises')->cascadeOnDelete();
            $table->unsignedSmallInteger('set_number')->default(1);
            $table->unsignedSmallInteger('reps_min')->nullable();
            $table->unsignedSmallInteger('reps_max')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->string('tempo')->nullable();
            $table->decimal('rpe_target', 4, 2)->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['assigned_exercise_id','set_number'], 'uniq_gas_exercise_set');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_assigned_sets');
        Schema::dropIfExists('gym_assigned_exercises');
        Schema::dropIfExists('gym_daily_assignments');
        Schema::dropIfExists('gym_weekly_assignments');
    }
};
