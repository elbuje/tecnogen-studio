# 📋 Bitácora de Operaciones & Log — TecnoGen Studio

Historial cronológico de cambios, decisiones técnicas e hitos del proyecto.

---

## 2026-09-26
* `FEAT`: **Pipeline Oficial OpenAI Sunburst con Assets Reales (`images.edit`)**: integración de `client.images.edit(model="gpt-image-2.5-sunburst", image=buf, prompt=prompt)` pasando el buffer binario con la fotografía real de la profesional (`Karina1.HIF`) y el logotipo oficial transparente (`JM_blanco_negro.png`).
* `FEAT`: **Sincronización Bidireccional de Producción (`/contents/sync-import`)**: creación de endpoints de importación masiva y visualizador público (`/contents/public/{id}`) para previsualización inmediata de carruseles en producción sin fricción de login.
* `FEAT`: **Columnas de Configuración Dinámica en Google Sheet**: soporte de `PRESENCIA DOCTORA`, `ESTILO PAGINADOR`, `BADGE ESTILO` e `IDIOMA PROMPTS` leídas y aplicadas automáticamente en cada lote de generación.
* `DEPLOY`: Generación completa de las 8 láminas del Carrusel #2 (ID: `57536732-f7dc-41ee-9008-b37ee4b9ea2f`), subida a Google Drive y despliegue en producción en **`https://studio.tecnogen.ar`**.

## 2026-09-25
* `FEAT`: **Identidad de Marca Oficial JM Odontología Integral**: extracción de colores corporativos (`#16345F`, `#7DD3FC`, `#1D1D1B`), tipografías y logo transparente desde el Manual de Marca en Google Drive (`Manual de identidad JM.pdf`).
* `FEAT`: **Soporte Nativo de Fotos iPhone (`pillow-heif`)**: integración y decodificación directa de imágenes `.HIF` / `.HEIC` de las carpetas de personajes (`Jessica.HIF`).
* `FEAT`: **Carga Dinámica de Modelos de IA desde Base de Datos**: conexión directa entre `/app/ai-settings`, tabla `ai_settings` y el pipeline de generación (`contents.py`), respetando el modelo activo (`gpt-image-2.5-sunburst`, `dall-e-3`, etc.) y la API Key del usuario sin dependencia de `.env` estático.
* `FEAT`: **Compositor Determinista HD (1080x1350)**: inserción estricta de numeración de láminas (`1/8`, `2/8`, ..., `8/8`), logo oficial, badges temáticos en español y botón de deslizamiento `Deslizá para leer »`.
* `FEAT`: **Botones Interactivos de Enlaces en Integraciones**: agregado de botones "Abrir Carpeta" y "Abrir Hoja" con enlaces externos directos en `Integrations.tsx`.
* `FEAT`: **Rediseño Completo del Visor (`ContentViewer.tsx`)**: vista centralizada 1080x1350 tipo Figma/Canva, tira inferior de diapositivas numeradas, navegación por teclado (`←`/`→`), inspector lateral con guion editable, copia de prompts de IA en 1 clic y descarga de PNGs en lote.
* `DEPLOY`: Despliegue y verificación en producción en Ploi `errante` (`studio.tecnogen.ar`).

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
