# Parte 3: Patrones de Dise�o Implementados

## Introducci�n

El proyecto Villa Mitre Server implementa m�ltiples **patrones de dise�o** reconocidos en la industria. Estos patrones no se eligieron por moda, sino porque resuelven problemas espec�ficos que tiene el proyecto.

**En esta gu�a ver�s:**
-  Qu� patr�n se usa
-  D�nde se implementa (con ejemplos de c�digo real)
-  Qu� problema resuelve
-  Ventajas y trade-offs
-  C�mo explicarlo en entrevistas

---

## �ndice de Patrones Implementados

1. **Service Layer Pattern** - Separar l�gica de negocio
2. **Dependency Injection** - Desacoplamiento e inyecci�n de dependencias
3. **Orchestration Pattern** - Coordinaci�n de servicios
4. **DTO (Data Transfer Object)** - Transferencia de datos tipada
5. **Factory Pattern** - Creaci�n de objetos para testing
6. **Repository Pattern** (via Eloquent) - Abstracci�n de datos
7. **Middleware Pattern** - Pipeline de procesamiento de requests
8. **Strategy Pattern** - Algoritmos intercambiables
9. **Circuit Breaker Pattern** - Resiliencia en integraciones
10. **Observer Pattern** (via Events) - Reacci�n a eventos
11. **Value Object Pattern** (via Enums) - Valores inmutables tipados
12. **Facade Pattern** (Laravel Facades) - API simplificada

---

## 1. Service Layer Pattern

### Problema que Resuelve
- L L�gica de negocio duplicada en m�ltiples controllers
- L Controllers con 500+ l�neas
- L Imposible testear l�gica sin HTTP
- L Dif�cil reutilizar funcionalidad

### Implementaci�n en el Proyecto

**Toda la l�gica de negocio vive en `app/Services/`**

```php
// L ANTES (sin Service Layer)
class AuthController {
    public function login(Request $request) {
        // 100 l�neas de l�gica aqu�
        $user = User::where('dni', $request->dni)->first();
        if (!$user) { /* ... */ }
        if (!Hash::check($request->password, $user->password)) { /* ... */ }
        if ($user->user_type === 'api') {
            // Refresh desde API externa
            // M�s l�gica...
        }
        $token = $user->createToken('auth')->plainTextToken;
        // Logging...
        return response()->json([...]);
    }
}

//  DESPU�S (con Service Layer)
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
-  Controller de 10 l�neas vs 100+
-  L�gica reutilizable (desde commands, jobs, etc.)
-  Testeable sin HTTP
-  Single Responsibility

### D�nde se Usa
- **Todos los servicios** en `app/Services/`
- 27 servicios implementados
- Organizaci�n por dominios

### Para Entrevistas
> "Usamos Service Layer para separar l�gica de negocio de controllers. Los controllers solo manejan HTTP (request/response) y delegan toda la l�gica a servicios. Esto nos da testabilidad, reutilizaci�n y mantiene los controllers ligeros (10-30 l�neas)."

---

## 2. Dependency Injection (DI)

### Problema que Resuelve
- L Acoplamiento fuerte (clases instancian sus dependencias)
- L Dif�cil testear (no puedes inyectar mocks)
- L Dif�cil cambiar implementaciones
- L Singletons est�ticos (pesadilla para tests)

### Implementaci�n en el Proyecto

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

**Laravel resuelve autom�ticamente:**
```php
// No necesitas hacer esto manualmente:
$cache = new CacheService();
$refresh = new UserRefreshService();
$password = new PasswordValidationService();
$auth = new AuthenticationService($cache, $refresh, $password);

// Laravel lo hace por ti cuando usas:
app(AuthenticationService::class);  // � Resuelve todo autom�ticamente
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
-  Flexibilidad (cambias implementaci�n f�cilmente)
-  Expl�cito (ves dependencias en constructor)

### D�nde se Usa
- **Todos los servicios**
- Controllers
- Middleware
- Commands

### Para Entrevistas
> "Usamos Dependency Injection v�a constructor en todos los servicios. Laravel resuelve las dependencias autom�ticamente. Esto nos da desacoplamiento y testabilidad: en producci�n inyecta servicios reales, en tests inyectamos mocks. Por ejemplo, AuthenticationService necesita CacheService y PasswordValidationService - los declaramos en el constructor y Laravel los resuelve."

---

## 3. Orchestration Pattern (Coordinator)

