# Mejoras de DDD en la Arquitectura del Proyecto

## Objetivo

Optimizar la estructura del proyecto para alinearse completamente con los
principios de Domain-Driven Design (DDD), eliminando duplicidades y mejorando la
organización de entidades y repositorios.

## Mejoras Solicitadas

1. **Entidades de Dominio**
    - Todas las entidades deben residir únicamente en las subcarpetas
      correspondientes dentro de `src/Domain/` (por ejemplo,
      `src/Domain/Employee/Employee.php`).
    - Eliminar cualquier duplicidad de entidades en `src/Entity`.

2. **Repositorios de Dominio**
    - Los repositorios deben estar ubicados dentro de cada subcarpeta de
      `src/Domain/` (por ejemplo, `src/Domain/Employee/EmployeeRepository.php`).
    - Eliminar o migrar los repositorios de `src/Repository` a la estructura de
      DDD.

3. **Separación de Contextos**
    - Mantener la estructura de carpetas por contexto de dominio: `Employee`,
      `Hiring`, `Payroll`, `Vacation`, `Shared`.
    - Cada contexto debe ser autónomo y contener sus propias entidades,
      repositorios y lógica de dominio.

4. **Consistencia y Claridad**
    - Evitar duplicidades y confusiones entre carpetas `Entity` y `Domain`.
    - Documentar brevemente la estructura y la ubicación de cada tipo de
      archivo.

## Ejemplo de Estructura Final Esperada

```
src/
  Domain/
    Employee/
      Employee.php
      EmployeeRepository.php
    Hiring/
      ...
    Payroll/
      ...
    Vacation/
      ...
    Shared/
      ...
```

## Notas

- El objetivo es lograr una arquitectura limpia, clara y alineada con DDD puro.
- No debe haber entidades ni repositorios fuera de `src/Domain`.
- Documentar los cambios realizados y justificar las decisiones de estructura.

