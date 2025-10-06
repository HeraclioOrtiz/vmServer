# API Integration Guide - Panel de Administración

## 🔌 **Integración con Backend APIs**

Esta guía detalla cómo integrar el frontend del panel de administración con los endpoints del backend Laravel, incluyendo autenticación, manejo de estados, y patrones de integración.

## 🔐 **Autenticación y Autorización**

### **Sistema de Autenticación**

#### **Login Flow**
```
1. Usuario ingresa credenciales (DNI + password)
2. POST /api/auth/login
3. Backend valida y retorna token + user data
4. Frontend guarda token en localStorage
5. Configura axios interceptor con token
6. Redirige según rol (gym/ o admin/)
```

#### **Token Management**
**Almacenamiento**:
- ✅ Token en `localStorage` para persistencia
- ✅ User data en React Query cache
- ✅ Refresh automático antes de expiración

**Interceptors**:
- ✅ Request: Agregar `Authorization: Bearer {token}`
- ✅ Response: Manejar 401 → logout automático
- ✅ Error: Retry con refresh token

#### **Role-Based Access**
**Verificación de permisos**:
- ✅ `useAuth()` hook para datos de usuario
- ✅ `usePermissions()` hook para verificar permisos
- ✅ `ProtectedRoute` component para rutas
- ✅ Conditional rendering por rol

### **Hooks de Autenticación**

#### **useAuth Hook**
**Funcionalidades**:
- ✅ Estado de autenticación actual
- ✅ Datos del usuario logueado
- ✅ Funciones login/logout
- ✅ Loading states
- ✅ Error handling

#### **usePermissions Hook**
**Funcionalidades**:
- ✅ Verificar permisos específicos
- ✅ Verificar roles (professor, admin)
- ✅ Verificar acceso a recursos
- ✅ Conditional rendering helpers

## 🏋️ **APIs del Panel de Gimnasio**

### **1. Ejercicios API**

#### **Lista de Ejercicios**
**Endpoint**: `GET /api/admin/gym/exercises`
**Query Parameters**:
```
?search=string              // Búsqueda por nombre
&muscle_group[]=chest       // Filtro por grupo muscular
&equipment[]=barbell        // Filtro por equipamiento
&difficulty[]=intermediate  // Filtro por dificultad
&tags[]=strength           // Filtro por tags
&page=1                    // Paginación
&per_page=20              // Elementos por página
&sort_by=name             // Ordenamiento
&sort_direction=asc       // Dirección
```

**React Query Integration**:
```typescript
const useExercises = (filters: ExerciseFilters) => {
  return useQuery({
    queryKey: ['exercises', filters],
    queryFn: () => exerciseApi.getAll(filters),
    staleTime: 5 * 60 * 1000, // 5 minutos
    keepPreviousData: true,   // Para paginación suave
  });
};
```

#### **CRUD Operations**
**Crear Ejercicio**:
- Endpoint: `POST /api/admin/gym/exercises`
- Mutation: `useCreateExercise()`
- Invalidate: `['exercises']` queries

**Actualizar Ejercicio**:
- Endpoint: `PUT /api/admin/gym/exercises/{id}`
- Mutation: `useUpdateExercise()`
- Invalidate: `['exercises']`, `['exercise', id]`

**Eliminar Ejercicio**:
- Endpoint: `DELETE /api/admin/gym/exercises/{id}`
- Mutation: `useDeleteExercise()`
- Invalidate: `['exercises']`

### **2. Plantillas Diarias API**

#### **Lista de Plantillas**
**Endpoint**: `GET /api/admin/gym/daily-templates`
**Filtros disponibles**:
- ✅ Por objetivo (strength, hypertrophy, endurance)
- ✅ Por nivel (beginner, intermediate, advanced)
- ✅ Por duración (rangos de minutos)
- ✅ Por creador (profesor específico)
- ✅ Por tipo (preset vs personalizada)

#### **Detalle de Plantilla**
**Endpoint**: `GET /api/admin/gym/daily-templates/{id}`
**Incluye**:
- ✅ Información básica de la plantilla
- ✅ Lista de ejercicios con orden
- ✅ Series por ejercicio
- ✅ Configuración de descansos
- ✅ Notas del profesor

