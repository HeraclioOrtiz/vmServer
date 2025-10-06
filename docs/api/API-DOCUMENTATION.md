# 📚 API Documentation - Villa Mitre Server

## 🔐 **Authentication Endpoints**

### **POST /api/auth/login**
Autentica un usuario en el sistema.

**Request Body:**
```json
{
  "dni": "12345678",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "dni": "12345678",
    "name": "Usuario Test",
    "email": "test@example.com",
    "user_type": "api",
    "type_label": "Usuario API",
    "is_professor": false,
    "is_admin": false,
    "permissions": [],
    "account_status": "active",
    "foto_url": "https://example.com/photo.jpg",
    "created_at": "2025-09-18T10:00:00.000000Z"
  },
  "fetched_from_api": false,
  "refreshed": false
}
```

**Error Responses:**
- `422 Unprocessable Entity`: Credenciales inválidas
- `500 Internal Server Error`: Error crítico del servidor

### **POST /api/auth/register**
Registra un nuevo usuario local.

**Request Body:**
```json
{
  "dni": "12345678",
  "name": "Nuevo Usuario",
  "email": "nuevo@example.com",
  "password": "password123",
  "phone": "011-1234-5678"
}
```

**Response (201 Created):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 2,
    "dni": "12345678",
    "name": "Nuevo Usuario",
    "user_type": "local",
    "promotion_status": "none"
  }
}
```

### **POST /api/auth/logout**
Cierra la sesión del usuario autenticado.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "message": "Logged out successfully"
}
```

