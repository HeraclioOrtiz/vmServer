# 🤖 Agentes Personalizados - Referencia Rápida

**Proyecto:** Villa Mitre Server
**Fecha:** 21 de Octubre 2025
**Ubicación:** `.claude/agents/`

---

## 🎯 Cómo Usar los Agentes

### Método 1: Comando `/agents` (Recomendado)

En Claude Code CLI, usa el comando interactivo:
```bash
/agents
```

Esto te permite:
- Ver todos los agentes disponibles
- Crear nuevos agentes
- Editar configuraciones
- Gestionar permisos de herramientas

### Método 2: Invocar Directamente

Desde el chat, simplemente menciona el agente:
```
@git-automation commit changes about password reset
@test-runner run password reset tests
@architect design notification system
```

### Método 3: Tool Task (Programático)

Desde otro agente o script:
```javascript
Task({
  subagent_type: "git-automation",
  description: "Commit password reset changes",
  prompt: "Git add all changes and commit with message about implementing password reset service"
})
```

---

## 📊 Tabla Comparativa de Agentes

| Agente | Modelo | Input | Output | Uso | Cuándo Usar |
|--------|--------|-------|--------|-----|-------------|
| **git-automation** | Haiku 3 | $0.25 | $1.25 | Git ops | Add, commit, push, branch ops |
| **test-runner** | Haiku 3.5 | $0.80 | $4.00 | Testing | Run tests, linters, syntax checks |
| **code-searcher** | Haiku 4.5 | $1.00 | $5.00 | Search | Find files, search code, explore |
| **bug-fixer** | Sonnet 3.7 | $3.00 | $15.00 | Bug fixes | Quick fixes, validation errors |
| **implementer** | Sonnet 4 | $3.00 | $15.00 | Implement | New features, CRUD, standard code |
| **refactorer** | Sonnet 4.5 | $3.00 | $15.00 | Refactor | Extract utilities, optimize code |
| **architect** | Opus 4.1 | $15.00 | $75.00 | Design | System design, architecture, audits |

**Precios:** Por millón de tokens (MTok)

---

## 🎨 Workflows por Nivel de Complejidad

### Nivel 1: Tarea Simple (Git, Tests, Search)

```
User request: "Commit these changes"
↓
@git-automation
├─ Análisis: $0.01
├─ Ejecución: $0.05
└─ Total: ~$0.06

Tiempo: 1-2 min
```

### Nivel 2: Bug Fix Simple

```
User request: "Fix validation error in ExerciseController"
↓
@bug-fixer
├─ Análisis: $0.50
├─ Implementación: $1.50
└─ Total: ~$2.00

Tiempo: 10-15 min
```

### Nivel 3: Feature Estándar

```
User request: "Implement PasswordResetController"
↓
@implementer
├─ Análisis: $1.00
├─ Implementación: $4.00
├─ Tests: $2.00
└─ Total: ~$7.00

Tiempo: 2-3 hours
```

### Nivel 4: Refactoring Medio

```
User request: "Extract QueryFilterBuilder utility"
↓
@refactorer
├─ Análisis: $1.50
├─ Implementación: $3.50
├─ Tests: $1.00
└─ Total: ~$6.00

Tiempo: 2-3 hours
```

### Nivel 5: Sistema Complejo

```
User request: "Design password recovery system"
↓
@architect (design)
├─ Análisis profundo: $3.00
├─ Diseño completo: $5.00
└─ Total: ~$8.00
↓
@implementer (backend)
├─ Service: $2.00
├─ Controller: $1.50
├─ Requests: $1.00
└─ Total: ~$4.50
↓
@implementer (tests)
├─ Unit tests: $2.00
├─ Feature tests: $1.50
└─ Total: ~$3.50
↓
@test-runner (verify)
└─ Total: ~$0.20
↓
@git-automation (commit)
└─ Total: ~$0.10

TOTAL: ~$16.30 (vs $75 todo Opus)
Ahorro: 78%
Tiempo: 1 día
```

