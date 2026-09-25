import os
import io
import logging
from typing import Optional, Dict, Any, Tuple
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageOps
import pillow_heif

logger = logging.getLogger(__name__)

# Registrar soporte HEIF/HIF en Pillow
try:
    pillow_heif.register_heif_opener()
except Exception as e:
    logger.warning(f"Aviso registrando pillow_heif: {e}")

def get_font(size: int, bold: bool = False, serif: bool = False) -> ImageFont.FreeTypeFont:
    """Obtiene tipografía del sistema con fallback seguro."""
    font_paths = []
    if serif:
        font_paths = [
            "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf",
            "/usr/share/fonts/truetype/liberation/LiberationSerif-Bold.ttf" if bold else "/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf"
        ]
    else:
        font_paths = [
            "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
            "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
            "/usr/share/fonts/truetype/freefont/FreeSansBold.ttf" if bold else "/usr/share/fonts/truetype/freefont/FreeSans.ttf"
        ]

    for p in font_paths:
        if os.path.exists(p):
            try:
                return ImageFont.truetype(p, size)
            except Exception:
                pass
    return ImageFont.load_default()

def hex_to_rgb(h: str, default: Tuple[int, int, int] = (22, 52, 95)) -> Tuple[int, int, int]:
    if not h:
        return default
    h = h.lstrip('#')
    if len(h) == 6:
        try:
            return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))
        except ValueError:
            return default
    return default

def draw_rounded_rect(draw: ImageDraw.ImageDraw, coords, radius: int, fill=None, outline=None, width=1):
    draw.rounded_rectangle(coords, radius=radius, fill=fill, outline=outline, width=width)

def wrap_text(text: str, font: ImageFont.FreeTypeFont, max_width: int, draw: ImageDraw.ImageDraw) -> list:
    """Envuelve texto respetando el ancho máximo en píxeles."""
    words = text.split()
    if not words:
        return []
    lines = []
    curr_line = words[0]
    for w in words[1:]:
        test_line = f"{curr_line} {w}"
        bbox = draw.textbbox((0, 0), test_line, font=font)
        if (bbox[2] - bbox[0]) <= max_width:
            curr_line = test_line
        else:
            lines.append(curr_line)
            curr_line = w
    lines.append(curr_line)
    return lines

def decode_image_bytes(image_bytes: bytes) -> Optional[Image.Image]:
    """Decodifica bytes a PIL Image soportando PNG, JPEG, WEBP, HIF y HEIC."""
    if not image_bytes:
        return None
    try:
        # Intentar apertura estándar
        return Image.open(io.BytesIO(image_bytes)).convert("RGBA")
    except Exception:
        try:
            heif_file = pillow_heif.read_heif(image_bytes)
            img = Image.frombytes(heif_file.mode, heif_file.size, heif_file.data, "raw")
            return img.convert("RGBA")
        except Exception as e:
            logger.error(f"Error decodificando imagen: {e}")
            return None

