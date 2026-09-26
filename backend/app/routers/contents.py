from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from typing import List, Optional, Dict, Any
import base64
import logging
import re
from pydantic import BaseModel
from app.database import get_db, SessionLocal
from app.models.user import User
from app.models.brand import Brand
from app.models.content import Content, Slide
from app.models.setting import AISetting
from app.schemas.content import ContentGenerateRequest, ContentOut, ContentGenerateResponse, SlideRegenerateRequest, SlideOut
from app.services.auth_service import get_current_user
from app.services.credit_service import charge_credits_atomic, refund_credits_atomic
from app.services.ai_image_service import AIImageService
from app.services.copy_service import generate_carousel_slides_copy, generate_post_caption

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/contents", tags=["Contenidos & Carruseles"])

class RegenerateAllRequest(BaseModel):
    global_feedback: Optional[str] = None

from app.services.google_automation_service import GoogleAutomationService
from datetime import datetime

def build_openai_slide_prompt(
    brand: Brand,
    slide_num: int,
    total_slides: int,
    slide_data: dict,
    doctor_name: str = "Karina",
    presencia_doctora: str = "Portada y Cierre",
    estilo_paginador: str = "Puntos y Flechas",
    badge_estilo: str = "Conceptos / Beneficios",
    idioma_prompts: str = "Español",
    global_feedback: Optional[str] = None
) -> str:
    slide_type = slide_data.get("slide_type", "content")
    headline = (slide_data.get("headline") or slide_data.get("title") or "").strip()
    body_text = (slide_data.get("body_text") or slide_data.get("body") or "").strip()
    badge = (slide_data.get("badge") or f"PASO {slide_num}").strip()
    brand_name = brand.name or "JM Odontología Integral"
    primary_color = brand.primary_color or "#16345F"
    accent_color = brand.accent_color or "#7DD3FC"
    bg_color = brand.bg_color or "#0B1E38"
    
    clean_doctor = "Dra. Karina"
    doctor_file = "Karina.HIF"
    doc_lower = (doctor_name or "").lower()
    if "luciana" in doc_lower:
        clean_doctor = "Dra. Luciana"
        doctor_file = "Luciana.JPG"
    elif "jessica" in doc_lower:
        clean_doctor = "Dra. Jessica"
        doctor_file = "Jessica.HIF"
    elif "karina" in doc_lower:
        clean_doctor = "Dra. Karina"
        doctor_file = "Karina.HIF"
    elif doctor_name:
        clean_doctor = f"Dra. {doctor_name.strip()}"
        doctor_file = f"{doctor_name.strip()}.JPG"

    # Determinar si la doctora debe aparecer en este slide según la regla configurada en el Sheet
    show_doctor = False
    pres_lower = presencia_doctora.lower()
    if "todas" in pres_lower:
        show_doctor = True
    elif "portada y cierre" in pres_lower:
        show_doctor = (slide_num == 1 or slide_num == total_slides or slide_type in ["cover", "cta"])
    elif "solo portada" in pres_lower:
        show_doctor = (slide_num == 1 or slide_type == "cover")
    elif "solo en cierre" in pres_lower:
        show_doctor = (slide_num == total_slides or slide_type == "cta")
    elif "ninguna" in pres_lower:
        show_doctor = False
    else:
        show_doctor = (slide_num == 1 or slide_num == total_slides)

    # Formatear Paginador según la regla del Sheet
    pag_lower = estilo_paginador.lower()
    if "línea" in pag_lower or "linea" in pag_lower or "conectada" in pag_lower:
        pag_desc = f'Paginador al pie: Indicador con línea conectada interactiva {slide_num}/{total_slides} y botón de deslizamiento.'
    elif "pastilla" in pag_lower:
        pag_desc = f'Paginador superior derecho: Pastilla de navegación elegante con texto "{slide_num}/{total_slides} >".'
    elif "puntos" in pag_lower or "flechas" in pag_lower:
        pag_desc = f'Paginador: Indicador con puntos de carrusel interactivos ● ○ ○ y flechas de navegación "< {slide_num}/{total_slides} >".'
    else:
        pag_desc = f'Paginador en esquina superior derecha: Texto simple "{slide_num}/{total_slides}".'

    feedback_instruction = f"Instrucción artística adicional: {global_feedback}. " if global_feedback else ""
    
    if slide_type == "cover" or slide_num == 1:
        visual_desc = (
            f'Lado derecho: Foto real de {clean_doctor} (usando asset oficial "/{clean_doctor.replace("Dra. ", "")}/{doctor_file}") '
            f'en ambo clínico azul marino, con expresión cálida, atenta y sonriente en consultorio dental moderno con iluminación de estudio.'
            if show_doctor else
            f'Visual central: Renderizado 3D de precisión médica y estética de sonrisa moderna de alta gama.'
        )
        prompt = (
            f"Diseño de portada para carrusel de Instagram en formato vertical 4:5 (1080x1350 px) de '{brand_name}'. "
            f"Lado izquierdo: Bloque gráfico con fondo azul marino ({primary_color}) y detalles en celeste luminoso ({accent_color}). "
            f"Logo oficial transparente exacto: '/Logos/JM_blanco_negro.png' en esquina inferior izquierda. "
            f'{visual_desc} '
            f'Badge superior: "{badge}". '
            f'Título principal en negrita: "{headline}". '
            f'{pag_desc} '
            f'Firma de marca: "{brand_name}". '
            f"Estilo: Pieza publicitaria premium, sin textos adicionales inventados, diseño limpio y profesional. {feedback_instruction}"
        )
    elif slide_type == "cta" or slide_num == total_slides:
        visual_desc = (
            f'Lado derecho: Foto real de {clean_doctor} (usando asset oficial "/{clean_doctor.replace("Dra. ", "")}/{doctor_file}") '
            f'en primer plano sonriendo con cercanía y amabilidad, invitando a la interacción.'
            if show_doctor else
            f'Visual: Composición estética y limpia con instrumental odontológico y tarjeta de consulta.'
        )
        prompt = (
            f"Lámina de cierre y llamada a la acción ({slide_num}/{total_slides}) en formato vertical 4:5 para '{brand_name}'. "
            f"Fondo: Azul marino ({primary_color}) con detalles en ({accent_color}). "
            f"Logo oficial: '/Logos/JM_blanco_negro.png' en esquina inferior izquierda. "
            f'{visual_desc} '
            f'Badge superior con ícono: "RESPONDE {clean_doctor.upper()}". '
            f'Título: "{headline}". '
            f'Llamado a la acción (CTA): "{body_text or "Dejanos tu consulta en los comentarios o agendá tu turno hoy mismo 👇"}". '
            f'{pag_desc} '
            f'Firma: "{brand_name}". {feedback_instruction}'
        )
    else:
        is_myth = "mito" in headline.lower() or "mito" in body_text.lower() or "❌" in headline or "❌" in body_text
        if show_doctor:
            visual_desc = (
                f'Elemento visual: {clean_doctor} (asset "/{clean_doctor.replace("Dra. ", "")}/{doctor_file}") '
                f'explicando en consultorio junto a una pantalla con modelo 3D de diagnóstico dental.'
            )
        elif is_myth:
            visual_desc = (
                f'Elemento visual: Renderizado 3D de precisión médica mostrando la pieza dental protegida y la estructura anatómica en alto detalle.'
            )
        else:
            visual_desc = (
                f'Elemento visual: Composición médica moderna con tecnología e instrumental odontológico de alta precisión sobre superficie mate.'
            )

        prompt = (
            f"Lámina educativa {slide_num} de {total_slides} en formato vertical 4:5 para '{brand_name}'. "
            f"Fondo: Azul marino ({bg_color}) con sutil marco y acentos en ({accent_color}). "
            f"Logo oficial: '/Logos/JM_blanco_negro.png' en esquina inferior izquierda. "
            f'{visual_desc} '
            f'Badge superior: "{badge}". '
            f'Título: "{headline}". '
            f'Cuerpo explicativo en español: "{body_text}". '
            f'{pag_desc} '
            f"Estilo: Tipografía de alto contraste, sobrio, estético y profesional. {feedback_instruction}"
        )

    return prompt.strip()

