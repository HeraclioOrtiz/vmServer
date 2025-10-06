# 🧪 Testing Guide - Villa Mitre Server

## 📋 **Estructura de Tests**

### **Organización por Capas**
```
tests/
├── Unit/                      # Tests unitarios
│   ├── Auth/                  # Servicios de autenticación
│   │   ├── AuthServiceTest.php
│   │   ├── AuthenticationServiceTest.php
│   │   ├── PasswordValidationServiceTest.php
│   │   └── UserRegistrationServiceTest.php
│   ├── Admin/                 # Servicios de administración
│   │   ├── UserManagementServiceTest.php
│   │   ├── ProfessorManagementServiceTest.php
│   │   └── AuditLogServiceTest.php
│   ├── External/              # Servicios externos
│   │   └── SocioDataMappingServiceTest.php
│   ├── User/                  # Servicios de usuario
│   │   ├── UserRefreshServiceTest.php
│   │   └── UserPromotionServiceTest.php
│   └── Core/                  # Servicios centrales
│       ├── CacheServiceTest.php
│       └── AuditServiceTest.php
├── Feature/                   # Tests de integración
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegistrationTest.php
│   ├── Admin/
│   │   ├── UserManagementTest.php
│   │   └── ProfessorManagementTest.php
│   └── Gym/
│       ├── AdminAccessTest.php
│       ├── AdminExerciseTest.php
│       ├── AdminWeeklyAssignmentTest.php
│       └── MobileMyPlanTest.php
└── TestCase.php               # Clase base para tests
```

## 🎯 **Tipos de Tests**

### **1. Tests Unitarios**
Testean servicios individuales con dependencias mockeadas.

**Ejemplo - AuthServiceTest:**
```php
/** @test */
public function it_authenticates_user_successfully()
{
    // Arrange
    $user = User::factory()->create(['dni' => '12345678']);
    $authResult = new AuthResult($user, false, false);

    $this->mockAuthenticationService
        ->shouldReceive('authenticate')
        ->with('12345678', 'password123')
        ->once()
        ->andReturn($authResult);

    // Act
    $result = $this->authService->authenticate('12345678', 'password123');

    // Assert
    $this->assertInstanceOf(AuthResult::class, $result);
    $this->assertEquals($user->id, $result->user->id);
}
```

### **2. Tests de Integración (Feature)**
Testean endpoints completos con base de datos real.

**Ejemplo - LoginTest:**
```php
/** @test */
public function user_can_login_with_valid_credentials()
{
    // Arrange
    $user = User::factory()->create([
        'dni' => '12345678',
        'password' => Hash::make('password123')
    ]);

    // Act
    $response = $this->postJson('/api/auth/login', [
        'dni' => '12345678',
        'password' => 'password123'
    ]);

    // Assert
    $response->assertStatus(200)
             ->assertJsonStructure([
                 'token',
                 'user' => ['id', 'dni', 'name']
             ]);
}
```

## 🔧 **Configuración de Testing**

### **PHPUnit Configuration**
```xml
<!-- phpunit.xml -->
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
    </php>
</phpunit>
```

### **Base TestCase**
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configuración común para todos los tests
        $this->artisan('migrate:fresh');
        $this->seed();
    }
}
```

## 🎭 **Mocking y Factories**

### **User Factory**
```php
<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'dni' => $this->faker->unique()->numerify('########'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'user_type' => UserType::LOCAL,
            'account_status' => 'active',
        ];
    }

    public function professor(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_professor' => true,
            'professor_since' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'permissions' => ['user_management', 'gym_admin'],
        ]);
    }

    public function apiUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserType::API,
            'socio_id' => $this->faker->randomNumber(5),
            'estado_socio' => 'ACTIVO',
            'semaforo' => 1,
        ]);
    }
}
```

### **Mocking Servicios Externos**
```php
// En tests que usan SociosApi
protected function setUp(): void
{
    parent::setUp();
    
    $this->mockSociosApi = Mockery::mock(SociosApiInterface::class);
    $this->app->instance(SociosApiInterface::class, $this->mockSociosApi);
}

/** @test */
public function it_fetches_user_from_external_api()
{
    // Arrange
    $socioData = [
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'email' => 'juan@example.com'
    ];

    $this->mockSociosApi
        ->shouldReceive('getSocioPorDni')
        ->with('12345678')
        ->once()
        ->andReturn($socioData);

    // Act & Assert...
}
```

## 🔒 **Testing de Seguridad**

### **Tests de Autenticación**
```php
/** @test */
public function it_prevents_access_without_token()
{
    $response = $this->getJson('/api/admin/users');
    
    $response->assertStatus(401)
             ->assertJson(['message' => 'Unauthenticated.']);
}

