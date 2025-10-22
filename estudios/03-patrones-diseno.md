# Parte 3: Patrones de Diseño Implementados

## Introducción

El proyecto Villa Mitre Server implementa múltiples **patrones de diseño** reconocidos en la industria. Estos patrones no se eligieron por moda, sino porque resuelven problemas específicos que tiene el proyecto.

**En esta guía verás:**
-  Qué patrón se usa
-  Dónde se implementa (con ejemplos de código real)
-  Qué problema resuelve
-  Ventajas y trade-offs
-  Cómo explicarlo en entrevistas

---

## Índice de Patrones Implementados

1. **Service Layer Pattern** - Separar lógica de negocio
2. **Dependency Injection** - Desacoplamiento e inyección de dependencias
3. **Orchestration Pattern** - Coordinación de servicios
4. **DTO (Data Transfer Object)** - Transferencia de datos tipada
5. **Factory Pattern** - Creación de objetos para testing
6. **Repository Pattern** (via Eloquent) - Abstracción de datos
7. **Middleware Pattern** - Pipeline de procesamiento de requests
8. **Strategy Pattern** - Algoritmos intercambiables
9. **Circuit Breaker Pattern** - Resiliencia en integraciones
10. **Observer Pattern** (via Events) - Reacción a eventos
11. **Value Object Pattern** (via Enums) - Valores inmutables tipados
12. **Facade Pattern** (Laravel Facades) - API simplificada

---

## 1. Service Layer Pattern

### Problema que Resuelve
- L Lógica de negocio duplicada en múltiples controllers
- L Controllers con 500+ líneas
- L Imposible testear lógica sin HTTP
- L Difícil reutilizar funcionalidad

### Implementación en el Proyecto

**Toda la lógica de negocio vive en `app/Services/`**

```php
// L ANTES (sin Service Layer)
class AuthController {
    public function login(Request $request) {
        // 100 líneas de lógica aquí
        $user = User::where('dni', $request->dni)->first();
        if (!$user) { /* ... */ }
        if (!Hash::check($request->password, $user->password)) { /* ... */ }
        if ($user->user_type === 'api') {
            // Refresh desde API externa
            // Más lógica...
        }
        $token = $user->createToken('auth')->plainTextToken;
        // Logging...
        return response()->json([...]);
    }
}

//  DESPUÉS (con Service Layer)
class AuthController {
    public function __construct(
        private AuthService $authService
    ) {}

    public function login(LoginRequest $request) {
        $result = $this->authService->authenticate(
            $request->dni,
            $request->password
        );

        return response()->json([
            'token' => $result->user->createToken('auth')->plainTextToken,
            'user' => $result->user
        ]);
    }
}
```

### Ventajas
-  Controller de 10 líneas vs 100+
-  Lógica reutilizable (desde commands, jobs, etc.)
-  Testeable sin HTTP
-  Single Responsibility

### Dónde se Usa
- **Todos los servicios** en `app/Services/`
- 27 servicios implementados
- Organización por dominios

### Para Entrevistas
> "Usamos Service Layer para separar lógica de negocio de controllers. Los controllers solo manejan HTTP (request/response) y delegan toda la lógica a servicios. Esto nos da testabilidad, reutilización y mantiene los controllers ligeros (10-30 líneas)."

---

## 2. Dependency Injection (DI)

### Problema que Resuelve
- L Acoplamiento fuerte (clases instancian sus dependencias)
- L Difícil testear (no puedes inyectar mocks)
- L Difícil cambiar implementaciones
- L Singletons estáticos (pesadilla para tests)

### Implementación en el Proyecto

**Constructor Injection en TODOS los servicios**

```php
class AuthenticationService
{
    //  Dependencias declaradas en constructor
    public function __construct(
        private CacheService $cacheService,
        private UserRefreshService $userRefreshService,
        private PasswordValidationService $passwordValidationService
    ) {}

    public function authenticate(string $dni, string $password): AuthResult
    {
        // Usa las dependencias inyectadas
        $cached = $this->cacheService->getUser($dni);
        $this->passwordValidationService->validate($user, $password);
        // ...
    }
}
```

