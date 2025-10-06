# Backend Requirements - Panel de Administración

## 🔧 **Nuevos Endpoints Requeridos**

### **Gestión de Usuarios (Admin)**

#### **Lista de Usuarios con Filtros Avanzados**
```php
GET /api/admin/users
```
**Query Parameters:**
```php
?search=string           // Busca en name, dni, email
&user_type[]=local      // Filtro por tipo
&user_type[]=api
&is_professor=boolean   // Solo profesores
&estado_socio[]=ACTIVO  // Estado de socio
&semaforo[]=1          // Semáforo (1=verde, 2=amarillo, 3=rojo)
&date_from=2025-01-01  // Fecha creación desde
&date_to=2025-12-31    // Fecha creación hasta
&has_gym_access=boolean // Tiene acceso al gimnasio
&page=1                // Paginación
&per_page=20           // Elementos por página
&sort_by=name          // Ordenar por campo
&sort_direction=asc    // Dirección ordenamiento
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "USER, TEST",
      "dni": "12345678",
      "email": "test@example.com",
      "user_type": "api",
      "type_label": "Usuario API",
      "is_professor": true,
      "estado_socio": "ACTIVO",
      "semaforo": 1,
      "last_login": "2025-09-18T10:30:00Z",
      "created_at": "2025-09-01T00:00:00Z",
      "gym_stats": {
        "templates_created": 5,
        "students_assigned": 12,
        "last_assignment": "2025-09-15T00:00:00Z"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  },
  "filters_summary": {
    "total_users": 150,
    "professors": 8,
    "api_users": 120,
    "local_users": 30,
    "active_socios": 110
  }
}
```

#### **Detalle Completo de Usuario**
```php
GET /api/admin/users/{id}
```
**Response:**
```json
{
  "user": {
    "id": 1,
    "basic_info": {
      "name": "USER, TEST",
      "dni": "12345678",
      "email": "test@example.com",
      "avatar_url": "https://...",
      "user_type": "api",
      "created_at": "2025-09-01T00:00:00Z",
      "last_login": "2025-09-18T10:30:00Z"
    },
    "club_info": {
      "socio_id": 12345,
      "socio_n": "12345",
      "categoria": "ACTIVO",
      "estado_socio": "ACTIVO",
      "semaforo": 1,
      "saldo": 0.00,
      "barcode": "12345678",
      "api_updated_at": "2025-09-18T00:00:00Z"
    },
    "system_roles": {
      "is_professor": true,
      "professor_since": "2025-09-01T00:00:00Z",
      "is_admin": false,
      "permissions": ["gym_admin", "create_templates"]
    },
    "gym_activity": {
      "templates_created": 5,
      "students_assigned": 12,
      "total_assignments": 45,
      "last_assignment": "2025-09-15T00:00:00Z",
      "avg_student_adherence": 78.5
    },
    "activity_log": {
      "total_logins": 156,
      "api_calls_this_month": 2340,
      "last_activity": "2025-09-18T10:30:00Z",
      "devices": ["Chrome/Windows", "Mobile App"]
    }
  }
}
```

#### **Actualizar Usuario (Admin)**
```php
PUT /api/admin/users/{id}
```
**Request:**
```json
{
  "basic_info": {
    "name": "Nuevo Nombre",
    "email": "nuevo@email.com",
    "phone": "+54911234567"
  },
  "roles": {
    "is_professor": true,
    "is_admin": false,
    "permissions": ["gym_admin", "create_templates"]
  },
  "access_config": {
    "account_status": "active",
    "password_reset_required": false,
    "session_timeout": 480
  },
  "notes": "Notas internas del administrador"
}
```

### **Gestión de Profesores**

#### **Asignar Rol de Profesor**
```php
POST /api/admin/users/{id}/assign-professor
```
**Request:**
```json
{
  "qualifications": {
    "education": "Licenciado en Educación Física",
    "certifications": ["Personal Trainer", "Functional Training"],
    "experience_years": 5,
    "specialties": ["strength", "hypertrophy", "rehabilitation"]
  },
  "permissions": {
    "can_create_templates": true,
    "can_assign_routines": true,
    "can_view_all_students": false,
    "max_students": 50
  },
  "notes": "Profesor especializado en fuerza"
}
```

