from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.database import get_db
from app.models.user import User, CreditLedger
from app.schemas.auth import UserRegister, UserLogin, TokenResponse
from app.services.auth_service import verify_password, get_password_hash, create_access_token, create_refresh_token, get_current_user

router = APIRouter(prefix="/auth", tags=["Autenticación"])

PLAN_INITIAL_CREDITS = {
    "starter": 75,
    "growth": 220,
    "agency": 750
}

@router.post("/register", response_model=TokenResponse, status_code=status.HTTP_201_CREATED)
def register(payload: UserRegister, db: Session = Depends(get_db)):
    existing = db.query(User).filter(User.email == payload.email).first()
    if existing:
        raise HTTPException(status_code=400, detail="El correo electrónico ya está registrado")
    
    plan = payload.plan_tier if payload.plan_tier in PLAN_INITIAL_CREDITS else "starter"
    credits = PLAN_INITIAL_CREDITS[plan]
    
    user = User(
        email=payload.email,
        password_hash=get_password_hash(payload.password),
        full_name=payload.full_name,
        role="client",
        plan_tier=plan,
        credits_balance=credits
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    
    # Registrar carga inicial en ledger
    ledger = CreditLedger(
        user_id=user.id,
        amount=credits,
        action_type="initial_signup",
        description=f"Créditos iniciales Plan {plan.capitalize()}"
    )
    db.add(ledger)
    db.commit()

    token_data = {"sub": user.id, "email": user.email, "role": user.role}
    access_token = create_access_token(token_data)
    refresh_token = create_refresh_token(token_data)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
        "user": {
            "id": user.id,
            "email": user.email,
            "full_name": user.full_name,
            "role": user.role,
            "plan_tier": user.plan_tier,
            "credits_balance": user.credits_balance
        }
    }

@router.post("/login", response_model=TokenResponse)
def login(payload: UserLogin, db: Session = Depends(get_db)):
    user = db.query(User).filter(User.email == payload.email).first()
    if not user or not verify_password(payload.password, user.password_hash):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Credenciales incorrectas")
    
    token_data = {"sub": user.id, "email": user.email, "role": user.role}
    access_token = create_access_token(token_data)
    refresh_token = create_refresh_token(token_data)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
        "user": {
            "id": user.id,
            "email": user.email,
            "full_name": user.full_name,
            "role": user.role,
            "plan_tier": user.plan_tier,
            "credits_balance": user.credits_balance
        }
    }

@router.get("/me")
def get_me(current_user: User = Depends(get_current_user)):
    return {
        "id": current_user.id,
        "email": current_user.email,
        "full_name": current_user.full_name,
        "role": current_user.role,
        "plan_tier": current_user.plan_tier,
        "credits_balance": current_user.credits_balance,
        "plan_renewal_date": current_user.plan_renewal_date
    }
