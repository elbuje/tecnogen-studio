from sqlalchemy.orm import Session
from fastapi import HTTPException, status
from app.models.user import User, CreditLedger
import logging

logger = logging.getLogger(__name__)

def charge_credits_atomic(
    db: Session,
    user_id: str,
    amount: int,
    action_type: str,
    description: str,
    reference_id: str = None
) -> int:
    """
    Descuenta créditos de forma atómica y auditable.
    Lanza 402 si no hay saldo suficiente.
    """
    user = db.query(User).filter(User.id == user_id).with_for_update().first()
    if not user:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    
    if user.credits_balance < amount:
        raise HTTPException(
            status_code=status.HTTP_402_PAYMENT_REQUIRED,
            detail={
                "error": "insufficient_credits",
                "required": amount,
                "available": user.credits_balance,
                "message": f"Necesitás {amount} créditos pero tenés {user.credits_balance}."
            }
        )
    
    user.credits_balance -= amount
    ledger = CreditLedger(
        user_id=user.id,
        amount=-amount,
        action_type=action_type,
        reference_id=reference_id,
        description=description
    )
    db.add(ledger)
    db.commit()
    db.refresh(user)
    return user.credits_balance

def refund_credits_atomic(
    db: Session,
    user_id: str,
    amount: int,
    description: str,
    reference_id: str = None
) -> int:
    """
    Reembolsa créditos automáticamente en caso de fallo en la generación.
    """
    user = db.query(User).filter(User.id == user_id).with_for_update().first()
    if not user:
        return 0
    
    user.credits_balance += amount
    ledger = CreditLedger(
        user_id=user.id,
        amount=amount,
        action_type="refund_failed_generation",
        reference_id=reference_id,
        description=description
    )
    db.add(ledger)
    db.commit()
    db.refresh(user)
    return user.credits_balance
