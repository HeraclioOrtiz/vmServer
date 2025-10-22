# Villa Mitre Server - Agentes de Claude Code

## 🎯 AGENTE POR DEFECTO: `@coordinator`

**Usa `@coordinator` para TODAS tus tareas diarias** - él automáticamente delegará al agente más barato y eficiente.

### ¿Por qué usar coordinator?

- **Ahorro de costos**: Usa Haiku 4.5 ($1/$5) en vez de Sonnet 4.5 ($3/$15) - 3x más barato
- **Decisiones inteligentes**: Analiza tu request y elige el agente óptimo
- **Automatización**: No necesitas saber qué agente usar, él decide por ti
- **60-80% menos tokens**: Comparado con usar Sonnet 4.5 directamente

## 🚀 Uso Recomendado

```bash
# ✅ MEJOR PRÁCTICA - Usa coordinator para todo
@coordinator ejecuta los tests
@coordinator commit los cambios con mensaje sobre auth
@coordinator busca dónde se usa AuthService
@coordinator arregla el error de validación en LoginController
@coordinator crea un NotificationService siguiendo los patrones existentes
@coordinator ejecuta tests, arregla errores y commitea

# ⚠️ Solo si sabes exactamente qué agente necesitas
@test-runner php artisan test
@git-automation git status && git commit
@code-searcher encuentra todos los controladores

# ❌ EVITAR - Muy caro para tareas simples
# No invoques directamente a Sonnet/Opus para tareas rutinarias
```

## 📋 Agentes Disponibles

### Ultra-Baratos (< $1/$5)

| Agente | Modelo | Costo | Uso |
|--------|--------|-------|-----|
| **coordinator** | Haiku 4.5 | $1/$5 | **DEFAULT** - Coordina y delega |
| git-automation | Haiku 3 | $0.25/$1.25 | Git operations |
| test-runner | Haiku 3.5 | $0.80/$4 | Ejecutar tests |
| code-searcher | Haiku 4.5 | $1/$5 | Buscar código |

### Precio Medio ($3/$15)

| Agente | Modelo | Costo | Uso |
|--------|--------|-------|-----|
| bug-fixer | Sonnet 3.7 | $3/$15 | Arreglar bugs simples |
| implementer | Sonnet 4 | $3/$15 | Implementar features estándar |
| refactorer | Sonnet 4.5 | $3/$15 | Refactorizar código |

### Premium ($15/$75)

| Agente | Modelo | Costo | Uso |
|--------|--------|-------|-----|
| architect | Opus 4.1 | $15/$75 | Diseño arquitectónico, análisis complejo |

## 🎯 Matriz de Decisión del Coordinator

El coordinator automáticamente elige:

```
Git ops → git-automation (Haiku 3)
Tests → test-runner (Haiku 3.5)
Buscar código → code-searcher (Haiku 4.5)
Bugs simples → bug-fixer (Sonnet 3.7)
Implementación → implementer (Sonnet 4)
Refactoring → refactorer (Sonnet 4.5)
Arquitectura → architect (Opus 4.1)
```

## 💰 Ejemplos de Ahorro Real

### Ejemplo 1: Ejecutar tests y commitear
```
❌ Sin coordinator (Sonnet 4.5 hace todo):
   ~10K tokens @ $3/$15 = ~$0.15

✅ Con coordinator:
   Coordinator (2K) + test-runner (3K) + git-automation (1K)
   @ $1/$5 + $0.80/$4 + $0.25/$1.25 = ~$0.05

💰 Ahorro: 67%
```

### Ejemplo 2: Buscar código y arreglar bug
```
❌ Sin coordinator: ~$0.30
✅ Con coordinator: ~$0.10
💰 Ahorro: 67%
```

### Ejemplo 3: Diseñar sistema complejo
```
⚠️ Con coordinator: ~$25 (delega a architect)
💡 No hay ahorro aquí - tareas complejas necesitan Opus
   Pero coordinator previene usar Opus para tareas simples!
```

## 📖 Más Información

- Ver `CLAUDE.md` en la raíz para estrategia completa
- `docs/development/AGENT-STRATEGY.md` - Guía detallada
- `docs/development/AGENTS-REFERENCE.md` - Referencia rápida

## 🎓 Tips

1. **Siempre empieza con @coordinator** - él sabrá qué hacer
2. **No pienses en qué agente usar** - déjalo decidir
3. **Tareas multi-paso** - coordinator las coordina automáticamente
4. **Solo usa agentes específicos** si realmente sabes que necesitas ese exacto
5. **Habla con Sonnet 4.5 directamente** solo para análisis muy complejos que coordinator no pueda manejar

---

**ROI Esperado: 60-80% reducción de costos con igual o mejor calidad** 🚀
