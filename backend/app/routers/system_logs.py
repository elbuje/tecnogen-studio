from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session
from typing import List, Optional, Dict, Any
from datetime import datetime
from app.database import get_db, SessionLocal
from app.models.user import User
from app.models.content import Content, Slide
from app.models.brand import Brand
from app.services.auth_service import get_current_user
import os
import re

router = APIRouter(prefix="/system", tags=["Logs & Monitoreo del Sistema"])

# Buffer de logs en memoria para actividad reciente
LOG_BUFFER: List[Dict[str, Any]] = []

def record_log(level: str, service: str, message: str, details: Optional[str] = None):
    entry = {
        "id": f"log_{int(datetime.now().timestamp() * 1000)}",
        "timestamp": datetime.now().isoformat(),
        "level": level.upper(),  # INFO, WARNING, ERROR, SUCCESS
        "service": service,      # OpenAI, Google Sheets, Google Drive, Base de Datos, Motor Gráfico
        "message": message,
        "details": details
    }
    LOG_BUFFER.insert(0, entry)
    if len(LOG_BUFFER) > 500:
        LOG_BUFFER.pop()

@router.get("/logs")
def get_system_logs(
    level: Optional[str] = None,
    service: Optional[str] = None,
    limit: int = Query(100, ge=1, le=500),
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """
    Devuelve los logs en vivo del sistema, incluyendo errores de generación,
    llamadas a OpenAI, Google Sheets, Drive y estado de la base de datos.
    """
    logs = list(LOG_BUFFER)

    # Inyectar estado reciente de contenidos de la BD en los logs
    recent_contents = db.query(Content).order_by(Content.created_at.desc()).limit(15).all()
    for c in recent_contents:
        slides = db.query(Slide).filter(Slide.content_id == c.id).all()
        failed_slides = [s for s in slides if s.status == "failed"]
        
        if c.status == "failed" or failed_slides:
            err_details = "\n".join([f"Slide {s.slide_number}: {s.feedback}" for s in failed_slides if s.feedback])
            logs.append({
                "id": f"content_err_{c.id}",
                "timestamp": c.updated_at.isoformat() if hasattr(c, 'updated_at') and c.updated_at else c.created_at.isoformat(),
                "level": "ERROR",
                "service": "Generación Carrusel",
                "message": f"Fallo en carrusel '{c.title}' (Fuente: {c.source}) - Estado: {c.status}",
                "details": err_details or "Fallo en ejecución de tareas de generación de láminas."
            })
        elif c.status == "ready_for_review" or c.status == "approved":
            logs.append({
                "id": f"content_ok_{c.id}",
                "timestamp": c.created_at.isoformat(),
                "level": "SUCCESS",
                "service": "Generación Carrusel",
                "message": f"Carrusel '{c.title}' generado con éxito ({len(slides)} láminas listas)",
                "details": f"ID: {c.id} | Fuente: {c.source}"
            })

    # Filtrado
    if level and level != "ALL":
        logs = [l for l in logs if l.get("level") == level.upper()]
    if service and service != "ALL":
        logs = [l for l in logs if service.lower() in l.get("service", "").lower()]

    # Deduplicar por id
    seen = set()
    deduped = []
    for l in logs:
        lid = l.get("id")
        if lid not in seen:
            seen.add(lid)
            deduped.append(l)

    # Ordenar por timestamp descendente
    deduped.sort(key=lambda x: x.get("timestamp", ""), reverse=True)

    # Determinar motor de base de datos activo
    from app.database import engine
    from app.config import settings
    if "sqlite" in settings.DATABASE_URL.lower():
        db_type = "ONLINE (SQLite)"
    else:
        db_type = f"ONLINE (MySQL: {engine.url.database or 'tecnogen_studio'})"

    return {
        "total": len(deduped[:limit]),
        "logs": deduped[:limit],
        "server_time": datetime.now().isoformat(),
        "database_status": db_type
    }

@router.delete("/logs")
def clear_system_logs(current_user: User = Depends(get_current_user)):
    global LOG_BUFFER
    LOG_BUFFER.clear()
    return {"message": "Logs en memoria limpiados exitosamente"}
