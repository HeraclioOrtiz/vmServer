# UI Components Guide - Panel de Administración

## 🎨 **Sistema de Componentes UI**

Esta guía detalla todos los componentes UI necesarios para implementar el panel de administración, organizados por categoría y funcionalidad.

## 📋 **Componentes Base (Fundacionales)**

### **1. Layout Components**

#### **AppLayout**
**Propósito**: Layout principal de la aplicación
**Funcionalidades**:
- ✅ Sidebar fijo/colapsable
- ✅ Header con navegación y usuario
- ✅ Área de contenido principal
- ✅ Breadcrumbs automáticos
- ✅ Responsive design (mobile hamburger menu)

#### **PageHeader**
**Propósito**: Header consistente para todas las páginas
**Funcionalidades**:
- ✅ Título de página
- ✅ Breadcrumbs
- ✅ Acciones principales (botones)
- ✅ Filtros rápidos opcionales

#### **Sidebar**
**Propósito**: Navegación principal
**Funcionalidades**:
- ✅ Menú jerárquico
- ✅ Indicadores de sección activa
- ✅ Collapse/expand
- ✅ Badges de notificación
- ✅ Perfil de usuario en footer

### **2. Form Components**

#### **FormField**
**Propósito**: Wrapper consistente para campos de formulario
**Funcionalidades**:
- ✅ Label con indicador de requerido
- ✅ Mensaje de error
- ✅ Texto de ayuda
- ✅ Estados: normal, error, disabled
- ✅ Soporte para diferentes tipos de input

#### **SearchInput**
**Propósito**: Input de búsqueda con funcionalidades avanzadas
**Funcionalidades**:
- ✅ Debounce automático
- ✅ Icono de búsqueda
- ✅ Botón de limpiar
- ✅ Placeholder dinámico
- ✅ Loading state

#### **MultiSelect**
**Propósito**: Selector múltiple con chips
**Funcionalidades**:
- ✅ Búsqueda dentro de opciones
- ✅ Selección/deselección masiva
- ✅ Chips removibles
- ✅ Límite de selecciones
- ✅ Agrupación de opciones

#### **DateRangePicker**
**Propósito**: Selector de rango de fechas
**Funcionalidades**:
- ✅ Calendario visual
- ✅ Presets (última semana, mes, etc.)
- ✅ Validación de rangos
- ✅ Formato personalizable

### **3. Data Display Components**

#### **DataTable**
**Propósito**: Tabla avanzada para listados
**Funcionalidades**:
- ✅ Paginación automática
- ✅ Ordenamiento por columnas
- ✅ Filtros por columna
- ✅ Selección múltiple
- ✅ Acciones por fila
- ✅ Columnas redimensionables
- ✅ Export a CSV/Excel
- ✅ Loading skeletons
- ✅ Empty states

#### **StatCard**
**Propósito**: Tarjeta de estadística
**Funcionalidades**:
- ✅ Valor principal destacado
- ✅ Indicador de cambio (%, trend)
- ✅ Icono representativo
- ✅ Colores por estado
- ✅ Click action opcional

#### **ProgressBar**
**Propósito**: Barra de progreso visual
**Funcionalidades**:
- ✅ Porcentaje numérico
- ✅ Colores por rango
- ✅ Animación suave
- ✅ Tamaños variables
- ✅ Labels opcionales

### **4. Navigation Components**

#### **Breadcrumbs**
**Propósito**: Navegación jerárquica
**Funcionalidades**:
- ✅ Links clickeables
- ✅ Separadores personalizables
- ✅ Truncado inteligente
- ✅ Integración con router

#### **Tabs**
**Propósito**: Navegación por pestañas
**Funcionalidades**:
- ✅ Tabs horizontales/verticales
- ✅ Badges de notificación
- ✅ Lazy loading de contenido
- ✅ Scroll horizontal en mobile

#### **Pagination**
**Propósito**: Paginación de listados
**Funcionalidades**:
- ✅ Números de página
- ✅ Botones prev/next
- ✅ Selector de elementos por página
- ✅ Información de total
- ✅ Jump to page

## 🏋️ **Componentes Específicos del Gimnasio**

### **1. Exercise Components**

