# CRUD Implementation Guide - Panel de Administración

## 🎯 **Visión General**

Esta guía proporciona instrucciones detalladas para implementar todos los CRUDs necesarios en el frontend del panel de administración Villa Mitre. Incluye tanto el **Panel de Gimnasio** (profesores) como el **Panel Villa Mitre** (administradores).

## 📋 **CRUDs Requeridos**

### **Panel de Gimnasio (Profesores)**
1. **Ejercicios** - CRUD completo con filtros avanzados
2. **Plantillas Diarias** - CRUD con wizard de creación
3. **Plantillas Semanales** - CRUD con calendario visual
4. **Asignaciones Semanales** - CRUD con proceso guiado
5. **Estudiantes Asignados** - Vista y gestión

### **Panel Villa Mitre (Administradores)**
1. **Usuarios** - CRUD completo con roles y permisos
2. **Profesores** - Gestión especializada con estadísticas
3. **Configuración del Sistema** - Formularios de configuración
4. **Reportes** - Vistas de solo lectura con filtros
5. **Logs de Auditoría** - Vista de solo lectura con búsqueda

## 🏋️ **Panel de Gimnasio - CRUDs**

### **1. CRUD de Ejercicios**

#### **Lista de Ejercicios (`/gym/exercises`)**
**Funcionalidades requeridas:**
- ✅ **Tabla paginada** (20 elementos por página)
- ✅ **Búsqueda en tiempo real** por nombre
- ✅ **Filtros múltiples**: grupo muscular, equipo, dificultad, tags
- ✅ **Ordenamiento** por nombre, fecha creación, popularidad
- ✅ **Acciones por fila**: Ver, Editar, Duplicar, Eliminar
- ✅ **Acciones masivas**: Eliminar seleccionados, Exportar
- ✅ **Vista previa rápida** en modal

**Componentes UI necesarios:**
- `ExerciseTable` - Tabla principal con paginación
- `ExerciseFilters` - Panel de filtros colapsable
- `ExercisePreviewModal` - Modal de vista previa
- `BulkActionsBar` - Barra de acciones masivas

**Estados de datos:**
- Loading: Skeleton de tabla
- Empty: Ilustración + botón "Crear primer ejercicio"
- Error: Toast notification + retry button

#### **Crear/Editar Ejercicio (`/gym/exercises/new`, `/gym/exercises/:id/edit`)**
**Formulario requerido:**
- ✅ **Información básica**: nombre, descripción, instrucciones
- ✅ **Categorización**: grupo muscular, patrón de movimiento, equipo
- ✅ **Configuración**: dificultad, tempo, tags
- ✅ **Multimedia**: URL de video, URL de imagen
- ✅ **Validación en tiempo real** con Zod
- ✅ **Preview en vivo** del ejercicio

**Componentes UI necesarios:**
- `ExerciseForm` - Formulario principal
- `TagSelector` - Selector de tags con autocompletado
- `MediaUploader` - Subida de imágenes/videos
- `ExercisePreview` - Vista previa en tiempo real

### **2. CRUD de Plantillas Diarias**

#### **Lista de Plantillas (`/gym/daily-templates`)**
**Funcionalidades requeridas:**
- ✅ **Vista de tarjetas** con información clave
- ✅ **Filtros**: objetivo, duración, nivel, creador
- ✅ **Búsqueda** por título y descripción
- ✅ **Indicadores visuales**: prefijada vs personalizada
- ✅ **Acciones**: Ver, Editar, Duplicar, Usar, Eliminar
- ✅ **Vista previa** de ejercicios en modal

**Componentes UI necesarios:**
- `TemplateGrid` - Grid de tarjetas responsivo
- `TemplateCard` - Tarjeta individual con acciones
- `TemplatePreviewModal` - Modal con lista de ejercicios
- `TemplateFilters` - Filtros en sidebar

#### **Crear/Editar Plantilla Diaria (Wizard)**
**Paso 1: Información General**
- ✅ **Datos básicos**: título, objetivo, duración, nivel
- ✅ **Configuración**: tags, descripción, notas
- ✅ **Validación**: campos requeridos, límites

**Paso 2: Selección de Ejercicios**
- ✅ **Buscador de ejercicios** con filtros
- ✅ **Drag & drop** para agregar/reordenar
- ✅ **Vista previa** de ejercicios seleccionados
- ✅ **Configuración por ejercicio**: notas, descanso

