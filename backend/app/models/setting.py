import uuid
from datetime import datetime
from sqlalchemy import Column, String, DateTime, Boolean, Text, JSON
from app.database import Base

def generate_uuid():
    return str(uuid.uuid4())

class AISetting(Base):
    __tablename__ = "ai_settings"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    provider = Column(String(50), nullable=False, default="openai")  # 'openai', 'anthropic', 'google', 'stability', 'flux'
    category = Column(String(30), nullable=False, default="image")  # 'image', 'text', 'multimodal'
    model_name = Column(String(100), nullable=False, default="gpt-image-2.5-sunburst")
    api_key_override = Column(Text, nullable=True)
    is_active = Column(Boolean, default=True, nullable=False)
    parameters = Column(JSON, default=dict)  # size, quality, temperature, custom headers
    
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