#### **ExerciseCard**
**Propósito**: Tarjeta de ejercicio en listados
**Funcionalidades**:
- ✅ Imagen/video preview
- ✅ Información básica (nombre, grupo muscular)
- ✅ Tags visuales
- ✅ Indicador de dificultad
- ✅ Acciones rápidas (editar, duplicar)
- ✅ Hover effects

#### **ExerciseSelector**
**Propósito**: Selector de ejercicios con búsqueda
**Funcionalidades**:
- ✅ Búsqueda por nombre
- ✅ Filtros por categoría
- ✅ Vista previa en modal
- ✅ Selección múltiple
- ✅ Drag & drop para reordenar
- ✅ Ejercicios favoritos

#### **ExercisePreview**
**Propósito**: Vista previa detallada de ejercicio
**Funcionalidades**:
- ✅ Video/imagen principal
- ✅ Instrucciones paso a paso
- ✅ Información técnica
- ✅ Variaciones sugeridas
- ✅ Músculos trabajados (visual)

### **2. Template Components**

#### **TemplateCard**
**Propósito**: Tarjeta de plantilla diaria/semanal
**Funcionalidades**:
- ✅ Información resumida
- ✅ Indicadores visuales (duración, nivel)
- ✅ Preview de ejercicios
- ✅ Acciones (usar, editar, duplicar)
- ✅ Estado (prefijada/personalizada)

#### **WeekCalendar**
**Propósito**: Calendario semanal interactivo
**Funcionalidades**:
- ✅ Vista de 7 días
- ✅ Drag & drop entre días
- ✅ Indicadores de rutina asignada
- ✅ Días de descanso visuales
- ✅ Click para editar día
- ✅ Responsive (stack en mobile)

#### **DaySlot**
**Propósito**: Slot individual para día de la semana
**Funcionalidades**:
- ✅ Drop zone para plantillas
- ✅ Vista previa de rutina asignada
- ✅ Botón de descanso
- ✅ Indicador de estado
- ✅ Acciones rápidas

### **3. Assignment Components**

#### **StudentSelector**
**Propósito**: Selector de estudiantes con información
**Funcionalidades**:
- ✅ Búsqueda por nombre/DNI
- ✅ Avatar y información básica
- ✅ Historial de asignaciones
- ✅ Indicadores de adherencia
- ✅ Filtros por estado

#### **AssignmentTimeline**
**Propósito**: Timeline de asignaciones de un estudiante
**Funcionalidades**:
- ✅ Línea temporal visual
- ✅ Asignaciones por período
- ✅ Indicadores de adherencia
- ✅ Zoom por período
- ✅ Click para ver detalle

#### **AdherenceChart**
**Propósito**: Gráfico de adherencia
**Funcionalidades**:
- ✅ Gráfico de barras/líneas
- ✅ Período configurable
- ✅ Comparación con promedio
- ✅ Drill-down por semana
- ✅ Export de datos

### **4. Workout Components**

#### **SetEditor**
**Propósito**: Editor de series para ejercicios
**Funcionalidades**:
- ✅ Tabla de series editable
- ✅ Plantillas de series (3x10, 5x5)
- ✅ Campos: reps, peso, RPE, descanso
- ✅ Validación de rangos
- ✅ Copiar/pegar series
- ✅ Calculadora de 1RM

#### **WorkoutPreview**
**Propósito**: Vista previa de rutina completa
**Funcionalidades**:
- ✅ Lista de ejercicios ordenada
- ✅ Series por ejercicio
- ✅ Tiempo estimado total
- ✅ Equipamiento necesario
- ✅ Notas del profesor
- ✅ Print/export friendly

## 👥 **Componentes de Administración de Usuarios**

### **1. User Management Components**

#### **UserTable**
**Propósito**: Tabla avanzada de usuarios
**Funcionalidades**:
- ✅ Columnas configurables
- ✅ Filtros múltiples
- ✅ Búsqueda global
- ✅ Acciones masivas
- ✅ Export de datos
- ✅ Indicadores visuales de estado

#### **UserCard**
**Propósito**: Tarjeta de usuario con información clave
**Funcionalidades**:
- ✅ Avatar y información básica
- ✅ Badges de rol
- ✅ Indicadores de estado
- ✅ Métricas rápidas
- ✅ Acciones principales

