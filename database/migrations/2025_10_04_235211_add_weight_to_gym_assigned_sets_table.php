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
        Schema::table('gym_assigned_sets', function (Blueprint $table) {
            // Agregar campos de peso para sets asignados
            $table->decimal('weight_min', 8, 2)->nullable()->after('reps_max')->comment('Peso mínimo recomendado en kg');
            $table->decimal('weight_max', 8, 2)->nullable()->after('weight_min')->comment('Peso máximo recomendado en kg');
            $table->decimal('weight_target', 8, 2)->nullable()->after('weight_max')->comment('Peso objetivo/sugerido en kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_assigned_sets', function (Blueprint $table) {
            $table->dropColumn(['weight_min', 'weight_max', 'weight_target']);
        });
    }
};
