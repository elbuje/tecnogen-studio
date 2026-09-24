import hashlib
import secrets
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List, Optional
from pydantic import BaseModel
from app.database import get_db
from app.models.user import User, ApiKey
from app.models.brand import Brand
from app.services.auth_service import get_current_user

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
