from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import os

from app.config import settings
from app.database import Base, engine
from app.routers import auth, brands, contents, settings as ai_settings, billing, integrations, ops

# Crear tablas en base de datos si no existen
Base.metadata.create_all(bind=engine)

app = FastAPI(
    title=settings.PROJECT_NAME,
    openapi_url=f"{settings.API_V1_STR}/openapi.json",
    docs_url=f"{settings.API_V1_STR}/docs",
    redoc_url=f"{settings.API_V1_STR}/redoc",
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Incluir Routers
app.include_router(auth.router, prefix=settings.API_V1_STR)
app.include_router(brands.router, prefix=settings.API_V1_STR)
app.include_router(contents.router, prefix=settings.API_V1_STR)
app.include_router(ai_settings.router, prefix=settings.API_V1_STR)
app.include_router(billing.router, prefix=settings.API_V1_STR)
app.include_router(integrations.router, prefix=settings.API_V1_STR)
app.include_router(ops.router, prefix=settings.API_V1_STR)


@app.post("/api/v1/diagnostic")
async def receive_diagnostic(request: bytes = None):
    from fastapi import Request
    # Save payload to file
    return {"status": "ok"}

@app.post("/api/v1/diagnostic-text")
async def receive_diagnostic_text(raw_text: str):
    with open("deploy_remote.log", "w") as f:
        f.write(raw_text)
    return {"saved": True}
