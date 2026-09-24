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
        # 1. Admin User (SuperAdmin)
        admin_email = "mfmujic@gmail.com"
        admin = db.query(User).filter(User.email == admin_email).first()
        if not admin:
            admin = User(
                email=admin_email,
                password_hash=get_password_hash("AdminTecnoGen2026!"),
                full_name="Marcelo Mujica (SuperAdmin)",
                role="superadmin",
                commercial_status="active",
                plan_tier="enterprise",
                plan_name="Master Agency Pro",
                plan_price_monthly=0,
                monthly_video_limit=999,
                avatar_minutes_quota=999,
                credits_balance=5000
            )
            db.add(admin)
            db.commit()
            db.refresh(admin)
            print(f"✅ SuperAdmin creado: {admin.email}")
        else:
            admin.role = "superadmin"
            admin.commercial_status = "active"
            db.commit()

        # 2. Support User
        support_email = "soporte@tecnobrain.com.ar"
        support = db.query(User).filter(User.email == support_email).first()
        if not support:
            support = User(
                email=support_email,
                password_hash=get_password_hash("SoporteTecnoGen2026!"),
                full_name="Agente de Soporte TecnoGen",
                role="support",
                commercial_status="active",
                plan_tier="growth",
                plan_name="Soporte Nivel 1",
                plan_price_monthly=0,
                credits_balance=500
            )
            db.add(support)
            db.commit()
            print(f"✅ Soporte creado: {support.email}")

        # 3. Client User (JM Odontología Integral)
        client_email = "mmujica@tecnobrain.com.ar"
        client = db.query(User).filter(User.email == client_email).first()
        if not client:
            client = User(
                email=client_email,
                password_hash=get_password_hash("JMOdonto2026!"),
                full_name="Dra. Jessica / JM Odontología",
                role="client",
                commercial_status="active",
                plan_tier="growth",
                plan_name="Plan Growth Pro (30 Videos/Mes)",
                plan_price_monthly=150,
                monthly_video_limit=30,
                videos_generated_this_month=6,
                avatar_minutes_quota=60,
                avatar_minutes_used=12,
                credits_balance=220,
                auto_mode_enabled=True,
                sheet_url="https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit",
                sheet_auto_mode="copilot"
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
                sheets_url="https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit",
                brand_rules={
                    "paginador": "abajo-linea-conectada",
                    "flechas_navegacion": True,
                    "doctora_presencia_default": "portada-y-cierre"
                }
            )
            db.add(brand)
            db.commit()
            print(f"✅ Marca creada: {brand.name}")
        else:
            client.commercial_status = "active"
            client.plan_name = "Plan Growth Pro (30 Videos/Mes)"
            client.plan_price_monthly = 150
            client.monthly_video_limit = 30
            client.auto_mode_enabled = True
            client.sheet_auto_mode = "copilot"
            client.sheet_url = "https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit"
            
            brand = db.query(Brand).filter(Brand.user_id == client.id).first()
            if brand:
                brand.sheets_url = "https://docs.google.com/spreadsheets/d/16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88/edit"
            db.commit()

        # 4. Default AI Setting
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

