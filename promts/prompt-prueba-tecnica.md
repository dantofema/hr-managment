# Prompt para Prueba Técnica: Desarrollo de Aplicación Simple

## Contexto

Desarrollamos una aplicación para una prueba técnica, priorizando la
funcionalidad y simplicidad sobre la escalabilidad. El objetivo es aprobar el
test técnico cumpliendo los siguientes lineamientos y restricciones.

## Stack Tecnológico

- **Backend:** Symfony (API Platform)
- **Frontend:** Vue.js
- **Estilos:** TailwindCSS
- **Base de datos:** PostgreSQL
- **Contenedores:** Todo debe ejecutarse en Docker
- **Arquitectura:** Seguir los lineamientos de DDD (Domain-Driven Design)
- **CORS:** Habilitado en el endpoint de empleados
- **MCP:** Disponemos de servidores MCP para coordinar agentes y tareas

## Reglas y Requerimientos

- Cada nueva funcionalidad debe tener al menos un test asociado (unitario o de
  integración)
- Las tablas y modelos deben ser lo más simples posible
- No buscamos escalar, solo cumplir y aprobar el test técnico
- Todo debe ser 100% funcional
- Utilizar la librería TailwindCSS para los estilos en el frontend

## Consideraciones

- El código debe ser claro, sencillo y enfocado en la funcionalidad
- La comunicación entre frontend y backend debe ser vía API REST
- El entorno de desarrollo y ejecución debe estar completamente dockerizado
- Documentar brevemente cada funcionalidad y test

## Ejemplo de estructura de carpetas

```
/backend (Symfony API Platform)
/frontend (Vue.js + TailwindCSS)
/docker (archivos de configuración)
/tests (tests unitarios e integración)
```

## Notas

- No se requiere implementar autenticación ni autorización a menos que el test
  lo exija explícitamente
- Utilizar los servidores MCP para automatizar tareas o coordinar agentes si es
  relevante para la prueba
- Mantener la simplicidad en todo momento