#### **Lista de Profesores con Estadísticas**
```php
GET /api/admin/professors
```
**Response:**
```json
{
  "professors": [
    {
      "id": 1,
      "name": "USER, TEST",
      "email": "test@example.com",
      "dni": "12345678",
      "avatar_url": "https://...",
      "stats": {
        "students_count": 12,
        "active_assignments": 8,
        "templates_created": 5,
        "avg_adherence": 78.5
      },
      "status": {
        "is_active": true,
        "last_login": "2025-09-18T10:30:00Z",
        "account_status": "active"
      },
      "specialties": ["strength", "hypertrophy"],
      "professor_since": "2025-09-01T00:00:00Z"
    }
  ]
}
```

#### **Estudiantes de un Profesor**
```php
GET /api/admin/professors/{id}/students
```
**Response:**
```json
{
  "professor": {
    "id": 1,
    "name": "USER, TEST"
  },
  "students": [
    {
      "id": 2,
      "name": "ALUMNO, PRUEBA",
      "avatar_url": "https://...",
      "assigned_since": "2025-09-01T00:00:00Z",
      "current_assignment": {
        "id": 15,
        "week_start": "2025-09-16",
        "week_end": "2025-09-22"
      },
      "adherence_rate": 85.0,
      "last_activity": "2025-09-17T18:00:00Z",
      "status": "active"
    }
  ]
}
```

### **Configuración del Sistema**

#### **Obtener Configuración**
```php
GET /api/admin/settings
```
**Response:**
```json
{
  "external_api": {
    "socios_api_base": "https://clubvillamitre.com/api_back_socios",
    "sync_interval_hours": 24,
    "auto_sync_enabled": true,
    "last_sync": "2025-09-18T02:00:00Z"
  },
  "user_system": {
    "allow_self_registration": false,
    "require_email_verification": true,
    "session_timeout": 480,
    "max_login_attempts": 5
  },
  "gym_system": {
    "max_templates_per_professor": 100,
    "max_students_per_professor": 50,
    "auto_archive_after_days": 90
  }
}
```

#### **Actualizar Configuración**
```php
PUT /api/admin/settings
```

### **Reportes y Auditoría**

#### **Reporte de Uso del Sistema**
```php
GET /api/admin/reports/system-usage
```
**Query Parameters:**
```php
?period=weekly          // daily, weekly, monthly
&date_from=2025-09-01
&date_to=2025-09-18
```

#### **Log de Auditoría**
```php
GET /api/admin/audit-log
```
**Query Parameters:**
```php
?user_id=1
&action=login
&resource_type=user
&severity=high
&date_from=2025-09-01
&date_to=2025-09-18
&page=1
&per_page=50
```

### **Herramientas de Administración**

#### **Sincronización Manual**
```php
POST /api/admin/tools/sync
```
**Request:**
```json
{
  "type": "full",           // full, partial, validate
  "user_ids": [1, 2, 3],   // Solo para type=partial
  "force": false           // Forzar sincronización
}
```

#### **Estado del Sistema**
```php
GET /api/admin/system/health
```
**Response:**
```json
{
  "status": "healthy",
  "checks": {
    "database": {
      "status": "healthy",
      "response_time": 15,
      "connections": 5
    },
    "external_api": {
      "status": "healthy",
      "last_check": "2025-09-18T12:00:00Z",
      "response_time": 250
    },
    "cache": {
      "status": "healthy",
      "hit_rate": 95.5,
      "memory_usage": 45.2
    }
  },
  "metrics": {
    "active_sessions": 23,
    "api_requests_per_minute": 45,
    "error_rate": 0.1,
    "disk_usage": 67.8
  }
}
```

## 🔐 **Middleware y Permisos**

### **Middleware de Administrador**
```php
// app/Http/Middleware/EnsureAdmin.php
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'Forbidden: admin role required.'
            ], 403);
        }
        
        return $next($request);
    }
}
```

### **Sistema de Permisos**
```php
// app/Models/User.php - Agregar métodos
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
```

## 📊 **Nuevos Modelos y Migraciones**

### **Tabla de Configuración del Sistema**
```php
// Migration: create_system_settings_table
Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->json('value');
    $table->string('category'); // api, users, gym, system
    $table->text('description')->nullable();
    $table->boolean('is_public')->default(false);
    $table->timestamps();
});
```

