import os
import sys
import io
import re
import json
import base64
import logging
from datetime import datetime
from PIL import Image
import pillow_heif
from openai import OpenAI
from dotenv import load_dotenv

# Configurar entorno
load_dotenv()
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.database import SessionLocal
from app.models.user import User
from app.models.brand import Brand
from app.models.content import Content, Slide
from app.models.setting import AISetting
from app.services.ai_image_service import AIImageService
from app.services.google_automation_service import GoogleAutomationService

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger("DualMethodRunner")

def convert_heif_to_jpeg(heif_bytes: bytes) -> bytes:
    try:
        heif_file = pillow_heif.read_heif(heif_bytes)
        img = Image.frombytes(heif_file.mode, heif_file.size, heif_file.data, "raw")
        # Redimensionar ligeramente si es gigante para que la API de OpenAI la procese rápido
        if max(img.size) > 2048:
            img.thumbnail((2048, 2048), Image.Resampling.LANCZOS)
        buf = io.BytesIO()
        img.save(buf, format="JPEG", quality=90)
        return buf.getvalue()
    except Exception as e:
        logger.error(f"Error convirtiendo HEIF a JPEG: {e}")
        return heif_bytes

def get_base64_data_url(raw_bytes: bytes, mime: str = "image/jpeg") -> str:
    b64 = base64.b64encode(raw_bytes).decode("utf-8")
    return f"data:{mime};base64,{b64}"

