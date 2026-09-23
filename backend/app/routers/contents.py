from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from typing import List, Optional
import base64
import logging
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
from app.services.copy_service import generate_carousel_slides_copy

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/contents", tags=["Contenidos & Carruseles"])

class RegenerateAllRequest(BaseModel):
    global_feedback: Optional[str] = None

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
    body = slide_data.get("body", "")
    subtitle = slide_data.get("subtitle", "")
    
    layout_preset = brand.layout_preset or "editorial-top"
    layout_guidelines = {
        "editorial-top": "Composición Editorial Hero: Titular superior de gran impacto, sujeto o elemento visual en zona media/baja, logo respetado en esquina superior.",
        "split-horizontal": "Composición Split Horizontal (50/50): Mitad superior con imagen clínica de alta resolución y mitad inferior con fondo de marca estructurado para el copy.",
        "split-vertical": "Composición Split Vertical: Columna izquierda con texto ordenado y columna derecha con fotografía del sujeto o producto.",
        "minimal-dark": "Composición Minimalista Dark: Gran titular central tipográfico, espacios negativos limpios y diseño sobrio de alta gama.",
        "testimonial-quote": "Composición de Testimonio y Social Proof: Cita destacada entre comillas, badge de satisfacción y fotografía de confianza.",
        "step-by-step": "Composición Paso a Paso / Checklist: Pasos numerados con badges de acento y tipografía jerárquica clara.",
        "before-after": "Composición Comparativa (Antes y Después): Dos bloques simétricos diferenciados con etiquetas claras de diagnóstico y resultado.",
        "stat-hero": "Composición Estadística Hero: Cifra o porcentaje en tamaño XXL central con texto explicativo de respaldo.",
        "full-bleed": "Composición Full Bleed: Fotografía envolvente a pantalla completa con gradiente inferior oscuro para contraste del texto.",
        "cta-conversion": "Composición Gran CTA de Cierre: Titular de acción directo, botón visual simulado de reserva/contacto y firma de marca."
    }
    layout_rule = layout_guidelines.get(layout_preset, layout_guidelines["editorial-top"])

    subject_instruction = ""
    if subject_presence in ["portada-y-cierre", "todas"] and (slide_num == 1 or slide_num == total_slides or subject_presence == "todas"):
        subject_instruction = "Incluir fotografía editorial hiperrealista y profesional de una odontóloga/doctora en uniforme clínico sonriente y transmitiendo confianza."
    else:
        subject_instruction = "Diseño gráfico editorial minimalista, con enfoque en tipografía legible, clínica y elementos gráficos de estética dental moderna."

    feedback_instruction = f"\nDIRECTIVA ESPECIAL DE REGENERACIÓN: {global_feedback}\n" if global_feedback else ""

    prompt = f"""
Diseño editorial premium para red social (Instagram / LinkedIn), formato vertical (1024x1536).
Marca: {brand.name}.
Regla de Composición y Layout: {layout_rule}
Paleta de colores: Fondo sólido o degradado suave en {brand.bg_color or '#0B1E38'}, detalles destacados en color acento {brand.accent_color or '#7DD3FC'}, y elementos primarios en {brand.primary_color or '#16345F'}.
Estilo: Fotografía clínica de alta gama y diseño publicitario editorial médico.
Zona superior izquierda (x:40, y:40, ancho 180px): Dejar completamente libre y despejada de texto o rostros para superposición posterior del logo.
{feedback_instruction}
CONTENIDO DEL SLIDE (Lámina {slide_num} de {total_slides}):
Tipo de lámina: {slide_type.upper()}
Título principal: "{title}"
{f'Subtítulo: "{subtitle}"' if subtitle else ''}
{f'Texto de cuerpo: "{body}"' if body else ''}

{subject_instruction}

Paginador inferior: Línea sutil con {total_slides} puntos donde el #{slide_num} está iluminado en color {brand.accent_color or '#7DD3FC'}.
Texto perfectamente legible en español sin errores tipográficos.
"""
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
        model_name = ai_setting.model_name if ai_setting else "dall-e-3"
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

        slides_copy = generate_carousel_slides_copy(
            topic=content.title,
            total_slides=content.total_slides,
            brand_name=brand.name,
            openai_client=image_service.client
        )

        failed_count = 0

        for i, slide_data in enumerate(slides_copy, start=1):
            slide_type = slide_data.get("slide_type", "content")
            prompt = build_openai_slide_prompt(brand, i, content.total_slides, slide_data, global_feedback=global_feedback)
            
            try:
                img_bytes = image_service.generate_slide_image(prompt=prompt)
                b64_str = base64.b64encode(img_bytes).decode('utf-8')
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
                logger.error(f"Fallo en OpenAI al generar slide {i}: {e}")
                existing = db.query(Slide).filter(Slide.content_id == content.id, Slide.slide_number == i).first()
                if existing:
                    existing.status = "failed"
                    existing.feedback = f"Error OpenAI: {str(e)}"
                else:
                    slide = Slide(
                        content_id=content.id,
                        slide_number=i,
                        slide_type=slide_type,
                        prompt_used=prompt,
                        feedback=f"Error OpenAI: {str(e)}",
                        status="failed",
                        version=1
                    )
                    db.add(slide)
                db.commit()
                failed_count += 1
                refund_credits_atomic(db, brand.user_id, 1, f"Reembolso por fallo OpenAI en slide {i}", content.id)

        if failed_count == content.total_slides:
            content.status = "failed"
        else:
            content.status = "ready_for_review"
        db.commit()
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
    if brand.user_id != current_user.id and current_user.role != "admin":
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
        description=f"Generación Carrusel: {payload.title} ({total_slides} slides)",
        reference_id=content.id
    )

    background_tasks.add_task(process_content_generation, content.id, SessionLocal)

    return {
        "content_id": content.id,
        "status": "generating",
        "estimated_time_seconds": total_slides * 15,
        "credits_charged": total_slides,
        "credits_remaining": remaining,
        "message": f"Tu carrusel se está generando con {total_slides} láminas mediante OpenAI."
    }

