# 📚 Índice de Documentación - Villa Mitre Server

## 📁 Estructura Organizada

### 🚀 **Inicio Rápido**
```
getting-started/
├── local-setup.md          # Configuración desarrollo local
└── environment.md          # Variables de entorno
```

### 🏋️ **Servicio de Gimnasios** (Nuevo)
```
gym/
├── TECHNICAL-ARCHITECTURE.md    # Arquitectura técnica interna
├── INTERNAL-FLOWS.md            # Flujos detallados del sistema
├── MOBILE-API-GUIDE.md          # Guía para desarrolladores móvil
├── ADMIN-PANEL-GUIDE.md         # Guía para profesores
├── DOMAIN-DESIGN.md             # Diseño de dominio
└── API-SPEC.md                  # Especificación endpoints
```

### 🌐 **Deployment**
```
deployment/
├── apache-deploy.md                              # Guía completa Apache
├── DEPLOYMENT-CHECKLIST.md                      # Lista verificación
├── GUÍA COMPLETA DE DEPLOYMENT EN PRODUCCIÓN.md # Deployment completo
├── DEVOPS-MYSQL-FIX.md                         # Fixes DevOps
├── PRODUCTION-DEPLOYMENT-GUIDE.md               # Guía producción
└── deploy.md                                    # Deploy básico
```

### 🔌 **API & Integración**
```
api/
└── mobile-contracts.md     # Contratos API móvil
```

### 💻 **Desarrollo**
```
development/
├── frontend-changes.md       # Cambios frontend
├── frontend-compatibility.md # Compatibilidad
├── debugging.md             # Herramientas debug
└── FIX-AUTH-CRASH.md        # Fix crash autenticación
```

### 🗄️ **Base de Datos**
```
database/
└── USERS-DATA-DICTIONARY.md  # Diccionario tabla users
```

### 🧪 **Testing**
```
testing/
└── testing-guide.md         # Guías de testing
```

### 🏗️ **Instalación**
```
installation/
└── requirements.md          # Requisitos sistema
```

### 📦 **Archivos Raíz**
```
docs/
├── README.md               # Índice principal
├── INDEX.md               # Este archivo (índice detallado)
└── CHANGELOG.md           # Historial de cambios
```

## 🎯 **Guías por Audiencia**

### 👨‍💻 **Desarrolladores Backend**
1. [Arquitectura Técnica](gym/TECHNICAL-ARCHITECTURE.md)
2. [Flujos Internos](gym/INTERNAL-FLOWS.md)
3. [Testing Guide](testing/testing-guide.md)
4. [Debugging](development/debugging.md)

### 📱 **Desarrolladores App Móvil**
1. [API Móvil Gimnasios](gym/MOBILE-API-GUIDE.md)
2. [Contratos API](api/mobile-contracts.md)
3. [Autenticación](api/authentication.md)

### 👨‍🏫 **Profesores (Usuarios Finales)**
1. [Panel Admin Gimnasios](gym/ADMIN-PANEL-GUIDE.md)

### 🚀 **DevOps/Deployment**
1. [Deploy Apache](deployment/apache-deploy.md)
2. [Checklist Deploy](deployment/DEPLOYMENT-CHECKLIST.md)
3. [DevOps MySQL Fix](deployment/DEVOPS-MYSQL-FIX.md)

### 🔧 **Setup Inicial**
1. [Setup Local](getting-started/local-setup.md)
2. [Variables Entorno](getting-started/environment.md)
3. [Requisitos](installation/requirements.md)

## 📊 **Estado de Documentación**

| Categoría | Archivos | Estado | Última Actualización |
|-----------|----------|--------|---------------------|
| 🏋️ Gimnasios | 6 | ✅ Completo | 2025-09-18 |
| 🌐 Deployment | 6 | ✅ Completo | 2025-09-10 |
| 💻 Desarrollo | 4 | ✅ Completo | 2025-09-08 |
| 🗄️ Base Datos | 1 | ✅ Completo | 2025-09-18 |
| 🔌 API | 1 | ⚠️ Parcial | 2025-09-08 |
| 🧪 Testing | 1 | ✅ Completo | 2025-09-18 |

## 🔍 **Búsqueda Rápida**

### Por Funcionalidad
- **Autenticación**: [API Auth](api/authentication.md), [Fix Crash](development/FIX-AUTH-CRASH.md)
- **Gimnasios**: [Todo en gym/](gym/)
- **Deployment**: [Todo en deployment/](deployment/)
- **Testing**: [Testing Guide](testing/testing-guide.md)
- **Base Datos**: [Users Dictionary](database/USERS-DATA-DICTIONARY.md)

### Por Tipo de Problema
- **Setup inicial**: [Local Setup](getting-started/local-setup.md)
- **Error deployment**: [DevOps Fix](deployment/DEVOPS-MYSQL-FIX.md)
- **Error auth**: [Auth Crash Fix](development/FIX-AUTH-CRASH.md)
- **Compatibilidad**: [Frontend Compatibility](development/frontend-compatibility.md)

---
**Documentación actualizada:** 2025-09-18  
**Total archivos:** 20+ documentos organizados  
**Estado:** ✅ Completo y organizado