### **GET /api/auth/me**
Obtiene información del usuario autenticado.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "dni": "12345678",
    "name": "Usuario Test",
    "is_professor": false,
    "is_admin": false,
    "permissions": []
  }
}
```

## 👨‍💼 **Admin Panel Endpoints**

### **User Management**

#### **GET /api/admin/users**
Lista usuarios con filtros avanzados.

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Query Parameters:**
```
?search=juan                    // Búsqueda por nombre, DNI, email
&user_type[]=api               // Filtro por tipo de usuario
&is_professor=true             // Solo profesores
&is_admin=false                // Excluir administradores
&estado_socio[]=ACTIVO         // Estado de socio
&semaforo[]=1                  // Semáforo de acceso
&account_status[]=active       // Estado de cuenta
&date_from=2025-01-01          // Fecha de registro desde
&date_to=2025-12-31            // Fecha de registro hasta
&has_gym_access=true           // Con acceso al gimnasio
&sort_by=created_at            // Campo de ordenamiento
&sort_direction=desc           // Dirección de ordenamiento
&per_page=20                   // Elementos por página
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Usuario Test",
      "dni": "12345678",
      "email": "test@example.com",
      "user_type": "api",
      "is_professor": false,
      "is_admin": false,
      "account_status": "active",
      "created_at": "2025-09-18T10:00:00.000000Z",
      "gym_stats": {
        "students_count": 5,
        "templates_created": 10,
        "active_assignments": 3
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
    "professors": 12,
    "admins": 3,
    "active_socios": 120
  }
}
```

#### **GET /api/admin/users/{id}**
Obtiene información detallada de un usuario.

**Response (200 OK):**
```json
{
  "user": {
    "basic_info": {
      "id": 1,
      "name": "Usuario Test",
      "display_name": "Test, Usuario",
      "dni": "12345678",
      "email": "test@example.com"
    },
    "club_info": {
      "socio_id": 12345,
      "categoria": "Activo",
      "estado_socio": "ACTIVO",
      "semaforo": 1,
      "saldo": 1500.50
    },
    "system_roles": {
      "is_professor": false,
      "is_admin": false,
      "permissions": [],
      "account_status": "active"
    },
    "gym_activity": null,
    "activity_log": [],
    "admin_notes": null
  }
}
```

#### **POST /api/admin/users**
Crea un nuevo usuario.

**Request Body:**
```json
{
  "name": "Nuevo Usuario",
  "dni": "87654321",
  "email": "nuevo@example.com",
  "password": "password123",
  "user_type": "local",
  "is_professor": false,
  "is_admin": false,
  "account_status": "active"
}
```

#### **PUT /api/admin/users/{id}**
Actualiza un usuario existente.

#### **DELETE /api/admin/users/{id}**
Suspende un usuario (soft delete).

#### **POST /api/admin/users/{id}/assign-admin**
Asigna rol de administrador.

**Request Body:**
```json
{
  "permissions": [
    "user_management",
    "gym_admin",
    "reports_access"
  ]
}
```

#### **POST /api/admin/users/{id}/suspend**
Suspende un usuario.

**Request Body:**
```json
{
  "reason": "Violación de términos de uso"
}
```

### **Professor Management**

#### **GET /api/admin/professors**
Lista profesores con estadísticas.

**Response (200 OK):**
```json
{
  "professors": [
    {
      "id": 1,
      "name": "Prof. Juan Pérez",
      "email": "juan@example.com",
      "dni": "12345678",
      "professor_since": "2025-01-01T00:00:00.000000Z",
      "account_status": "active",
      "stats": {
        "students_count": 15,
        "active_assignments": 8,
        "templates_created": 25,
        "total_assignments": 45
      },
      "specialties": ["strength", "hypertrophy"],
      "permissions": []
    }
  ],
  "summary": {
    "total_professors": 5,
    "active_professors": 4,
    "total_students_assigned": 50,
    "total_active_assignments": 25
  }
}
```

#### **POST /api/admin/professors/{user_id}/assign**
Asigna rol de profesor a un usuario.

**Request Body:**
```json
{
  "qualifications": {
    "education": "Licenciado en Educación Física",
    "certifications": [
      "Certificación en Entrenamiento Funcional",
      "Curso de Nutrición Deportiva"
    ],
    "experience_years": 5,
    "specialties": ["strength", "hypertrophy", "functional"]
  },
  "permissions": {
    "can_create_templates": true,
    "can_assign_routines": true,
    "can_view_all_students": false,
    "max_students": 20
  },
  "schedule": {
    "available_days": [1, 2, 3, 4, 5],
    "start_time": "08:00",
    "end_time": "18:00"
  },
  "notes": "Especialista en entrenamiento de fuerza"
}
```

#### **GET /api/admin/professors/{id}/students**
Obtiene estudiantes asignados a un profesor.

### **Audit Logs**

#### **GET /api/admin/audit**
Lista logs de auditoría con filtros.

**Query Parameters:**
```
?user_id=1                     // Usuario específico
&action=login                  // Acción específica
&resource_type=user            // Tipo de recurso
&severity=high                 // Severidad
&category=user_management      // Categoría
&date_from=2025-09-01          // Fecha desde
&date_to=2025-09-18            // Fecha hasta
&search=texto                  // Búsqueda de texto
&per_page=50                   // Elementos por página
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "Admin User",
        "dni": "12345678"
      },
      "action": "login",
      "action_description": "Inicio de sesión",
      "action_icon": "login",
      "resource_type": "user",
      "resource_id": 1,
      "severity": "low",
      "severity_color": "green",
      "category": "auth",
      "ip_address": "192.168.1.100",
      "created_at": "2025-09-18T10:00:00.000000Z",
      "has_changes": false
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 1250,
    "last_page": 25
  }
}
```

#### **GET /api/admin/audit/stats**
Obtiene estadísticas de auditoría.

#### **POST /api/admin/audit/export**
Exporta logs de auditoría.

**Request Body:**
```json
{
  "format": "csv",
  "date_from": "2025-09-01",
  "date_to": "2025-09-18",
  "filters": {
    "severity": "high",
    "category": "user_management"
  }
}
```

## 🏋️ **Gym Panel Endpoints**

### **Exercise Management** (Profesores)

#### **GET /api/admin/gym/exercises**
Lista ejercicios con filtros.

**Headers:**
```
Authorization: Bearer {professor_token}
```

**Query Parameters:**
```
?q=press                       // Búsqueda por nombre, músculo o equipo
&muscle_group=chest            // Filtro por grupo muscular
&equipment=barbell             // Filtro por equipamiento
&per_page=20                   // Elementos por página (max: 100)
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Press de Banca",
      "muscle_group": "chest",
      "movement_pattern": "push",
      "equipment": "barbell",
      "difficulty": "intermediate",
      "tags": ["compound", "strength"],
      "instructions": "Acostarse en el banco...",
      "tempo": "3-1-1",
      "created_at": "2025-09-18T10:00:00Z",
      "updated_at": "2025-09-18T10:00:00Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/admin/gym/exercises?page=1",
    "last": "http://localhost:8000/api/admin/gym/exercises?page=8",
    "prev": null,
    "next": "http://localhost:8000/api/admin/gym/exercises?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 8,
    "per_page": 20,
    "to": 20,
    "total": 150
  }
}
```

#### **POST /api/admin/gym/exercises**
Crea un nuevo ejercicio.

**Request Body:**
```json
{
  "name": "Press de Banca",
  "muscle_group": "chest",
  "movement_pattern": "push",
  "equipment": "barbell",
  "difficulty": "intermediate",
  "tags": ["compound", "strength"],
  "instructions": "Descripción detallada del ejercicio...",
  "tempo": "3-1-1"
}
```

**Response (201 Created):**
```json
{
  "id": 1,
  "name": "Press de Banca",
  "muscle_group": "chest",
  "movement_pattern": "push",
  "equipment": "barbell",
  "difficulty": "intermediate",
  "tags": ["compound", "strength"],
  "instructions": "Descripción detallada del ejercicio...",
  "tempo": "3-1-1",
  "created_at": "2025-09-18T10:00:00Z",
  "updated_at": "2025-09-18T10:00:00Z"
}
```

#### **GET /api/admin/gym/exercises/{id}**
Obtiene un ejercicio específico.

#### **PUT /api/admin/gym/exercises/{id}**
Actualiza un ejercicio existente.

#### **DELETE /api/admin/gym/exercises/{id}**
Elimina un ejercicio.

**Response (204 No Content)**

### **Daily Templates Management**

#### **GET /api/admin/gym/daily-templates**
Lista plantillas diarias.

#### **POST /api/admin/gym/daily-templates**
Crea una nueva plantilla diaria.

#### **GET /api/admin/gym/daily-templates/{id}**
Obtiene una plantilla específica.

#### **PUT /api/admin/gym/daily-templates/{id}**
Actualiza una plantilla diaria.

#### **DELETE /api/admin/gym/daily-templates/{id}**
Elimina una plantilla diaria.

### **Weekly Templates Management**

#### **GET /api/admin/gym/weekly-templates**
Lista plantillas semanales.

#### **POST /api/admin/gym/weekly-templates**
Crea una nueva plantilla semanal.

#### **GET /api/admin/gym/weekly-templates/{id}**
Obtiene una plantilla semanal específica.

#### **PUT /api/admin/gym/weekly-templates/{id}**
Actualiza una plantilla semanal.

#### **DELETE /api/admin/gym/weekly-templates/{id}**
Elimina una plantilla semanal.

### **Weekly Assignments Management**

#### **GET /api/admin/gym/weekly-assignments**
Lista asignaciones semanales.

**Query Parameters:**
```
?user_id=1                     // Usuario específico
&from=2025-09-01               // Fecha desde
&to=2025-09-30                 // Fecha hasta
&created_by=1                  // Creado por profesor
&source_type=manual            // Tipo de fuente (template|manual|assistant)
&sort_by=week_start            // Campo de ordenamiento
&sort_direction=desc           // Dirección de ordenamiento
&per_page=20                   // Elementos por página
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "week_start": "2025-09-16",
      "week_end": "2025-09-22",
      "source_type": "manual",
      "weekly_template_id": null,
      "created_by": 1,
      "notes": "Rutina personalizada",
      "created_at": "2025-09-15T10:00:00Z",
      "updated_at": "2025-09-15T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "last_page": 3
  }
}
```

#### **POST /api/admin/gym/weekly-assignments**
Crea una nueva asignación semanal.

**Request Body:**
```json
{
  "user_id": 2,
  "week_start": "2025-09-16",
  "week_end": "2025-09-22",
  "source_type": "manual",
  "weekly_template_id": null,
  "notes": "Rutina personalizada para principiante",
  "days": [
    {
      "weekday": 1,
      "date": "2025-09-16",
      "title": "Entrenamiento de Pecho",
      "notes": "Enfoque en técnica",
      "exercises": [
        {
          "exercise_id": 1,
          "order": 1,
          "name": "Press de Banca",
          "muscle_group": "chest",
          "equipment": "barbell",
          "instructions": "Mantener la espalda pegada al banco",
          "tempo": "3-1-1",
          "notes": "Calentar bien antes",
          "sets": [
            {
              "set_number": 1,
              "reps_min": 8,
              "reps_max": 12,
              "rest_seconds": 90,
              "tempo": "3-1-1",
              "rpe_target": 7.5,
              "notes": "Serie de calentamiento"
            }
          ]
        }
      ]
    }
  ]
}
```

**Response (201 Created):**
```json
{
  "message": "Asignación semanal creada exitosamente.",
  "assignment": {
    "id": 1,
    "user_id": 2,
    "week_start": "2025-09-16",
    "week_end": "2025-09-22",
    "source_type": "manual",
    "created_by": 1,
    "notes": "Rutina personalizada para principiante",
    "created_at": "2025-09-15T10:00:00Z"
  }
}
```

#### **GET /api/admin/gym/weekly-assignments/{id}**
Obtiene una asignación específica con detalles completos.

#### **PUT /api/admin/gym/weekly-assignments/{id}**
Actualiza una asignación semanal.

#### **DELETE /api/admin/gym/weekly-assignments/{id}**
Elimina una asignación semanal.

### **Mobile Gym Endpoints** (Estudiantes)

#### **GET /api/gym/my-week**
Obtiene la rutina semanal del usuario autenticado.

**Headers:**
```
Authorization: Bearer {student_token}
```

**Query Parameters:**
```
?date=2025-09-18               // Fecha específica (opcional, default: hoy)
```

**Response (200 OK):**
```json
{
  "week_start": "2025-09-16",
  "week_end": "2025-09-22",
  "days": [
    {
      "weekday": 1,
      "date": "2025-09-16",
      "has_session": true,
      "title": "Entrenamiento de Pecho"
    },
    {
      "weekday": 2,
      "date": "2025-09-17",
      "has_session": true,
      "title": "Entrenamiento de Espalda"
    },
    {
      "weekday": 3,
      "date": "2025-09-18",
      "has_session": false,
      "title": null
    },
    {
      "weekday": 4,
      "date": "2025-09-19",
      "has_session": true,
      "title": "Entrenamiento de Piernas"
    },
    {
      "weekday": 5,
      "date": "2025-09-20",
      "has_session": false,
      "title": null
    },
    {
      "weekday": 6,
      "date": "2025-09-21",
      "has_session": true,
      "title": "Entrenamiento Full Body"
    },
    {
      "weekday": 7,
      "date": "2025-09-22",
      "has_session": false,
      "title": null
    }
  ]
}
```

**Response (Sin asignación):**
```json
{
  "week_start": null,
  "week_end": null,
  "days": []
}
```

#### **GET /api/gym/my-day**
Obtiene la rutina del día específico.

**Query Parameters:**
```
?date=2025-09-18               // Fecha específica (opcional, default: hoy)
```

**Response (200 OK):**
```json
{
  "title": "Entrenamiento de Pecho",
  "exercises": [
    {
      "name": "Press de Banca",
      "order": 1,
      "sets": [
        {
          "reps": "8-12",
          "rest_seconds": 90,
          "tempo": "3-1-1",
          "rpe_target": 7.5,
          "notes": "Serie de calentamiento"
        },
        {
          "reps": "6-8",
          "rest_seconds": 120,
          "tempo": "3-1-1",
          "rpe_target": 8.5,
          "notes": "Serie de trabajo"
        }
      ],
      "notes": "Mantener la espalda pegada al banco"
    },
    {
      "name": "Press Inclinado con Mancuernas",
      "order": 2,
      "sets": [
        {
          "reps": "10-15",
          "rest_seconds": 60,
          "tempo": null,
          "rpe_target": 7.0,
          "notes": null
        }
      ],
      "notes": "Controlar el movimiento"
    }
  ]
}
```

**Response (Sin rutina para el día):**
```json
{
  "title": null,
  "exercises": []
}
```

**Response (Sin asignación):**
```json
{
  "message": "No assignment found for date"
}
```

## 🔒 **Authentication & Authorization**

### **Token-Based Authentication**
Todos los endpoints protegidos requieren un token Bearer en el header:
```
Authorization: Bearer {token}
```

### **Role-Based Access Control**

#### **Admin Permissions:**
- `user_management`: Gestión de usuarios
- `gym_admin`: Administración del gimnasio
- `system_settings`: Configuración del sistema
- `reports_access`: Acceso a reportes
- `audit_logs`: Logs de auditoría
- `super_admin`: Permisos de super administrador

#### **Professor Permissions:**
- Acceso a endpoints `/api/admin/gym/*`
- Gestión de plantillas y asignaciones
- Vista de estudiantes asignados

#### **Student Access:**
- Acceso a endpoints `/api/gym/*`
- Solo datos propios del usuario

## 📊 **Response Formats**

### **Success Response:**
```json
{
  "data": {...},
  "message": "Operation successful",
  "meta": {...}
}
```

### **Error Response:**
```json
{
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  },
  "code": "ERROR_CODE"
}
```

### **Pagination Meta:**
```json
{
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8,
    "from": 1,
    "to": 20
  }
}
```

## 🚨 **Error Codes**

| Code | Status | Description |
|------|--------|-------------|
| `UNAUTHENTICATED` | 401 | Token inválido o expirado |
| `FORBIDDEN` | 403 | Sin permisos para la acción |
| `VALIDATION_ERROR` | 422 | Errores de validación |
| `NOT_FOUND` | 404 | Recurso no encontrado |
| `SERVER_ERROR` | 500 | Error interno del servidor |
| `RATE_LIMIT_EXCEEDED` | 429 | Límite de requests excedido |

## 🔧 **Development Notes**

### **Testing Endpoints:**
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"12345678","password":"password123"}'

# Get users (admin)
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer {token}"

# Get my workout (student)
curl -X GET http://localhost:8000/api/gym/my-day \
  -H "Authorization: Bearer {token}"
```

### **Rate Limiting:**
- Authentication endpoints: 5 requests per minute
- Admin endpoints: 60 requests per minute
- General endpoints: 100 requests per minute

### **API Versioning:**
Current version: `v1` (implicit)
Future versions will use `/api/v2/` prefix.
