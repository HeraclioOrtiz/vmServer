# 🔧 Propuestas de Refactorización - Villa Mitre Server

**Fecha:** 21 de Octubre 2025
**Rama:** `refactor/code-audit-and-improvements`
**Basado en:** AUDIT-REPORT-2025-10-21.md

---

## 🎯 Objetivo

Este documento presenta propuestas concretas y accionables para mejorar la calidad del código, priorizadas por:
1. **Impacto**: Cuánto mejora el código
2. **Esfuerzo**: Tiempo requerido
3. **Riesgo**: Probabilidad de romper funcionalidad existente

---

## 📊 Matriz de Priorización

| ID | Propuesta | Impacto | Esfuerzo | Riesgo | Prioridad |
|----|-----------|---------|----------|--------|-----------|
| P1 | Mover DNI a .env | Medio | Bajo (30m) | Bajo | 🟢 ALTA |
| P2 | QueryFilterBuilder | Alto | Medio (6h) | Bajo | 🟢 ALTA |
| P3 | Estandarizar errores | Alto | Medio (4h) | Medio | 🟡 MEDIA |
| P4 | Split TemplateService | Muy Alto | Alto (3d) | Alto | 🔴 ALTA |
| P5 | Split ExerciseService | Alto | Alto (2d) | Alto | 🟡 MEDIA |
| P6 | Centralizar cache | Medio | Bajo (4h) | Bajo | 🟢 ALTA |
| P7 | Extraer validadores | Medio | Medio (1d) | Bajo | 🟡 MEDIA |
| P8 | Resource classes | Medio | Medio (1d) | Bajo | 🔵 BAJA |

**Leyenda Prioridad:**
- 🟢 ALTA: Hacer primero (bajo esfuerzo, alto impacto, bajo riesgo)
- 🟡 MEDIA: Hacer después (buen impacto pero más esfuerzo/riesgo)
- 🔴 ALTA: Requiere planificación (muy importante pero complejo)
- 🔵 BAJA: Puede esperar

---

## 🟢 Propuestas de ALTA PRIORIDAD (Quick Wins)

### P1: Configurar Profesor por Defecto vía .env

**Problema Actual:**
```php
// UserPromotionService.php:284
$professor = User::where('dni', '22222222')->first();  // HARDCODED
```

**Solución Propuesta:**

**Paso 1:** Crear archivo de configuración
```php
// config/gym.php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Professor Assignment
    |--------------------------------------------------------------------------
    |
    | DNI del profesor por defecto para auto-asignación de nuevos estudiantes.
    | Esto es temporal hasta que se implemente la UI de asignación manual.
    |
    | Para deshabilitar la auto-asignación, dejar en null.
    |
    */
    'default_professor_dni' => env('GYM_DEFAULT_PROFESSOR_DNI', null),
    'auto_assign_students' => env('GYM_AUTO_ASSIGN_STUDENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Gym Settings
    |--------------------------------------------------------------------------
    */
    'max_exercises_per_template' => env('GYM_MAX_EXERCISES', 20),
    'max_students_per_professor' => env('GYM_MAX_STUDENTS', 50),
];
```

**Paso 2:** Actualizar .env.example
```env
# Gym Configuration
GYM_DEFAULT_PROFESSOR_DNI=22222222
GYM_AUTO_ASSIGN_STUDENTS=true
GYM_MAX_EXERCISES=20
GYM_MAX_STUDENTS=50
```

