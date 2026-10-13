---
title: "Servicios de IA y Composición Visual"
description: "Motor de prompts dinámicos, lectura de modelos de BD (gpt-image-2.5-sunburst), decodificación HEIF/HIF y transmisión de assets reales a OpenAI images.edit."
tags:
  - ai
  - openai
  - gpt-image-2.5-sunburst
  - images.edit
  - pillow-heif
  - real-assets
  - prompt-engineering
---

# 🤖 Servicios de IA & Composición de Marca con Assets Reales

El pipeline visual combina la potencia de **OpenAI `gpt-image-2.5-sunburst`** a través del endpoint `client.images.edit` junto con la **decodificación de fotografías reales (`pillow-heif`) y logos corporativos** para garantizar fidelidad absoluta de la persona y de la marca.

---

## 🎨 1. Motor de Generación con Assets Reales (`images.edit`)

* **Resolución del Modelo:** Dinámica desde la tabla `ai_settings` en la Base de Datos según la configuración activa del usuario en `/app/ai-settings` (por defecto `gpt-image-2.5-sunburst`).
* **Endpoint Utilizado:** `client.images.edit(model="gpt-image-2.5-sunburst", image=buf, prompt=prompt)`
* **Paso de Imágenes Binarias:** Se envía un buffer RGBA compuesto en memoria que incluye:
  1. **Fotografía Real del Profesional (`Karina1.HIF` / `Jessica.HIF`):** Decodificada mediante `pillow-heif` y posicionada en el cuadrante derecho.
  2. **Logo Oficial Transparente (`JM_blanco_negro.png`):** Posicionado en la esquina inferior izquierda.
* **Resultado:** El modelo conserva el rostro real de la doctora, su indumentaria clínica (ambo azul marino con ribete rosa) y el logo, mientras diseña la escena odontológica de alta gama, iluminación y tipografía en español.

---

## 📐 2. Micro-Asistentes de Texto & Generación de Copy (`AIService.php`)

* **Asistente de Campos (`/app/ai/assist-field`):** Permite enriquecer o sugerir titulares, categorías y cuerpos para cada lámina. Incorpora **fidelidad temática estricta**: respeta inamoviblemente el tema del usuario (miedos, prevención, bienestar) y prohíbe explícitamente desviaciones hacia tratamientos invasivos o implantes a menos que el usuario lo indique.
* **Redactor de Copy y Hashtags (`/app/ai/generate-post-copy`):** Redacta el pie de foto (caption) y hashtags optimizados para Instagram según el titular y el tipo de contenido (Post Único vs Carrusel), evitando invitaciones erróneas a "deslizar" cuando se trata de una pieza única.
* **Compilador de Prompt Maestro Editable:** Formateador reactivo en frontend y backend. En modo `single_post`, genera la directriz para exactamente 1 pieza (1080x1080 px) sin paginador ni slides múltiples. Se presenta en un `<textarea>` editable por el usuario con detección de cambios manuales y recompilación bajo demanda.

---

## 🎨 3. Selección Dinámica de Fondos y Logos de Marca

* **Fondos Dinámicos (`Brand::getBackgroundOptions()`):** Derivados 100% de la identidad de la marca activa (`bg_color`, `primary_color`, `accent_color`, gradientes corporativos y fondos de estudio neutros), organizados en grupos visuales en el generador.
* **Modal y Miniatura de Variantes de Logo:** Selector visual con miniatura interactiva sobre fondo oscuro y modal emergente de variantes indexadas desde Google Drive para selección con un clic.

---

## 🔄 4. Biblioteca de Creaciones & Visor de Prompts (`/app/creations`)

* **Visor de Prompts (`#promptViewerModal`):** Acceso al Prompt Maestro de producción y a los prompts específicos de cada slide con copiado rápido al portapapeles.
* **Regeneración Granular:** Regeneración individual lámina por lámina o en lote.
* **Sincronización Directa:** Integración con Google Drive para persistencia de piezas en la carpeta del cliente.

---

## 📚 Documentación Técnica Detallada
* Guía completa de integración y arquitectura: [[guides/pipeline_sunburst_real_assets_architecture]]


