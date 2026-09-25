from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from typing import List, Optional, Dict, Any
from datetime import datetime
import json
import logging

from app.database import get_db, SessionLocal
from app.models.user import User, CreditLedger, AuditLog
from app.models.brand import Brand
from app.models.content import Content, Slide
from app.models.setting import AISetting
from app.schemas.ops import (
    ClientListItem, ClientUpdatePayload, ClientCreatePayload,
    ImpersonateResponse, OpsOverviewStats, RenderQueueItem,
    FinopsResponse, FinopsProvider, TeamMemberCreate, TeamMemberItem,
    AgentTriggerJobPayload, AgentTriggerJobResponse
)
from app.services.auth_service import (
    get_current_ops_user, get_current_superadmin, get_password_hash,
    create_access_token, create_refresh_token
)
from app.services.copy_service import generate_carousel_slides_copy
from app.services.ai_image_service import AIImageService
from app.routers.contents import process_content_generation

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/ops", tags=["Operaciones & SuperAdmin"])

# -------------------------------------------------------------
# 1. OVERVIEW EJECUTIVO Y SAAS METRICS
# -------------------------------------------------------------
@router.get("/overview", response_model=OpsOverviewStats)
def get_ops_overview(
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    clients = db.query(User).filter(User.role.in_(["client", "agency"])).all()
    total_clients = len(clients)
    active_clients = sum(1 for c in clients if c.commercial_status == "active")
    suspended_clients = sum(1 for c in clients if "suspended" in (c.commercial_status or ""))
    trial_clients = sum(1 for c in clients if c.commercial_status == "trial")
    
    # Calcular MRR estimado sumando planes de clientes activos
    mrr_total = sum(c.plan_price_monthly or 150 for c in clients if c.commercial_status in ["active", "trial"])
    
    # Videos y minutos
    total_videos = sum(c.videos_generated_this_month or 0 for c in clients)
    total_avatar_mins = sum(c.avatar_minutes_used or 0 for c in clients)
    
    # Cola de renders
    queue_pending = db.query(Content).filter(Content.status.in_(["draft", "generating", "copy_approved"])).count()
    queue_failed = db.query(Content).filter(Content.status == "failed").count()
    
    # Agentes autónomos activos
    agents_active = sum(1 for c in clients if c.auto_mode_enabled or c.sheet_auto_mode in ["autonomous", "copilot"])
    
    return {
        "total_clients": total_clients,
        "active_clients": active_clients,
        "suspended_clients": suspended_clients,
        "trial_clients": trial_clients,
        "mrr_total_usd": float(mrr_total),
        "total_videos_month": total_videos,
        "total_avatar_minutes_month": total_avatar_mins,
        "queue_pending": queue_pending,
        "queue_failed": queue_failed,
        "agents_autonomous_active": agents_active
    }


# -------------------------------------------------------------
# 2. GESTIÓN DE CLIENTES & ESTADOS COMERCIALES
# -------------------------------------------------------------
@router.get("/clients", response_model=List[ClientListItem])
def list_ops_clients(
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    clients = db.query(User).filter(User.role.in_(["client", "agency"])).order_by(User.created_at.desc()).all()
    result = []
    for client in clients:
        brands_count = db.query(Brand).filter(Brand.user_id == client.id).count()
        result.append({
            "id": client.id,
            "email": client.email,
            "full_name": client.full_name,
            "role": client.role,
            "commercial_status": client.commercial_status or "active",
            "plan_tier": client.plan_tier or "growth",
            "plan_name": client.plan_name or "Plan Growth Pro",
            "plan_price_monthly": client.plan_price_monthly or 150,
            "monthly_video_limit": client.monthly_video_limit or 30,
            "videos_generated_this_month": client.videos_generated_this_month or 0,
            "avatar_minutes_quota": client.avatar_minutes_quota or 60,
            "avatar_minutes_used": client.avatar_minutes_used or 0,
            "credits_balance": client.credits_balance or 0,
            "auto_mode_enabled": bool(client.auto_mode_enabled),
            "sheet_url": client.sheet_url,
            "sheet_auto_mode": client.sheet_auto_mode or "copilot",
            "sheet_last_sync_at": client.sheet_last_sync_at,
            "brands_count": brands_count,
            "created_at": client.created_at,
            "updated_at": client.updated_at
        })
    return result


@router.post("/clients", status_code=status.HTTP_201_CREATED)
def create_ops_client(
    payload: ClientCreatePayload,
    current_user: User = Depends(get_current_superadmin),
    db: Session = Depends(get_db)
):
    existing = db.query(User).filter(User.email == payload.email).first()
    if existing:
        raise HTTPException(status_code=400, detail="El correo ya se encuentra registrado.")
    
    new_client = User(
        email=payload.email,
        password_hash=get_password_hash(payload.password),
        full_name=payload.full_name,
        role="client",
        commercial_status="active",
        plan_tier=payload.plan_tier,
        plan_name=payload.plan_name,
        plan_price_monthly=payload.plan_price_monthly,
        monthly_video_limit=payload.monthly_video_limit,
        videos_generated_this_month=0,
        avatar_minutes_quota=payload.avatar_minutes_quota,
        avatar_minutes_used=0,
        credits_balance=payload.credits_balance,
        auto_mode_enabled=payload.auto_mode_enabled,
        sheet_url=payload.sheet_url,
        sheet_auto_mode=payload.sheet_auto_mode,
        notes=payload.notes
    )
    db.add(new_client)
    db.commit()
    db.refresh(new_client)
    
    # Crear Brand por defecto
    brand_name = payload.brand_name or f"{payload.full_name} Brand"
    brand = Brand(
        user_id=new_client.id,
        name=brand_name,
        primary_color="#16345F",
        accent_color="#7DD3FC",
        bg_color="#0B1E38"
    )
    db.add(brand)
    
    # Log de Auditoría
    log = AuditLog(
        user_id=current_user.id,
        user_email=current_user.email,
        target_user_id=new_client.id,
        target_email=new_client.email,
        action="create_client",
        details=f"Cliente creado con plan {payload.plan_name} (${payload.plan_price_monthly}/mes)"
    )
    db.add(log)
    db.commit()
    
    return {"message": "Cliente dado de alta con éxito", "client_id": new_client.id}


@router.put("/clients/{client_id}")
def update_ops_client(
    client_id: str,
    payload: ClientUpdatePayload,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    client = db.query(User).filter(User.id == client_id).first()
    if not client:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    
    # Si es soporte y trata de cambiar finanzas críticas, solo superadmin puede
    if payload.plan_price_monthly is not None and current_user.role == "support":
        raise HTTPException(status_code=403, detail="Soporte no tiene permisos para modificar tarifas de planes")

    updated_fields = []
    if payload.full_name is not None:
        client.full_name = payload.full_name
        updated_fields.append("full_name")
    if payload.commercial_status is not None:
        client.commercial_status = payload.commercial_status
        updated_fields.append(f"status->{payload.commercial_status}")
    if payload.plan_tier is not None:
        client.plan_tier = payload.plan_tier
        updated_fields.append("plan_tier")
    if payload.plan_name is not None:
        client.plan_name = payload.plan_name
        updated_fields.append("plan_name")
    if payload.plan_price_monthly is not None:
        client.plan_price_monthly = payload.plan_price_monthly
        updated_fields.append("plan_price")
    if payload.monthly_video_limit is not None:
        client.monthly_video_limit = payload.monthly_video_limit
        updated_fields.append("video_limit")
    if payload.avatar_minutes_quota is not None:
        client.avatar_minutes_quota = payload.avatar_minutes_quota
        updated_fields.append("avatar_quota")
    if payload.credits_balance is not None:
        client.credits_balance = payload.credits_balance
        updated_fields.append("credits_balance")
    if payload.auto_mode_enabled is not None:
        client.auto_mode_enabled = payload.auto_mode_enabled
        updated_fields.append(f"auto_mode->{payload.auto_mode_enabled}")
    if payload.sheet_url is not None:
        client.sheet_url = payload.sheet_url
        updated_fields.append("sheet_url")
    if payload.sheet_auto_mode is not None:
        client.sheet_auto_mode = payload.sheet_auto_mode
        updated_fields.append(f"sheet_auto_mode->{payload.sheet_auto_mode}")
    if payload.notes is not None:
        client.notes = payload.notes

    # Registrar en auditoría
    log = AuditLog(
        user_id=current_user.id,
        user_email=current_user.email,
        target_user_id=client.id,
        target_email=client.email,
        action="update_client_commercial",
        details=f"Campos modificados: {', '.join(updated_fields)}"
    )
    db.add(log)
    db.commit()
    db.refresh(client)
    
    return {"message": "Cliente actualizado correctamente", "client_id": client.id}


# -------------------------------------------------------------
# 3. IMPERSONACIÓN ASISTIDA (LOGIN-AS-CLIENT)
# -------------------------------------------------------------
@router.post("/clients/{client_id}/impersonate", response_model=ImpersonateResponse)
def impersonate_client(
    client_id: str,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    client = db.query(User).filter(User.id == client_id).first()
    if not client:
        raise HTTPException(status_code=404, detail="Cliente no encontrado para impersonación")
    
    # Crear token con identidad del cliente pero metadata de impersonación
    token_data = {
        "sub": client.id,
        "email": client.email,
        "role": client.role,
        "impersonated_by": current_user.email
    }
    access_token = create_access_token(token_data)
    
    # Registrar en auditoría de seguridad
    log = AuditLog(
        user_id=current_user.id,
        user_email=current_user.email,
        target_user_id=client.id,
        target_email=client.email,
        action="impersonate_login",
        details=f"Acceso de soporte/operaciones iniciado por {current_user.email}"
    )
    db.add(log)
    db.commit()
    
    brand = db.query(Brand).filter(Brand.user_id == client.id).first()
    
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "client_user": {
            "id": client.id,
            "email": client.email,
            "full_name": client.full_name,
            "role": client.role,
            "plan_tier": client.plan_tier,
            "credits_balance": client.credits_balance,
            "brand_name": brand.name if brand else "",
            "impersonated_by": current_user.email
        },
        "message": f"Sesión de impersonación iniciada para {client.email}"
    }


# -------------------------------------------------------------
# 4. MONITOR DE COLA DE RENDERS & REINTENTOS
# -------------------------------------------------------------
@router.get("/queue", response_model=List[RenderQueueItem])
def get_render_queue(
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    contents = db.query(Content).order_by(Content.created_at.desc()).limit(100).all()
    queue = []
    for c in contents:
        brand = c.brand
        user = brand.user if brand else None
        queue.append({
            "content_id": c.id,
            "client_id": user.id if user else "n/a",
            "client_name": user.full_name if user else "Desconocido",
            "client_email": user.email if user else "n/a",
            "brand_name": brand.name if brand else "Sin Marca",
            "title": c.title,
            "status": c.status,
            "type": c.type,
            "source": c.source or "web_form",
            "sheet_row_ref": c.sheet_row_ref,
            "created_at": c.created_at,
            "updated_at": c.updated_at
        })
    return queue


@router.post("/queue/{content_id}/retry")
def retry_render_job(
    content_id: str,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    content = db.query(Content).filter(Content.id == content_id).first()
    if not content:
        raise HTTPException(status_code=404, detail="Contenido no encontrado en la cola")
    
    content.status = "generating"
    db.commit()
    
    background_tasks.add_task(process_content_generation, content_id, SessionLocal)
    
    log = AuditLog(
        user_id=current_user.id,
        user_email=current_user.email,
        target_user_id=content.brand.user_id if content.brand else None,
        action="retry_render_job",
        details=f"Reintento forzado de generación para contenido {content.id} ({content.title})"
    )
    db.add(log)
    db.commit()
    
    return {"message": "Generación reenviada a la cola en segundo plano", "content_id": content.id}


# -------------------------------------------------------------
# 5. AGENTES & GOOGLE SHEETS INGESTION (agentes.tecnobrain.ar)
# -------------------------------------------------------------
@router.get("/agents")
def list_agent_configurations(
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    clients = db.query(User).filter(User.role.in_(["client", "agency"])).all()
    results = []
    for c in clients:
        results.append({
            "client_id": c.id,
            "client_email": c.email,
            "client_name": c.full_name,
            "auto_mode_enabled": c.auto_mode_enabled,
            "sheet_url": c.sheet_url or "https://docs.google.com/spreadsheets/d/...",
            "sheet_auto_mode": c.sheet_auto_mode,
            "sheet_last_sync_at": c.sheet_last_sync_at,
            "monthly_videos_done": f"{c.videos_generated_this_month}/{c.monthly_video_limit}",
            "status": "ready" if c.commercial_status == "active" else "paused_account"
        })
    return results


@router.get("/agents/sheet-jobs")
def get_sheet_jobs_for_client(
    client_email: str,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    """
    Lee las filas del Google Sheet del cliente y devuelve las tareas pendientes y su estado.
    """
    client = db.query(User).filter(User.email == client_email).first()
    if not client:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    
    brand = db.query(Brand).filter(Brand.user_id == client.id).first()
    sheet_url = (brand.sheets_url if brand else None) or client.sheet_url
    if not sheet_url:
        raise HTTPException(status_code=400, detail="El cliente no tiene un Google Sheet vinculado")

    from app.services.google_automation_service import GoogleAutomationService
    g_svc = GoogleAutomationService()
    try:
        jobs = g_svc.read_sheet_jobs(sheet_url)
        return {
            "client_email": client.email,
            "sheet_url": sheet_url,
            "total_rows": len(jobs),
            "pending_count": sum(1 for j in jobs if j.get("is_pending")),
            "jobs": jobs
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error leyendo Google Sheet: {str(e)}")


@router.post("/agents/sync-sheet")
def sync_sheet_automatically(
    client_email: str,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    """
    Recorre el Google Sheet del cliente, detecta filas con 'Pendiente — enviar a la IA'
    que no hayan sido procesadas, las pone en 'Procesando' y dispara la generación.
    """
    client = db.query(User).filter(User.email == client_email).first()
    if not client:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    
    brand = db.query(Brand).filter(Brand.user_id == client.id).first()
    sheet_url = (brand.sheets_url if brand else None) or client.sheet_url
    if not sheet_url:
        raise HTTPException(status_code=400, detail="El cliente no tiene un Google Sheet vinculado")

    from app.services.google_automation_service import GoogleAutomationService
    g_svc = GoogleAutomationService()
    jobs = g_svc.read_sheet_jobs(sheet_url)

    pending_jobs = [j for j in jobs if j.get("is_pending")]
    if not pending_jobs:
        return {
            "success": True,
            "message": "No hay filas con estado 'Pendiente — enviar a la IA' pendientes de procesar.",
            "processed_count": 0
        }

    triggered = []
    mode_to_use = client.sheet_auto_mode or "autonomous"

    for job in pending_jobs:
        row_ref = str(job["row_number"])
        topic = job["titulo"] or job["tema"] or f"Contenido Fila {row_ref}"
        
        # 1. Parsear el guion del Sheet si existe
        parsed_slides = g_svc.parse_script_to_slides(job.get("guion", ""))
        total_slides = len(parsed_slides) if parsed_slides else 6

        # 2. Marcar en el Sheet como "Procesando"
        g_svc.update_sheet_row_status(
            sheet_url=sheet_url,
            row_number=job["row_number"],
            estado="Procesando"
        )

        # 3. Crear registro Content
        new_content = Content(
            brand_id=brand.id,
            type="carousel",
            title=topic,
            hook_text=parsed_slides[0].get("title", topic) if parsed_slides else topic,
            caption_copy=job.get("copy_instagram") or f"💡 {topic}\n\nEn {brand.name} priorizamos tu bienestar y sonrisa.",
            hashtags="#saluddental #odontologia #" + brand.name.replace(" ", ""),
            status="generating" if mode_to_use == "autonomous" else "ready_for_review",
            source="google_sheet",
            sheet_row_ref=row_ref,
            total_slides=total_slides
        )
        db.add(new_content)
        db.commit()
        db.refresh(new_content)

        # 4. Crear los Slides en la base de datos
        if parsed_slides:
            for s in parsed_slides:
                slide = Slide(
                    content_id=new_content.id,
                    slide_number=s["slide_number"],
                    slide_type=s["slide_type"],
                    prompt_used=s["title"],
                    status="pending"
                )
                db.add(slide)
        else:
            slides_copy = generate_carousel_slides_copy(topic=topic, total_slides=total_slides, brand_name=brand.name)
            for idx, s in enumerate(slides_copy, start=1):
                slide = Slide(
                    content_id=new_content.id,
                    slide_number=idx,
                    slide_type=s.get("slide_type", "content"),
                    prompt_used=s.get("title", ""),
                    status="pending"
                )
                db.add(slide)
        db.commit()

        # 5. Lanzar renderizado
        if mode_to_use == "autonomous":
            background_tasks.add_task(process_content_generation, new_content.id, SessionLocal)

        triggered.append({
            "row": job["row_number"],
            "title": topic,
            "content_id": new_content.id,
            "slides_count": total_slides
        })

    client.sheet_last_sync_at = datetime.utcnow()
    db.commit()

    return {
        "success": True,
        "message": f"Se disparó la creación para {len(triggered)} carruseles desde el Sheet.",
        "processed_count": len(triggered),
        "items": triggered
    }


@router.post("/agents/trigger-job", response_model=AgentTriggerJobResponse)
def trigger_agent_job_from_sheet(
    payload: AgentTriggerJobPayload,
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    """
    Endpoint de integración directa para agentes en `agentes.tecnobrain.ar`.
    Lee las novedades de la planilla y dispara la creación en modo Piloto Automático o Copiloto.
    """
    client = db.query(User).filter(User.email == payload.client_email).first()
    if not client:
        raise HTTPException(status_code=404, detail=f"Cliente {payload.client_email} no encontrado en TecnoGen")
    
    if "suspended" in (client.commercial_status or ""):
        raise HTTPException(status_code=403, detail=f"Cuenta suspendida comercialmente: {client.commercial_status}")
    
    brand = None
    if payload.brand_id:
        brand = db.query(Brand).filter(Brand.id == payload.brand_id, Brand.user_id == client.id).first()
    if not brand:
        brand = db.query(Brand).filter(Brand.user_id == client.id).first()
    
    if not brand:
        raise HTTPException(status_code=400, detail="El cliente no posee ningún Brand Kit configurado.")

    mode_to_use = payload.mode
    if mode_to_use == "auto":
        mode_to_use = client.sheet_auto_mode or "copilot"

    # Marcar en el Sheet si se especificó fila y existe URL
    sheet_url = (brand.sheets_url if brand else None) or client.sheet_url
    from app.services.google_automation_service import GoogleAutomationService
    g_svc = GoogleAutomationService()

    if sheet_url and payload.sheet_row_ref and payload.sheet_row_ref.isdigit():
        try:
            g_svc.update_sheet_row_status(
                sheet_url=sheet_url,
                row_number=int(payload.sheet_row_ref),
                estado="Procesando"
            )
        except Exception as e:
            logger.warning(f"No se pudo actualizar estado en Sheet a Procesando: {e}")

    # 1. Generar copy con Copy Service
    total_slides = 6
    slides_copy = generate_carousel_slides_copy(
        topic=payload.topic,
        total_slides=total_slides,
        brand_name=brand.name
    )

    # 2. Crear registro de Contenido
    new_content = Content(
        brand_id=brand.id,
        type=payload.format or "carousel",
        title=payload.topic,
        hook_text=slides_copy[0].get("title", payload.topic) if slides_copy else payload.topic,
        caption_copy=f"💡 {payload.topic}\n\nEn {brand.name} priorizamos tu bienestar con tecnología y calidez.\n\n#SaludBucal #EsteticaDental #{brand.name.replace(' ', '')}",
        hashtags=f"#{brand.name.replace(' ', '')} #SaludDental #TecnoGen",
        status="generating" if mode_to_use == "autonomous" else "ready_for_review",
        source="google_sheet",
        sheet_row_ref=payload.sheet_row_ref,
        total_slides=total_slides
    )
    db.add(new_content)
    db.commit()
    db.refresh(new_content)

    # 3. Crear las láminas
    for idx, s_data in enumerate(slides_copy, start=1):
        slide = Slide(
            content_id=new_content.id,
            slide_number=idx,
            slide_type=s_data.get("slide_type", "content"),
            prompt_used=s_data.get("title", ""),
            status="pending"
        )
        db.add(slide)

    # 4. Actualizar métricas del cliente
    client.videos_generated_this_month = (client.videos_generated_this_month or 0) + 1
    client.sheet_last_sync_at = datetime.utcnow()
    db.commit()

    # 5. Si es Autónomo, lanzar renderizado en segundo plano
    if mode_to_use == "autonomous":
        background_tasks.add_task(process_content_generation, new_content.id, SessionLocal)

    viewer_url = f"/app/viewer/{new_content.id}"

    log = AuditLog(
        user_id=current_user.id,
        user_email=current_user.email,
        target_user_id=client.id,
        target_email=client.email,
        action="agent_sheet_trigger",
        details=f"Generación disparada desde Sheet (Fila {payload.sheet_row_ref}). Modo: {mode_to_use}. Contenido: {new_content.id}"
    )
    db.add(log)
    db.commit()
    db.commit()

    return {
        "success": True,
        "job_id": new_content.id,
        "client_email": client.email,
        "title": new_content.title,
        "status": new_content.status,
        "mode_executed": mode_to_use,
        "viewer_url": viewer_url,
        "message": "Trabajo recibido y procesado exitosamente por el Agente TecnoGen"
    }


# -------------------------------------------------------------
# 6. FINOPS & API EXPENSES (Solo SuperAdmin)
# -------------------------------------------------------------
@router.get("/finops", response_model=FinopsResponse)
def get_finops_breakdown(
    current_user: User = Depends(get_current_superadmin),
    db: Session = Depends(get_db)
):
    clients = db.query(User).filter(User.role.in_(["client", "agency"])).all()
    mrr_revenue = sum(c.plan_price_monthly or 150 for c in clients if c.commercial_status in ["active", "trial"])
    
    # Consumo simulado/calculado de proveedores externos
    providers = [
        FinopsProvider(
            provider="OpenAI / ChatGPT Plus API",
            service_type="Copywriting, Ganchos & GPT-4o-mini",
            cost_this_month_usd=14.80,
            quota_info="450K tokens consumidos",
            status="healthy",
            last_updated="Hace 5 min"
        ),
        FinopsProvider(
            provider="HeyGen Avatar & Video Rendering",
            service_type="Síntesis de Avatares Clínicos Hiperrealistas",
            cost_this_month_usd=48.50,
            quota_info="38.5 mins consumidos",
            status="healthy",
            last_updated="Hace 12 min"
        ),
        FinopsProvider(
            provider="ElevenLabs Voice AI",
            service_type="Clonación de Voz & Síntesis de Audio",
            cost_this_month_usd=11.20,
            quota_info="120K caracteres de audio",
            status="healthy",
            last_updated="Hace 1 hora"
        ),
        FinopsProvider(
            provider="Cloudinary & S3 Storage",
            service_type="CDN y Entrega de Videos / Carruseles",
            cost_this_month_usd=4.50,
            quota_info="28 GB de ancho de banda",
            status="healthy",
            last_updated="Hoy 08:00"
        )
    ]
    
    total_api_cost = sum(p.cost_this_month_usd for p in providers)
    net_margin = float(mrr_revenue) - total_api_cost
    margin_percentage = (net_margin / float(mrr_revenue) * 100) if mrr_revenue > 0 else 0.0
    
    return {
        "gross_revenue_mrr_usd": float(mrr_revenue),
        "total_api_cost_usd": float(total_api_cost),
        "net_margin_usd": float(net_margin),
        "margin_percentage": round(margin_percentage, 1),
        "providers": providers
    }


# -------------------------------------------------------------
# 7. EQUIPO DE SOPORTE & AUDITORÍA
# -------------------------------------------------------------
@router.get("/team", response_model=List[TeamMemberItem])
def list_team_members(
    current_user: User = Depends(get_current_superadmin),
    db: Session = Depends(get_db)
):
    team = db.query(User).filter(User.role.in_(["superadmin", "admin", "support"])).all()
    return [{
        "id": m.id,
        "email": m.email,
        "full_name": m.full_name,
        "role": m.role,
        "created_at": m.created_at
    } for m in team]


@router.post("/team", status_code=status.HTTP_201_CREATED)
def create_team_member(
    payload: TeamMemberCreate,
    current_user: User = Depends(get_current_superadmin),
    db: Session = Depends(get_db)
):
    existing = db.query(User).filter(User.email == payload.email).first()
    if existing:
        raise HTTPException(status_code=400, detail="El correo ya se encuentra registrado")
    
    new_member = User(
        email=payload.email,
        password_hash=get_password_hash(payload.password),
        full_name=payload.full_name,
        role=payload.role,
        commercial_status="active"
    )
    db.add(new_member)
    db.commit()
    db.refresh(new_member)
    
    log = AuditLog(
        user_id=current_user.id,
        user_email=current_user.email,
        target_user_id=new_member.id,
        target_email=new_member.email,
        action="create_team_member",
        details=f"Nuevo miembro del equipo creado con rol: {payload.role}"
    )
    db.add(log)
    db.commit()
    
    return {"message": "Miembro del equipo registrado con éxito", "id": new_member.id}


@router.get("/audit-logs")
def get_audit_logs(
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    logs = db.query(AuditLog).order_by(AuditLog.created_at.desc()).limit(50).all()
    return [{
        "id": l.id,
        "user_email": l.user_email,
        "target_email": l.target_email,
        "action": l.action,
        "details": l.details,
        "created_at": l.created_at
    } for l in logs]


@router.get("/live-logs")
def get_live_logs(
    limit: int = 100,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    """Devuelve los últimos logs del sistema y contenidos recientes con su estado detallado."""
    recent_contents = db.query(Content).order_by(Content.created_at.desc()).limit(20).all()
    items = []
    for c in recent_contents:
        slides = db.query(Slide).filter(Slide.content_id == c.id).all()
        items.append({
            "id": c.id,
            "title": c.title,
            "status": c.status,
            "total_slides": c.total_slides,
            "slides_ready": sum(1 for s in slides if s.status == "generated"),
            "slides_failed": sum(1 for s in slides if s.status == "failed"),
            "created_at": c.created_at.isoformat() if c.created_at else None,
            "updated_at": c.updated_at.isoformat() if c.updated_at else None,
        })
    return {
        "status": "online",
        "recent_contents": items
    }


@router.post("/resolve-stuck")
def resolve_stuck_contents(
    background_tasks: BackgroundTasks,
    current_user: User = Depends(get_current_ops_user),
    db: Session = Depends(get_db)
):
    """Detecta contenidos en estado 'generating' trabados y los procesa de inmediato."""
    stuck = db.query(Content).filter(Content.status == "generating").all()
    fixed_count = 0
    for c in stuck:
        background_tasks.add_task(process_content_generation, c.id, SessionLocal)
        fixed_count += 1
    return {"message": f"Se relanzaron {fixed_count} contenidos trabados", "count": fixed_count}