#### **Wizard de Creación**
**Pasos del wizard**:
1. **Información básica** → Validación local
2. **Selección de ejercicios** → Query exercises API
3. **Configuración de series** → Validación compleja
4. **Revisión final** → POST con todos los datos

### **3. Plantillas Semanales API**

#### **Lista con Vista de Calendario**
**Endpoint**: `GET /api/admin/gym/weekly-templates`
**Response incluye**:
- ✅ Información básica de la plantilla
- ✅ Días de la semana configurados
- ✅ Plantillas diarias asignadas por día
- ✅ Estadísticas de uso

#### **Builder de Plantilla Semanal**
**Funcionalidades**:
- ✅ Drag & drop de plantillas diarias
- ✅ Configuración de días de descanso
- ✅ Vista previa en tiempo real
- ✅ Validación de estructura

### **4. Asignaciones API**

#### **Lista de Asignaciones**
**Endpoint**: `GET /api/admin/gym/weekly-assignments`
**Filtros**:
- ✅ Por estudiante (búsqueda)
- ✅ Por rango de fechas
- ✅ Por estado (activa, completada, cancelada)
- ✅ Por adherencia (rangos)

#### **Wizard de Asignación**
**Pasos**:
1. **Seleccionar estudiante** → `GET /api/users?role=student`
2. **Configurar fechas** → Validación de conflictos
3. **Elegir método** → Templates o manual
4. **Revisión** → `POST /api/admin/gym/weekly-assignments`

## 👥 **APIs del Panel de Administración**

### **1. Usuarios API**

#### **Lista Avanzada de Usuarios**
**Endpoint**: `GET /api/admin/users`
**Filtros complejos**:
```
?search=string              // Nombre, DNI, email
&user_type[]=api           // Tipo de usuario
&is_professor=true         // Solo profesores
&estado_socio[]=ACTIVO     // Estado de socio
&semaforo[]=1             // Semáforo de acceso
&date_from=2025-01-01     // Fecha de registro
&date_to=2025-12-31       // Hasta fecha
&has_gym_access=true      // Con acceso al gym
&sort_by=last_login       // Ordenamiento
```

#### **Perfil Completo de Usuario**
**Endpoint**: `GET /api/admin/users/{id}`
**Secciones de datos**:
- ✅ Información básica y del club
- ✅ Roles y permisos del sistema
- ✅ Actividad en el gimnasio
- ✅ Historial de logins y acciones
- ✅ Configuración de acceso

#### **Edición de Usuario**
**Endpoint**: `PUT /api/admin/users/{id}`
**Secciones editables**:
- ✅ Datos personales básicos
- ✅ Roles y permisos
- ✅ Configuración de acceso
- ✅ Notas internas del admin

### **2. Profesores API**

#### **Lista de Profesores con Estadísticas**
**Endpoint**: `GET /api/admin/professors`
**Datos incluidos**:
- ✅ Información básica del profesor
- ✅ Estadísticas de estudiantes
- ✅ Plantillas creadas
- ✅ Adherencia promedio
- ✅ Última actividad

#### **Asignación de Rol Profesor**
**Endpoint**: `POST /api/admin/users/{id}/assign-professor`
**Datos requeridos**:
- ✅ Calificaciones y certificaciones
- ✅ Permisos específicos
- ✅ Límites de estudiantes
- ✅ Especialidades

#### **Estudiantes por Profesor**
**Endpoint**: `GET /api/admin/professors/{id}/students`
**Funcionalidades**:
- ✅ Lista de estudiantes asignados
- ✅ Métricas de adherencia
- ✅ Reasignación a otro profesor
- ✅ Historial de asignaciones

### **3. Configuración del Sistema API**

#### **Obtener Configuración**
**Endpoint**: `GET /api/admin/settings`
**Secciones**:
- ✅ Configuración de API externa
- ✅ Sistema de usuarios
- ✅ Configuración del gimnasio
- ✅ Notificaciones

#### **Actualizar Configuración**
**Endpoint**: `PUT /api/admin/settings`
**Validaciones**:
- ✅ Validación por sección
- ✅ Test de conectividad para APIs
- ✅ Backup antes de cambios críticos
- ✅ Rollback en caso de error

### **4. Reportes API**