def process_content_generation(content_id: str, db_factory, global_feedback: Optional[str] = None):
    db = db_factory()
    try:
        content = db.query(Content).filter(Content.id == content_id).first()
        if not content:
            return
        
        content.status = "generating"
        db.commit()
        
        ai_setting = db.query(AISetting).filter(AISetting.is_active == True, AISetting.category == "image").first()
        model_name = ai_setting.model_name if ai_setting else "gpt-image-2.5-sunburst"
        api_key = ai_setting.api_key_override if (ai_setting and ai_setting.api_key_override) else None
        
        try:
            image_service = AIImageService(api_key=api_key, model=model_name)
        except Exception as e:
            logger.error(f"Error inicializando servicio OpenAI: {e}")
            content.status = "failed"
            db.commit()
            refund_credits_atomic(db, content.brand.user_id, content.total_slides, f"Rechazado: {str(e)}", content.id)
            return

        brand = content.brand
        doctor_name = content.hook_text or "Karina"
        
        # Descargar Logo oficial y Fotos de Personajes desde Google Drive
        try:
            g_svc = GoogleAutomationService()
            logo_bytes = g_svc.get_brand_logo_bytes(brand) if g_svc.is_ready else None
            subject_bytes = g_svc.get_brand_subject_bytes(brand, doctor_name) if g_svc.is_ready else None
        except Exception as e_drive:
            logger.warning(f"Aviso al obtener activos de Drive: {e_drive}")
            g_svc = None
            logo_bytes = None
            subject_bytes = None

        # Verificar si los slides ya vienen desglosados (ej. desde Google Sheet o BD)
        existing_slides = db.query(Slide).filter(Slide.content_id == content.id).order_by(Slide.slide_number).all()
        
        slides_copy = []
        if existing_slides and len(existing_slides) > 0 and any(s.headline or s.body_text for s in existing_slides):
            for s in existing_slides:
                slides_copy.append({
                    "slide_number": s.slide_number,
                    "slide_type": s.slide_type or "content",
                    "headline": s.headline or content.title,
                    "title": s.headline or content.title,
                    "body_text": s.body_text or "",
                    "body": s.body_text or "",
                    "badge": s.badge or f"PASO {s.slide_number}"
                })
        else:
            slides_copy = generate_carousel_slides_copy(
                topic=content.title,
                total_slides=content.total_slides,
                brand_name=brand.name,
                openai_client=image_service.client
            )

        # Generar caption y hashtags con IA si no vienen provistos
        if not content.caption_copy or "💡" in content.caption_copy or "Nuevo contenido generado" in content.caption_copy:
            try:
                caption_data = generate_post_caption(
                    topic=content.title,
                    brand_name=brand.name,
                    slides=slides_copy,
                    openai_client=image_service.client
                )
                content.caption_copy = caption_data.get("caption", content.caption_copy)
                content.hashtags = caption_data.get("hashtags", content.hashtags)
            except Exception as e:
                logger.warning(f"Error generando caption enriquecido: {e}")

        # Subcarpeta de Google Drive para este carrusel
        carousel_drive_folder_id = None
        if brand.gdrive_output_folder_id and g_svc and g_svc.is_ready:
            try:
                date_str = datetime.now().strftime("%Y-%m-%d")
                clean_title = re.sub(r'[^a-zA-Z0-9_\- ]', '', content.title)[:35]
                folder_name = f"[Carrusel] {clean_title} - {date_str}"
                carousel_drive_folder_id = g_svc.create_drive_folder(brand.gdrive_output_folder_id, folder_name)
            except Exception as e_f:
                logger.warning(f"No se pudo crear carpeta en Drive: {e_f}")
                carousel_drive_folder_id = brand.gdrive_output_folder_id

        failed_count = 0

        for i, slide_data in enumerate(slides_copy, start=1):
            slide_data["total_slides"] = len(slides_copy)
            slide_type = slide_data.get("slide_type", "content")
            
            headline = (slide_data.get("headline") or slide_data.get("title") or content.title).strip()
            body_text = (slide_data.get("body_text") or slide_data.get("body") or "").strip()
            badge = (slide_data.get("badge") or (f"PASO {i}" if i > 1 else "CASO CLÍNICO")).upper()

            slide_data["headline"] = headline
            slide_data["title"] = headline
            slide_data["body_text"] = body_text
            slide_data["body"] = body_text
            slide_data["badge"] = badge

            prompt = build_openai_slide_prompt(
                brand=brand,
                slide_num=i,
                total_slides=len(slides_copy),
                slide_data=slide_data,
                doctor_name=doctor_name,
                global_feedback=global_feedback
            )
            
            try:
                # Generar imagen directamente con el modelo OpenAI Sunburst
                img_bytes = image_service.generate_slide_image(
                    prompt=prompt,
                    slide_info=slide_data,
                    brand_info={
                        "name": brand.name or "JM Odontología Integral",
                        "primary_color": brand.primary_color or "#16345F",
                        "accent_color": brand.accent_color or "#7DD3FC",
                        "doctor": doctor_name
                    }
                )

                b64_str = base64.b64encode(img_bytes).decode('utf-8')
                data_uri = f"data:image/png;base64,{b64_str}"

                # Subir imagen a Google Drive
                drive_link = None
                if carousel_drive_folder_id and g_svc and g_svc.is_ready:
                    try:
                        file_name = f"Slide_{i}_de_{len(slides_copy)}.png"
                        upload_res = g_svc.upload_file_bytes(carousel_drive_folder_id, file_name, img_bytes)
                        if upload_res:
                            drive_link = upload_res.get("url")
                    except Exception as e_up:
                        logger.warning(f"Error subiendo slide {i} a Drive: {e_up}")
                
                # Guardar slide en base de datos
                existing = db.query(Slide).filter(Slide.content_id == content.id, Slide.slide_number == i).first()
                if existing:
                    existing.image_url = data_uri
                    existing.headline = headline
                    existing.body_text = body_text
                    existing.badge = badge
                    existing.prompt_used = prompt
                    existing.gdrive_file_id = drive_link
                    existing.status = "generated"
                    existing.version += 1
                else:
                    slide = Slide(
                        content_id=content.id,
                        slide_number=i,
                        slide_type=slide_type,
                        image_url=data_uri,
                        headline=headline,
                        body_text=body_text,
                        badge=badge,
                        prompt_used=prompt,
                        gdrive_file_id=drive_link,
                        status="generated",
                        version=1
                    )
                    db.add(slide)
                db.commit()
            except Exception as e:
                logger.error(f"Fallo al generar slide {i}: {e}")
                existing = db.query(Slide).filter(Slide.content_id == content.id, Slide.slide_number == i).first()
                if existing:
                    existing.status = "failed"
                    existing.feedback = f"Error: {str(e)}"
                else:
                    slide = Slide(
                        content_id=content.id,
                        slide_number=i,
                        slide_type=slide_type,
                        prompt_used=prompt,
                        feedback=f"Error: {str(e)}",
                        status="failed",
                        version=1
                    )
                    db.add(slide)
                db.commit()
                failed_count += 1
                refund_credits_atomic(db, brand.user_id, 1, f"Reembolso por fallo en slide {i}", content.id)

        if failed_count == len(slides_copy):
            content.status = "failed"
        else:
            content.status = "ready_for_review"
        db.commit()

        # Si provino de un Google Sheet, actualizar el Sheet con estado 'Ya realizado' y link de preview
        if content.source == "google_sheet" and content.sheet_row_ref and brand.sheets_url and g_svc and g_svc.is_ready:
            try:
                digits = re.sub(r'\D', '', str(content.sheet_row_ref))
                row_idx = int(digits) if digits else 0
                if row_idx > 0:
                    preview_link = f"https://studio.tecnogen.ar/app/viewer/{content.id}"
                    final_sheet_status = "Ya realizado" if content.status == "ready_for_review" else "Pendiente — enviar a la IA"
                    g_svc.update_sheet_row_status(
                        sheet_url=brand.sheets_url,
                        row_number=row_idx,
                        estado=final_sheet_status,
                        content_id=content.id,
                        preview_url=preview_link
                    )
            except Exception as e_sheet:
                logger.error(f"Error actualizando estado en Google Sheet: {e_sheet}")
            except Exception as e_sheet:
                logger.error(f"Error actualizando estado en Google Sheet: {e_sheet}")

    except Exception as e_global:
        logger.error(f"Error crítico en process_content_generation: {e_global}", exc_info=True)
        try:
            content = db.query(Content).filter(Content.id == content_id).first()
            if content:
                content.status = "failed"
                db.commit()
                if content.brand and content.brand.user_id:
                    refund_credits_atomic(db, content.brand.user_id, content.total_slides, f"Fallo en generación: {str(e_global)}", content.id)
        except Exception as e_cleanup:
            logger.error(f"Error en rollback de fallos: {e_cleanup}")
    finally:
        db.close()

