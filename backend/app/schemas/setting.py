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
