---
title: "Modelo de Datos y Esquema Relacional"
description: "Estructura DDL PostgreSQL, tablas, llaves foráneas, índices y ciclo de vida de entidades para TecnoGen Studio."
tags:
  - database
  - postgresql
  - ddl
  - sql
  - alembic
---

# 🗄️ Modelo de Datos (PostgreSQL 16+)

El modelo de datos garantiza integridad referencial estricta, trazabilidad financiera mediante un ledger de auditoría y soporte multi-tenant completo.

---

## 📊 Tablas Principales

### 1. `users`
* **Campos:** `id` (UUID PK), `email` (Unique), `password_hash`, `full_name`, `role` (`admin`, `client`, `agency`), `plan_tier` (`starter`, `growth`, `agency`), `credits_balance` (INT >= 0), `google_oauth_token`, `google_refresh_token`, `stripe_customer_id`, `plan_renewal_date`, `created_at`, `updated_at`.
* **Regla:** `credits_balance` actúa como caché desnormalizada de lectura rápida; la suma de `credit_ledger` es la fuente de verdad.

### 2. `credit_ledger` (Ledger Inmutable)
* **Campos:** `id` (UUID PK), `user_id` (FK), `amount` (INT con signo), `action_type` (`monthly_renewal`, `pack_purchase`, `generate_carousel`, `generate_post`, `regenerate_slide`, `admin_adjustment`), `reference_id` (UUID lógico), `description` (TEXT), `created_at`.

### 3. `brands` (Brand Kit Multi-Tenant)
* **Campos:** `id` (UUID PK), `user_id` (FK), `name`, `primary_color`, `accent_color`, `bg_color`, `font_style_title`, `font_style_body`, `logo_position`, `logo_width_px`, `gdrive_input_folder_id`, `gdrive_output_folder_id`, `sheets_url`, `metricool_user_token`, `metricool_blog_id`, `brand_rules` (JSONB), `created_at`.

### 4. `brand_assets`
* **Campos:** `id` (UUID PK), `brand_id` (FK), `asset_type` (`logo_white`, `logo_color`, `logo_black`, `photo_person`, `photo_product`), `label`, `file_url`, `gdrive_file_id`, `mime_type`, `created_at`.

### 5. `contents`
* **Campos:** `id` (UUID PK), `brand_id` (FK), `type` (`carousel`, `single_post`, `video_reel`), `title`, `hook_text`, `caption_copy`, `hashtags`, `status` (`draft`, `copy_approved`, `generating`, `ready_for_review`, `approved`, `scheduled`, `published`, `failed`), `source`, `sheet_row_ref`, `total_slides`, `metricool_post_id`, `scheduled_for`, `version`, `created_at`, `updated_at`.

### 6. `slides`
* **Campos:** `id` (UUID PK), `content_id` (FK), `slide_number`, `slide_type` (`cover`, `content`, `comparison`, `data_highlight`, `cta`), `image_url`, `gdrive_file_id`, `prompt_used`, `feedback`, `status` (`pending`, `generating`, `generated`, `approved`, `rejected`, `failed`), `version`, `created_at`.
* **Constraint Unique:** `(content_id, slide_number, version)`.

### 7. `api_keys`
* **Campos:** `id` (UUID PK), `user_id` (FK), `key_hash` (SHA-256), `key_prefix` (ej: `tg_live_xxx`), `label`, `is_active`, `last_used_at`, `created_at`.
