# HR System

> Sistema de gestión de recursos humanos desarrollado con Symfony y Vue.js
> siguiendo los principios de Domain-Driven Design (DDD)

## 📋 Tabla de Contenidos

- [🎯 Descripción del Proyecto](#-descripción-del-proyecto)
- [🚀 Tecnologías](#-tecnologías)
- [🏗️ Arquitectura](#️-arquitectura)
- [⚡ Inicio Rápido](#-inicio-rápido)
- [📦 Instalación](#-instalación)
- [🔧 Configuración](#-configuración)
- [💻 Desarrollo](#-desarrollo)
- [🌟 Funcionalidades](#-funcionalidades)
- [🔌 API](#-api)
- [🧪 Testing](#-testing)
- [📂 Estructura del Proyecto](#-estructura-del-proyecto)
- [🎨 Frontend](#-frontend)
- [🤖 Agentes IA](#-agentes-ia)
- [🗺️ Roadmap](#️-roadmap)
- [📚 Documentación Adicional](#-documentación-adicional)

## 🎯 Descripción del Proyecto

Este es un **Sistema de Gestión de Recursos Humanos** desarrollado como prueba
técnica, que implementa funcionalidades básicas para la administración de
empleados, nóminas y vacaciones. El proyecto está construido con tecnologías
modernas y sigue estrictamente los principios de **Domain-Driven Design (DDD)**.

### Características Principales

- ✅ **Gestión de Empleados** - CRUD completo con cálculos automáticos
- 🔄 **Arquitectura DDD** - Separación clara de capas y responsabilidades
- 🌐 **API REST** - Backend con API Platform y documentación automática
- 🎨 **Interfaz Moderna** - Frontend responsive con Vue.js y Tailwind CSS
- 🐳 **Dockerizado** - Entorno completo de desarrollo en contenedores
- 🧪 **Testing** - Cobertura de tests unitarios e integración

## 🚀 Tecnologías

### Backend

- **PHP 8.2+** - Lenguaje principal
- **Symfony 7.3** - Framework web
- **API Platform 4.1** - API REST automática
- **Doctrine ORM 3.5** - Mapeo objeto-relacional
- **PostgreSQL 15** - Base de datos
- **PHPUnit 11.5** - Testing

### Frontend

- **Vue.js 3.5** - Framework JavaScript reactivo
- **Vite 6.3** - Build tool y dev server
- **Tailwind CSS 4.1** - Framework de estilos utility-first
- **Cypress 13.7** - Testing end-to-end

### DevOps

- **Docker & Docker Compose** - Contenedorización
- **Node.js 18** - Runtime para frontend
- **Alpine Linux** - Imágenes base optimizadas

## 🏗️ Arquitectura

El proyecto implementa **Domain-Driven Design (DDD)** con separación estricta de
capas:

```
src/
├── Domain/                     # 🎯 Lógica de negocio pura
│   ├── Employee/              # Entidades y value objects
│   ├── Payroll/               # Dominio de nóminas
│   └── Vacation/              # Dominio de vacaciones
│
├── Application/               # 🔄 Casos de uso y servicios
│   ├── UseCase/              # CQRS: Commands y Queries
│   │   └── Employee/
│   │       ├── CreateEmployee/
│   │       └── GetEmployee/
│   ├── Service/              # Servicios de aplicación
│   └── DTO/                  # Data Transfer Objects
│
└── Infrastructure/           # 🔧 Detalles técnicos
    ├── ApiResource/         # Configuración API Platform
    ├── Controller/          # Controladores HTTP
    └── Doctrine/           # Persistencia y repositorios
```

### Patrones Implementados

- **CQRS** - Separación de comandos y consultas
- **Repository Pattern** - Abstracción de persistencia
- **DTO Pattern** - Transferencia de datos entre capas
- **Application Service** - Orquestación de casos de uso

## ⚡ Inicio Rápido

```bash
# Clonar el repositorio
git clone <repository-url>
cd hr-system

# Levantar todos los servicios
docker-compose up -d

# Ejecutar migraciones
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Acceder a la aplicación
# Frontend: http://localhost:5173
# Backend API: http://localhost:8000
# Documentación API: http://localhost:8000/api/docs
```

## 📦 Instalación

### Prerrequisitos

- Docker y Docker Compose instalados
- Git

### Pasos de Instalación

1. **Clonar el repositorio**

```bash
git clone <repository-url>
cd hr-system
```

2. **Configurar variables de entorno**

```bash
# El archivo .env ya está configurado para desarrollo
# Modificar si es necesario para producción
```

3. **Levantar los servicios**

```bash
docker-compose up -d
```

4. **Instalar dependencias del backend**

```bash
docker-compose exec app composer install
```

5. **Ejecutar migraciones**

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

6. **Instalar dependencias del frontend**

```bash
docker-compose exec node npm install
```

## 🔧 Configuración

### Servicios Docker

| Servicio   | Puerto | Descripción                    |
|------------|--------|--------------------------------|
| `app`      | 8000   | Backend Symfony + API Platform |
| `database` | 5432   | PostgreSQL 15                  |
| `node`     | 5173   | Frontend Vue.js + Vite         |
| `cypress`  | -      | Testing E2E (perfil testing)   |

### Base de Datos

```bash
# Crear nueva migración
docker-compose exec app php bin/console make:migration

# Ejecutar migraciones
docker-compose exec app php bin/console doctrine:migrations:migrate

# Verificar estado
docker-compose exec app php bin/console doctrine:migrations:status
```

## 💻 Desarrollo

### Backend (Symfony)

```bash
# Consola Symfony
docker-compose exec app php bin/console

# Limpiar cache
docker-compose exec app php bin/console cache:clear

# Crear entidad
docker-compose exec app php bin/console make:entity

# Ver rutas
docker-compose exec app php bin/console debug:router
```

### Frontend (Vue.js)

```bash
# Desarrollo con hot reload
docker-compose exec node npm run dev

# Build para producción
docker-compose exec node npm run build

# Preview del build
docker-compose exec node npm run preview
```

### Logs y Debugging

```bash
# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs específicos
docker-compose logs -f app
docker-compose logs -f node
docker-compose logs -f database
```

## 🌟 Funcionalidades

### ✅ Implementadas

#### 👥 Gestión de Empleados

- **Crear empleado** - Formulario completo con validación
- **Listar empleados** - Vista con paginación y filtros
- **Ver detalles** - Información completa del empleado
- **Cálculos automáticos**:
    - Años de servicio
    - Días de vacaciones anuales
    - Elegibilidad para vacaciones

#### 🔌 API REST

- **Endpoints completos** para empleados
- **Documentación automática** con API Platform
- **Validación** de datos de entrada
- **Respuestas estructuradas** con DTOs

#### 🎨 Interfaz de Usuario

- **Diseño responsive** con Tailwind CSS
- **Navegación intuitiva**
- **Componentes reutilizables**
- **Estados de carga y error**

### 🚧 En Desarrollo

#### 📊 Nóminas

- Cálculo de salarios
- Historial de pagos
- Reportes mensuales

#### 🏖️ Vacaciones

- Solicitudes de vacaciones
- Aprobación de vacaciones
- Calendario de ausencias

## 🔌 API

### Endpoints Disponibles

#### Empleados

```http
GET    /api/employees           # Listar empleados
POST   /api/employees           # Crear empleado
GET    /api/employees/{id}      # Obtener empleado
PUT    /api/employees/{id}      # Actualizar empleado
DELETE /api/employees/{id}      # Eliminar empleado
```

### Ejemplo de Uso

```bash
# Crear empleado
curl -X POST http://localhost:8000/api/employees \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "position": "Desarrollador",
    "salary": 50000,
    "hireDate": "2024-01-15"
  }'

# Obtener empleados
curl http://localhost:8000/api/employees
```

### Documentación Interactiva

Accede a la documentación completa de la API en:
**http://localhost:8000/api/docs**

## 🧪 Testing

### Backend Tests

```bash
# Ejecutar todos los tests (IMPORTANTE: usar APP_ENV=test)
docker-compose exec app env APP_ENV=test vendor/bin/phpunit

# Con log de resultados
docker-compose exec app env APP_ENV=test vendor/bin/phpunit --log-junit agents/logs/phpunit.log

# Tests específicos
docker-compose exec app env APP_ENV=test vendor/bin/phpunit tests/Domain/Employee/
docker-compose exec app env APP_ENV=test vendor/bin/phpunit tests/Api/

# Con cobertura
docker-compose exec app env APP_ENV=test vendor/bin/phpunit --coverage-html coverage/
```

> **⚠️ Importante**: Es necesario usar `env APP_ENV=test` antes del comando
> PHPUnit en Docker para evitar errores de configuración del framework de testing.
> Sin esto, los tests fallarán con el error "Could not find service '
> test.service_container'".

### Frontend Tests

```bash
# Tests end-to-end con Cypress
docker-compose --profile testing up cypress

# Modo interactivo (requiere X11)
docker-compose exec node npx cypress open
```

### Estado Actual de Tests

- ✅ **143/145 tests pasando** (99.3% éxito)
- ✅ **Tests unitarios** de dominio completos
- ✅ **Tests de API** funcionales
- ⚠️ **2 tests fallan** por conexión DB (esperado en CI)

## 📂 Estructura del Proyecto

```
hr-system/
├── 📁 backend/
│   ├── 📁 src/
│   │   ├── 📁 Domain/           # Lógica de negocio
│   │   ├── 📁 Application/      # Casos de uso
│   │   └── 📁 Infrastructure/   # Detalles técnicos
│   ├── 📁 tests/               # Tests PHP
│   ├── 📁 config/              # Configuración Symfony
│   └── 📄 composer.json        # Dependencias PHP
│
├── 📁 frontend/
│   ├── 📁 src/
│   │   ├── 📁 components/      # Componentes Vue
│   │   ├── 📄 App.vue          # Componente principal
│   │   └── 📄 main.js          # Punto de entrada
│   ├── 📁 cypress/             # Tests E2E
│   └── 📄 package.json         # Dependencias Node
│
├── 📄 docker-compose.yml       # Configuración Docker
├── 📄 Dockerfile              # Imagen de la aplicación
└── 📄 README.md               # Este archivo
```

## 🎨 Frontend

### Componentes Principales

- **App.vue** - Componente raíz con navegación
- **EmployeesList.vue** - Lista de empleados con funcionalidades CRUD
- **Counter.vue** - Componente de demostración

### Estilos y UI

- **Tailwind CSS 4.1** - Framework utility-first
- **Diseño responsive** - Mobile-first approach
- **Componentes modulares** - Reutilizables y mantenibles
- **Paleta de colores** - Consistente y profesional

### Estado y Reactivity

- **Composition API** - Vue 3 moderno
- **Estado local** - Gestión simple con ref/reactive
- **Comunicación HTTP** - Fetch API nativo

## 🤖 Agentes IA

### Descripción

El proyecto incluye un sistema de agentes IA especializados para tareas de
desarrollo y gestión de calidad. Cada agente tiene un rol específico y está
documentado con prompts detallados para garantizar consistencia y eficiencia en
el trabajo.

### Roles Disponibles

Los agentes están organizados en `agents/roles/` con los siguientes roles
especializados:

#### 🔧 **Backend Agent** (`backend_prompt.md`)

- Desarrollo y mantenimiento del backend Symfony
- Implementación de nuevas funcionalidades en la API
- Refactoring siguiendo principios DDD

#### 🎨 **Frontend Agent** (`frontend_prompt.md`)

- Desarrollo de componentes Vue.js
- Implementación de interfaces de usuario
- Integración con la API backend

#### 🔍 **QA Tester Agent** (`qa_tester_prompt.md`)

- Ejecución de suites de testing completas
- Gestión de status de tareas (PENDING → IN_PROGRESS → COMPLETED/REVISION)
- Validación de calidad antes de marcar tareas como completadas

#### 🐛 **Debugger Agent** (`debugger_prompt.md`)

- Diagnóstico y corrección de errores
- Análisis de logs y debugging
- Optimización de rendimiento

#### 🚀 **DevOps Agent** (`devops_prompt.md`)

- Gestión de contenedores Docker
- Configuración de entornos
- Automatización de despliegues

#### 🎯 **Coordinator Agent** (`coordinator_prompt.md`)

- Coordinación entre diferentes agentes
- Gestión de flujos de trabajo
- Asignación y seguimiento de tareas

### Estructura de Agentes

```
agents/
├── guideline.md              # Guía general para todos los agentes
├── logs/                     # Logs de ejecución de tests y tareas
│   ├── phpunit.log
│   ├── cypress_errors.log
│   └── README.md
├── roles/                    # Definición de roles especializados
│   ├── backend_prompt.md     # Agente de desarrollo backend
│   ├── coordinator_prompt.md # Agente coordinador
│   ├── debugger_prompt.md    # Agente de debugging
│   ├── devops_prompt.md      # Agente de DevOps
│   ├── frontend_prompt.md    # Agente de desarrollo frontend
│   └── qa_tester_prompt.md   # Agente de testing y QA
└── tasks/                    # Tareas asignadas a los agentes
    ├── task_048_phpunit_fix_jwt_performance.md
    ├── task_049_phpunit_fix_uuid_format_errors.md
    └── ... (más tareas)
```

### Gestión de Tareas

Los agentes siguen un sistema de gestión de tareas con estados claros:

- **PENDING**: Tarea disponible para ser tomada
- **IN_PROGRESS**: Tarea en desarrollo por un agente
- **COMPLETED**: Tarea completada sin errores
- **REVISION**: Tarea completada pero con errores detectados

### Documentación de Agentes

Para más detalles sobre el funcionamiento de los agentes, consulta:

- [📄 Guía General](agents/guideline.md) - Lineamientos para todos los agentes
- [📁 Roles](agents/roles/) - Documentación específica de cada rol

## 🗺️ Roadmap

### Fase 1 - Completada ✅

- [x] Configuración del entorno Docker
- [x] Backend Symfony con API Platform
- [x] Frontend Vue.js con Tailwind CSS
- [x] Gestión básica de empleados
- [x] Refactoring a arquitectura DDD
- [x] Tests unitarios y de integración

### Fase 2 - En Progreso 🚧

- [ ] Módulo de nóminas
- [ ] Módulo de vacaciones
- [ ] Mejoras en la UI/UX
- [ ] Tests E2E completos

### Fase 3 - Planificada 📋

- [ ] Autenticación y autorización
- [ ] Reportes y analytics
- [ ] Notificaciones
- [ ] API versioning

## 📚 Documentación Adicional

- [📄 DDD Refactoring Summary](./DDD_REFACTORING_SUMMARY.md) - Detalles del
  refactoring a DDD
- [📄 Prompt Técnico](agents/prompt-prueba-tecnica.md) - Especificaciones del
  proyecto
- [🔗 API Documentation](http://localhost:8000/api/docs) - Documentación
  interactiva de la API
- [🔗 Symfony Documentation](https://symfony.com/doc/current/) - Documentación
  oficial de Symfony
- [🔗 Vue.js Documentation](https://vuejs.org/) - Documentación oficial de Vue.js

---

## 🤝 Contribución

Este proyecto fue desarrollado como prueba técnica siguiendo las mejores
prácticas de desarrollo:

- **Clean Code** - Código limpio y legible
- **SOLID Principles** - Principios de diseño orientado a objetos
- **DDD Architecture** - Arquitectura dirigida por el dominio
- **Testing First** - Desarrollo guiado por tests
- **Docker Everything** - Entorno completamente containerizado

---

**Desarrollado con ❤️ usando Symfony, Vue.js y las mejores prácticas de
desarrollo**
