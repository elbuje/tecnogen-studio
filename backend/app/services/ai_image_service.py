import os
import base64
import logging
import io
from typing import Optional, Dict, Any
from openai import OpenAI
from app.config import settings

logger = logging.getLogger(__name__)

class AIImageService:
    def __init__(self, api_key: Optional[str] = None, model: Optional[str] = None):
        self.api_key = api_key or settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")
        self.model = model or settings.OPENAI_IMAGE_MODEL or "dall-e-3"
        if not self.api_key:
            raise ValueError("No se encontró OPENAI_API_KEY configurada. La generación requiere una API Key válida de OpenAI.")
        self.client = OpenAI(api_key=self.api_key)

    def generate_slide_image(
        self,
        prompt: str,
        size: str = "1024x1792",  # Proporción vertical para DALL-E 3 (o 1024x1024 / 1024x1536 según modelo)
        quality: str = "standard"
    ) -> bytes:
        """
        Llama de forma estricta a la API de OpenAI.
        Si la API Key falla o el modelo devuelve un error, se propaga la excepción inmediatamente.
        NO se permite fallback sintético.
        """
        if not self.client:
            raise RuntimeError("Cliente OpenAI no inicializado. Se requiere una API Key válida.")

        try:
            # En DALL-E 3 los tamaños soportados son 1024x1024, 1024x1792 (vertical) o 1792x1024 (horizontal)
            image_size = "1024x1792" if self.model == "dall-e-3" else "1024x1536"
            
            # Para modelos compatibles con b64_json
            response = self.client.images.generate(
                model=self.model,
                prompt=prompt,
                size=image_size,
                quality=quality,
                n=1,
                response_format="b64_json"
            )
            image_base64 = response.data[0].b64_json
            return base64.b64decode(image_base64)
        except Exception as e:
            # Si el modelo no soporta b64_json o falla por url, intentamos descargar de url
            try:
                response = self.client.images.generate(
                    model=self.model,
                    prompt=prompt,
                    size="1024x1792" if self.model == "dall-e-3" else "1024x1024",
                    n=1
                )
                if response.data[0].url:
                    import httpx
                    res = httpx.get(response.data[0].url, timeout=30.0)
                    return res.content
                elif response.data[0].b64_json:
                    return base64.b64decode(response.data[0].b64_json)
            except Exception as inner_e:
                logger.error(f"Error crítico en OpenAI Image API: {inner_e}")
                raise inner_e
            logger.error(f"Error crítico en OpenAI Image API: {e}")
            raise e
