from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from typing import List, Optional
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

from app.services.composer_service import render_slide_composite
from app.services.google_automation_service import GoogleAutomationService

def build_openai_slide_prompt(
    brand: Brand,
    slide_num: int,
    total_slides: int,
    slide_data: dict,
    subject_presence: str = "portada-y-cierre",
    global_feedback: Optional[str] = None
) -> str:
    slide_type = slide_data.get("slide_type", "content")
    title = slide_data.get("title", "")
    
    feedback_instruction = f"Directive: {global_feedback}. " if global_feedback else ""
    
    # Directiva estricta de pre-prompt: Fondo fotográfico de alta gama 100% libre de texto
    preprompt = "Cinematic luxury dental clinic and aesthetic dentistry editorial photography. Bright clean medical studio, soft natural daylight, shallow depth of field, 8k resolution."
    negative_rules = "STRICTLY NO TEXT, NO LETTERS, NO TYPOGRAPHY, NO WORDS, NO CHARTS, NO GRAPHICS, NO SIGNS, NO WATERMARK. Pure visual photographic background plate only."
    
    if slide_type == "cover" or slide_num == 1:
        scene = f"Hero shot of modern aesthetic dental practice interior for: {title}."
    elif slide_type == "cta" or slide_num == total_slides:
        scene = "Warm elegant clinic reception and welcoming consultation atmosphere."
    else:
        scene = f"High-end aesthetic dental care details, state-of-the-art dental equipment, clean pristine clinic setting for: {title}."

    prompt = f"{preprompt} {scene} {feedback_instruction} Color harmony with subtle brand accents in {brand.accent_color or '#7DD3FC'}. {negative_rules}"
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
        brand_info = {
            "name": brand.name or "JM Odontología Integral",
            "primary_color": brand.primary_color or "#16345F",
            "accent_color": brand.accent_color or "#7DD3FC",
            "bg_color": brand.bg_color or "#1D1D1B",
            "font_style_title": brand.font_style_title or "serif-editorial",
            "font_style_body": brand.font_style_body or "sans-modern"
        }

        # Descargar Logo oficial y Fotos de Personajes desde Google Drive
        g_svc = GoogleAutomationService()
        logo_bytes = g_svc.get_brand_logo_bytes(brand) if g_svc.is_ready else None
        subject_bytes = g_svc.get_brand_subject_bytes(brand, "Jessica") if g_svc.is_ready else None

        # Verificar si ya existen láminas pre-cargadas (ej. originadas del GUION del Sheet)
        existing_slides = db.query(Slide).filter(Slide.content_id == content.id).order_by(Slide.slide_number).all()
        
        if existing_slides and len(existing_slides) > 0:
            slides_copy = []
            for es in existing_slides:
                slides_copy.append({
                    "slide_number": es.slide_number,
                    "slide_type": es.slide_type,
                    "badge": f"PASO {es.slide_number}" if es.slide_type == "content" else es.slide_type.upper(),
                    "headline": es.prompt_used or content.title,
                    "title": es.prompt_used or content.title,
                    "body_text": es.prompt_used or content.title,
                    "body": es.prompt_used or content.title
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

        failed_count = 0

        for i, slide_data in enumerate(slides_copy, start=1):
            slide_data["total_slides"] = content.total_slides
            slide_type = slide_data.get("slide_type", "content")
            prompt = build_openai_slide_prompt(brand, i, content.total_slides, slide_data, global_feedback=global_feedback)
            
            try:
                # 1. Generar placa de fondo limpia con IA (Strict NO-TEXT)
                bg_bytes = image_service.generate_slide_image(
                    prompt=prompt,
                    slide_info=slide_data,
                    brand_info=brand_info
                )
                
                # 2. Asignar foto de sujeto real (Jessica) en Portada y láminas estratégicas
                curr_subject_bytes = subject_bytes if (i == 1 or i == 3 or i == 7) else None
                
                # 3. Composición determinista con Pillow (Numeración 1/8, Logo oficial, Textos en español)
                final_png_bytes = render_slide_composite(
                    slide_num=i,
                    total_slides=content.total_slides,
                    slide_data=slide_data,
                    brand_info=brand_info,
                    background_bytes=bg_bytes,
                    logo_bytes=logo_bytes,
                    subject_bytes=curr_subject_bytes
                )

                b64_str = base64.b64encode(final_png_bytes).decode('utf-8')
                data_uri = f"data:image/png;base64,{b64_str}"
                
                # Check if slide already exists (for re-generation)
                existing = db.query(Slide).filter(Slide.content_id == content.id, Slide.slide_number == i).first()
                if existing:
                    existing.image_url = data_uri
                    existing.prompt_used = prompt
                    existing.status = "generated"
                    existing.version += 1
                else:
                    slide = Slide(
                        content_id=content.id,
                        slide_number=i,
                        slide_type=slide_type,
                        image_url=data_uri,
                        prompt_used=prompt,
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

        if failed_count == content.total_slides:
            content.status = "failed"
        else:
            content.status = "ready_for_review"
        db.commit()

        # Si provino de un Google Sheet, actualizar el Sheet con el nuevo estado y link de preview
        if content.source == "google_sheet" and content.sheet_row_ref and brand.sheets_url:
            try:
                from app.services.google_automation_service import GoogleAutomationService
                g_svc = GoogleAutomationService()
                digits = re.sub(r'\D', '', str(content.sheet_row_ref))
                row_idx = int(digits) if digits else 0
                if row_idx > 0:
                    preview_link = f"https://studio.tecnogen.ar/app/viewer/{content.id}"
                    final_sheet_status = "Listo para Revisión" if content.status == "ready_for_review" else "Error"
                    g_svc.update_sheet_row_status(
                        sheet_url=brand.sheets_url,
                        row_number=row_idx,
                        estado=final_sheet_status,
                        content_id=content.id,
                        preview_url=preview_link
                    )
            except Exception as e_sheet:
                logger.error(f"Error actualizando estado en Google Sheet: {e_sheet}")

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

@router.get("/{content_id}", response_model=ContentOut)
def get_content(content_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")
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
        
        # 1. Generar placa limpia de fondo con el modelo seleccionado por el usuario en BD
        bg_bytes = image_service.generate_slide_image(prompt=prompt, slide_info=slide_data, brand_info=brand_info)
        
        # 2. Descargar activos de Drive
        g_svc = GoogleAutomationService()
        logo_bytes = g_svc.get_brand_logo_bytes(brand) if g_svc.is_ready else None
        subject_bytes = g_svc.get_brand_subject_bytes(brand, "Jessica") if (g_svc.is_ready and (slide_number == 1 or slide_number == 3 or slide_number == 7)) else None
        
        # 3. Composición determinista HD
        final_png_bytes = render_slide_composite(
            slide_num=slide_number,
            total_slides=content.total_slides,
            slide_data=slide_data,
            brand_info=brand_info,
            background_bytes=bg_bytes,
            logo_bytes=logo_bytes,
            subject_bytes=subject_bytes
        )
        
        b64_str = base64.b64encode(final_png_bytes).decode('utf-8')
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
