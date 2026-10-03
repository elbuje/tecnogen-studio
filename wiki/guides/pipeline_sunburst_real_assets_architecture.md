---
title: "Pipeline de Generación con OpenAI Sunburst y Assets Reales"
description: "Guía técnica y arquitectura completa para la integración de fotografías reales de profesionales (.HIF/.PNG decodificadas con pillow-heif), logotipos transparentes y OpenAI images.edit con gpt-image-2.5-sunburst."
tags:
  - ai
  - openai
  - gpt-image-2.5-sunburst
  - images.edit
  - pillow-heif
  - architecture
  - pipeline
---

# 🎨 Arquitectura y Pipeline: Generación con OpenAI Sunburst y Assets Reales

Este documento registra la arquitectura técnica, parámetros de API, flujo de procesamiento y código de integración implementado para generar carruseles de alta fidelidad utilizando **OpenAI `gpt-image-2.5-sunburst`** con **fotografías reales del profesional** y **logotipos de marca oficiales**.

---

## 1. El Desafío y la Solución Técnica

### ❌ El problema de `client.images.generate`
* El endpoint `/v1/images/generations` es **100% Text-to-Image**.
* Aunque se incluya la ruta del archivo o una descripción detallada en el texto, el modelo no tiene acceso a los píxeles reales, resultando en un rostro sintético y un logo deformado o alucinado.

### ✅ La solución con `client.images.edit`
* El endpoint `/v1/images/edits` permite enviar **archivos binarios reales** (`image=...`) junto con el `prompt` y el modelo `gpt-image-2.5-sunburst`.
* **El modelo recibe directamente los píxeles reales del profesional** (rostro, peinado, ambo con ribete rosa) y del **logo oficial**, integrándolos en la escena publicitaria con iluminación de estudio, fondos de marca y tipografía médica de alta calidad.

---

## 2. Diagrama de Flujo del Pipeline

```mermaid
flowchart TD
    A["Google Drive / Assets Oficiales"] -->|Descarga Karina1.HIF| B["Decodificador pillow-heif"]
    A -->|Descarga JM_blanco_negro.png| C["Buffer PNG Logo"]
    B -->|Convertir a RGBA| D["Constructor de Canvas de Referencia"]
    C -->|Posicionar en Esquina| D
    D -->|Buffer Binario PNG (1024x1024)| E["AIImageService (OpenAI SDK)"]
    F["Google Sheet (Guion Literal + Layout)"] -->|Prompt estructurado en Español| E
    E -->|client.images.edit gpt-image-2.5-sunburst| G["OpenAI Image API"]
    G -->|Imagen Generada HD (1254x1254)| H["Base de Datos / SQLite & MySQL"]
    H -->|Sincronización en Tiempo Real| I["Google Drive Output"]
    H -->|Sync API| J["Visor de Producción (studio.tecnogen.ar)"]
    H -->|Update Status| K["Google Sheet (Ya realizado + Link)"]
```

---

## 3. Ejemplo Visual del Resultado Obtenido

