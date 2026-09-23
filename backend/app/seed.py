from app.database import SessionLocal, Base, engine
from app.models.user import User, CreditLedger
from app.models.brand import Brand
from app.models.setting import AISetting
from app.services.auth_service import get_password_hash
import uuid

def seed():
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()
    try:
        # 1. Admin User
        admin_email = "mfmujic@gmail.com"
        admin = db.query(User).filter(User.email == admin_email).first()
        if not admin:
            admin = User(
                email=admin_email,
                password_hash=get_password_hash("AdminTecnoGen2026!"),
                full_name="Marcelo Mujica (Admin)",
                role="admin",
                plan_tier="agency",
                credits_balance=1000
            )
            db.add(admin)
            db.commit()
            db.refresh(admin)
            print(f"✅ Admin creado: {admin.email}")

        # 2. Client User (JM Odontología Integral)
        client_email = "mmujica@tecnobrain.com.ar"
        client = db.query(User).filter(User.email == client_email).first()
        if not client:
            client = User(
                email=client_email,
                password_hash=get_password_hash("JMOdonto2026!"),
                full_name="Dra. Jessica / JM Odontología",
                role="client",
                plan_tier="growth",
                credits_balance=220
            )
            db.add(client)
            db.commit()
            db.refresh(client)
            print(f"✅ Cliente creado: {client.email}")

            # Crear marca para JM Odontología
            brand = Brand(
                user_id=client.id,
                name="JM Odontología Integral",
                primary_color="#16345F",
                accent_color="#7DD3FC",
                bg_color="#0B1E38",
                font_style_title="serif-editorial",
                font_style_body="sans-modern",
                logo_position="top-left",
                logo_width_px=180,
                brand_rules={
                    "paginador": "abajo-linea-conectada",
                    "flechas_navegacion": True,
                    "doctora_presencia_default": "portada-y-cierre"
                }
            )
            db.add(brand)
            db.commit()
            print(f"✅ Marca creada: {brand.name}")

        # 3. Default AI Setting
        ai_setting = db.query(AISetting).filter(AISetting.category == "image").first()
        if not ai_setting:
            ai_setting = AISetting(
                provider="openai",
                category="image",
                model_name="gpt-image-2.5-sunburst",
                is_active=True,
                parameters={"size": "1024x1536", "quality": "high"}
            )
            db.add(ai_setting)
            db.commit()
            print(f"✅ Configuración de IA creada: {ai_setting.model_name}")

        print("🚀 Seed completado exitosamente.")
    finally:
        db.close()

if __name__ == "__main__":
    seed()
