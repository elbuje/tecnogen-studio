# 📋 Bitácora de Operaciones & Log — TecnoGen Studio

Historial cronológico de cambios, decisiones técnicas e hitos del proyecto.

---

## 2026-10-13
* `FEAT`: **Fondos Dinámicos de Marca en Formulario (`/app/create`)**: derivación directa de colores (`bg_color`, `primary_color`, `accent_color`) y gradiente desde el modelo `Brand` agrupados con `<optgroup>` y dot interactivo en vivo.
* `FEAT`: **Previsualizador de Miniatura y Modal de Galería de Logos**: miniatura contigua al selector sobre contenedor oscuro y modal `#logosModal` que permite seleccionar visualmente las variantes oficiales de logo escaneadas de Drive.
* `FIX`: **Fidelidad Temática de Micro-Asistente IA**: eliminación de valores hardcodeados de demo en el HTML; `assistField` toma el texto actual del input como tema inamovible y `AIService.php` prohíbe explícitamente desviaciones hacia cirugías o implantes cuando el usuario propone bienestar, hábitos o prevención.
* `FIX`: **Compilación Estricta de Prompt para Post Único**: validación en `updatePromptPreview()` para que el modo `single_post` genere exactamente 1 pieza en el prompt, suprimiendo paginadores y guiones secuenciales múltiples.
* `FEAT`: **Prompt Maestro Editable y Copiable**: reemplazo de div estático por `<textarea id="promptPreviewBox">` nativo, botones de copiado y expansión, detección de edición manual (`✏️ Editado manualmente`) y recompilación desde formulario.
* `FEAT`: **Generación de Copy y Hashtags Sincronizados**: nuevo endpoint `/app/ai/generate-post-copy`, método `AIService::generatePostCopy()` y botón `✨ Redactar Copy y Hashtags Alineados con IA` para generar pie de foto y hashtags coherentes con el titular de la pieza.
* `FEAT`: **Visor de Prompts en Biblioteca (`/app/creations`)**: botón `⌨️ Prompt` en tarjetas de creación, visor modal `#promptViewerModal` seleccionable y copiable, y visualización de prompt por slide individual.
* `DEPLOY`: Despliegue en producción verificado en Ploi `errante` (`studio.tecnogen.ar`).

## 2026-10-10
* `FEAT`: **Dinamismo Total de Marca (JM Odontología Integral)**: vinculación unificada de la marca oficial en la base de datos, purga de marcas genéricas anteriores y actualización de `DatabaseSeeder.php` para que todas las cuentas operen bajo JM Odontología Integral (#16345F, #7DD3FC, #1D1D1B).
* `FEAT`: **Extracción de Fotografías de Personajes desde Google Drive**: actualización de `AIService.php` y `Character.php` para priorizar y descargar directamente los assets fotográficos reales desde las carpetas de Google Drive de cada profesional (`1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ`, `1Qc8t4ZAQEao4spaQjdL07WjppBu1ThZo`, `1PNC5gKeTv6OgHyXlgbZmHuAeNw8IH4ps`) y alimentar el canvas de referencia en `images.edit`.
* `FEAT`: **Micro-Asistentes IA y Compilador de Prompts Contextualizados**: adaptación de `assistField`, `parseScript` y `generateFullScript` para inyectar automáticamente el contexto médico de JM Odontología Integral, generando copies, ganchos, CTAs y hashtags coherentes.
* `FEAT`: **Generador de Contenido & Creaciones con Enlaces a Drive**: inclusión de botones de auditoría `📁 Ver Drive` para personajes, logos y creaciones en `/app/create` y `/app/creations`.
* `DEPLOY`: Despliegue en producción verificado en Ploi (`studio.tecnogen.ar`).
* `FEAT`: **Módulo Definición de Marca & Catálogo de Logos en Google Drive (`/app/brand`)**: creación de la vista y rutas para administración de identidad visual, conexión de carpeta de Google Drive de logotipos con escaneo paralelo y caché inteligente, visor de variantes con alternador de fondo claro/oscuro (🌓), lightbox en alta resolución y asignación de logotipo principal.
* `FEAT`: **Integración de Manual de Marca en Google Drive**: modal interactivo de vinculación y tarjeta de acceso directo al PDF de identidad oficial.
* `ARCH`: **Pipeline de Selección de Logos para IA**: especificación y confirmación de la lógica del agente para seleccionar automáticamente la variante de logo idónea (blanco/luz en fondos oscuros, institucional en fondos claros, isotipo en láminas complejas) montándolo en el canvas de referencia para `gpt-image-2.5-sunburst` (`images.edit`).
* `FEAT`: Actualización de la barra de navegación lateral y script de despliegue `deploy_laravel_ploi.sh`.

## 2026-10-04
* `FEAT`: **Catálogo de Tipos Visuales — Generación Completa de 28 Estructuras con Sunburst**: creación del comando artisan `php artisan visual-types:sunburst` y generación de las 28 imágenes demostrativas de alta resolución (1024x1024) exclusivamente con `gpt-image-2.5-sunburst`, garantizando textos 100% en español, estética SaaS médica (#16345F / #0284C7) y sincronización con el visor de catálogo en `/visual-types`.
* `FEAT`: **Persistencia de Tipo Visual Predeterminado**: configuración y persistencia de `tipo_visual` en la columna JSON `brand_rules` de la tabla `brands` al seleccionar un estilo desde la interfaz web.
* `DOC`: Creación del nodo taxonómico `wiki/nodes/catalogo_tipos_visuales.md` y registro de sesión.

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