**Paso 3:** Refactorizar UserPromotionService
```php
// app/Services/User/UserPromotionService.php

private function assignToDefaultProfessor(User $student): void
{
    // Verificar si la auto-asignación está habilitada
    if (!config('gym.auto_assign_students', false)) {
        Log::info('Auto-assignment disabled, skipping', [
            'student_id' => $student->id,
        ]);
        return;
    }

    // Obtener DNI del profesor por defecto desde configuración
    $defaultDni = config('gym.default_professor_dni');

    if (!$defaultDni) {
        Log::warning('Default professor DNI not configured', [
            'student_id' => $student->id,
        ]);
        return;
    }

    $professor = User::where('dni', $defaultDni)
        ->where('is_professor', true)
        ->first();

    if (!$professor) {
        Log::error('Default professor not found', [
            'dni' => $defaultDni,
            'student_id' => $student->id,
        ]);
        return;
    }

    // Resto del código existente...
    try {
        ProfessorStudentAssignment::create([
            'professor_id' => $professor->id,
            'student_id' => $student->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        Log::info('Student auto-assigned to default professor', [
            'student_id' => $student->id,
            'professor_id' => $professor->id,
            'dni' => $defaultDni,
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to auto-assign student', [
            'student_id' => $student->id,
            'professor_id' => $professor->id,
            'error' => $e->getMessage(),
        ]);
    }
}
```

**Beneficios:**
- ✅ Fácil cambiar DNI sin tocar código
- ✅ Puede deshabilitarse en testing/staging
- ✅ Mejor logging y error handling
- ✅ Preparado para múltiples ambientes

**Esfuerzo:** 30 minutos
**Riesgo:** Bajo (solo cambio de configuración)
**Testing:** Verificar que sigue asignando correctamente

---

### P2: Crear QueryFilterBuilder Utility

**Problema Actual:**
Lógica de filtrado duplicada en 4+ archivos con ~200 líneas totales de código duplicado.

**Solución Propuesta:**

```php
// app/Services/Core/QueryFilterBuilder.php
<?php

namespace App\Services\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class QueryFilterBuilder
{
    /**
     * Aplica búsqueda en múltiples campos
     */
    public function applySearch(Builder $query, ?string $search, array $searchFields): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchFields) {
            foreach ($searchFields as $field) {
                $q->orWhere($field, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Aplica filtro por rango de fechas
     */
    public function applyDateRange(
        Builder $query,
        string $dateField,
        ?string $from = null,
        ?string $to = null
    ): Builder {
        if ($from) {
            $query->whereDate($dateField, '>=', Carbon::parse($from));
        }

        if ($to) {
            $query->whereDate($dateField, '<=', Carbon::parse($to));
        }

        return $query;
    }

    /**
     * Aplica filtro IN para múltiples valores
     */
    public function applyInFilter(Builder $query, string $field, $values): Builder
    {
        if (empty($values)) {
            return $query;
        }

        // Manejar arrays y strings separados por coma
        if (is_string($values)) {
            $values = explode(',', $values);
        }

        return $query->whereIn($field, $values);
    }

    /**
     * Aplica filtro de comparación
     */
    public function applyComparison(
        Builder $query,
        string $field,
        $value,
        string $operator = '='
    ): Builder {
        if ($value === null || $value === '') {
            return $query;
        }

        return $query->where($field, $operator, $value);
    }

    /**
     * Aplica filtro booleano
     */
    public function applyBooleanFilter(Builder $query, string $field, ?bool $value): Builder
    {
        if ($value === null) {
            return $query;
        }

        return $query->where($field, $value);
    }

    /**
     * Aplica ordenamiento
     */
    public function applySort(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortOrder = 'desc'
    ): Builder {
        $allowedOrders = ['asc', 'desc'];
        $order = in_array(strtolower($sortOrder), $allowedOrders) ? $sortOrder : 'desc';

        return $query->orderBy($sortBy, $order);
    }
}
```

**Uso en servicios:**

