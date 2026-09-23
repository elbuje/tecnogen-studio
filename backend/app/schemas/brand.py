from pydantic import BaseModel
from typing import Optional, Dict, Any, List
from datetime import datetime

class BrandAssetBase(BaseModel):
    asset_type: str
    label: Optional[str] = None
    file_url: Optional[str] = None
    gdrive_file_id: Optional[str] = None
    mime_type: Optional[str] = None

class BrandAssetOut(BrandAssetBase):
    id: str
    brand_id: str
    created_at: datetime

    class Config:
        from_attributes = True

class BrandBase(BaseModel):
    name: str
    primary_color: str = "#16345F"
    accent_color: str = "#7DD3FC"
    bg_color: Optional[str] = "#0B1E38"
    font_style_title: Optional[str] = "serif-editorial"
    font_style_body: Optional[str] = "sans-modern"
    layout_preset: Optional[str] = "editorial-top"
    logo_position: Optional[str] = "top-left"
    logo_width_px: Optional[int] = 180
    
    # 5 Carpetas Especializadas
    gdrive_logos_folder_id: Optional[str] = None
    gdrive_subjects_folder_id: Optional[str] = None
    gdrive_brand_manual_folder_id: Optional[str] = None
    gdrive_templates_folder_id: Optional[str] = None
    gdrive_products_folder_id: Optional[str] = None
    
    gdrive_input_folder_id: Optional[str] = None
    gdrive_output_folder_id: Optional[str] = None
    sheets_url: Optional[str] = None
    metricool_user_token: Optional[str] = None
    metricool_blog_id: Optional[str] = None
    brand_rules: Optional[Dict[str, Any]] = {}

class BrandCreate(BrandBase):
    pass

class BrandUpdate(BaseModel):
    name: Optional[str] = None
    primary_color: Optional[str] = None
    accent_color: Optional[str] = None
    bg_color: Optional[str] = None
    font_style_title: Optional[str] = None
    font_style_body: Optional[str] = None
    layout_preset: Optional[str] = None
    logo_position: Optional[str] = None
    logo_width_px: Optional[int] = None
    gdrive_logos_folder_id: Optional[str] = None
    gdrive_subjects_folder_id: Optional[str] = None
    gdrive_brand_manual_folder_id: Optional[str] = None
    gdrive_templates_folder_id: Optional[str] = None
    gdrive_products_folder_id: Optional[str] = None
    gdrive_input_folder_id: Optional[str] = None
    gdrive_output_folder_id: Optional[str] = None
    sheets_url: Optional[str] = None
    metricool_user_token: Optional[str] = None
    metricool_blog_id: Optional[str] = None
    brand_rules: Optional[Dict[str, Any]] = None

class BrandOut(BrandBase):
    id: str
    user_id: str
    created_at: datetime
    assets: List[BrandAssetOut] = []

    class Config:
        from_attributes = True
