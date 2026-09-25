import os
import base64
import logging
import requests
from typing import Optional, Dict, Any
from app.config import settings

logger = logging.getLogger(__name__)

class AIImageService:
    def __init__(self, api_key: Optional[str] = None, model: Optional[str] = None):
        self.api_key = api_key or settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")
        self.model = model or getattr(settings, "OPENAI_IMAGE_MODEL", "gpt-image-2.5-sunburst-2026-09-08") or "dall-e-3"
        self.client = None

        if not self.api_key or self.api_key in ["tu-api-key-de-openai", ""]:
            logger.error("AIImageService inicializado sin API Key válida.")
        else:
            try:
                from openai import OpenAI
                self.client = OpenAI(api_key=self.api_key.strip(), timeout=60.0)
            except Exception as e:
                logger.error(f"Error al inicializar cliente OpenAI: {e}")
                raise RuntimeError(f"No se pudo inicializar el cliente de OpenAI: {str(e)}")

    def generate_slide_image(
        self,
        prompt: str,
        size: str = "1024x1024",
        quality: str = "standard",
        slide_info: Optional[Dict[str, Any]] = None,
        brand_info: Optional[Dict[str, Any]] = None
    ) -> bytes:
        """
        Invoca directamente la API de OpenAI con el modelo exacto seleccionado por el usuario.
        Si la llamada falla, arroja la excepción real para informar el error exacto sin fallbacks artificiales.
        """
        if not self.client or not self.api_key:
            raise RuntimeError("API Key de OpenAI no configurada o inválida. Por favor configúrala en Ajustes de IA.")

        model_to_use = self.model
        logger.info(f"🚀 Iniciando generación real con modelo OpenAI: '{model_to_use}'...")

        try:
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
                    raise RuntimeError(f"Fallo al descargar la imagen generada desde OpenAI URL ({r.status_code}): {r.text}")
            else:
                raise RuntimeError("Respuesta de OpenAI vacía o en formato no compatible.")

        except Exception as e:
            err_msg = str(e)
            logger.error(f"❌ Fallo real en OpenAI con modelo '{model_to_use}': {err_msg}")
            raise RuntimeError(f"Error OpenAI ({model_to_use}): {err_msg}")
