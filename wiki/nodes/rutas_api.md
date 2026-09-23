---
title: "Especificación de Rutas y Endpoints REST"
description: "Mapeo completo de endpoints de autenticación, marcas, generación de contenido, integraciones y API pública."
tags:
  - api
  - rest
  - fastapi
  - endpoints
---

# 🌐 Rutas & Endpoints de la API

La API de TecnoGen Studio corre en FastAPI y expone endpoints bajo `/api/v1/` autenticados mediante JWT (`Authorization: Bearer <token>`) o API Key (`X-KS-API-Key`).

---

## 📋 Catálogo de Rutas

### 🔐 Autenticación & Perfil (`/api/v1/auth`, `/api/v1/user`)
* `POST /api/v1/auth/register` — Registro con hash bcrypt + asignación de plan.
* `POST /api/v1/auth/login` — Autenticación email/password y retorno de tokens.
* `POST /api/v1/auth/google` — OAuth 2.0 (scopes Google Drive + Profile).
* `POST /api/v1/auth/refresh` — Renovación de token de acceso.
* `GET /api/v1/user/profile` — Datos de usuario, plan, balance y estado de conexiones.

### 🎨 Marcas & Brand Assets (`/api/v1/brands`)
* `GET /api/v1/brands` — Lista marcas del usuario con conteo de assets.
* `POST /api/v1/brands` — Crea marca (validando cuota según plan).
* `POST /api/v1/brands/{brand_id}/assets` — Sube logo o foto (Multipart / auto-conversión HEIF).
* `POST /api/v1/brands/{brand_id}/connect-drive` — Vincula carpetas de input/output.
* `POST /api/v1/brands/{brand_id}/connect-sheets` — Vincula URL de Google Sheets.
* `POST /api/v1/brands/{brand_id}/connect-metricool` — Configura credenciales Metricool.

### 🖼️ Generación & Slides (`/api/v1/contents`)
* `POST /api/v1/contents/generate` — Inicia generación de carrusel (202 Accepted + descuento atómico).
* `GET /api/v1/contents` — Listado con filtros de marca y estado.
* `GET /api/v1/contents/{content_id}` — Detalle completo del contenido y slides.
* `POST /api/v1/contents/{content_id}/slides/{slide_number}/regenerate` — Regenera slide específico (+1 crédito).
* `POST /api/v1/contents/{content_id}/approve` — Marca carrusel como aprobado para distribución.
* `GET /api/v1/contents/{content_id}/download` — Descarga ZIP con imágenes de alta resolución y `copy.txt`.

### 🔗 Integraciones (`/api/v1/integrations`)
* `GET /api/v1/integrations/metricool/best-times` — Consulta heatmap de mejores horarios.
* `POST /api/v1/integrations/metricool/schedule` — Normaliza imágenes y agenda en Metricool.

### 🤖 API Pública para Agentes & MCP (`/api/v1/public`)
* `POST /api/v1/public/generate` — Dispara generación desde agentes externos con API Key.

### 💳 Créditos & Facturación (`/api/v1/billing`)
* `GET /api/v1/billing/balance` — Saldo y movimientos de ledger.
* `POST /api/v1/billing/purchase-pack` — Compra de paquetes de créditos (25, 50, 100).
