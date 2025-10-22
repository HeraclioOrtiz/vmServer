# Parte 8: Preparación para Entrevistas - Guía Completa

## Introducción

Esta guía te prepara específicamente para **entrevistas técnicas** usando el proyecto Villa Mitre Server como caso de estudio. Incluye qué destacar en tu CV, respuestas preparadas, y cómo demostrar expertise.

---

## =Ä Para tu CV

### Descripción del Proyecto

```
Villa Mitre Server - API REST Backend para Gestión de Gimnasio


Desarrollé una API REST completa en Laravel 12 para sistema de gestión de
gimnasio del Club Villa Mitre, con integración a API externa del club y
sistema de templates de ejercicios para profesores y estudiantes.

STACK TÉCNICO:
" Backend: Laravel 12, PHP 8.2+ (enums, readonly, match expressions)
" Autenticación: Laravel Sanctum (token-based, stateless)
" Base de Datos: MySQL 8.0+ (prod), SQLite in-memory (testing)
" Testing: PHPUnit 11.5, Mockery, Faker (95%+ cobertura)
" Arquitectura: Service-Oriented, Domain-Driven Design principles

CARACTERÍSTICAS CLAVE:
" Sistema dual de usuarios (local + sincronización automática con API externa)
" Arquitectura de servicios (27 servicios en 6 dominios)
" 12 patrones de diseño implementados (DI, DTO, Circuit Breaker, etc.)
" Sistema de templates jerárquico (Exercise ’ Daily ’ Weekly ’ Assignment)
" Testing exhaustivo (Unit + Feature, 95%+ coverage)
" Auto-refresh transparente de datos cada 24h
" Panel de administración completo con auditoría

LOGROS:
" 95%+ cobertura de tests (Unit + Feature)
" Integración resiliente con API externa (Circuit Breaker pattern)
" Sistema de clonación de templates para integridad histórica
" PasswordValidationService que previno crash en producción
" Documentación completa (CLAUDE.md + 20+ docs técnicos)
```

### Sección de Skills

```
BACKEND:
" PHP (8.2+): Enums, Readonly Properties, Match Expressions, Typed Properties
" Laravel (12): Eloquent, Sanctum, Migrations, Factories, Queues
" Arquitectura: Service Layer, SOA, DDD, Clean Architecture principles
" Patrones: Dependency Injection, DTO, Repository, Circuit Breaker, Strategy

TESTING:
" PHPUnit, Mockery, Pest syntax
" Unit Testing (servicios aislados con mocks)
" Feature Testing (flujo completo HTTP)
" 95%+ cobertura, TDD cuando aplica

DATABASES:
" MySQL 8.0+ (foreign keys, transactions, JSON columns)
" SQLite (testing in-memory)
" Eloquent ORM (relaciones complejas, query optimization)

API DESIGN:
" RESTful APIs
" Token-based auth (Sanctum)
" Rate limiting, middleware stack
" Circuit breaker para integraciones externas
```

---

## <¯ Elevator Pitch (30 segundos)

**Contexto:** Te preguntan "Cuéntame sobre tu proyecto más reciente"

```
"Desarrollé Villa Mitre Server, una API REST en Laravel 12 para
gestión de gimnasio. Lo interesante es el sistema dual de usuarios:
tenemos usuarios locales y usuarios sincronizados desde la API del
club que se actualizan automáticamente cada 24h de forma transparente.

Implementé arquitectura de servicios con 27 servicios organizados
por dominios, 12 patrones de diseño, y 95%+ cobertura de tests.

La integración externa usa Circuit Breaker pattern para resiliencia.
El sistema de templates permite a profesores crear rutinas complejas
y asignarlas a estudiantes, clonando los datos para mantener
integridad histórica.

Uso PHP 8.2 con features modernas como enums tipados, readonly
properties, y match expressions. Todo testeado con PHPUnit usando
SQLite in-memory."
```

---

## <¤ Preguntas Comunes y Respuestas Preparadas

### 1. Arquitectura

#### "¿Qué arquitectura usaste y por qué?"

**Respuesta estructurada:**

> "Usé **Service-Oriented Architecture** con principios de **Domain-Driven Design**.
>
> **Estructura:**
> - Controllers ligeros (10-30 líneas): solo HTTP layer
> - Services (27 servicios): TODA la lógica de negocio
> - Organización por dominios: Auth, Gym, Admin, User, External, Core
>
> **Por qué:**
> 1. **Testabilidad:** Servicios aislados, testeo con mocks
> 2. **Reutilización:** Un servicio usado por controllers, commands, jobs
> 3. **Mantenibilidad:** Cada servicio = una responsabilidad (SRP)
> 4. **Escalabilidad:** Futuros microservicios (dominios ya definidos)
>
> **Ejemplo concreto:**
> AuthService coordina AuthenticationService (autenticar), UserRegistrationService (registrar), AuditService (auditar). Dependency Injection vía constructor. Laravel resuelve automáticamente."

