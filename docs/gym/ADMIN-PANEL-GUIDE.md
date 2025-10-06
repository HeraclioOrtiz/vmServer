# Gym Service - Admin Panel Guide

## Introducción

Esta guía está dirigida a **profesores** que utilizan el panel administrativo para crear y asignar rutinas de entrenamiento a sus alumnos. El sistema permite gestionar catálogos de ejercicios, plantillas reutilizables y asignaciones semanales.

## Acceso al Panel

### Requisitos
- **Cuenta de profesor**: usuario con rol `is_professor = true`
- **Autenticación**: login con DNI y contraseña
- **Token de acceso**: se obtiene automáticamente al iniciar sesión

### Activar Rol de Profesor
Un administrador del sistema debe ejecutar:
```bash
php artisan user:make-professor {dni_o_email}
```

## Funcionalidades Principales

### 1. Gestión de Ejercicios

#### Crear Ejercicio
Agrega ejercicios al catálogo base que podrás usar en todas las rutinas.

**Campos requeridos:**
- **Nombre**: ej. "Sentadilla con barra"
- **Grupo muscular**: ej. "Piernas", "Pecho", "Espalda"
- **Equipo**: ej. "Barra", "Mancuernas", "Peso corporal"
- **Dificultad**: Principiante, Intermedio, Avanzado
- **Tags**: etiquetas para filtrar (ej. "compound", "barbell", "strength")
- **Instrucciones**: técnica de ejecución
- **Tempo**: ej. "3-1-1" (excéntrico-pausa-concéntrico)

#### Buscar y Filtrar
- **Por nombre**: busca en el nombre del ejercicio
- **Por grupo muscular**: filtra por zona trabajada
- **Por equipo**: filtra por implementos necesarios
- **Por tags**: combinaciones de etiquetas

### 2. Plantillas Diarias (Sesiones)

#### Crear Plantilla Diaria
Las plantillas diarias son sesiones de entrenamiento reutilizables.

**Información general:**
- **Título**: ej. "Push 60' fuerza"
- **Objetivo**: strength, hypertrophy, endurance
- **Duración estimada**: minutos
- **Nivel**: principiante, intermedio, avanzado
- **Tags**: para organización

**Agregar ejercicios:**
1. Selecciona ejercicios del catálogo
2. Define el orden de ejecución
3. Configura las series para cada ejercicio:
   - **Series**: número (ej. 4)
   - **Repeticiones**: rango (ej. 8-10) o fijo (ej. 12)
   - **Descanso**: segundos entre series
   - **Tempo**: opcional (ej. "2-1-1")
   - **RPE objetivo**: escala 1-10 (opcional)
   - **Notas**: instrucciones específicas

#### Plantillas Prefijadas
El sistema incluye 20 plantillas diarias listas para usar:
- Full-Body 45' (sin equipo)
- Full-Body 60' (equipo básico)
- Push Fuerza 60'
- Pull Fuerza 60'
- Legs Fuerza 60'
- Upper 45' / Lower 45'
- PPL Día 1-6 (Push/Pull/Legs)
- Cardio + Core 30'
- Mobility Flow 20'
- Y más...

### 3. Plantillas Semanales

#### Crear Plantilla Semanal
Combina plantillas diarias para formar semanas completas reutilizables.

**Configuración:**
- **Título**: ej. "PPL Intermedio 6 días"
- **Split**: PPL, Upper/Lower, Full-Body
- **Días por semana**: 3-7
- **Objetivo**: general, fuerza, hipertrofia

**Mapeo de días:**
- Lunes → Plantilla diaria X
- Martes → Plantilla diaria Y
- Miércoles → Descanso (sin plantilla)
- etc.

### 4. Asignación a Alumnos

#### Crear Asignación Semanal
Asigna una semana específica de entrenamiento a un alumno.

**Proceso:**
1. **Seleccionar alumno**: buscar por nombre o DNI
2. **Definir fechas**: semana de inicio y fin
3. **Elegir estrategia**:
   - **Desde plantilla semanal**: usar plantilla existente
   - **Manual**: armar día por día
   - **Asistente**: sugerencias automáticas (futuro)

**Armado manual:**
- Selecciona plantilla diaria para cada día
- Personaliza ejercicios si es necesario
- Ajusta series/repeticiones/descansos
- Agrega notas específicas

#### Gestionar Asignaciones
- **Ver asignaciones**: filtrar por alumno o fecha
- **Editar notas**: agregar observaciones sin cambiar ejercicios
- **Duplicar semana**: clonar para semana siguiente (con progresión opcional)
- **Eliminar**: quitar asignación completa

