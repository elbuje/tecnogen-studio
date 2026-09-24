from pydantic_settings import BaseSettings, SettingsConfigDict
from typing import List, Optional

class Settings(BaseSettings):
    # App
    PROJECT_NAME: str = "TecnoGen Studio"
    API_V1_STR: str = "/api/v1"
    ENVIRONMENT: str = "development"
    APP_URL: str = "https://studio.tecnogen.ar"
    CORS_ORIGINS: List[str] = ["*"]
    PORT: int = 8018
    
    # Database
    DATABASE_URL: str = "sqlite:///./tecnogen_dev.db"
    
    # Redis / Celery
    REDIS_URL: str = "redis://localhost:6379/0"
    
    # JWT
    JWT_SECRET_KEY: str = "tecnogen_studio_super_secret_jwt_key_2026_change_in_production"
    JWT_ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 60 * 24 * 7
    REFRESH_TOKEN_EXPIRE_DAYS: int = 30
    
    # IA Providers & Default Model Selection
    DEFAULT_AI_PROVIDER: str = "openai"
    DEFAULT_IMAGE_PROVIDER: str = "openai"
    OPENAI_API_KEY: Optional[str] = None
    OPENAI_IMAGE_MODEL: str = "gpt-image-2.5-sunburst"
    OPENAI_TEXT_MODEL: str = "gpt-4o-mini"
    
    # Google Drive Service Account / OAuth
    GOOGLE_SERVICE_ACCOUNT_EMAIL: Optional[str] = "727210347000-compute@developer.gserviceaccount.com"
    GOOGLE_SERVICE_ACCOUNT_KEY_PATH: Optional[str] = "storage/google-service-account.json"
    GOOGLE_CLIENT_ID: Optional[str] = None
    GOOGLE_CLIENT_SECRET: Optional[str] = None
    
    # Mercado Pago
    MERCADOPAGO_ACCESS_TOKEN: Optional[str] = None
    MERCADOPAGO_PUBLIC_KEY: Optional[str] = None
    
    # Metricool
    METRICOOL_API_URL: str = "https://app.metricool.com/api"
    
    model_config = SettingsConfigDict(
        env_file=".env",
        extra="allow",
        case_sensitive=False
    )

settings = Settings()
