# 🤖 Estrategia de Agentes por Complejidad - Villa Mitre Server

**Fecha:** 21 de Octubre 2025
**Objetivo:** Optimizar costos usando el modelo apropiado para cada tipo de tarea

---

## 📊 Niveles de Modelos

### 1️⃣ Haiku (Más Económico) 💰
**Ideal para:** Tareas simples, repetitivas, automáticas
**Costo:** ~$0.25/1M tokens input, ~$1.25/1M tokens output
**Límite de contexto:** 200K tokens

### 2️⃣ Sonnet (Intermedio) 💰💰
**Ideal para:** Implementación, refactoring, código estándar
**Costo:** ~$3/1M tokens input, ~$15/1M tokens output
**Límite de contexto:** 200K tokens

### 3️⃣ Opus (Más Costoso) 💰💰💰
**Ideal para:** Análisis profundo, arquitectura, decisiones complejas
**Costo:** ~$15/1M tokens input, ~$75/1M tokens output
**Límite de contexto:** 200K tokens

---

## 🎯 Clasificación de Tareas para Villa Mitre

### 🟢 Nivel 1: HAIKU (Tareas Simples)

#### Git Operations
- ✅ `git add`, `git commit`, `git push`
- ✅ Crear branches
- ✅ Merge simple (sin conflictos)
- ✅ Git status, log, diff

**Ejemplo de uso:**
```bash
# Haiku puede manejar perfectamente:
git add .
git commit -m "feat: Add password reset endpoint"
git push origin refactor/code-audit-and-improvements
```

#### File Operations
- ✅ Lectura de archivos específicos
- ✅ Búsqueda de patrones (grep)
- ✅ Listado de archivos (glob)
- ✅ Copiar/mover archivos

#### Testing Simple
- ✅ Ejecutar tests existentes
- ✅ Verificar que tests pasen
- ✅ Linter / formateo de código

**Ejemplo:**
```bash
# Haiku ejecuta y reporta resultados:
php artisan test --filter=PasswordResetServiceTest
php artisan pint
```

#### Documentación Menor
- ✅ Actualizar versiones en README
- ✅ Agregar entradas a CHANGELOG
- ✅ Formatear markdown existente

#### Tasks Automáticas
- ✅ Limpiar cache (`php artisan cache:clear`)
- ✅ Migrations simples (`php artisan migrate`)
- ✅ Seeders (`php artisan db:seed`)

**Prompt sugerido para Haiku:**
```
"Ejecuta los tests unitarios de PasswordResetService y reporta los resultados"
"Haz commit de los cambios con mensaje: 'feat: Implement password reset service'"
"Lista todos los archivos .php en app/Services/Auth/"
```

---

### 🟡 Nivel 2: SONNET (Implementación Estándar)

#### Implementación de Features
- ✅ Crear servicios siguiendo patrones existentes
- ✅ Crear controllers RESTful estándar
- ✅ Implementar CRUD básico
- ✅ Form Requests con validaciones

**Ejemplo: Password Reset Implementation**
```
"Implementa PasswordResetService.php siguiendo el patrón de AuthService.
Debe incluir métodos: requestReset, validateToken, resetPassword.
Usa PasswordValidationService para validar contraseñas."
```

#### Refactoring Estándar
- ✅ Extraer métodos duplicados
- ✅ Mover hardcoded values a config
- ✅ Rename variables/métodos
- ✅ Simplificar lógica condicional

**Ejemplo: P1 - DNI Hardcodeado**
```
"Refactoriza UserPromotionService para mover el DNI hardcodeado '22222222'
a config/gym.php. Agrega feature flag auto_assign_students."
```

#### Testing Intermedio
- ✅ Escribir tests unitarios para servicios
- ✅ Tests de integración para endpoints
- ✅ Mocks y factories estándar

**Ejemplo:**
```
"Escribe tests unitarios para PasswordResetService cubriendo:
- requestReset con usuario local (success)
- requestReset con usuario API (debe fallar)
- validateToken con token válido/inválido
- resetPassword success/failure"
```

#### Migrations y Models
- ✅ Crear migrations estándar
- ✅ Agregar campos a models existentes
- ✅ Relaciones Eloquent básicas
- ✅ Scopes y accessors simples

