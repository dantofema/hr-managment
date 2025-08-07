# Guía avanzada: Uso de múltiples agentes en Junie con prompts

Esta guía explica cómo utilizar Junie para coordinar múltiples agentes en el
desarrollo de software, aprovechando la infraestructura MCP (Model Context
Protocol) y la carpeta `promts`.

## 1. ¿Qué es un agente en Junie?

Un agente es una entidad IA especializada en una función (por ejemplo:
planificación, filesystem, base de datos, git, etc.). Cada agente tiene su
propio prompt de sistema y responde a tareas específicas.

## 2. Estructura recomendada de la carpeta `promts`

```
/promts
├── planner/
│   └── prompt.system.txt
├── filesystem/
│   └── prompt.system.txt
├── postgres/
│   └── prompt.system.txt
├── git/
│   └── prompt.system.txt
├── ...
```

## 3. Ejemplos de agentes

- **planner**: descompone requerimientos y coordina tareas.
- **filesystem**: crea, edita y organiza archivos y carpetas.
- **postgres**: gestiona la base de datos y migraciones.
- **git**: controla versiones, ramas y commits.
- **github**: interactúa con repositorios remotos.
- **fetch**: obtiene información externa relevante.

## 4. Cómo indicar los servidores MCP disponibles

En el prompt de sistema de cada agente, especifica los servidores MCP que puede
utilizar. Ejemplo:

```
Servidores MCP disponibles:
- filesystem: para manipulación de archivos
- postgres: para base de datos
- git: para control de versiones
- github: para repositorios remotos
- fetch: para documentación externa
```

Al asignar tareas, el planner debe indicar explícitamente qué agente y servidor
MCP debe ejecutar cada paso.

## 5. Uso avanzado de Junie

- Puedes seleccionar el agente adecuado desde la interfaz de Junie antes de
  enviar tu prompt.
- Personaliza los prompts de sistema para cada agente en la carpeta `promts`.
- Utiliza ejemplos en `/promts/{agente}/ejemplos/` para guiar la interacción.
- Para flujos complejos, el planner puede coordinar varios agentes en secuencia,
  asegurando que cada uno ejecute su parte y reporte el resultado.
- Aprovecha la integración con Docker y PHPUnit para automatizar pruebas y
  despliegues.

## 6. Ejemplo de flujo multi-agente

**Usuario:** "Quiero una funcionalidad para registrar vacaciones."

**planner:**

1. Divide la tarea en subtareas (entidad, migración, endpoint, test).
2. Asigna cada subtarea al agente correspondiente (filesystem, postgres, etc.).
3. Supervisa que cada agente reporte éxito antes de avanzar.

**filesystem:** Crea la entidad y el test.

**postgres:** Genera la migración.

**git:** Hace commit solo si los tests pasan.

---

Con esta estructura y metodología, puedes aprovechar al máximo Junie y los
agentes MCP para un desarrollo colaborativo, automatizado y eficiente.
