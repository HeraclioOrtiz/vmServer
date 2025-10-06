<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_daily_templates', function (Blueprint $table) {
            // Índices para filtros frecuentes
            $table->index('goal', 'idx_daily_templates_goal');
            $table->index('level', 'idx_daily_templates_level');
            $table->index('is_preset', 'idx_daily_templates_is_preset');
            $table->index('estimated_duration_min', 'idx_daily_templates_duration');
            $table->index('created_by', 'idx_daily_templates_created_by');
            
            // Índice compuesto para ordenamiento por defecto
            $table->index(['is_preset', 'created_at', 'title'], 'idx_daily_templates_default_sort');
            
            // Índice para búsqueda por título
            $table->index('title', 'idx_daily_templates_title');
        });

        Schema::table('gym_daily_template_exercises', function (Blueprint $table) {
            // Índices para relaciones y ordenamiento
            $table->index(['daily_template_id', 'display_order'], 'idx_template_exercises_order');
            $table->index('exercise_id', 'idx_template_exercises_exercise_id');
        });

        Schema::table('gym_daily_template_sets', function (Blueprint $table) {
            // Índice para relaciones y ordenamiento de series
            $table->index(['daily_template_exercise_id', 'set_number'], 'idx_template_sets_order');
        });
    }

    public function down(): void
    {
        Schema::table('gym_daily_templates', function (Blueprint $table) {
            $table->dropIndex('idx_daily_templates_goal');
            $table->dropIndex('idx_daily_templates_level');
            $table->dropIndex('idx_daily_templates_is_preset');
            $table->dropIndex('idx_daily_templates_duration');
            $table->dropIndex('idx_daily_templates_created_by');
            $table->dropIndex('idx_daily_templates_default_sort');
            $table->dropIndex('idx_daily_templates_title');
        });

        Schema::table('gym_daily_template_exercises', function (Blueprint $table) {
            $table->dropIndex('idx_template_exercises_order');
            $table->dropIndex('idx_template_exercises_exercise_id');
        });

        Schema::table('gym_daily_template_sets', function (Blueprint $table) {
            $table->dropIndex('idx_template_sets_order');
        });
    }
};
