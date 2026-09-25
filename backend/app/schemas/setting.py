from pydantic import BaseModel
from typing import Optional, Dict, Any
from datetime import datetime

class AISettingBase(BaseModel):
    provider: str  # openai, anthropic, google, stability, flux
    category: str = "image"  # image, text, multimodal
    model_name: str
    api_key_override: Optional[str] = None
    is_active: bool = True
    parameters: Optional[Dict[str, Any]] = {}

class AISettingCreate(AISettingBase):
    pass

class AISettingOut(AISettingBase):
    id: str
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True

class AITestModelRequest(BaseModel):
    provider: str = "openai"
    model_name: str = "dall-e-3"
    api_key: Optional[str] = None
    prompt: Optional[str] = None

class AITestModelResponse(BaseModel):
    status: str  # success, error
    provider: str
    model_used: str
    image_url: Optional[str] = None
    message: str
    duration_seconds: Optional[float] = None