#### Bug Fixes Simples
- ✅ Fix de validaciones
- ✅ Corrección de queries
- ✅ Ajustes de respuestas API

**Prompt sugerido para Sonnet:**
```
"Implementa WeeklyAssignmentController siguiendo el patrón de ExerciseController.
Incluye: index, store, show, update, destroy. Usa WeeklyAssignmentService."

"Refactoriza ExerciseService extrayendo la lógica de cache a CacheService.
Mantén la misma funcionalidad pero usa el servicio centralizado."
```

---

### 🔴 Nivel 3: OPUS (Análisis y Decisiones Complejas)

#### Arquitectura y Diseño
- ✅ Diseño de nuevos sistemas (como Password Recovery)
- ✅ Decisiones de patrones arquitectónicos
- ✅ Refactoring de servicios grandes (>400 líneas)
- ✅ Optimización de performance compleja

**Ejemplo: P4 - Split TemplateService**
```
"Analiza TemplateService (623 líneas) y propón una estrategia de división
en servicios más pequeños. Considera:
- Responsabilidades actuales
- Dependencias entre métodos
- Impacto en controllers existentes
- Plan de migración sin breaking changes"
```

#### Análisis de Codebase
- ✅ Code audits completos
- ✅ Identificación de code smells
- ✅ Análisis de deuda técnica
- ✅ Propuestas de mejora priorizadas

**Ejemplo:**
```
"Analiza app/Services/Gym/ completo y genera:
1. Métricas de complejidad por archivo
2. Código duplicado entre servicios
3. Oportunidades de refactoring
4. Priorización por impacto/esfuerzo"
```

#### Resolución de Problemas Complejos
- ✅ Bugs difíciles de reproducir
- ✅ Issues de performance
- ✅ Problemas de arquitectura
- ✅ Decisiones de trade-offs técnicos

**Ejemplo:**
```
"Investigá por qué TemplateService tiene queries N+1 en getWeeklyTemplate.
Proponé solución con eager loading optimizada sin romper la API existente."
```

#### Diseño de APIs
- ✅ Diseño de endpoints complejos
- ✅ Versionado de API
- ✅ Estrategias de migración
- ✅ Backwards compatibility

#### Security Audits
- ✅ Análisis de vulnerabilidades
- ✅ Revisión de autenticación/autorización
- ✅ Validación de input sanitization
- ✅ Propuestas de hardening

**Prompt sugerido para Opus:**
```
"Analiza el sistema de autenticación dual (local + API) y propone mejoras
de seguridad considerando:
- Rate limiting actual
- Token management
- Password policies
- Session handling
- Riesgos de promoción de usuarios
Dame un informe priorizado con esfuerzo estimado."
```

---

## 🎨 Workflows Recomendados

### Workflow 1: Implementar Nueva Feature (P8 - Password Recovery)

#### Fase 1: Diseño (OPUS) 💰💰💰
```
Tiempo: 30 min
Costo: ~$2-3

"Diseña sistema completo de password recovery para sistema dual (local + API).
Incluye: arquitectura, endpoints, servicios, seguridad, casos edge."
```

**Output esperado:**
- Documento de diseño completo
- Diagrama de flujo
- Lista de archivos a crear/modificar
- Consideraciones de seguridad

#### Fase 2: Implementación Backend (SONNET) 💰💰
```
Tiempo: 2-3 horas
Costo: ~$5-8

"Implementa PasswordResetService y PasswordResetController según diseño.
Usa patrones existentes de AuthService. Incluye validaciones."
```

**Output esperado:**
- Service completamente funcional
- Controller con 4 endpoints
- Form Requests con validaciones
- Notification de email

#### Fase 3: Testing (SONNET) 💰💰
```
Tiempo: 1-2 horas
Costo: ~$3-5

"Escribe tests completos para password reset:
- Unit tests para PasswordResetService
- Feature tests para endpoints
- Tests de rate limiting"
```

#### Fase 4: Git Operations (HAIKU) 💰
```
Tiempo: 5 min
Costo: ~$0.10

"Commitea los cambios con conventional commits apropiados.
Push a branch refactor/code-audit-and-improvements"
```