#### "¿Cómo manejas la complejidad?"

**Respuesta:**

> "Uso el **Orchestration Pattern** para servicios complejos:
>
> - **Orquestadores:** API pública simple (AuthService, ExerciseService)
> - **Especialistas:** Lógica específica (AuthenticationService, PasswordValidationService)
>
> El orquestador coordina especialistas. Cada especialista tiene UNA responsabilidad y es testeable por separado.
>
> Ejemplo: AuthService.authenticate() delega autenticación a AuthenticationService, auditoría a AuditService. Si algo falla, el orquestador maneja el error y loggea. Clean y mantenible."

### 2. Decisiones Técnicas

#### "¿Por qué Laravel sobre otros frameworks?"

**Respuesta:**

> "Elegí Laravel 12 por razones específicas del proyecto:
>
> 1. **Eloquent ORM:** Relaciones complejas (templates ’ assignments ’ sets). Eloquent maneja esto elegantemente.
> 2. **Sanctum built-in:** Token auth perfecto para API REST. No necesitamos OAuth.
> 3. **Service Container:** DI automática. Crítico para arquitectura de servicios.
> 4. **Ecosystem:** Tinker, Pint, Pail. Developer experience excelente.
> 5. **PHP 8.2+:** Enums, readonly, match. Type safety moderna.
>
> Consideré Symfony (más verbose), Lumen (muy minimalista), y Node.js (Express). Laravel ganó por productividad sin sacrificar calidad."

#### "¿Cómo integraste la API externa?"

**Respuesta:**

> "Implementé **Circuit Breaker pattern** en SociosApi:
>
> **Configuración:**
> - Timeout: 10 segundos
> - Reintentos: 3 intentos con 1s entre ellos
> - Fallback: Si falla, retorno null (no lanzamos excepción)
>
> **Código:**
> ```php
> try {
>     $response = Http::timeout(10)->retry(3, 1000)->get($url);
>     return $response->json();
> } catch (RequestException $e) {
>     Log::error('API failed', ['error' => $e->getMessage()]);
>     return null;  // Graceful degradation
> }
> ```
>
> **Resultado:** Si API del club está caída, nuestro sistema sigue funcionando con datos locales. Previene cascading failures."

### 3. Desafíos Técnicos

#### "¿Cuál fue el desafío técnico más difícil?"

**Respuesta:**

> "El **sistema dual de usuarios con auto-sync transparente**.
>
> **Problema:** Tenemos usuarios locales (registrados en app) y usuarios API (socios del club). Los usuarios API deben sincronizarse automáticamente con la API externa cada 24h, pero de forma transparente.
>
> **Solución:**
> 1. Campo `user_type` enum (LOCAL | API)
> 2. Método `User->needsRefresh()` verifica última sync
> 3. En cada login, si es API user y >24h, auto-refresh
> 4. SociosApi con Circuit Breaker (resiliencia)
> 5. SocioDataMappingService mapea datos externos ’ modelo interno
>
> **Resultado:** Usuario API siempre tiene datos actualizados, sin saber que pasó. Performance: cache-first strategy. Si API falla, continuamos con datos cached.
>
> **Extra:** Soportamos promoción: usuario LOCAL se hace socio del club ’ lo promovemos a API user automáticamente."

#### "¿Qué bug/issue crítico resolviste?"

**Respuesta:**

> "**PasswordValidationService** que previno un crash en producción.
>
> **Problema:** `Hash::check()` puede lanzar excepciones con datos malformados (null, strings inválidos). Esto crasheaba la app en login.
>
> **Solución:** Servicio dedicado:
> ```php
> public function validate(User $user, string $password): void {
>     try {
>         if (!Hash::check($password, $user->password)) {
>             throw ValidationException::withMessages(['password' => ['Invalid']]);
>         }
>     } catch (\Exception $e) {
>         $this->logCriticalError($user, $e);
>         throw ValidationException::withMessages(['password' => ['Error']]);
>     }
> }
> ```
>
> **Resultado:** Nunca más crasheos. Siempre usamos `$this->passwordValidationService->validate()` en vez de `Hash::check()` directamente. Logging para debugging. Error handling robusto."

### 4. Testing

#### "Explica tu estrategia de testing"

**Respuesta:**

