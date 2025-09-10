# 📚 DOCUMENTACIÓN DEL PROYECTO VILLA MITRE SERVER

## 🚀 **INICIO RÁPIDO**

### **🔧 Configuración Local**
- [📋 Instalación Local](getting-started/local-setup.md) - Setup completo desarrollo
- [🐳 Docker Setup](deployment/docker-setup.md) - Configuración con Docker
- [⚙️ Variables de Entorno](getting-started/environment.md) - Configuración .env

### **🌐 Deployment**
- [🚀 Deploy Apache](deployment/deploy.md) - Deployment producción
- [📋 Checklist Deploy](deployment/DEPLOYMENT-CHECKLIST.md) - Lista verificación
- [🔄 Migración Docker→Apache](deployment/apache-deploy.md) - Guía migración

## 📖 **DOCUMENTACIÓN TÉCNICA**

### **🔌 API & Integración**
- [📱 Contratos Mobile](api/mobile-contracts.md) - Endpoints y contratos API
- [🔗 API Externa](api/external-integration.md) - Integración Club Villa Mitre
- [🔐 Autenticación](api/authentication.md) - Sistema auth y tokens

### **💻 Desarrollo**
- [🎨 Frontend Changes](development/frontend-changes.md) - Cambios requeridos frontend
- [🔧 Compatibilidad](development/frontend-compatibility.md) - Guía compatibilidad
- [🧪 Testing Guide](development/testing-guide.md) - Guías de testing
- [🐛 Debugging](development/debugging.md) - Herramientas debug

### **🏗️ Arquitectura**
- [📊 Diseño Base Datos](architecture/database-design.md) - Estructura BD
- [⚡ Servicios](architecture/services-overview.md) - Arquitectura servicios
- [🔄 APIs Externas](architecture/external-apis.md) - Integración APIs

## 📋 **ESTRUCTURA DEL PROYECTO**

```
vmServer/
├── app/
│   ├── Http/Controllers/     # Controladores API
│   ├── Services/            # Lógica de negocio
│   ├── Models/              # Modelos Eloquent
│   ├── DTOs/                # Objetos transferencia datos
│   └── Contracts/           # Interfaces
├── database/
│   ├── migrations/          # Migraciones BD
│   └── seeders/            # Datos iniciales
├── docs/                   # Documentación (este directorio)
└── temp-cleanup/           # Archivos temporales
```

## 🎯 **FUNCIONALIDADES PRINCIPALES**

- ✅ **Autenticación unificada** - Local + API externa
- ✅ **Promoción automática** - Usuario local → API
- ✅ **Gestión imágenes** - URLs directas desde API
- ✅ **Cache inteligente** - Optimización rendimiento
- ✅ **Manejo errores** - Logging y recuperación
- ✅ **API RESTful** - Endpoints móviles completos

## 🔧 **COMANDOS ÚTILES**

```bash
# Desarrollo
php artisan serve
php artisan migrate
php artisan cache:clear

# Testing
php artisan test
php artisan tinker

# Debug
php artisan route:list
php artisan config:show
```

---
**Proyecto:** Villa Mitre Server API  
**Versión:** 2.0  
**Última actualización:** 2025-09-10  
**Estado:** ✅ Producción Ready
