# 📱 MANUAL COMPLETO - APP MÓVIL VILLA MITRE

## 🎯 **GUÍA PARA DESARROLLO FRONTEND**

Este manual proporciona toda la información necesaria para desarrollar la aplicación móvil del gimnasio Villa Mitre, incluyendo endpoints, flujos de datos, autenticación y funcionalidades principales.

---

## 📋 **ÍNDICE**

1. [Información General](#información-general)
2. [Autenticación y Seguridad](#autenticación-y-seguridad)
3. [Tipos de Usuarios](#tipos-de-usuarios)
4. [Endpoints Principales](#endpoints-principales)
5. [Flujos de Datos](#flujos-de-datos)
6. [Estados y Validaciones](#estados-y-validaciones)
7. [Manejo de Errores](#manejo-de-errores)
8. [Funcionalidades por Rol](#funcionalidades-por-rol)
9. [Integración con Panel Admin](#integración-con-panel-admin)
10. [Casos de Uso Principales](#casos-de-uso-principales)

---

## 🏢 **INFORMACIÓN GENERAL**

### **Arquitectura del Sistema**
- **Backend:** Laravel API REST
- **Base URL:** `http://127.0.0.1:8000/api`
- **Autenticación:** Laravel Sanctum (Bearer Token)
- **Formato de Respuesta:** JSON
- **Códigos de Estado:** HTTP estándar (200, 201, 401, 422, 500)

### **Características Principales**
- Sistema dual de usuarios (Local + API Externa)
- Gestión de entrenamientos personalizados
- Sistema de promociones automático
- Sincronización en tiempo real con panel administrativo
- Manejo resiliente de APIs externas

---

## 🔐 **AUTENTICACIÓN Y SEGURIDAD**

### **Flujo de Autenticación**

#### **1. Login de Usuario**
- **Endpoint:** `POST /auth/login`
- **Datos requeridos:** `dni`, `password`
- **Respuesta exitosa:** Token de acceso + datos del usuario
- **Validaciones:** DNI debe existir, contraseña correcta

#### **2. Registro de Usuario**
- **Endpoint:** `POST /auth/register`
- **Datos requeridos:** `name`, `email`, `dni`, `password`, `password_confirmation`
- **Validaciones de contraseña:**
  - Mínimo 8 caracteres
  - Al menos 1 mayúscula
  - Al menos 1 minúscula
  - Al menos 1 número
  - Al menos 1 símbolo especial

#### **3. Obtener Perfil**
- **Endpoint:** `GET /auth/me`
- **Requiere:** Token de autenticación
- **Respuesta:** Datos completos del usuario autenticado

#### **4. Logout**
- **Endpoint:** `POST /auth/logout`
- **Requiere:** Token de autenticación
- **Acción:** Invalida el token actual

### **Manejo de Tokens**
- **Formato:** Bearer Token en header `Authorization`
- **Duración:** Configurable (por defecto persistente)
- **Renovación:** Automática en cada request exitoso
- **Invalidación:** Manual via logout o automática por seguridad

---

## 👥 **TIPOS DE USUARIOS**

### **1. Usuario Local (`user_type: "local"`)**
- Creado directamente en el sistema
- Datos almacenados en base de datos local
- Acceso completo a funcionalidades básicas

### **2. Usuario API (`user_type: "api"`)**
- Sincronizado con sistema externo del club
- Datos adicionales del club (saldo, estado socio, etc.)
- Funcionalidades extendidas y promociones

### **Roles Disponibles**
- **Estudiante:** Usuario básico con acceso a entrenamientos
- **Profesor:** Gestión de ejercicios y plantillas (requiere `is_professor: true`)
- **Admin:** Acceso completo al sistema (requiere `is_admin: true`)

### **Estados de Promoción**
- `pending`: Solicitud en proceso
- `approved`: Usuario promovido a API
- `rejected`: Solicitud rechazada
- `null`: Sin solicitud de promoción

---

## 🔗 **ENDPOINTS PRINCIPALES**

### **📱 MÓVIL - ENTRENAMIENTOS**

#### **Ver Mi Semana**
- **Endpoint:** `GET /gym/my-week`
- **Descripción:** Obtiene el plan de entrenamiento semanal del usuario
- **Respuesta:** Array de días con entrenamientos asignados
- **Datos incluidos:** Ejercicios, sets, repeticiones, descansos

#### **Ver Mi Día**
- **Endpoint:** `GET /gym/my-day`
- **Descripción:** Obtiene el entrenamiento del día actual
- **Respuesta:** Detalle completo del entrenamiento diario
- **Datos incluidos:** Ejercicios específicos, progresión, notas

#### **Ver Día Específico**
- **Endpoint:** `GET /gym/my-day/{date}`
- **Parámetro:** Fecha en formato YYYY-MM-DD
- **Descripción:** Obtiene entrenamiento de una fecha específica

### **🎯 PROMOCIONES**

#### **Verificar Elegibilidad**
- **Endpoint:** `GET /promotion/eligibility`
- **Descripción:** Verifica si el usuario puede solicitar promoción
- **Respuesta:** Estado de elegibilidad y requisitos

#### **Verificar DNI en Club**
- **Endpoint:** `POST /promotion/check-dni`
- **Datos:** `dni`
- **Descripción:** Verifica si el DNI existe en el sistema del club
- **Nota:** Puede fallar por conectividad externa (manejable)

#### **Solicitar Promoción**
- **Endpoint:** `POST /promotion/request`
- **Datos requeridos:** `reason`, `additional_info`, `club_password`
- **Descripción:** Envía solicitud de promoción a usuario API
- **Nota:** Requiere verificación previa de DNI

### **👤 GESTIÓN DE PERFIL**

#### **Ver Perfil Completo**
- **Endpoint:** `GET /auth/me`
- **Descripción:** Datos completos del usuario autenticado
- **Incluye:** Permisos, estado de promoción, datos del club (si aplica)

#### **Actualizar Perfil**
- **Endpoint:** `PUT /users/{id}`
- **Datos:** Campos editables del perfil
- **Restricciones:** Solo datos propios, campos limitados por rol

---

## 🔄 **FLUJOS DE DATOS**

### **Flujo de Login**
1. Usuario ingresa DNI y contraseña
2. App envía credenciales a `/auth/login`
3. Sistema valida y puede consultar API externa
4. Respuesta incluye token + datos de usuario
5. App almacena token para requests posteriores

### **Flujo de Entrenamiento Diario**
1. App solicita entrenamiento del día (`/gym/my-day`)
2. Sistema consulta asignaciones del usuario
3. Respuesta incluye ejercicios, sets, y progresión
4. Usuario completa ejercicios (tracking local)
5. App puede enviar progreso al sistema

### **Flujo de Promoción**
1. Usuario verifica elegibilidad (`/promotion/eligibility`)
2. Si es elegible, verifica DNI (`/promotion/check-dni`)
3. Si DNI es válido, solicita promoción (`/promotion/request`)
4. Sistema procesa solicitud con API externa
5. Admin revisa y aprueba/rechaza desde panel
6. Usuario recibe notificación de cambio de estado

### **Flujo de Sincronización**
1. App hace requests periódicos para datos actualizados
2. Sistema verifica cambios desde panel admin
3. Datos se actualizan automáticamente
4. Usuario ve cambios sin necesidad de logout/login

---

## ✅ **ESTADOS Y VALIDACIONES**

### **Estados de Usuario**
- **account_status:** `active`, `inactive`, `suspended`
- **promotion_status:** `pending`, `approved`, `rejected`, `null`
- **user_type:** `local`, `api`

### **Validaciones Importantes**

#### **Registro**
- DNI único en el sistema
- Email válido y único
- Contraseña con requisitos de seguridad
- Confirmación de contraseña coincidente

#### **Login**
- DNI debe existir
- Contraseña correcta
- Cuenta debe estar activa
- No debe estar suspendida

#### **Promoción**
- Usuario debe ser tipo `local`
- No debe tener solicitud pendiente
- DNI debe existir en sistema del club
- Debe proporcionar contraseña del club

### **Campos Obligatorios por Pantalla**

#### **Pantalla de Login**
- `dni` (requerido, numérico)
- `password` (requerido, mínimo 8 caracteres)

#### **Pantalla de Registro**
- `name` (requerido, máximo 255 caracteres)
- `email` (requerido, formato email válido)
- `dni` (requerido, numérico, único)
- `password` (requerido, cumplir política de seguridad)
- `password_confirmation` (requerido, debe coincidir)

#### **Pantalla de Promoción**
- `reason` (requerido, máximo 500 caracteres)
- `additional_info` (opcional, máximo 1000 caracteres)
- `club_password` (requerido, proporcionado por el club)

---

## ⚠️ **MANEJO DE ERRORES**

### **Códigos de Estado HTTP**

#### **200 - OK**
- Request exitoso
- Datos válidos en respuesta

#### **201 - Created**
- Recurso creado exitosamente
- Usado en registro, solicitudes, etc.

#### **401 - Unauthorized**
- Token inválido o expirado
- Credenciales incorrectas
- **Acción:** Redirigir a login

#### **403 - Forbidden**
- Usuario sin permisos suficientes
- Acceso denegado a recurso
- **Acción:** Mostrar mensaje de acceso denegado

#### **422 - Unprocessable Entity**
- Errores de validación
- Datos incorrectos o faltantes
- **Respuesta incluye:** Array de errores específicos

#### **500 - Internal Server Error**
- Error del servidor
- Problemas de conectividad externa
- **Acción:** Mostrar mensaje genérico, reintentar

### **Manejo de APIs Externas**
- **Timeout:** 10 segundos máximo
- **Reintentos:** Hasta 3 intentos automáticos
- **Fallback:** Funcionalidad degradada si API externa falla
- **Notificación:** Informar al usuario sobre limitaciones temporales

### **Mensajes de Error Comunes**
- "Credenciales incorrectas"
- "Tu sesión ha expirado, por favor inicia sesión nuevamente"
- "No tienes permisos para realizar esta acción"
- "Error de conexión, verifica tu internet"
- "Servicio temporalmente no disponible"

---

## 🎭 **FUNCIONALIDADES POR ROL**

### **👨‍🎓 ESTUDIANTE**

#### **Entrenamientos**
- ✅ Ver mi semana de entrenamiento
- ✅ Ver mi día de entrenamiento
- ✅ Ver entrenamientos de fechas específicas
- ✅ Seguir progresión de ejercicios

#### **Promociones**
- ✅ Verificar elegibilidad para promoción
- ✅ Solicitar promoción a usuario API
- ✅ Ver estado de solicitud de promoción

#### **Perfil**
- ✅ Ver mi perfil completo
- ✅ Actualizar datos básicos
- ✅ Ver historial de entrenamientos

### **👨‍🏫 PROFESOR**

#### **Gestión de Ejercicios**
- ✅ Crear nuevos ejercicios
- ✅ Editar ejercicios existentes
- ✅ Ver lista completa de ejercicios
- ✅ Duplicar ejercicios

#### **Plantillas de Entrenamiento**
- ✅ Crear plantillas diarias
- ✅ Crear plantillas semanales
- ✅ Asignar plantillas a estudiantes
- ✅ Ver reportes de adherencia

#### **Todas las funciones de Estudiante**

### **👨‍💼 ADMINISTRADOR**

#### **Gestión de Usuarios**
- ✅ Ver lista completa de usuarios
- ✅ Buscar y filtrar usuarios
- ✅ Cambiar tipo de usuario
- ✅ Ver estadísticas de usuarios

#### **Sistema de Promociones**
- ✅ Ver solicitudes pendientes
- ✅ Aprobar/rechazar promociones
- ✅ Ver historial completo
- ✅ Gestionar usuarios elegibles

#### **Configuración del Sistema**
- ✅ Configurar parámetros generales
- ✅ Ver logs de auditoría
- ✅ Generar reportes avanzados

#### **Todas las funciones anteriores**

---

## 🔄 **INTEGRACIÓN CON PANEL ADMIN**

### **Sincronización en Tiempo Real**

#### **Cambios que se Reflejan Inmediatamente**
- Nuevas plantillas de entrenamiento creadas por profesores
- Modificaciones en ejercicios existentes
- Cambios en asignaciones de entrenamientos
- Actualizaciones de estado de promoción
- Modificaciones en datos de usuario

#### **Flujo de Sincronización**
1. **Admin/Profesor hace cambio** en panel web
2. **Sistema actualiza** base de datos
3. **App móvil detecta** cambios en próximo request
4. **Datos se actualizan** automáticamente en la app
5. **Usuario ve cambios** sin necesidad de reiniciar

### **Datos Compartidos**
- **Ejercicios:** Creados en panel, visibles en móvil
- **Plantillas:** Diseñadas en panel, ejecutadas en móvil
- **Asignaciones:** Gestionadas en panel, seguidas en móvil
- **Usuarios:** Administrados en panel, autenticados en móvil
- **Promociones:** Solicitadas en móvil, gestionadas en panel

---

## 📋 **CASOS DE USO PRINCIPALES**

### **Caso 1: Usuario Nuevo se Registra**
1. **Descarga** la app
2. **Selecciona** "Crear cuenta"
3. **Completa** formulario de registro
4. **Recibe** confirmación y token
5. **Accede** a funcionalidades básicas
6. **Puede solicitar** promoción posteriormente

### **Caso 2: Usuario Existente Inicia Sesión**
1. **Abre** la app
2. **Ingresa** DNI y contraseña
3. **Sistema verifica** credenciales
4. **Recibe** token y datos actualizados
5. **Accede** a su panel personalizado

### **Caso 3: Estudiante Ve su Entrenamiento**
1. **Inicia sesión** en la app
2. **Navega** a "Mi Entrenamiento"
3. **Ve** plan semanal completo
4. **Selecciona** día específico
5. **Sigue** rutina detallada
6. **Marca** ejercicios completados

### **Caso 4: Usuario Solicita Promoción**
1. **Verifica** elegibilidad en la app
2. **Confirma** que cumple requisitos
3. **Verifica** DNI en sistema del club
4. **Completa** formulario de solicitud
5. **Envía** solicitud al sistema
6. **Espera** aprobación del administrador
7. **Recibe** notificación de cambio de estado

### **Caso 5: Profesor Crea Nuevo Ejercicio**
1. **Accede** al panel de profesor
2. **Selecciona** "Crear Ejercicio"
3. **Completa** detalles del ejercicio
4. **Guarda** en el sistema
5. **Ejercicio** aparece automáticamente en app móvil
6. **Estudiantes** pueden verlo en sus rutinas

### **Caso 6: Admin Gestiona Promoción**
1. **Recibe** notificación de nueva solicitud
2. **Revisa** detalles en panel admin
3. **Verifica** información del usuario
4. **Aprueba** o rechaza solicitud
5. **Usuario** recibe notificación en móvil
6. **Cambios** se reflejan inmediatamente

---

## 🔧 **CONSIDERACIONES TÉCNICAS**

### **Manejo de Conectividad**
- **Modo Offline:** Funcionalidad básica disponible
- **Sincronización:** Automática al recuperar conexión
- **Cache Local:** Datos críticos almacenados localmente
- **Indicadores:** Estado de conexión visible para usuario

### **Rendimiento**
- **Paginación:** Listas grandes divididas en páginas
- **Cache:** Datos frecuentes almacenados temporalmente
- **Lazy Loading:** Carga de imágenes y datos bajo demanda
- **Optimización:** Requests mínimos necesarios

### **Seguridad**
- **HTTPS:** Todas las comunicaciones encriptadas
- **Tokens:** Almacenamiento seguro en dispositivo
- **Validación:** Datos validados en cliente y servidor
- **Timeout:** Sesiones con tiempo límite configurable

### **UX/UI Recomendaciones**
- **Loading States:** Indicadores durante requests
- **Error Handling:** Mensajes claros y accionables
- **Offline Indicators:** Estado de conectividad visible
- **Pull to Refresh:** Actualización manual de datos
- **Progressive Enhancement:** Funcionalidad básica siempre disponible

---

## 📞 **SOPORTE Y TROUBLESHOOTING**

### **Problemas Comunes**

#### **"No puedo iniciar sesión"**
- Verificar DNI y contraseña
- Confirmar conexión a internet
- Verificar que cuenta esté activa
- Contactar administrador si persiste

#### **"No veo mis entrenamientos"**
- Verificar que tenga asignaciones
- Confirmar que es la fecha correcta
- Actualizar datos (pull to refresh)
- Verificar permisos de usuario

#### **"Error al solicitar promoción"**
- Confirmar elegibilidad
- Verificar DNI en sistema del club
- Revisar contraseña del club
- Verificar conexión a internet

#### **"La app está lenta"**
- Verificar conexión a internet
- Cerrar y reabrir la app
- Limpiar cache si es posible
- Actualizar a última versión

### **Información de Contacto**
- **Soporte Técnico:** Administrador del sistema
- **Problemas de Cuenta:** Recepción del gimnasio
- **Solicitudes de Promoción:** Administración del club
- **Problemas de Entrenamientos:** Profesores asignados

---

## 🎯 **RESUMEN EJECUTIVO**

La app móvil Villa Mitre es una aplicación integral que conecta estudiantes, profesores y administradores en un ecosistema completo de gestión de entrenamientos. 

**Características Clave:**
- ✅ **Autenticación segura** con sistema dual local/API
- ✅ **Entrenamientos personalizados** sincronizados en tiempo real
- ✅ **Sistema de promociones** automatizado
- ✅ **Integración completa** con panel administrativo
- ✅ **Experiencia fluida** entre plataformas
- ✅ **Manejo resiliente** de conectividad

**Para el Desarrollador Frontend:**
Este manual proporciona toda la información necesaria para implementar una aplicación móvil robusta, escalable y fácil de usar, con integración perfecta al ecosistema Villa Mitre.

---

*Documento actualizado: 2025-09-24*  
*Versión del Sistema: 100% Funcional*  
*Estado de Integración: Perfección Absoluta*
