<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gym\DailyTemplate;
use App\Models\Gym\DailyTemplateExercise;
use App\Models\Gym\Exercise;
use App\Services\Gym\SetService;
use App\Models\User;

class GymDailyTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar plantillas existentes
        DailyTemplate::query()->delete();
        
        echo "📋 Creando 2 plantillas diarias con ejercicios y sets...\n\n";
        
        $setService = app(SetService::class);
        
        // Obtener usuario para created_by
        $user = User::where('is_admin', true)->first() ?? User::first();
        
        // Obtener los 4 ejercicios creados
        $pressBanca = Exercise::where('name', 'Press de Banca con Barra')->first();
        $pesoMuerto = Exercise::where('name', 'Peso Muerto Convencional')->first();
        $sentadilla = Exercise::where('name', 'Sentadilla Trasera (Back Squat)')->first();
        $dominadas = Exercise::where('name', 'Dominadas (Pull-ups)')->first();
        
        // ============================================================
        // PLANTILLA 1: Rutina Push (Fuerza)
        // ============================================================
        echo "Creando Plantilla 1: Rutina Push (Fuerza)...\n";
        
        $template1 = DailyTemplate::create([
            'created_by' => $user ? $user->id : null,
            'title' => 'Rutina Push - Fuerza',
            'goal' => 'strength',
            'estimated_duration_min' => 60,
            'level' => 'intermediate',
            'tags' => ['push', 'fuerza', 'pecho', 'hombros'],
            'is_preset' => true,
        ]);
        
        echo "  ✅ Plantilla creada (ID: {$template1->id})\n";
        
        // Agregar Press de Banca
        if ($pressBanca) {
            $te1 = DailyTemplateExercise::create([
                'daily_template_id' => $template1->id,
                'exercise_id' => $pressBanca->id,
                'display_order' => 1,
                'notes' => 'Ejercicio principal. Controlar la bajada, explosivo en la subida.',
            ]);
            
            // 3 sets de fuerza
            $setService->createSetsForExercise($te1, [
                ['set_number' => 1, 'reps_min' => 4, 'reps_max' => 6, 'rpe_target' => 8.5, 'rest_seconds' => 180, 'weight_min' => 40, 'weight_max' => 80, 'weight_target' => 60],
                ['set_number' => 2, 'reps_min' => 4, 'reps_max' => 6, 'rpe_target' => 9.0, 'rest_seconds' => 180, 'weight_min' => 45, 'weight_max' => 85, 'weight_target' => 65],
                ['set_number' => 3, 'reps_min' => 4, 'reps_max' => 6, 'rpe_target' => 9.5, 'rest_seconds' => 180, 'weight_min' => 50, 'weight_max' => 90, 'weight_target' => 70],
            ]);
            
            echo "    • {$pressBanca->name} - 3 sets agregados\n";
        }
        
        echo "\n";
        
        // ============================================================
        // PLANTILLA 2: Full Body (General)
        // ============================================================
        echo "Creando Plantilla 2: Full Body - General...\n";
        
        $template2 = DailyTemplate::create([
            'created_by' => $user ? $user->id : null,
            'title' => 'Full Body - General',
            'goal' => 'general',
            'estimated_duration_min' => 75,
            'level' => 'intermediate',
            'tags' => ['full-body', 'general', 'completo'],
            'is_preset' => true,
        ]);
        
        echo "  ✅ Plantilla creada (ID: {$template2->id})\n";
        
        // 1. Sentadilla
        if ($sentadilla) {
            $te2_1 = DailyTemplateExercise::create([
                'daily_template_id' => $template2->id,
                'exercise_id' => $sentadilla->id,
                'display_order' => 1,
                'notes' => 'Mantener la espalda neutra durante todo el movimiento.',
            ]);
            
            $setService->createSetsForExercise($te2_1, [
                ['set_number' => 1, 'reps_min' => 8, 'reps_max' => 10, 'rpe_target' => 7.5, 'rest_seconds' => 120, 'weight_min' => 50, 'weight_max' => 90, 'weight_target' => 70],
                ['set_number' => 2, 'reps_min' => 8, 'reps_max' => 10, 'rpe_target' => 8.0, 'rest_seconds' => 120, 'weight_min' => 55, 'weight_max' => 95, 'weight_target' => 75],
                ['set_number' => 3, 'reps_min' => 8, 'reps_max' => 10, 'rpe_target' => 8.5, 'rest_seconds' => 120, 'weight_min' => 60, 'weight_max' => 100, 'weight_target' => 80],
            ]);
            
            echo "    • {$sentadilla->name} - 3 sets agregados\n";
        }
        
        // 2. Press de Banca
        if ($pressBanca) {
            $te2_2 = DailyTemplateExercise::create([
                'daily_template_id' => $template2->id,
                'exercise_id' => $pressBanca->id,
                'display_order' => 2,
                'notes' => 'Agarre ligeramente más ancho que los hombros.',
            ]);
            
            $setService->createSetsForExercise($te2_2, [
                ['set_number' => 1, 'reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 7.5, 'rest_seconds' => 90, 'weight_min' => 30, 'weight_max' => 60, 'weight_target' => 45],
                ['set_number' => 2, 'reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 8.0, 'rest_seconds' => 90, 'weight_min' => 35, 'weight_max' => 65, 'weight_target' => 50],
                ['set_number' => 3, 'reps_min' => 8, 'reps_max' => 12, 'rpe_target' => 8.5, 'rest_seconds' => 90, 'weight_min' => 40, 'weight_max' => 70, 'weight_target' => 55],
            ]);
            
            echo "    • {$pressBanca->name} - 3 sets agregados\n";
        }
        
        // 3. Dominadas
        if ($dominadas) {
            $te2_3 = DailyTemplateExercise::create([
                'daily_template_id' => $template2->id,
                'exercise_id' => $dominadas->id,
                'display_order' => 3,
                'notes' => 'Si no puedes completar las repeticiones, usar banda elástica de asistencia.',
            ]);
            
            $setService->createSetsForExercise($te2_3, [
                ['set_number' => 1, 'reps_min' => 5, 'reps_max' => 8, 'rpe_target' => 8.0, 'rest_seconds' => 120, 'weight_min' => 0, 'weight_max' => 20, 'weight_target' => 10],
                ['set_number' => 2, 'reps_min' => 5, 'reps_max' => 8, 'rpe_target' => 8.5, 'rest_seconds' => 120, 'weight_min' => 0, 'weight_max' => 25, 'weight_target' => 12],
                ['set_number' => 3, 'reps_min' => 5, 'reps_max' => 8, 'rpe_target' => 9.0, 'rest_seconds' => 120, 'weight_min' => 0, 'weight_max' => 30, 'weight_target' => 15],
            ]);
            
            echo "    • {$dominadas->name} - 3 sets agregados\n";
        }
        
        // 4. Peso Muerto
        if ($pesoMuerto) {
            $te2_4 = DailyTemplateExercise::create([
                'daily_template_id' => $template2->id,
                'exercise_id' => $pesoMuerto->id,
                'display_order' => 4,
                'notes' => 'Mantener la barra pegada al cuerpo. Espalda siempre neutra.',
            ]);
            
            $setService->createSetsForExercise($te2_4, [
                ['set_number' => 1, 'reps_min' => 5, 'reps_max' => 8, 'rpe_target' => 8.0, 'rest_seconds' => 150, 'weight_min' => 60, 'weight_max' => 120, 'weight_target' => 90],
                ['set_number' => 2, 'reps_min' => 5, 'reps_max' => 8, 'rpe_target' => 8.5, 'rest_seconds' => 150, 'weight_min' => 70, 'weight_max' => 130, 'weight_target' => 100],
                ['set_number' => 3, 'reps_min' => 5, 'reps_max' => 8, 'rpe_target' => 9.0, 'rest_seconds' => 150, 'weight_min' => 80, 'weight_max' => 140, 'weight_target' => 110],
            ]);
            
            echo "    • {$pesoMuerto->name} - 3 sets agregados\n";
        }
        
        echo "\n🎉 ¡2 plantillas diarias creadas exitosamente!\n\n";
        
        // Mostrar resumen
        echo "📊 RESUMEN DE PLANTILLAS:\n";
        echo str_repeat("=", 80) . "\n";
        
        $templates = DailyTemplate::with(['exercises.exercise', 'exercises.sets'])->get();
        
        foreach ($templates as $i => $tpl) {
            echo "\n" . ($i + 1) . ". {$tpl->title}\n";
            echo "   Goal: {$tpl->goal} | Nivel: {$tpl->level} | Duración: {$tpl->estimated_duration_min} min\n";
            echo "   Ejercicios: {$tpl->exercises->count()}\n";
            
            foreach ($tpl->exercises as $te) {
                $setsCount = $te->sets->count();
                echo "     • {$te->exercise->name} ({$setsCount} sets)\n";
            }
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
    }
}