@router.post("/generate", response_model=ContentGenerateResponse, status_code=status.HTTP_202_ACCEPTED)
def generate_content(
    payload: ContentGenerateRequest,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    brand = db.query(Brand).filter(Brand.id == payload.brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")

    total_slides = payload.total_slides or 6
    if total_slides < 1 or total_slides > 15:
        raise HTTPException(status_code=400, detail="El total de slides debe estar entre 1 y 15")

    content = Content(
        brand_id=brand.id,
        type=payload.type or "carousel",
        title=payload.title,
        hook_text=payload.title,
        caption_copy=f"💡 {payload.title}\n\nEn {brand.name} transformamos sonrisas con precisión y calidez.\n\n¿Querés evaluar tu caso? Escribinos por privado o agendá tu consulta hoy mismo.",
        hashtags="#saluddental #odontologia #esteticadental #sonrisasana",
        status="generating",
        source="web_form",
        total_slides=total_slides,
        version=1
    )
    db.add(content)
    db.commit()
    db.refresh(content)

    remaining = charge_credits_atomic(
        db=db,
        user_id=current_user.id,
        amount=total_slides,
        action_type="generate_carousel",
        description=f"Generación {payload.type or 'Carrusel'}: {payload.title} ({total_slides} slides)",
        reference_id=content.id
    )

    background_tasks.add_task(process_content_generation, content.id, SessionLocal)

    return {
        "content_id": content.id,
        "status": "generating",
        "estimated_time_seconds": total_slides * 15,
        "credits_charged": total_slides,
        "credits_remaining": remaining,
        "message": f"Tu {payload.type or 'carrusel'} se está generando con {total_slides} láminas."
    }

@router.post("/{content_id}/regenerate-all", response_model=ContentGenerateResponse, status_code=status.HTTP_202_ACCEPTED)
def regenerate_all_slides(
    content_id: str,
    payload: RegenerateAllRequest,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")

    # Descuento atómico de créditos
    remaining = charge_credits_atomic(
        db=db,
        user_id=current_user.id,
        amount=content.total_slides,
        action_type="regenerate_carousel_all",
        description=f"Regeneración Completa: {content.title} ({content.total_slides} slides)",
        reference_id=content.id
    )

    content.status = "generating"
    db.commit()

    background_tasks.add_task(process_content_generation, content.id, SessionLocal, payload.global_feedback)

    return {
        "content_id": content.id,
        "status": "generating",
        "estimated_time_seconds": content.total_slides * 15,
        "credits_charged": content.total_slides,
        "credits_remaining": remaining,
        "message": f"Regenerando todas las {content.total_slides} láminas."
    }

@router.get("", response_model=List[ContentOut])
def list_contents(
    brand_id: Optional[str] = None,
    status: Optional[str] = None,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    if current_user.role in ["admin", "superadmin", "support"]:
        query = db.query(Content)
    else:
        query = db.query(Content).join(Brand).filter(Brand.user_id == current_user.id)
        
    if brand_id:
        query = query.filter(Content.brand_id == brand_id)
    if status:
        query = query.filter(Content.status == status)
    return query.order_by(Content.created_at.desc()).all()

@router.get("/public/{content_id}", response_model=ContentOut)
def get_public_content(content_id: str, db: Session = Depends(get_db)):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    return content

class SyncImportPayload(BaseModel):
    contents: List[Dict[str, Any]]

@router.post("/sync-import")
def sync_import_contents(payload: SyncImportPayload, db: Session = Depends(get_db)):
    imported_count = 0
    brand = db.query(Brand).first()
    if not brand:
        raise HTTPException(status_code=400, detail="No brand configured")
    for c_data in payload.contents:
        cid = c_data.get("id")
        existing_c = db.query(Content).filter(Content.id == cid).first()
        if existing_c:
            existing_c.title = c_data.get("title", existing_c.title)
            existing_c.type = c_data.get("type", existing_c.type)
            existing_c.status = c_data.get("status", existing_c.status)
            existing_c.total_slides = c_data.get("total_slides", existing_c.total_slides)
            existing_c.caption_copy = c_data.get("caption_copy", existing_c.caption_copy)
            existing_c.hashtags = c_data.get("hashtags", existing_c.hashtags)
            existing_c.hook_text = c_data.get("hook_text", existing_c.hook_text)
        else:
            existing_c = Content(
                id=cid,
                brand_id=brand.id,
                title=c_data.get("title"),
                type=c_data.get("type", "carousel"),
                status=c_data.get("status", "ready_for_review"),
                total_slides=c_data.get("total_slides", len(c_data.get("slides", []))),
                caption_copy=c_data.get("caption_copy"),
                hashtags=c_data.get("hashtags"),
                hook_text=c_data.get("hook_text")
            )
            db.add(existing_c)
        db.commit()

        for s_data in c_data.get("slides", []):
            s_num = s_data.get("slide_number")
            existing_s = db.query(Slide).filter(Slide.content_id == cid, Slide.slide_number == s_num).first()
            if existing_s:
                existing_s.image_url = s_data.get("image_url")
                existing_s.headline = s_data.get("headline")
                existing_s.body_text = s_data.get("body_text")
                existing_s.badge = s_data.get("badge")
                existing_s.prompt_used = s_data.get("prompt_used")
                existing_s.gdrive_file_id = s_data.get("gdrive_file_id")
                existing_s.status = s_data.get("status", "generated")
            else:
                slide = Slide(
                    id=s_data.get("id"),
                    content_id=cid,
                    slide_number=s_num,
                    slide_type=s_data.get("slide_type", "content"),
                    image_url=s_data.get("image_url"),
                    headline=s_data.get("headline"),
                    body_text=s_data.get("body_text"),
                    badge=s_data.get("badge"),
                    prompt_used=s_data.get("prompt_used"),
                    gdrive_file_id=s_data.get("gdrive_file_id"),
                    status=s_data.get("status", "generated"),
                    version=1
                )
                db.add(slide)
        db.commit()
        imported_count += 1
    return {"status": "ok", "imported": imported_count}

@router.get("/{content_id}", response_model=ContentOut)
def get_content(content_id: str, db: Session = Depends(get_db)):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    return content

@router.delete("/{content_id}")
def delete_content(content_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")

    title = content.title
    db.delete(content)
    db.commit()
    return {"message": f"Contenido '{title}' eliminado exitosamente.", "deleted_id": content_id}

@router.post("/{content_id}/approve")
def approve_content(content_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")

    content.status = "approved"
    db.commit()
    return {"message": "Contenido aprobado con éxito", "content_id": content.id, "status": content.status}

@router.post("/{content_id}/slides/{slide_number}/regenerate", response_model=SlideOut)
def regenerate_slide(
    content_id: str,
    slide_number: int,
    payload: SlideRegenerateRequest,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")


    charge_credits_atomic(
        db=db,
        user_id=current_user.id,
        amount=1,
        action_type="regenerate_slide",
        description=f"Regeneración Slide #{slide_number} - {content.title}",
        reference_id=content.id
    )

    existing_slide = db.query(Slide).filter(
        Slide.content_id == content_id,
        Slide.slide_number == slide_number
    ).order_by(Slide.version.desc()).first()

    new_version = (existing_slide.version + 1) if existing_slide else 1
    
    ai_setting = db.query(AISetting).filter(AISetting.is_active == True, AISetting.category == "image").first()
    model_name = ai_setting.model_name if ai_setting else "gpt-image-2.5-sunburst"
    api_key = ai_setting.api_key_override if (ai_setting and ai_setting.api_key_override) else None
    
    try:
        image_service = AIImageService(api_key=api_key, model=model_name)
        brand = content.brand
        brand_info = {
            "name": brand.name or "JM Odontología Integral",
            "primary_color": brand.primary_color or "#16345F",
            "accent_color": brand.accent_color or "#7DD3FC",
            "bg_color": brand.bg_color or "#1D1D1B",
            "layout_preset": brand.layout_preset or "editorial-top",
            "logo_position": brand.logo_position or "top-left",
            "logo_width_px": brand.logo_width_px or 220
        }
        
        slide_data = {
            "slide_number": slide_number,
            "total_slides": content.total_slides,
            "slide_type": "cover" if slide_number == 1 else ("cta" if slide_number == content.total_slides else "content"),
            "badge": f"PASO {slide_number}" if (1 < slide_number < content.total_slides) else ("PORTADA" if slide_number == 1 else "CONSULTA"),
            "headline": payload.feedback or f"Lámina #{slide_number} - {content.title}",
            "body_text": payload.feedback or f"Contenido clínico especializado de {brand.name}."
        }
        
        prompt = build_openai_slide_prompt(brand, slide_number, content.total_slides, slide_data, global_feedback=payload.feedback)
        
        # 1. Generar imagen directamente con el modelo seleccionado por el usuario en BD
        img_bytes = image_service.generate_slide_image(prompt=prompt, slide_info=slide_data, brand_info=brand_info)
        
        b64_str = base64.b64encode(img_bytes).decode('utf-8')
        data_uri = f"data:image/png;base64,{b64_str}"

        if existing_slide:
            existing_slide.image_url = data_uri
            existing_slide.prompt_used = prompt
            existing_slide.feedback = payload.feedback
            existing_slide.status = "generated"
            existing_slide.version = new_version
            db.commit()
            db.refresh(existing_slide)
            return existing_slide
        else:
            new_slide = Slide(
                content_id=content.id,
                slide_number=slide_number,
                slide_type="content",
                image_url=data_uri,
                prompt_used=prompt,
                feedback=payload.feedback,
                status="generated",
                version=new_version
            )
            db.add(new_slide)
            db.commit()
            db.refresh(new_slide)
            return new_slide
    except Exception as e:
        logger.error(f"Fallo al regenerar slide con OpenAI: {e}")
        refund_credits_atomic(db, current_user.id, 1, f"Reembolso por fallo al regenerar slide #{slide_number}", content.id)
        raise HTTPException(status_code=500, detail=f"Fallo al conectar con OpenAI Image API: {str(e)}")