### Problema que Resuelve
- L Servicios muy grandes con m�ltiples responsabilidades
- L Dif�cil entender qu� hace un servicio
- L Violaci�n de Single Responsibility

### Implementaci�n en el Proyecto

**Servicio orquestador + servicios especializados**

```php
// AuthService - ORQUESTADOR (API p�blica)
class AuthService
{
    public function __construct(
        private AuthenticationService $authenticationService,  // Especialista
        private UserRegistrationService $userRegistrationService,  // Especialista
        private AuditService $auditService  // Especialista
    ) {}

    // M�todo p�blico: coordina especialistas
    public function authenticate(string $dni, string $password): AuthResult
    {
        try {
            // 1. Delega autenticaci�n al especialista
            $result = $this->authenticationService->authenticate($dni, $password);

            // 2. Delega auditor�a al especialista
            $this->auditService->logLogin($result->user->id, true);

            return $result;

        } catch (\Exception $e) {
            // 3. Log de fallo tambi�n delegado
            $user = $this->authenticationService->getUserByDni($dni);
            if ($user) {
                $this->auditService->logLogin($user->id, false);
            }
            throw $e;
        }
    }
}

// AuthenticationService - ESPECIALISTA (l�gica pura de autenticaci�n)
class AuthenticationService
{
    public function authenticate(string $dni, string $password): AuthResult
    {
        // Solo se encarga de autenticar
        // Cache, validaci�n, refresh, etc.
    }
}
```

### Ventajas
-  API p�blica simple (AuthService)
-  Complejidad delegada a especialistas
-  Cada especialista es testeable por separado
-  F�cil agregar funcionalidad (nuevo especialista)

### Ejemplos en el Proyecto
- **AuthService** coordina: Authentication + Registration + Audit
- **ExerciseService** coordina: ExerciseCrud + ExerciseStats
- **TemplateService** coordina: DailyTemplate + WeeklyTemplate

### Para Entrevistas
> "Usamos Orchestration Pattern para servicios complejos. El orquestador (como AuthService) es la API p�blica y coordina servicios especializados. Por ejemplo, AuthService coordina AuthenticationService (autentica), UserRegistrationService (registra), y AuditService (audita). Cada especialista tiene una responsabilidad �nica y es testeable. El orquestador simplemente los coordina."

---

## 4. DTO (Data Transfer Object) Pattern

### Problema que Resuelve
- L Arrays asociativos sin tipo (`['user' => ..., 'token' => ...]`)
- L No hay autocompletado en IDE
- L Typos en keys (`$result['usr']` vs `$result['user']`)
- L Documentaci�n pobre

### Implementaci�n en el Proyecto

**C�digo real del proyecto:**
```php
namespace App\DTOs;

use App\Models\User;

/**
 * Resultado de autenticaci�n
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
$result->user;  // � IDE autocompleta
$result->refreshed;  // � Type-safe

// L SIN DTO (array asociativo)
public function authenticate(string $dni, string $password): array
{
    return ['user' => $user, 'refreshed' => $wasRefreshed];
}

// Uso propenso a errores
$result = $service->authenticate('12345678', 'pass');
$result['usr'];  // � Typo, no detectado hasta runtime
```

### Ventajas
-  Type safety
-  Autocompletado IDE
-  Documentaci�n clara
-  Inmutabilidad (readonly properties)
-  Validaci�n en construcci�n (si se agrega)

### D�nde se Usa
- `app/DTOs/AuthResult.php`
- Cualquier respuesta compleja de servicios

### Para Entrevistas
> "Usamos DTOs para transferir datos entre capas. En vez de arrays asociativos sin tipo, tenemos objetos tipados con readonly properties. Por ejemplo, AuthResult tiene user, fetchedFromApi, y refreshed. Esto nos da type safety, autocompletado en el IDE, y previene typos. Los DTOs son inmutables y documentan claramente la estructura de datos."

---

## 5. Factory Pattern (para Testing)

### Problema que Resuelve
- L Crear datos de prueba manualmente es tedioso
- L Datos hardcodeados en tests
- L Tests fr�giles (cambias estructura � rompen todos los tests)

### Implementaci�n en el Proyecto

**C�digo real:**
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

    // M�todos de estado (state methods)
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
    // Factory crea datos autom�ticamente
    $admin = User::factory()->admin()->create();
    $professor = User::factory()->professor()->create();

    // Test con datos realistas
    actingAs($admin)
        ->postJson('/api/admin/professors', [
            'user_id' => $professor->id
        ])
        ->assertOk();
});