**Costo total:** ~$10-16 (vs ~$50-75 si todo fuera Opus)
**Ahorro:** 70-80%

---

### Workflow 2: Refactoring (P2 - QueryFilterBuilder)

#### Fase 1: Análisis (OPUS) 💰💰💰
```
Tiempo: 20 min
Costo: ~$1.50

"Analiza lógica de filtrado en: UserManagementService, ProfessorManagementService,
TemplateService, WeeklyAssignmentService. Identifica patrones comunes."
```

#### Fase 2: Diseño de Utility (SONNET) 💰💰
```
Tiempo: 30 min
Costo: ~$1.50

"Diseña QueryFilterBuilder utility class que abstraiga filtrado común.
Debe soportar: search, filters, sorting, pagination."
```

#### Fase 3: Implementación (SONNET) 💰💰
```
Tiempo: 2 horas
Costo: ~$4

"Implementa QueryFilterBuilder.php y refactoriza los 4 servicios para usarlo."
```

#### Fase 4: Testing (SONNET) 💰💰
```
Tiempo: 1 hora
Costo: ~$2

"Escribe tests unitarios para QueryFilterBuilder y verifica que los servicios
refactorizados mantienen la misma funcionalidad."
```

#### Fase 5: Verificación (HAIKU) 💰
```
Tiempo: 5 min
Costo: ~$0.10

"Ejecuta suite completa de tests y reporta resultados."
```

#### Fase 6: Commit (HAIKU) 💰
```
Tiempo: 2 min
Costo: ~$0.05

"Git add, commit con mensaje 'refactor: Extract QueryFilterBuilder utility', push"
```

**Costo total:** ~$9-10 (vs ~$40-50 todo Opus)
**Ahorro:** 75-80%

---

### Workflow 3: Bug Fix Simple

#### Investigación (SONNET) 💰💰
```
Tiempo: 15 min
Costo: ~$0.75

"Investiga por qué ExerciseController@update retorna 500 cuando weight es null.
Lee el código y reproduce el error."
```

#### Fix (SONNET) 💰💰
```
Tiempo: 10 min
Costo: ~$0.50

"Fixa la validación en ExerciseRequest para permitir weight null.
Actualiza SetService para manejar peso opcional."
```

#### Test (HAIKU) 💰
```
Tiempo: 5 min
Costo: ~$0.10

"Ejecuta tests relacionados con exercises y sets."
```

#### Commit (HAIKU) 💰
```
Tiempo: 2 min
Costo: ~$0.05

"Commit con mensaje 'fix: Allow null weight for bodyweight exercises'"
```

**Costo total:** ~$1.40 (vs ~$8-10 todo Opus)
**Ahorro:** 85-90%

---

## 📋 Matriz de Decisión Rápida

| Tarea | Requiere Análisis Profundo? | Código Complejo? | Modelo Recomendado |
|-------|------------------------------|------------------|--------------------|
| Git operations | ❌ | ❌ | HAIKU |
| Ejecutar tests | ❌ | ❌ | HAIKU |
| Leer archivos | ❌ | ❌ | HAIKU |
| Buscar código | ❌ | ❌ | HAIKU |
| Implementar CRUD | ❌ | ✅ | SONNET |
| Escribir tests | ❌ | ✅ | SONNET |
| Refactoring simple | ❌ | ✅ | SONNET |
| Bug fix estándar | ❌ | ✅ | SONNET |
| Diseñar sistema | ✅ | ✅ | OPUS |
| Code audit | ✅ | ❌ | OPUS |
| Split servicio grande | ✅ | ✅ | OPUS |
| Performance issues | ✅ | ✅ | OPUS |
| Security audit | ✅ | ✅ | OPUS |
| Arquitectura nueva | ✅ | ✅ | OPUS |

---

## 🎯 Estrategia para Villa Mitre - Propuestas Pendientes

### P2: QueryFilterBuilder (6 horas)
```
1. OPUS (20 min, ~$1.50): Análisis de patrones duplicados
2. SONNET (2.5h, ~$5): Implementación + refactoring
3. SONNET (1h, ~$2): Tests
4. HAIKU (5 min, ~$0.10): Ejecutar tests + commit

Total: ~$8.60 (vs ~$45 todo Opus)
Ahorro: 80%
```

