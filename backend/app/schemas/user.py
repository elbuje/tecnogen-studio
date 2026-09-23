from pydantic import BaseModel, EmailStr
from typing import Optional

class UserProfileUpdate(BaseModel):
    full_name: Optional[str] = None
    email: Optional[EmailStr] = None
    brand_name: Optional[str] = None
    new_password: Optional[str] = None

class UserProfileOut(BaseModel):
    id: str
    email: str
    full_name: Optional[str] = None
    role: str
    plan_tier: str
    credits_balance: int
    brand_name: Optional[str] = None
    brand_id: Optional[str] = None
