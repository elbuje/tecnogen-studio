import os
import base64
import logging
import io
from typing import Optional, Dict, Any
from PIL import Image, ImageDraw, ImageFont
from app.config import settings

logger = logging.getLogger(__name__)

class AIImageService:
    def __init__(self, api_key: Optional[str] = None, model: Optional[str] = None):
        self.api_key = api_key or settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")
        self.model = model or getattr(settings, "OPENAI_IMAGE_MODEL", "dall-e-3") or "dall-e-3"

        self.client = None
        if self.api_key and self.api_key not in ["tu-api-key-de-openai", ""]:
            try:
                from openai import OpenAI
                self.client = OpenAI(api_key=self.api_key, timeout=45.0)
            except Exception as e:
                logger.warning(f"No se pudo instanciar cliente OpenAI: {e}")

    def generate_slide_image(
        self,
        prompt: str,
        size: str = "1024x1024",
        quality: str = "standard",
        slide_info: Optional[Dict[str, Any]] = None,
        brand_info: Optional[Dict[str, Any]] = None
    ) -> bytes:
        """
        Genera imagen fotográfica hiperrealista con OpenAI DALL-E-3 API.
        """
        if self.client:
            resolved_primary = self.model or "dall-e-3"
            models_to_try = [resolved_primary, "dall-e-3", "dall-e-2"]
            # Deduplicar preservando orden
            seen = set()
            models_to_try = [m for m in models_to_try if not (m in seen or seen.add(m))]

            for m in models_to_try:
                try:
                    img_size = "1024x1024"
                    response = self.client.images.generate(
                        model=m,
                        prompt=prompt,
                        size=img_size,
                        n=1
                    )
                    first_img = response.data[0]
                    if hasattr(first_img, 'b64_json') and first_img.b64_json:
                        return base64.b64decode(first_img.b64_json)
                    elif hasattr(first_img, 'url') and first_img.url:
                        import requests
                        r = requests.get(first_img.url, timeout=25)
                        if r.status_code == 200:
                            return r.content
                except Exception as e:
                    logger.warning(f"Intento con modelo {m} falló ({e}). Probando siguiente...")

        # 2. Generador Gráfico de Contingencia (Pillow / Editorial Canvas HD)
        return self._render_editorial_canvas(prompt=prompt, slide_info=slide_info, brand_info=brand_info)

    def _render_editorial_canvas(
        self,
        prompt: str,
        slide_info: Optional[Dict[str, Any]] = None,
        brand_info: Optional[Dict[str, Any]] = None
    ) -> bytes:
        """
        Renderiza una lámina de 1024x1536 px con estética editorial médica/profesional de lujo,
        degradados sutiles, tipografía refinada, badge de número y detalles de marca.
        """
        width, height = 1024, 1536
        
        # Paleta de colores
        bg_hex = (brand_info.get("bg_color") if brand_info else None) or "#0B1E38"
        accent_hex = (brand_info.get("accent_color") if brand_info else None) or "#7DD3FC"
        primary_hex = (brand_info.get("primary_color") if brand_info else None) or "#16345F"
        brand_name = (brand_info.get("name") if brand_info else None) or "JM Odontología Integral"

        def hex_to_rgb(h):
            h = h.lstrip('#')
            if len(h) == 6:
                return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))
            return (11, 30, 56)

        bg_rgb = hex_to_rgb(bg_hex)
        accent_rgb = hex_to_rgb(accent_hex)
        primary_rgb = hex_to_rgb(primary_hex)

        # Crear imagen base con degradado vertical sutil
        img = Image.new("RGB", (width, height), bg_rgb)
        draw = ImageDraw.Draw(img)

        # Pintar degradado oscuro
        for y in range(height):
            factor = y / height
            r = int(bg_rgb[0] * (1 - factor * 0.4))
            g = int(bg_rgb[1] * (1 - factor * 0.4))
            b = int(bg_rgb[2] * (1 - factor * 0.4) + primary_rgb[2] * factor * 0.3)
            draw.line([(0, y), (width, y)], fill=(min(255, r), min(255, g), min(255, b)))

        # Resplandor sutil en la parte superior derecha
        for rad in range(350, 0, -10):
            glow_opacity = int(25 * (1 - rad / 350))
            draw.ellipse(
                [width - 250 - rad, -100 - rad, width - 250 + rad, -100 + rad],
                outline=None,
                fill=(min(255, accent_rgb[0] + glow_opacity), min(255, accent_rgb[1] + glow_opacity), min(255, accent_rgb[2] + glow_opacity))
            )

        # Header / Logo Placeholder
        draw.rectangle([70, 70, 260, 125], fill=(primary_rgb[0], primary_rgb[1], primary_rgb[2]))
        draw.rectangle([70, 70, 260, 125], outline=accent_rgb, width=2)
        draw.text((85, 88), brand_name[:18].upper(), fill=(255, 255, 255))

        # Badge Superior
        badge_text = (slide_info.get("badge") if slide_info else None) or "TEMA CLAVE"
        slide_num = (slide_info.get("slide_number") if slide_info else 1) or 1
        total_slides = (slide_info.get("total_slides") if slide_info else 6) or 6

        # Badge pill
        draw.rectangle([70, 240, 320, 285], fill=(primary_rgb[0], primary_rgb[1], primary_rgb[2]))
        draw.rectangle([70, 240, 320, 285], outline=accent_rgb, width=1)
        draw.text((90, 253), f"{badge_text.upper()}", fill=accent_rgb)

        # Título Principal
        title_text = (slide_info.get("title") if slide_info else None) or prompt[:80]
        # Cortar en líneas de ~24 caracteres
        words = title_text.split()
        lines = []
        curr = ""
        for w in words:
            if len(curr + " " + w) < 22:
                curr = (curr + " " + w).strip()
            else:
                lines.append(curr)
                curr = w
        if curr:
            lines.append(curr)

        y_pos = 340
        for line in lines[:4]:
            draw.text((70, y_pos), line, fill=(255, 255, 255))
            y_pos += 65

        # Línea divisoria de acento
        y_pos += 20
        draw.line([(70, y_pos), (220, y_pos)], fill=accent_rgb, width=4)
        y_pos += 40

        # Subtítulo / Cuerpo
        body_text = (slide_info.get("body") if slide_info else None) or (slide_info.get("subtitle") if slide_info else "")
        if not body_text:
            body_text = "Técnicas avanzadas de odontología y estética de vanguardia diseñadas para resultados naturales y duraderos."

        body_words = body_text.split()
        body_lines = []
        b_curr = ""
        for bw in body_words:
            if len(b_curr + " " + bw) < 36:
                b_curr = (b_curr + " " + bw).strip()
            else:
                body_lines.append(b_curr)
                b_curr = bw
        if b_curr:
            body_lines.append(b_curr)

        for bline in body_lines[:6]:
            draw.text((70, y_pos), bline, fill=(203, 213, 225))
            y_pos += 45

        # Visual Card Hero en zona media-inferior
        card_top = 800
        card_bottom = 1360
        draw.rounded_rectangle([70, card_top, width - 70, card_bottom], radius=24, fill=(primary_rgb[0] // 2, primary_rgb[1] // 2, primary_rgb[2] // 2), outline=(accent_rgb[0]//2, accent_rgb[1]//2, accent_rgb[2]//2), width=2)
        
        # Icono / Decoración en la tarjeta
        draw.text((110, card_top + 40), f"✦  ESTÉTICA & PRECISIÓN DIGITAL", fill=accent_rgb)
        draw.text((110, card_top + 100), f"Lámina {slide_num} de {total_slides} — {brand_name}", fill=(255, 255, 255))
        draw.text((110, card_top + 150), "Deslizá para continuar viendo el caso clínico ➔", fill=(148, 163, 184))

        # Paginador en la parte inferior
        dots_y = 1430
        dot_spacing = 30
        start_x = width // 2 - (total_slides * dot_spacing) // 2
        for d in range(1, total_slides + 1):
            cx = start_x + d * dot_spacing
            if d == slide_num:
                draw.ellipse([cx - 7, dots_y - 7, cx + 7, dots_y + 7], fill=accent_rgb)
            else:
                draw.ellipse([cx - 4, dots_y - 4, cx + 4, dots_y + 4], fill=(100, 116, 139))

        # Footer
        draw.text((70, 1470), "TecnoGen Studio AI — Multi-Format Engine", fill=(71, 85, 105))

        out_buffer = io.BytesIO()
        img.save(out_buffer, format="PNG", quality=95)
        return out_buffer.getvalue()
