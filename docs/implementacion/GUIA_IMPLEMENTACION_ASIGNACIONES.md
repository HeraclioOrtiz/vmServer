# 🚀 **GUÍA DE IMPLEMENTACIÓN: SISTEMA DE ASIGNACIONES JERÁRQUICO**

## 📋 **INFORMACIÓN DEL PROYECTO**

**Objetivo:** Implementar sistema de asignaciones jerárquico (Admin→Profesor→Estudiante)  
**Duración estimada:** 5.5-6.5 días  
**Prioridad:** Alta  
**Dependencias:** Sistema de plantillas diarias (✅ Completado)

---

## 🎯 **FASE 1: FUNDACIÓN DE DATOS (Días 1-2)**

### **📊 PASO 1.1: Crear Migraciones**

#### **🗃️ Migración 1: professor_student_assignments**
```bash
php artisan make:migration create_professor_student_assignments_table
```

**Estructura:**
```php
Schema::create('professor_student_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('professor_id')->constrained('users');
    $table->foreignId('student_id')->constrained('users');
    $table->foreignId('assigned_by')->constrained('users'); // admin
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
    $table->text('admin_notes')->nullable();
    $table->timestamps();
    
    // Índices
    $table->index(['professor_id', 'status']);
    $table->index(['student_id', 'status']);
    $table->unique(['professor_id', 'student_id', 'status']); // Un estudiante activo por profesor
});
```

#### **🗃️ Migración 2: daily_assignments**
```bash
php artisan make:migration create_daily_assignments_table
```

**Estructura:**
```php
Schema::create('daily_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('professor_student_assignment_id')->constrained();
    $table->foreignId('daily_template_id')->constrained('gym_daily_templates');
    $table->foreignId('assigned_by')->constrained('users'); // profesor
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->json('frequency'); // días de la semana
    $table->text('professor_notes')->nullable();
    $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
    $table->timestamps();
    
    // Índices
    $table->index(['professor_student_assignment_id', 'status']);
    $table->index(['daily_template_id']);
    $table->index(['start_date', 'end_date']);
});
```

#### **🗃️ Migración 3: assignment_progress**
```bash
php artisan make:migration create_assignment_progress_table
```

**Estructura:**
```php
Schema::create('assignment_progress', function (Blueprint $table) {
    $table->id();
    $table->foreignId('daily_assignment_id')->constrained();
    $table->date('scheduled_date');
    $table->enum('status', ['pending', 'completed', 'skipped', 'cancelled'])->default('pending');
    $table->timestamp('completed_at')->nullable();
    $table->json('exercise_progress')->nullable(); // progreso por ejercicio
    $table->text('student_notes')->nullable();
    $table->text('professor_feedback')->nullable();
    $table->timestamps();
    
    // Índices
    $table->index(['daily_assignment_id', 'scheduled_date']);
    $table->index(['status', 'scheduled_date']);
});
```

### **📊 PASO 1.2: Crear Modelos Eloquent**

#### **🏗️ Modelo: ProfessorStudentAssignment**
```bash
php artisan make:model ProfessorStudentAssignment
```

**Implementación:**
```php
<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessorStudentAssignment extends Model
{
    protected $fillable = [
        'professor_id', 'student_id', 'assigned_by',
        'start_date', 'end_date', 'status', 'admin_notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relaciones
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function dailyAssignments(): HasMany
    {
        return $this->hasMany(DailyAssignment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForProfessor($query, $professorId)
    {
        return $query->where('professor_id', $professorId);
    }
}
```

#### **🏗️ Modelo: DailyAssignment**
```bash
php artisan make:model DailyAssignment
```

**Implementación:**
```php
<?php

namespace App\Models\Gym;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyAssignment extends Model
{
    protected $fillable = [
        'professor_student_assignment_id', 'daily_template_id', 'assigned_by',
        'start_date', 'end_date', 'frequency', 'professor_notes', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'frequency' => 'array',
    ];

    // Relaciones
    public function professorStudentAssignment(): BelongsTo
    {
        return $this->belongsTo(ProfessorStudentAssignment::class);
    }

    public function dailyTemplate(): BelongsTo
    {
        return $this->belongsTo(DailyTemplate::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(AssignmentProgress::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->whereHas('professorStudentAssignment', function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
    }
}
```

### **📊 PASO 1.3: Ejecutar Migraciones**
```bash
php artisan migrate
```