**Paso 3: Configuración de Series**
- ✅ **Editor de series** por ejercicio
- ✅ **Plantillas de series** (3x10, 5x5, etc.)
- ✅ **Configuración avanzada**: RPE, tempo, notas
- ✅ **Vista previa final** de la plantilla

**Componentes UI necesarios:**
- `TemplateWizard` - Contenedor del wizard
- `ExerciseSelector` - Selector con drag & drop
- `SetEditor` - Editor de series por ejercicio
- `TemplatePreview` - Vista previa final

### **3. CRUD de Plantillas Semanales**

#### **Lista de Plantillas Semanales (`/gym/weekly-templates`)**
**Funcionalidades requeridas:**
- ✅ **Vista de calendario** por plantilla
- ✅ **Filtros**: split, días por semana, creador
- ✅ **Información resumida**: días activos, plantillas usadas
- ✅ **Acciones**: Ver, Editar, Duplicar, Usar, Eliminar

#### **Crear/Editar Plantilla Semanal**
**Funcionalidades requeridas:**
- ✅ **Información básica**: título, tipo de split, descripción
- ✅ **Calendario interactivo** de 7 días
- ✅ **Asignación de plantillas diarias** por día
- ✅ **Drag & drop** entre días
- ✅ **Configuración de días de descanso**
- ✅ **Vista previa** de la semana completa

**Componentes UI necesarios:**
- `WeeklyTemplateBuilder` - Constructor principal
- `WeekCalendar` - Calendario interactivo
- `DaySlot` - Slot para cada día de la semana
- `DailyTemplateSelector` - Selector de plantillas diarias

### **4. CRUD de Asignaciones Semanales**

#### **Lista de Asignaciones (`/gym/assignments`)**
**Funcionalidades requeridas:**
- ✅ **Tabla con información del estudiante**
- ✅ **Filtros**: estudiante, fecha, estado, adherencia
- ✅ **Indicadores visuales**: adherencia, progreso
- ✅ **Acciones**: Ver, Editar, Duplicar, Cancelar
- ✅ **Vista de calendario** opcional

#### **Crear Nueva Asignación (Wizard)**
**Paso 1: Seleccionar Estudiante**
- ✅ **Buscador de estudiantes** con filtros
- ✅ **Información del estudiante**: historial, estadísticas
- ✅ **Verificación de conflictos** con asignaciones existentes

**Paso 2: Configurar Fechas**
- ✅ **Selector de semana** con calendario
- ✅ **Detección de conflictos** automática
- ✅ **Sugerencias** de semanas disponibles

**Paso 3: Elegir Método de Asignación**
- ✅ **Desde plantilla semanal** - Selector de plantillas
- ✅ **Manual** - Asignación día por día
- ✅ **Asistente IA** - Generación automática

**Paso 4: Revisión y Personalización**
- ✅ **Vista previa** de la asignación completa
- ✅ **Edición inline** de ejercicios/series
- ✅ **Notas personalizadas** por día
- ✅ **Confirmación final**

## 🏛️ **Panel Villa Mitre - CRUDs**

### **1. CRUD de Usuarios**

#### **Lista de Usuarios (`/admin/users`)**
**Funcionalidades requeridas:**
- ✅ **Tabla avanzada** con información completa
- ✅ **Búsqueda global**: nombre, DNI, email
- ✅ **Filtros múltiples**: tipo, rol, estado, fecha
- ✅ **Ordenamiento** por múltiples columnas
- ✅ **Acciones**: Ver perfil, Editar, Cambiar rol, Suspender
- ✅ **Acciones masivas**: Exportar, Cambiar estado
- ✅ **Estadísticas resumidas** en header

**Componentes UI necesarios:**
- `UserTable` - Tabla principal con todas las funcionalidades
- `UserFilters` - Panel de filtros avanzados
- `UserStatsHeader` - Estadísticas resumidas
- `BulkUserActions` - Acciones masivas

#### **Detalle de Usuario (`/admin/users/:id`)**
**Secciones requeridas:**
- ✅ **Información básica**: datos personales, avatar
- ✅ **Información del club**: socio, estado, semáforo
- ✅ **Roles del sistema**: profesor, admin, permisos
- ✅ **Actividad del gimnasio**: plantillas, asignaciones
- ✅ **Log de actividad**: logins, acciones, dispositivos
- ✅ **Configuración de acceso**: estado cuenta, sesiones