```php
// UserManagementService.php - ANTES (67 líneas)
private function applyFilters(Builder $query, array $filters): Builder
{
    if (!empty($filters['search'])) {
        $search = $filters['search'];
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('dni', 'like', "%{$search}%");
        });
    }
    // ... 50+ líneas más
}

// UserManagementService.php - DESPUÉS (15 líneas)
public function __construct(
    private QueryFilterBuilder $filterBuilder,
    private AuditService $auditService
) {}

private function applyFilters(Builder $query, array $filters): Builder
{
    $this->filterBuilder->applySearch($query, $filters['search'] ?? null, [
        'name', 'email', 'dni'
    ]);

    $this->filterBuilder->applyInFilter($query, 'user_type', $filters['user_type'] ?? null);
    $this->filterBuilder->applyBooleanFilter($query, 'is_admin', $filters['is_admin'] ?? null);
    $this->filterBuilder->applyDateRange($query, 'created_at',
        $filters['created_from'] ?? null,
        $filters['created_to'] ?? null
    );

    return $this->filterBuilder->applySort($query,
        $filters['sort_by'] ?? 'created_at',
        $filters['sort_order'] ?? 'desc'
    );
}
```

**Archivos a Refactorizar:**
1. `UserManagementService::applyFilters()` - 67 líneas → 15 líneas
2. `ProfessorManagementService::applyFilters()` - 23 líneas → 12 líneas
3. `TemplateService::applyDailyTemplateFilters()` - 78 líneas → 20 líneas
4. `WeeklyAssignmentService::applyFilters()` - 22 líneas → 10 líneas

**Beneficios:**
- ✅ Elimina ~190 líneas de código duplicado
- ✅ Lógica de filtrado testeable en un solo lugar
- ✅ Fácil agregar nuevos tipos de filtros
- ✅ Consistencia en todos los servicios

**Esfuerzo:** 4-6 horas
**Riesgo:** Bajo (mantener tests de integración)

---

### P6: Centralizar Operaciones de Cache

**Problema Actual:**
- Uso directo de `Cache::remember()`, `Cache::forget()` en múltiples servicios
- Código específico de Redis en `TemplateService` (línea 586)
- TTLs inconsistentes (300s, 600s, 1800s)

**Solución Propuesta:**

**Paso 1:** Expandir CacheService existente
```php
// app/Services/Core/CacheService.php

<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // TTL Constants
    private const USER_TTL = 3600;           // 1 hora
    private const TEMPLATE_TTL = 300;        // 5 minutos
    private const STATS_TTL = 600;           // 10 minutos
    private const EXERCISE_TTL = 1800;       // 30 minutos
    private const SETTINGS_TTL = 3600;       // 1 hora

    /**
     * Cachea templates con TTL apropiado
     */
    public function rememberTemplate(string $key, \Closure $callback)
    {
        return Cache::remember("template:{$key}", self::TEMPLATE_TTL, $callback);
    }

    /**
     * Cachea estadísticas
     */
    public function rememberStats(string $key, \Closure $callback)
    {
        return Cache::remember("stats:{$key}", self::STATS_TTL, $callback);
    }

    /**
     * Cachea ejercicios
     */
    public function rememberExercise(string $key, \Closure $callback)
    {
        return Cache::remember("exercise:{$key}", self::EXERCISE_TTL, $callback);
    }

    /**
     * Limpia cache por patrón (independiente del driver)
     */
    public function forgetPattern(string $pattern): void
    {
        // Usar tags si está disponible (Redis, Memcached)
        if (Cache::supportsTags()) {
            Cache::tags([$pattern])->flush();
        } else {
            // Fallback: limpiar todo el cache (SQLite, File)
            Cache::flush();
        }
    }

    /**
     * Limpia cache de templates
     */
    public function forgetTemplateCache(): void
    {
        $this->forgetPattern('template');
    }

    /**
     * Limpia cache de ejercicios
     */
    public function forgetExerciseCache(): void
    {
        $this->forgetPattern('exercise');
    }

    /**
     * Limpia cache de estadísticas
     */
    public function forgetStatsCache(): void
    {
        $this->forgetPattern('stats');
    }
}
```

**Paso 2:** Refactorizar servicios

