# Panel Villa Mitre - Especificaciones Técnicas

## 🏛️ **Panel de Administradores del Club**

### **Dashboard Principal**
**Ruta:** `/admin/dashboard`
**Acceso:** Solo administradores (`role: 'admin'` o `is_super_admin: true`)

**Métricas mostradas:**
- Total de usuarios registrados
- Profesores activos
- Alumnos con rutinas asignadas
- Uso del sistema (sesiones, API calls)
- Estado de sincronización con API externa

**Componentes:**
```typescript
interface AdminDashboard {
  userStats: {
    total_users: number;
    active_professors: number;
    students_with_routines: number;
    new_users_this_week: number;
  };
  systemHealth: {
    api_status: 'healthy' | 'degraded' | 'down';
    last_sync: string;
    pending_promotions: number;
    error_rate: number;
  };
  gymUsage: {
    active_assignments: number;
    templates_created: number;
    adherence_average: number;
  };
}
```

## 👥 **Gestión de Usuarios**

### **Lista de Usuarios**
**Ruta:** `/admin/users`
**Endpoint:** `GET /api/admin/users`

**Funcionalidades:**
- ✅ Listado paginado con búsqueda avanzada
- ✅ Filtros por tipo, estado, rol
- ✅ Ordenamiento por múltiples campos
- ✅ Acciones masivas (exportar, cambiar estado)
- ✅ Vista detallada de usuario

**Componente Table:**
```typescript
interface UserTableRow {
  id: number;
  name: string;
  dni: string;
  email?: string;
  user_type: 'local' | 'api';
  type_label: string;
  is_professor: boolean;
  estado_socio?: string;
  semaforo?: number;
  last_login?: string;
  created_at: string;
  actions: React.ReactNode;
}

interface UserFilters {
  search: string;              // Busca en nombre, DNI, email
  user_type: ('local' | 'api')[];
  is_professor: boolean | null;
  estado_socio: string[];
  semaforo: number[];
  date_from: string;
  date_to: string;
  has_gym_access: boolean | null;
}

interface UserActions {
  view: (userId: number) => void;
  edit: (userId: number) => void;
  toggleProfessor: (userId: number) => void;
  resetPassword: (userId: number) => void;
  viewActivity: (userId: number) => void;
  delete: (userId: number) => void;
}
```

### **Detalle de Usuario**
**Ruta:** `/admin/users/:id`
**Endpoint:** `GET /api/admin/users/:id`

**Información mostrada:**
```typescript
interface UserDetail {
  basicInfo: {
    id: number;
    name: string;
    dni: string;
    email?: string;
    avatar_url?: string;
    user_type: 'local' | 'api';
    created_at: string;
    last_login?: string;
  };
  
  clubInfo?: {
    socio_id?: number;
    socio_n?: string;
    categoria?: string;
    estado_socio?: string;
    semaforo?: number;
    saldo?: number;
    barcode?: string;
    api_updated_at?: string;
  };
  
  systemRoles: {
    is_professor: boolean;
    professor_since?: string;
    is_admin: boolean;
    permissions: string[];
  };
  
  gymActivity?: {
    templates_created: number;
    students_assigned: number;
    last_assignment: string;
    total_assignments: number;
  };
  
  activityLog: {
    logins: number;
    api_calls: number;
    last_activity: string;
    devices: string[];
  };
}
```

### **Editar Usuario**
**Ruta:** `/admin/users/:id/edit`
**Endpoints:** `PUT /api/admin/users/:id`

**Formulario por secciones:**

**Sección 1: Información Básica**
```typescript
interface UserBasicEdit {
  name: string;
  email?: string;
  phone?: string;
  // DNI no editable para usuarios API
  // user_type no editable (requiere proceso especial)
}
```

**Sección 2: Roles y Permisos**
```typescript
interface UserRolesEdit {
  is_professor: boolean;
  is_admin: boolean;
  permissions: {
    gym_admin: boolean;
    user_management: boolean;
    system_settings: boolean;
    reports_access: boolean;
  };
  notes?: string; // Notas internas del admin
}
```