**Componentes UI necesarios:**
- `UserProfile` - Vista principal del perfil
- `UserInfoTabs` - Tabs para diferentes secciones
- `ActivityTimeline` - Timeline de actividad
- `PermissionsMatrix` - Matriz de permisos visual

#### **Editar Usuario (`/admin/users/:id/edit`)**
**Formulario por secciones:**
- ✅ **Información básica**: nombre, email, teléfono
- ✅ **Roles y permisos**: checkboxes con descripciones
- ✅ **Configuración de acceso**: estado, timeouts, IPs
- ✅ **Notas internas**: comentarios del administrador

### **2. Gestión de Profesores**

#### **Lista de Profesores (`/admin/professors`)**
**Funcionalidades requeridas:**
- ✅ **Vista de tarjetas** con estadísticas
- ✅ **Métricas por profesor**: estudiantes, plantillas, adherencia
- ✅ **Filtros**: estado, especialidad, carga de trabajo
- ✅ **Acciones**: Ver perfil, Ver estudiantes, Editar permisos

#### **Asignar Rol de Profesor (`/admin/professors/assign`)**
**Wizard de asignación:**
- ✅ **Selección de usuario** con búsqueda
- ✅ **Formulario de calificaciones**: educación, certificaciones
- ✅ **Configuración de permisos**: límites, especialidades
- ✅ **Revisión y confirmación**

### **3. Configuración del Sistema**

#### **Configuración General (`/admin/settings`)**
**Secciones de configuración:**
- ✅ **API Externa**: URLs, credenciales, timeouts
- ✅ **Sistema de usuarios**: registro, autenticación, sesiones
- ✅ **Gimnasio**: límites, plantillas, notificaciones
- ✅ **Notificaciones**: canales, templates, frecuencia

**Componentes UI necesarios:**
- `SettingsTabs` - Tabs para diferentes secciones
- `SettingsForm` - Formularios por sección
- `ConfigurationPreview` - Vista previa de cambios
- `SettingsBackup` - Backup/restore de configuración

### **4. Reportes y Métricas**

#### **Dashboard de Reportes (`/admin/reports`)**
**Reportes disponibles:**
- ✅ **Uso del sistema**: usuarios activos, API calls, errores
- ✅ **Actividad de profesores**: plantillas, asignaciones, adherencia
- ✅ **Sincronización API**: estado, errores, estadísticas
- ✅ **Métricas de gimnasio**: uso, popularidad, tendencias

**Componentes UI necesarios:**
- `ReportGrid` - Grid de reportes disponibles
- `ReportCard` - Tarjeta por reporte con preview
- `ChartContainer` - Contenedor para gráficos
- `ReportFilters` - Filtros por período/usuario

### **5. Logs de Auditoría**

#### **Visor de Auditoría (`/admin/audit`)**
**Funcionalidades requeridas:**
- ✅ **Tabla de logs** con paginación
- ✅ **Filtros avanzados**: usuario, acción, recurso, fecha
- ✅ **Búsqueda de texto** en detalles
- ✅ **Exportación** de logs filtrados
- ✅ **Vista detallada** de cada entrada

## 🎨 **Componentes UI Comunes**

### **Componentes Base Requeridos**
- `DataTable` - Tabla con paginación, filtros, ordenamiento
- `SearchInput` - Input de búsqueda con debounce
- `FilterPanel` - Panel de filtros colapsable
- `ActionDropdown` - Dropdown de acciones por fila
- `BulkActions` - Barra de acciones masivas
- `ConfirmDialog` - Modal de confirmación
- `FormWizard` - Wizard de múltiples pasos
- `LoadingStates` - Skeletons y spinners
- `EmptyStates` - Estados vacíos con ilustraciones

### **Componentes Especializados**
- `UserSelector` - Selector de usuarios con búsqueda
- `ExerciseSelector` - Selector de ejercicios con filtros
- `WeekCalendar` - Calendario semanal interactivo
- `PermissionMatrix` - Matriz visual de permisos
- `StatCard` - Tarjeta de estadística
- `ActivityTimeline` - Timeline de actividad
- `ChartWrapper` - Wrapper para gráficos

## 🔄 **Patrones de Implementación**

