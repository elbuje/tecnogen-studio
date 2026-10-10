# Sesión: Generador de Contenido con IA por Campo, Autoparser de Guiones y Carpeta de Creaciones en Google Drive

**Fecha:** 2026-10-10  
**Proyecto:** TecnoGen Studio (Laravel 11 + Blade SaaS)  
**Entorno Local:** `http://localhost:8018/`  
**Producción:** `https://studio.tecnogen.ar/`  

---

## 🎯 Objetivos Cumplidos

1. **Generador de Contenidos Unificado (`/app/create`):**
   - **Autoparser de Guiones Externos:** Modal y endpoint `POST /app/ai/parse-script` para pegar textos largos desde ChatGPT, Google Sheets, Notion o WhatsApp, desglosando automáticamente en Gancho/Hook, Badges, Láminas 1..N, CTA y Caption con hashtags.
   - **Micro-asistente IA en Cada Campo:** Cada caja de texto (Título/Hook, Badges de lámina, Títulos de lámina, Cuerpos de texto, CTA y Caption) cuenta con su botón interactivo `✨ IA` (`POST /app/ai/assist-field`). Si está vacío genera desde cero con tono clínico TecnoGen; si tiene borrador, lo pule y optimiza.
   - **Redacción de Carrusel Completo con IA:** Modal para generar un carrusel completo de 4 a 7 láminas a partir de un tema (`POST /app/ai/generate-full-script`).
   - **Integración Modular:** Selectores de Personaje (con avatar y rol), 28 Tipos Visuales, 49 Deslizadores y variantes de Logotipos de Google Drive.
   - **Compilador de Prompt en Tiempo Real:** Vista previa del prompt que se envía al modelo `gpt-image-2.5-sunburst`.

2. **Biblioteca de Creaciones y Google Drive (`/app/creations`):**
   - Panel de Creaciones que lista todas las publicaciones generadas con sus láminas, títulos y captions.
   - **Vinculación de Carpeta de Google Drive:** Configuración de la URL de destino (`gdrive_output_folder_url` / `gdrive_output_folder_id`) tanto en Creaciones como en Definición de Marca (`/app/brand`).
   - Acceso con 1 clic para abrir la carpeta de Drive en nueva pestaña y copiar captions con hashtags al portapapeles.

3. **Navegación del SaaS (`layouts/app.blade.php`):**
   - Menú lateral reestructurado con accesos directos a **Generador (IA Pro)** y **Creaciones & Drive**.

---

## 🚀 Despliegue
- Código commiteado y pusheado a ramas `dev` y `main`.
- Desplegado en producción en Ploi (`studio.tecnogen.ar` en servidor `errante`).
