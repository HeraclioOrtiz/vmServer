<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignAllStudentsToProfessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:assign-to-professor {professor_dni=22222222}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna todos los estudiantes a un profesor específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $professorDni = $this->argument('professor_dni');
        
        $this->info("🔍 Buscando profesor con DNI: {$professorDni}");
        
        // Buscar profesor
        $professor = User::where('dni', $professorDni)
            ->where(function($query) {
                $query->where('is_professor', true)
                      ->orWhere('is_admin', true);
            })
            ->first();

        if (!$professor) {
            $this->error("❌ No se encontró profesor con DNI: {$professorDni}");
            $this->error("   Verifica que el usuario exista y sea profesor o admin.");
            return 1;
        }

        $this->info("✅ Profesor encontrado: {$professor->name} (ID: {$professor->id})");
        $this->newLine();

        // Buscar todos los estudiantes (usuarios que NO son profesores ni admins)
        $students = User::where('is_professor', false)
            ->where('is_admin', false)
            ->where('is_super_admin', false)
            ->where('is_active', true)
            ->get();

        if ($students->isEmpty()) {
            $this->warn("⚠️  No hay estudiantes para asignar.");
            return 0;
        }

        $this->info("👥 Estudiantes encontrados: {$students->count()}");
        $this->newLine();

        // Confirmar acción
        if (!$this->confirm("¿Deseas asignar {$students->count()} estudiantes al profesor {$professor->name}?", true)) {
            $this->warn("❌ Operación cancelada.");
            return 0;
        }

        $this->newLine();
        $this->info("🚀 Iniciando asignaciones...");
        
        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        DB::beginTransaction();
        
        try {
            foreach ($students as $student) {
                // Verificar si ya está asignado a este profesor
                $existingAssignment = ProfessorStudentAssignment::where('professor_id', $professor->id)
                    ->where('student_id', $student->id)
                    ->first();

                if ($existingAssignment) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Crear asignación
                ProfessorStudentAssignment::create([
                    'professor_id' => $professor->id,
                    'student_id' => $student->id,
                    'assigned_at' => now(),
                    'assigned_by' => $professor->id,
                    'status' => 'active',
                ]);

                $assigned++;
                $progressBar->advance();
            }

            DB::commit();
            
            $progressBar->finish();
            $this->newLine(2);

            // Resumen
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("✅ ASIGNACIONES COMPLETADAS");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("👨‍🏫 Profesor: {$professor->name}");
            $this->info("📊 Total estudiantes: {$students->count()}");
            $this->info("✅ Nuevas asignaciones: {$assigned}");
            $this->info("⏭️  Ya asignados (omitidos): {$skipped}");
            if ($errors > 0) {
                $this->error("❌ Errores: {$errors}");
            }
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $progressBar->finish();
            $this->newLine(2);
            $this->error("❌ Error durante la asignación: " . $e->getMessage());
            return 1;
        }
    }
}