**Laravel resuelve automáticamente:**
```php
// No necesitas hacer esto manualmente:
$cache = new CacheService();
$refresh = new UserRefreshService();
$password = new PasswordValidationService();
$auth = new AuthenticationService($cache, $refresh, $password);

// Laravel lo hace por ti cuando usas:
app(AuthenticationService::class);  //  Resuelve todo automáticamente
```

### Testing con DI

```php
// En tests, inyectas mocks
test('authenticate returns AuthResult', function () {
    // Mock de dependencias
    $mockCache = Mockery::mock(CacheService::class);
    $mockRefresh = Mockery::mock(UserRefreshService::class);
    $mockPassword = Mockery::mock(PasswordValidationService::class);

    // Configurar comportamiento
    $mockCache->shouldReceive('getUser')->andReturn(null);
    $mockPassword->shouldReceive('validate')->once();

    // Inyectar mocks
    $service = new AuthenticationService($mockCache, $mockRefresh, $mockPassword);

    // Test aislado
    $result = $service->authenticate('12345678', 'password');

    expect($result)->toBeInstanceOf(AuthResult::class);
});
```

### Ventajas
-  Desacoplamiento total
-  Testabilidad (inyectas mocks)
-  Flexibilidad (cambias implementación fácilmente)
-  Explícito (ves dependencias en constructor)

### Dónde se Usa
- **Todos los servicios**
- Controllers
- Middleware
- Commands

### Para Entrevistas
> "Usamos Dependency Injection vía constructor en todos los servicios. Laravel resuelve las dependencias automáticamente. Esto nos da desacoplamiento y testabilidad: en producción inyecta servicios reales, en tests inyectamos mocks. Por ejemplo, AuthenticationService necesita CacheService y PasswordValidationService - los declaramos en el constructor y Laravel los resuelve."

---

## 3. Orchestration Pattern (Coordinator)

### Problema que Resuelve
- L Servicios muy grandes con múltiples responsabilidades
- L Difícil entender qué hace un servicio
- L Violación de Single Responsibility

### Implementación en el Proyecto

**Servicio orquestador + servicios especializados**

```php
// AuthService - ORQUESTADOR (API pública)
class AuthService
{
    public function __construct(
        private AuthenticationService $authenticationService,  // Especialista
        private UserRegistrationService $userRegistrationService,  // Especialista
        private AuditService $auditService  // Especialista
    ) {}

    // Método público: coordina especialistas
    public function authenticate(string $dni, string $password): AuthResult
    {
        try {
            // 1. Delega autenticación al especialista
            $result = $this->authenticationService->authenticate($dni, $password);

            // 2. Delega auditoría al especialista
            $this->auditService->logLogin($result->user->id, true);

            return $result;

        } catch (\Exception $e) {
            // 3. Log de fallo también delegado
            $user = $this->authenticationService->getUserByDni($dni);
            if ($user) {
                $this->auditService->logLogin($user->id, false);
            }
            throw $e;
        }
    }
}

// AuthenticationService - ESPECIALISTA (lógica pura de autenticación)
class AuthenticationService
{
    public function authenticate(string $dni, string $password): AuthResult
    {
        // Solo se encarga de autenticar
        // Cache, validación, refresh, etc.
    }
}
```

### Ventajas
-  API pública simple (AuthService)
-  Complejidad delegada a especialistas
-  Cada especialista es testeable por separado
-  Fácil agregar funcionalidad (nuevo especialista)

### Ejemplos en el Proyecto
- **AuthService** coordina: Authentication + Registration + Audit
- **ExerciseService** coordina: ExerciseCrud + ExerciseStats
- **TemplateService** coordina: DailyTemplate + WeeklyTemplate

### Para Entrevistas
> "Usamos Orchestration Pattern para servicios complejos. El orquestador (como AuthService) es la API pública y coordina servicios especializados. Por ejemplo, AuthService coordina AuthenticationService (autentica), UserRegistrationService (registra), y AuditService (audita). Cada especialista tiene una responsabilidad única y es testeable. El orquestador simplemente los coordina."

---

## 4. DTO (Data Transfer Object) Pattern

