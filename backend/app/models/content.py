import uuid
from datetime import datetime
from sqlalchemy import Column, String, Integer, DateTime, ForeignKey, Text, UniqueConstraint, JSON
from sqlalchemy.orm import relationship
from app.database import Base

def generate_uuid():
    return str(uuid.uuid4())

class Content(Base):
    __tablename__ = "contents"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    brand_id = Column(String(36), ForeignKey("brands.id", ondelete="CASCADE"), nullable=False, index=True)
    type = Column(String(20), nullable=False, default="carousel")  # 'carousel', 'single_post', 'video_reel'
    title = Column(String(255), nullable=False)
    hook_text = Column(Text, nullable=True)
    caption_copy = Column(Text, nullable=True)
    hashtags = Column(Text, nullable=True)
    status = Column(String(30), nullable=False, default="draft", index=True)
    # 'draft', 'copy_approved', 'generating', 'ready_for_review', 'approved', 'scheduled', 'published', 'failed'
    source = Column(String(30), default="web_form")  # 'web_form', 'google_sheet', 'api', 'mcp'
    sheet_row_ref = Column(String(50), nullable=True)
    total_slides = Column(Integer, default=6)
    
    metricool_post_id = Column(String(100), nullable=True)
    scheduled_for = Column(DateTime, nullable=True)
    version = Column(Integer, default=1, nullable=False)
    
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    brand = relationship("Brand", back_populates="contents")
    slides = relationship("Slide", back_populates="content", cascade="all, delete-orphan", order_by="Slide.slide_number")


class Slide(Base):
    __tablename__ = "slides"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    content_id = Column(String(36), ForeignKey("contents.id", ondelete="CASCADE"), nullable=False, index=True)
    slide_number = Column(Integer, nullable=False)
    slide_type = Column(String(30), default="content")  # 'cover', 'content', 'comparison', 'data_highlight', 'cta'
    
    image_url = Column(Text, nullable=True)
    gdrive_file_id = Column(Text, nullable=True)
    
    prompt_used = Column(Text, nullable=True)
    feedback = Column(Text, nullable=True)
    status = Column(String(20), default="pending", nullable=False)  # 'pending', 'generating', 'generated', 'approved', 'rejected', 'failed'
    version = Column(Integer, default=1, nullable=False)
    
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    content = relationship("Content", back_populates="slides")

    __table_args__ = (
        UniqueConstraint("content_id", "slide_number", "version", name="uq_slide_content_number_version"),
    )