## Flujos de Trabajo Recomendados

### Flujo Básico (Profesor Nuevo)
1. **Revisar catálogo**: explorar ejercicios disponibles
2. **Crear ejercicios adicionales**: si faltan ejercicios específicos
3. **Usar plantillas prefijadas**: asignar directamente las 20 plantillas incluidas
4. **Asignar a alumno**: crear primera semana con plantillas existentes

### Flujo Avanzado (Profesor Experimentado)
1. **Crear plantillas diarias personalizadas**: sesiones específicas para tus métodos
2. **Crear plantillas semanales**: combinar tus plantillas diarias favoritas
3. **Asignación eficiente**: usar plantillas semanales para múltiples alumnos
4. **Progresión sistemática**: duplicar semanas con ajustes graduales

### Flujo de Progresión
1. **Semana 1**: asignar plantilla base
2. **Semana 2**: duplicar y aumentar 1-2 repeticiones
3. **Semana 3**: duplicar y aumentar carga o series
4. **Semana 4**: deload (reducir volumen 20-30%)
5. **Repetir ciclo**: con nuevas progresiones

## Consejos de Productividad

### Organización del Catálogo
- **Usa tags consistentes**: "compound", "isolation", "barbell", "dumbbell"
- **Nombres descriptivos**: incluye implemento y variante
- **Instrucciones claras**: técnica y puntos clave de seguridad

### Creación de Plantillas
- **Empieza con prefijadas**: modifica las 20 plantillas incluidas
- **Plantillas por objetivo**: separa fuerza, hipertrofia, resistencia
- **Duraciones realistas**: considera tiempo de calentamiento y estiramiento
- **Progresión lógica**: ordena ejercicios por prioridad e interferencia

### Asignación Eficiente
- **Plantillas semanales**: crea 3-4 plantillas semanales base para reutilizar
- **Duplicación inteligente**: usa la función duplicar con progresión automática
- **Notas específicas**: personaliza con observaciones del alumno
- **Planificación anticipada**: asigna 2-3 semanas por adelantado

## Interpretación de Datos

### Métricas de Uso (Futuro)
- **Adherencia**: % de ejercicios completados por alumno
- **Plantillas populares**: más usadas por otros profesores
- **Tiempo de armado**: eficiencia en creación de rutinas

### Feedback de Alumnos (Futuro)
- **RPE reportado**: vs RPE objetivo
- **Ejercicios problemáticos**: alta tasa de omisión
- **Sugerencias**: comentarios de los alumnos

## Resolución de Problemas

### Problemas Comunes
1. **No puedo acceder al panel**: verificar rol de profesor
2. **Ejercicio no aparece**: revisar filtros activos
3. **Plantilla no se guarda**: verificar campos requeridos
4. **Alumno no ve rutina**: confirmar fechas de asignación

### Limitaciones Actuales
- **Sin progresión automática**: debe configurarse manualmente
- **Sin métricas de adherencia**: en desarrollo
- **Sin notificaciones**: los alumnos deben revisar la app

### Soporte Técnico
Para problemas técnicos:
1. Verificar conexión a internet
2. Refrescar página/reiniciar sesión
3. Contactar soporte con detalles del error
4. Incluir captura de pantalla si es posible

## Mejores Prácticas

### Seguridad
- **Cerrar sesión**: al terminar de usar el panel
- **No compartir credenciales**: cada profesor debe tener su cuenta
- **Verificar asignaciones**: confirmar alumno correcto antes de guardar

### Pedagógicas
- **Progresión gradual**: aumentos del 2.5-5% por semana
- **Variedad controlada**: cambiar ejercicios cada 4-6 semanas
- **Individualización**: considerar limitaciones y preferencias del alumno
- **Comunicación**: usar notas para explicar cambios o énfasis

### Organizacionales
- **Nomenclatura consistente**: usar convenciones claras para nombres
- **Backup de plantillas**: exportar/documentar plantillas importantes
- **Revisión periódica**: actualizar plantillas según resultados
- **Colaboración**: compartir plantillas exitosas con otros profesores

## Próximas Funcionalidades

### En Desarrollo
- **Asistente de armado**: sugerencias automáticas por objetivo
- **Progresión inteligente**: cálculos automáticos de incrementos
- **Métricas de adherencia**: dashboards de seguimiento
- **Notificaciones**: recordatorios automáticos a alumnos

### Planificadas
- **Plantillas colaborativas**: compartir entre profesores
- **Análisis de rendimiento**: reportes de progreso
- **Integración con wearables**: datos de frecuencia cardíaca
- **App móvil para profesores**: gestión desde dispositivos móviles
