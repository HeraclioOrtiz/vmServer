# 🖥️ MANUAL COMPLETO - PANEL ADMIN VILLA MITRE

## 🎯 **GUÍA PARA DESARROLLO FRONTEND ADMINISTRATIVO**

Este manual proporciona toda la información necesaria para desarrollar el panel de administración web del gimnasio Villa Mitre, incluyendo endpoints, flujos administrativos, gestión de usuarios y funcionalidades avanzadas.

---

## 📋 **ÍNDICE**

1. [Información General](#información-general)
2. [Autenticación Administrativa](#autenticación-administrativa)
3. [Roles y Permisos](#roles-y-permisos)
4. [Endpoints Administrativos](#endpoints-administrativos)
5. [Gestión de Usuarios](#gestión-de-usuarios)
6. [Sistema de Promociones](#sistema-de-promociones)
7. [Panel Gimnasio](#panel-gimnasio)
8. [Configuración del Sistema](#configuración-del-sistema)
9. [Auditoría y Logs](#auditoría-y-logs)
10. [Integración con App Móvil](#integración-con-app-móvil)

---

## 🏢 **INFORMACIÓN GENERAL**

### **Arquitectura del Panel**
- **Frontend:** React + Vite + TypeScript
- **Styling:** Tailwind CSS
- **Estado:** React Query para cache y sincronización
- **Backend:** Laravel API REST
- **Base URL:** `http://127.0.0.1:8000/api`
- **Autenticación:** Laravel Sanctum (Bearer Token)

### **Dos Paneles Especializados**
1. **Panel Villa Mitre (Admins):** Gestión completa del sistema
2. **Panel Gimnasio (Profesores):** Gestión de entrenamientos y ejercicios

### **Características Principales**
- Dashboard con métricas en tiempo real
- Gestión avanzada de usuarios con filtros múltiples
- Sistema completo de promociones
- CRUD completo de ejercicios y plantillas
- Auditoría automática de todas las acciones
- Reportes y estadísticas avanzadas

---

## 🔐 **AUTENTICACIÓN ADMINISTRATIVA**

### **Flujo de Autenticación Admin**

#### **Login Administrativo**
- **Endpoint:** `POST /test/login` (para testing) o `POST /auth/login`
- **Datos requeridos:** `dni`, `password`
- **Validación adicional:** Usuario debe tener `is_admin: true` o `is_professor: true`
- **Respuesta:** Token + datos completos del usuario + permisos

#### **Verificación de Permisos**
- **Endpoint:** `GET /auth/me`
- **Respuesta incluye:**
  - `is_admin`: Acceso completo al sistema
  - `is_professor`: Acceso al panel gimnasio
  - `permissions`: Array de permisos específicos
  - `account_status`: Estado de la cuenta

### **Permisos Granulares**
- `user_management`: Gestión de usuarios
- `gym_admin`: Administración del gimnasio
- `system_settings`: Configuración del sistema
- `reports_access`: Acceso a reportes
- `audit_logs`: Visualización de logs
- `super_admin`: Acceso total al sistema

---

## 👥 **ROLES Y PERMISOS**

### **🔑 SUPER ADMINISTRADOR**
- **Permisos:** Todos los permisos del sistema
- **Acceso:** Panel Villa Mitre completo + Panel Gimnasio
- **Funciones:**
  - Gestión completa de usuarios
  - Configuración del sistema
  - Auditoría y logs
  - Reportes avanzados
  - Gestión de promociones

### **👨‍💼 ADMINISTRADOR**
- **Permisos:** `user_management`, `reports_access`, `audit_logs`
- **Acceso:** Panel Villa Mitre (limitado)
- **Funciones:**
  - Gestión de usuarios básica
  - Visualización de reportes
  - Gestión de promociones
  - Auditoría limitada

### **👨‍🏫 PROFESOR**
- **Permisos:** `gym_admin`
- **Acceso:** Panel Gimnasio completo
- **Funciones:**
  - CRUD de ejercicios
  - Gestión de plantillas
  - Asignaciones de entrenamientos
  - Reportes de adherencia

---

## 🔗 **ENDPOINTS ADMINISTRATIVOS**

### **👥 GESTIÓN DE USUARIOS**

#### **Lista de Usuarios**
- **Endpoint:** `GET /users`
- **Parámetros de filtro:**
  - `search`: Búsqueda por nombre, email o DNI
  - `user_type`: Filtrar por tipo (local/api)
  - `account_status`: Filtrar por estado
  - `is_professor`: Solo profesores
  - `is_admin`: Solo administradores
  - `needs_refresh`: Usuarios que necesitan actualización
- **Paginación:** Automática con `page` y `per_page`

#### **Ver Usuario Específico**
- **Endpoint:** `GET /users/{id}`
- **Respuesta:** Datos completos del usuario + historial

#### **Estadísticas de Usuarios**
- **Endpoint:** `GET /admin/users/stats`
- **Respuesta:**
  - Total de usuarios
  - Usuarios por tipo
  - Usuarios activos/inactivos
  - Crecimiento mensual
  - Métricas de engagement

#### **Cambiar Tipo de Usuario**
- **Endpoint:** `POST /users/{id}/change-type`
- **Datos:** `type` (local/api)
- **Validación:** Solo admins pueden cambiar tipos

### **🎯 SISTEMA DE PROMOCIONES**

#### **Solicitudes Pendientes**
- **Endpoint:** `GET /promotion/pending`
- **Respuesta:** Lista de solicitudes esperando aprobación
- **Datos incluidos:** Usuario, fecha, razón, información adicional

#### **Historial de Promociones**
- **Endpoint:** `GET /promotion/history`
- **Filtros:** Por fecha, estado, usuario
- **Respuesta:** Historial completo con detalles

#### **Usuarios Elegibles**
- **Endpoint:** `GET /promotion/eligible`
- **Respuesta:** Usuarios que pueden solicitar promoción
- **Criterios:** Tipo local, sin solicitud pendiente, activos

#### **Estadísticas de Promociones**
- **Endpoint:** `GET /promotion/stats`
- **Métricas:**
  - Solicitudes por mes
  - Tasa de aprobación
  - Tiempo promedio de procesamiento
  - Usuarios promovidos vs rechazados

### **🏋️ PANEL GIMNASIO**

#### **Gestión de Ejercicios**
- **Listar:** `GET /admin/gym/exercises`
- **Crear:** `POST /admin/gym/exercises`
- **Ver:** `GET /admin/gym/exercises/{id}`
- **Editar:** `PUT /admin/gym/exercises/{id}`
- **Eliminar:** `DELETE /admin/gym/exercises/{id}`
- **Duplicar:** `POST /admin/gym/exercises/{id}/duplicate`

#### **Plantillas Diarias**
- **Listar:** `GET /admin/gym/daily-templates`
- **Crear:** `POST /admin/gym/daily-templates`
- **Ver:** `GET /admin/gym/daily-templates/{id}`
- **Editar:** `PUT /admin/gym/daily-templates/{id}`
- **Duplicar:** `POST /admin/gym/daily-templates/{id}/duplicate`

#### **Plantillas Semanales**
- **Listar:** `GET /admin/gym/weekly-templates`
- **Crear:** `POST /admin/gym/weekly-templates`
- **Gestionar:** Endpoints similares a plantillas diarias

#### **Asignaciones**
- **Ver asignaciones:** `GET /admin/gym/weekly-assignments`
- **Crear asignación:** `POST /admin/gym/weekly-assignments`
- **Estadísticas:** `GET /admin/gym/assignments/stats`

---

## 📊 **GESTIÓN DE USUARIOS**

### **Dashboard de Usuarios**
- **Métricas principales:**
  - Total de usuarios registrados
  - Usuarios activos en el último mes
  - Nuevos registros por semana
  - Distribución por tipo (local/api)
  - Usuarios con promociones pendientes

### **Filtros Avanzados**
- **Por estado:** Activo, Inactivo, Suspendido
- **Por tipo:** Local, API
- **Por rol:** Estudiante, Profesor, Admin
- **Por fecha:** Registro, último acceso
- **Por promoción:** Pendiente, Aprobado, Rechazado

### **Acciones Masivas**
- Cambiar estado de múltiples usuarios
- Enviar notificaciones grupales
- Exportar listas filtradas
- Aplicar cambios en lote

### **Perfil de Usuario Detallado**
- **Información básica:** Nombre, email, DNI, teléfono
- **Estado de cuenta:** Activo/inactivo, fecha de registro
- **Tipo de usuario:** Local/API con detalles del club
- **Promociones:** Historial completo de solicitudes
- **Actividad:** Último acceso, entrenamientos completados
- **Permisos:** Roles y permisos específicos

---

## 🎯 **SISTEMA DE PROMOCIONES**

### **Dashboard de Promociones**
- **Solicitudes pendientes:** Número y lista
- **Procesadas hoy:** Aprobadas y rechazadas
- **Tiempo promedio:** De procesamiento
- **Tasa de éxito:** Porcentaje de aprobaciones

### **Gestión de Solicitudes**

#### **Revisar Solicitud**
- **Datos del solicitante:** Perfil completo
- **Información de la solicitud:** Razón, información adicional
- **Verificación:** Estado en sistema del club
- **Historial:** Solicitudes anteriores si las hay

#### **Procesar Solicitud**
- **Aprobar:** Cambia usuario a tipo API
- **Rechazar:** Mantiene como local, registra razón
- **Posponer:** Marca para revisión posterior
- **Solicitar información:** Requiere datos adicionales

### **Reportes de Promociones**
- **Por período:** Solicitudes por mes/semana
- **Por resultado:** Aprobadas vs rechazadas
- **Por tiempo:** Tiempo de procesamiento
- **Por usuario:** Historial individual

---

## 🏋️ **PANEL GIMNASIO**

### **Gestión de Ejercicios**

#### **Crear Ejercicio**
- **Datos requeridos:**
  - `name`: Nombre del ejercicio
  - `description`: Descripción detallada
  - `muscle_group`: Grupo muscular principal
  - `equipment`: Equipamiento necesario
  - `difficulty`: Nivel de dificultad
- **Datos opcionales:**
  - `instructions`: Instrucciones paso a paso
  - `tips`: Consejos de ejecución
  - `variations`: Variaciones del ejercicio

#### **Plantillas Diarias**
- **Estructura compleja requerida:**
  - `title`: Título único
  - `description`: Descripción
  - `category`: strength, cardio, flexibility, etc.
  - `difficulty_level`: 1-5
  - `estimated_duration`: Duración en minutos
  - `target_muscle_groups`: Array de grupos musculares
  - `exercises`: Array de ejercicios con sets detallados

#### **Estructura de Sets**
```
exercises: [
  {
    exercise_id: ID_del_ejercicio,
    order: 1,
    rest_seconds: 60,
    sets: [
      {
        set_number: 1,
        reps: 12,
        weight: 20.5,
        rest_seconds: 60
      }
    ]
  }
]
```

### **Asignaciones de Entrenamientos**
- **Asignar plantilla a usuario específico**
- **Asignaciones grupales por criterios**
- **Programación de entrenamientos**
- **Seguimiento de progreso**

---

## ⚙️ **CONFIGURACIÓN DEL SISTEMA**

### **Configuraciones Disponibles**
- **Endpoint:** `GET /admin/settings`
- **Categorías:**
  - `general`: Configuraciones generales
  - `security`: Configuraciones de seguridad
  - `notifications`: Configuraciones de notificaciones
  - `api`: Configuraciones de APIs externas

### **Actualizar Configuración**
- **Endpoint:** `PUT /admin/settings/{key}`
- **Datos:** `value`, `description` (opcional)
- **Validación:** Solo super admins pueden modificar

### **Configuraciones Críticas**
- **Timeout de sesión**
- **Política de contraseñas**
- **Configuración de API externa del club**
- **Límites de rate limiting**
- **Configuración de cache**

---

## 📋 **AUDITORÍA Y LOGS**

### **Sistema de Auditoría**
- **Endpoint:** `GET /admin/audit-logs`
- **Filtros:**
  - Por usuario
  - Por acción
  - Por fecha
  - Por módulo del sistema

### **Eventos Auditados**
- **Usuarios:** Creación, modificación, eliminación
- **Promociones:** Solicitudes, aprobaciones, rechazos
- **Ejercicios:** CRUD completo
- **Plantillas:** Creación, modificación, asignación
- **Configuración:** Cambios en settings
- **Autenticación:** Logins, logouts, fallos

### **Información de Logs**
- **Usuario:** Quién realizó la acción
- **Acción:** Qué se hizo
- **Recurso:** Sobre qué se actuó
- **Timestamp:** Cuándo ocurrió
- **IP:** Desde dónde se realizó
- **Detalles:** Información adicional relevante

---

## 🔄 **INTEGRACIÓN CON APP MÓVIL**

### **Sincronización Bidireccional**

#### **Panel → App Móvil**
- **Nuevos ejercicios** creados en panel aparecen en móvil
- **Plantillas asignadas** se reflejan inmediatamente
- **Cambios de usuario** actualizan permisos en móvil
- **Configuraciones** afectan comportamiento de la app

#### **App Móvil → Panel**
- **Solicitudes de promoción** aparecen en panel
- **Actividad de usuarios** se refleja en estadísticas
- **Progreso de entrenamientos** visible en reportes
- **Datos de uso** alimentan métricas del dashboard

### **Monitoreo en Tiempo Real**
- **Usuarios activos:** Cuántos están usando la app
- **Solicitudes pendientes:** Notificaciones en tiempo real
- **Errores de API:** Monitoreo de fallos de conectividad
- **Métricas de uso:** Funcionalidades más utilizadas

---

## 📊 **REPORTES Y ESTADÍSTICAS**

### **Dashboard Principal**
- **Usuarios totales** y crecimiento
- **Promociones** pendientes y procesadas
- **Ejercicios** más utilizados
- **Plantillas** más asignadas
- **Actividad** del sistema en tiempo real

### **Reportes Detallados**

#### **Reporte de Usuarios**
- **Crecimiento:** Nuevos usuarios por período
- **Actividad:** Usuarios activos vs inactivos
- **Distribución:** Por tipo, rol, estado
- **Engagement:** Frecuencia de uso de la app

#### **Reporte de Entrenamientos**
- **Adherencia:** Porcentaje de completitud
- **Ejercicios populares:** Más utilizados
- **Plantillas efectivas:** Mejor adherencia
- **Progreso:** Mejoras en rendimiento

#### **Reporte de Promociones**
- **Conversión:** Tasa de local a API
- **Tiempo de procesamiento:** Eficiencia administrativa
- **Razones de rechazo:** Análisis de patrones
- **Impacto:** Cambios post-promoción

---

## ⚠️ **MANEJO DE ERRORES**

### **Errores Específicos del Panel**

#### **403 - Permisos Insuficientes**
- **Causa:** Usuario sin permisos para la acción
- **Acción:** Mostrar mensaje específico, ocultar funcionalidad
- **Ejemplo:** Profesor intentando acceder a gestión de usuarios

#### **422 - Validación Fallida**
- **Causa:** Datos incorrectos en formularios
- **Acción:** Mostrar errores específicos por campo
- **Ejemplo:** Crear ejercicio sin nombre requerido

#### **500 - Error de API Externa**
- **Causa:** Fallo en conexión con sistema del club
- **Acción:** Mostrar mensaje de servicio temporalmente no disponible
- **Funcionalidad:** Continuar con funciones que no requieren API externa

### **Estados de Carga**
- **Loading:** Durante requests largos
- **Empty State:** Cuando no hay datos
- **Error State:** Cuando falla la carga
- **Retry:** Opción de reintentar operaciones fallidas

---

## 🎯 **CASOS DE USO PRINCIPALES**

### **Caso 1: Admin Gestiona Promoción**
1. **Recibe notificación** de nueva solicitud
2. **Accede** al panel de promociones
3. **Revisa** detalles del solicitante
4. **Verifica** información en sistema del club
5. **Aprueba** o rechaza con comentarios
6. **Usuario** recibe notificación automática

### **Caso 2: Profesor Crea Rutina**
1. **Accede** al panel gimnasio
2. **Crea** nuevos ejercicios si es necesario
3. **Diseña** plantilla diaria con wizard
4. **Asigna** plantilla a estudiantes específicos
5. **Estudiantes** ven nueva rutina en móvil
6. **Monitorea** adherencia y progreso

### **Caso 3: Super Admin Configura Sistema**
1. **Accede** a configuración avanzada
2. **Modifica** parámetros del sistema
3. **Configura** integración con API externa
4. **Establece** políticas de seguridad
5. **Cambios** se aplican inmediatamente
6. **Auditoría** registra todas las modificaciones

---

## 🔧 **CONSIDERACIONES TÉCNICAS**

### **Rendimiento**
- **Paginación:** Todas las listas grandes
- **Filtros:** Búsqueda en tiempo real con debounce
- **Cache:** React Query para optimización
- **Lazy Loading:** Componentes y datos bajo demanda

### **UX/UI Específicas**
- **Breadcrumbs:** Navegación clara en secciones profundas
- **Modales:** Para acciones críticas (eliminar, aprobar)
- **Tooltips:** Explicaciones de funcionalidades complejas
- **Shortcuts:** Atajos de teclado para acciones frecuentes
- **Bulk Actions:** Selección múltiple para operaciones masivas

### **Seguridad**
- **Confirmación:** Acciones críticas requieren confirmación
- **Timeout:** Sesiones administrativas con tiempo límite
- **Logs:** Todas las acciones administrativas auditadas
- **Permisos:** Verificación en cada acción

---

## 📞 **SOPORTE ADMINISTRATIVO**

### **Problemas Comunes**

#### **"No puedo ver cierta funcionalidad"**
- Verificar permisos del usuario
- Confirmar rol asignado correctamente
- Revisar configuración de la cuenta

#### **"Los cambios no se reflejan en móvil"**
- Verificar conectividad del servidor
- Confirmar que la acción se completó exitosamente
- Revisar logs de auditoría para confirmar cambio

#### **"Error al procesar promoción"**
- Verificar conectividad con API del club
- Confirmar que el DNI existe en sistema externo
- Revisar logs de error para detalles específicos

---

## 🎯 **RESUMEN EJECUTIVO**

El Panel Admin Villa Mitre es una plataforma web completa que permite la gestión integral del ecosistema del gimnasio.

**Características Clave:**
- ✅ **Gestión completa de usuarios** con filtros avanzados
- ✅ **Sistema de promociones** automatizado y eficiente
- ✅ **Panel gimnasio** para profesores con CRUD completo
- ✅ **Auditoría automática** de todas las acciones
- ✅ **Reportes avanzados** y métricas en tiempo real
- ✅ **Integración perfecta** con app móvil
- ✅ **Configuración flexible** del sistema

**Para el Desarrollador Frontend:**
Este manual proporciona toda la información necesaria para implementar un panel administrativo robusto, escalable y fácil de usar, con integración perfecta al ecosistema Villa Mitre.

---

*Documento actualizado: 2025-09-24*  
*Versión del Sistema: 100% Funcional*  
*Estado de Integración: Perfección Absoluta*