```php
// ExerciseService.php - ANTES
Cache::remember('exercise_stats', 600, function () {
    // ...
});

Cache::forget('exercise_stats');
Cache::forget('exercise_filter_options');
for ($i = 1; $i <= 20; $i++) {
    Cache::forget("most_used_exercises_{$i}");
}

// ExerciseService.php - DESPUÉS
public function __construct(
    private CacheService $cacheService,
    // ...
) {}

$this->cacheService->rememberStats('exercise_stats', function () {
    // ...
});

$this->cacheService->forgetExerciseCache();
```

**Beneficios:**
- ✅ TTLs centralizados y consistentes
- ✅ Sin código específico de driver (compatible con SQLite, Redis, etc.)
- ✅ Fácil cambiar estrategia de cache
- ✅ Cache limpieza más eficiente

**Esfuerzo:** 3-4 horas
**Riesgo:** Bajo

---

## 🟡 Propuestas de MEDIA PRIORIDAD

### P3: Estandarizar Manejo de Errores

**Problema Actual:**
```php
// Algunos servicios lanzan excepciones
throw new \Exception('Only super admins can create admin users.');

// Otros retornan arrays
return [
    'success' => false,
    'error' => 'EXERCISE_IN_USE',
];
```

**Propuesta A - Excepciones Tipadas (RECOMENDADO):**

```php
// app/Exceptions/Gym/ExerciseInUseException.php
<?php

namespace App\Exceptions\Gym;

use Exception;

class ExerciseInUseException extends Exception
{
    public function __construct(
        public readonly int $exerciseId,
        public readonly array $dependencies
    ) {
        $count = count($dependencies);
        parent::__construct(
            "Exercise #{$exerciseId} cannot be deleted. Used in {$count} template(s)."
        );
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'EXERCISE_IN_USE',
            'message' => $this->getMessage(),
            'dependencies' => $this->dependencies,
        ], 409); // Conflict
    }
}

// Uso
throw new ExerciseInUseException($exercise->id, $dependencies);
```

**Propuesta B - Result Objects:**

```php
// app/DTOs/Result.php
<?php

namespace App\DTOs;

class Result
{
    private function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?string $error = null,
        public readonly ?string $message = null
    ) {}

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(true, $data, null, $message);
    }

    public static function failure(string $error, ?string $message = null): self
    {
        return new self(false, null, $error, $message);
    }
}

// Uso
return Result::failure('EXERCISE_IN_USE', 'Exercise is used in templates');
```

**Recomendación:** Opción A (Excepciones) - más Laravel-friendly

**Esfuerzo:** 4-5 horas
**Riesgo:** Medio (requiere cambios en controllers)

---

### P5: Split ExerciseService

**Problema:** 449 líneas, múltiples responsabilidades

**Propuesta de Split:**

```
ExerciseService (449 líneas)
    ↓
    ├─ ExerciseService (CRUD core) (~150 líneas)
    ├─ ExerciseDependencyChecker (~80 líneas)
    ├─ ExerciseDeletionService (~120 líneas)
    └─ ExerciseStatisticsService (~100 líneas)
```

**Implementación sugerida:**

```php
// app/Services/Gym/ExerciseService.php (refactorizado)
class ExerciseService
{
    public function __construct(
        private CacheService $cacheService,
        private ExerciseDependencyChecker $dependencyChecker,
        private ExerciseDeletionService $deletionService,
        private ExerciseStatisticsService $statsService
    ) {}

    public function create(array $data): Exercise { /* ... */ }
    public function update(Exercise $exercise, array $data): Exercise { /* ... */ }

    public function delete(Exercise $exercise): array
    {
        return $this->deletionService->delete($exercise);
    }

    public function forceDelete(Exercise $exercise): bool
    {
        return $this->deletionService->forceDelete($exercise);
    }

    public function checkDependencies(Exercise $exercise): array
    {
        return $this->dependencyChecker->check($exercise);
    }

    public function getStatistics(array $filters = []): array
    {
        return $this->statsService->getStatistics($filters);
    }
}
```

**Esfuerzo:** 1.5-2 días
**Riesgo:** Alto (requiere tests exhaustivos)
**Beneficio:** Alta cohesión, mejor testabilidad

---

