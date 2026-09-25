---
title: "Wiki de TecnoGen Studio"
description: "Plataforma SaaS B2B de generación de contenido visual con IA (Carruseles, Posts y Reels) multi-marca con arquitectura Zero-Storage y distribución Metricool."
tags:
  - saas
  - ai
  - fastapi
  - react
  - vite
  - postgresql
  - celery
  - redis
  - gdrive
  - metricool
  - ploi
  - errante
---

# 🎨 TecnoGen Studio — Wiki del Proyecto

Plataforma SaaS multi-tenant que permite a dueños de negocio, clínicas y agencias de marketing transformar guiones estructurados o ideas en piezas visuales de alto impacto (Carruseles y Posts) con IA generativa (`gpt-image-2.5-sunburst`), composición determinista de marca (Pillow) y almacenamiento directo en Google Drive del cliente (Zero-Storage para el operador).

---

## 🗺️ Nodos de Conocimiento

* [[nodes/arquitectura_base]] — Arquitectura global del sistema (FastAPI + Vite/React + Celery/Redis + PostgreSQL).
* [[nodes/modelo_datos]] — Esquema relacional PostgreSQL (Usuarios, Marcas, Assets, Contenidos, Slides, Credit Ledger, API Keys).
* [[nodes/rutas_api]] — Especificación completa de endpoints REST `/api/v1/` y API pública para agentes MCP.
* [[nodes/servicios_ia_composer]] — Motor de generación de imágenes OpenAI, templates de prompts dinámicos y motor de composición alfa Pillow.
* [[nodes/zero_storage_gdrive]] — Estrategia de almacenamiento efímero, integración con Google Drive v3 y Google Sheets.
* [[nodes/frontend_vite_react]] — Arquitectura de Frontend (Vite + React 18 + Tailwind + Shadcn UI), visor interactivo 1024x1536 y Brand Kit.
* [[nodes/despliegue_produccion_errante]] — Estrategia de despliegue en servidor producción Ploi `errante` (`72.61.34.92`), Nginx, SSL y dominios (`studio.tecnogen.ar`).

---

## 🔗 Fuentes de Verdad & Enlaces

* **Fuentes del Proyecto:** [[sources]]
* **Historial & Bitácora:** [[log]]
* **Estado Operativo:** [`status.md`](file:///home/mfmujic/tecnogen-studio/status.md)
* **MetaWiki Global:** [`~/.agent/wiki/index.md`](file:///home/mfmujic/.agent/wiki/index.md)

---

## 📜 Sesiones de Trabajo

* [[sessions/2026-09-25-brand-manual-and-ai-pipeline]] — Extracción de identidad de marca oficial (JM Odontología Integral), soporte para fotos HIF de Google Drive, resolución dinámica de modelos de IA en base de datos (`gpt-image-2.5-sunburst`) y rediseño del visor y botones de carpeta.
* [[sessions/2026-09-24-automatizacion-google-sheets-sync-y-prompts-ia]] — Automatización de Google Sheets, sincronización asíncrona, visor de prompts de IA y resolución de errores de producción.
* [[sessions/2026-09-23-especificacion-prd-e-instalacion-wiki]] — Análisis de PRD v1.0, instalación de arquitectura Wiki de 3 capas y definición de roadmap hacia Ploi `errante`.
