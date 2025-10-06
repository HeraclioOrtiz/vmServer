# 🎨 **GUÍA FRONTEND - SISTEMA DE ASIGNACIONES JERÁRQUICO**

## 📅 **INFORMACIÓN DEL DOCUMENTO**
- **Fecha:** 26 de Septiembre, 2025
- **Versión:** 1.0
- **Backend:** ✅ **100% COMPLETADO Y TESTADO**
- **API Base:** `http://127.0.0.1:8000/api`

---

## 🎯 **ARQUITECTURA DEL SISTEMA**

### **🏛️ JERARQUÍA IMPLEMENTADA:**
```
👑 ADMINISTRADOR
├── Gestiona asignaciones profesor-estudiante
├── Ve estadísticas generales del sistema
├── Controla estudiantes sin asignar
└── Supervisa todas las asignaciones

👨‍🏫 PROFESOR
├── Ve sus estudiantes asignados
├── Asigna plantillas a sus estudiantes
├── Hace seguimiento de progreso
├── Ve calendario semanal
└── Proporciona feedback

🎓 ESTUDIANTE
├── Ve sus entrenamientos programados
├── Sigue cronograma automático
├── Registra progreso de sesiones
└── Recibe feedback del profesor
```

---

## 🛠️ **STACK TECNOLÓGICO RECOMENDADO**

### **📦 DEPENDENCIAS PRINCIPALES:**
```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "typescript": "^5.0.0",
    "@tanstack/react-query": "^4.32.0",
    "axios": "^1.5.0",
    "react-router-dom": "^6.15.0",
    "tailwindcss": "^3.3.0",
    "@headlessui/react": "^1.7.0",
    "@heroicons/react": "^2.0.0",
    "react-hook-form": "^7.45.0",
    "zod": "^3.22.0",
    "@hookform/resolvers": "^3.3.0",
    "date-fns": "^2.30.0",
    "chart.js": "^4.4.0",
    "react-chartjs-2": "^5.2.0"
  }
}
```

### **🏗️ ESTRUCTURA DE CARPETAS:**
```
src/
├── components/
│   ├── common/           # Componentes reutilizables
│   ├── admin/           # Componentes específicos de admin
│   ├── professor/       # Componentes específicos de profesor
│   └── student/         # Componentes específicos de estudiante
├── hooks/
│   ├── useAuth.ts       # Hook de autenticación
│   ├── useAssignments.ts # Hook para asignaciones
│   └── useTemplates.ts  # Hook para plantillas
├── services/
│   ├── api.ts           # Cliente API base
│   ├── auth.ts          # Servicios de autenticación
│   └── assignments.ts   # Servicios de asignaciones
├── types/
│   ├── auth.ts          # Tipos de autenticación
│   ├── assignments.ts   # Tipos de asignaciones
│   └── templates.ts     # Tipos de plantillas
├── pages/
│   ├── admin/           # Páginas de administrador
│   ├── professor/       # Páginas de profesor
│   └── student/         # Páginas de estudiante
└── utils/
    ├── constants.ts     # Constantes de la aplicación
    └── helpers.ts       # Funciones auxiliares
```

---

## 🔐 **AUTENTICACIÓN Y ROLES**

### **🔑 SISTEMA DE TOKENS:**
```typescript
// types/auth.ts
export interface User {
  id: number;
  name: string;
  email: string;
  dni: string;
  is_admin: boolean;
  is_professor: boolean;
  avatar_path?: string;
}

export interface AuthResponse {
  token: string;
  user: User;
  expires_at: string;
}

// services/auth.ts
export const authService = {
  login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
    const response = await api.post('/auth/login', credentials);
    return response.data;
  },
  
  logout: async (): Promise<void> => {
    await api.post('/auth/logout');
  },
  
  getProfile: async (): Promise<User> => {
    const response = await api.get('/auth/profile');
    return response.data;
  }
};
```

### **🛡️ PROTECCIÓN DE RUTAS:**
```typescript
// components/common/ProtectedRoute.tsx
interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRole?: 'admin' | 'professor' | 'student';
}

export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({
  children,
  requiredRole
}) => {
  const { user, isLoading } = useAuth();
  
  if (isLoading) return <LoadingSpinner />;
  
  if (!user) {
    return <Navigate to="/login" />;
  }
  
  if (requiredRole === 'admin' && !user.is_admin) {
    return <Navigate to="/unauthorized" />;
  }
  
  if (requiredRole === 'professor' && !user.is_professor && !user.is_admin) {
    return <Navigate to="/unauthorized" />;
  }
  
  return <>{children}</>;
};
```