---

## 💰 Ahorro Estimado por Propuesta

### P2: QueryFilterBuilder

**Sin agentes (todo Opus):**
```
Análisis: 20 min × $8/hora = $2.67
Implementación: 4h × $8/hora = $32.00
Tests: 1h × $8/hora = $8.00
Total: $42.67
```

**Con agentes optimizados:**
```
@architect (análisis): $1.50
@refactorer (implementación): $5.00
@implementer (tests): $2.00
@test-runner (verificación): $0.10
@git-automation (commit): $0.05
Total: $8.65
```

**Ahorro: $34.02 (80%)**

---

### P6: Centralizar Cache

**Sin agentes:** $32.00
**Con agentes:** $8.10
**Ahorro: $23.90 (75%)**

---

### P8: Password Recovery

**Sin agentes:** $120.00
**Con agentes:** $16.70
**Ahorro: $103.30 (86%)**

---

### P5: Split ExerciseService

**Sin agentes:** $128.00
**Con agentes:** $24.20
**Ahorro: $103.80 (81%)**

---

### P4: Split TemplateService

**Sin agentes:** $192.00
**Con agentes:** $48.30
**Ahorro: $143.70 (75%)**

---

## 🗺️ Mapa de Decisión Rápida

```
┌─ ¿Es solo Git? ────────────────→ @git-automation
│
├─ ¿Es solo ejecutar tests? ─────→ @test-runner
│
├─ ¿Es buscar código? ───────────→ @code-searcher
│
├─ ¿Es bug simple? ──────────────→ @bug-fixer
│
├─ ¿Es implementación estándar? ─→ @implementer
│
├─ ¿Es refactoring <400 líneas? ─→ @refactorer
│
└─ ¿Necesita diseño/arquitectura? → @architect
```

---

## 📋 Ejemplos Prácticos

### Ejemplo 1: Fin del Día

```bash
# Listar cambios
@code-searcher "Show me what files changed today"

# Ejecutar todos los tests
@test-runner "Run full test suite"

# Si pasan, commitear
@git-automation "Commit today's work with message about implementing password reset features"

# Push
@git-automation "Push to refactor branch"
```

**Costo total:** ~$1.50
**Tiempo:** 5-10 min

---

### Ejemplo 2: Implementar Feature Completa

```bash
# Paso 1: Diseño (si es nuevo sistema)
@architect "Design email notification system for password reset. Include rate limiting, templating, and queue support"

# Paso 2: Buscar código relacionado
@code-searcher "Find all notification and email related code in the codebase"

# Paso 3: Implementar
@implementer "Implement NotificationService according to docs/notifications/DESIGN.md. Follow AuthService pattern"

# Paso 4: Tests
@implementer "Write unit and feature tests for NotificationService"

# Paso 5: Verificar
@test-runner "Run notification tests"

# Paso 6: Commit
@git-automation "Commit notification system implementation"
```

**Costo total:** ~$20-25
**vs todo Opus:** ~$100-120
**Ahorro:** 75-80%

---

### Ejemplo 3: Bug Fix Rápido

```bash
# Buscar el bug
@code-searcher "Find ExerciseController validation logic"

# Fixear
@bug-fixer "Fix validation in ExerciseController to allow null weight for bodyweight exercises"

# Verificar
@test-runner "Run exercise tests"

# Commit
@git-automation "Commit fix for bodyweight exercise validation"
```

**Costo total:** ~$2.50
**Tiempo:** 10-15 min

---

### Ejemplo 4: Refactoring Session

```bash
# Buscar código duplicado
@code-searcher "Find all services that have filtering logic"

# Refactorizar
@refactorer "Extract common filtering logic from UserManagementService, ProfessorManagementService, and TemplateService into QueryFilterBuilder utility"

# Tests
@test-runner "Run tests for affected services"

# Commit
@git-automation "Commit QueryFilterBuilder utility extraction"
```

**Costo total:** ~$7-8
**vs Opus:** ~$40-45
**Ahorro:** 80%

