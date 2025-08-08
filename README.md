# HR System

Un sistema moderno de gestión de RRHH construido con Symfony, TailwindCSS y
Vue.js.

---

## 🚀 Inicio Rápido

### Requisitos

- Docker y Docker Compose
- PHP 8.4+
- Node.js 18+
- Composer

### Configuración Local con Docker

1. **Clona el repositorio**
   ```bash
   git clone <repository-url>
   cd hr-system
   ```

2. **Inicia los servicios con Docker**
   ```bash
   docker-compose up -d
   ```
   Esto levantará:
    - Aplicación Symfony (puerto 8000)
    - Base de datos PostgreSQL (puerto 5432)
    - Node.js con Vite dev server (puerto 5173)

3. **Accede a la aplicación**
    - Backend: http://localhost:8000
    - Frontend: http://localhost:5173 (o el puerto indicado en logs)

---

### Comandos útiles

```bash
# Iniciar todos los servicios
$ docker-compose up -d

# Ver logs
$ docker-compose logs -f

# Detener servicios
$ docker-compose down

# Reconstruir contenedores
$ docker-compose up -d --build

# Acceder a la app (Symfony)
$ docker exec -it hr-system-app-1 bash

# Acceder al Node.js (Vite)
$ docker exec -it hr-system-node-1 sh

# Acceder a la base de datos
$ docker exec -it hr-system-database-1 psql -U app -d app
```

---

## 🛠️ Stack Tecnológico

- **Backend:** Symfony 7.x, Doctrine ORM, API Platform
- **Frontend:** Vue.js 3.x, TailwindCSS 4.x, Vite
- **Infraestructura:** Docker, PostgreSQL, Nginx

---

## 📁 Estructura del Proyecto

- `src/` Código backend Symfony
- `frontend/` Código frontend Vue.js
- `public/` Archivos públicos y compilados
- `migrations/` Migraciones de base de datos
- `tests/` Pruebas automatizadas

---

## 🎨 Desarrollo Frontend

- **Vue.js:** Componentes en `frontend/src/`
- **TailwindCSS:** Configuración en `frontend/tailwind.config.js`, CSS compilado
  en `public/css/app.css`
- **Vite:** Servidor de desarrollo y build

### Comandos Frontend (dentro del contenedor Node.js)

```bash
# Desarrollo (hot reload)
docker exec hr-system-node-1 npm run dev

# Build producción
docker exec hr-system-node-1 npm run build

# Preview build
docker exec hr-system-node-1 npm run preview
```

---

## 🧪 Pruebas

### Backend (PHPUnit)

```bash
# Todas las pruebas
docker exec hr-system-app-1 php vendor/bin/phpunit

# Prueba específica
docker exec hr-system-app-1 php vendor/bin/phpunit tests/Controller/HomeControllerTest.php

# Con cobertura
docker exec hr-system-app-1 php vendor/bin/phpunit --coverage-html coverage/
```

### Frontend

Actualmente no hay pruebas automáticas configuradas. Puedes verificar el
funcionamiento manualmente accediendo a http://localhost:5173.

---

## 🔧 Configuración y Variables de Entorno

Copia `.env` a `.env.local` y ajusta según tu entorno:

```bash
cp .env .env.local
```

Variables clave:

- `DATABASE_URL` - Cadena de conexión a la base de datos
- `APP_ENV` - Entorno (dev/prod)
- `APP_SECRET` - Clave secreta

---

## 🚨 Solución de Problemas

- **Puertos en uso:**
  ```bash
  lsof -i :5173  # o :8000, :5432
  kill -9 <PID>
  docker rm hr-system-node-1
  ```
- **Logs de Vite:**
  ```bash
  docker logs hr-system-node-1
  ```
- **Reiniciar contenedores:**
  ```bash
  docker-compose restart node
  docker-compose up -d --build
  ```

---

## 📝 Documentación API

- Docs: http://localhost:8000/api/docs
- Endpoint: http://localhost:8000/api

---

## 🤝 Contribuir

1. Haz un fork
2. Crea una rama feature
3. Realiza tus cambios
4. Ejecuta pruebas
5. Haz un pull request

---

## 📄 Licencia

MIT License