#### **Reporte de Uso del Sistema**
**Endpoint**: `GET /api/admin/reports/system-usage`
**Parámetros**:
```
?period=weekly             // daily, weekly, monthly
&date_from=2025-09-01     // Fecha inicio
&date_to=2025-09-18       // Fecha fin
&metrics[]=active_users   // Métricas específicas
&format=json              // json, csv, pdf
```

#### **Reporte de Actividad de Profesores**
**Endpoint**: `GET /api/admin/reports/professor-activity`
**Datos incluidos**:
- ✅ Plantillas creadas por período
- ✅ Asignaciones realizadas
- ✅ Adherencia promedio de estudiantes
- ✅ Comparación entre profesores

### **5. Auditoría API**

#### **Logs de Auditoría**
**Endpoint**: `GET /api/admin/audit-log`
**Filtros avanzados**:
```
?user_id=1                // Usuario específico
&action=login             // Acción específica
&resource_type=user       // Tipo de recurso
&severity=high            // Severidad
&date_from=2025-09-01     // Rango de fechas
&search=text              // Búsqueda en detalles
```

## 🔄 **Patrones de Integración**

### **1. React Query Patterns**

#### **Query Keys Structure**
```typescript
// Estructura jerárquica de keys
const queryKeys = {
  exercises: ['exercises'] as const,
  exercisesList: (filters: ExerciseFilters) => 
    [...queryKeys.exercises, 'list', filters] as const,
  exercise: (id: number) => 
    [...queryKeys.exercises, 'detail', id] as const,
  
  users: ['users'] as const,
  usersList: (filters: UserFilters) => 
    [...queryKeys.users, 'list', filters] as const,
  user: (id: number) => 
    [...queryKeys.users, 'detail', id] as const,
};
```

#### **Optimistic Updates**
```typescript
const useUpdateExercise = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: updateExercise,
    onMutate: async (newExercise) => {
      // Cancel outgoing refetches
      await queryClient.cancelQueries(['exercises']);
      
      // Snapshot previous value
      const previousExercises = queryClient.getQueryData(['exercises']);
      
      // Optimistically update
      queryClient.setQueryData(['exercises'], (old) => 
        updateExerciseInList(old, newExercise)
      );
      
      return { previousExercises };
    },
    onError: (err, newExercise, context) => {
      // Rollback on error
      queryClient.setQueryData(['exercises'], context.previousExercises);
    },
    onSettled: () => {
      // Refetch to ensure consistency
      queryClient.invalidateQueries(['exercises']);
    },
  });
};
```

### **2. Error Handling Patterns**

#### **Global Error Handler**
```typescript
const useGlobalErrorHandler = () => {
  return useQueryClient().setDefaultOptions({
    queries: {
      onError: (error) => {
        if (error.status === 401) {
          // Redirect to login
          logout();
        } else if (error.status >= 500) {
          // Show system error toast
          toast.error('Error del servidor. Intente nuevamente.');
        }
      },
    },
    mutations: {
      onError: (error) => {
        // Show specific error message
        toast.error(error.message || 'Error al procesar la solicitud');
      },
    },
  });
};
```

#### **Retry Logic**
```typescript
const useExercises = (filters) => {
  return useQuery({
    queryKey: ['exercises', filters],
    queryFn: () => exerciseApi.getAll(filters),
    retry: (failureCount, error) => {
      // Don't retry on 4xx errors
      if (error.status >= 400 && error.status < 500) {
        return false;
      }
      // Retry up to 3 times for 5xx errors
      return failureCount < 3;
    },
    retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
  });
};
```

### **3. Loading States Management**

#### **Skeleton Loading**
```typescript
const ExerciseList = () => {
  const { data: exercises, isLoading, error } = useExercises(filters);
  
  if (isLoading) {
    return <ExerciseTableSkeleton rows={20} />;
  }
  
  if (error) {
    return <ErrorState onRetry={() => refetch()} />;
  }
  
  if (!exercises?.data.length) {
    return <EmptyState onCreateFirst={() => navigate('/exercises/new')} />;
  }
  
  return <ExerciseTable data={exercises.data} />;
};
```

