# 🚀 PROMPT: Correcciones del Panel de Administración Villa Mitre

## 📋 **INSTRUCCIONES PARA EL ASISTENTE**

Usa este prompt para guiar las correcciones sistemáticas del Panel de Administración. Sigue el orden exacto y verifica cada elemento antes de continuar al siguiente.

---

## 🎯 **CONTEXTO DEL PROYECTO**

Estoy trabajando en el Panel de Administración Villa Mitre, un sistema Laravel que tiene dos paneles:
1. **Panel de Gimnasio** (profesores) - Gestión de ejercicios, plantillas y asignaciones
2. **Panel Villa Mitre** (administradores) - Gestión de usuarios, configuración y auditoría

**Problema identificado:** Según la auditoría, el sistema está ~40-50% implementado con problemas críticos que impiden su funcionamiento.

---

## 🔧 **FASE 1: CORRECCIONES CRÍTICAS (PRIORIDAD MÁXIMA)**

### **TAREA 1.1: Agregar Rutas Faltantes del Gimnasio**

**PROBLEMA:** Las rutas del panel de gimnasio no están definidas en `/routes/admin.php`

**ACCIÓN REQUERIDA:**
```
Agrega las siguientes rutas al archivo /routes/admin.php después de las rutas existentes:

// ==================== PANEL DE GIMNASIO ====================
Route::middleware(['auth:sanctum', 'professor'])->prefix('admin/gym')->group(function () {
    
    // Ejercicios
    Route::prefix('exercises')->group(function () {
        Route::get('/', [ExerciseController::class, 'index'])->name('admin.gym.exercises.index');
        Route::post('/', [ExerciseController::class, 'store'])->name('admin.gym.exercises.store');
        Route::get('/{exercise}', [ExerciseController::class, 'show'])->name('admin.gym.exercises.show');
        Route::put('/{exercise}', [ExerciseController::class, 'update'])->name('admin.gym.exercises.update');
        Route::delete('/{exercise}', [ExerciseController::class, 'destroy'])->name('admin.gym.exercises.destroy');
        Route::post('/{exercise}/duplicate', [ExerciseController::class, 'duplicate'])->name('admin.gym.exercises.duplicate');
    });
    
    // Plantillas Diarias
    Route::prefix('daily-templates')->group(function () {
        Route::get('/', [DailyTemplateController::class, 'index'])->name('admin.gym.daily-templates.index');
        Route::post('/', [DailyTemplateController::class, 'store'])->name('admin.gym.daily-templates.store');
        Route::get('/{template}', [DailyTemplateController::class, 'show'])->name('admin.gym.daily-templates.show');
        Route::put('/{template}', [DailyTemplateController::class, 'update'])->name('admin.gym.daily-templates.update');
        Route::delete('/{template}', [DailyTemplateController::class, 'destroy'])->name('admin.gym.daily-templates.destroy');
        Route::post('/{template}/duplicate', [DailyTemplateController::class, 'duplicate'])->name('admin.gym.daily-templates.duplicate');
    });
    
    // Plantillas Semanales
    Route::prefix('weekly-templates')->group(function () {
        Route::get('/', [WeeklyTemplateController::class, 'index'])->name('admin.gym.weekly-templates.index');
        Route::post('/', [WeeklyTemplateController::class, 'store'])->name('admin.gym.weekly-templates.store');
        Route::get('/{template}', [WeeklyTemplateController::class, 'show'])->name('admin.gym.weekly-templates.show');
        Route::put('/{template}', [WeeklyTemplateController::class, 'update'])->name('admin.gym.weekly-templates.update');
        Route::delete('/{template}', [WeeklyTemplateController::class, 'destroy'])->name('admin.gym.weekly-templates.destroy');
        Route::post('/{template}/duplicate', [WeeklyTemplateController::class, 'duplicate'])->name('admin.gym.weekly-templates.duplicate');
    });
    
    // Asignaciones Semanales
    Route::prefix('weekly-assignments')->group(function () {
        Route::get('/', [WeeklyAssignmentController::class, 'index'])->name('admin.gym.weekly-assignments.index');
        Route::post('/', [WeeklyAssignmentController::class, 'store'])->name('admin.gym.weekly-assignments.store');
        Route::get('/{assignment}', [WeeklyAssignmentController::class, 'show'])->name('admin.gym.weekly-assignments.show');
        Route::put('/{assignment}', [WeeklyAssignmentController::class, 'update'])->name('admin.gym.weekly-assignments.update');
        Route::delete('/{assignment}', [WeeklyAssignmentController::class, 'destroy'])->name('admin.gym.weekly-assignments.destroy');
        Route::get('/{assignment}/adherence', [WeeklyAssignmentController::class, 'adherence'])->name('admin.gym.weekly-assignments.adherence');
    });
});

IMPORTANTE: Agrega también los imports necesarios al inicio del archivo:
use App\Http\Controllers\Gym\Admin\ExerciseController;
use App\Http\Controllers\Gym\Admin\DailyTemplateController;
use App\Http\Controllers\Gym\Admin\WeeklyTemplateController;
use App\Http\Controllers\Gym\Admin\WeeklyAssignmentController;
```