---

## 🎓 Mejores Prácticas

### 1. Usa el Agente Más Barato Posible

```
❌ Malo:
@architect "Run tests"  → Wasting $75/MTok

✅ Bueno:
@test-runner "Run tests" → Only $4/MTok
```

### 2. Combina Agentes en Secuencia

```
@architect → Diseña
@implementer → Implementa
@test-runner → Verifica
@git-automation → Commitea
```

### 3. Sé Específico en los Prompts

```
❌ Vago:
@implementer "Add password reset"

✅ Específico:
@implementer "Implement PasswordResetService according to
docs/auth/PASSWORD-RECOVERY.md. Include requestReset,
validateToken, and resetPassword methods. Follow AuthService
pattern for dependency injection."
```

### 4. Verifica Siempre con Tests

```
@implementer "..."
@test-runner "Run affected tests"  ← Siempre verificar
```

### 5. Commits Frecuentes

```
Después de cada feature completada:
@git-automation "Commit changes"
```

---

## 🔧 Configuración y Personalización

### Ubicación de Archivos

```
.claude/agents/
├── git-automation.md    (Haiku 3)
├── test-runner.md       (Haiku 3.5)
├── code-searcher.md     (Haiku 4.5)
├── bug-fixer.md         (Sonnet 3.7)
├── implementer.md       (Sonnet 4)
├── refactorer.md        (Sonnet 4.5)
└── architect.md         (Opus 4.1)
```

### Formato de Archivo

```markdown
---
name: agent-name
description: When to use this agent
tools: Tool1, Tool2, Tool3
model: claude-model-id
---

System prompt with detailed instructions...
```

### Modificar un Agente

```bash
# Opción 1: Editor
vim .claude/agents/implementer.md

# Opción 2: Comando (si soportado)
/agents edit implementer

# Los cambios son inmediatos
```

### Crear Agente Nuevo

```bash
# Opción 1: Archivo manual
# Copiar template de agente existente

# Opción 2: Comando interactivo
/agents create
```

---

## 📊 Métricas y Monitoreo

### Tracking de Costos

```
Cada semana, revisa:
- ¿Qué agente usaste más?
- ¿Cuál fue el costo total?
- ¿Hubo tareas que usaron agente incorrecto?
```

### Optimización Continua

```
Cada mes:
- Actualiza prompts de agentes
- Ajusta tool permissions
- Compara costos vs beneficios
```

---

## 🚨 Troubleshooting

### Problema: Agente No Aparece

```bash
# Verificar ubicación
ls .claude/agents/

# Verificar formato YAML válido
cat .claude/agents/agent-name.md

# Reiniciar Claude Code
```

### Problema: Agente Usa Herramientas Incorrectas

```markdown
# Limitar tools en frontmatter
---
tools: Read, Edit  # Solo estas tools
---
```

### Problema: Agente Muy Caro

```markdown
# Cambiar a modelo más barato
---
model: claude-3-5-haiku-20241022  # En vez de Opus
---
```

---

## 📚 Recursos Adicionales

- **Documentación Oficial**: https://docs.claude.com/en/docs/claude-code/sub-agents
- **Estrategia Completa**: `docs/development/AGENT-STRATEGY.md`
- **Precios**: https://claude.com/pricing
- **CLAUDE.md**: Guía rápida en raíz del proyecto

---

## ✅ Checklist de Uso Diario

Inicio del día:
- [ ] Revisar qué features implementar
- [ ] Planificar qué agentes usar

Durante desarrollo:
- [ ] @code-searcher para buscar código
- [ ] @implementer para features
- [ ] @refactorer para mejoras
- [ ] @test-runner después de cambios

Fin del día:
- [ ] @test-runner full suite
- [ ] @git-automation commit changes
- [ ] @git-automation push

---

**Última actualización:** 21 de Octubre 2025
**Autor:** Claude Code (Sonnet 4.5)
**Ubicación:** `docs/development/AGENTS-REFERENCE.md`