## 🔴 Propuestas que Requieren Planificación

### P4: Split TemplateService

**El más importante pero también el más complejo**

**Propuesta de Split:**

```
TemplateService (623 líneas)
    ↓
    ├─ DailyTemplateService (~150 líneas)
    │   ├─ create(), update(), delete()
    │   ├─ findById(), paginate()
    │   └─ duplicate()
    │
    ├─ WeeklyTemplateService (~120 líneas)
    │   ├─ create(), update(), delete()
    │   ├─ findById(), paginate()
    │   └─ duplicate()
    │
    ├─ TemplateFilterBuilder (~100 líneas)
    │   ├─ applyDailyFilters()
    │   ├─ applyWeeklyFilters()
    │   └─ buildSortQuery()
    │
    ├─ TemplateStatisticsService (~80 líneas)
    │   ├─ getDailyStats()
    │   ├─ getWeeklyStats()
    │   └─ getUsageMetrics()
    │
    └─ TemplateCacheManager (~80 líneas)
        ├─ rememberDailyTemplate()
        ├─ rememberWeeklyTemplate()
        ├─ clearDailyCache()
        └─ clearWeeklyCache()
```

**Plan de Implementación:**

1. **Día 1 - Preparación**
   - Crear tests de integración para funcionalidad existente
   - Documentar API actual
   - Crear clases vacías

2. **Día 2 - Extracción**
   - Mover métodos a nuevas clases
   - Mantener TemplateService como facade/orchestrator
   - Controllers siguen usando TemplateService

3. **Día 3 - Testing y Ajustes**
   - Run tests exhaustivos
   - Fix any broken functionality
   - Update documentation

**Esfuerzo:** 3 días
**Riesgo:** Alto
**Beneficio:** Muy Alto

---

## 🔵 Propuestas de BAJA PRIORIDAD

### P7: Extraer Validadores

```php
// app/Validators/UserValidator.php
class UserValidator
{
    public function validateDniAvailability(string $dni, ?int $excludeId = null): void
    {
        $query = User::where('dni', $dni);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'dni' => ['El DNI ya está registrado.']
            ]);
        }
    }
}
```

**Esfuerzo:** 1 día
**Riesgo:** Bajo

---

### P8: Resource Classes para Respuestas Complejas

```php
// app/Http/Resources/UserDetailResource.php
class UserDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // ... structured response
        ];
    }
}
```

**Esfuerzo:** 1 día
**Riesgo:** Bajo

---

## 🎯 Roadmap Recomendado

### Semana 1: Quick Wins
- Día 1: P1 (DNI a .env) + P6 (Centralizar cache)
- Día 2-3: P2 (QueryFilterBuilder)

### Semana 2: Fundaciones
- Día 1-2: P3 (Estandarizar errores)
- Día 3-5: Preparación para P4 (tests, documentación)

### Semana 3-4: Refactorización Mayor
- Semana 3: P4 (Split TemplateService)
- Semana 4: P5 (Split ExerciseService)

### Semana 5-6: Pulido
- P7 (Validadores)
- P8 (Resources)
- Documentación
- Testing completo

---

## ✅ Checklist de Implementación

Para cada propuesta:

- [ ] Crear branch feature específico
- [ ] Escribir tests ANTES de refactorizar
- [ ] Implementar cambios incrementalmente
- [ ] Run test suite completo
- [ ] Actualizar documentación
- [ ] Code review
- [ ] Merge a refactor/code-audit-and-improvements

---

## 📝 Notas Finales

**Enfoque recomendado:**
1. Comenzar con Quick Wins (P1, P2, P6) - 1 semana
2. Ganar confianza con cambios pequeños
3. Luego abordar refactorizaciones mayores (P4, P5)

**Principios a seguir:**
- Tests primero
- Commits pequeños y frecuentes
- Mantener funcionalidad durante refactor
- Documentar decisiones arquitectónicas

---

**Documento creado por:** Claude Code
**Última actualización:** 2025-10-21