### **Tabla de Log de Auditoría**
```php
// Migration: create_audit_logs_table
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action'); // login, create, update, delete, etc.
    $table->string('resource_type'); // user, assignment, template, etc.
    $table->unsignedBigInteger('resource_id')->nullable();
    $table->json('details')->nullable(); // Datos adicionales
    $table->ipAddress('ip_address');
    $table->text('user_agent')->nullable();
    $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
    $table->timestamps();
    
    $table->index(['user_id', 'created_at']);
    $table->index(['action', 'created_at']);
    $table->index(['resource_type', 'resource_id']);
});
```

### **Tabla de Permisos de Usuario**
```php
// Migration: add_admin_fields_to_users_table
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false)->after('is_professor');
    $table->json('permissions')->nullable()->after('is_admin');
    $table->text('admin_notes')->nullable()->after('permissions');
    $table->enum('account_status', ['active', 'suspended', 'pending'])->default('active');
    $table->timestamp('professor_since')->nullable();
    $table->integer('session_timeout')->default(480); // minutos
    
    $table->index('is_admin');
    $table->index('account_status');
});
```

## 🔄 **Servicios y Jobs**

### **Servicio de Auditoría**
```php
// app/Services/AuditService.php
class AuditService
{
    public function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?array $details = null,
        string $severity = 'low'
    ): void {
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
}
```

### **Job de Sincronización**
```php
// app/Jobs/SyncUsersJob.php
class SyncUsersJob implements ShouldQueue
{
    public function __construct(
        private array $userIds = [],
        private bool $force = false
    ) {}
    
    public function handle(): void
    {
        // Lógica de sincronización con API externa
        // Log de progreso y errores
        // Notificación de completitud
    }
}
```

## 📈 **Métricas y Monitoreo**

### **Collector de Métricas**
```php
// app/Services/MetricsService.php
class MetricsService
{
    public function getSystemMetrics(): array
    {
        return [
            'active_sessions' => $this->getActiveSessions(),
            'api_requests_per_minute' => $this->getApiRequestsPerMinute(),
            'error_rate' => $this->getErrorRate(),
            'database_performance' => $this->getDatabasePerformance(),
        ];
    }
    
    public function getUserStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_professors' => User::where('is_professor', true)->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
        ];
    }
}
```

## 🚨 **Sistema de Alertas**

### **Configuración de Alertas**
```php
// app/Services/AlertService.php
class AlertService
{
    public function checkSystemHealth(): void
    {
        $metrics = app(MetricsService::class)->getSystemMetrics();
        
        if ($metrics['error_rate'] > config('alerts.error_rate_threshold')) {
            $this->sendAlert('high_error_rate', $metrics);
        }
        
        if ($metrics['response_time'] > config('alerts.response_time_threshold')) {
            $this->sendAlert('slow_response', $metrics);
        }
    }
    
    private function sendAlert(string $type, array $data): void
    {
        // Enviar notificación por email, Slack, etc.
    }
}
```

## 🔧 **Comandos Artisan**

### **Comando de Gestión de Administradores**
```php
// app/Console/Commands/MakeAdmin.php
class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {identifier : DNI o email} {--remove : Quitar rol admin}';
    
    public function handle(): int
    {
        $identifier = $this->argument('identifier');
        $remove = $this->option('remove');
        
        $user = User::where('dni', $identifier)
                   ->orWhere('email', $identifier)
                   ->first();
        
        if (!$user) {
            $this->error('Usuario no encontrado.');
            return self::FAILURE;
        }
        
        $user->is_admin = !$remove;
        $user->save();
        
        app(AuditService::class)->log(
            $remove ? 'admin_role_removed' : 'admin_role_assigned',
            'user',
            $user->id,
            ['by_command' => true]
        );
        
        $this->info(($remove ? 'Removido' : 'Asignado') . ' rol admin para: ' . $user->name);
        return self::SUCCESS;
    }
}
```

### **Comando de Limpieza de Logs**
```php
// app/Console/Commands/CleanupAuditLogs.php
class CleanupAuditLogs extends Command
{
    protected $signature = 'audit:cleanup {--days=90 : Días a mantener}';
    
    public function handle(): int
    {
        $days = $this->option('days');
        $deleted = AuditLog::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->info("Eliminados {$deleted} registros de auditoría anteriores a {$days} días.");
        return self::SUCCESS;
    }
}
```