| Entrada (Canvas de Referencia) | Salida Oficial (`gpt-image-2.5-sunburst` via `images.edit`) |
| :---: | :---: |
| [`backend/test_composite_input.png`](file:///home/mfmujic/tecnogen-studio/backend/test_composite_input.png) | [`backend/test_sunburst_edit_karina.png`](file:///home/mfmujic/tecnogen-studio/backend/test_sunburst_edit_karina.png) |
| *Foto real de Karina y logo posicionados en el canvas base.* | *Portada final generada con rostro real, ambo original, clínica moderna y textos nítidos.* |

---

## 4. Implementación del Servicio (`AIImageService`)

Archivo: [`backend/app/services/ai_image_service.py`](file:///home/mfmujic/tecnogen-studio/backend/app/services/ai_image_service.py)

```python
import io
import base64
import logging
from PIL import Image
import pillow_heif
from openai import OpenAI

class AIImageService:
    def __init__(self, api_key: str = None, model: str = "gpt-image-2.5-sunburst"):
        self.client = OpenAI(api_key=api_key, timeout=90.0)
        self.model = model

    def build_reference_canvas(
        self,
        subject_bytes: bytes = None,
        logo_bytes: bytes = None,
        brand_color: str = "#16345F",
        canvas_size: tuple = (1024, 1024)
    ) -> io.BytesIO:
        # Crear canvas con color primario de marca
        hex_c = brand_color.lstrip('#')
        r, g, b = tuple(int(hex_c[i:i+2], 16) for i in (0, 2, 4))
        canvas = Image.new("RGBA", canvas_size, (r, g, b, 255))

        # 1. Integrar foto real del profesional
        if subject_bytes:
            try:
                heif_file = pillow_heif.read_heif(subject_bytes)
                subj_img = Image.frombytes(heif_file.mode, heif_file.size, heif_file.data, "raw").convert("RGBA")
            except Exception:
                subj_img = Image.open(io.BytesIO(subject_bytes)).convert("RGBA")

            subj_img.thumbnail((int(canvas_size[0] * 0.55), int(canvas_size[1] * 0.90)), Image.Resampling.LANCZOS)
            canvas.paste(subj_img, (canvas_size[0] - subj_img.width, canvas_size[1] - subj_img.height), subj_img)

        # 2. Integrar logo oficial transparente
        if logo_bytes:
            logo_img = Image.open(io.BytesIO(logo_bytes)).convert("RGBA")
            logo_img.thumbnail((int(canvas_size[0] * 0.28), int(canvas_size[1] * 0.14)), Image.Resampling.LANCZOS)
            canvas.paste(logo_img, (35, canvas_size[1] - logo_img.height - 40), logo_img)

        buf = io.BytesIO()
        canvas.save(buf, format="PNG")
        buf.seek(0)
        buf.name = "reference_input.png"
        return buf

    def generate_slide_image(
        self,
        prompt: str,
        subject_bytes: bytes = None,
        logo_bytes: bytes = None,
        brand_info: dict = None
    ) -> bytes:
        img_buffer = self.build_reference_canvas(
            subject_bytes=subject_bytes,
            logo_bytes=logo_bytes,
            brand_color=brand_info.get("primary_color", "#16345F")
        )

        # Transmisión binaria directa a Sunburst
        response = self.client.images.edit(
            model=self.model,
            image=img_buffer,
            prompt=prompt
        )

        first_img = response.data[0]
        if hasattr(first_img, 'b64_json') and first_img.b64_json:
            return base64.b64decode(first_img.b64_json)
        elif hasattr(first_img, 'url') and first_img.url:
            import requests
            return requests.get(first_img.url, timeout=30).content
```

---

## 5. Reglas de Presencia en el Carrusel (Configurables vía Google Sheet)

| Columna en Sheet | Opciones Válidas | Comportamiento en Generación |
| :--- | :--- | :--- |
| **`PRESENCIA DOCTORA`** | `Portada y Cierre`, `Todas las slides`, `Solo portada`, `Solo en cierre`, `Ninguna` | Define si el canvas de referencia enviado a Sunburst incluye la foto real del profesional en cada slide o solo el logo con gráficos médicos 3D. |
| **`ESTILO PAGINADOR`** | `Puntos y Flechas`, `Línea Conectada`, `Pastilla Superior`, `Simple` | Configura el tipo de indicador interactivo en el prompt de diseño. |
| **`BADGE ESTILO`** | `Conceptos / Beneficios`, `Mito vs Verdad`, `Pasos Numerados`, `Sin Badge` | Formatea la pastilla de categoría superior de cada slide. |
| **`IDIOMA PROMPTS`** | `Español`, `Inglés` | Asegura que toda la redacción y tipografía permanezca en español nativo. |

---

## 6. Enlaces de Verificación y Acceso

* **Visor del Carrusel Oficial en Producción:**
  🔗 [https://studio.tecnogen.ar/app/viewer/57536732-f7dc-41ee-9008-b37ee4b9ea2f](https://studio.tecnogen.ar/app/viewer/57536732-f7dc-41ee-9008-b37ee4b9ea2f)
* **Google Sheet Operativo:**
  🔗 [https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit](https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit)
* **Script de Ejecución Autónomo:**
  [`backend/generate_real_assets_carousel.py`](file:///home/mfmujic/tecnogen-studio/backend/generate_real_assets_carousel.py)