#### **Progressive Loading**
```typescript
const useInfiniteExercises = (filters) => {
  return useInfiniteQuery({
    queryKey: ['exercises', 'infinite', filters],
    queryFn: ({ pageParam = 1 }) => 
      exerciseApi.getAll({ ...filters, page: pageParam }),
    getNextPageParam: (lastPage) => 
      lastPage.meta.current_page < lastPage.meta.last_page 
        ? lastPage.meta.current_page + 1 
        : undefined,
  });
};
```

### **4. Form Integration Patterns**

#### **Form with API Validation**
```typescript
const ExerciseForm = ({ exerciseId }) => {
  const { data: exercise } = useExercise(exerciseId);
  const updateExercise = useUpdateExercise();
  
  const form = useForm({
    defaultValues: exercise,
    resolver: zodResolver(exerciseSchema),
  });
  
  const onSubmit = async (data) => {
    try {
      await updateExercise.mutateAsync({ id: exerciseId, ...data });
      toast.success('Ejercicio actualizado correctamente');
      navigate('/exercises');
    } catch (error) {
      // Handle API validation errors
      if (error.status === 422) {
        Object.entries(error.errors).forEach(([field, messages]) => {
          form.setError(field, { message: messages[0] });
        });
      }
    }
  };
  
  return (
    <form onSubmit={form.handleSubmit(onSubmit)}>
      {/* Form fields */}
    </form>
  );
};
```

### **5. Real-time Updates**

#### **Polling for Live Data**
```typescript
const useSystemMetrics = () => {
  return useQuery({
    queryKey: ['system', 'metrics'],
    queryFn: systemApi.getMetrics,
    refetchInterval: 30000, // 30 seconds
    refetchIntervalInBackground: true,
  });
};
```

#### **WebSocket Integration**
```typescript
const useRealtimeNotifications = () => {
  const queryClient = useQueryClient();
  
  useEffect(() => {
    const ws = new WebSocket('/ws/notifications');
    
    ws.onmessage = (event) => {
      const notification = JSON.parse(event.data);
      
      // Update relevant queries based on notification type
      if (notification.type === 'user_updated') {
        queryClient.invalidateQueries(['users']);
      }
    };
    
    return () => ws.close();
  }, [queryClient]);
};
```

## 📊 **Performance Optimization**

### **1. Query Optimization**

#### **Selective Fetching**
```typescript
// Only fetch needed fields
const useUsersList = (fields = ['id', 'name', 'email']) => {
  return useQuery({
    queryKey: ['users', 'list', { fields }],
    queryFn: () => userApi.getAll({ fields: fields.join(',') }),
  });
};
```

#### **Prefetching**
```typescript
const useExerciseListWithPrefetch = () => {
  const queryClient = useQueryClient();
  
  const { data } = useExercises();
  
  // Prefetch exercise details on hover
  const prefetchExercise = (id: number) => {
    queryClient.prefetchQuery({
      queryKey: ['exercise', id],
      queryFn: () => exerciseApi.getById(id),
      staleTime: 10 * 60 * 1000, // 10 minutes
    });
  };
  
  return { data, prefetchExercise };
};
```

### **2. Bundle Optimization**

#### **Code Splitting**
```typescript
// Lazy load admin routes
const AdminRoutes = lazy(() => import('./admin/AdminRoutes'));
const GymRoutes = lazy(() => import('./gym/GymRoutes'));

// Route-based splitting
const AppRouter = () => (
  <Routes>
    <Route path="/admin/*" element={
      <Suspense fallback={<PageSkeleton />}>
        <AdminRoutes />
      </Suspense>
    } />
    <Route path="/gym/*" element={
      <Suspense fallback={<PageSkeleton />}>
        <GymRoutes />
      </Suspense>
    } />
  </Routes>
);
```

### **3. Caching Strategy**

#### **Cache Configuration**
```typescript
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,     // 5 minutes
      cacheTime: 10 * 60 * 1000,    // 10 minutes
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

// Different cache times by data type
const cacheConfig = {
  exercises: 30 * 60 * 1000,      // 30 minutes (rarely change)
  users: 5 * 60 * 1000,          // 5 minutes (change frequently)
  settings: 60 * 60 * 1000,      // 1 hour (very stable)
  metrics: 1 * 60 * 1000,        // 1 minute (real-time)
};
```

Esta guía proporciona todos los patrones y estrategias necesarios para integrar eficientemente el frontend del panel de administración con las APIs del backend Laravel.