**VERIFICACIÓN:**
- [ ] Rutas agregadas correctamente
- [ ] Imports añadidos
- [ ] Middleware 'professor' aplicado
- [ ] Nombres de rutas consistentes

---

### **TAREA 1.2: Verificar y Crear Middleware Faltante**

**PROBLEMA:** Los middleware 'admin' y 'professor' pueden no existir o estar mal configurados

**ACCIÓN REQUERIDA:**
```
1. Verifica si existe /app/Http/Middleware/EnsureAdmin.php
2. Verifica si existe /app/Http/Middleware/EnsureProfessor.php
3. Si no existen, créalos con este código:

// EnsureAdmin.php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || (!$user->is_admin && !$user->is_super_admin)) {
            return response()->json([
                'message' => 'Forbidden: admin role required.'
            ], 403);
        }
        
        return $next($request);
    }
}

// EnsureProfessor.php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class EnsureProfessor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || (!$user->is_professor && !$user->is_admin && !$user->is_super_admin)) {
            return response()->json([
                'message' => 'Forbidden: professor role required.'
            ], 403);
        }
        
        return $next($request);
    }
}

4. Registra los middleware en app/Http/Kernel.php en la sección $middlewareAliases:
'admin' => \App\Http\Middleware\EnsureAdmin::class,
'professor' => \App\Http\Middleware\EnsureProfessor::class,
```

**VERIFICACIÓN:**
- [ ] Middleware EnsureAdmin existe y funciona
- [ ] Middleware EnsureProfessor existe y funciona
- [ ] Ambos están registrados en Kernel.php
- [ ] Lógica de permisos correcta

---

### **TAREA 1.3: Crear Modelos Faltantes**

**PROBLEMA:** Los modelos SystemSetting y AuditLog no existen

**ACCIÓN REQUERIDA:**
```
1. Crea /app/Models/SystemSetting.php:

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'category',
        'description',
        'is_public'
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean'
    ];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $category = 'general', $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'category' => $category,
                'description' => $description
            ]
        );
    }
}

2. Crea /app/Models/AuditLog.php:

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'details',
        'ip_address',
        'user_agent',
        'severity'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
```

**VERIFICACIÓN:**
- [ ] SystemSetting.php creado con métodos get/set
- [ ] AuditLog.php creado con relaciones
- [ ] Casts configurados correctamente
- [ ] Scopes útiles implementados

---

### **TAREA 1.4: Extender Modelo User**

**PROBLEMA:** El modelo User no tiene los campos necesarios para admin

