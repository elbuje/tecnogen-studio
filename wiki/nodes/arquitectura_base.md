---
title: "Arquitectura Base del Sistema"
description: "Estructura modular de TecnoGen Studio, pipeline de procesamiento asíncrono, flujo de datos y dependencias principales."
tags:
  - arquitectura
  - backend
  - fastapi
  - celery
  - redis
---

# 🏗️ Arquitectura Base de TecnoGen Studio

TecnoGen Studio es una plataforma SaaS multi-tenant desacoplada diseñada para procesar pipelines de generación gráfica intensiva sin bloquear el hilo principal de la aplicación web.

---

## 🧩 Componentes Principales

```mermaid
flowchart TB
    subgraph INGESTA ["1. Capa de Ingesta"]
        A1[Google Sheets API] 
        A2[Web Form UI / Fast Creation]
        A3[REST API / MCP Connector]
    end

    subgraph BACKEND ["2. Backend FastAPI (Puerto 8018)"]
        B1[Auth & RBAC JWT / API Keys]
        B2[Content Orchestrator]
        B3[Copywriter Service - GPT-4o-mini]
        B4[Credit Ledger & Atomic Billing]
    end

    subgraph WORKERS ["3. Cola Asíncrona (Celery + Redis)"]
        W1[Image Generation Task - OpenAI Image API]
        W2[Brand Composer Task - Pillow RGBA Engine]
        W3[Drive Dispatcher Task - Google Drive API]
        W4[SSE / WebSocket Notifier]
    end

    subgraph STORAGE ["4. Almacenamiento"]
        S1[(PostgreSQL 16 - Estado & Metadata)]
        S2[(Google Drive del Cliente - Zero Storage)]
        S3[Cloudflare R2 / Temp Local - Fallback 30d]
    end

    subgraph CLIENT ["5. Frontend SPA (Puerto 5192)"]
        F1[Landing & Pricing]
        F2[Dashboard & Brand Kit]
        F3[Interactive Slide Approval Viewer 1024x1536]
        F4[Metricool Best Times & Scheduler]
    end

    A1 & A2 & A3 --> B2
    B2 --> B3
    B2 --> B4
    B2 --> S1
    B2 -->|Encolar Tarea 202 Accepted| W1
    W1 --> W2
    W2 --> W3
    W3 --> S2
    W3 --> S1
    W4 --> F3
    S1 --> F2
    S2 --> F3
```

---

## ⚡ Flujo de Vida de una Generación

1. **Recepción & Validación:** El cliente o agente externo envía una solicitud con `brand_id`, `type`, `title`, `total_slides` y configuración de sujeto/marca.
2. **Reserva Atómica de Créditos:** En una transacción SQL aislada, se verifica saldo (`credits_balance >= total_slides`) y se descuenta, registrando el movimiento negativo en `credit_ledger`.
3. **Generación de Copy (si aplica):** GPT-4o-mini genera títulos, subtítulos, hooks y cuerpo por slide basándose en las directrices de la marca.
4. **Despacho Asíncrono:** La tarea entra a Celery con backend Redis. La API responde inmediatamente `202 Accepted`.
5. **Generación Gráfica Slide por Slide:**
   - OpenAI Image API genera la base vertical `1024x1536` en alta calidad.
   - Pillow (`composer_service`) superpone el logo oficial de la marca respetando canales alfa y márgenes.
   - La imagen resultante se sube inmediatamente al Google Drive del cliente.
6. **Manejo de Fallas & Auto-Reembolso:** Si falla la generación de un slide individual, se reembolsa 1 crédito automáticamente a la cuenta del usuario y se marca el slide con estado `failed` permitiendo reintento granular.
7. **Revisión en Visor:** El usuario visualiza la pieza generada en el visor interactivo, pudiendo aprobar o regenerar láminas individuales con feedback específico.