**Sección 3: Configuración de Acceso**
```typescript
interface UserAccessEdit {
  account_status: 'active' | 'suspended' | 'pending';
  password_reset_required: boolean;
  two_factor_enabled: boolean;
  session_timeout: number; // minutos
  allowed_ips?: string[]; // IPs permitidas (opcional)
}
```

## 👨‍🏫 **Gestión de Profesores**

### **Lista de Profesores**
**Ruta:** `/admin/professors`
**Endpoint:** `GET /api/admin/users?is_professor=true`

**Vista especializada:**
```typescript
interface ProfessorCard {
  id: number;
  name: string;
  avatar_url?: string;
  email?: string;
  dni: string;
  
  stats: {
    students_count: number;
    active_assignments: number;
    templates_created: number;
    avg_adherence: number;
  };
  
  status: {
    is_active: boolean;
    last_login: string;
    account_status: 'active' | 'suspended';
  };
  
  specialties: string[]; // Tags de especialidad
  
  actions: {
    viewProfile: () => void;
    viewStudents: () => void;
    viewTemplates: () => void;
    editRoles: () => void;
    suspend: () => void;
  };
}
```

### **Asignar Rol de Profesor**
**Ruta:** `/admin/professors/assign`
**Endpoint:** `POST /api/admin/users/:id/assign-professor`

**Proceso de asignación:**
```typescript
interface ProfessorAssignment {
  user_id: number;
  
  qualifications: {
    education: string;
    certifications: string[];
    experience_years: number;
    specialties: string[];
  };
  
  permissions: {
    can_create_templates: boolean;
    can_assign_routines: boolean;
    can_view_all_students: boolean;
    can_export_data: boolean;
    max_students: number; // límite de alumnos
  };
  
  schedule?: {
    available_days: number[]; // 1-7
    start_time: string;
    end_time: string;
    timezone: string;
  };
  
  notes: string; // Notas del administrador
}

// Wizard de 3 pasos
interface ProfessorAssignmentWizard {
  step1: UserSelection;
  step2: QualificationsForm;
  step3: PermissionsConfig;
}
```

### **Gestión de Estudiantes por Profesor**
**Ruta:** `/admin/professors/:id/students`
**Endpoint:** `GET /api/admin/professors/:id/students`

**Funcionalidades:**
- Ver todos los alumnos asignados a un profesor
- Reasignar alumnos a otro profesor
- Ver historial de asignaciones
- Métricas de rendimiento por alumno

```typescript
interface ProfessorStudents {
  professor: User;
  students: {
    id: number;
    name: string;
    avatar_url?: string;
    assigned_since: string;
    current_assignment?: Assignment;
    adherence_rate: number;
    last_activity: string;
    status: 'active' | 'inactive' | 'completed';
  }[];
  
  actions: {
    reassignStudent: (studentId: number, newProfessorId: number) => void;
    viewAssignmentHistory: (studentId: number) => void;
    removeStudent: (studentId: number) => void;
  };
}
```

## ⚙️ **Configuración del Sistema**

### **Configuración General**
**Ruta:** `/admin/settings`
**Endpoint:** `GET/PUT /api/admin/settings`

**Secciones de configuración:**

**API Externa:**
```typescript
interface ExternalApiSettings {
  socios_api: {
    base_url: string;
    login: string;
    token: string;
    timeout: number;
    verify_ssl: boolean;
    retry_attempts: number;
  };
  
  sync_settings: {
    auto_sync_enabled: boolean;
    sync_interval_hours: number;
    sync_time: string; // HH:MM
    batch_size: number;
  };
  
  image_settings: {
    base_url: string;
    timeout: number;
    cache_duration: number;
  };
}
```

**Sistema de Usuarios:**
```typescript
interface UserSystemSettings {
  registration: {
    allow_self_registration: boolean;
    require_email_verification: boolean;
    default_user_type: 'local' | 'api';
    auto_promote_to_api: boolean;
  };
  
  authentication: {
    session_timeout: number;
    max_login_attempts: number;
    lockout_duration: number;
    require_strong_passwords: boolean;
    two_factor_required: boolean;
  };
  
  professor_management: {
    auto_assign_permissions: boolean;
    max_students_per_professor: number;
    require_qualifications: boolean;
    approval_required: boolean;
  };
}
```