@router.post("/{content_id}/regenerate-all", response_model=ContentGenerateResponse, status_code=status.HTTP_202_ACCEPTED)
def regenerate_all_slides(
    content_id: str,
    payload: RegenerateAllRequest,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """
    Regenera todas las láminas del carrusel completo consumiendo N créditos.
    """
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role != "admin":
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
        "message": f"Regenerando todas las {content.total_slides} láminas del carrusel."
    }

@router.get("", response_model=List[ContentOut])
def list_contents(
    brand_id: Optional[str] = None,
    status: Optional[str] = None,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
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
    if content.brand.user_id != current_user.id and current_user.role != "admin":
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")
    return content

@router.delete("/{content_id}")
def delete_content(content_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    """
    Elimina un carrusel/contenido por completo.
    """
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role != "admin":
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")

    title = content.title
    db.delete(content)
    db.commit()
    return {"message": f"Carrusel '{title}' eliminado exitosamente.", "deleted_id": content_id}

@router.post("/{content_id}/approve")
def approve_content(content_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado")
    if content.brand.user_id != current_user.id and current_user.role != "admin":
        raise HTTPException(status_code=403, detail="No tienes acceso a este contenido")

    content.status = "approved"
    db.commit()
    return {"message": "Carrusel aprobado con éxito", "content_id": content.id, "status": content.status}

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
    if content.brand.user_id != current_user.id and current_user.role != "admin":
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
    model_name = ai_setting.model_name if ai_setting else "dall-e-3"
    api_key = ai_setting.api_key_override if (ai_setting and ai_setting.api_key_override) else None
    
    try:
        image_service = AIImageService(api_key=api_key, model=model_name)
        brand = content.brand
        prompt = f"Lámina #{slide_number} de {content.total_slides} para {brand.name}. Tema: {content.title}. MODIFICACIÓN SOLICITADA POR EL USUARIO: {payload.feedback}. Estilo clínico premium, fondo {brand.bg_color}, detalles en {brand.accent_color}."
        
        img_bytes = image_service.generate_slide_image(prompt=prompt)
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