> "95%+ cobertura con dos capas:
>
> **Unit Tests:**
> - Servicios aislados con mocks (Mockery)
> - Ejemplo: AuthenticationService mockeando CacheService, PasswordValidationService
> - Rápidos (milisegundos), deterministas
>
> **Feature Tests:**
> - Flujo completo: Request ’ Controller ’ Service ’ DB ’ Response
> - Ejemplo: POST /api/auth/login ’ verifico status 200, token generado, user en response
> - Con RefreshDatabase (rollback automático)
>
> **SQLite in-memory:** 10x más rápido que MySQL, cada test tiene DB limpia, CI/CD friendly.
>
> **Factories:** User::factory()->admin()->create() genera datos realistas con Faker.
>
> Ejecutamos en GitHub Actions: tests en paralelo, coverage check (>90%), upload a Codecov."

#### "¿Cómo testeas la integración con API externa?"

**Respuesta:**

> "Dos enfoques:
>
> **1. Unit tests con mocks:**
> ```php
> $mockSociosApi = Mockery::mock(SociosApi::class);
> $mockSociosApi->shouldReceive('getSociusByDni')
>               ->andReturn(['id' => 123, 'nombre' => 'Juan']);
> ```
> Testeamos lógica de UserRefreshService sin llamar API real.
>
> **2. Tests manuales/E2E (opcional):**
> - Environment de staging con API externa de test
> - Verificamos integración real
> - Pero no en CI (no queremos dependencia externa)
>
> En producción: Circuit Breaker previene issues. Si API falla, loggeamos pero no crasheamos."

### 5. Patrones de Diseño

#### "¿Qué patrones implementaste?"

**Respuesta:**

> "12 patrones implementados:
>
> **Los más importantes:**
>
> 1. **Service Layer:** Toda lógica en servicios, controllers ligeros
> 2. **Dependency Injection:** Constructor injection en todos los servicios
> 3. **DTO:** AuthResult, no arrays asociativos. Type safety.
> 4. **Circuit Breaker:** Resiliencia en SociosApi
> 5. **Factory:** User::factory() para testing
> 6. **Orchestration:** AuthService coordina especialistas
>
> **Ejemplo DTO:**
> ```php
> class AuthResult {
>     public function __construct(
>         public readonly User $user,
>         public readonly bool $refreshed
>     ) {}
> }
> ```
> Type-safe, inmutable, IDE autocomplete. En vez de array con typos.
>
> **Ejemplo Circuit Breaker:**
> Timeout 10s, 3 reintentos, si falla ’ null (no exception). Sistema sigue funcionando."

### 6. Sistema de Gimnasio

#### "Explica el sistema de templates"

**Respuesta:**

> "Jerarquía de 4 niveles:
>
> **1. Exercise:** Ejercicio base (press banca). muscle_group, equipment, difficulty
>
> **2. DailyTemplate:** Rutina de UN día. Contiene múltiples ejercicios con sets (reps, weight, rest_time)
>
> **3. WeeklyTemplate:** Rutina de UNA semana (7 daily templates). Metadata: category, difficulty, target_goals
>
> **4. WeeklyAssignment:** Asignación a estudiante. **CLONAMOS** todos los sets a AssignedSet.
>
> **¿Por qué clonar?**
> Integridad histórica. Si profesor edita template, no afecta asignaciones ya entregadas. Estudiante tiene snapshot del momento de asignación.
>
> **API Mobile:**
> GET /api/gym/my-week ’ rutina activa con progreso
> POST /api/gym/complete-set ’ marca set como completado (reps_completed, weight_used)
>
> **Servicios:** ExerciseService, DailyTemplateService, WeeklyTemplateService, WeeklyAssignmentService. Todo transaccional (DB::transaction)."

### 7. Escalabilidad

#### "¿Cómo escalaría este proyecto?"

**Respuesta:**

> "Varios enfoques:
>
> **Corto plazo (sin cambiar arquitectura):**
> 1. **Read replicas MySQL:** Queries de lectura a replicas
> 2. **Redis cache:** Cache más agresivo (usuarios, templates)
> 3. **Queue workers:** Procesos pesados a queues
> 4. **CDN:** Assets estáticos
>
> **Mediano plazo (optimizaciones):**
> 1. **Eager loading:** Eliminar N+1 queries
> 2. **Database indexes:** Optimizar queries lentas
> 3. **API rate limiting:** Por usuario, no global
> 4. **Pagination:** Todas las listas
>
> **Largo plazo (arquitectura):**
> 1. **Microservicios:** Ya tenemos dominios definidos (Auth, Gym, Admin)
>     - AuthService ’ Auth microservice
>     - GymService ’ Gym microservice
> 2. **Event-driven:** Comunicación entre microservicios vía eventos
> 3. **GraphQL:** En vez de REST (si clientes necesitan flexibilidad)
>
> **Monitoreo:** NewRelic/Datadog para identificar bottlenecks. Prometheus + Grafana para métricas."

### 8. Seguridad

