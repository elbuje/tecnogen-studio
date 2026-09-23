from PIL import Image
import io
import logging

logger = logging.getLogger(__name__)

def compose_brand_logo(
    slide_image_bytes: bytes,
    logo_image_bytes: bytes,
    position: str = "top-left",
    logo_width: int = 180,
    margin: int = 40
) -> bytes:
    """
    Superpone el logo oficial de la marca sobre la imagen generada por IA.
    Usa composición alfa (RGBA) para preservar transparencia y nitidez.
    """
    try:
        slide = Image.open(io.BytesIO(slide_image_bytes)).convert("RGBA")
        logo = Image.open(io.BytesIO(logo_image_bytes)).convert("RGBA")
        
        # Redimensionar logo manteniendo proporción
        aspect_ratio = logo.height / logo.width
        logo_height = max(1, int(logo_width * aspect_ratio))
        logo = logo.resize((logo_width, logo_height), Image.Resampling.LANCZOS)
        
        # Coordenadas
        positions = {
            "top-left": (margin, margin),
            "top-right": (slide.width - logo_width - margin, margin),
            "top-center": ((slide.width - logo_width) // 2, margin),
            "bottom-left": (margin, slide.height - logo_height - margin),
            "bottom-right": (slide.width - logo_width - margin, slide.height - logo_height - margin),
        }
        coords = positions.get(position, positions["top-left"])
        
        # Pegar usando el canal alfa del logo como máscara
        slide.paste(logo, coords, mask=logo)
        
        output = io.BytesIO()
        slide.save(output, format="PNG", optimize=True)
        return output.getvalue()
    except Exception as e:
        logger.error(f"Error en compose_brand_logo: {e}")
        return slide_image_bytes
