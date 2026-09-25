from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from app.database import get_db
from app.models.user import User
from app.models.setting import AISetting
from app.schemas.setting import AISettingCreate, AISettingOut, AITestModelRequest, AITestModelResponse
from app.services.auth_service import get_current_user
import os

router = APIRouter(prefix="/settings/ai", tags=["Configuración de IA"])

@router.get("", response_model=List[AISettingOut])
def get_ai_settings(current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    return db.query(AISetting).all()

@router.post("/test", response_model=AITestModelResponse)
def test_ai_model(
    payload: AITestModelRequest,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    import time
    import base64
    import requests
    from app.config import settings

    t0 = time.time()
    api_key = payload.api_key
    if not api_key:
        setting = db.query(AISetting).filter(AISetting.provider == payload.provider, AISetting.category == "image").first()
        if setting and setting.api_key_override:
            api_key = setting.api_key_override
        else:
            api_key = settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")

    if not api_key or api_key in ["tu-api-key-de-openai", ""]:
        raise HTTPException(status_code=400, detail="Debes ingresar una API Key válida para probar el modelo.")

    prompt = payload.prompt or "Close-up portrait of a friendly smiling person with bright natural healthy teeth, soft medical daylight, clean background, editorial photography, 8k resolution."

    if payload.provider == "openai":
        try:
            from openai import OpenAI
            client = OpenAI(api_key=api_key, timeout=35.0)
            model_to_use = payload.model_name
            
            response = client.images.generate(
                model=model_to_use,
                prompt=prompt,
                size="1024x1024",
                n=1
            )
            first_img = response.data[0]
            data_uri = None
            if hasattr(first_img, 'b64_json') and first_img.b64_json:
                data_uri = f"data:image/png;base64,{first_img.b64_json}"
            elif hasattr(first_img, 'url') and first_img.url:
                r = requests.get(first_img.url, timeout=20)
                if r.status_code == 200:
                    b64 = base64.b64encode(r.content).decode('utf-8')
                    data_uri = f"data:image/png;base64,{b64}"
                else:
                    data_uri = first_img.url

            elapsed = round(time.time() - t0, 2)
            return {
                "status": "success",
                "provider": payload.provider,
                "model_used": model_to_use,
                "image_url": data_uri,
                "message": f"¡Prueba exitosa! Imagen generada correctamente con '{model_to_use}' en {elapsed}s.",
                "duration_seconds": elapsed
            }
        except Exception as e:
            err_str = str(e)
            raise HTTPException(
                status_code=400,
                detail=f"Fallo en la prueba del modelo '{payload.model_name}': {err_str}"
            )
    else:
        raise HTTPException(
            status_code=400,
            detail=f"El proveedor '{payload.provider}' aún no está activo para pruebas en vivo."
        )

@router.post("", response_model=AISettingOut, status_code=status.HTTP_201_CREATED)
def create_or_update_ai_setting(
    payload: AISettingCreate,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    # Desactivar otros del mismo category si este es activo
    if payload.is_active:
        db.query(AISetting).filter(AISetting.category == payload.category).update({"is_active": False})
    
    # Buscar si ya existe una configuración para este category y provider
    existing = db.query(AISetting).filter(
        AISetting.category == payload.category,
        AISetting.provider == payload.provider
    ).first()

    if existing:
        existing.model_name = payload.model_name
        existing.api_key_override = payload.api_key_override
        existing.is_active = payload.is_active
        existing.parameters = payload.parameters or {}
        db.commit()
        db.refresh(existing)
        return existing

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