#### "¿Qué medidas de seguridad implementaste?"

**Respuesta:**

> "Múltiples capas:
>
> **Autenticación:**
> - Sanctum token-based (tokens hasheados con SHA-256)
> - Bcrypt para passwords
> - Token expiration configurable
>
> **Autorización:**
> - Middleware stack (auth:sanctum, admin, professor)
> - Role-based access control (is_admin, is_professor, student_gym)
>
> **Rate Limiting:**
> - 5 intentos/min en login
> - Configurable por ruta
>
> **Validación:**
> - FormRequests en TODAS las entradas
> - Password strength validation (8 chars, uppercase, number)
> - PasswordValidationService para prevenir crashes
>
> **Auditoría:**
> - AuditService loggea: login exitoso/fallido, CRUD crítico, cambios de roles
> - Tabla audit_logs para compliance
>
> **Best practices:**
> - HTTPS only en prod
> - CSRF protection
> - SQL injection prevention (Eloquent)
> - XSS prevention (Laravel escaping)
>
> **Password Reset:**
> - Tokens de un solo uso
> - Expiración 60 minutos
> - Revocación de todos los tokens al resetear"

---

## =¡ Preguntas para Hacer al Entrevistador

**Al final de la entrevista, siempre pregunta:**

1. "¿Qué stack técnico usa el equipo actualmente?"
2. "¿Cómo es el proceso de code review?"
3. "¿Qué cobertura de tests tienen?"
4. "¿Usan CI/CD? ¿Qué herramientas?"
5. "¿Cómo manejan integraciones con sistemas externos?"
6. "¿Cuál es el desafío técnico más interesante que tiene el equipo ahora?"

---

## =Ê Métricas para Destacar

| Métrica | Valor | Impacto |
|---------|-------|---------|
| **Cobertura de tests** | 95%+ | Calidad, confiabilidad |
| **Servicios** | 27 en 6 dominios | Arquitectura escalable |
| **Patrones** | 12 implementados | Buenas prácticas |
| **Líneas de código** | ~15,000 PHP | Proyecto substancial |
| **Endpoints API** | 50+ rutas | Funcionalidad completa |
| **Performance** | SQLite 10x faster | Testing eficiente |
| **Documentación** | 20+ docs MD | Mantenibilidad |

---

## <“ Preparación Final

### Día Antes de la Entrevista

1. **Repasa Partes 1-7** de estos documentos
2. **Practica elevator pitch** en voz alta
3. **Revisa código real** de servicios clave:
   - `app/Services/Auth/AuthService.php`
   - `app/Services/Auth/AuthenticationService.php`
   - `app/Services/Gym/WeeklyTemplateService.php`
4. **Prepara ejemplos concretos** de código para mostrar
5. **Duerme bien** =4

### Durante la Entrevista

1. **Estructura respuestas:** Contexto ’ Problema ’ Solución ’ Resultado
2. **Usa ejemplos concretos** del código real
3. **Menciona números:** 95% coverage, 27 servicios, 50+ endpoints
4. **Demuestra pensamiento crítico:** "También consideré X pero elegí Y porque..."
5. **Sé honesto:** Si no sabes algo, di "No tengo experiencia con eso, pero aprendería rápido"

### Errores a Evitar

- L Respuestas genéricas ("Usé buenas prácticas")
- L No dar ejemplos concretos
- L Mentir sobre conocimientos
- L No preparar preguntas para el entrevistador
- L Hablar mal de proyectos/equipos anteriores

---

## <¯ Checklist Final

```
¡ CV actualizado con Villa Mitre Server
¡ Elevator pitch practicado (30 segundos)
¡ Partes 1-7 leídas y comprendidas
¡ Código de servicios clave revisado
¡ Respuestas a las 8 preguntas comunes practicadas
¡ 5 preguntas para el entrevistador preparadas
¡ GitHub/GitLab profile actualizado (si aplica)
¡ Portfolio/LinkedIn con proyecto destacado
```

---

## =€ Mensaje Final

**Has construido un proyecto profesional y completo.** Tienes:

 Arquitectura sólida (SOA + DDD)
 12 patrones de diseño
 95%+ cobertura de tests
 Integración externa resiliente
 Documentación exhaustiva
 Decisiones técnicas justificadas

**Confía en tu trabajo. Sabes de lo que hablas.**

Este no es un proyecto de tutorial. Es un sistema real con complejidad real, decisiones reales, y soluciones reales. Demuestra:

- Pensamiento arquitectónico
- Buenas prácticas
- Testing serio
- Code quality
- Capacidad de resolver problemas complejos

**Éxito en tus entrevistas! <‰**

---

**Documento para estudio y preparación profesional**
**Proyecto:** Villa Mitre Server - Preparación Completa para Entrevistas
