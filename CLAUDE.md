# CLAUDE.md — Proyecto WAG: Aplicación de Gestión de Minterio (IRONCHIP S.A.)

## Visión general del proyecto

Aplicación web para la gestión del taller de imagen personal del **CIFP Santurtzi LHII**. La primera versión ya está desarrollada y funcional; este proyecto aborda las mejoras identificadas tras la revisión con la jefa de taller.

**Cliente:** CIFP Santurtzi LHII (taller de imagen personal)
**Empresa desarrolladora:** IRONCHIP S.A.
**Stack:** Laravel (back-end) + Vue.js 3 (front-end SPA) + MySQL + Docker + AWS (EC2 + RDS)

---

## Arquitectura

La aplicación sigue el patrón **MVC**:

- **Modelo:** Laravel Eloquent ORM — lógica de negocio y conexión a la base de datos (MySQL).
- **Vista:** Vue.js 3 — Single Page Application (SPA) con componentes dinámicos.
- **Controlador:** Laravel Controllers — gestión de peticiones y respuestas API RESTful.

Infraestructura en AWS:
- **EC2:** servidor virtual escalable.
- **RDS:** base de datos MySQL gestionada para producción.
- **Docker:** aislamiento de entornos de testing y producción.

---

## Estado actual y áreas de mejora

La aplicación base ya existe. Hay **cuatro áreas de mejora prioritarias** que se deben abordar:

### 1. Docker Compose — despliegue incompleto

**Problema:** El contenedor no se despliega correctamente. Las variables de entorno del fichero `.env` no se pasan bien a los contenedores, especialmente los datos de conexión a la base de datos. La aplicación funciona en local pero falla en entornos WSL. Las pruebas nunca llegaron a ejecutarse en el entorno Docker.

**Objetivo:**
- Completar y corregir la configuración de `docker-compose.yml`.
- Garantizar que los tres servicios arrancan correctamente: `app`, `db`, `webserver`.
- Asegurar que las variables de entorno se inyectan bien desde `.env`.
- Validar el funcionamiento en entornos Linux/WSL.
- Configurar un pipeline CI/CD básico para automatizar el despliegue.

**Criterio de éxito:** `docker compose up` levanta los tres servicios sin errores y la aplicación es accesible con la base de datos conectada.

---

### 2. Modales rotos — `ModalVerCliente.vue` y `ModalEditCliente.vue`

**Problema:** Una refactorización de estilos CSS rompió los event listeners de varios botones. Los modales de gestión de clientes presentan los siguientes fallos:

- `ModalVerCliente.vue`: el botón de cierre no funciona; el contenido se corta en pantallas pequeñas.
- `ModalEditCliente.vue`: el formulario aparece pero los datos no se cargan correctamente al inicializar.
- En ambos: el overlay (fondo oscuro) permanece en pantalla aunque el modal se cierre.
- Los botones de creación de nuevos clientes y edición de inventario están deshabilitados.

**Objetivo:**
- Revisar y corregir los estilos CSS que bloquean los eventos de clic.
- Asegurar que el overlay se destruye/oculta correctamente al cerrar el modal.
- Verificar la inicialización de datos en `ModalEditCliente.vue`.
- Asegurar que los botones de creación e inventario vuelven a funcionar.

**Criterio de éxito:** Los modales se abren, muestran datos correctos y se cierran sin dejar overlay residual.

---

### 3. Responsive global de la aplicación

**Problema:** La aplicación no se adapta bien a pantallas pequeñas (móvil/tablet), especialmente en los formularios de los modales.

**Objetivo:**
- Revisar y mejorar el diseño responsive en todos los componentes Vue.
- Prestar especial atención a los modales y formularios en viewports estrechos.
- Asegurar que el contenido no se corta ni se solapa en ningún tamaño de pantalla habitual (móvil, tablet, escritorio).

**Criterio de éxito:** La aplicación es completamente usable en dispositivos móviles y tablets sin pérdida de funcionalidad ni visual roto.

---

### 4. Nueva sección: Portfolio de trabajos de alumnos

**Contexto:** La jefa de taller necesita documentar visualmente el progreso de cada alumno/a. Actualmente solo existe un registro cuantitativo (número de cortes, tintes, etc.), pero no hay evidencia visual de la calidad del trabajo.