#### **UserProfile**
**Propósito**: Vista detallada del perfil de usuario
**Funcionalidades**:
- ✅ Información completa en tabs
- ✅ Historial de actividad
- ✅ Configuración de permisos
- ✅ Estadísticas de uso
- ✅ Acciones administrativas

### **2. Permission Components**

#### **PermissionMatrix**
**Propósito**: Matriz visual de permisos
**Funcionalidades**:
- ✅ Grid de roles vs permisos
- ✅ Checkboxes interactivos
- ✅ Agrupación por categoría
- ✅ Herencia de permisos
- ✅ Bulk edit de permisos

#### **RoleSelector**
**Propósito**: Selector de roles con descripción
**Funcionalidades**:
- ✅ Lista de roles disponibles
- ✅ Descripción de cada rol
- ✅ Permisos incluidos
- ✅ Roles múltiples
- ✅ Validación de conflictos

### **3. Activity Components**

#### **ActivityTimeline**
**Propósito**: Timeline de actividad del usuario
**Funcionalidades**:
- ✅ Eventos cronológicos
- ✅ Iconos por tipo de acción
- ✅ Filtros por tipo
- ✅ Paginación infinita
- ✅ Detalles expandibles

#### **LoginHistory**
**Propósito**: Historial de inicios de sesión
**Funcionalidades**:
- ✅ Tabla de sesiones
- ✅ Información de dispositivo
- ✅ Geolocalización
- ✅ Sesiones activas
- ✅ Acciones de seguridad

## 📊 **Componentes de Reportes y Analytics**

### **1. Chart Components**

#### **ChartContainer**
**Propósito**: Contenedor universal para gráficos
**Funcionalidades**:
- ✅ Soporte para múltiples librerías
- ✅ Loading states
- ✅ Error handling
- ✅ Export de imagen
- ✅ Responsive design

#### **MetricsDashboard**
**Propósito**: Dashboard de métricas clave
**Funcionalidades**:
- ✅ Grid de métricas
- ✅ Período configurable
- ✅ Comparación temporal
- ✅ Drill-down por métrica
- ✅ Alertas por umbral

### **2. Report Components**

#### **ReportBuilder**
**Propósito**: Constructor de reportes personalizados
**Funcionalidades**:
- ✅ Selector de métricas
- ✅ Filtros configurables
- ✅ Agrupación de datos
- ✅ Vista previa en tiempo real
- ✅ Guardado de reportes

#### **ReportViewer**
**Propósito**: Visualizador de reportes
**Funcionalidades**:
- ✅ Múltiples formatos (tabla, gráfico)
- ✅ Export a PDF/Excel
- ✅ Programación de envíos
- ✅ Comentarios y anotaciones
- ✅ Compartir por link

## 🔧 **Componentes de Configuración**

### **1. Settings Components**

#### **SettingsPanel**
**Propósito**: Panel de configuración por sección
**Funcionalidades**:
- ✅ Formularios organizados
- ✅ Validación en tiempo real
- ✅ Preview de cambios
- ✅ Backup/restore
- ✅ Confirmación de cambios críticos

#### **ConfigurationWizard**
**Propósito**: Wizard de configuración inicial
**Funcionalidades**:
- ✅ Pasos guiados
- ✅ Validación por paso
- ✅ Progress indicator
- ✅ Navegación libre
- ✅ Resumen final

### **2. System Components**

#### **SystemStatus**
**Propósito**: Indicador de estado del sistema
**Funcionalidades**:
- ✅ Health checks visuales
- ✅ Métricas en tiempo real
- ✅ Alertas automáticas
- ✅ Acciones de mantenimiento
- ✅ Historial de incidentes

#### **AuditLogViewer**
**Propósito**: Visualizador de logs de auditoría
**Funcionalidades**:
- ✅ Tabla filtrable
- ✅ Búsqueda de texto
- ✅ Agrupación por usuario/acción
- ✅ Export de logs
- ✅ Vista detallada por entrada

## 🎯 **Componentes de Feedback y Estados**

### **1. Loading States**

#### **TableSkeleton**
**Propósito**: Skeleton para tablas
**Funcionalidades**:
- ✅ Filas y columnas configurables
- ✅ Animación shimmer
- ✅ Respeta estructura real
- ✅ Diferentes densidades