### **1. Patrón de Lista/CRUD**
```
/resource
├── index.tsx          # Lista principal
├── create.tsx         # Formulario de creación
├── [id]/
│   ├── index.tsx      # Vista detallada
│   ├── edit.tsx       # Formulario de edición
│   └── delete.tsx     # Confirmación de eliminación
└── components/
    ├── ResourceTable.tsx
    ├── ResourceForm.tsx
    ├── ResourceFilters.tsx
    └── ResourceCard.tsx
```

### **2. Patrón de Wizard**
```
/resource/create
├── index.tsx          # Contenedor del wizard
├── steps/
│   ├── Step1.tsx      # Paso 1
│   ├── Step2.tsx      # Paso 2
│   └── Step3.tsx      # Paso 3
├── components/
│   ├── WizardNav.tsx  # Navegación del wizard
│   └── StepWrapper.tsx
└── hooks/
    └── useWizard.tsx  # Lógica del wizard
```

### **3. Patrón de Dashboard**
```
/dashboard
├── index.tsx          # Dashboard principal
├── components/
│   ├── MetricCard.tsx
│   ├── ChartCard.tsx
│   └── RecentActivity.tsx
└── hooks/
    └── useDashboard.tsx
```

## 📊 **Estados de Datos**

### **Estados Globales (React Query)**
- `useUsers` - Lista de usuarios con filtros
- `useUser` - Usuario individual
- `useExercises` - Catálogo de ejercicios
- `useDailyTemplates` - Plantillas diarias
- `useWeeklyTemplates` - Plantillas semanales
- `useAssignments` - Asignaciones semanales
- `useSystemSettings` - Configuración del sistema
- `useAuditLogs` - Logs de auditoría

### **Estados Locales (useState/useReducer)**
- Filtros de tablas
- Estado de formularios
- Selecciones múltiples
- Estado de wizards
- Modales abiertos/cerrados

## 🔐 **Permisos y Acceso**

### **Control de Acceso por Ruta**
- `/gym/*` - Requiere `is_professor: true`
- `/admin/users/*` - Requiere `is_admin: true`
- `/admin/settings/*` - Requiere `is_super_admin: true`
- `/admin/audit/*` - Requiere `audit_access: true`

### **Control de Acceso por Acción**
- **Crear**: Verificar permisos de escritura
- **Editar**: Verificar ownership o permisos admin
- **Eliminar**: Verificar permisos de eliminación
- **Ver**: Verificar permisos de lectura

## 🚀 **Prioridades de Implementación**

### **Fase 1: CRUDs Básicos (2-3 semanas)**
1. ✅ CRUD de Ejercicios (completo)
2. ✅ CRUD de Usuarios (básico)
3. ✅ Lista de Plantillas Diarias (solo lectura)
4. ✅ Lista de Asignaciones (solo lectura)

### **Fase 2: Funcionalidades Avanzadas (2-3 semanas)**
1. ✅ Wizard de Plantillas Diarias
2. ✅ Wizard de Asignaciones
3. ✅ Gestión de Profesores
4. ✅ Configuración del Sistema

### **Fase 3: Reportes y Auditoría (1-2 semanas)**
1. ✅ Dashboard de Reportes
2. ✅ Logs de Auditoría
3. ✅ Métricas avanzadas
4. ✅ Exportaciones

### **Fase 4: Optimización y UX (1 semana)**
1. ✅ Performance optimization
2. ✅ Responsive design
3. ✅ Accessibility
4. ✅ Testing integral

## 📋 **Checklist de Implementación**

### **Por cada CRUD implementar:**
- [ ] **Lista**: Tabla/Grid con paginación y filtros
- [ ] **Crear**: Formulario con validación
- [ ] **Ver**: Vista detallada de solo lectura
- [ ] **Editar**: Formulario pre-poblado
- [ ] **Eliminar**: Confirmación y soft delete
- [ ] **Búsqueda**: Input con debounce
- [ ] **Filtros**: Panel colapsable
- [ ] **Ordenamiento**: Por columnas relevantes
- [ ] **Acciones masivas**: Para operaciones múltiples
- [ ] **Estados de carga**: Skeletons y spinners
- [ ] **Estados vacíos**: Ilustraciones y CTAs
- [ ] **Manejo de errores**: Toast notifications
- [ ] **Responsive design**: Mobile-first
- [ ] **Accessibility**: ARIA labels y navegación por teclado

Esta guía proporciona toda la información necesaria para implementar los CRUDs del panel de administración sin incluir código específico, enfocándose en funcionalidades, componentes y patrones de implementación.
