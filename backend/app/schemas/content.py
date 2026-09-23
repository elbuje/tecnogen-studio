from pydantic import BaseModel
from typing import Optional, List, Dict, Any
from datetime import datetime

class SlideBase(BaseModel):
    slide_number: int
    slide_type: Optional[str] = "content"
    image_url: Optional[str] = None
    gdrive_file_id: Optional[str] = None
    prompt_used: Optional[str] = None
    feedback: Optional[str] = None
    status: Optional[str] = "pending"
    version: Optional[int] = 1

class SlideOut(SlideBase):
    id: str
    content_id: str
    created_at: datetime

    class Config:
        from_attributes = True

class SlideRegenerateRequest(BaseModel):
    feedback: str

class ContentGenerateRequest(BaseModel):
    brand_id: str
    type: Optional[str] = "carousel"
    title: str
    total_slides: Optional[int] = 6
    subject_label: Optional[str] = None
    subject_presence: Optional[str] = "portada-y-cierre"
    notes: Optional[str] = None
    auto_generate_copy: Optional[bool] = True
    custom_slides: Optional[List[Dict[str, Any]]] = None

class ContentOut(BaseModel):
    id: str
    brand_id: str
    type: str
    title: str
    hook_text: Optional[str] = None
    caption_copy: Optional[str] = None
    hashtags: Optional[str] = None
    status: str
    source: str
    total_slides: int
    metricool_post_id: Optional[str] = None
    scheduled_for: Optional[datetime] = None
    version: int
    created_at: datetime
    updated_at: datetime
    slides: List[SlideOut] = []

    class Config:
        from_attributes = True

class ContentGenerateResponse(BaseModel):
    content_id: str
    status: str
    estimated_time_seconds: int
    credits_charged: int
    credits_remaining: int
    message: str
