from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from typing import List, Optional
import io
import base64
from app.database import get_db
from app.models.user import User
from app.models.brand import Brand
from app.models.content import Content, Slide
from app.models.setting import AISetting
from app.schemas.content import ContentGenerateRequest, ContentOut, ContentGenerateResponse, SlideRegenerateRequest, SlideOut
from app.services.auth_service import get_current_user
from app.services.credit_service import charge_credits_atomic, refund_credits_atomic
from app.services.ai_image_service import AIImageService

router = APIRouter(prefix="/contents", tags=["Contenidos & Carruseles"])

def process_content_generation(content_id: str, db_factory):
    """
    Worker síncrono/background para generación de slides.
    """
    db = db_factory()
    try:
        content = db.query(Content).filter(Content.id == content_id).first()
        if not content:
            return
        
        content.status = "generating"
        db.commit()
        
        # Obtener configuración de IA
        ai_setting = db.query(AISetting).filter(AISetting.is_active == True, AISetting.category == "image").first()
        model_name = ai_setting.model_name if ai_setting else "gpt-image-2.5-sunburst"
        api_key = ai_setting.api_key_override if ai_setting else None
        
        image_service = AIImageService(api_key=api_key, model=model_name)
        brand = content.brand

        for slide_num in range(1, content.total_slides + 1):
            prompt = f"Lámina {slide_num} de {content.total_slides}: {content.title}. Estilo: {brand.font_style_title}, Fondo: {brand.bg_color}, Acento: {brand.accent_color}."
            slide_type = "cover" if slide_num == 1 else ("cta" if slide_num == content.total_slides else "content")
            
            try:
                # Generar imagen (bytes PNG)
                img_bytes = image_service.generate_slide_image(prompt)
                
                # Convertir a data URI para visualización inmediata en frontend
                b64_str = base64.b64encode(img_bytes).decode('utf-8')
                data_uri = f"data:image/png;base64,{b64_str}"
                
                slide = Slide(
                    content_id=content.id,
                    slide_number=slide_num,
                    slide_type=slide_type,
                    image_url=data_uri,
                    prompt_used=prompt,
                    status="generated",
                    version=1
                )
                db.add(slide)
                db.commit()
            except Exception as e:
                # Falló slide -> reembolsar 1 crédito
                slide = Slide(
                    content_id=content.id,
                    slide_number=slide_num,
                    slide_type=slide_type,
                    prompt_used=prompt,
                    status="failed",
                    version=1
                )
                db.add(slide)
                db.commit()
                refund_credits_atomic(db, brand.user_id, 1, f"Reembolso por fallo en slide {slide_num}", content.id)

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

    # 1. Crear registro de contenido
    content = Content(
        brand_id=brand.id,
        type=payload.type or "carousel",
        title=payload.title,
        hook_text=payload.title,
        caption_copy=f"💡 {payload.title}\n\nDejanos tu comentario o consulta.",
        hashtags="#salud #consejos #bienestar",
        status="generating",
        source="web_form",
        total_slides=total_slides,
        version=1
    )
    db.add(content)
    db.commit()
    db.refresh(content)

    # 2. Descuento atómico de créditos
    remaining = charge_credits_atomic(
        db=db,
        user_id=current_user.id,
        amount=total_slides,
        action_type="generate_carousel",
        description=f"Generación Carrusel: {payload.title} ({total_slides} slides)",
        reference_id=content.id
    )

    # 3. Lanzar procesamiento en background
    from app.database import SessionLocal
    background_tasks.add_task(process_content_generation, content.id, SessionLocal)

    return {
        "content_id": content.id,
        "status": "generating",
        "estimated_time_seconds": total_slides * 15,
        "credits_charged": total_slides,
        "credits_remaining": remaining,
        "message": f"Tu carrusel se está generando con {total_slides} láminas."
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

    # Descontar 1 crédito atómicamente
    charge_credits_atomic(
        db=db,
        user_id=current_user.id,
        amount=1,
        action_type="regenerate_slide",
        description=f"Regeneración Slide #{slide_number} - {content.title}",
        reference_id=content.id
    )

    # Buscar slide existente
    existing_slide = db.query(Slide).filter(
        Slide.content_id == content_id,
        Slide.slide_number == slide_number
    ).order_by(Slide.version.desc()).first()

    new_version = (existing_slide.version + 1) if existing_slide else 1
    
    # Generar nueva imagen con feedback
    ai_setting = db.query(AISetting).filter(AISetting.is_active == True, AISetting.category == "image").first()
    model_name = ai_setting.model_name if ai_setting else "gpt-image-2.5-sunburst"
    api_key = ai_setting.api_key_override if ai_setting else None
    
    image_service = AIImageService(api_key=api_key, model=model_name)
    prompt = f"Lámina {slide_number} de {content.total_slides}: {content.title}. MODIFICACIÓN: {payload.feedback}"
    
    img_bytes = image_service.generate_slide_image(prompt)
    b64_str = base64.b64encode(img_bytes).decode('utf-8')
    data_uri = f"data:image/png;base64,{b64_str}"

    new_slide = Slide(
        content_id=content.id,
        slide_number=slide_number,
        slide_type=existing_slide.slide_type if existing_slide else "content",
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
