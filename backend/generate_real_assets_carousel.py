import os
import sys
import io
import json
import base64
import logging
from datetime import datetime
from PIL import Image
import pillow_heif
from dotenv import load_dotenv

load_dotenv()
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.database import SessionLocal
from app.models.user import User
from app.models.brand import Brand
from app.models.content import Content, Slide
from app.models.setting import AISetting
from app.services.ai_image_service import AIImageService
from app.services.google_automation_service import GoogleAutomationService
from app.routers.contents import build_openai_slide_prompt

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger("RealAssetCarouselRunner")

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
    target_job = None
    for j in jobs:
        if str(j.get("nro")) == "2" or "3 frases sobre sacarte una muela" in j.get("titulo", ""):
            target_job = j
            break
    
    if not target_job:
        logger.error("No se encontró el carrusel #2 en el Google Sheet.")
        return

    logger.info(f"Procesando Carrusel con Assets Reales: '{target_job['titulo']}' para Dra. {target_job['doctora_ref']}")

    # 2. Descargar Foto real de Karina y Logo oficial
    logger.info("Descargando foto oficial de Karina y Logo oficial desde Google Drive...")
    karina_raw_bytes = g_svc.get_brand_subject_bytes(brand, "Karina")
    logo_raw_bytes = g_svc.get_brand_logo_bytes(brand)

    logger.info(f"Foto Karina: {len(karina_raw_bytes) if karina_raw_bytes else 0} bytes | Logo: {len(logo_raw_bytes) if logo_raw_bytes else 0} bytes")

    # 3. Estructurar las 8 diapositivas literales
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

    # 4. Inicializar AIImageService con gpt-image-2.5-sunburst
    ai_setting = db.query(AISetting).filter(AISetting.is_active == True, AISetting.category == "image").first()
    model_name = ai_setting.model_name if ai_setting else "gpt-image-2.5-sunburst"
    api_key = ai_setting.api_key_override if (ai_setting and ai_setting.api_key_override) else os.getenv("OPENAI_API_KEY")
    
    image_service = AIImageService(api_key=api_key, model=model_name)

    # 5. Crear Contenido en BD
    content = Content(
        brand_id=brand.id,
        title=f"[Oficial Sunburst Real] {target_job['titulo']}",
        type="carousel",
        status="generating",
        total_slides=total_slides,
        hook_text="Karina",
        caption_copy=target_job.get("copy_instagram") or "3 frases sobre sacarte una muela que necesitás conocer. ¿Mito o verdad? Deslizá para descubrirlas.",
        hashtags="#JMOdontologia #SaludBucal #MitosDentales #OdontologiaMontevideo"
    )
    db.add(content)
    db.commit()
    db.refresh(content)

    # 6. Crear subcarpeta en Google Drive
    drive_folder_id = None
    if brand.gdrive_output_folder_id and g_svc.is_ready:
        try:
            drive_folder_id = g_svc.create_drive_folder(
                brand.gdrive_output_folder_id,
                f"[Carrusel Sunburst Real] 3 frases sobre sacarte una muela - {datetime.now().strftime('%Y-%m-%d %H%M')}"
            )
        except Exception as e:
            logger.warning(f"Error creando carpeta en Drive: {e}")

    # 7. Generar las 8 láminas enviando foto real y logo
    for i, s_data in enumerate(raw_slides, start=1):
        logger.info(f"Generando Slide {i}/{total_slides} con gpt-image-2.5-sunburst y Assets Reales...")
        
        prompt = build_openai_slide_prompt(
            brand=brand,
            slide_num=i,
            total_slides=total_slides,
            slide_data=s_data,
            doctor_name="Karina",
            presencia_doctora=target_job.get("presencia_doctora") or "Portada y Cierre",
            estilo_paginador=target_job.get("estilo_paginador") or "Puntos y Flechas",
            badge_estilo=target_job.get("badge_estilo") or "Conceptos / Beneficios",
            idioma_prompts="Español"
        )

        try:
            img_bytes = image_service.generate_slide_image(
                prompt=prompt,
                slide_info=s_data,
                brand_info={
                    "name": brand_name,
                    "primary_color": primary_color,
                    "accent_color": accent_color,
                    "doctor": "Karina"
                },
                subject_bytes=karina_raw_bytes if s_data["show_doctor"] else None,
                logo_bytes=logo_raw_bytes
            )

            b64_str = base64.b64encode(img_bytes).decode("utf-8")
            data_uri = f"data:image/png;base64,{b64_str}"

            drive_url = None
            if drive_folder_id and g_svc.is_ready:
                up_res = g_svc.upload_file_bytes(drive_folder_id, f"Slide_{i}_Oficial.png", img_bytes)
                if up_res:
                    drive_url = up_res.get("url")

            slide_rec = Slide(
                content_id=content.id,
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
            logger.info(f"✅ Slide {i}/{total_slides} generado y guardado exitosamente.")
        except Exception as e:
            logger.error(f"❌ Error generando slide {i}: {e}")

    content.status = "ready_for_review"
    db.commit()
    logger.info(f"🎉 Carrusel completo generado con ID: {content.id}")

    # Sincronizar con Google Sheet
    if g_svc.is_ready and brand.sheets_url:
        preview_url = f"https://studio.tecnogen.ar/app/viewer/{content.id}"
        try:
            g_svc.update_sheet_row_status(
                sheet_url=brand.sheets_url,
                row_number=target_job["row_number"],
                estado="Ya realizado",
                content_id=content.id,
                preview_url=preview_url,
                tab_name="Carruseles"
            )
            logger.info(f"Google Sheet actualizado con link: {preview_url}")
        except Exception as e_s:
            logger.warning(f"Error actualizando Google Sheet: {e_s}")

    print("\n" + "="*70)
    print(f"CARRUSEL GENERADO CON ASSETS REALES: https://studio.tecnogen.ar/app/viewer/{content.id}")
    print("="*70 + "\n")

if __name__ == "__main__":
    run()