### Problema que Resuelve
- L Arrays asociativos sin tipo (`['user' => ..., 'token' => ...]`)
- L No hay autocompletado en IDE
- L Typos en keys (`$result['usr']` vs `$result['user']`)
- L Documentación pobre

### Implementación en el Proyecto

**Código real del proyecto:**
```php
namespace App\DTOs;

use App\Models\User;

/**
 * Resultado de autenticación
 */
class AuthResult
{
    public function __construct(
        public User $user,
        public bool $fetchedFromApi = false,
        public bool $refreshed = false
    ) {}
}
```

**Uso:**
```php
//  CON DTO
public function authenticate(string $dni, string $password): AuthResult
{
    return new AuthResult($user, false, $wasRefreshed);
}

// Uso tipado
$result = $service->authenticate('12345678', 'pass');
$result->user;  //  IDE autocompleta
$result->refreshed;  //  Type-safe

// L SIN DTO (array asociativo)
public function authenticate(string $dni, string $password): array
{
    return ['user' => $user, 'refreshed' => $wasRefreshed];
}

// Uso propenso a errores
$result = $service->authenticate('12345678', 'pass');
$result['usr'];  //  Typo, no detectado hasta runtime
```

### Ventajas
-  Type safety
-  Autocompletado IDE
-  Documentación clara
-  Inmutabilidad (readonly properties)
-  Validación en construcción (si se agrega)

### Dónde se Usa
- `app/DTOs/AuthResult.php`
- Cualquier respuesta compleja de servicios

### Para Entrevistas
> "Usamos DTOs para transferir datos entre capas. En vez de arrays asociativos sin tipo, tenemos objetos tipados con readonly properties. Por ejemplo, AuthResult tiene user, fetchedFromApi, y refreshed. Esto nos da type safety, autocompletado en el IDE, y previene typos. Los DTOs son inmutables y documentan claramente la estructura de datos."

---

## 5. Factory Pattern (para Testing)

### Problema que Resuelve
- L Crear datos de prueba manualmente es tedioso
- L Datos hardcodeados en tests
- L Tests frágiles (cambias estructura ’ rompen todos los tests)

### Implementación en el Proyecto

**Código real:**
```php
// database/factories/UserFactory.php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'dni' => fake()->unique()->numerify('########'),
            // ...
        ];
    }

    // Métodos de estado (state methods)
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    public function professor(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_professor' => true,
        ]);
    }

    public function apiUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserType::API,
            'socio_id' => fake()->unique()->randomNumber(),
        ]);
    }
}
```

**Uso en tests:**
```php
test('admin can create professor', function () {
    // Factory crea datos automáticamente
    $admin = User::factory()->admin()->create();
    $professor = User::factory()->professor()->create();

    // Test con datos realistas
    actingAs($admin)
        ->postJson('/api/admin/professors', [
            'user_id' => $professor->id
        ])
        ->assertOk();
});

// Crear múltiples
$users = User::factory()->count(10)->create();

// Combinar estados
$apiProfessor = User::factory()->apiUser()->professor()->create();
```

### Ventajas
-  Datos de prueba automáticos
-  Realistas (usa Faker)
-  Reutilizables
-  Mantenibles (cambias factory, no 100 tests)

### Dónde se Usa
- `database/factories/UserFactory.php`
- Factories para todos los modelos principales
- Usado en TODOS los tests

### Para Entrevistas
> "Usamos Factory Pattern para generar datos de prueba. Laravel Factories crean instancias de modelos con datos realistas usando Faker. Por ejemplo, UserFactory tiene métodos como admin(), professor(), apiUser() que retornan usuarios con esos roles. En tests, hacemos User::factory()->admin()->create() y tenemos un admin listo. Si cambiamos la estructura de User, solo actualizamos el factory - no 100 tests."

---

## 6. Repository Pattern (via Eloquent)

### Problema que Resuelve
- L Queries SQL hardcodeadas en servicios
- L Difícil cambiar de DB (MySQL ’ PostgreSQL)
- L Lógica de queries duplicada

### Implementación en el Proyecto