### P6: Centralizar Cache (4 horas)
```
1. OPUS (15 min, ~$1): Análisis de uso actual de cache
2. SONNET (2h, ~$4): Expandir CacheService
3. SONNET (1.5h, ~$3): Refactorizar servicios
4. HAIKU (5 min, ~$0.10): Tests + commit

Total: ~$8.10 (vs ~$30 todo Opus)
Ahorro: 73%
```

### P8: Password Recovery (10-15 horas)
```
1. OPUS (30 min, ~$2.50): Diseño completo ✅ YA HECHO
2. SONNET (3h, ~$6): Backend (service, controller, requests)
3. SONNET (2h, ~$4): Email notification + config
4. SONNET (2h, ~$4): Tests unitarios + integración
5. HAIKU (10 min, ~$0.20): Ejecutar tests + commits

Total: ~$16.70 (vs ~$75 todo Opus)
Ahorro: 78%
```

### P5: Split ExerciseService (2 días)
```
1. OPUS (1h, ~$8): Análisis profundo + estrategia de split
2. SONNET (4h, ~$8): Implementar nuevos servicios
3. SONNET (2h, ~$4): Refactorizar controller
4. SONNET (2h, ~$4): Tests
5. HAIKU (10 min, ~$0.20): Verificación + commits

Total: ~$24.20 (vs ~$120 todo Opus)
Ahorro: 80%
```

### P4: Split TemplateService (3 días) - MÁS COMPLEJO
```
1. OPUS (2h, ~$16): Análisis exhaustivo + plan detallado
2. OPUS (1h, ~$8): Diseño de interfaces entre servicios
3. SONNET (6h, ~$12): Implementar 5 servicios nuevos
4. SONNET (3h, ~$6): Refactorizar controllers
5. SONNET (3h, ~$6): Tests completos
6. HAIKU (15 min, ~$0.30): Verificación + commits

Total: ~$48.30 (vs ~$200 todo Opus)
Ahorro: 76%
```

---

## 💡 Tips para Maximizar Eficiencia

### 1. Usa Context Caching
```
# Primera llamada: Full context
OPUS analiza codebase completo → $15

# Llamadas siguientes en 5 min: Context cached
OPUS usa cache → $1.50 (90% ahorro)

Estrategia: Haz múltiples análisis relacionados en la misma sesión
```

### 2. Prompts Específicos para Sonnet
```
❌ MAL: "Mejora el código"
✅ BIEN: "Refactoriza UserManagementService.getUsers() para usar
         QueryFilterBuilder. Mantén misma firma de método."

Sonnet es excelente siguiendo patrones existentes.
```

### 3. Chain de Agentes
```
OPUS → Genera plan detallado
  ↓
SONNET → Implementa cada paso del plan
  ↓
HAIKU → Ejecuta tests y commits

Esto es más eficiente que un solo agente Opus haciendo todo.
```

### 4. Divide Tareas Grandes
```
En vez de:
"Implementa password recovery completo" → OPUS 4h → $32

Mejor:
"Diseña sistema" → OPUS 30min → $4
"Implementa service" → SONNET 1h → $2
"Implementa controller" → SONNET 1h → $2
"Escribe tests" → SONNET 1h → $2
"Git operations" → HAIKU 5min → $0.10

Total: ~$10.10 (ahorro 68%)
```

### 5. Reutiliza Análisis
```
Si OPUS ya analizó el sistema de auth ($8):
- No pagues de nuevo por analizar password reset
- Referencia el análisis anterior
- Solo pide análisis incremental
```

---

## 📊 Ahorro Estimado para Refactoring Completo

### Sin Estrategia (Todo OPUS)
```
P2 QueryFilterBuilder:    6h × $8/h  = $48
P6 Centralizar Cache:     4h × $8/h  = $32
P8 Password Recovery:    15h × $8/h  = $120
P5 Split Exercise:       16h × $8/h  = $128
P4 Split Template:       24h × $8/h  = $192

TOTAL: ~$520
```

### Con Estrategia Optimizada
```
P2: $8.60
P6: $8.10
P8: $16.70
P5: $24.20
P4: $48.30

TOTAL: ~$106
```