// Crear m�ltiples
$users = User::factory()->count(10)->create();

// Combinar estados
$apiProfessor = User::factory()->apiUser()->professor()->create();
```

### Ventajas
-  Datos de prueba autom�ticos
-  Realistas (usa Faker)
-  Reutilizables
-  Mantenibles (cambias factory, no 100 tests)

### D�nde se Usa
- `database/factories/UserFactory.php`
- Factories para todos los modelos principales
- Usado en TODOS los tests

### Para Entrevistas
> "Usamos Factory Pattern para generar datos de prueba. Laravel Factories crean instancias de modelos con datos realistas usando Faker. Por ejemplo, UserFactory tiene m�todos como admin(), professor(), apiUser() que retornan usuarios con esos roles. En tests, hacemos User::factory()->admin()->create() y tenemos un admin listo. Si cambiamos la estructura de User, solo actualizamos el factory - no 100 tests."

---

## 6. Repository Pattern (via Eloquent)

### Problema que Resuelve
- L Queries SQL hardcodeadas en servicios
- L Dif�cil cambiar de DB (MySQL � PostgreSQL)
- L L�gica de queries duplicada

### Implementaci�n en el Proyecto

**Laravel Eloquent es un Repository Pattern "light"**

```php
// Eloquent como Repository
class User extends Model
{
    // Eloquent provee m�todos de acceso a datos
    public static function findByDni(string $dni): ?User
    {
        return static::where('dni', $dni)->first();
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class, 'created_by');
    }
}

// Uso en servicios (abstracci�n de DB)
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
-  Abstracci�n de base de datos
-  Query builder fluido
-  Relaciones Eloquent
-  F�cil testear (fake DB)

### D�nde se Usa
- **Todos los modelos** en `app/Models/`
- Servicios usan modelos Eloquent (no SQL directo)

### Para Entrevistas
> "Usamos Eloquent como Repository Pattern. Eloquent abstrae el acceso a datos: en vez de SQL directo, usamos m�todos como User::where('dni', $dni)->first(). Esto nos da portabilidad (cambiar DB es trivial), relaciones declarativas (hasMany, belongsTo), y query builder fluido. Los servicios trabajan con modelos Eloquent, no con SQL."

---

## 7. Middleware Pattern (Pipeline)

### Problema que Resuelve
- L L�gica de autenticaci�n/autorizaci�n duplicada en controllers
- L Concerns transversales (logging, CORS, rate limiting)
- L C�digo repetitivo en cada endpoint

### Implementaci�n en el Proyecto

**C�digo real:**
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

        // 3. Verificar permiso espec�fico
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

// Pipeline: Request � auth:sanctum � admin � Controller
```

**Sin middleware:**
```php
// L C�digo repetido en CADA m�todo
class AdminController {
    public function index() {
        if (!auth()->user()) return response()->json(..., 401);
        if (!auth()->user()->isAdmin()) return response()->json(..., 403);
        if (auth()->user()->account_status !== 'active') return response()->json(..., 403);
        // L�gica...
    }

    public function createProfessor() {
        if (!auth()->user()) return response()->json(..., 401);
        if (!auth()->user()->isAdmin()) return response()->json(..., 403);
        if (auth()->user()->account_status !== 'active') return response()->json(..., 403);
        // L�gica...
    }
}
```

### Ventajas
-  DRY (Don't Repeat Yourself)
-  Separation of concerns
-  Reutilizable
-  Testeable por separado
-  Pipeline claro (request � middleware1 � middleware2 � controller)

### Middleware en el Proyecto
- `EnsureAdmin` - Verifica rol admin
- `EnsureProfessor` - Verifica rol profesor
- `CheckLicense` - Verifica licencia activa
- Sanctum auth middleware (Laravel)

### Para Entrevistas
> "Usamos Middleware Pattern para concerns transversales. Por ejemplo, EnsureAdmin verifica que el usuario est� autenticado, sea admin, tenga permisos, y cuenta activa. Aplicamos middleware a grupos de rutas: todas las rutas admin pasan por auth:sanctum y admin middleware. Esto evita duplicar c�digo de autenticaci�n/autorizaci�n en cada controller. El request fluye por un pipeline: Request � auth � admin � controller."

---

## 8. Strategy Pattern

### Problema que Resuelve
- L L�gica condicional compleja (muchos if/else)
- L Dif�cil agregar nuevos algoritmos
- L Violaci�n de Open/Closed Principle

### Implementaci�n en el Proyecto

**UserType Enum con comportamiento:**
```php
enum UserType: string
{
    case LOCAL = 'local';
    case API = 'api';