**Objetivo — Back-end (Laravel):**
- Crear las nuevas tablas necesarias en la base de datos (migración).
- Desarrollar los endpoints API RESTful para:
  - Subir fotos (antes/después) asociadas a un servicio y alumno.
  - Listar, filtrar y eliminar entradas del portfolio.
- Gestionar el almacenamiento de imágenes (local en desarrollo, S3/disco en producción).
- Clasificar las entradas por tipo de servicio: cortes, colores, mechas, tratamientos.

**Objetivo — Front-end (Vue.js):**
- Crear `PortfolioView.vue` como nueva vista principal de la sección.
- Implementar la interfaz de subida de fotos antes/después al finalizar un servicio.
- Desarrollar un componente de comparación visual (antes ↔ después).
- Filtrar el portfolio por alumno y por tipo de servicio.

**Criterio de éxito:** Un alumno/a puede subir fotos al terminar un servicio, y la jefa de taller puede consultar el historial visual de cada alumno filtrado por categoría.

---

### 5. Tests

**Problema:** Las pruebas nunca se ejecutaron en el entorno Docker y la cobertura es insuficiente para garantizar la estabilidad de las mejoras.

**Objetivo:**
- Escribir tests para los endpoints de la nueva sección de portfolio (PHPUnit / Laravel Testing).
- Añadir tests para los modales corregidos (Vitest o Vue Test Utils).
- Asegurar que todos los tests pasan dentro del entorno Docker.
- Cubrir al menos los flujos críticos: creación de clientes, edición de inventario, subida de fotos al portfolio.

**Criterio de éxito:** `php artisan test` y los tests de front-end pasan sin errores dentro del contenedor Docker.

---

## Convenciones y guías de desarrollo

### General
- Todo el código nuevo debe seguir las convenciones ya existentes en el proyecto.
- Comentar en castellano o en euskera según el contexto del fichero.
- No introducir nuevas dependencias sin justificación explícita.

### Back-end (Laravel)
- Usar Eloquent ORM para todas las interacciones con la base de datos.
- Los nuevos endpoints deben seguir el estándar RESTful ya establecido.
- Las migraciones deben ser reversibles (`up` y `down`).
- Validar siempre los datos de entrada con Form Requests.

### Front-end (Vue.js 3)
- Usar la Composition API (`<script setup>`) para los componentes nuevos.
- Los nombres de componentes en PascalCase: `PortfolioView.vue`, `ModalVerCliente.vue`.
- No mezclar lógica de negocio con lógica de presentación en el mismo componente.
- Usar variables CSS para colores y espaciados; no hardcodear valores en los estilos.

### Docker
- El fichero `.env.example` debe estar actualizado con todas las variables necesarias.
- No subir el `.env` real al repositorio.
- Los tres servicios obligatorios son: `app` (Laravel), `db` (MySQL), `webserver` (Nginx).

### CSS / Responsive
- Mobile-first: diseñar primero para pantallas pequeñas y ampliar con media queries.
- Los modales deben ser scroll interno si el contenido supera la altura de la pantalla.
- El overlay debe usar `position: fixed` con `z-index` correctamente jerarquizado.

---

## Planificación (resumen)

| Tarea                          | Estado     |
|-------------------------------|------------|
| Análisis y planificación       | ✅ Hecho   |
| Docker Compose — corrección    | 🔧 Pendiente |
| Corrección de modales          | 🔧 Pendiente |
| Mejora responsive global       | 🔧 Pendiente |
| Portfolio — back-end           | 🔧 Pendiente |
| Portfolio — front-end          | 🔧 Pendiente |
| Comparación antes/después      | 🔧 Pendiente |
| Tests                          | 🔧 Pendiente |
| Documentación                  | 🔄 En curso |
| **Entrega final**              | **18 mayo 2026** |
| **Defensa**                    | **26 mayo 2026** |

---

## Contacto y contexto del cliente

- **Centro:** CIFP Santurtzi LHII
- **Departamento:** Taller de imagen personal (peluquería, estética)
- **Usuaria principal:** Jefa de taller
- **Necesidad principal:** Gestión de citas, control de stock e historial visual del trabajo de los alumnos.