#### **CardSkeleton**
**Propósito**: Skeleton para tarjetas
**Funcionalidades**:
- ✅ Tamaños variables
- ✅ Elementos configurables
- ✅ Grid responsive
- ✅ Animación suave

### **2. Empty States**

#### **EmptyState**
**Propósito**: Estado vacío con call-to-action
**Funcionalidades**:
- ✅ Ilustración contextual
- ✅ Mensaje personalizado
- ✅ Botón de acción principal
- ✅ Acciones secundarias
- ✅ Diferentes variantes

#### **NoResults**
**Propósito**: Estado de búsqueda sin resultados
**Funcionalidades**:
- ✅ Mensaje contextual
- ✅ Sugerencias de búsqueda
- ✅ Botón limpiar filtros
- ✅ Búsquedas relacionadas

### **3. Notification Components**

#### **Toast**
**Propósito**: Notificaciones temporales
**Funcionalidades**:
- ✅ Tipos: success, error, warning, info
- ✅ Auto-dismiss configurable
- ✅ Acciones (undo, retry)
- ✅ Stack de múltiples toasts
- ✅ Posición configurable

#### **AlertBanner**
**Propósito**: Alertas persistentes
**Funcionalidades**:
- ✅ Diferentes severidades
- ✅ Dismissible opcional
- ✅ Acciones integradas
- ✅ Iconos contextuales
- ✅ Animaciones de entrada/salida

## 🔄 **Componentes de Workflow**

### **1. Wizard Components**

#### **WizardContainer**
**Propósito**: Contenedor para wizards multi-paso
**Funcionalidades**:
- ✅ Navegación entre pasos
- ✅ Validación por paso
- ✅ Progress indicator
- ✅ Persistencia de datos
- ✅ Navegación libre/lineal

#### **StepIndicator**
**Propósito**: Indicador visual de progreso
**Funcionalidades**:
- ✅ Pasos completados/pendientes
- ✅ Paso actual destacado
- ✅ Click para navegar
- ✅ Responsive design
- ✅ Estados de error

### **2. Modal Components**

#### **ConfirmDialog**
**Propósito**: Modal de confirmación
**Funcionalidades**:
- ✅ Diferentes tipos (delete, save, etc.)
- ✅ Mensaje personalizable
- ✅ Botones configurables
- ✅ Loading state en acciones
- ✅ Keyboard navigation

#### **FormModal**
**Propósito**: Modal con formulario
**Funcionalidades**:
- ✅ Formulario completo
- ✅ Validación integrada
- ✅ Tamaños variables
- ✅ Scroll interno
- ✅ Dirty state warning

## 📱 **Responsive Design Guidelines**

### **Breakpoints**
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px  
- **Desktop**: > 1024px

### **Adaptaciones por Dispositivo**

#### **Mobile (< 768px)**
- ✅ Sidebar → Hamburger menu
- ✅ Tablas → Cards verticales
- ✅ Formularios → Stack vertical
- ✅ Modales → Full screen
- ✅ Filtros → Bottom sheet

#### **Tablet (768px - 1024px)**
- ✅ Sidebar colapsable
- ✅ Tablas con scroll horizontal
- ✅ Formularios en 2 columnas
- ✅ Modales centrados
- ✅ Touch-friendly targets

#### **Desktop (> 1024px)**
- ✅ Sidebar fijo
- ✅ Tablas completas
- ✅ Formularios multi-columna
- ✅ Modales con overlay
- ✅ Hover states

## 🎨 **Design System Integration**

### **Tokens de Diseño**
- **Colores**: Paleta Villa Mitre + estados
- **Tipografía**: Inter (títulos, cuerpo, código)
- **Espaciado**: Sistema de 8px
- **Sombras**: 4 niveles de elevación
- **Bordes**: Radius consistente
- **Animaciones**: Duraciones estándar

### **Componentes Accesibles**
- ✅ **ARIA labels** en todos los componentes
- ✅ **Keyboard navigation** completa
- ✅ **Focus management** apropiado
- ✅ **Screen reader** compatible
- ✅ **Color contrast** WCAG AA
- ✅ **Text scaling** hasta 200%

Esta guía proporciona una base sólida para implementar todos los componentes UI necesarios en el panel de administración, manteniendo consistencia y siguiendo las mejores prácticas de UX/UI.
