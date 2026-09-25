---
title: "Servicios de IA y Composición Visual"
description: "Motor de prompts dinámicos, lectura de modelos de BD (gpt-image-2.5-sunburst), decodificación HEIF/HIF y composición determinista HD con Pillow."
tags:
  - ai
  - openai
  - gpt-image-2.5-sunburst
  - pillow
  - pillow-heif
  - composer
  - prompt-engineering
---

# 🤖 Servicios de IA & Composición de Marca

El pipeline visual combina generación por difusión/red neuronal con composición determinista por capas para garantizar nitidez tipográfica, fidelidad de marca y cero artefactos de texto en inglés.

---

## 🎨 1. Motor de Generación de Imágenes (OpenAI)

* **Resolución del Modelo:** Dinámica desde la tabla `ai_settings` en la Base de Datos según la configuración activa del usuario en `/app/ai-settings` (por defecto `gpt-image-2.5-sunburst`, con soporte para `dall-e-3`, `flux-1.1-pro`, etc.).
* **Formato & Proporción:** `1024x1024` / `1024x1536` px (re-escalado a formato Instagram Portrait 1080x1350).
* **Negative Prompting Estricto:** Instrucciones mandatorias de no inclusión de texto (`STRICTLY NO TEXT, NO LETTERS, NO TYPOGRAPHY, NO SIGNS`) para obtener placas de fondo fotográficas puras.
* **Salida:** Decodificación automática de `b64_json` o descarga de `url`.

---

## 🖼️ 2. Motor de Composición Determinista (Pillow RGBA HD)

El servicio `composer_service.py` ejecuta el renderizado en alta definición (1080x1350 px):

1. **Numeración Obligatoria de Láminas:** Pastilla superior derecha (`1/8`, `2/8`, etc.).
2. **Logo Oficial de Google Drive:** Posicionado en la esquina superior izquierda con transparencia.
3. **Fotografía Real del Profesional (`pillow-heif`):** Decodificación directa de fotos iPhone `.HIF` / `.HEIC` e inserción en tarjeta encuadrada con borde en color acento (`#7DD3FC`).
4. **Tipografía y Badges en Español:** Título y cuerpo con respaldo de alto contraste para máxima legibilidad.
5. **Pastilla de Deslizamiento:** `Deslizá para leer »` y llamada a la acción en la lámina final.

---

## 🔄 3. Regeneración Granular de Slides

* Selección de lámina individual desde el visor `/app/viewer/:id`.
* Envío de feedback específico con incremento de versión del slide (`version + 1`).
* Procesamiento inmediato con el modelo activo en base de datos y recomposición completa de la lámina.
