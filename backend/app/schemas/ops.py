from pydantic import BaseModel, EmailStr
from typing import Optional, List, Dict, Any
from datetime import datetime

class ClientListItem(BaseModel):
    id: str
    email: str
    full_name: Optional[str] = None
    role: str
    commercial_status: str
    plan_tier: str
    plan_name: Optional[str] = None
    plan_price_monthly: Optional[int] = None
    monthly_video_limit: int
    videos_generated_this_month: int
    avatar_minutes_quota: int
    avatar_minutes_used: int
    credits_balance: int
    auto_mode_enabled: bool
    sheet_url: Optional[str] = None
    sheet_auto_mode: str
    sheet_last_sync_at: Optional[datetime] = None
    brands_count: int = 0
    created_at: datetime
    updated_at: datetime

class ClientUpdatePayload(BaseModel):
    full_name: Optional[str] = None
    commercial_status: Optional[str] = None  # 'active', 'trial', 'suspended_payment', 'suspended_manual', 'cancelled'
    plan_tier: Optional[str] = None
    plan_name: Optional[str] = None
    plan_price_monthly: Optional[int] = None
    monthly_video_limit: Optional[int] = None
    avatar_minutes_quota: Optional[int] = None
    credits_balance: Optional[int] = None
    auto_mode_enabled: Optional[bool] = None
    sheet_url: Optional[str] = None
    sheet_auto_mode: Optional[str] = None
    notes: Optional[str] = None

class ClientCreatePayload(BaseModel):
    email: EmailStr
    password: str
    full_name: str
    brand_name: Optional[str] = None
    plan_tier: str = "growth"
    plan_name: str = "Plan Growth Pro"
    plan_price_monthly: int = 150
    monthly_video_limit: int = 30
    avatar_minutes_quota: int = 60
    credits_balance: int = 220
    auto_mode_enabled: bool = False
    sheet_url: Optional[str] = None
    sheet_auto_mode: str = "copilot"
    notes: Optional[str] = None

class ImpersonateResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
    client_user: Dict[str, Any]
    message: str

class OpsOverviewStats(BaseModel):
    total_clients: int
    active_clients: int
    suspended_clients: int
    trial_clients: int
    mrr_total_usd: float
    total_videos_month: int
    total_avatar_minutes_month: int
    queue_pending: int
    queue_failed: int
    agents_autonomous_active: int

class RenderQueueItem(BaseModel):
    content_id: str
    client_id: str
    client_name: str
    client_email: str
    brand_name: str
    title: str
    status: str
    type: str
    source: str
    sheet_row_ref: Optional[str] = None
    created_at: datetime
    updated_at: datetime

class FinopsProvider(BaseModel):
    provider: str
    service_type: str
    cost_this_month_usd: float
    quota_info: str
    status: str
    last_updated: str

class FinopsResponse(BaseModel):
    gross_revenue_mrr_usd: float
    total_api_cost_usd: float
    net_margin_usd: float
    margin_percentage: float
    providers: List[FinopsProvider]

class TeamMemberCreate(BaseModel):
    email: EmailStr
    password: str
    full_name: str
    role: str = "support"  # 'superadmin', 'admin', 'support'

class TeamMemberItem(BaseModel):
    id: str
    email: str
    full_name: Optional[str] = None
    role: str
    created_at: datetime

class AgentTriggerJobPayload(BaseModel):
    client_email: EmailStr
    topic: str
    audience: Optional[str] = "Audiencia General"
    format: Optional[str] = "carousel"  # 'carousel', 'video_reel', 'single_post'
    sheet_row_ref: Optional[str] = None
    mode: Optional[str] = "auto"  # 'autonomous' or 'copilot'
    brand_id: Optional[str] = None
    custom_instructions: Optional[str] = None

class AgentTriggerJobResponse(BaseModel):
    success: bool
    job_id: str
    client_email: str
    title: str
    status: str
    mode_executed: str
    viewer_url: str
    message: str
