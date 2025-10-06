# 🏛️ Villa Mitre Server

**Sistema de gestión integral para Club Villa Mitre con panel de administración y funcionalidades de gimnasio.**

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
<img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-8.0+-orange?style=for-the-badge&logo=mysql" alt="MySQL">
<img src="https://img.shields.io/badge/Tests-Passing-green?style=for-the-badge" alt="Tests">
</p>

## 🎯 **Descripción del Proyecto**

Villa Mitre Server es una API REST robusta desarrollada en Laravel que proporciona:

- **🔐 Sistema de autenticación dual** (usuarios locales + API externa)
- **👨‍💼 Panel de administración completo** con gestión de usuarios y profesores
- **🏋️ Sistema de gimnasio** con plantillas, asignaciones y seguimiento
- **📱 API móvil** para estudiantes del gimnasio
- **📊 Sistema de auditoría** y reportes completos
- **🔒 Seguridad robusta** con roles y permisos granulares

## 🏗️ **Arquitectura del Sistema**

### **Servicios Modularizados por Dominio**
```
app/Services/
├── Auth/           🔐 Autenticación y registro
├── Admin/          👨‍💼 Gestión administrativa  
├── User/           👥 Gestión de usuarios
├── Gym/            🏋️ Funcionalidades del gimnasio
├── External/       🌐 Integración con APIs externas
└── Core/           ⚙️ Servicios centrales (Cache, Audit)
```

### **Controladores Ligeros**
- **Responsabilidad única**: Solo validación y coordinación
- **Lógica delegada**: Servicios especializados manejan la lógica de negocio
- **APIs consistentes**: Respuestas estandarizadas y manejo de errores

## 🚀 **Instalación y Configuración**

### **Requisitos del Sistema**
- PHP 8.2+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (para assets)

### **Instalación**
```bash
# Clonar el repositorio
git clone <repository-url>
cd vmServer

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos
php artisan migrate:fresh --seed

# Crear usuario administrador
php create_admin_user.php

# Iniciar servidor
php artisan serve
```

### **Configuración de Entorno**
```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=villa_mitre
DB_USERNAME=root
DB_PASSWORD=

# API Externa de Socios
SOCIOS_API_BASE_URL=https://api.socios.com
SOCIOS_API_LOGIN=usuario
SOCIOS_API_PASSWORD=password

# Cache y Sesiones
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
```

## 📚 **Documentación**

### **Guías Principales**
- 📖 [**API Documentation**](docs/api/API-DOCUMENTATION.md) - Endpoints y ejemplos
- 🏗️ [**Services Architecture**](docs/architecture/SERVICES-ARCHITECTURE.md) - Arquitectura de servicios
- 🧪 [**Testing Guide**](docs/testing/TESTING-GUIDE-MAIN.md) - Guía de testing
- 👨‍💼 [**Admin Panel Guide**](docs/admin-panel/) - Panel de administración
- 🔐 [**User Credentials**](docs/USER-CREDENTIALS.md) - Usuarios de prueba y acceso

### **Documentación Técnica**
- [CRUD Implementation Guide](docs/admin-panel/CRUD-IMPLEMENTATION-GUIDE.md)
- [UI Components Guide](docs/admin-panel/UI-COMPONENTS-GUIDE.md)
- [API Integration Guide](docs/admin-panel/API-INTEGRATION-GUIDE.md)

### **Documentación del Gimnasio**
- 🏋️ [**Gym Documentation**](docs/gym/GYM-DOCUMENTATION.md) - Sistema completo del gimnasio
- 📋 [**Gym Business Rules**](docs/gym/GYM-BUSINESS-RULES.md) - Reglas de negocio y validaciones
- 🎨 [**Gym Panel Specs**](docs/admin-panel/GYM-PANEL-SPECS.md) - Especificaciones de UI

## 🔐 **Autenticación y Seguridad**

### **Tipos de Usuario**
- **👤 Usuario Local**: Registrado directamente en el sistema
- **🏛️ Usuario API**: Sincronizado desde sistema externo del club
- **👨‍🏫 Profesor**: Acceso al panel del gimnasio
- **👨‍💼 Administrador**: Acceso completo al sistema

