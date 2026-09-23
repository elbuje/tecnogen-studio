---
title: "Servicios de IA y Composición Visual"
description: "Motor de prompts dinámicos, generación de imágenes con OpenAI Image API y composición determinista de logos con Pillow."
tags:
  - ai
  - openai
  - pillow
  - composer
  - prompt-engineering
---

# 🤖 Servicios de IA & Composición de Marca

El pipeline visual combina generación por difusión/red neuronal con composición determinista por capas para garantizar nitidez tipográfica y fidelidad de marca.

---

## 🎨 1. Motor de Generación de Imágenes (OpenAI)

* **Modelo:** `gpt-image-2.5-sunburst` (OpenAI Image API)
* **Formato & Proporción:** `1024x1536` px (2:3 vertical nativo para Instagram/LinkedIn carousels).
* **Calidad:** `high`, salida en formato base64 (`b64_json`) transformado a bytes PNG.
* **Reserva de Espacio:** Cada prompt emitido por `prompt_builder.py` instruye al modelo a dejar un cuadrante despejado (`x:40, y:40, 180px`) para la posterior inserción del logo oficial.

---

## 🖼️ 2. Motor de Composición Determinista (Pillow RGBA)

El servicio `composer_service.py` ejecuta la superposición exacta del logo vectorial/PNG con transparencia del cliente:

```python
from PIL import Image
import io

def compose_brand_logo(
    slide_image_bytes: bytes,
    logo_image_bytes: bytes,
    position: str = "top-left",
    logo_width: int = 180,
    margin: int = 40
) -> bytes:
    slide = Image.open(io.BytesIO(slide_image_bytes)).convert("RGBA")
    logo = Image.open(io.BytesIO(logo_image_bytes)).convert("RGBA")
    
    aspect_ratio = logo.height / logo.width
    logo_height = int(logo_width * aspect_ratio)
    logo = logo.resize((logo_width, logo_height), Image.LANCZOS)
    
    positions = {
        "top-left": (margin, margin),
        "top-right": (slide.width - logo_width - margin, margin),
        "top-center": ((slide.width - logo_width) // 2, margin),
        "bottom-left": (margin, slide.height - logo_height - margin),
        "bottom-right": (slide.width - logo_width - margin, slide.height - logo_height - margin),
    }
    coords = positions.get(position, positions["top-left"])
    slide.paste(logo, coords, mask=logo)
    
    output = io.BytesIO()
    slide.save(output, format="PNG", optimize=True)
    return output.getvalue()
```

---

## 🔄 3. Regeneración Granular de Slides

* Si un carrusel de 7 slides tiene 1 lámina con texto a corregir, el usuario introduce feedback.
* Se incrementa la versión del slide (`version + 1`) manteniendo intactas las 6 láminas restantes.
* Se descuenta exactamente **1 crédito** y se re-ejecuta el worker únicamente para ese slide.