**ACCIÓN REQUERIDA:**
```
Agrega estos campos y métodos al modelo User (/app/Models/User.php):

// En $fillable, agrega:
'is_admin',
'permissions',
'admin_notes',
'account_status',
'professor_since',
'session_timeout'

// En $casts, agrega:
'is_admin' => 'boolean',
'permissions' => 'array',
'professor_since' => 'datetime',
'session_timeout' => 'integer'

// Agrega estos métodos:
public function hasPermission(string $permission): bool
{
    return in_array($permission, $this->permissions ?? []);
}

public function canManageUsers(): bool
{
    return $this->is_admin || $this->hasPermission('user_management');
}

public function canManageGym(): bool
{
    return $this->is_professor || $this->hasPermission('gym_admin');
}

public function isActive(): bool
{
    return $this->account_status === 'active';
}

public function isSuspended(): bool
{
    return $this->account_status === 'suspended';
}
```

**VERIFICACIÓN:**
- [ ] Campos agregados a $fillable
- [ ] Casts configurados
- [ ] Métodos de permisos implementados
- [ ] Métodos de estado implementados

---

### **TAREA 1.5: Crear Migraciones Faltantes**

**PROBLEMA:** Las migraciones para los nuevos campos y tablas no existen

**ACCIÓN REQUERIDA:**
```
Ejecuta estos comandos para crear las migraciones:

1. php artisan make:migration add_admin_fields_to_users_table --table=users
2. php artisan make:migration create_system_settings_table
3. php artisan make:migration create_audit_logs_table

Luego edita cada migración:

// add_admin_fields_to_users_table.php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false)->after('is_professor');
        $table->json('permissions')->nullable()->after('is_admin');
        $table->text('admin_notes')->nullable()->after('permissions');
        $table->enum('account_status', ['active', 'suspended', 'pending'])->default('active')->after('admin_notes');
        $table->timestamp('professor_since')->nullable()->after('account_status');
        $table->integer('session_timeout')->default(480)->after('professor_since');
        
        $table->index('is_admin');
        $table->index('account_status');
    });
}

// create_system_settings_table.php
public function up()
{
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();
        $table->json('value');
        $table->string('category');
        $table->text('description')->nullable();
        $table->boolean('is_public')->default(false);
        $table->timestamps();
        
        $table->index(['category', 'key']);
    });
}

// create_audit_logs_table.php
public function up()
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('action');
        $table->string('resource_type');
        $table->unsignedBigInteger('resource_id')->nullable();
        $table->json('details')->nullable();
        $table->ipAddress('ip_address');
        $table->text('user_agent')->nullable();
        $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
        $table->timestamps();
        
        $table->index(['user_id', 'created_at']);
        $table->index(['action', 'created_at']);
        $table->index(['resource_type', 'resource_id']);
        $table->index('severity');
    });
}

Ejecuta: php artisan migrate
```

**VERIFICACIÓN:**
- [ ] Migraciones creadas correctamente
- [ ] Campos agregados a users
- [ ] Tabla system_settings creada
- [ ] Tabla audit_logs creada
- [ ] Índices configurados
- [ ] Migraciones ejecutadas sin errores

---

## 🔍 **FASE 2: VERIFICACIÓN DETALLADA**

### **TAREA 2.1: Auditar Controllers Existentes**

**ACCIÓN REQUERIDA:**
```
Revisa cada controller y verifica que tenga todos los métodos requeridos:

1. /app/Http/Controllers/Admin/AdminUserController.php
   - Verifica métodos: index, store, show, update, destroy, stats, assignAdminRole, removeAdminRole, suspend, activate
   - Verifica filtros en index(): search, user_type, is_professor, estado_socio, semaforo, date_from, date_to
   - Verifica paginación y ordenamiento

2. /app/Http/Controllers/Admin/AdminProfessorController.php
   - Verifica métodos: index, show, update, assignProfessor, removeProfessor, students, reassignStudent
   - Verifica estadísticas en index()

3. /app/Http/Controllers/Admin/AuditLogController.php
   - Verifica métodos: index, show, stats, filterOptions, export
   - Verifica filtros avanzados

4. Controllers de Gimnasio:
   - ExerciseController: index, store, show, update, destroy, duplicate
   - DailyTemplateController: index, store, show, update, destroy, duplicate
   - WeeklyTemplateController: index, store, show, update, destroy, duplicate
   - WeeklyAssignmentController: index, store, show, update, destroy, adherence
```

