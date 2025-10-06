# 🔒 PERMISOS DEL SISTEMA GIMNASIO

## ✅ REGLA DE NEGOCIO

**SOLO Profesores y Administradores pueden crear ejercicios, sets y rutinas diarias.**

---

## 🛡️ MIDDLEWARE IMPLEMENTADO

### **EnsureProfessor** (`app/Http/Middleware/EnsureProfessor.php`)

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    
    if (!$user || (!$user->is_professor && !$user->is_admin && !$user->is_super_admin)) {
        return response()->json([
            'message' => 'Forbidden: professor role required.'
        ], 403);
    }
    
    return $next($request);
}
```

**Permite acceso a:**
- ✅ Profesores (`is_professor = true`)
- ✅ Administradores (`is_admin = true`)
- ✅ Super Administradores (`is_super_admin = true`)

**Bloquea:**
- ❌ Estudiantes regulares
- ❌ Usuarios sin permisos
- ❌ Usuarios no autenticados

---

## 📋 RUTAS PROTEGIDAS

### **API Routes** (`routes/api.php`)

```php
// Admin Gym (protegido por rol 'profesor')
Route::prefix('admin/gym')->middleware('professor')->group(function () {
    Route::apiResource('exercises', GymExerciseController::class);
    Route::apiResource('daily-templates', GymDailyTemplateController::class);
    Route::apiResource('weekly-templates', GymWeeklyTemplateController::class);
    Route::apiResource('weekly-assignments', GymWeeklyAssignmentController::class);
});
```

### **Admin Routes** (`routes/admin.php`)

```php
Route::middleware(['auth:sanctum', 'professor'])->prefix('admin/gym')->group(function () {
    // Ejercicios
    Route::prefix('exercises')->group(function () {
        Route::get('/', [ExerciseController::class, 'index']);
        Route::post('/', [ExerciseController::class, 'store']);              // ✅ PROTEGIDO
        Route::put('/{exercise}', [ExerciseController::class, 'update']);    // ✅ PROTEGIDO
        Route::delete('/{exercise}', [ExerciseController::class, 'destroy']); // ✅ PROTEGIDO
        Route::post('/{exercise}/duplicate', [ExerciseController::class, 'duplicate']); // ✅ PROTEGIDO
    });
    
    // Plantillas Diarias
    Route::prefix('daily-templates')->group(function () {
        Route::get('/', [DailyTemplateController::class, 'index']);
        Route::post('/', [DailyTemplateController::class, 'store']);         // ✅ PROTEGIDO
        Route::put('/{template}', [DailyTemplateController::class, 'update']); // ✅ PROTEGIDO
        Route::delete('/{template}', [DailyTemplateController::class, 'destroy']); // ✅ PROTEGIDO
        Route::post('/{template}/duplicate', [DailyTemplateController::class, 'duplicate']); // ✅ PROTEGIDO
    });
});
```

---

## 🔐 OPERACIONES PROTEGIDAS

### **EJERCICIOS**

| Operación | Endpoint | Método | Requiere Admin/Profesor |
|-----------|----------|--------|-------------------------|
| Listar | `GET /admin/gym/exercises` | GET | ✅ Sí |
| Ver | `GET /admin/gym/exercises/{id}` | GET | ✅ Sí |
| **Crear** | `POST /admin/gym/exercises` | POST | ✅ **SÍ** |
| **Actualizar** | `PUT /admin/gym/exercises/{id}` | PUT | ✅ **SÍ** |
| **Eliminar** | `DELETE /admin/gym/exercises/{id}` | DELETE | ✅ **SÍ** |
| **Duplicar** | `POST /admin/gym/exercises/{id}/duplicate` | POST | ✅ **SÍ** |

### **PLANTILLAS DIARIAS**

| Operación | Endpoint | Método | Requiere Admin/Profesor |
|-----------|----------|--------|-------------------------|
| Listar | `GET /admin/gym/daily-templates` | GET | ✅ Sí |
| Ver | `GET /admin/gym/daily-templates/{id}` | GET | ✅ Sí |
| **Crear** | `POST /admin/gym/daily-templates` | POST | ✅ **SÍ** |
| **Actualizar** | `PUT /admin/gym/daily-templates/{id}` | PUT | ✅ **SÍ** |
| **Eliminar** | `DELETE /admin/gym/daily-templates/{id}` | DELETE | ✅ **SÍ** |
| **Duplicar** | `POST /admin/gym/daily-templates/{id}/duplicate` | POST | ✅ **SÍ** |

### **SETS**

Los sets se crean/actualizan/eliminan **a través de las plantillas diarias**, por lo tanto heredan la misma protección:

- **Crear sets**: Solo al crear/actualizar una plantilla diaria (protegido)
- **Actualizar sets**: Solo al actualizar una plantilla diaria (protegido)
- **Eliminar sets**: Solo al eliminar una plantilla diaria o ejercicio de plantilla (protegido)

---

## 👥 USUARIOS CON PERMISOS

### **EN LA BASE DE DATOS ACTUAL:**

1. **Admin Villa Mitre** (ID: 1) ✅
   - Email: admin@villamitre.com
   - `is_admin = true`
   - **PUEDE crear ejercicios, sets y plantillas**

2. **Profesor Juan Pérez** (ID: 2) ✅
   - Email: profesor@villamitre.com
   - `is_professor = true`
   - **PUEDE crear ejercicios, sets y plantillas**

3. **Usuario Local** (ID: 3) ❌
   - Email: local@villamitre.com
   - Usuario regular
   - **NO PUEDE crear ejercicios, sets ni plantillas**

4. **Usuario API Regular** (ID: 4) ❌
   - Email: api@villamitre.com
   - Usuario regular
   - **NO PUEDE crear ejercicios, sets ni plantillas**

5. **María García** (ID: 5) ❌
   - Email: maria.garcia@villamitre.com
   - Usuario regular
   - **NO PUEDE crear ejercicios, sets ni plantillas**

---

## 🧪 TESTING DE PERMISOS

### **Test 1: Usuario Regular intenta crear ejercicio**
```bash
curl -X POST http://localhost:8000/api/admin/gym/exercises \
  -H "Authorization: Bearer {token_usuario_regular}" \
  -H "Content-Type: application/json" \
  -d '{"name": "Test Exercise"}'