**AHORRO: $414 (80% de reducción de costos)**

---

## 🚀 Guía de Implementación

### Paso 1: Configurar Agentes en Claude Code

```bash
# .claude/config.json (si soportado)
{
  "agents": {
    "haiku": {
      "model": "claude-3-haiku-20240307",
      "tasks": ["git", "test", "read", "search"]
    },
    "sonnet": {
      "model": "claude-3-5-sonnet-20241022",
      "tasks": ["implement", "refactor", "test-write", "bugfix"]
    },
    "opus": {
      "model": "claude-3-opus-20240229",
      "tasks": ["design", "analyze", "architecture", "security"]
    }
  }
}
```

### Paso 2: Template de Prompts por Nivel

#### HAIKU Template
```
Tarea simple y directa:
- [Acción específica]
- No requiere análisis
- Output esperado: [resultado concreto]

Ejemplo: "Ejecuta php artisan test --filter=PasswordReset y reporta pass/fail"
```

#### SONNET Template
```
Implementación estándar siguiendo patrones:
- Contexto: [servicio/feature relacionado]
- Patrón a seguir: [referencia a código existente]
- Requerimientos específicos: [lista]
- Tests esperados: [cobertura]

Ejemplo: "Implementa PasswordResetController siguiendo patrón de AuthController"
```

#### OPUS Template
```
Análisis profundo / Decisión arquitectónica:
- Problema complejo: [descripción]
- Contexto del sistema: [arquitectura actual]
- Restricciones: [backwards compatibility, performance, etc]
- Output esperado: [documento de diseño / propuesta detallada]

Ejemplo: "Analiza TemplateService y propón estrategia de división en 5 servicios"
```

---

## ✅ Checklist de Decisión de Modelo

Antes de cada tarea, pregúntate:

- [ ] ¿Requiere análisis de múltiples archivos? → Considera OPUS
- [ ] ¿Es una decisión arquitectónica? → OPUS
- [ ] ¿Afecta a >3 servicios/controllers? → OPUS para diseño, SONNET para implementación
- [ ] ¿Es implementación estándar siguiendo patrones? → SONNET
- [ ] ¿Ya existe código similar que copiar? → SONNET
- [ ] ¿Es ejecución de comando simple? → HAIKU
- [ ] ¿Es operación git? → HAIKU
- [ ] ¿Es lectura/búsqueda de archivos? → HAIKU

---

## 📚 Casos de Estudio

### Caso 1: P1 - DNI Hardcodeado (YA COMPLETADO)

**Lo que se hizo:** Todo con modelo principal
**Tiempo:** 30 min
**Costo estimado:** ~$4

**Lo que se podría haber hecho:**
```
1. SONNET (20 min): Crear config/gym.php y refactorizar service
2. HAIKU (5 min): Tests
3. HAIKU (5 min): Commit

Costo optimizado: ~$1.50
Ahorro: 62%
```

**Lección:** Para refactorings simples, Sonnet es suficiente.

---

### Caso 2: Diseño de Password Recovery (RECIÉN COMPLETADO)

**Lo que se hizo:** OPUS para diseño completo
**Tiempo:** 30-40 min
**Costo estimado:** ~$3-4

**Decisión correcta:** ✅
- Sistema nuevo requería análisis profundo
- Consideraciones de seguridad complejas
- Múltiples opciones arquitectónicas
- Output: Documento de 1000 líneas muy detallado

**Próximos pasos con SONNET:**
- Implementación del código (ahorro de 70%)
- Tests (ahorro de 70%)

---

## 🎓 Conclusión

### Regla de Oro
```
OPUS  → Pensar (Design, Architecture, Analysis)
SONNET → Construir (Implementation, Refactoring, Testing)
HAIKU  → Automatizar (Git, Tests execution, File ops)
```

### ROI Esperado
```
Inversión en estrategia: 1-2 horas de setup
Ahorro por proyecto: 70-80% en costos
Tiempo de desarrollo: Similar o mejor
Calidad del código: Igual (cada modelo para su propósito óptimo)
```

---

**Última actualización:** 21 de Octubre 2025
**Autor:** Claude Code (Sonnet 4.5)
**Estado:** Guía completa lista para uso