**VERIFICACIÓN POR CONTROLLER:**
- [ ] AdminUserController - Métodos completos
- [ ] AdminProfessorController - Métodos completos
- [ ] AuditLogController - Métodos completos
- [ ] ExerciseController - Métodos completos
- [ ] DailyTemplateController - Métodos completos
- [ ] WeeklyTemplateController - Métodos completos
- [ ] WeeklyAssignmentController - Métodos completos

---

### **TAREA 2.2: Crear Form Requests Faltantes**

**ACCIÓN REQUERIDA:**
```
Crea los Form Requests necesarios en /app/Http/Requests/Admin/:

1. php artisan make:request Admin/UserUpdateRequest
2. php artisan make:request Admin/ProfessorAssignmentRequest
3. php artisan make:request Admin/Gym/ExerciseRequest
4. php artisan make:request Admin/Gym/DailyTemplateRequest
5. php artisan make:request Admin/Gym/WeeklyTemplateRequest
6. php artisan make:request Admin/Gym/WeeklyAssignmentRequest

Ejemplo de ExerciseRequest:
public function rules()
{
    return [
        'name' => 'required|string|max:255',
        'muscle_group' => 'required|string|in:chest,back,legs,shoulders,arms,core',
        'movement_pattern' => 'required|string',
        'equipment' => 'required|string',
        'difficulty' => 'required|in:beginner,intermediate,advanced',
        'tags' => 'array',
        'tags.*' => 'string',
        'instructions' => 'required|string',
        'tempo' => 'nullable|string',
        'video_url' => 'nullable|url',
        'image_url' => 'nullable|url'
    ];
}
```

**VERIFICACIÓN:**
- [ ] UserUpdateRequest creado con validaciones
- [ ] ProfessorAssignmentRequest creado
- [ ] ExerciseRequest creado con reglas completas
- [ ] DailyTemplateRequest creado
- [ ] WeeklyTemplateRequest creado
- [ ] WeeklyAssignmentRequest creado

---

### **TAREA 2.3: Crear Services Faltantes**

**ACCIÓN REQUERIDA:**
```
Crea los Services necesarios en /app/Services/Admin/:

1. UserManagementService.php - Lógica de gestión de usuarios
2. ProfessorService.php - Lógica de gestión de profesores
3. AuditService.php - Sistema de auditoría
4. ExerciseService.php - Lógica de ejercicios
5. TemplateService.php - Lógica de plantillas
6. AssignmentService.php - Lógica de asignaciones

Ejemplo de AuditService:
<?php
namespace App\Services\Admin;
use App\Models\AuditLog;

class AuditService
{
    public function log(string $action, string $resourceType, ?int $resourceId = null, ?array $details = null, string $severity = 'low'): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'severity' => $severity,
        ]);
    }

    public function getFilteredLogs(array $filters)
    {
        $query = AuditLog::with('user');
        
        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }
        
        if (isset($filters['action'])) {
            $query->byAction($filters['action']);
        }
        
        if (isset($filters['severity'])) {
            $query->bySeverity($filters['severity']);
        }
        
        return $query->latest()->paginate(20);
    }
}
```

**VERIFICACIÓN:**
- [ ] UserManagementService creado
- [ ] ProfessorService creado
- [ ] AuditService creado con métodos log y getFilteredLogs
- [ ] ExerciseService creado
- [ ] TemplateService creado
- [ ] AssignmentService creado

---

## 🧪 **FASE 3: TESTING Y VALIDACIÓN**