    // M�todos de comportamiento (estrategias)
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
    // L�gica para usuario API
    $this->userRefreshService->refreshFromApi($user);
}

// L SIN Strategy Pattern (string comparison)
if ($user->user_type === 'api') {  // Magic string
    // L�gica...
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
-  Open/Closed (agregar estrategia sin modificar c�digo)
-  Type-safe (enums tipados)

### D�nde se Usa
- `app/Enums/UserType.php`
- `app/Enums/PromotionStatus.php`
- Servicios con m�ltiples "estrategias" (Registration, Refresh)

### Para Entrevistas
> "Usamos Strategy Pattern para algoritmos intercambiables. Por ejemplo, tenemos dos tipos de usuarios (LOCAL y API) con comportamientos diferentes. Usamos Enums con m�todos: UserType::LOCAL y UserType::API tienen m�todos isLocal(), isApi(). Esto elimina magic strings y condicionales complejos. Tambi�n en servicios: UserRegistrationService tiene registerLocal() y registerFromApi() - dos estrategias de registro."

---

## 9. Circuit Breaker Pattern

### Problema que Resuelve
- L Llamadas a APIs externas que fallan repetidamente
- L Timeouts largos afectan performance
- L Cascading failures (fallo en API externa tumba todo el sistema)

### Implementaci�n en el Proyecto

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
                        // Solo reintentar en errores de conexi�n
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

            // Retornar null en vez de lanzar excepci�n
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
-  Reintentos autom�ticos
-  Timeouts configurables
-  Graceful degradation

### Para Entrevistas
> "Implementamos Circuit Breaker en SociosApi para integraciones externas. Configuramos timeouts (10s), reintentos (3 intentos con 1s entre ellos), y fallback gracefully. Si la API externa falla, loggeamos el error pero retornamos null - no lanzamos excepci�n. Esto previene cascading failures: si la API del club est� ca�da, nuestro sistema sigue funcionando con datos locales."

---

## 10. Observer Pattern (via Laravel Events)

### Problema que Resuelve
- L Acoplamiento entre componentes (A necesita notificar a B, C, D)
- L L�gica de "efectos secundarios" en servicios principales
- L Dif�cil agregar nuevas reacciones a eventos

### Implementaci�n en el Proyecto

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
        // Log de auditor�a
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
// � Dispara autom�ticamente 3 listeners
```

### Ventajas
-  Desacoplamiento
-  F�cil agregar reacciones (nuevo listener)
-  Separation of concerns

### Para Entrevistas
> "Usamos Observer Pattern v�a Laravel Events para reaccionar a eventos del sistema. Por ejemplo, cuando un usuario es promovido (LOCAL � API), disparamos un evento UserPromoted. M�ltiples listeners reaccionan: uno env�a email, otro loggea, otro invalida cache. El servicio principal no conoce a los listeners - est�n desacoplados. Agregar una nueva reacci�n es solo crear un nuevo listener."

---

## 11. Value Object Pattern (via Enums PHP 8.1+)

### Problema que Resuelve
- L Magic strings/numbers (`'admin'`, `'local'`, `1`, `2`)
- L No type safety
- L Typos no detectados hasta runtime

### Implementaci�n en el Proyecto

**C�digo real:**
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
$user->user_type = 'api';  // Typo: 'apu' � No detectado

if ($user->user_type === 'api') {
    // String comparison, propenso a errores
}
```

### Ventajas
-  Type safety
-  No magic strings
-  M�todos en enums (comportamiento)
-  IDE autocompleta

### D�nde se Usa
- `UserType`, `PromotionStatus`
- Cualquier valor constante

### Para Entrevistas
> "Usamos Enums de PHP 8.1+ como Value Objects. Por ejemplo, UserType enum tiene LOCAL y API. Esto elimina magic strings, da type safety, y permite m�todos (isLocal(), label()). El IDE autocompleta y detecta typos en compile time. Los enums son inmutables y representan valores de dominio."

---

## 12. Facade Pattern (Laravel Facades)

### Problema que Resuelve
- L API compleja dif�cil de usar
- L Necesidad de instanciar clases repetidamente
- L C�digo verbose

### Implementaci�n en el Proyecto

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
-  Static calls (m�s legible)

### D�nde se Usa
- **Todo el proyecto** usa Laravel Facades
- Hash, Cache, Log, DB, Storage, etc.

### Para Entrevistas
> "Usamos Laravel Facades para APIs limpias. Facades son proxies est�ticos sobre servicios del contenedor. Por ejemplo, Hash::make() es m�s limpio que app('hash')->make(). Los facades son testeables: en tests hacemos Hash::shouldReceive() para mockearlos. Esto simplifica el c�digo sin perder testabilidad."

---

## Resumen de Patrones

| Patr�n | D�nde | Por Qu� | Beneficio Clave |
|--------|-------|---------|----------------|
| **Service Layer** | `app/Services/` | Separar l�gica de controllers | Testabilidad, reutilizaci�n |
| **Dependency Injection** | Todos los servicios | Desacoplamiento | Testeable con mocks |
| **Orchestration** | AuthService, ExerciseService | Coordinar especialistas | Complexity management |
| **DTO** | AuthResult | Transfer tipado | Type safety |
| **Factory** | UserFactory | Datos de prueba | Tests mantenibles |
| **Repository** | Eloquent | Abstracci�n DB | Portabilidad |
| **Middleware** | EnsureAdmin | Concerns transversales | DRY, separation |
| **Strategy** | UserType enum | Algoritmos intercambiables | Open/Closed |
| **Circuit Breaker** | SociosApi | Resiliencia | Fault tolerance |
| **Observer** | Events | Reacci�n a eventos | Desacoplamiento |
| **Value Object** | Enums | Valores inmutables | Type safety |
| **Facade** | Laravel Facades | API simplificada | Clean code |

---

## Para Entrevistas: Preguntas T�picas

### "�Qu� patrones de dise�o usaste?"

**Respuesta:**

> "Usamos varios patrones clave:
>
> 1. **Service Layer** para separar l�gica de negocio. Controllers de 10 l�neas, toda la l�gica en servicios.
>
> 2. **Dependency Injection** v�a constructor. Laravel resuelve autom�ticamente. Testeamos con mocks.
>
> 3. **Orchestration** para servicios complejos. AuthService coordina Authentication, Registration, y Audit services.
>
> 4. **DTO** para transferir datos tipados. AuthResult tiene user, fetchedFromApi, refreshed - type-safe.
>
> 5. **Circuit Breaker** en SociosApi para resiliencia. Reintentos, timeouts, fallback gracefully.
>
> 6. **Middleware** para autenticaci�n/autorizaci�n. Pipeline claro sin duplicar c�digo.
>
> Estos patrones resuelven problemas reales: testabilidad, mantenibilidad, escalabilidad."

### "�Por qu� usas DTOs en vez de arrays?"

**Respuesta:**

> "Los DTOs dan type safety y mejor developer experience:
>
> - **Type safety:** `$result->user` en vez de `$result['user']` - IDE detecta typos
> - **Autocompletado:** IDE sabe qu� propiedades existen
> - **Documentaci�n:** La clase documenta la estructura
> - **Inmutabilidad:** readonly properties
> - **Refactoring seguro:** Cambias DTO, IDE encuentra todos los usos
>
> Por ejemplo, AuthResult tiene user, fetchedFromApi, refreshed. Cualquier c�digo que use AuthResult tiene garant�a de tipo."

### "�C�mo manejan integraciones externas?"

**Respuesta:**

> "Usamos Circuit Breaker en SociosApi:
>
> - **Timeouts:** 10 segundos m�ximo
> - **Reintentos:** 3 intentos con 1s entre ellos
> - **Fallback:** Si falla, retornamos null - no lanzamos excepci�n
> - **Graceful degradation:** Sistema sigue funcionando con datos locales
>
> Esto previene cascading failures. Si la API del club est� ca�da, nuestro sistema sigue operativo."

---

## Pr�ximos Pasos

 **Parte 1:** Estructura General
 **Parte 2:** Arquitectura de Servicios
 **Parte 3:** Patrones de Dise�o

**Siguiente:**
- **Parte 4:** Stack Tecnol�gico detallado
- **Parte 5:** Sistema de Autenticaci�n completo
- **Parte 6:** Sistema de Gimnasio

---

**Documento para estudio y preparaci�n profesional**
**Proyecto:** Villa Mitre Server - Laravel 12 API Backend
**12 patrones implementados** con ejemplos reales
