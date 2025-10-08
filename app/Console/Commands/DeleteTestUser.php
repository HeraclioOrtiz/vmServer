<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\TemplateAssignment;
use App\Models\Gym\AssignmentProgress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete-test {dni : DNI del usuario de prueba a eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina un usuario de prueba y todas sus asignaciones relacionadas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dni = $this->argument('dni');

        $this->info("🔍 Buscando usuario con DNI: {$dni}");

        $user = User::where('dni', $dni)->first();

        if (!$user) {
            $this->error("❌ Usuario con DNI {$dni} no encontrado.");
            return 1;
        }

        $this->info("✅ Usuario encontrado: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Confirmar eliminación
        if (!$this->confirm('¿Estás seguro de eliminar este usuario y todas sus asignaciones?')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        DB::beginTransaction();

        try {
            $deletedItems = [
                'assignment_progress' => 0,
                'template_assignments' => 0,
                'professor_student_assignments' => 0,
                'personal_access_tokens' => 0,
            ];

            // 1. Eliminar progreso de asignaciones
            $deletedItems['assignment_progress'] = AssignmentProgress::whereHas('assignment', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })->delete();

            $this->info("🗑️  Progreso de asignaciones eliminados: {$deletedItems['assignment_progress']}");

            // 2. Eliminar asignaciones de plantillas
            $deletedItems['template_assignments'] = TemplateAssignment::where('student_id', $user->id)->delete();

            $this->info("🗑️  Asignaciones de plantillas eliminadas: {$deletedItems['template_assignments']}");

            // 3. Eliminar asignaciones profesor-estudiante
            $deletedItems['professor_student_assignments'] = ProfessorStudentAssignment::where('student_id', $user->id)->delete();

            $this->info("🗑️  Asignaciones profesor-estudiante eliminadas: {$deletedItems['professor_student_assignments']}");

            // 4. Eliminar tokens de acceso
            $deletedItems['personal_access_tokens'] = DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->delete();

            $this->info("🗑️  Tokens de acceso eliminados: {$deletedItems['personal_access_tokens']}");

            // 5. Eliminar usuario
            $user->delete();

            $this->info("🗑️  Usuario eliminado: {$user->name} (DNI: {$dni})");

            DB::commit();

            $this->newLine();
            $this->info('✅ Usuario de prueba y todas sus asignaciones eliminados exitosamente.');
            $this->newLine();
            $this->table(
                ['Tipo', 'Cantidad Eliminada'],
                [
                    ['Progreso de asignaciones', $deletedItems['assignment_progress']],
                    ['Asignaciones de plantillas', $deletedItems['template_assignments']],
                    ['Asignaciones profesor-estudiante', $deletedItems['professor_student_assignments']],
                    ['Tokens de acceso', $deletedItems['personal_access_tokens']],
                    ['Usuario', 1],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('❌ Error eliminando usuario de prueba:');
            $this->error($e->getMessage());

            return 1;
        }
    }
}
