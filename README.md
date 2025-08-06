> ⚠️ **Advertencia:** Este proyecto fue desarrollado como parte de una prueba
> técnica en un tiempo limitado de **5 horas y media**. Algunas funcionalidades
> pueden no estar completamente implementadas.

# HR Management

## Datos personales

**Autor:** Alejandro Leone  
**Email:** dantofema@gmail.com

---

## Cómo levantar el proyecto

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/dantofema/hr-managment.git
   ```
2. Instalar dependencias backend:
   ```bash
   composer install
   ```
3. Instalar dependencias frontend:
   ```bash
   npm install
   ```
4. Levantar los servicios con Docker:
   ```bash
   docker compose up -d
   ```
5. Acceder a la web:
    - Backend API: http://localhost:8000
    - Frontend Vue: http://localhost:5173

---

## Características del proyecto

- Gestión de empleados, salarios y nóminas (payroll) mediante API RESTful.
- Arquitectura basada en DDD (Domain-Driven Design).
- Implementación de migraciones, repositorios y fixtures para datos iniciales.
- Uso de DTO (Data Transfer Object) para desacoplar la lógica de negocio.
- Implementación de CORS para permitir el acceso desde el frontend Vue.
- Documentación interactiva con Swagger.
- Página web simple desarrollada con Vue para visualizar empleados.
- Tests OK: 27 tests, 124 assertions.

---

## Arquitectura DDD

El proyecto está estructurado siguiendo los principios de Domain-Driven Design,
separando claramente las capas de dominio, aplicación, infraestructura y
presentación. Esto facilita la escalabilidad, el mantenimiento y la comprensión
del sistema.

---

## Implementación de CORS

Se configuró CORS en el backend (ver `config/packages/nelmio_cors.yaml`) para
permitir peticiones desde el frontend y otros orígenes, asegurando la
interoperabilidad y seguridad de la API.

---

## Tests

El proyecto cuenta con una suite de tests automatizados que validan la lógica de
negocio y los endpoints. Resultado actual:

- **27 tests**
- **124 assertions**

---

## Frontend Vue

Se desarrolló una página web simple con Vue que permite visualizar los empleados
en una tabla, editar y eliminar registros de manera intuitiva.

---

## Swagger

La API cuenta con documentación Swagger disponible en `/api/docs`, permitiendo
explorar y probar los endpoints de manera interactiva.

---

## Servicios API RESTful

- **Empleados**: CRUD completo para la gestión de empleados.
- **Salarios**: Endpoints para la gestión y actualización de salarios.
- **Payroll**: Servicios para la administración de nóminas.

---

## Migraciones, Repositorios y Fixtures

Se crearon migraciones para la estructura de la base de datos, repositorios para
el acceso a datos y fixtures para poblar datos de ejemplo en el entorno de
desarrollo.

---

## Uso de DTO

Se implementaron Data Transfer Objects para separar la lógica de negocio de la
presentación y facilitar la validación y transformación de datos.

---