**Gimnasio:**
```typescript
interface GymSystemSettings {
  templates: {
    allow_public_templates: boolean;
    max_templates_per_professor: number;
    require_approval_for_sharing: boolean;
  };
  
  assignments: {
    max_concurrent_assignments: number;
    allow_overlapping_weeks: boolean;
    auto_archive_completed: boolean;
    archive_after_days: number;
  };
  
  notifications: {
    send_assignment_notifications: boolean;
    send_adherence_alerts: boolean;
    weekly_report_enabled: boolean;
    notification_methods: ('email' | 'push' | 'sms')[];
  };
}
```

### **Gestión de Permisos**
**Ruta:** `/admin/permissions`

**Sistema de roles y permisos:**
```typescript
interface Permission {
  id: string;
  name: string;
  description: string;
  category: 'users' | 'gym' | 'system' | 'reports';
  level: 'read' | 'write' | 'admin';
}

interface Role {
  id: string;
  name: string;
  description: string;
  permissions: Permission[];
  is_system_role: boolean; // No editable
  users_count: number;
}

// Roles predefinidos
const SYSTEM_ROLES = {
  SUPER_ADMIN: 'super_admin',
  ADMIN: 'admin', 
  PROFESSOR: 'professor',
  STUDENT: 'student',
  VIEWER: 'viewer'
};
```

## 📊 **Reportes y Auditoría**

### **Dashboard de Reportes**
**Ruta:** `/admin/reports`

**Reportes disponibles:**

**Uso del Sistema:**
```typescript
interface SystemUsageReport {
  period: 'daily' | 'weekly' | 'monthly';
  metrics: {
    active_users: number;
    api_calls: number;
    login_sessions: number;
    error_rate: number;
    response_time_avg: number;
  };
  charts: {
    user_activity: ChartData;
    api_usage: ChartData;
    error_trends: ChartData;
  };
}
```

**Actividad de Profesores:**
```typescript
interface ProfessorActivityReport {
  professors: {
    id: number;
    name: string;
    students_assigned: number;
    templates_created: number;
    assignments_this_month: number;
    avg_student_adherence: number;
    last_activity: string;
  }[];
  
  summary: {
    total_professors: number;
    active_professors: number;
    avg_students_per_professor: number;
    total_assignments: number;
  };
}
```

**Sincronización API:**
```typescript
interface ApiSyncReport {
  last_sync: {
    timestamp: string;
    duration: number;
    users_updated: number;
    errors: number;
    status: 'success' | 'partial' | 'failed';
  };
  
  sync_history: {
    date: string;
    users_processed: number;
    users_updated: number;
    users_created: number;
    errors: number;
    duration: number;
  }[];
  
  error_log: {
    timestamp: string;
    user_dni: string;
    error_type: string;
    error_message: string;
    resolved: boolean;
  }[];
}
```

### **Auditoría de Acciones**
**Ruta:** `/admin/audit`
**Endpoint:** `GET /api/admin/audit-log`

**Log de auditoría:**
```typescript
interface AuditLogEntry {
  id: number;
  user_id: number;
  user_name: string;
  action: string;
  resource_type: 'user' | 'professor' | 'assignment' | 'template' | 'system';
  resource_id?: number;
  details: Record<string, any>;
  ip_address: string;
  user_agent: string;
  timestamp: string;
  
  // Campos calculados
  severity: 'low' | 'medium' | 'high' | 'critical';
  category: 'auth' | 'data' | 'system' | 'security';
}

interface AuditFilters {
  user_id?: number;
  action?: string;
  resource_type?: string;
  severity?: string;
  date_from: string;
  date_to: string;
  ip_address?: string;
}
```

## 🔧 **Herramientas de Administración**

### **Sincronización Manual**
**Ruta:** `/admin/tools/sync`

