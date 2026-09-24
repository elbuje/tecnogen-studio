from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from app.database import get_db
from app.models.user import User
from app.models.brand import Brand, BrandAsset
from app.schemas.brand import BrandCreate, BrandOut, BrandUpdate, BrandAssetOut, BrandAssetBase
from app.services.auth_service import get_current_user

router = APIRouter(prefix="/brands", tags=["Marcas"])

PLAN_MAX_BRANDS = {
    "starter": 1,
    "growth": 3,
    "agency": 9999
}

@router.get("", response_model=List[BrandOut])
def list_brands(current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    if current_user.role in ["admin", "superadmin", "support"]:
        return db.query(Brand).all()
    return db.query(Brand).filter(Brand.user_id == current_user.id).all()

@router.post("", response_model=BrandOut, status_code=status.HTTP_201_CREATED)
def create_brand(payload: BrandCreate, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    # Validar cuota de marcas según plan
    max_brands = PLAN_MAX_BRANDS.get(current_user.plan_tier, 1)
    current_count = db.query(Brand).filter(Brand.user_id == current_user.id).count()
    if current_count >= max_brands and current_user.role not in ["admin", "superadmin"]:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail=f"Tu plan {current_user.plan_tier.capitalize()} permite hasta {max_brands} marca(s). Actualizá tu plan para agregar más."
        )

    brand = Brand(
        user_id=current_user.id,
        name=payload.name,
        primary_color=payload.primary_color,
        accent_color=payload.accent_color,
        bg_color=payload.bg_color,
        font_style_title=payload.font_style_title,
        font_style_body=payload.font_style_body,
        logo_position=payload.logo_position,
        logo_width_px=payload.logo_width_px,
        gdrive_input_folder_id=payload.gdrive_input_folder_id,
        gdrive_output_folder_id=payload.gdrive_output_folder_id,
        sheets_url=payload.sheets_url,
        metricool_user_token=payload.metricool_user_token,
        metricool_blog_id=payload.metricool_blog_id,
        brand_rules=payload.brand_rules or {}
    )
    db.add(brand)
    db.commit()
    db.refresh(brand)
    return brand

@router.get("/{brand_id}", response_model=BrandOut)
def get_brand(brand_id: str, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = db.query(Brand).filter(Brand.id == brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")
    return brand

@router.put("/{brand_id}", response_model=BrandOut)
def update_brand(brand_id: str, payload: BrandUpdate, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = db.query(Brand).filter(Brand.id == brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")
    
    update_data = payload.model_dump(exclude_unset=True)
    for key, val in update_data.items():
        setattr(brand, key, val)
    
    db.commit()
    db.refresh(brand)
    return brand

@router.post("/{brand_id}/assets", response_model=BrandAssetOut, status_code=status.HTTP_201_CREATED)
def add_brand_asset(brand_id: str, payload: BrandAssetBase, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    brand = db.query(Brand).filter(Brand.id == brand_id).first()
    if not brand:
        raise HTTPException(status_code=404, detail="Marca no encontrada")
    if brand.user_id != current_user.id and current_user.role not in ["admin", "superadmin", "support"]:
        raise HTTPException(status_code=403, detail="No tienes acceso a esta marca")


    asset = BrandAsset(
        brand_id=brand.id,
        asset_type=payload.asset_type,
        label=payload.label,
        file_url=payload.file_url,
        gdrive_file_id=payload.gdrive_file_id,
        mime_type=payload.mime_type
    )
    db.add(asset)
    db.commit()
    db.refresh(asset)
    return asset