### **Endpoints Principales**
```bash
# Autenticación
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout
GET  /api/auth/me

# Panel de Administración
GET  /api/admin/users
GET  /api/admin/professors
GET  /api/admin/audit

# Panel del Gimnasio
GET  /api/admin/gym/exercises
POST /api/admin/gym/weekly-assignments

# API Móvil
GET  /api/gym/my-week
GET  /api/gym/my-day
```

## 🧪 **Testing**

### **Ejecutar Tests**
```bash
# Todos los tests
php artisan test

# Tests unitarios
php artisan test --testsuite=Unit

# Tests de integración
php artisan test --testsuite=Feature

# Con cobertura
php artisan test --coverage
```

### **Tests Críticos**
- ✅ **AuthenticationService**: Manejo seguro de credenciales
- ✅ **PasswordValidationService**: Prevención de crashes críticos
- ✅ **UserManagementService**: Gestión de roles y permisos
- ✅ **SocioDataMappingService**: Validación de datos externos

## 📊 **Características Principales**

### **🔐 Sistema de Autenticación Dual**
- Usuarios locales con registro manual
- Integración automática con API externa del club
- Promoción automática de usuarios locales a API
- Validación robusta con manejo de errores críticos

### **👨‍💼 Panel de Administración**
- Gestión completa de usuarios con filtros avanzados
- Asignación de roles de profesor con calificaciones
- Sistema de permisos granular
- Logs de auditoría completos con exportación

### **🏋️ Sistema de Gimnasio**
- Creación de plantillas de ejercicios
- Asignaciones semanales personalizadas
- Seguimiento de progreso de estudiantes
- API móvil para acceso desde aplicaciones

### **📱 API Móvil**
- Endpoints optimizados para aplicaciones móviles
- Rutinas diarias y semanales
- Seguimiento de ejercicios y series
- Sincronización en tiempo real

## 🛠️ **Desarrollo**

### **Estructura del Proyecto**
```
vmServer/
├── app/
│   ├── Http/Controllers/     # Controladores ligeros
│   ├── Services/            # Lógica de negocio modularizada
│   ├── Models/              # Modelos Eloquent
│   └── Enums/               # Enumeraciones tipadas
├── database/
│   ├── migrations/          # Migraciones de BD
│   └── factories/           # Factories para testing
├── tests/
│   ├── Unit/               # Tests unitarios
│   └── Feature/            # Tests de integración
└── docs/                   # Documentación completa
```

### **Comandos Útiles**
```bash
# Refrescar base de datos
php artisan migrate:fresh --seed

# Crear usuario administrador
php create_admin_user.php

# Limpiar cache
php artisan cache:clear
php artisan config:clear

# Generar documentación API
php artisan route:list --json > docs/routes.json
```

## 🔧 **Troubleshooting**

### **Problemas Comunes**
- **Error 500 en login**: Verificar configuración de base de datos
- **Token inválido**: Regenerar clave de aplicación con `php artisan key:generate`
- **API externa no responde**: Verificar credenciales en `.env`

### **Logs y Debugging**
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log

# Modo debug
APP_DEBUG=true
```

## 📈 **Performance y Monitoreo**

### **Optimizaciones Implementadas**
- ✅ **Cache de usuarios** con Redis
- ✅ **Circuit breaker** para APIs externas
- ✅ **Queries optimizadas** con eager loading
- ✅ **Paginación eficiente** en listados

### **Métricas de Performance**
- **Response time**: < 200ms para endpoints críticos
- **Database queries**: < 5 queries por request
- **Memory usage**: < 128MB por request
- **Cache hit rate**: > 80% para datos de usuarios

## 🤝 **Contribución**

### **Guías de Desarrollo**
1. **Fork** del repositorio
2. **Crear branch** para nueva funcionalidad
3. **Escribir tests** para nuevos cambios
4. **Ejecutar suite completa** de tests
5. **Crear Pull Request** con descripción detallada

### **Estándares de Código**
- **PSR-12** para estilo de código PHP
- **Tests obligatorios** para nuevas funcionalidades
- **Documentación actualizada** para cambios de API
- **Commits descriptivos** siguiendo conventional commits

## 📞 **Soporte**

Para reportar bugs o solicitar funcionalidades:
- 🐛 **Issues**: Usar el sistema de issues del repositorio
- 📧 **Email**: contacto@villamitre.com
- 📱 **Urgencias**: Solo para problemas críticos de producción

---

**Villa Mitre Server - Sistema de gestión integral desarrollado con Laravel** 🏛️
