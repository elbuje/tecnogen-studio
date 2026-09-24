from sqlalchemy import create_engine
from sqlalchemy.orm import declarative_base, sessionmaker
import logging
from app.config import settings

logger = logging.getLogger(__name__)

db_url = settings.DATABASE_URL

# Configuración de Engine
if db_url.startswith("sqlite"):
    engine = create_engine(db_url, connect_args={"check_same_thread": False})
else:
    engine = create_engine(db_url, pool_pre_ping=True, pool_size=10, max_overflow=20)

from sqlalchemy import text

SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

Base = declarative_base()

def run_auto_migrations():
    """Agrega columnas faltantes de forma no destructiva en SQLite."""
    try:
        with engine.connect() as conn:
            # Users table
            res = conn.execute(text("PRAGMA table_info(users)"))
            user_cols = [row[1] for row in res.fetchall()]
            
            user_needed = {
                "commercial_status": "VARCHAR(30) DEFAULT 'active'",
                "plan_name": "VARCHAR(50) DEFAULT 'Plan Growth Pro'",
                "plan_price_monthly": "INTEGER DEFAULT 150",
                "monthly_video_limit": "INTEGER DEFAULT 30",
                "videos_generated_this_month": "INTEGER DEFAULT 0",
                "avatar_minutes_quota": "INTEGER DEFAULT 60",
                "avatar_minutes_used": "INTEGER DEFAULT 0",
                "auto_mode_enabled": "BOOLEAN DEFAULT 0",
                "sheet_url": "TEXT",
                "sheet_auto_mode": "VARCHAR(30) DEFAULT 'copilot'",
                "sheet_last_sync_at": "DATETIME",
                "notes": "TEXT"
            }
            for col, col_type in user_needed.items():
                if user_cols and col not in user_cols:
                    conn.execute(text(f"ALTER TABLE users ADD COLUMN {col} {col_type}"))
            
            # Contents table
            res_c = conn.execute(text("PRAGMA table_info(contents)"))
            content_cols = [row[1] for row in res_c.fetchall()]
            if content_cols and "sheet_row_ref" not in content_cols:
                conn.execute(text("ALTER TABLE contents ADD COLUMN sheet_row_ref VARCHAR(50)"))
                
            conn.commit()
    except Exception as e:
        logger.warning(f"Aviso en auto_migrations: {e}")

run_auto_migrations()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

