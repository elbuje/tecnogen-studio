import uuid
from datetime import datetime
from sqlalchemy import Column, String, Integer, DateTime, Boolean, ForeignKey, Text
from sqlalchemy.dialects.postgresql import UUID
from sqlalchemy.orm import relationship
from app.database import Base

def generate_uuid():
    return str(uuid.uuid4())

class User(Base):
    __tablename__ = "users"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    email = Column(String(255), unique=True, nullable=False, index=True)
    password_hash = Column(String(255), nullable=False)
    full_name = Column(String(100), nullable=True)
    role = Column(String(20), nullable=False, default="client")  # 'superadmin', 'admin', 'support', 'client'
    plan_tier = Column(String(20), nullable=False, default="growth")  # 'starter', 'growth', 'enterprise', 'custom'
    commercial_status = Column(String(30), nullable=False, default="active")  # 'active', 'trial', 'suspended_payment', 'suspended_manual', 'cancelled'
    plan_name = Column(String(50), nullable=True, default="Plan Growth Pro")
    plan_price_monthly = Column(Integer, nullable=True, default=150)
    monthly_video_limit = Column(Integer, nullable=False, default=30)
    videos_generated_this_month = Column(Integer, nullable=False, default=0)
    avatar_minutes_quota = Column(Integer, nullable=False, default=60)
    avatar_minutes_used = Column(Integer, nullable=False, default=0)
    
    # Automatización y Agentes
    auto_mode_enabled = Column(Boolean, default=False, nullable=False)
    sheet_url = Column(Text, nullable=True)
    sheet_auto_mode = Column(String(30), default="copilot", nullable=False)  # 'autonomous', 'copilot', 'disabled'
    sheet_last_sync_at = Column(DateTime, nullable=True)
    notes = Column(Text, nullable=True)
    credits_balance = Column(Integer, nullable=False, default=220)
    
    google_oauth_token = Column(Text, nullable=True)
    google_refresh_token = Column(Text, nullable=True)
    stripe_customer_id = Column(String(100), nullable=True)
    mercadopago_payer_id = Column(String(100), nullable=True)
    plan_renewal_date = Column(DateTime, nullable=True)
    
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    brands = relationship("Brand", back_populates="user", cascade="all, delete-orphan")
    credit_ledger = relationship("CreditLedger", back_populates="user", cascade="all, delete-orphan")
    api_keys = relationship("ApiKey", back_populates="user", cascade="all, delete-orphan")


class CreditLedger(Base):
    __tablename__ = "credit_ledger"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    user_id = Column(String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    amount = Column(Integer, nullable=False)  # Positivo recarga, Negativo consumo
    action_type = Column(String(50), nullable=False)  # 'monthly_renewal', 'pack_purchase', 'generate_carousel', etc.
    reference_id = Column(String(36), nullable=True)
    description = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False, index=True)

    user = relationship("User", back_populates="credit_ledger")


class ApiKey(Base):
    __tablename__ = "api_keys"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    user_id = Column(String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    key_hash = Column(String(64), nullable=False, index=True)  # SHA-256
    key_prefix = Column(String(16), nullable=False)
    label = Column(String(100), nullable=True)
    is_active = Column(Boolean, default=True, nullable=False)
    last_used_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    user = relationship("User", back_populates="api_keys")


class AuditLog(Base):
    __tablename__ = "audit_logs"

    id = Column(String(36), primary_key=True, default=generate_uuid)
    user_id = Column(String(36), nullable=False, index=True)
    user_email = Column(String(255), nullable=True)
    target_user_id = Column(String(36), nullable=True, index=True)
    target_email = Column(String(255), nullable=True)
    action = Column(String(100), nullable=False, index=True)  # 'impersonate_login', 'suspend_client', 'activate_client', 'update_quota', etc.
    details = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False, index=True)