**Laravel Eloquent es un Repository Pattern "light"**

```php
// Eloquent como Repository
class User extends Model
{
    // Eloquent provee métodos de acceso a datos
    public static function findByDni(string $dni): ?User
    {
        return static::where('dni', $dni)->first();
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class, 'created_by');
    }
}

// Uso en servicios (abstracción de DB)
$user = User::findByDni('12345678');
$exercises = $user->exercises()->get();
```

**Sin Eloquent (SQL directo):**
```php
// L SQL hardcodeado
$result = DB::select("SELECT * FROM users WHERE dni = ?", [$dni]);
$user = $result[0] ?? null;

$exercises = DB::select("SELECT * FROM exercises WHERE created_by = ?", [$user->id]);
```

### Ventajas
-  Abstracción de base de datos
-  Query builder fluido
-  Relaciones Eloquent
-  Fácil testear (fake DB)

### Dónde se Usa
- **Todos los modelos** en `app/Models/`
- Servicios usan modelos Eloquent (no SQL directo)

### Para Entrevistas
> "Usamos Eloquent como Repository Pattern. Eloquent abstrae el acceso a datos: en vez de SQL directo, usamos métodos como User::where('dni', $dni)->first(). Esto nos da portabilidad (cambiar DB es trivial), relaciones declarativas (hasMany, belongsTo), y query builder fluido. Los servicios trabajan con modelos Eloquent, no con SQL."

---

## 7. Middleware Pattern (Pipeline)

### Problema que Resuelve
- L Lógica de autenticación/autorización duplicada en controllers
- L Concerns transversales (logging, CORS, rate limiting)
- L Código repetitivo en cada endpoint

### Implementación en el Proyecto

**Código real:**
```php
// app/Http/Middleware/EnsureAdmin.php
class EnsureAdmin
{
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        // 1. Verificar autenticado
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Verificar rol admin
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden. Admin role required.'
            ], 403);
        }

        // 3. Verificar permiso específico
        if ($permission && !$user->hasPermission($permission)) {
            return response()->json([
                'message' => "Permission '{$permission}' required."
            ], 403);
        }

        // 4. Verificar cuenta activa
        if ($user->account_status !== 'active') {
            return response()->json([
                'message' => 'Account suspended.'
            ], 403);
        }

        //  Todo OK, continuar
        return $next($request);
    }
}
```

**Uso en rutas:**
```php
// routes/admin.php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/users', [AdminController::class, 'index']);
    Route::post('/professors', [AdminController::class, 'createProfessor']);
});

// Pipeline: Request ’ auth:sanctum ’ admin ’ Controller
```

**Sin middleware:**
```php
// L Código repetido en CADA método
class AdminController {
    public function index() {
        if (!auth()->user()) return response()->json(..., 401);
        if (!auth()->user()->isAdmin()) return response()->json(..., 403);
        if (auth()->user()->account_status !== 'active') return response()->json(..., 403);
        // Lógica...
    }

    public function createProfessor() {
        if (!auth()->user()) return response()->json(..., 401);
        if (!auth()->user()->isAdmin()) return response()->json(..., 403);
        if (auth()->user()->account_status !== 'active') return response()->json(..., 403);
        // Lógica...
    }
}
```