/** @test */
public function it_prevents_non_admin_access_to_admin_endpoints()
{
    $user = User::factory()->create(['is_admin' => false]);
    
    $response = $this->actingAs($user)
                     ->getJson('/api/admin/users');
    
    $response->assertStatus(403)
             ->assertJson(['message' => 'Forbidden. Admin role required.']);
}
```

### **Tests de Validación de Passwords**
```php
/** @test */
public function it_handles_critical_password_validation_errors()
{
    // Test específico para el bug crítico reportado
    $user = User::factory()->create([
        'dni' => '58964605',
        'password' => Hash::make('Zzxx4518688')
    ]);

    // Este test asegura que no hay crash del servidor
    try {
        $this->passwordValidationService->validate($user, 'Zzxx4518688');
        $this->assertTrue(true);
    } catch (ValidationException $e) {
        // Validación normal - OK
        $this->assertStringContains('Credenciales', $e->getMessage());
    } catch (\Exception $e) {
        // Cualquier otro error es un fallo crítico
        $this->fail('Critical error should be handled: ' . $e->getMessage());
    }
}
```

## 📊 **Tests de Performance**

### **Testing de Carga**
```php
/** @test */
public function it_handles_multiple_concurrent_logins()
{
    $users = User::factory()->count(50)->create();
    
    $responses = [];
    foreach ($users as $user) {
        $responses[] = $this->postJson('/api/auth/login', [
            'dni' => $user->dni,
            'password' => 'password123'
        ]);
    }
    
    foreach ($responses as $response) {
        $response->assertStatus(200);
    }
}
```

### **Testing de Cache**
```php
/** @test */
public function it_caches_user_data_efficiently()
{
    $user = User::factory()->create(['dni' => '12345678']);
    
    // Primera llamada - debe hacer query a DB
    $this->cacheService->getUser('12345678');
    
    // Segunda llamada - debe usar cache
    $cachedUser = $this->cacheService->getUser('12345678');
    
    $this->assertEquals($user->id, $cachedUser->id);
}
```

## 🎯 **Tests Específicos por Servicio**

### **AuthenticationService Tests**
- ✅ Autenticación desde cache
- ✅ Autenticación desde base de datos
- ✅ Refresh automático de usuarios API
- ✅ Manejo de usuarios no encontrados
- ✅ Validación de credenciales sin autenticar

### **PasswordValidationService Tests**
- ✅ Validación de passwords correctas
- ✅ Manejo de passwords incorrectas
- ✅ **Manejo de errores críticos** (bug específico)
- ✅ Validación de fortaleza de passwords
- ✅ Logging de intentos fallidos
- ✅ Hashing seguro de passwords

### **UserManagementService Tests**
- ✅ Filtrado avanzado de usuarios
- ✅ Creación de usuarios con validaciones
- ✅ Prevención de creación de admins por no-super-admins
- ✅ Actualización de usuarios
- ✅ Suspensión y activación
- ✅ Asignación/remoción de roles

### **SocioDataMappingService Tests**
- ✅ Mapeo completo de datos de socio
- ✅ Manejo de campos faltantes
- ✅ Sanitización de strings
- ✅ Validación de emails y URLs
- ✅ Parsing de valores numéricos
- ✅ Manejo de fechas inválidas
- ✅ Validación de datos mapeados

## 🚀 **Comandos de Testing**

### **Ejecutar Tests**
```bash
# Todos los tests
php artisan test

# Solo tests unitarios
php artisan test --testsuite=Unit

# Solo tests de integración
php artisan test --testsuite=Feature

# Tests específicos
php artisan test tests/Unit/Auth/AuthServiceTest.php

# Con coverage
php artisan test --coverage

# Tests en paralelo
php artisan test --parallel
```

### **Tests con Filtros**
```bash
# Tests que contengan "password" en el nombre
php artisan test --filter password

# Tests de un grupo específico
php artisan test --group auth

# Excluir tests lentos
php artisan test --exclude-group slow
```

## 📈 **Métricas de Testing**

### **Cobertura de Código**
- **Objetivo**: 80%+ de cobertura
- **Servicios críticos**: 95%+ (Auth, Password, UserManagement)
- **Controladores**: 70%+ (lógica delegada a servicios)

### **Performance Benchmarks**
- **Tests unitarios**: < 100ms por test
- **Tests de integración**: < 500ms por test
- **Suite completa**: < 2 minutos

### **Calidad de Tests**
- **Assertions por test**: 2-5 assertions
- **Setup/teardown**: Mínimo y reutilizable
- **Mocking**: Solo dependencias externas
- **Datos de prueba**: Factories en lugar de fixtures

## 🔍 **Debugging Tests**

### **Tests Fallidos**
```bash
# Ejecutar con output detallado
php artisan test --verbose

# Parar en el primer fallo
php artisan test --stop-on-failure

# Debug específico
php artisan test --filter test_name --debug
```

### **Logging en Tests**
```php
// En tests, usar Log::info() para debugging
Log::info('Test data:', ['user' => $user->toArray()]);

// O usar dump() para output inmediato
dump($response->json());
```

## ✅ **Checklist de Testing**

### **Antes de Commit**
- [ ] Todos los tests pasan
- [ ] Cobertura de código > 80%
- [ ] No hay tests skipped sin razón
- [ ] Tests de seguridad incluidos
- [ ] Performance tests para cambios críticos

### **Antes de Deploy**
- [ ] Suite completa de tests ejecutada
- [ ] Tests de integración con APIs externas
- [ ] Tests de carga básicos
- [ ] Validación de configuración de producción

### **Tests Críticos (Siempre Ejecutar)**
- [ ] AuthenticationService (por bug crítico reportado)
- [ ] PasswordValidationService (manejo de errores)
- [ ] UserManagementService (permisos y roles)
- [ ] SocioDataMappingService (validación de datos externos)

**Los tests están diseñados para prevenir regresiones y asegurar que el sistema funcione correctamente, especialmente considerando el bug crítico de autenticación reportado.** 🛡️
