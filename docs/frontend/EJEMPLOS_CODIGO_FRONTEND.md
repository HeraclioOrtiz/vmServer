# 💻 **EJEMPLOS DE CÓDIGO FRONTEND**

## 🔧 **CONFIGURACIÓN INICIAL**

### **API Client Setup:**
```typescript
// services/api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 10000,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### **React Query Setup:**
```typescript
// App.tsx
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutos
      cacheTime: 10 * 60 * 1000, // 10 minutos
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <Router>
        <Routes>
          <Route path="/admin/*" element={
            <ProtectedRoute requiredRole="admin">
              <AdminLayout />
            </ProtectedRoute>
          } />
          <Route path="/professor/*" element={
            <ProtectedRoute requiredRole="professor">
              <ProfessorLayout />
            </ProtectedRoute>
          } />
        </Routes>
      </Router>
    </QueryClientProvider>
  );
}
```

## 🎯 **COMPONENTES CLAVE**

### **📊 Dashboard Admin:**
```typescript
// pages/admin/Dashboard.tsx
export const AdminDashboard: React.FC = () => {
  const { data: stats, isLoading } = useQuery(
    ['admin-stats'],
    () => api.get('/admin/assignments-stats').then(res => res.data)
  );

  if (isLoading) return <DashboardSkeleton />;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatsCard 
        title="Profesores" 
        value={stats?.total_professors}
        icon={UserGroupIcon}
        color="blue"
      />
      <StatsCard 
        title="Estudiantes" 
        value={stats?.total_students}
        icon={AcademicCapIcon}
        color="green"
      />
      <StatsCard 
        title="Asignaciones" 
        value={stats?.active_assignments}
        icon={LinkIcon}
        color="purple"
      />
      <StatsCard 
        title="Sin Asignar" 
        value={stats?.unassigned_students}
        icon={ExclamationTriangleIcon}
        color="orange"
      />
    </div>
  );
};
```

### **📋 Tabla de Asignaciones:**
```typescript
// components/admin/AssignmentTable.tsx
export const AssignmentTable: React.FC = () => {
  const [filters, setFilters] = useState({});
  const { data: assignments, isLoading } = useQuery(
    ['assignments', filters],
    () => api.get('/admin/assignments', { params: filters })
  );

  const deleteMutation = useMutation(
    (id: number) => api.delete(`/admin/assignments/${id}`),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['assignments']);
        toast.success('Asignación eliminada');
      }
    }
  );

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-300">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Profesor
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Estudiante
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Estado
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {assignments?.data.map((assignment) => (
            <tr key={assignment.id}>
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="flex items-center">
                  <img 
                    className="h-8 w-8 rounded-full" 
                    src={assignment.professor.avatar_path || '/default-avatar.png'} 
                  />
                  <span className="ml-3 text-sm font-medium text-gray-900">
                    {assignment.professor.name}
                  </span>
                </div>
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {assignment.student.name}
              </td>
              <td className="px-6 py-4 whitespace-nowrap">
                <StatusBadge status={assignment.status} />
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  onClick={() => deleteMutation.mutate(assignment.id)}
                  className="text-red-600 hover:text-red-900"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
```

### **👨‍🏫 Panel Profesor:**
```typescript
// pages/professor/MyStudents.tsx
export const MyStudents: React.FC = () => {
  const { data: students } = useQuery(
    ['professor-students'],
    () => api.get('/professor/my-students').then(res => res.data)
  );

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {students?.data.map(assignment => (
        <StudentCard 
          key={assignment.id}
          assignment={assignment}
          onAssignTemplate={() => setSelectedAssignment(assignment)}
        />
      ))}
    </div>
  );
};

// components/professor/StudentCard.tsx
interface StudentCardProps {
  assignment: ProfessorStudentAssignment;
  onAssignTemplate: () => void;
}

export const StudentCard: React.FC<StudentCardProps> = ({
  assignment,
  onAssignTemplate
}) => {
  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center mb-4">
        <img 
          className="h-12 w-12 rounded-full" 
          src={assignment.student.avatar_path || '/default-avatar.png'} 
        />
        <div className="ml-4">
          <h3 className="text-lg font-semibold">{assignment.student.name}</h3>
          <p className="text-sm text-gray-500">
            Desde {format(new Date(assignment.start_date), 'dd/MM/yyyy')}
          </p>
        </div>
      </div>
      
      <div className="space-y-2 mb-4">
        <div className="flex justify-between text-sm">
          <span>Plantillas activas:</span>
          <span className="font-medium">{assignment.active_templates || 0}</span>
        </div>
        <div className="flex justify-between text-sm">
          <span>Progreso:</span>
          <span className="font-medium">{assignment.progress_percentage || 0}%</span>
        </div>
      </div>
      
      <div className="flex space-x-2">
        <button
          onClick={onAssignTemplate}
          className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
        >
          Asignar Plantilla
        </button>
        <button className="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
          Ver Progreso
        </button>
      </div>
    </div>
  );
};
```

## 🔧 **HOOKS PERSONALIZADOS**

### **useAssignments Hook:**
```typescript
// hooks/useAssignments.ts
export const useAssignments = (filters?: AssignmentFilters) => {
  return useQuery(
    ['assignments', filters],
    () => api.get('/admin/assignments', { params: filters }).then(res => res.data),
    {
      staleTime: 5 * 60 * 1000,
      keepPreviousData: true,
    }
  );
};

export const useCreateAssignment = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: CreateAssignmentRequest) => 
      api.post('/admin/assignments', data).then(res => res.data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['assignments']);
        queryClient.invalidateQueries(['admin-stats']);
        queryClient.invalidateQueries(['unassigned-students']);
      }
    }
  );
};
```

### **useProfessor Hook:**
```typescript
// hooks/useProfessor.ts
export const useProfessorStudents = () => {
  return useQuery(
    ['professor-students'],
    () => api.get('/professor/my-students').then(res => res.data),
    {
      staleTime: 2 * 60 * 1000,
    }
  );
};