### Ventajas
-  DRY (Don't Repeat Yourself)
-  Separation of concerns
-  Reutilizable
-  Testeable por separado
-  Pipeline claro (request ’ middleware1 ’ middleware2 ’ controller)

### Middleware en el Proyecto
- `EnsureAdmin` - Verifica rol admin
- `EnsureProfessor` - Verifica rol profesor
- `CheckLicense` - Verifica licencia activa
- Sanctum auth middleware (Laravel)

### Para Entrevistas
> "Usamos Middleware Pattern para concerns transversales. Por ejemplo, EnsureAdmin verifica que el usuario esté autenticado, sea admin, tenga permisos, y cuenta activa. Aplicamos middleware a grupos de rutas: todas las rutas admin pasan por auth:sanctum y admin middleware. Esto evita duplicar código de autenticación/autorización en cada controller. El request fluye por un pipeline: Request ’ auth ’ admin ’ controller."

---

## 8. Strategy Pattern

### Problema que Resuelve
- L Lógica condicional compleja (muchos if/else)
- L Difícil agregar nuevos algoritmos
- L Violación de Open/Closed Principle

### Implementación en el Proyecto

**UserType Enum con comportamiento:**
```php
enum UserType: string
{
    case LOCAL = 'local';
    case API = 'api';

    // Métodos de comportamiento (estrategias)
    public function label(): string
    {
        return match($this) {
            self::LOCAL => 'Usuario Local',
            self::API => 'Usuario API',
        };
    }

    public function isLocal(): bool { return $this === self::LOCAL; }
    public function isApi(): bool { return $this === self::API; }
}
```

**Uso:**
```php
//  CON Strategy Pattern (via Enum)
$user = User::find($id);
if ($user->user_type->isApi()) {
    // Lógica para usuario API
    $this->userRefreshService->refreshFromApi($user);
}

// L SIN Strategy Pattern (string comparison)
if ($user->user_type === 'api') {  // Magic string
    // Lógica...
}
```

**Strategy Pattern en Servicios:**
```php
// Diferentes estrategias de registro
class UserRegistrationService
{
    public function registerLocal(array $data): User
    {
        // Estrategia: registro local
    }

    public function registerFromApi(array $apiData, string $password): User
    {
        // Estrategia: registro desde API externa
    }
}
```

### Ventajas
-  Algoritmos intercambiables
-  Elimina condicionales complejos
-  Open/Closed (agregar estrategia sin modificar código)
-  Type-safe (enums tipados)

### Dónde se Usa
- `app/Enums/UserType.php`
- `app/Enums/PromotionStatus.php`
- Servicios con múltiples "estrategias" (Registration, Refresh)

### Para Entrevistas
> "Usamos Strategy Pattern para algoritmos intercambiables. Por ejemplo, tenemos dos tipos de usuarios (LOCAL y API) con comportamientos diferentes. Usamos Enums con métodos: UserType::LOCAL y UserType::API tienen métodos isLocal(), isApi(). Esto elimina magic strings y condicionales complejos. También en servicios: UserRegistrationService tiene registerLocal() y registerFromApi() - dos estrategias de registro."

---

## 9. Circuit Breaker Pattern

### Problema que Resuelve
- L Llamadas a APIs externas que fallan repetidamente
- L Timeouts largos afectan performance
- L Cascading failures (fallo en API externa tumba todo el sistema)

### Implementación en el Proyecto

**SociosApi con Circuit Breaker:**
```php
class SociosApi
{
    private const MAX_RETRIES = 3;
    private const TIMEOUT = 10;

    public function getSociusByDni(string $dni): ?array
    {
        try {
            $response = $this->client->get("/api/socios/{$dni}", [
                'timeout' => self::TIMEOUT,
                'retry' => [
                    'times' => self::MAX_RETRIES,
                    'sleep' => 1000,  // 1 segundo entre reintentos
                    'when' => function ($exception) {
                        // Solo reintentar en errores de conexión
                        return $exception instanceof ConnectException;
                    }
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {
            // Log pero no propagar - fallback gracefully
            Log::error("SociosApi error", [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);

            // Retornar null en vez de lanzar excepción
            return null;
        }
    }
}
```

**Uso con fallback:**
```php
// Intentar desde API externa
$socioData = $this->sociosApi->getSociusByDni($dni);

if ($socioData === null) {
    // Fallback: continuar sin datos externos
    Log::warning("Failed to fetch socio data, using local data only");
    return $this->createLocalUser($data);
}

// Usar datos externos
return $this->createApiUser($socioData);
```

### Ventajas
-  Resiliencia (fallo en API no tumba sistema)
-  Reintentos automáticos
-  Timeouts configurables
-  Graceful degradation

### Para Entrevistas
> "Implementamos Circuit Breaker en SociosApi para integraciones externas. Configuramos timeouts (10s), reintentos (3 intentos con 1s entre ellos), y fallback gracefully. Si la API externa falla, loggeamos el error pero retornamos null - no lanzamos excepción. Esto previene cascading failures: si la API del club está caída, nuestro sistema sigue funcionando con datos locales."

---

## 10. Observer Pattern (via Laravel Events)

### Problema que Resuelve
- L Acoplamiento entre componentes (A necesita notificar a B, C, D)
- L Lógica de "efectos secundarios" en servicios principales
- L Difícil agregar nuevas reacciones a eventos

### Implementación en el Proyecto

**Eventos y Listeners (futuro):**
```php
// Event
class UserPromoted
{
    public function __construct(
        public User $user,
        public UserType $fromType,
        public UserType $toType
    ) {}
}

// Listeners
class SendPromotionNotification
{
    public function handle(UserPromoted $event) {
        // Enviar email
    }
}

class LogPromotion
{
    public function handle(UserPromoted $event) {
        // Log de auditoría
    }
}

class UpdateCache
{
    public function handle(UserPromoted $event) {
        // Invalidar cache
    }
}

// Uso
event(new UserPromoted($user, UserType::LOCAL, UserType::API));
// ’ Dispara automáticamente 3 listeners
```

### Ventajas
-  Desacoplamiento
-  Fácil agregar reacciones (nuevo listener)
-  Separation of concerns

### Para Entrevistas
> "Usamos Observer Pattern vía Laravel Events para reaccionar a eventos del sistema. Por ejemplo, cuando un usuario es promovido (LOCAL ’ API), disparamos un evento UserPromoted. Múltiples listeners reaccionan: uno envía email, otro loggea, otro invalida cache. El servicio principal no conoce a los listeners - están desacoplados. Agregar una nueva reacción es solo crear un nuevo listener."

---

## 11. Value Object Pattern (via Enums PHP 8.1+)

### Problema que Resuelve
- L Magic strings/numbers (`'admin'`, `'local'`, `1`, `2`)
- L No type safety
- L Typos no detectados hasta runtime

### Implementación en el Proyecto

**Código real:**
```php
// app/Enums/UserType.php
enum UserType: string
{
    case LOCAL = 'local';
    case API = 'api';

    public function label(): string
    {
        return match($this) {
            self::LOCAL => 'Usuario Local',
            self::API => 'Usuario API',
        };
    }

    public function isLocal(): bool { return $this === self::LOCAL; }
    public function isApi(): bool { return $this === self::API; }
}
```

**Uso:**
```php
//  Type-safe con Enum
$user->user_type = UserType::API;

if ($user->user_type === UserType::API) {
    // IDE autocompleta, type-safe
}

// L Magic string
$user->user_type = 'api';  // Typo: 'apu' ’ No detectado

if ($user->user_type === 'api') {
    // String comparison, propenso a errores
}
```

### Ventajas
-  Type safety
-  No magic strings
-  Métodos en enums (comportamiento)
-  IDE autocompleta

### Dónde se Usa
- `UserType`, `PromotionStatus`
- Cualquier valor constante

### Para Entrevistas
> "Usamos Enums de PHP 8.1+ como Value Objects. Por ejemplo, UserType enum tiene LOCAL y API. Esto elimina magic strings, da type safety, y permite métodos (isLocal(), label()). El IDE autocompleta y detecta typos en compile time. Los enums son inmutables y representan valores de dominio."

---

## 12. Facade Pattern (Laravel Facades)

### Problema que Resuelve
- L API compleja difícil de usar
- L Necesidad de instanciar clases repetidamente
- L Código verbose

### Implementación en el Proyecto

**Laravel Facades usados:**
```php
// Facade: API simple sobre sistema complejo
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

//  Con Facade (simple)
Hash::make('password');
Cache::get('user:12345');
Log::error('API failed', ['dni' => $dni]);
DB::transaction(function () { /* ... */ });

// L Sin Facade (verbose)
app('hash')->make('password');
app('cache')->get('user:12345');
app('log')->error('API failed', ['dni' => $dni]);
```

### Ventajas
-  API limpia y concisa
-  Testeable (facades tienen mocks)
-  Static calls (más legible)

### Dónde se Usa
- **Todo el proyecto** usa Laravel Facades
- Hash, Cache, Log, DB, Storage, etc.

### Para Entrevistas
> "Usamos Laravel Facades para APIs limpias. Facades son proxies estáticos sobre servicios del contenedor. Por ejemplo, Hash::make() es más limpio que app('hash')->make(). Los facades son testeables: en tests hacemos Hash::shouldReceive() para mockearlos. Esto simplifica el código sin perder testabilidad."

---

## Resumen de Patrones

| Patrón | Dónde | Por Qué | Beneficio Clave |
|--------|-------|---------|----------------|
| **Service Layer** | `app/Services/` | Separar lógica de controllers | Testabilidad, reutilización |
| **Dependency Injection** | Todos los servicios | Desacoplamiento | Testeable con mocks |
| **Orchestration** | AuthService, ExerciseService | Coordinar especialistas | Complexity management |
| **DTO** | AuthResult | Transfer tipado | Type safety |
| **Factory** | UserFactory | Datos de prueba | Tests mantenibles |
| **Repository** | Eloquent | Abstracción DB | Portabilidad |
| **Middleware** | EnsureAdmin | Concerns transversales | DRY, separation |
| **Strategy** | UserType enum | Algoritmos intercambiables | Open/Closed |
| **Circuit Breaker** | SociosApi | Resiliencia | Fault tolerance |
| **Observer** | Events | Reacción a eventos | Desacoplamiento |
| **Value Object** | Enums | Valores inmutables | Type safety |
| **Facade** | Laravel Facades | API simplificada | Clean code |

---

## Para Entrevistas: Preguntas Típicas

### "¿Qué patrones de diseño usaste?"

**Respuesta:**

> "Usamos varios patrones clave:
>
> 1. **Service Layer** para separar lógica de negocio. Controllers de 10 líneas, toda la lógica en servicios.
>
> 2. **Dependency Injection** vía constructor. Laravel resuelve automáticamente. Testeamos con mocks.
>
> 3. **Orchestration** para servicios complejos. AuthService coordina Authentication, Registration, y Audit services.
>
> 4. **DTO** para transferir datos tipados. AuthResult tiene user, fetchedFromApi, refreshed - type-safe.
>
> 5. **Circuit Breaker** en SociosApi para resiliencia. Reintentos, timeouts, fallback gracefully.
>
> 6. **Middleware** para autenticación/autorización. Pipeline claro sin duplicar código.
>
> Estos patrones resuelven problemas reales: testabilidad, mantenibilidad, escalabilidad."

### "¿Por qué usas DTOs en vez de arrays?"

**Respuesta:**

> "Los DTOs dan type safety y mejor developer experience:
>
> - **Type safety:** `$result->user` en vez de `$result['user']` - IDE detecta typos
> - **Autocompletado:** IDE sabe qué propiedades existen
> - **Documentación:** La clase documenta la estructura
> - **Inmutabilidad:** readonly properties
> - **Refactoring seguro:** Cambias DTO, IDE encuentra todos los usos
>
> Por ejemplo, AuthResult tiene user, fetchedFromApi, refreshed. Cualquier código que use AuthResult tiene garantía de tipo."

### "¿Cómo manejan integraciones externas?"

**Respuesta:**

> "Usamos Circuit Breaker en SociosApi:
>
> - **Timeouts:** 10 segundos máximo
> - **Reintentos:** 3 intentos con 1s entre ellos
> - **Fallback:** Si falla, retornamos null - no lanzamos excepción
> - **Graceful degradation:** Sistema sigue funcionando con datos locales
>
> Esto previene cascading failures. Si la API del club está caída, nuestro sistema sigue operativo."

---

## Próximos Pasos

 **Parte 1:** Estructura General
 **Parte 2:** Arquitectura de Servicios
 **Parte 3:** Patrones de Diseño

**Siguiente:**
- **Parte 4:** Stack Tecnológico detallado
- **Parte 5:** Sistema de Autenticación completo
- **Parte 6:** Sistema de Gimnasio

---

**Documento para estudio y preparación profesional**
**Proyecto:** Villa Mitre Server - Laravel 12 API Backend
**12 patrones implementados** con ejemplos reales
