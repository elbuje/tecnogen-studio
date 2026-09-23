---
title: "Estrategia de Despliegue en Producción — Servidor Errante"
description: "Configuración y aprovisionamiento en Ploi servidor errante (72.61.34.92) para studio.tecnogen.ar."
tags:
  - deploy
  - ploi
  - errante
  - nginx
  - docker
  - produccion
---

# 🚀 Despliegue en Producción — Servidor Errante

TecnoGen Studio tendrá su entorno de producción alojado en el servidor gestionado por Ploi: **`errante` (`72.61.34.92`)** bajo el dominio **`studio.tecnogen.ar`**.

---

## 🏗️ Topología en `errante`

```mermaid
flowchart LR
    Internet((Internet / Clientes)) -->|HTTPS / SSL Let's Encrypt| Nginx[Nginx Reverse Proxy]
    Nginx -->|/api/*| FastAPI[FastAPI Backend - Port 8000/Uvicorn]
    Nginx -->|/* (SPA)| ViteDist[Static Files /app/static]
    FastAPI --> Celery[Worker Celery]
    Celery <--> Redis[(Redis 7)]
    FastAPI <--> Postgres[(PostgreSQL 16)]
    Celery <--> Postgres
    Celery -->|OAuth / Drive API| GDrive[(Google Drive Cliente)]
    Celery -->|REST API| Metricool[(Metricool API)]
```

---

## ⚙️ Configuración del Sitio en Ploi

* **Dominio:** `studio.tecnogen.ar`
* **DNS:** Registro A apuntando a `72.61.34.92`
* **Tipo de Sitio en Ploi:** Docker Compose o Python/FastAPI Daemon con Nginx Proxy y base de datos PostgreSQL.
* **Certificado SSL:** Let's Encrypt auto-renovable vía Ploi.
* **Daemons Ploi (Background Workers):**
  - Celery Worker: `celery -A app.workers.celery_app worker --loglevel=info --concurrency=4`
* **Variables de Entorno Seguras:** Inyectadas mediante Ploi Environment Editor (`.env`).