**Herramientas de sincronización:**
```typescript
interface SyncTools {
  fullSync: {
    description: 'Sincronización completa de todos los usuarios';
    estimated_duration: string;
    last_run: string;
    onExecute: () => Promise<void>;
  };
  
  partialSync: {
    description: 'Sincronizar usuarios específicos';
    user_selection: number[];
    onExecute: (userIds: number[]) => Promise<void>;
  };
  
  validateData: {
    description: 'Validar integridad de datos';
    checks: ('duplicates' | 'missing_fields' | 'invalid_references')[];
    onExecute: () => Promise<ValidationReport>;
  };
}
```

### **Mantenimiento del Sistema**
**Ruta:** `/admin/tools/maintenance`

**Herramientas de mantenimiento:**
```typescript
interface MaintenanceTools {
  cacheManagement: {
    clear_all: () => Promise<void>;
    clear_user_cache: () => Promise<void>;
    clear_api_cache: () => Promise<void>;
    view_cache_stats: () => Promise<CacheStats>;
  };
  
  databaseOptimization: {
    analyze_tables: () => Promise<void>;
    optimize_tables: () => Promise<void>;
    cleanup_old_logs: (days: number) => Promise<void>;
    backup_database: () => Promise<string>; // Returns backup file path
  };
  
  systemHealth: {
    check_api_connectivity: () => Promise<HealthCheck>;
    check_database_performance: () => Promise<PerformanceReport>;
    check_disk_space: () => Promise<DiskUsage>;
    check_memory_usage: () => Promise<MemoryUsage>;
  };
}
```

## 🚨 **Monitoreo y Alertas**

### **Dashboard de Monitoreo**
**Ruta:** `/admin/monitoring`

**Métricas en tiempo real:**
```typescript
interface MonitoringDashboard {
  realTimeMetrics: {
    active_sessions: number;
    api_requests_per_minute: number;
    error_rate: number;
    response_time: number;
    database_connections: number;
  };
  
  alerts: {
    id: string;
    type: 'error' | 'warning' | 'info';
    message: string;
    timestamp: string;
    resolved: boolean;
    actions?: AlertAction[];
  }[];
  
  systemStatus: {
    api_external: 'healthy' | 'degraded' | 'down';
    database: 'healthy' | 'slow' | 'down';
    cache: 'healthy' | 'degraded' | 'down';
    storage: 'healthy' | 'full' | 'error';
  };
}
```

### **Configuración de Alertas**
```typescript
interface AlertConfiguration {
  error_rate_threshold: number; // %
  response_time_threshold: number; // ms
  failed_logins_threshold: number;
  disk_usage_threshold: number; // %
  
  notification_channels: {
    email: {
      enabled: boolean;
      recipients: string[];
    };
    slack: {
      enabled: boolean;
      webhook_url: string;
    };
    sms: {
      enabled: boolean;
      phone_numbers: string[];
    };
  };
}
```

## 🎨 **Componentes UI Específicos**

### **UserSelector con Búsqueda Avanzada**
```typescript
interface AdvancedUserSelectorProps {
  users: User[];
  selected: number[];
  onSelectionChange: (selected: number[]) => void;
  filters: UserFilters;
  multiSelect?: boolean;
  showUserType?: boolean;
  showProfessorBadge?: boolean;
}
```

### **PermissionMatrix**
```typescript
interface PermissionMatrixProps {
  roles: Role[];
  permissions: Permission[];
  onChange: (roleId: string, permissionId: string, granted: boolean) => void;
  readonly?: boolean;
}
```

### **SystemHealthIndicator**
```typescript
interface SystemHealthIndicatorProps {
  status: 'healthy' | 'degraded' | 'down';
  metrics: HealthMetrics;
  onRefresh: () => void;
  showDetails?: boolean;
}
```

### **AuditLogViewer**
```typescript
interface AuditLogViewerProps {
  entries: AuditLogEntry[];
  filters: AuditFilters;
  onFiltersChange: (filters: AuditFilters) => void;
  onExport: (format: 'csv' | 'json') => void;
}
```
