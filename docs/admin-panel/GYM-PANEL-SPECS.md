# Panel de Gimnasio - Especificaciones Técnicas

## 🏋️ **Panel de Profesores - Funcionalidades**

### **Dashboard Principal**
**Ruta:** `/gym/dashboard`
**Acceso:** Solo profesores (`is_professor: true`)

**Métricas mostradas:**
- Total de alumnos asignados
- Rutinas activas esta semana
- Plantillas creadas por el profesor
- Adherencia promedio de alumnos

**Componentes:**
```typescript
// Tarjetas de métricas
interface MetricCard {
  title: string;
  value: number;
  change: number; // % cambio vs semana anterior
  icon: React.ComponentType;
}

// Gráfico de adherencia semanal
interface AdherenceChart {
  data: {
    week: string;
    adherence: number;
  }[];
}
```

## 📋 **Gestión de Ejercicios**

### **Lista de Ejercicios**
**Ruta:** `/gym/exercises`
**Endpoint:** `GET /api/admin/gym/exercises`

**Funcionalidades:**
- ✅ Listado paginado (20 por página)
- ✅ Búsqueda por nombre
- ✅ Filtros por grupo muscular, equipo, dificultad
- ✅ Ordenamiento por nombre, fecha creación
- ✅ Acciones: Ver, Editar, Eliminar

**Componente Table:**
```typescript
interface ExerciseTableRow {
  id: number;
  name: string;
  muscle_group: string;
  equipment: string;
  difficulty: 'beginner' | 'intermediate' | 'advanced';
  tags: string[];
  created_at: string;
  actions: React.ReactNode;
}

interface ExerciseFilters {
  search: string;
  muscle_group: string[];
  equipment: string[];
  difficulty: string[];
}
```

### **Crear/Editar Ejercicio**
**Rutas:** `/gym/exercises/new`, `/gym/exercises/:id/edit`
**Endpoints:** `POST /api/admin/gym/exercises`, `PUT /api/admin/gym/exercises/:id`

**Formulario:**
```typescript
interface ExerciseForm {
  name: string;                    // Requerido
  muscle_group: string;           // Select
  movement_pattern: string;       // Select
  equipment: string;              // Select
  difficulty: 'beginner' | 'intermediate' | 'advanced';
  tags: string[];                 // Multi-select con chips
  instructions: string;           // Textarea
  tempo?: string;                 // Opcional, formato "3-1-1"
  video_url?: string;            // Opcional, URL de video
  image_url?: string;            // Opcional, URL de imagen
}

// Validaciones con Zod
const exerciseSchema = z.object({
  name: z.string().min(3, 'Mínimo 3 caracteres'),
  muscle_group: z.string().min(1, 'Selecciona un grupo muscular'),
  // ... resto de validaciones
});
```

## 📅 **Gestión de Plantillas Diarias**

### **Lista de Plantillas Diarias**
**Ruta:** `/gym/daily-templates`
**Endpoint:** `GET /api/admin/gym/daily-templates`

**Funcionalidades:**
- ✅ Listado con vista de tarjetas
- ✅ Filtros por objetivo, duración, nivel
- ✅ Búsqueda por título
- ✅ Indicador de plantillas prefijadas vs personalizadas
- ✅ Duplicar plantilla
- ✅ Vista previa rápida

**Componente Card:**
```typescript
interface DailyTemplateCard {
  id: number;
  title: string;
  goal: 'strength' | 'hypertrophy' | 'endurance' | 'mobility';
  duration_minutes: number;
  level: 'beginner' | 'intermediate' | 'advanced';
  exercises_count: number;
  is_preset: boolean;
  created_at: string;
  actions: {
    view: () => void;
    edit: () => void;
    duplicate: () => void;
    delete: () => void;
  };
}
```

### **Crear/Editar Plantilla Diaria**
**Rutas:** `/gym/daily-templates/new`, `/gym/daily-templates/:id/edit`

**Wizard de 3 pasos:**

**Paso 1: Información General**
```typescript
interface TemplateBasicInfo {
  title: string;
  goal: 'strength' | 'hypertrophy' | 'endurance' | 'mobility';
  duration_minutes: number;
  level: 'beginner' | 'intermediate' | 'advanced';
  tags: string[];
  description?: string;
}
```