```
**Resultado esperado:** `403 Forbidden: professor role required.`

### **Test 2: Admin crea ejercicio**
```bash
curl -X POST http://localhost:8000/api/admin/gym/exercises \
  -H "Authorization: Bearer {token_admin}" \
  -H "Content-Type: application/json" \
  -d '{"name": "Test Exercise", "muscle_groups": ["pecho"]}'
```
**Resultado esperado:** `201 Created`

### **Test 3: Profesor crea plantilla**
```bash
curl -X POST http://localhost:8000/api/admin/gym/daily-templates \
  -H "Authorization: Bearer {token_profesor}" \
  -H "Content-Type: application/json" \
  -d '{"title": "Test Template", "goal": "strength"}'
```
**Resultado esperado:** `201 Created`

---

## ✅ VERIFICACIÓN ACTUAL

```
✅ Middleware EnsureProfessor implementado correctamente
✅ Rutas protegidas con middleware 'professor'
✅ Solo admins y profesores pueden:
   • Crear ejercicios
   • Modificar ejercicios
   • Eliminar ejercicios
   • Crear plantillas diarias
   • Modificar plantillas diarias
   • Eliminar plantillas diarias
   • Crear/modificar/eliminar sets (a través de plantillas)
✅ Usuarios regulares reciben 403 Forbidden
```

---

## 📝 NOTAS ADICIONALES

1. **Sets**: No tienen endpoints directos. Se manejan a través de las plantillas diarias, heredando automáticamente la protección.

2. **Seeders**: Usan el usuario Admin (ID: 1) para crear plantillas, cumpliendo con la regla de negocio.

3. **Frontend**: Debe ocultar opciones de crear/editar/eliminar para usuarios que no sean admin o profesor.

4. **Respuestas de error**: El middleware devuelve un mensaje claro: `"Forbidden: professor role required."`

---

**Última actualización:** 2025-09-30
**Estado:** ✅ IMPLEMENTADO Y FUNCIONAL