---

## 📊 **ENDPOINTS DISPONIBLES**

### **👑 ENDPOINTS DE ADMINISTRADOR:**

#### **📋 Gestión de Asignaciones:**
```typescript
// GET /admin/assignments
interface AssignmentListResponse {
  data: ProfessorStudentAssignment[];
  current_page: number;
  total: number;
  per_page: number;
}

// POST /admin/assignments
interface CreateAssignmentRequest {
  professor_id: number;
  student_id: number;
  start_date: string;
  end_date?: string;
  admin_notes?: string;
}

// PUT /admin/assignments/{id}
interface UpdateAssignmentRequest {
  status: 'active' | 'paused' | 'completed' | 'cancelled';
  end_date?: string;
  admin_notes?: string;
}
```

#### **📊 Estadísticas y Reportes:**
```typescript
// GET /admin/assignments-stats
interface AdminStats {
  total_professors: number;
  total_students: number;
  active_assignments: number;
  unassigned_students: number;
  assignment_rate: number;
}

// GET /admin/students/unassigned
interface UnassignedStudentsResponse {
  data: User[];
  count: number;
}
```

### **👨‍🏫 ENDPOINTS DE PROFESOR:**

#### **🎓 Gestión de Estudiantes:**
```typescript
// GET /professor/my-students
interface ProfessorStudentsResponse {
  data: ProfessorStudentAssignment[];
  current_page: number;
  total: number;
}

// POST /professor/assign-template
interface AssignTemplateRequest {
  professor_student_assignment_id: number;
  daily_template_id: number;
  start_date: string;
  end_date?: string;
  frequency: number[]; // [1,3,5] = Lun, Mie, Vie
  professor_notes?: string;
}
```

#### **📈 Seguimiento y Progreso:**
```typescript
// GET /professor/my-stats
interface ProfessorStats {
  total_students: number;
  total_assignments: number;
  completed_sessions: number;
  pending_sessions: number;
  adherence_rate: number;
}

// GET /professor/today-sessions
interface TodaySession {
  id: number;
  student_name: string;
  template_title: string;
  scheduled_date: string;
  status: 'pending' | 'completed' | 'skipped';
}

// GET /professor/weekly-calendar
interface WeeklyCalendarResponse {
  sessions: CalendarSession[];
  week_start: string;
  week_end: string;
}
```

---

## 🎨 **COMPONENTES PRINCIPALES A DESARROLLAR**

### **👑 PANEL DE ADMINISTRADOR**

#### **📊 Dashboard Principal:**
```typescript
// pages/admin/Dashboard.tsx
export const AdminDashboard: React.FC = () => {
  const { data: stats } = useQuery(['admin-stats'], 
    () => assignmentService.getAdminStats()
  );
  
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatsCard 
        title="Profesores Activos" 
        value={stats?.total_professors} 
        icon={UserGroupIcon}
      />
      <StatsCard 
        title="Estudiantes" 
        value={stats?.total_students} 
        icon={AcademicCapIcon}
      />
      <StatsCard 
        title="Asignaciones Activas" 
        value={stats?.active_assignments} 
        icon={LinkIcon}
      />
      <StatsCard 
        title="Sin Asignar" 
        value={stats?.unassigned_students} 
        icon={ExclamationTriangleIcon}
      />
    </div>
  );
};
```

#### **📋 Gestión de Asignaciones:**
```typescript
// pages/admin/Assignments.tsx
export const AssignmentsManagement: React.FC = () => {
  const [filters, setFilters] = useState<AssignmentFilters>({});
  const { data: assignments, isLoading } = useQuery(
    ['assignments', filters],
    () => assignmentService.getAssignments(filters)
  );
  
  const createMutation = useMutation(
    assignmentService.createAssignment,
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['assignments']);
        toast.success('Asignación creada exitosamente');
      }
    }
  );
  
  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold">Gestión de Asignaciones</h1>
        <Button onClick={() => setShowCreateModal(true)}>
          Nueva Asignación
        </Button>
      </div>
      
      <AssignmentFilters 
        filters={filters} 
        onFiltersChange={setFilters} 
      />
      
      <AssignmentTable 
        assignments={assignments?.data || []} 
        isLoading={isLoading}
        onEdit={handleEdit}
        onDelete={handleDelete}
      />
      
      <CreateAssignmentModal 
        isOpen={showCreateModal}
        onClose={() => setShowCreateModal(false)}
        onSubmit={createMutation.mutate}
      />
    </div>
  );
};
```

