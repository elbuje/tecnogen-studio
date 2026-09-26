import os
import io
import base64
import logging
import requests
from typing import Optional, Dict, Any
from PIL import Image
import pillow_heif
from app.config import settings

logger = logging.getLogger(__name__)

class AIImageService:
    def __init__(self, api_key: Optional[str] = None, model: Optional[str] = None):
        self.api_key = api_key or settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")
        self.model = model or getattr(settings, "OPENAI_IMAGE_MODEL", "gpt-image-2.5-sunburst") or "gpt-image-2.5-sunburst"
        self.client = None

        if not self.api_key or self.api_key in ["tu-api-key-de-openai", ""]:
            logger.error("AIImageService inicializado sin API Key válida.")
        else:
            try:
                from openai import OpenAI
                self.client = OpenAI(api_key=self.api_key.strip(), timeout=90.0)
            except Exception as e:
                logger.error(f"Error al inicializar cliente OpenAI: {e}")
                raise RuntimeError(f"No se pudo inicializar el cliente de OpenAI: {str(e)}")

    def build_reference_canvas(
        self,
        subject_bytes: Optional[bytes] = None,
        logo_bytes: Optional[bytes] = None,
        brand_color: str = "#16345F",
        canvas_size: tuple = (1024, 1024)
    ) -> io.BytesIO:
        """
        Construye una imagen base de referencia en memoria combinando el sujeto real (Dra. Karina)
        y el logo oficial transparente en una composición equilibrada para alimentar a client.images.edit.
        """
        # Convertir color hexadecimal a RGBA
        hex_c = brand_color.lstrip('#')
        r, g, b = tuple(int(hex_c[i:i+2], 16) for i in (0, 2, 4))
        canvas = Image.new("RGBA", canvas_size, (r, g, b, 255))

        # 1. Procesar Sujeto / Doctora
        if subject_bytes:
            try:
                try:
                    heif_file = pillow_heif.read_heif(subject_bytes)
                    subj_img = Image.frombytes(heif_file.mode, heif_file.size, heif_file.data, "raw").convert("RGBA")
                except Exception:
                    subj_img = Image.open(io.BytesIO(subject_bytes)).convert("RGBA")

                # Escalar proporcionalmente para ubicar a la derecha
                target_w = int(canvas_size[0] * 0.55)
                target_h = int(canvas_size[1] * 0.90)
                subj_img.thumbnail((target_w, target_h), Image.Resampling.LANCZOS)
                
                pos_x = canvas_size[0] - subj_img.width
                pos_y = canvas_size[1] - subj_img.height
                canvas.paste(subj_img, (pos_x, pos_y), subj_img if subj_img.mode == "RGBA" else None)
            except Exception as e:
                logger.warning(f"No se pudo procesar sujeto en el canvas de referencia: {e}")

        # 2. Procesar Logo oficial
        if logo_bytes:
            try:
                logo_img = Image.open(io.BytesIO(logo_bytes)).convert("RGBA")
                logo_w = int(canvas_size[0] * 0.28)
                logo_h = int(canvas_size[1] * 0.14)
                logo_img.thumbnail((logo_w, logo_h), Image.Resampling.LANCZOS)
                
                # Ubicar en esquina inferior izquierda
                canvas.paste(logo_img, (35, canvas_size[1] - logo_img.height - 40), logo_img)
            except Exception as e:
                logger.warning(f"No se pudo procesar logo en el canvas de referencia: {e}")

        buf = io.BytesIO()
        canvas.save(buf, format="PNG")
        buf.seek(0)
        buf.name = "reference_input.png"
        return buf

    def generate_slide_image(
        self,
        prompt: str,
        size: str = "1024x1024",
        quality: str = "standard",
        slide_info: Optional[Dict[str, Any]] = None,
        brand_info: Optional[Dict[str, Any]] = None,
        reference_image_bytes: Optional[bytes] = None,
        subject_bytes: Optional[bytes] = None,
        logo_bytes: Optional[bytes] = None
    ) -> bytes:
        """
        Invoca la API de OpenAI (gpt-image-2.5-sunburst).
        Si se proporcionan activos reales (sujeto / logo / referencia), utiliza client.images.edit
        para transmitir los bytes reales directamente a la IA y garantizar fidelidad absoluta.
        """
        if not self.client or not self.api_key:
            raise RuntimeError("API Key de OpenAI no configurada o inválida. Por favor configúrala en Ajustes de IA.")

        model_to_use = self.model
        logger.info(f"🚀 Iniciando generación real con modelo OpenAI: '{model_to_use}'...")

        # Determinar si construimos o usamos imagen de referencia real
        img_buffer = None
        if reference_image_bytes:
            img_buffer = io.BytesIO(reference_image_bytes)
            img_buffer.name = "reference.png"
        elif subject_bytes or logo_bytes:
            primary_col = (brand_info.get("primary_color") if brand_info else None) or "#16345F"
            img_buffer = self.build_reference_canvas(
                subject_bytes=subject_bytes,
                logo_bytes=logo_bytes,
                brand_color=primary_col
            )

        try:
            if img_buffer:
                logger.info(f"📸 Transmitiendo imagen real y logo a OpenAI '{model_to_use}' via images.edit...")
                response = self.client.images.edit(
                    model=model_to_use,
                    image=img_buffer,
                    prompt=prompt
                )
            else:
                logger.info(f"✨ Invocando OpenAI '{model_to_use}' via images.generate...")
                response = self.client.images.generate(
                    model=model_to_use,
                    prompt=prompt,
                    size=size,
                    n=1
                )

            first_img = response.data[0]
            if hasattr(first_img, 'b64_json') and first_img.b64_json:
                return base64.b64decode(first_img.b64_json)
            elif hasattr(first_img, 'url') and first_img.url:
                r = requests.get(first_img.url, timeout=30)
                if r.status_code == 200:
                    return r.content
                else:
                    raise RuntimeError(f"Fallo al descargar imagen desde OpenAI URL ({r.status_code}): {r.text}")
            else:
                raise RuntimeError("Respuesta de OpenAI vacía o en formato no compatible.")

        except Exception as e:
            err_msg = str(e)
            logger.error(f"❌ Fallo real en OpenAI con modelo '{model_to_use}': {err_msg}")
            raise RuntimeError(f"Error OpenAI ({model_to_use}): {err_msg}")