def compose_brand_logo(
    slide_image: Image.Image,
    logo_image_bytes: bytes,
    position: str = "top-left",
    logo_width: int = 220,
    margin: int = 50
) -> Image.Image:
    """Superpone el logo oficial de la marca."""
    if not logo_image_bytes:
        return slide_image
    try:
        logo = decode_image_bytes(logo_image_bytes)
        if not logo:
            return slide_image

        # Redimensionar logo proporcionalmente
        aspect = logo.height / logo.width
        logo_height = max(1, int(logo_width * aspect))
        logo = logo.resize((logo_width, logo_height), Image.Resampling.LANCZOS)

        # Posiciones
        w, h = slide_image.size
        positions = {
            "top-left": (margin, margin),
            "top-right": (w - logo_width - margin, margin),
            "top-center": ((w - logo_width) // 2, margin),
            "bottom-left": (margin, h - logo_height - margin),
            "bottom-right": (w - logo_width - margin, h - logo_height - margin),
        }
        coords = positions.get(position, positions["top-left"])

        slide_image.paste(logo, coords, mask=logo)
        return slide_image
    except Exception as e:
        logger.error(f"Error superponiendo logo: {e}")
        return slide_image

def render_slide_composite(
    slide_num: int,
    total_slides: int,
    slide_data: Dict[str, Any],
    brand_info: Dict[str, Any],
    background_bytes: Optional[bytes] = None,
    logo_bytes: Optional[bytes] = None,
    subject_bytes: Optional[bytes] = None
) -> bytes:
    """
    Motor Maestro de Composición Determinista (Pillow RGBA HD - 1080x1350 Instagram Portrait).
    Garantiza:
    1. Numeración clara de láminas (ej: 1/8, 2/8) en pastilla destacada.
    2. Logo oficial de la marca en alta resolución.
    3. Foto real del profesional/sujeto de Google Drive con marco pulido si aplica.
    4. Tipografía en español con la paleta cromática del Manual de Marca (Relax Blue, Sky Pulse, White).
    5. Cero carteles en inglés o basura generativa.
    """
    width, height = 1080, 1350
    primary_rgb = hex_to_rgb(brand_info.get("primary_color"), (22, 52, 95))     # #16345F
    accent_rgb = hex_to_rgb(brand_info.get("accent_color"), (125, 211, 252))   # #7DD3FC
    bg_rgb = hex_to_rgb(brand_info.get("bg_color"), (29, 29, 27))              # #1D1D1B
    brand_name = brand_info.get("name") or "JM Odontología Integral"

    slide_type = slide_data.get("slide_type", "content")
    headline = (slide_data.get("headline") or slide_data.get("title") or "").strip()
    body_text = (slide_data.get("body_text") or slide_data.get("body") or "").strip()
    badge_text = (slide_data.get("badge") or f"PASO {slide_num}").upper()

    # 1. Base Canvas
    canvas = Image.new("RGBA", (width, height), (*bg_rgb, 255))
    
    # 2. Fondo (Foto IA limpia o Degradado Profesional)
    if background_bytes:
        bg_img = decode_image_bytes(background_bytes)
        if bg_img:
            bg_img = ImageOps.fit(bg_img, (width, height), Image.Resampling.LANCZOS)
            # Aplicar velo oscuro elegante para garantizar contraste tipográfico 100% nítido
            overlay_dark = Image.new("RGBA", (width, height), (15, 23, 42, 160))
            canvas.paste(bg_img, (0, 0))
            canvas.paste(overlay_dark, (0, 0), mask=overlay_dark)
    else:
        # Degradado vertical Relax Blue -> Core Black
        grad = Image.new("RGBA", (width, height), (0, 0, 0, 0))
        draw_grad = ImageDraw.Draw(grad)
        for y in range(height):
            ratio = y / height
            r = int(primary_rgb[0] * (1 - ratio * 0.7))
            g = int(primary_rgb[1] * (1 - ratio * 0.7))
            b = int(primary_rgb[2] * (1 - ratio * 0.7))
            draw_grad.line([(0, y), (width, y)], fill=(r, g, b, 255))
        canvas.paste(grad, (0, 0), mask=grad)

    # 3. Foto de Sujeto Real (Jessica / Doctora de Google Drive) si existe
    if subject_bytes:
        subj_img = decode_image_bytes(subject_bytes)
        if subj_img:
            # Encuadrar en tarjeta vertical flotante
            card_w, card_h = 420, 560
            subj_fit = ImageOps.fit(subj_img, (card_w, card_h), Image.Resampling.LANCZOS)
            
            # Crear máscara redondeada
            mask = Image.new("L", (card_w, card_h), 0)
            mask_draw = ImageDraw.Draw(mask)
            mask_draw.rounded_rectangle([0, 0, card_w, card_h], radius=32, fill=255)
            
            # Marco contenedor con borde Sky Pulse
            border_card = Image.new("RGBA", (card_w + 12, card_h + 12), (*accent_rgb, 255))
            b_mask = Image.new("L", (card_w + 12, card_h + 12), 0)
            ImageDraw.Draw(b_mask).rounded_rectangle([0, 0, card_w + 12, card_h + 12], radius=38, fill=255)
            
            # Posicionar foto según tipo de lámina
            if slide_type == "cover" or slide_num == 1:
                subj_x = width - card_w - 60
                subj_y = height // 2 - card_h // 2 + 60
            else:
                subj_x = width - card_w - 50
                subj_y = height - card_h - 140
                
            canvas.paste(border_card, (subj_x - 6, subj_y - 6), mask=b_mask)
            canvas.paste(subj_fit, (subj_x, subj_y), mask=mask)

    # 4. Capa de Dibujo Vectorial y Tipográfico
    draw = ImageDraw.Draw(canvas)

    # --- Header: Logo & Slide Counter (ej: 1/8) ---
    # Numeración de Lámina (Top Right Pill)
    counter_text = f"{slide_num} / {total_slides}"
    counter_font = get_font(28, bold=True)
    c_bbox = draw.textbbox((0, 0), counter_text, font=counter_font)
    c_w = c_bbox[2] - c_bbox[0] + 36
    c_h = 50
    c_x = width - c_w - 50
    c_y = 50
    
    # Pill de numeración
    draw_rounded_rect(draw, [c_x, c_y, c_x + c_w, c_y + c_h], radius=25, fill=(*primary_rgb, 230), outline=(*accent_rgb, 255), width=2)
    draw.text((c_x + 18, c_y + 10), counter_text, font=counter_font, fill=(255, 255, 255, 255))

    # Superponer Logo Oficial (Top Left)
    if logo_bytes:
        canvas = compose_brand_logo(canvas, logo_bytes, position="top-left", logo_width=210, margin=50)
        draw = ImageDraw.Draw(canvas)
    else:
        # Fallback tipográfico del nombre de marca
        brand_font = get_font(30, bold=True)
        draw.text((50, 55), brand_name, font=brand_font, fill=(*accent_rgb, 255))

    # --- Badge de Tema / Paso ---
    badge_font = get_font(22, bold=True)
    b_bbox = draw.textbbox((0, 0), badge_text, font=badge_font)
    b_w = b_bbox[2] - b_bbox[0] + 32
    b_h = 44
    b_x = 50
    b_y = 150
    draw_rounded_rect(draw, [b_x, b_y, b_x + b_w, b_y + b_h], radius=14, fill=(*accent_rgb, 40), outline=(*accent_rgb, 200), width=1)
    draw.text((b_x + 16, b_y + 9), badge_text, font=badge_font, fill=(*accent_rgb, 255))

    # --- Contenido Central (Headline & Body Text) ---
    max_text_width = width - 100 if not subject_bytes else (width - 520)

    if slide_type == "cover" or slide_num == 1:
        # Portada Impactante
        title_font = get_font(56, bold=True, serif=True)
        title_lines = wrap_text(headline or brand_name, title_font, max_text_width, draw)
        
        # Tarjeta de fondo de texto frosted glass
        card_top = 230
        line_h = 70
        card_height = len(title_lines) * line_h + 120
        draw_rounded_rect(draw, [50, card_top, 50 + max_text_width + 40, card_top + card_height], radius=24, fill=(15, 23, 42, 220), outline=(*primary_rgb, 200), width=2)
        
        cur_y = card_top + 40
        for l in title_lines:
            draw.text((70, cur_y), l, font=title_font, fill=(255, 255, 255, 255))
            cur_y += line_h

        # Footer de Portada: "Deslizá ➔"
        swipe_box_w, swipe_box_h = 280, 56
        swipe_x = 50
        swipe_y = height - 120
        draw_rounded_rect(draw, [swipe_x, swipe_y, swipe_x + swipe_box_w, swipe_y + swipe_box_h], radius=28, fill=(*accent_rgb, 255))
        swipe_font = get_font(22, bold=True)
        draw.text((swipe_x + 28, swipe_y + 14), "Deslizá para leer »", font=swipe_font, fill=(15, 23, 42, 255))

    elif slide_type == "cta" or slide_num == total_slides:
        # Lámina de Cierre & Llamado a la Acción
        card_x = 50
        card_y = 230
        card_w = width - 100
        card_h = height - 380
        draw_rounded_rect(draw, [card_x, card_y, card_x + card_w, card_y + card_h], radius=32, fill=(15, 23, 42, 235), outline=(*accent_rgb, 220), width=2)
        
        # Título de Cierre
        title_font = get_font(46, bold=True, serif=True)
        t_lines = wrap_text(headline or "Agendá tu Consulta", title_font, card_w - 80, draw)
        cur_y = card_y + 50
        for l in t_lines:
            draw.text((card_x + 40, cur_y), l, font=title_font, fill=(*accent_rgb, 255))
            cur_y += 60

        cur_y += 20
        # Cuerpo / Reflexión
        body_font = get_font(32, bold=False)
        b_lines = wrap_text(body_text, body_font, card_w - 80, draw)
        for l in b_lines:
            draw.text((card_x + 40, cur_y), l, font=body_font, fill=(240, 240, 240, 255))
            cur_y += 46

        # Botón / Pastilla de Acción
        cta_btn_w = card_w - 80
        cta_btn_h = 75
        cta_y = card_y + card_h - 110
        draw_rounded_rect(draw, [card_x + 40, cta_y, card_x + 40 + cta_btn_w, cta_y + cta_btn_h], radius=20, fill=(*accent_rgb, 255))
        cta_font = get_font(28, bold=True)
        cta_text = "Escribinos por DM o WhatsApp  ➔"
        cta_bbox = draw.textbbox((0, 0), cta_text, font=cta_font)
        cta_tw = cta_bbox[2] - cta_bbox[0]
        draw.text((card_x + 40 + (cta_btn_w - cta_tw) // 2, cta_y + 20), cta_text, font=cta_font, fill=(15, 23, 42, 255))

    else:
        # Lámina de Contenido (Pasos 2 a 7)
        card_x = 50
        card_y = 230
        card_w = max_text_width + 40
        
        # Calcular altura según texto
        body_font = get_font(34, bold=False)
        body_lines = wrap_text(body_text or headline, body_font, card_w - 60, draw)
        line_h = 50
        card_h = max(260, len(body_lines) * line_h + 100)
        
        draw_rounded_rect(draw, [card_x, card_y, card_x + card_w, card_y + card_h], radius=28, fill=(15, 23, 42, 230), outline=(*primary_rgb, 220), width=2)
        
        # Acento lateral Sky Pulse en la tarjeta
        draw_rounded_rect(draw, [card_x, card_y + 30, card_x + 8, card_y + card_h - 30], radius=4, fill=(*accent_rgb, 255))
        
        cur_y = card_y + 45
        for l in body_lines:
            draw.text((card_x + 35, cur_y), l, font=body_font, fill=(255, 255, 255, 255))
            cur_y += line_h

        # Indicador de paso en el footer
        footer_text = f"Paso {slide_num} de {total_slides}  ➔"
        footer_font = get_font(22, bold=True)
        draw.text((50, height - 90), footer_text, font=footer_font, fill=(*accent_rgb, 220))

    # Exportar en formato PNG optimizado
    output = io.BytesIO()
    canvas.save(output, format="PNG", optimize=True)
    return output.getvalue()
