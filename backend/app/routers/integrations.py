from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from typing import List, Optional
from pydantic import BaseModel
from app.database import get_db, SessionLocal
from app.models.user import User, ApiKey
from app.models.brand import Brand
from app.models.content import Content, Slide
from app.services.auth_service import get_current_user
import logging

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/integrations", tags=["Integraciones & APIs"])

router = APIRouter(prefix="/integrations", tags=["Integraciones & APIs"])

class DriveConnectRequest(BaseModel):
    brand_id: str
    logos_folder_id: Optional[str] = None
    subjects_folder_id: Optional[str] = None
    brand_manual_folder_id: Optional[str] = None
    templates_folder_id: Optional[str] = None
    products_folder_id: Optional[str] = None
    output_folder_id: Optional[str] = None

class SheetsConnectRequest(BaseModel):
    brand_id: str
    sheets_url: str

class MetricoolConnectRequest(BaseModel):
    brand_id: str
    metricool_user_token: str
    metricool_blog_id: str

class ApiKeyCreateRequest(BaseModel):
    label: str

from app.config import settings

@router.get("")
def get_integrations_status(brand_id: Optional[str] = None, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = None
    if brand_id:
        brand = db.query(Brand).filter(Brand.id == brand_id).first()
    else:
        brand = db.query(Brand).filter(Brand.user_id == current_user.id).first()

    api_keys = db.query(ApiKey).filter(ApiKey.user_id == current_user.id).all()

    return {
        "brand_id": brand.id if brand else None,
        "brand_name": brand.name if brand else None,
        "gdrive": {
            "connected": bool(brand and (brand.gdrive_logos_folder_id or brand.gdrive_subjects_folder_id or brand.gdrive_input_folder_id or brand.gdrive_output_folder_id)),
            "logos_folder_id": brand.gdrive_logos_folder_id if brand else None,
            "subjects_folder_id": brand.gdrive_subjects_folder_id if brand else None,
            "brand_manual_folder_id": brand.gdrive_brand_manual_folder_id if brand else None,
            "templates_folder_id": brand.gdrive_templates_folder_id if brand else None,
            "products_folder_id": brand.gdrive_products_folder_id if brand else None,
            "output_folder_id": brand.gdrive_output_folder_id if brand else None,
            "service_account_email": settings.GOOGLE_SERVICE_ACCOUNT_EMAIL
        },
        "sheets": {
            "connected": bool(brand and brand.sheets_url),
            "sheets_url": brand.sheets_url if brand else None
        },
        "metricool": {
            "connected": bool(brand and brand.metricool_user_token),
            "blog_id": brand.metricool_blog_id if brand else None,
            "has_token": bool(brand and brand.metricool_user_token)
        },
        "api_keys_count": len(api_keys)
    }

@router.post("/drive")
def connect_drive(payload: DriveConnectRequest, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = db.query(Brand).filter(Brand.id == payload.brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role != "admin":
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")

    brand.gdrive_logos_folder_id = payload.logos_folder_id
    brand.gdrive_subjects_folder_id = payload.subjects_folder_id
    brand.gdrive_brand_manual_folder_id = payload.brand_manual_folder_id
    brand.gdrive_templates_folder_id = payload.templates_folder_id
    brand.gdrive_products_folder_id = payload.products_folder_id
    brand.gdrive_output_folder_id = payload.output_folder_id
    
    db.commit()
    return {"message": "5 Carpetas de Google Drive vinculadas exitosamente", "status": "connected"}

@router.post("/sheets")
def connect_sheets(payload: SheetsConnectRequest, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = db.query(Brand).filter(Brand.id == payload.brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role != "admin":
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")

    brand.sheets_url = payload.sheets_url
    db.commit()
    return {"message": "Google Sheet vinculado con éxito", "status": "connected"}

@router.post("/metricool")
def connect_metricool(payload: MetricoolConnectRequest, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = db.query(Brand).filter(Brand.id == payload.brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role != "admin":
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")

    brand.metricool_user_token = payload.metricool_user_token
    brand.metricool_blog_id = payload.metricool_blog_id
    db.commit()
    return {"message": "Cuenta de Metricool conectada exitosamente", "status": "connected"}

@router.get("/api-keys")
def list_api_keys(current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    keys = db.query(ApiKey).filter(ApiKey.user_id == current_user.id).order_by(ApiKey.created_at.desc()).all()
    return [
        {
            "id": k.id,
            "key_prefix": k.key_prefix,
            "label": k.label,
            "is_active": k.is_active,
            "last_used_at": k.last_used_at,
            "created_at": k.created_at
        }
        for k in keys
    ]

@router.post("/api-keys")
def create_api_key(payload: ApiKeyCreateRequest, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    raw_key = f"tg_live_{secrets.token_urlsafe(24)}"
    key_hash = hashlib.sha256(raw_key.encode("utf-8")).hexdigest()
    key_prefix = raw_key[:12] + "..."

    api_key = ApiKey(
        user_id=current_user.id,
        key_hash=key_hash,
        key_prefix=key_prefix,
        label=payload.label,
        is_active=True
    )
    db.add(api_key)
    db.commit()
    db.refresh(api_key)

    return {
        "id": api_key.id,
        "label": api_key.label,
        "key_prefix": api_key.key_prefix,
        "api_key": raw_key,
        "message": "Copia tu API Key ahora. No se volverá a mostrar completa por seguridad."
    }

@router.delete("/api-keys/{key_id}")
def revoke_api_key(key_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    key = db.query(ApiKey).filter(ApiKey.id == key_id, ApiKey.user_id == current_user.id).first()
    if not key:
        raise HTTPException(status_code=404, detail="API Key no encontrada")
    db.delete(key)
    db.commit()
    return {"message": "API Key revocada exitosamente"}

import logging
logger = logging.getLogger(__name__)

class SyncSheetRequest(BaseModel):
    brand_id: Optional[str] = None

@router.post("/sync-sheet")
def sync_brand_sheet(
    payload: SyncSheetRequest,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """
    Sincroniza el Google Sheet de la marca del usuario:
    Lee las filas con estado 'Pendiente — enviar a la IA',
    crea los contenidos en el sistema, dispara la generación por IA y actualiza la fila en el Sheet.
    """
    brand = None
    if payload.brand_id:
        brand = db.query(Brand).filter(Brand.id == payload.brand_id).first()
    if not brand:
        brand = db.query(Brand).filter(Brand.user_id == current_user.id).first()
    if not brand:
        brand = db.query(Brand).first()
    if not brand:
        brand = Brand(
            user_id=current_user.id,
            name="JM Odontología Integral",
            primary_color="#16345F",
            accent_color="#7DD3FC",
            bg_color="#0B1E38",
            sheets_url="https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit"
        )
        db.add(brand)
        db.commit()
        db.refresh(brand)

    sheet_url = brand.sheets_url or current_user.sheet_url or "https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit"
    if not brand.sheets_url:
        brand.sheets_url = sheet_url
        db.commit()

    try:
        from app.services.google_automation_service import GoogleAutomationService
        from app.models.content import Content, Slide
        from app.routers.contents import process_content_generation

        g_svc = GoogleAutomationService()
        jobs = g_svc.read_sheet_jobs(sheet_url)
    except Exception as e:
        logger.exception("Error leyendo el Google Sheet")
        raise HTTPException(status_code=500, detail=f"Error conectando con Google Sheets: {str(e)}")

    pending_jobs = [j for j in jobs if j.get("is_pending")]

    if not pending_jobs:
        return {
            "success": True,
            "detail": f"¡Google Sheet sincronizado! No se encontraron filas nuevas con estado 'Pendiente — enviar a la IA'. Total filas revisadas: {len(jobs)}",
            "pending_count": 0
        }

    triggered = []
    for job in pending_jobs:
        row_idx = job["row_index"]
        topic = job.get("topic") or f"Contenido Sheet Fila {row_idx}"
        script_raw = job.get("script") or ""
        total_slides = job.get("total_slides") or 4

        try:
            # 1. Crear registro de Contenido con campos exactos del modelo
            content = Content(
                brand_id=brand.id,
                title=topic,
                type="carousel",
                status="generating",
                source="google_sheet",
                total_slides=total_slides,
                sheet_row_ref=f"row_{row_idx}"
            )
            db.add(content)
            db.commit()
            db.refresh(content)

            # 2. Desglosar slides desde el guión del sheet
            slides_data = g_svc.parse_script_to_slides(script_raw, total_slides, topic)
            for s in slides_data:
                slide_obj = Slide(
                    content_id=content.id,
                    slide_number=s.get("order_index") or 1,
                    slide_type=s.get("slide_type") or "content",
                    prompt_used=f"Generando prompt con OpenAI para: {s.get('headline', topic)}...",
                    status="pending"
                )
                db.add(slide_obj)
            db.commit()

            # 3. Disparar generación asíncrona en segundo plano
            background_tasks.add_task(process_content_generation, content.id, SessionLocal)
            triggered.append({"row_index": row_idx, "content_id": content.id, "title": topic})
        except Exception as e:
            logger.exception(f"Error procesando fila {row_idx}: {e}")
            triggered.append({"row_index": row_idx, "error": str(e)})

    return {
        "success": True,
        "detail": f"¡Sincronización exitosa! Se pusieron en marcha {len(triggered)} contenidos con IA desde tu Sheet.",
        "pending_count": len(triggered),
        "items": triggered
    }