**Paso 2: Selección de Ejercicios**
```typescript
interface ExerciseSelection {
  exercise_id: number;
  display_order: number;
  notes?: string;
  rest_between_sets?: number;
}

// Componente drag & drop para reordenar
interface ExerciseList {
  available: Exercise[];      // Ejercicios disponibles
  selected: ExerciseSelection[]; // Ejercicios seleccionados
  onAdd: (exercise: Exercise) => void;
  onRemove: (index: number) => void;
  onReorder: (from: number, to: number) => void;
}
```

**Paso 3: Configuración de Series**
```typescript
interface SetConfiguration {
  exercise_id: number;
  sets: {
    set_number: number;
    reps_min?: number;
    reps_max?: number;
    rest_seconds?: number;
    tempo?: string;
    rpe_target?: number;
    notes?: string;
  }[];
}

// Componente para cada ejercicio
interface ExerciseSetsEditor {
  exercise: Exercise;
  sets: SetConfiguration['sets'];
  onSetsChange: (sets: SetConfiguration['sets']) => void;
  onAddSet: () => void;
  onRemoveSet: (index: number) => void;
}
```

## 📆 **Gestión de Plantillas Semanales**

### **Lista de Plantillas Semanales**
**Ruta:** `/gym/weekly-templates`
**Endpoint:** `GET /api/admin/gym/weekly-templates`

**Vista de calendario semanal:**
```typescript
interface WeeklyTemplateCard {
  id: number;
  title: string;
  split: 'full-body' | 'upper-lower' | 'ppl' | 'custom';
  days_per_week: number;
  days: {
    weekday: 1 | 2 | 3 | 4 | 5 | 6 | 7;
    daily_template?: DailyTemplate;
    is_rest_day: boolean;
  }[];
  created_at: string;
}

// Componente visual de semana
interface WeeklyCalendarView {
  template: WeeklyTemplateCard;
  onDayClick: (weekday: number) => void;
  onEditDay: (weekday: number) => void;
}
```

### **Crear/Editar Plantilla Semanal**
**Rutas:** `/gym/weekly-templates/new`, `/gym/weekly-templates/:id/edit`

**Interfaz drag & drop:**
```typescript
interface WeeklyTemplateBuilder {
  basicInfo: {
    title: string;
    split: string;
    description?: string;
  };
  days: {
    [weekday: number]: {
      daily_template_id?: number;
      is_rest_day: boolean;
    };
  };
  availableTemplates: DailyTemplate[];
}

// Componente de día de la semana
interface DaySlot {
  weekday: number;
  dayName: string;
  assignedTemplate?: DailyTemplate;
  isRestDay: boolean;
  onAssignTemplate: (templateId: number) => void;
  onSetRestDay: () => void;
  onClear: () => void;
}
```

## 👥 **Gestión de Asignaciones**

### **Lista de Asignaciones**
**Ruta:** `/gym/assignments`
**Endpoint:** `GET /api/admin/gym/weekly-assignments`

**Funcionalidades:**
- ✅ Vista de tabla con filtros
- ✅ Filtro por alumno, fecha, estado
- ✅ Búsqueda por nombre de alumno
- ✅ Indicadores de adherencia
- ✅ Acciones rápidas (editar notas, duplicar)

**Componente:**
```typescript
interface AssignmentTableRow {
  id: number;
  student: {
    id: number;
    name: string;
    avatar_url?: string;
  };
  week_start: string;
  week_end: string;
  source_type: 'template' | 'manual';
  adherence_percentage?: number;
  status: 'active' | 'completed' | 'cancelled';
  notes?: string;
  created_at: string;
  actions: React.ReactNode;
}

interface AssignmentFilters {
  student_search: string;
  date_from: string;
  date_to: string;
  status: string[];
  source_type: string[];
}
```

### **Crear Nueva Asignación**
**Ruta:** `/gym/assignments/new`
**Endpoint:** `POST /api/admin/gym/weekly-assignments`

**Wizard de 4 pasos:**