**✅ Criterios de aceptación Fase 1:**
- [ ] Migraciones ejecutadas sin errores
- [ ] Modelos creados con relaciones correctas
- [ ] Índices funcionando correctamente
- [ ] Tests básicos de modelos pasando

---

## 🏗️ **FASE 2: SERVICIOS Y LÓGICA DE NEGOCIO (Días 3-4)**

### **📊 PASO 2.1: Crear AssignmentService**

```bash
php artisan make:service AssignmentService
```

**Implementación:**
```php
<?php

namespace App\Services\Gym;

use App\Models\Gym\ProfessorStudentAssignment;
use App\Models\Gym\DailyAssignment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    // Asignaciones profesor-estudiante (Admin)
    public function assignStudentToProfessor(array $data): ProfessorStudentAssignment
    {
        return DB::transaction(function () use ($data) {
            // Validar que el estudiante no esté ya asignado activamente
            $existingAssignment = ProfessorStudentAssignment::where('student_id', $data['student_id'])
                ->where('status', 'active')
                ->first();

            if ($existingAssignment) {
                throw new \Exception('El estudiante ya está asignado a un profesor');
            }

            // Crear asignación
            $assignment = ProfessorStudentAssignment::create($data);

            // Notificar al profesor (implementar después)
            // $this->notifyProfessor($assignment);

            return $assignment;
        });
    }

    public function getProfessorStudents($professorId, array $filters = []): LengthAwarePaginator
    {
        $query = ProfessorStudentAssignment::with(['student', 'assignedBy'])
            ->where('professor_id', $professorId);

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    // Asignaciones de plantillas (Profesor)
    public function assignTemplateToStudent(array $data): DailyAssignment
    {
        return DB::transaction(function () use ($data) {
            // Validar que el profesor tenga el estudiante asignado
            $professorStudentAssignment = ProfessorStudentAssignment::find($data['professor_student_assignment_id']);
            
            if (!$professorStudentAssignment || $professorStudentAssignment->status !== 'active') {
                throw new \Exception('Asignación profesor-estudiante no válida');
            }

            // Crear asignación de plantilla
            $assignment = DailyAssignment::create($data);

            // Generar progreso inicial
            $this->generateProgressSchedule($assignment);

            return $assignment;
        });
    }

    private function generateProgressSchedule(DailyAssignment $assignment): void
    {
        // Generar fechas según frecuencia
        $startDate = $assignment->start_date;
        $endDate = $assignment->end_date ?? $startDate->addWeeks(4);
        $frequency = $assignment->frequency; // [1,3,5] = Lun, Mie, Vie

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->dayOfWeek, $frequency)) {
                AssignmentProgress::create([
                    'daily_assignment_id' => $assignment->id,
                    'scheduled_date' => $currentDate->format('Y-m-d'),
                    'status' => 'pending'
                ]);
            }
            $currentDate->addDay();
        }
    }
}
```

### **📊 PASO 2.2: Crear Controladores**

#### **🎯 AdminAssignmentController**
```bash
php artisan make:controller Admin/AssignmentController
```

**Implementación:**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Gym\AssignmentService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignmentService
    ) {}

    public function index(Request $request)
    {
        $assignments = $this->assignmentService->getAllProfessorStudentAssignments(
            $this->buildFilters($request)
        );

        return response()->json($assignments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'professor_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $validated['assigned_by'] = auth()->id();

        $assignment = $this->assignmentService->assignStudentToProfessor($validated);

        return response()->json($assignment, 201);
    }

    private function buildFilters(Request $request): array
    {
        return [
            'professor_id' => $request->integer('professor_id'),
            'student_id' => $request->integer('student_id'),
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ];
    }
}
```

#### **🎯 ProfessorAssignmentController**
```bash
php artisan make:controller Gym/Professor/AssignmentController
```

**Implementación:**
```php
<?php

namespace App\Http\Controllers\Gym\Professor;