### **👨‍🏫 PANEL DE PROFESOR**

#### **🎓 Mis Estudiantes:**
```typescript
// pages/professor/MyStudents.tsx
export const MyStudents: React.FC = () => {
  const { data: students } = useQuery(
    ['professor-students'],
    () => assignmentService.getProfessorStudents()
  );
  
  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {students?.data.map(assignment => (
          <StudentCard 
            key={assignment.id}
            assignment={assignment}
            onAssignTemplate={() => handleAssignTemplate(assignment)}
            onViewProgress={() => handleViewProgress(assignment)}
          />
        ))}
      </div>
    </div>
  );
};
```

#### **📅 Calendario Semanal:**
```typescript
// pages/professor/WeeklyCalendar.tsx
export const WeeklyCalendar: React.FC = () => {
  const [currentWeek, setCurrentWeek] = useState(new Date());
  const { data: calendar } = useQuery(
    ['weekly-calendar', currentWeek],
    () => assignmentService.getWeeklyCalendar(currentWeek)
  );
  
  return (
    <div className="space-y-6">
      <CalendarHeader 
        currentWeek={currentWeek}
        onWeekChange={setCurrentWeek}
      />
      
      <div className="grid grid-cols-7 gap-4">
        {calendar?.sessions.map(session => (
          <CalendarDay 
            key={session.date}
            date={session.date}
            sessions={session.sessions}
            onSessionClick={handleSessionClick}
          />
        ))}
      </div>
    </div>
  );
};
```

#### **📝 Asignación de Plantillas:**
```typescript
// components/professor/AssignTemplateModal.tsx
export const AssignTemplateModal: React.FC<AssignTemplateModalProps> = ({
  isOpen,
  onClose,
  assignment
}) => {
  const { register, handleSubmit, formState: { errors } } = useForm<AssignTemplateForm>();
  const { data: templates } = useQuery(['templates'], templateService.getTemplates);
  
  const assignMutation = useMutation(
    assignmentService.assignTemplate,
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['professor-students']);
        onClose();
        toast.success('Plantilla asignada exitosamente');
      }
    }
  );
  
  return (
    <Modal isOpen={isOpen} onClose={onClose}>
      <form onSubmit={handleSubmit(assignMutation.mutate)}>
        <div className="space-y-4">
          <TemplateSelector 
            templates={templates}
            {...register('daily_template_id', { required: true })}
          />
          
          <DateRangePicker 
            startDate={register('start_date')}
            endDate={register('end_date')}
          />
          
          <FrequencySelector 
            {...register('frequency', { required: true })}
          />
          
          <TextArea 
            label="Notas del profesor"
            {...register('professor_notes')}
          />
        </div>
        
        <div className="flex justify-end space-x-3 mt-6">
          <Button variant="secondary" onClick={onClose}>
            Cancelar
          </Button>
          <Button type="submit" loading={assignMutation.isLoading}>
            Asignar Plantilla
          </Button>
        </div>
      </form>
    </Modal>
  );
};
```

---

## 🔧 **HOOKS PERSONALIZADOS**

### **📊 Hook de Asignaciones:**
```typescript
// hooks/useAssignments.ts
export const useAssignments = (filters?: AssignmentFilters) => {
  return useQuery(
    ['assignments', filters],
    () => assignmentService.getAssignments(filters),
    {
      staleTime: 5 * 60 * 1000, // 5 minutos
      cacheTime: 10 * 60 * 1000, // 10 minutos
    }
  );
};

export const useCreateAssignment = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    assignmentService.createAssignment,
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['assignments']);
        queryClient.invalidateQueries(['admin-stats']);
        queryClient.invalidateQueries(['unassigned-students']);
      }
    }
  );
};

export const useProfessorStats = () => {
  return useQuery(
    ['professor-stats'],
    () => assignmentService.getProfessorStats(),
    {
      refetchInterval: 30000, // Actualizar cada 30 segundos
    }
  );
};
```