def run():
    db = SessionLocal()
    brand = db.query(Brand).first()
    user = db.query(User).first()
    
    if not brand or not user:
        logger.error("No se encontró Marca o Usuario en la base de datos.")
        return

    g_svc = GoogleAutomationService()
    
    # 1. Obtener Guion del Carrusel #2
    jobs = g_svc.read_sheet_jobs(brand.sheets_url, tab_name="Carruseles")
    # Buscar el trabajo Nro 2 o Row 3
    target_job = None
    for j in jobs:
        if str(j.get("nro")) == "2" or "3 frases sobre sacarte una muela" in j.get("titulo", ""):
            target_job = j
            break
    
    if not target_job:
        logger.error("No se encontró el carrusel #2 en el Google Sheet.")
        return

    logger.info(f"Procesando Carrusel: '{target_job['titulo']}' para Dra. {target_job['doctora_ref']}")

    # 2. Estructurar los 8 slides desde el guión literal
    raw_slides = [
        {
            "slide_number": 1,
            "slide_type": "cover",
            "badge": "MITO O VERDAD",
            "headline": "3 frases sobre sacarte una muela. ¿Mito o verdad? 🦷",
            "body": "",
            "show_doctor": True
        },
        {
            "slide_number": 2,
            "slide_type": "content",
            "badge": "FRASE 1",
            "headline": "Frase 1",
            "body": "«Si ya te dijeron que hay que sacarla, no hay vuelta atrás»",
            "show_doctor": False
        },
        {
            "slide_number": 3,
            "slide_type": "content",
            "badge": "REALIDAD CLÍNICA",
            "headline": "❌ MITO",
            "body": "Casi siempre se puede pedir una segunda opinión antes de decidir.",
            "show_doctor": False
        },
        {
            "slide_number": 4,
            "slide_type": "content",
            "badge": "FRASE 2",
            "headline": "Frase 2",
            "body": "«Un tratamiento de conducto duele más que la extracción»",
            "show_doctor": False
        },
        {
            "slide_number": 5,
            "slide_type": "content",
            "badge": "REALIDAD CLÍNICA",
            "headline": "❌ MITO",
            "body": "Con anestesia moderna, el malestar es similar.",
            "show_doctor": False
        },
        {
            "slide_number": 6,
            "slide_type": "content",
            "badge": "FRASE 3",
            "headline": "Frase 3",
            "body": "«Pedir otra opinión es perder el tiempo, si ya me dieron el diagnóstico»",
            "show_doctor": False
        },
        {
            "slide_number": 7,
            "slide_type": "content",
            "badge": "REALIDAD CLÍNICA",
            "headline": "❌ MITO",
            "body": "Puede ser justo lo que salva tu diente.",
            "show_doctor": False
        },
        {
            "slide_number": 8,
            "slide_type": "cta",
            "badge": "RESPONDE DRA. KARINA",
            "headline": "¿Cuántas sabías?",
            "body": "Contanos en los comentarios o agendá tu consulta de evaluación 🦷👇",
            "show_doctor": True
        }
    ]

    total_slides = len(raw_slides)
    brand_name = brand.name or "JM Odontología Integral"
    primary_color = brand.primary_color or "#16345F"
    accent_color = brand.accent_color or "#7DD3FC"
    bg_color = brand.bg_color or "#0B1E38"

    # 3. Descargar Foto real de Karina y Logo oficial
    logger.info("Descargando foto oficial de Karina y Logo oficial desde Google Drive...")
    karina_raw_bytes = g_svc.get_brand_subject_bytes(brand, "Karina")
    logo_raw_bytes = g_svc.get_brand_logo_bytes(brand)

    karina_jpeg_bytes = convert_heif_to_jpeg(karina_raw_bytes) if karina_raw_bytes else None
    
    # Inicializar OpenAI Image Service (Sunburst)
    ai_setting = db.query(AISetting).filter(AISetting.is_active == True, AISetting.category == "image").first()
    model_name = ai_setting.model_name if ai_setting else "gpt-image-2.5-sunburst"
    api_key = ai_setting.api_key_override if (ai_setting and ai_setting.api_key_override) else os.getenv("OPENAI_API_KEY")
    
    image_service = AIImageService(api_key=api_key, model=model_name)
    client = OpenAI(api_key=api_key)

    # -------------------------------------------------------------
    # MÉTODO A: VISIÓN FISONÓMICA DETALLADA (Prompt Engineering Puro)
    # -------------------------------------------------------------
    logger.info("==================================================")
    logger.info("INICIANDO MÉTODO A: VISIÓN FISONÓMICA DETALLADA")
    logger.info("==================================================")

    doctora_fisonomia = (
        "Foto hiperrealista de la Dra. Karina, odontóloga profesional de aproximadamente 35 años, "
        "tez clara, cabello castaño oscuro lacio y suelto a los hombros, mirada empática y cálida, "
        "maquillaje natural, sonrisa elegante con dientes blancos impecables. Viste un ambo médico azul marino "
        "con cuello en V con sutil ribete rosa/magenta interno, en consultorio odontológico moderno y luminoso con iluminación de estudio."
    )

    prompts_metodo_a = []
    for s in raw_slides:
        num = s["slide_number"]
        badge = s["badge"]
        headline = s["headline"]
        body = s["body"]
        show_doc = s["show_doctor"]

        if num == 1:
            p = (
                f"Diseño de portada para carrusel de Instagram en formato vertical 4:5 (1080x1350 px) de '{brand_name}'. "
                f"Composición publicitaria premium dividida armónicamente. "
                f"Lado izquierdo: Bloque tipográfico con fondo azul marino ({primary_color}) y acentos en celeste brillante ({accent_color}). "
                f"Badge superior en pastilla luminosa: '{badge}'. "
                f"Título principal en tipografía sans-serif audaz y limpia: '{headline}'. "
                f"Paginador interactivo con línea conectada '< 1/{total_slides} >' en esquina superior derecha. "
                f"Logo oficial blanco y celeste en esquina inferior izquierda: 'JM Odontología Integral'. "
                f"Lado derecho: {doctora_fisonomia} "
                f"Estilo: Pieza publicitaria médica de alta gama, sobria, sin textos extras, ultra nítida."
            )
        elif num == total_slides:
            p = (
                f"Lámina final de llamada a la acción ({num}/{total_slides}) en formato vertical 4:5 para '{brand_name}'. "
                f"Fondo: Azul marino ({primary_color}) con detalles en celeste ({accent_color}). "
                f"Lado derecho o primer plano: {doctora_fisonomia} con gesto cordial y cercano invitando al diálogo. "
                f"Lado izquierdo: Badge '{badge}', Título '{headline}', Botón/Llamado a la acción: '{body}'. "
                f"Paginador: Indicador interactivo '{num}/{total_slides}'. "
                f"Logo oficial en esquina inferior: 'JM Odontología Integral'. Diseño premium y profesional."
            )
        else:
            is_myth = "MITO" in headline or "MITO" in body
            p = (
                f"Lámina educativa {num} de {total_slides} en formato vertical 4:5 para '{brand_name}'. "
                f"Fondo: Azul profundo ({bg_color}) con sutiles acentos geométricos en celeste ({accent_color}). "
                f"Badge superior: '{badge}'. "
                f"Título destacado en negrita: '{headline}'. "
                f"Cuerpo explicativo en español claro: '{body}'. "
                f"Visual central: Renderizado 3D de precisión médica {'mostrando anatomía dental y protección del diente' if is_myth else 'mostrando instrumental y tecnología odontológica moderna'}. "
                f"Paginador con línea conectada '{num}/{total_slides}'. "
                f"Logo minimalista en esquina inferior izquierda. Tipografía editorial de alto impacto y contraste."
            )
        prompts_metodo_a.append(p)

    # Crear Contenido en BD para Método A
    content_a = Content(
        brand_id=brand.id,
        title=f"[Método A - Visión Fisonómica] {target_job['titulo']}",
        type="carousel",
        status="generating",
        total_slides=total_slides,
        hook_text="Karina",
        caption_copy=target_job.get("copy_instagram") or "3 frases sobre sacarte una muela que necesitás conocer. ¿Mito o verdad? Deslizá para descubrirlas.",
        hashtags="#JMOdontologia #SaludBucal #MitosDentales #OdontologiaMontevideo"
    )
    db.add(content_a)
    db.commit()
    db.refresh(content_a)

    # Crear carpeta en Google Drive para Método A
    drive_folder_a = None
    if brand.gdrive_output_folder_id and g_svc.is_ready:
        try:
            drive_folder_a = g_svc.create_drive_folder(
                brand.gdrive_output_folder_id, 
                f"[Carrusel Método A - Fisonómico] Sacarte una Muela - {datetime.now().strftime('%Y-%m-%d %H%M')}"
            )
        except Exception as e:
            logger.warning(f"Error creando carpeta Drive Método A: {e}")

    # Generar slides Método A
    for i, (s_data, prompt) in enumerate(zip(raw_slides, prompts_metodo_a), start=1):
        logger.info(f"[Método A] Generando Slide {i}/{total_slides}...")
        try:
            img_bytes = image_service.generate_slide_image(
                prompt=prompt,
                slide_info=s_data,
                brand_info={"name": brand_name, "primary_color": primary_color, "accent_color": accent_color, "doctor": "Karina"}
            )
            data_uri = get_base64_data_url(img_bytes, "image/png")
            
            drive_url = None
            if drive_folder_a and g_svc.is_ready:
                up_res = g_svc.upload_file_bytes(drive_folder_a, f"Slide_{i}_MetodoA.png", img_bytes)
                if up_res:
                    drive_url = up_res.get("url")

            slide_rec = Slide(
                content_id=content_a.id,
                slide_number=i,
                slide_type=s_data["slide_type"],
                image_url=data_uri,
                headline=s_data["headline"],
                body_text=s_data["body"],
                badge=s_data["badge"],
                prompt_used=prompt,
                gdrive_file_id=drive_url,
                status="generated",
                version=1
            )
            db.add(slide_rec)
            db.commit()
            logger.info(f"[Método A] Slide {i} generado exitosamente.")
        except Exception as e:
            logger.error(f"[Método A] Error generando slide {i}: {e}")

    content_a.status = "ready_for_review"
    db.commit()
    logger.info(f"✅ Método A completado. ID: {content_a.id}")

    # -------------------------------------------------------------
    # MÉTODO B: MULTIMODAL DIRECTO (GPT-4o Vision + Sunburst)
    # -------------------------------------------------------------
    logger.info("==================================================")
    logger.info("INICIANDO MÉTODO B: MULTIMODAL DIRECTO")
    logger.info("==================================================")

    # Preparar imágenes en base64 para GPT-4o
    multimodal_content = []
    multimodal_content.append({
        "type": "text",
        "text": (
            "Eres un Director de Arte y Prompt Engineer de élite. Analiza detalladamente la fotografía real adjunta de la odontóloga "
            "(Dra. Karina) y el logo oficial de 'JM Odontología Integral'.\n"
            "Debes generar exactamente 8 prompts en español para el modelo de generación de imágenes de alta gama (formato vertical 4:5 1080x1350 px) "
            "para un carrusel de Instagram sobre: '3 frases sobre sacarte una muela. ¿Mito o verdad? 🦷'.\n\n"
            "REGLAS CRUCIALES:\n"
            "1. En las láminas con la doctora (Slide 1 y Slide 8), transcribe con máxima precisión sus rasgos fisonómicos exactos observados en la foto "
            "(rostro, peinado castaño, ambo médico azul marino con ribete rosa en V, sonrisa, edad) para que la IA la replique idéntica.\n"
            "2. En todas las láminas, integra el logo blanco/celeste de JM en esquina inferior izquierda, fondo azul marino (#16345F / #0B1E38), detalles en celeste (#7DD3FC) y el paginador interactivo con línea conectada.\n"
            "3. Guion exacto de las 8 láminas:\n"
            "- Slide 1 (Portada): Badge 'MITO O VERDAD' | Título: '3 frases sobre sacarte una muela. ¿Mito o verdad? 🦷' | Dra. Karina en portada.\n"
            "- Slide 2: Badge 'FRASE 1' | Título: 'Frase 1' | Texto: '«Si ya te dijeron que hay que sacarla, no hay vuelta atrás»'\n"
            "- Slide 3: Badge 'REALIDAD CLÍNICA' | Título: '❌ MITO' | Texto: 'Casi siempre se puede pedir una segunda opinión antes de decidir.'\n"
            "- Slide 4: Badge 'FRASE 2' | Título: 'Frase 2' | Texto: '«Un tratamiento de conducto duele más que la extracción»'\n"
            "- Slide 5: Badge 'REALIDAD CLÍNICA' | Título: '❌ MITO' | Texto: 'Con anestesia moderna, el malestar es similar.'\n"
            "- Slide 6: Badge 'FRASE 3' | Título: 'Frase 3' | Texto: '«Pedir otra opinión es perder el tiempo, si ya me dieron el diagnóstico»'\n"
            "- Slide 7: Badge 'REALIDAD CLÍNICA' | Título: '❌ MITO' | Texto: 'Puede ser justo lo que salva tu diente.'\n"
            "- Slide 8 (Cierre): Badge 'RESPONDE DRA. KARINA' | Título: '¿Cuántas sabías?' | Texto: 'Contanos en los comentarios o agendá tu consulta de evaluación 🦷👇' | Dra. Karina en cierre.\n\n"
            "Responde ÚNICAMENTE un JSON con formato: {\"prompts\": [\"prompt slide 1\", \"prompt slide 2\", ...]}"
        )
    })

    if karina_jpeg_bytes:
        multimodal_content.append({
            "type": "image_url",
            "image_url": {
                "url": get_base64_data_url(karina_jpeg_bytes, "image/jpeg"),
                "detail": "high"
            }
        })
    if logo_raw_bytes:
        multimodal_content.append({
            "type": "image_url",
            "image_url": {
                "url": get_base64_data_url(logo_raw_bytes, "image/png"),
                "detail": "high"
            }
        })

    logger.info("Consultando GPT-4o Multimodal para análisis visual y redacción de prompts exactos...")
    gpt4o_res = client.chat.completions.create(
        model="gpt-4o",
        messages=[{"role": "user", "content": multimodal_content}],
        response_format={"type": "json_object"},
        temperature=0.4
    )
    
    gpt4o_json = json.loads(gpt4o_res.choices[0].message.content)
    prompts_metodo_b = gpt4o_json.get("prompts", [])
    logger.info(f"Prompts generados por GPT-4o Multimodal: {len(prompts_metodo_b)}")

    # Crear Contenido en BD para Método B
    content_b = Content(
        brand_id=brand.id,
        title=f"[Método B - Multimodal Directo] {target_job['titulo']}",
        type="carousel",
        status="generating",
        total_slides=total_slides,
        hook_text="Karina",
        caption_copy=target_job.get("copy_instagram") or "3 frases sobre sacarte una muela que necesitás conocer. ¿Mito o verdad? Deslizá para descubrirlas.",
        hashtags="#JMOdontologia #SaludBucal #MitosDentales #OdontologiaMontevideo"
    )
    db.add(content_b)
    db.commit()
    db.refresh(content_b)

    # Crear carpeta en Google Drive para Método B
    drive_folder_b = None
    if brand.gdrive_output_folder_id and g_svc.is_ready:
        try:
            drive_folder_b = g_svc.create_drive_folder(
                brand.gdrive_output_folder_id, 
                f"[Carrusel Método B - Multimodal] Sacarte una Muela - {datetime.now().strftime('%Y-%m-%d %H%M')}"
            )
        except Exception as e:
            logger.warning(f"Error creando carpeta Drive Método B: {e}")

    # Generar slides Método B
    for i, (s_data, prompt) in enumerate(zip(raw_slides, prompts_metodo_b), start=1):
        logger.info(f"[Método B] Generando Slide {i}/{total_slides}...")
        try:
            img_bytes = image_service.generate_slide_image(
                prompt=prompt,
                slide_info=s_data,
                brand_info={"name": brand_name, "primary_color": primary_color, "accent_color": accent_color, "doctor": "Karina"}
            )
            data_uri = get_base64_data_url(img_bytes, "image/png")
            
            drive_url = None
            if drive_folder_b and g_svc.is_ready:
                up_res = g_svc.upload_file_bytes(drive_folder_b, f"Slide_{i}_MetodoB.png", img_bytes)
                if up_res:
                    drive_url = up_res.get("url")

            slide_rec = Slide(
                content_id=content_b.id,
                slide_number=i,
                slide_type=s_data["slide_type"],
                image_url=data_uri,
                headline=s_data["headline"],
                body_text=s_data["body"],
                badge=s_data["badge"],
                prompt_used=prompt,
                gdrive_file_id=drive_url,
                status="generated",
                version=1
            )
            db.add(slide_rec)
            db.commit()
            logger.info(f"[Método B] Slide {i} generado exitosamente.")
        except Exception as e:
            logger.error(f"[Método B] Error generando slide {i}: {e}")

    content_b.status = "ready_for_review"
    db.commit()
    logger.info(f"✅ Método B completado. ID: {content_b.id}")

    # Actualizar Google Sheet con el estado y links de los dos métodos
    if g_svc.is_ready and brand.sheets_url:
        preview_a = f"https://studio.tecnogen.ar/app/viewer/{content_a.id}"
        preview_b = f"https://studio.tecnogen.ar/app/viewer/{content_b.id}"
        combined_links = f"Método A: {preview_a} | Método B: {preview_b}"
        try:
            g_svc.update_sheet_row_status(
                sheet_url=brand.sheets_url,
                row_number=target_job["row_number"],
                estado="Ya realizado",
                content_id=f"{content_a.id} / {content_b.id}",
                preview_url=combined_links,
                tab_name="Carruseles"
            )
            logger.info("Google Sheet actualizado exitosamente con ambos links de preview.")
        except Exception as e_sheet:
            logger.warning(f"Error actualizando Sheet: {e_sheet}")

    print("\n" + "="*70)
    print("PROCESAMIENTO DUAL COMPLETADO EXITOSAMENTE")
    print(f"Método A (Visión Fisonómica): https://studio.tecnogen.ar/app/viewer/{content_a.id}")
    print(f"Método B (Multimodal Directo): https://studio.tecnogen.ar/app/viewer/{content_b.id}")
    print("="*70 + "\n")

if __name__ == "__main__":
    run()
