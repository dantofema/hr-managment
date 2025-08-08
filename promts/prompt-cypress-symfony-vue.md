# Prompt para Instalar, Configurar y Probar Cypress en Proyecto Symfony + Vue

## Objetivo

Instalar, configurar y ejecutar pruebas end-to-end (E2E) utilizando Cypress en
un proyecto con backend Symfony y frontend Vue.js, asegurando que Cypress se
ejecute siempre desde Docker.

## Pasos Solicitados

1. **Instalación de Cypress en Docker**
    - Agregar Cypress como dependencia de desarrollo en el frontend (Vue.js):
      ```bash
      npm install --save-dev cypress
      ```
    - Crear un servicio específico para Cypress en `docker-compose.yml` usando
      una imagen oficial de Cypress (por ejemplo, `cypress/included`).
    - Montar el código fuente del frontend y mapear la carpeta de tests de
      Cypress.
    - Ejemplo de servicio en `docker-compose.yml`:
      ```yaml
      cypress:
        image: cypress/included:13.7.3
        working_dir: /e2e
        volumes:
          - ./frontend:/e2e
        environment:
          - CYPRESS_baseUrl=http://frontend:5173
        depends_on:
          - frontend
      ```

2. **Configuración de Cypress**
    - Inicializar la configuración de Cypress en el proyecto frontend:
      ```bash
      npx cypress open
      ```
    - Configurar la baseUrl en `cypress.config.js` o `cypress.json` para apuntar
      al frontend (por ejemplo, `http://localhost:5173` si usas Vite).
    - Si el frontend consume la API Symfony, asegurarse de que el backend esté
      corriendo y accesible desde Cypress.

3. **Ejecución de Cypress en Docker**
    - Ejecutar Cypress desde el contenedor Docker:
      ```bash
      docker-compose run --rm cypress
      ```
    - Para ejecutar en modo headless:
      ```bash
      docker-compose run --rm cypress run
      ```

4. **Creación de un Test Básico**
    - Crear un archivo de prueba en `frontend/cypress/e2e/` (por ejemplo,
      `home.cy.js`).
    - El test debe verificar que la página principal carga correctamente y
      muestra un texto esperado.

5. **Actualización del README**
    - Documentar en el README los pasos para instalar, configurar y ejecutar
      Cypress exclusivamente desde Docker.
    - Incluir ejemplos de comandos y recomendaciones para troubleshooting.

## Notas

- Cypress debe ejecutarse siempre desde Docker, nunca desde el host.
- Asegurarse de que los endpoints de la API estén disponibles y configurados
  para CORS si se accede desde Cypress.
- Los tests deben ser simples y funcionales, enfocados en validar la integración
  frontend-backend.