### **TAREA 3.1: Probar Endpoints Críticos**

**ACCIÓN REQUERIDA:**
```
Prueba estos endpoints usando Postman o similar:

1. Panel Admin:
   GET /api/admin/users - Lista de usuarios
   GET /api/admin/professors - Lista de profesores
   GET /api/admin/audit - Logs de auditoría

2. Panel Gimnasio:
   GET /api/admin/gym/exercises - Lista de ejercicios
   GET /api/admin/gym/daily-templates - Plantillas diarias
   GET /api/admin/gym/weekly-assignments - Asignaciones

Verifica que:
- Responden con status 200
- Retornan JSON válido
- Aplican filtros correctamente
- Paginación funciona
- Middleware de seguridad funciona (403 sin permisos)
```

**VERIFICACIÓN:**
- [ ] Endpoints admin funcionan
- [ ] Endpoints gimnasio funcionan
- [ ] Filtros aplicados correctamente
- [ ] Paginación operativa
- [ ] Seguridad funcionando

---

### **TAREA 3.2: Verificar Base de Datos**

**ACCIÓN REQUERIDA:**
```
Ejecuta estas queries para verificar la estructura:

1. DESCRIBE users; - Verifica nuevos campos admin
2. SHOW TABLES; - Verifica que existan system_settings y audit_logs
3. DESCRIBE system_settings; - Verifica estructura
4. DESCRIBE audit_logs; - Verifica estructura
5. SHOW INDEX FROM users; - Verifica índices
6. SHOW INDEX FROM audit_logs; - Verifica índices
```

**VERIFICACIÓN:**
- [ ] Tabla users tiene campos admin
- [ ] Tabla system_settings existe
- [ ] Tabla audit_logs existe
- [ ] Índices configurados correctamente
- [ ] Relaciones funcionando

---

## 📊 **REPORTE FINAL**

### **TEMPLATE DE REPORTE:**
```
FECHA: [Fecha actual]
AUDITOR: [Tu nombre]

ESTADO GENERAL:
- Rutas: ✅/⚠️/❌
- Middleware: ✅/⚠️/❌
- Controllers: ✅/⚠️/❌
- Requests: ✅/⚠️/❌
- Services: ✅/⚠️/❌
- Models: ✅/⚠️/❌
- Migrations: ✅/⚠️/❌

FUNCIONALIDADES CRÍTICAS:
- Panel Admin: ✅/⚠️/❌
- Panel Gimnasio: ✅/⚠️/❌
- Sistema Auditoría: ✅/⚠️/❌
- Seguridad: ✅/⚠️/❌

PROBLEMAS PENDIENTES:
[Lista de problemas no resueltos]

PRÓXIMOS PASOS:
[Acciones requeridas]

COMPLETITUD ESTIMADA: [X]%
```

---

## 🎯 **INSTRUCCIONES DE USO**

**Para usar este prompt:**

1. **Copia la sección que necesites** (Fase 1, 2 o 3)
2. **Pégala en una nueva conversación** con el asistente
3. **Agrega el contexto:** "Estoy trabajando en el proyecto Villa Mitre..."
4. **Ejecuta las tareas** en el orden indicado
5. **Verifica cada checklist** antes de continuar
6. **Reporta el progreso** usando el template final

**Tiempo estimado:**
- Fase 1 (Crítica): 2-4 horas
- Fase 2 (Detallada): 4-6 horas  
- Fase 3 (Testing): 1-2 horas
- **Total: 7-12 horas**

**Prioridad de ejecución:**
1. **FASE 1 COMPLETA** (sin esto el sistema no funciona)
2. **FASE 2** (mejora la robustez)
3. **FASE 3** (asegura calidad)

---

**NOTA IMPORTANTE:** Este prompt está diseñado para ser usado paso a paso. No intentes hacer todo de una vez. Completa cada fase antes de continuar a la siguiente.
