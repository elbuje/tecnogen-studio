from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from app.database import get_db
from app.models.user import User
from app.models.setting import AISetting
from app.schemas.setting import AISettingCreate, AISettingOut
from app.services.auth_service import get_current_user

router = APIRouter(prefix="/settings/ai", tags=["Configuración de IA"])

@router.get("", response_model=List[AISettingOut])
def get_ai_settings(current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    return db.query(AISetting).all()

@router.post("", response_model=AISettingOut, status_code=status.HTTP_201_CREATED)
def create_or_update_ai_setting(payload: AISettingCreate, current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    if current_user.role != "admin":
        raise HTTPException(status_code=403, detail="Solo administradores pueden cambiar la configuración de IA del sistema")
    
    # Desactivar otros del mismo category si este es activo
    if payload.is_active:
        db.query(AISetting).filter(AISetting.category == payload.category).update({"is_active": False})
    
    setting = AISetting(
        provider=payload.provider,
        category=payload.category,
        model_name=payload.model_name,
        api_key_override=payload.api_key_override,
        is_active=payload.is_active,
        parameters=payload.parameters or {}
    )
    db.add(setting)
    db.commit()
    db.refresh(setting)
    return setting