**Paso 1: Seleccionar Alumno**
```typescript
interface StudentSelector {
  students: User[];
  selectedStudent?: User;
  searchTerm: string;
  onStudentSelect: (student: User) => void;
  onSearch: (term: string) => void;
}
```

**Paso 2: Configurar Fechas**
```typescript
interface WeekSelector {
  week_start: string;
  week_end: string;
  onWeekChange: (start: string, end: string) => void;
  conflictingAssignments?: Assignment[];
}
```

**Paso 3: Elegir Método**
```typescript
interface AssignmentMethod {
  method: 'template' | 'manual' | 'assistant';
  weeklyTemplate?: WeeklyTemplate;
  manualDays?: ManualDayAssignment[];
  assistantConfig?: AssistantConfig;
}

interface AssistantConfig {
  goal: 'strength' | 'hypertrophy' | 'endurance';
  days_per_week: number;
  session_duration: number;
  experience_level: 'beginner' | 'intermediate' | 'advanced';
  available_equipment: string[];
}
```

**Paso 4: Revisión y Personalización**
```typescript
interface AssignmentReview {
  assignment: WeeklyAssignmentDraft;
  onDayEdit: (weekday: number) => void;
  onExerciseEdit: (dayId: number, exerciseId: number) => void;
  onNotesChange: (notes: string) => void;
  onSubmit: () => void;
}
```

## 📊 **Reportes y Métricas**

### **Dashboard de Reportes**
**Ruta:** `/gym/reports`

**Métricas disponibles:**
- Adherencia por alumno/período
- Ejercicios más utilizados
- Plantillas más populares
- Progresión de alumnos
- Uso del sistema por profesor

**Componentes:**
```typescript
interface ReportCard {
  title: string;
  description: string;
  chart: React.ComponentType;
  filters: ReportFilters;
  exportOptions: ('pdf' | 'excel' | 'csv')[];
}

interface AdherenceReport {
  student_id: number;
  student_name: string;
  total_assignments: number;
  completed_sessions: number;
  adherence_percentage: number;
  trend: 'improving' | 'stable' | 'declining';
}
```

## 🔧 **Configuración y Preferencias**

### **Configuración del Profesor**
**Ruta:** `/gym/settings`

**Opciones:**
```typescript
interface ProfessorSettings {
  default_session_duration: number;
  preferred_rest_times: {
    strength: number;
    hypertrophy: number;
    endurance: number;
  };
  default_rpe_targets: {
    beginner: number;
    intermediate: number;
    advanced: number;
  };
  notification_preferences: {
    new_assignments: boolean;
    adherence_alerts: boolean;
    weekly_reports: boolean;
  };
  template_sharing: {
    allow_public: boolean;
    allow_collaboration: boolean;
  };
}
```

## 🎨 **Componentes UI Específicos**

### **ExerciseSelector**
```typescript
interface ExerciseSelectorProps {
  exercises: Exercise[];
  selected: number[];
  onSelectionChange: (selected: number[]) => void;
  filters: ExerciseFilters;
  multiSelect?: boolean;
}
```

### **SetEditor**
```typescript
interface SetEditorProps {
  sets: Set[];
  onSetsChange: (sets: Set[]) => void;
  exerciseType: 'strength' | 'cardio' | 'flexibility';
  showAdvanced?: boolean;
}
```

### **WeekCalendar**
```typescript
interface WeekCalendarProps {
  days: DayAssignment[];
  onDayClick: (weekday: number) => void;
  readonly?: boolean;
  showAdherence?: boolean;
}
```

### **StudentPicker**
```typescript
interface StudentPickerProps {
  students: User[];
  selected?: User;
  onSelect: (student: User) => void;
  searchable?: boolean;
  showAvatar?: boolean;
}
```

## 🔄 **Estados de Carga y Error**

### **Loading States**
- Skeleton para listas de ejercicios
- Spinner para formularios
- Progress bar para wizards
- Shimmer para tarjetas

### **Error States**
- Error boundaries para crashes
- Toast notifications para errores de API
- Inline errors para validación de formularios
- Retry buttons para fallos de red

### **Empty States**
- Ilustraciones para listas vacías
- Call-to-action para crear primer elemento
- Onboarding para nuevos profesores