use App\Http\Controllers\Controller;
use App\Services\Gym\AssignmentService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignmentService
    ) {}

    public function myStudents(Request $request)
    {
        $students = $this->assignmentService->getProfessorStudents(
            auth()->id(),
            $this->buildFilters($request)
        );

        return response()->json($students);
    }

    public function assignTemplate(Request $request)
    {
        $validated = $request->validate([
            'professor_student_assignment_id' => 'required|exists:professor_student_assignments,id',
            'daily_template_id' => 'required|exists:gym_daily_templates,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'frequency' => 'required|array',
            'frequency.*' => 'integer|between:0,6',
            'professor_notes' => 'nullable|string|max:1000'
        ]);

        $validated['assigned_by'] = auth()->id();

        $assignment = $this->assignmentService->assignTemplateToStudent($validated);

        return response()->json($assignment, 201);
    }

    private function buildFilters(Request $request): array
    {
        return [
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ];
    }
}
```

### **📊 PASO 2.3: Crear Rutas**

**routes/api.php:**
```php
// Rutas de Admin - Asignaciones
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('assignments', Admin\AssignmentController::class);
    Route::get('professors/{professor}/students', [Admin\AssignmentController::class, 'professorStudents']);
    Route::get('students/unassigned', [Admin\AssignmentController::class, 'unassignedStudents']);
});

// Rutas de Profesor - Asignaciones
Route::middleware(['auth:api', 'role:professor'])->prefix('professor')->group(function () {
    Route::get('my-students', [Gym\Professor\AssignmentController::class, 'myStudents']);
    Route::post('assign-template', [Gym\Professor\AssignmentController::class, 'assignTemplate']);
    Route::get('assignments/{assignment}', [Gym\Professor\AssignmentController::class, 'show']);
});
```

**✅ Criterios de aceptación Fase 2:**
- [ ] AssignmentService implementado y testeado
- [ ] Controladores creados con validaciones
- [ ] Rutas configuradas correctamente
- [ ] Middleware de roles funcionando

---

## 🧪 **FASE 3: TESTING Y VALIDACIÓN (Día 5)**

### **📊 PASO 3.1: Tests Unitarios**

#### **🧪 Test AssignmentService**
```bash
php artisan make:test AssignmentServiceTest --unit
```

#### **🧪 Test Controladores**
```bash
php artisan make:test AdminAssignmentControllerTest
php artisan make:test ProfessorAssignmentControllerTest
```

### **📊 PASO 3.2: Tests de Integración**
```bash
php artisan make:test AssignmentFlowTest
```

### **📊 PASO 3.3: Validación Manual**
- [ ] Admin puede asignar estudiantes a profesores
- [ ] Profesor ve solo sus estudiantes
- [ ] Profesor puede asignar plantillas
- [ ] Validaciones funcionan correctamente
- [ ] Notificaciones se envían

**✅ Criterios de aceptación Fase 3:**
- [ ] Cobertura de tests > 90%
- [ ] Todos los tests pasan
- [ ] Validación manual exitosa
- [ ] Performance aceptable (< 200ms)

---

## 📚 **FASE 4: DOCUMENTACIÓN Y FINALIZACIÓN (Día 6)**

### **📊 PASO 4.1: Documentación API**
- Endpoints de asignaciones
- Ejemplos de requests/responses
- Códigos de error

### **📊 PASO 4.2: Guía de Usuario**
- Manual para administradores
- Manual para profesores
- Casos de uso comunes

### **📊 PASO 4.3: Deployment**
- Migraciones en producción
- Verificación de funcionamiento
- Monitoreo inicial

**✅ Criterios de aceptación Fase 4:**
- [ ] Documentación completa
- [ ] Sistema en producción
- [ ] Usuarios capacitados
- [ ] Métricas funcionando

---

## 🎯 **CHECKLIST FINAL**

### **✅ FUNCIONALIDADES CORE:**
- [ ] Admin asigna estudiantes a profesores
- [ ] Profesor ve sus estudiantes asignados
- [ ] Profesor asigna plantillas a estudiantes
- [ ] Sistema valida permisos correctamente
- [ ] Progreso se trackea automáticamente

### **✅ CALIDAD:**
- [ ] Tests unitarios > 90% cobertura
- [ ] Tests de integración pasando
- [ ] Performance < 200ms endpoints
- [ ] Documentación completa

### **✅ SEGURIDAD:**
- [ ] Validaciones de permisos
- [ ] Sanitización de inputs
- [ ] Auditoría de acciones
- [ ] Logs de seguridad

---

**DOCUMENTO CREADO:** 2025-01-26 10:52  
**ESTIMACIÓN TOTAL:** 5.5-6.5 días  
**PRÓXIMO PASO:** Ejecutar Fase 1  
**RESPONSABLE:** Equipo de desarrollo
