# 📋 Bitácora de Operaciones & Log — TecnoGen Studio

Historial cronológico de cambios, decisiones técnicas e hitos del proyecto.

---

## 2026-09-24
* `FEAT`: Implementación de **4 Arquitecturas Estructurales de Distribución de Pantalla y UI (Multi-Layout)**:
  1. **TecnoGen Clásico:** Barra de navegación superior fija + Menú lateral completo (260px) + Canvas central estándar.
  2. **HeyGen & Canva Creative Studio:** Riel de iconos compacto izquierdo (72px) con tooltips dinámicos + Barra de herramientas de estudio superior con acciones rápidas (`+ Generar Contenido`) + Canvas enmarcado para diseño audiovisual.
  3. **Claude Cowork (Anthropic):** Espacio de trabajo editorial minimalista con panel lateral colapsable de proyectos, árbol de navegación por secciones, migas de pan editoriales y lienzo amplio tipo documento.
  4. **ChatGPT Canvas (OpenAI):** Barra lateral colapsable oscura con botón "+ Nuevo Carrusel", selector central de modelo / motor IA (`TecnoGen Studio 4.0`) y canvas de enfoque total.
* `FEAT`: Automatización de **Google Sheets (Carru_JM)**: creación de endpoint `/api/v1/integrations/sync-sheet`, lectura de guiones estructurados, mapeo automático a slides y actualización bidireccional del Sheet (ID de contenido, estado y link de preview).
* `FEAT`: **Auditoría de Prompts en Visor**: caja con estilo monospace y botón de 1 clic para copiar el prompt exacto enviado a la IA para cada lámina.
* `FEAT`: **Generación Asíncrona con BackgroundTasks**: desacople de generación con OpenAI para respuesta inmediata a la UI y ejecución en background 100% autónoma en el servidor.
* `FIX`: Normalización de resolución de rutas para `google-service-account.json`, corrección de modelos ORM y supervisión permanente de daemon FastAPI con Ploi (`id: 226698`).
* `DEPLOY`: Despliegue continuo en producción en Ploi `errante` (`72.61.34.92`) para **`https://studio.tecnogen.ar`**.

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
