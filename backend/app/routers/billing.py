from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.database import get_db
from app.models.user import User, CreditLedger
from app.services.auth_service import get_current_user

router = APIRouter(prefix="/billing", tags=["Facturación & Créditos"])

@router.get("/balance")
def get_billing_balance(current_user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    recent_ledger = db.query(CreditLedger).filter(
        CreditLedger.user_id == current_user.id
    ).order_by(CreditLedger.created_at.desc()).limit(20).all()

    return {
        "credits_balance": current_user.credits_balance,
        "plan_tier": current_user.plan_tier,
        "plan_renewal_date": current_user.plan_renewal_date,
        "history": [
            {
                "id": entry.id,
                "amount": entry.amount,
                "action_type": entry.action_type,
                "description": entry.description,
                "created_at": entry.created_at
            }
            for entry in recent_ledger
        ]
    }
