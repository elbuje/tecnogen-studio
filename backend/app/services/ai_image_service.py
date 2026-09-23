import os
import base64
import logging
from typing import Optional
from openai import OpenAI
from app.config import settings

logger = logging.getLogger(__name__)

class AIImageService:
    def __init__(self, api_key: Optional[str] = None, model: Optional[str] = None):
        self.api_key = api_key or settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")
        self.model = model or settings.OPENAI_IMAGE_MODEL
        self.client = OpenAI(api_key=self.api_key) if self.api_key else None

    def generate_slide_image(
        self,
        prompt: str,
        size: str = "1024x1536",
        quality: str = "high"
    ) -> bytes:
        """
        Genera una lámina usando el modelo configurado.
        Si no hay API key real configurada en dev, genera un placeholder visual SVG/PNG nítido de prueba.
        """
        if self.client and self.api_key and not self.api_key.startswith("mock"):
            try:
                response = self.client.images.generate(
                    model=self.model,
                    prompt=prompt,
                    size=size,
                    quality=quality,
                    n=1,
                    response_format="b64_json"
                )
                image_base64 = response.data[0].b64_json
                return base64.b64decode(image_base64)
            except Exception as e:
                logger.error(f"Error generando imagen con OpenAI ({self.model}): {e}")
                raise e

        # Fallback Mock para entorno Dev sin API Key activa
        return self._generate_mock_slide_bytes(prompt)

    def _generate_mock_slide_bytes(self, prompt: str) -> bytes:
        from PIL import Image, ImageDraw, ImageFont
        import io
        
        # Crear imagen vertical 1024x1536
        img = Image.new("RGBA", (1024, 1536), color=(11, 30, 56, 255))
        draw = ImageDraw.Draw(img)
        
        # Gradiente sutil o rectángulos de diseño
        draw.rectangle([40, 40, 984, 1496], outline=(125, 211, 252, 100), width=4)
        
        # Marcador de logo
        draw.rectangle([50, 50, 230, 130], fill=(22, 52, 95, 200), outline=(125, 211, 252, 255), width=2)
        draw.text((65, 80), "[LOGO RESERVED]", fill=(255, 255, 255, 255))
        
        # Título y prompt preview
        draw.text((80, 400), "TecnoGen Studio", fill=(125, 211, 252, 255))
        draw.text((80, 460), "Lámina Generada con IA", fill=(255, 255, 255, 255))
        
        # Paginador inferior
        for i in range(6):
            cx = 400 + i * 45
            color = (125, 211, 252, 255) if i == 0 else (255, 255, 255, 100)
            draw.ellipse([cx, 1420, cx + 18, 1438], fill=color)
        
        output = io.BytesIO()
        img.save(output, format="PNG")
        return output.getvalue()
