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

## 📐 2. Configuración Dinámica de Layout (Google Sheets)

El pipeline respeta 4 nuevas dimensiones configurables por fila desde el Sheet:
1. **`PRESENCIA DOCTORA`:** `Portada y Cierre`, `Todas las slides`, `Solo portada`, `Solo en cierre`, `Ninguna`.
2. **`ESTILO PAGINADOR`:** `Puntos y Flechas`, `Línea Conectada`, `Pastilla Superior`, `Simple`.
3. **`BADGE ESTILO`:** `Conceptos / Beneficios`, `Mito vs Verdad`, `Pasos Numerados`, `Sin Badge`.
4. **`IDIOMA PROMPTS`:** `Español`, `Inglés`.

---

## 🔄 3. Regeneración Granular y Sincronización en Producción

* Selección de lámina individual desde el visor `/app/viewer/:id`.
* Endpoint de sincronización masiva `/api/v1/contents/sync-import` para sincronizar de inmediato SQLite y MySQL de producción.
* Endpoint público `/api/v1/contents/public/:id` para revisión fluida de previews sin requerir login.

---

## 📚 Documentación Técnica Detallada
* Guía completa de integración y arquitectura: [[guides/pipeline_sunburst_real_assets_architecture]]