### **📅 Hook de Calendario:**
```typescript
// hooks/useCalendar.ts
export const useWeeklyCalendar = (week: Date) => {
  return useQuery(
    ['weekly-calendar', format(week, 'yyyy-MM-dd')],
    () => assignmentService.getWeeklyCalendar(week),
    {
      staleTime: 2 * 60 * 1000, // 2 minutos
    }
  );
};

export const useTodaySessions = () => {
  return useQuery(
    ['today-sessions'],
    () => assignmentService.getTodaySessions(),
    {
      refetchInterval: 60000, // Actualizar cada minuto
    }
  );
};
```

---

## 🎨 **COMPONENTES REUTILIZABLES**

### **📊 Tarjeta de Estadísticas:**
```typescript
// components/common/StatsCard.tsx
interface StatsCardProps {
  title: string;
  value: number | undefined;
  icon: React.ComponentType<{ className?: string }>;
  trend?: {
    value: number;
    isPositive: boolean;
  };
}

export const StatsCard: React.FC<StatsCardProps> = ({
  title,
  value,
  icon: Icon,
  trend
}) => {
  return (
    <div className="bg-white rounded-lg shadow p-6">
      <div className="flex items-center">
        <div className="flex-shrink-0">
          <Icon className="h-8 w-8 text-indigo-600" />
        </div>
        <div className="ml-5 w-0 flex-1">
          <dl>
            <dt className="text-sm font-medium text-gray-500 truncate">
              {title}
            </dt>
            <dd className="flex items-baseline">
              <div className="text-2xl font-semibold text-gray-900">
                {value ?? '-'}
              </div>
              {trend && (
                <div className={`ml-2 flex items-baseline text-sm font-semibold ${
                  trend.isPositive ? 'text-green-600' : 'text-red-600'
                }`}>
                  {trend.isPositive ? '+' : ''}{trend.value}%
                </div>
              )}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  );
};
```

### **📋 Tabla de Asignaciones:**
```typescript
// components/admin/AssignmentTable.tsx
interface AssignmentTableProps {
  assignments: ProfessorStudentAssignment[];
  isLoading: boolean;
  onEdit: (assignment: ProfessorStudentAssignment) => void;
  onDelete: (id: number) => void;
}

export const AssignmentTable: React.FC<AssignmentTableProps> = ({
  assignments,
  isLoading,
  onEdit,
  onDelete
}) => {
  if (isLoading) {
    return <TableSkeleton />;
  }
  
  return (
    <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
      <table className="min-w-full divide-y divide-gray-300">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Profesor
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Estudiante
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Estado
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Fecha Inicio
            </th>
            <th className="relative px-6 py-3">
              <span className="sr-only">Acciones</span>
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {assignments.map((assignment) => (
            <tr key={assignment.id}>
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="flex items-center">
                  <div className="flex-shrink-0 h-10 w-10">
                    <img 
                      className="h-10 w-10 rounded-full" 
                      src={assignment.professor.avatar_path || '/default-avatar.png'} 
                      alt="" 
                    />
                  </div>
                  <div className="ml-4">
                    <div className="text-sm font-medium text-gray-900">
                      {assignment.professor.name}
                    </div>
                  </div>
                </div>
              </td>
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="text-sm text-gray-900">
                  {assignment.student.name}
                </div>
              </td>
              <td className="px-6 py-4 whitespace-nowrap">
                <StatusBadge status={assignment.status} />
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {format(new Date(assignment.start_date), 'dd/MM/yyyy')}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <ActionMenu 
                  onEdit={() => onEdit(assignment)}
                  onDelete={() => onDelete(assignment.id)}
                />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
```

---

## 🚀 **GUÍA DE IMPLEMENTACIÓN**

### **📅 CRONOGRAMA RECOMENDADO (8-10 días):**

#### **Día 1-2: Configuración Base**
- ✅ Setup del proyecto React + TypeScript
- ✅ Configuración de Tailwind CSS
- ✅ Setup de React Query
- ✅ Configuración de rutas
- ✅ Servicios de API base

#### **Día 3-4: Autenticación y Layout**
- ✅ Sistema de autenticación
- ✅ Layout principal con navegación
- ✅ Protección de rutas por roles
- ✅ Componentes base reutilizables

#### **Día 5-6: Panel de Administrador**
- ✅ Dashboard con estadísticas
- ✅ Gestión de asignaciones
- ✅ Lista de estudiantes sin asignar
- ✅ Formularios de creación/edición

