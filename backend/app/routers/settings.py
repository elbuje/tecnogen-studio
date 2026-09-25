from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from app.database import get_db
from app.models.user import User
from app.models.setting import AISetting
from app.schemas.setting import (
    AISettingCreate,
    AISettingOut,
    AITestModelRequest,
    AITestModelResponse,
    AIFetchModelsRequest,
    AIFetchModelsResponse,
    AIModelItem
)
from app.services.auth_service import get_current_user
import os
import requests

router = APIRouter(prefix="/settings/ai", tags=["Configuración de IA"])

@router.get("", response_model=List[AISettingOut])
def get_ai_settings(current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    return db.query(AISetting).all()

@router.post("/fetch-models", response_model=AIFetchModelsResponse)
def fetch_provider_models(
    payload: AIFetchModelsRequest,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """
    Consulta directamente la API oficial del proveedor (ej: OpenAI Platform /v1/models)
    con la API Key provista por el usuario para devolver la lista real de modelos disponibles.
    """
    from app.config import settings

    api_key = payload.api_key
    if not api_key:
        setting = db.query(AISetting).filter(AISetting.provider == payload.provider, AISetting.category == "image").first()
        if setting and setting.api_key_override:
            api_key = setting.api_key_override
        else:
            api_key = settings.OPENAI_API_KEY or os.getenv("OPENAI_API_KEY")

    if payload.provider == "openai":
        if not api_key or api_key in ["tu-api-key-de-openai", ""]:
            raise HTTPException(status_code=400, detail="Por favor ingresá tu API Key de OpenAI para consultar los modelos disponibles en tu cuenta.")

        try:
            resp = requests.get(
                "https://api.openai.com/v1/models",
                headers={"Authorization": f"Bearer {api_key.strip()}"},
                timeout=15
            )
            if resp.status_code != 200:
                err_data = resp.json().get("error", {})
                err_msg = err_data.get("message", f"Error {resp.status_code} al consultar OpenAI")
                raise HTTPException(status_code=resp.status_code, detail=f"OpenAI API Error: {err_msg}")

            data = resp.json().get("data", [])
            items = []
            for m in data:
                m_id = m.get("id", "")
                m_type = "other"
                if "dall-e" in m_id or "image" in m_id or "sunburst" in m_id:
                    m_type = "image"
                elif "gpt-4" in m_id or "gpt-3.5" in m_id or "o1" in m_id or "o3" in m_id or "chatgpt" in m_id:
                    m_type = "chat"
                elif "tts" in m_id or "whisper" in m_id or "audio" in m_id:
                    m_type = "audio"
                elif "embedding" in m_id:
                    m_type = "embedding"

                name_label = m_id
                if m_id == "dall-e-3":
                    name_label = "DALL-E 3 (Generación de Imágenes HD)"
                elif m_id == "dall-e-2":
                    name_label = "DALL-E 2 (Generación de Imágenes Estándar)"
                elif "gpt-image-2.5-sunburst" in m_id:
                    name_label = "GPT Image 2.5 Sunburst (Renderizado HD y Tipografía)"
                elif m_id == "gpt-4o":
                    name_label = "GPT-4o (Omni Multimodal)"
                elif m_id == "gpt-4o-mini":
                    name_label = "GPT-4o Mini (Rápido y Económico)"

                items.append(AIModelItem(
                    id=m_id,
                    name=name_label,
                    type=m_type,
                    description=f"Propietario: {m.get('owned_by', 'system')}",
                    owned_by=m.get("owned_by")
                ))

            # Priorizar modelos de imagen al principio, seguidos por GPT-4o
            def sort_key(item: AIModelItem):
                if "gpt-image-2.5-sunburst" in item.id:
                    return 0
                if item.id == "dall-e-3":
                    return 1
                if item.id == "dall-e-2":
                    return 2
                if item.type == "image":
                    return 3
                if item.id.startswith("gpt-4o"):
                    return 4
                if item.type == "chat":
                    return 5
                return 6

            items.sort(key=lambda x: (sort_key(x), x.id))

            return AIFetchModelsResponse(
                provider=payload.provider,
                models=items,
                count=len(items),
                source="live_api"
            )
        except HTTPException:
            raise
        except Exception as e:
            raise HTTPException(status_code=500, detail=f"Error al conectar con OpenAI API: {str(e)}")

    elif payload.provider == "flux":
        catalog = [
            AIModelItem(id="flux-1.1-pro", name="FLUX 1.1 Pro", type="image", description="Black Forest Labs - Máxima fidelidad fotográfica"),
            AIModelItem(id="flux-dev", name="FLUX.1 Dev", type="image", description="Black Forest Labs - Open weights profesional"),
            AIModelItem(id="flux-schnell", name="FLUX.1 Schnell", type="image", description="Black Forest Labs - Ultra alta velocidad"),
        ]
        return AIFetchModelsResponse(provider=payload.provider, models=catalog, count=len(catalog), source="catalog")

    elif payload.provider == "stability":
        catalog = [
            AIModelItem(id="sd3-large", name="Stable Diffusion 3 Large", type="image", description="Stability AI - Manejo tipográfico y espacial"),
            AIModelItem(id="stable-diffusion-xl-1024-v1-0", name="SDXL 1.0", type="image", description="Stability AI - Clásico 1024x1024"),
        ]
        return AIFetchModelsResponse(provider=payload.provider, models=catalog, count=len(catalog), source="catalog")

    elif payload.provider == "google":
        catalog = [
            AIModelItem(id="imagen-3.0-generate-001", name="Imagen 3 (Vertex AI)", type="image", description="Google Cloud - Generación fotorrealista"),
            AIModelItem(id="imagen-3.0-fast-generate-001", name="Imagen 3 Fast", type="image", description="Google Cloud - Generación optimizada"),
            AIModelItem(id="gemini-1.5-pro", name="Gemini 1.5 Pro", type="chat", description="Google DeepMind - Multimodal 2M tokens"),
            AIModelItem(id="gemini-1.5-flash", name="Gemini 1.5 Flash", type="chat", description="Google DeepMind - Rápido y multimodal"),
        ]
        return AIFetchModelsResponse(provider=payload.provider, models=catalog, count=len(catalog), source="catalog")

    elif payload.provider == "anthropic":
        catalog = [
            AIModelItem(id="claude-3-5-sonnet-20241022", name="Claude 3.5 Sonnet", type="chat", description="Anthropic - Especialista en copywriting y razonamiento"),
            AIModelItem(id="claude-3-5-haiku-20241022", name="Claude 3.5 Haiku", type="chat", description="Anthropic - Rápido y eficiente"),
        ]
        return AIFetchModelsResponse(provider=payload.provider, models=catalog, count=len(catalog), source="catalog")

    else:
        raise HTTPException(status_code=400, detail=f"Proveedor no reconocido: {payload.provider}")


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
