# 📋 Bitácora de Operaciones & Log — TecnoGen Studio

Historial cronológico de cambios, decisiones técnicas e hitos del proyecto.

---

## 2026-09-24
* `FEAT`: Implementación de **4 Arquitecturas Estructurales de Distribución de Pantalla y UI (Multi-Layout)**:
  1. **TecnoGen Clásico:** Barra de navegación superior fija + Menú lateral completo (260px) + Canvas central estándar.
  2. **HeyGen & Canva Creative Studio:** Riel de iconos compacto izquierdo (72px) con tooltips dinámicos + Barra de herramientas de estudio superior con acciones rápidas (`+ Generar Contenido`) + Canvas enmarcado para diseño audiovisual.
  3. **Claude Cowork (Anthropic):** Espacio de trabajo editorial minimalista con panel lateral colapsable de proyectos, árbol de navegación por secciones, migas de pan editoriales y lienzo amplio tipo documento.
  4. **ChatGPT Canvas (OpenAI):** Barra lateral colapsable oscura con botón "+ Nuevo Carrusel", selector central de modelo / motor IA (`TecnoGen Studio 4.0`) y canvas de enfoque total.
* `DEPLOY`: Despliegue exitoso en producción en Ploi `errante` (`72.61.34.92`) para el dominio **`https://studio.tecnogen.ar`**.
* `REPO`: Creación y sincronización del repositorio oficial en GitHub: **`https://github.com/elbuje/tecnogen-studio`** (Ramas `main` y `dev`).
* `SSL`: Emisión de certificado SSL con Let's Encrypt y redirección HTTPS 301.
* `BACKEND`: Puesta en marcha de FastAPI con PM2 en el puerto `8028` y base de datos persistente en `/home/ploi/studio.tecnogen.ar/storage/tecnogen_prod.db`.
* `FRONTEND`: Compilación de la aplicación React 18 + Vite en `/public` y conexión de proxy reverso hacia `/api/`.

## 2026-09-23
* `INIT`: Instalación de LLM Wiki bajo el estándar de 3 capas y flujos en `.agent/workflows/`.
* `FEAT`: Registro y análisis detallado de la especificación técnica completa (PRD v1.0).
* `DOC`: Creación de nodos maestros de conocimiento en `wiki/nodes/` (Arquitectura, Modelo de Datos, Rutas API, Servicios IA + Composer, Zero-Storage, Frontend y Despliegue en `errante`).
* `CONFIG`: Asignación de puertos dev en `/home/mfmujic/PORT_REGISTRY.md` (Backend 8018, Frontend 5192).
* `FEAT`: Implementación de pantalla **Mi Perfil** con edición de datos personales, marca y contraseña.
* `FEAT`: Refactorización de **Modelos & IA** con selector dinámico Empresa -> Key -> Modelos y política estricta de API Key del usuario.
* `FEAT`: Conexión de **Google Drive con 5 carpetas especializadas** (Logos, Sujetos, Manuales, Plantillas, Productos) y explicaciones con sintaxis `[TAG:nombre]`.
* `FEAT`: Renovación de **Brand Kit con 10 Tarjetas de Distribución y Composición Gráfica** con mini wireframes interactivos.
* `FEAT`: Creación de sección **Mi Biblioteca** con buscador, filtros y eliminación de carruseles.
* `FEAT`: Visor de láminas 1024x1536 con auto-polling y regeneración individual por slide o completa.