#### **Día 7-8: Panel de Profesor**
- ✅ Vista de estudiantes asignados
- ✅ Asignación de plantillas
- ✅ Calendario semanal
- ✅ Seguimiento de progreso

#### **Día 9-10: Pulido y Testing**
- ✅ Optimizaciones de UX
- ✅ Testing de componentes
- ✅ Responsive design
- ✅ Documentación final

---

## 📱 **CONSIDERACIONES DE UX/UI**

### **🎨 DISEÑO VISUAL:**
- **Paleta de colores:** Azul primario (#3B82F6), Verde éxito (#10B981), Rojo error (#EF4444)
- **Tipografía:** Inter o similar, jerarquía clara
- **Espaciado:** Sistema de 8px (4, 8, 16, 24, 32, 48px)
- **Sombras:** Sutiles, consistentes con Tailwind

### **📱 RESPONSIVE:**
- **Mobile first:** Diseño optimizado para móviles
- **Breakpoints:** sm (640px), md (768px), lg (1024px), xl (1280px)
- **Navegación:** Hamburger menu en móvil, sidebar en desktop

### **♿ ACCESIBILIDAD:**
- **Contraste:** WCAG AA compliant
- **Keyboard navigation:** Todos los elementos accesibles
- **Screen readers:** ARIA labels apropiados
- **Focus indicators:** Visibles y consistentes

---

## 🔍 **TESTING RECOMENDADO**

### **🧪 TIPOS DE TESTING:**
```typescript
// __tests__/components/AssignmentTable.test.tsx
import { render, screen } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AssignmentTable } from '../components/admin/AssignmentTable';

describe('AssignmentTable', () => {
  const queryClient = new QueryClient();
  
  const renderWithProviders = (component: React.ReactElement) => {
    return render(
      <QueryClientProvider client={queryClient}>
        {component}
      </QueryClientProvider>
    );
  };
  
  it('renders assignment data correctly', () => {
    const mockAssignments = [
      {
        id: 1,
        professor: { name: 'Juan Pérez' },
        student: { name: 'María García' },
        status: 'active',
        start_date: '2025-09-26'
      }
    ];
    
    renderWithProviders(
      <AssignmentTable 
        assignments={mockAssignments}
        isLoading={false}
        onEdit={jest.fn()}
        onDelete={jest.fn()}
      />
    );
    
    expect(screen.getByText('Juan Pérez')).toBeInTheDocument();
    expect(screen.getByText('María García')).toBeInTheDocument();
  });
});
```

---

## 📋 **CHECKLIST DE IMPLEMENTACIÓN**

### **✅ CONFIGURACIÓN INICIAL:**
- [ ] Proyecto React + TypeScript configurado
- [ ] Tailwind CSS instalado y configurado
- [ ] React Query configurado
- [ ] React Router configurado
- [ ] Servicios de API implementados

### **✅ AUTENTICACIÓN:**
- [ ] Login/logout funcionando
- [ ] Protección de rutas por roles
- [ ] Manejo de tokens
- [ ] Persistencia de sesión

### **✅ PANEL ADMINISTRADOR:**
- [ ] Dashboard con estadísticas
- [ ] Lista de asignaciones con filtros
- [ ] Crear nueva asignación
- [ ] Editar/eliminar asignaciones
- [ ] Lista de estudiantes sin asignar

### **✅ PANEL PROFESOR:**
- [ ] Lista de estudiantes asignados
- [ ] Asignar plantillas a estudiantes
- [ ] Calendario semanal
- [ ] Seguimiento de progreso
- [ ] Estadísticas personales

### **✅ UX/UI:**
- [ ] Diseño responsive
- [ ] Loading states
- [ ] Error handling
- [ ] Notificaciones toast
- [ ] Confirmaciones de acciones

### **✅ TESTING:**
- [ ] Tests unitarios de componentes
- [ ] Tests de integración
- [ ] Tests de hooks personalizados
- [ ] Tests E2E críticos

---

**📋 DOCUMENTO CREADO:** 26/09/2025 12:42 PM  
**🎯 BACKEND STATUS:** ✅ 100% COMPLETADO Y TESTADO  
**🚀 PRÓXIMO PASO:** Implementación Frontend  
**📞 CONTACTO:** Equipo Backend disponible para consultas