export const useAssignTemplate = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: AssignTemplateRequest) => 
      api.post('/professor/assign-template', data).then(res => res.data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['professor-students']);
        queryClient.invalidateQueries(['weekly-calendar']);
        queryClient.invalidateQueries(['professor-stats']);
      }
    }
  );
};
```

## 📱 **COMPONENTES RESPONSIVE**

### **StatsCard Component:**
```typescript
// components/common/StatsCard.tsx
interface StatsCardProps {
  title: string;
  value: number | undefined;
  icon: React.ComponentType<{ className?: string }>;
  color: 'blue' | 'green' | 'purple' | 'orange';
}

export const StatsCard: React.FC<StatsCardProps> = ({
  title,
  value,
  icon: Icon,
  color
}) => {
  const colorClasses = {
    blue: 'text-blue-600 bg-blue-100',
    green: 'text-green-600 bg-green-100',
    purple: 'text-purple-600 bg-purple-100',
    orange: 'text-orange-600 bg-orange-100',
  };

  return (
    <div className="bg-white rounded-lg shadow p-6">
      <div className="flex items-center">
        <div className={`flex-shrink-0 p-3 rounded-md ${colorClasses[color]}`}>
          <Icon className="h-6 w-6" />
        </div>
        <div className="ml-5 w-0 flex-1">
          <dl>
            <dt className="text-sm font-medium text-gray-500 truncate">
              {title}
            </dt>
            <dd className="text-2xl font-semibold text-gray-900">
              {value ?? '-'}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  );
};
```

---

**📋 DOCUMENTO CREADO:** 26/09/2025  
**🎯 PROPÓSITO:** Ejemplos prácticos para implementación  
**📞 SOPORTE:** Backend team disponible**
