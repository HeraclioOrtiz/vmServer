<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plantillas diarias (sesiones reusables)
        Schema::create('gym_daily_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('goal')->nullable(); // strength|hypertrophy|endurance
            $table->unsignedSmallInteger('estimated_duration_min')->nullable();
            $table->string('level')->nullable(); // beginner|intermediate|advanced
            $table->json('tags')->nullable();
            $table->boolean('is_preset')->default(false); // true para las 20 predefinidas
            $table->timestamps();
        });

        // Ejercicios dentro de una plantilla diaria (ordenados)
        Schema::create('gym_daily_template_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_template_id')->constrained('gym_daily_templates')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('gym_exercises')->restrictOnDelete();
            $table->unsignedSmallInteger('display_order')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Series prescritas por ejercicio de plantilla diaria
        Schema::create('gym_daily_template_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_template_exercise_id')->constrained('gym_daily_template_exercises')->cascadeOnDelete();
            $table->unsignedSmallInteger('set_number')->default(1);
            $table->unsignedSmallInteger('reps_min')->nullable();
            $table->unsignedSmallInteger('reps_max')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->string('tempo')->nullable();
            $table->decimal('rpe_target', 4, 2)->unsigned()->nullable(); // e.g. 7.5
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Índices útiles
        Schema::table('gym_daily_template_exercises', function (Blueprint $table) {
            $table->index(['daily_template_id', 'display_order'], 'idx_gdte_template_order');
        });
        Schema::table('gym_daily_template_sets', function (Blueprint $table) {
            $table->unique(['daily_template_exercise_id', 'set_number'], 'uniq_gdts_exercise_set');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_daily_template_sets');
        Schema::dropIfExists('gym_daily_template_exercises');
        Schema::dropIfExists('gym_daily_templates');
    }
};
