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
        Schema::table('gym_exercises', function (Blueprint $table) {
            // 1. AGREGAR: description
            if (!Schema::hasColumn('gym_exercises', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            // 2. AGREGAR: muscle_groups (JSON) y target_muscle_groups (JSON)
            if (!Schema::hasColumn('gym_exercises', 'muscle_groups')) {
                $table->json('muscle_groups')->nullable()->after('description');
            }
            if (!Schema::hasColumn('gym_exercises', 'target_muscle_groups')) {
                $table->json('target_muscle_groups')->nullable()->after('muscle_groups');
            }
            
            // 3. RENOMBRAR: difficulty -> difficulty_level
            if (Schema::hasColumn('gym_exercises', 'difficulty') && !Schema::hasColumn('gym_exercises', 'difficulty_level')) {
                $table->renameColumn('difficulty', 'difficulty_level');
            }
        });
        
        // 4. MIGRAR: muscle_group (singular) -> muscle_groups (plural, JSON)
        $exercises = \Illuminate\Support\Facades\DB::table('gym_exercises')->get();
        foreach ($exercises as $exercise) {
            $updates = [];
            
            // Migrar muscle_group a muscle_groups como array
            if (isset($exercise->muscle_group) && $exercise->muscle_group) {
                $updates['muscle_groups'] = json_encode([$exercise->muscle_group]);
            }
            
            if (!empty($updates)) {
                \Illuminate\Support\Facades\DB::table('gym_exercises')
                    ->where('id', $exercise->id)
                    ->update($updates);
            }
        }
        
        // 5. ELIMINAR: muscle_group (singular) y tempo
        Schema::table('gym_exercises', function (Blueprint $table) {
            if (Schema::hasColumn('gym_exercises', 'muscle_group')) {
                $table->dropColumn('muscle_group');
            }
            if (Schema::hasColumn('gym_exercises', 'tempo')) {
                $table->dropColumn('tempo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_exercises', function (Blueprint $table) {
            // Revertir cambios
            if (!Schema::hasColumn('gym_exercises', 'muscle_group')) {
                $table->string('muscle_group')->nullable();
            }
            if (!Schema::hasColumn('gym_exercises', 'tempo')) {
                $table->string('tempo')->nullable();
            }
            
            if (Schema::hasColumn('gym_exercises', 'difficulty_level')) {
                $table->renameColumn('difficulty_level', 'difficulty');
            }
            
            if (Schema::hasColumn('gym_exercises', 'target_muscle_groups')) {
                $table->dropColumn('target_muscle_groups');
            }
            if (Schema::hasColumn('gym_exercises', 'muscle_groups')) {
                $table->dropColumn('muscle_groups');
            }
            if (Schema::hasColumn('gym_exercises', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
