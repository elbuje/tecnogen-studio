import uuid
from datetime import datetime
from sqlalchemy import Column, String, Integer, DateTime, ForeignKey, Text, JSON
from sqlalchemy.orm import relationship
from app.database import Base

def generate_uuid():
    return str(uuid.uuid4())

class Brand(Base):
    __tablename__ = "brands"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    user_id = Column(String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    name = Column(String(100), nullable=False)
    
    # Identidad Visual
    primary_color = Column(String(7), nullable=False, default="#16345F")
    accent_color = Column(String(7), nullable=False, default="#7DD3FC")
    bg_color = Column(String(7), default="#0B1E38")
    font_style_title = Column(String(50), default="serif-editorial")
    font_style_body = Column(String(50), default="sans-modern")
    
    # Composición de Marca
    logo_position = Column(String(30), default="top-left")
    logo_width_px = Column(Integer, default=180)
    
    # Conexiones
    gdrive_input_folder_id = Column(Text, nullable=True)
    gdrive_output_folder_id = Column(Text, nullable=True)
    sheets_url = Column(Text, nullable=True)
    metricool_user_token = Column(Text, nullable=True)
    metricool_blog_id = Column(String(100), nullable=True)
    
    # Reglas adicionales
    brand_rules = Column(JSON, default=dict)
    
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    user = relationship("User", back_populates="brands")
    assets = relationship("BrandAsset", back_populates="brand", cascade="all, delete-orphan")
    contents = relationship("Content", back_populates="brand", cascade="all, delete-orphan")


class BrandAsset(Base):
    __tablename__ = "brand_assets"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    brand_id = Column(String(36), ForeignKey("brands.id", ondelete="CASCADE"), nullable=False, index=True)
    asset_type = Column(String(30), nullable=False)  # 'logo_white', 'logo_color', 'logo_black', 'photo_person', 'photo_product'
    label = Column(String(100), nullable=True)
    file_url = Column(Text, nullable=True)
    gdrive_file_id = Column(Text, nullable=True)
    mime_type = Column(String(50), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    brand = relationship("Brand", back_populates="assets")
