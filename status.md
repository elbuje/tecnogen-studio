# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-09-23  
> **Fase Actual:** Fase 1 — Especificación & Scaffolding de Arquitectura  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** Hostinger VPS (`72.62.107.109`) — Backend: `:8018` / Frontend: `:5192`

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS multi-tenant que permite generar carruseles y posts visuales de alto impacto con IA generativa, composición determinista de logos (Pillow RGBA) y almacenamiento en Google Drive del cliente (Zero-Storage).

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Documentación & PRD** | 🟢 Completado | PRD v1.0 analizado y documentado en Wiki. |
| **LLM Wiki (3 Capas)** | 🟢 Instalado | Nodos, flujos `.agent/`, MetaWiki y grafo `:5190` activos. |
| **Backend (FastAPI)** | 🟡 Pendiente Scaffolding | Modelos SQLAlchemy, Alembic, Schemas y Rutas `/api/v1/`. |
| **Workers & Celery** | 🟡 Pendiente Scaffolding | Tareas asíncronas de generación, composer y dispatch a Drive. |
| **Generación IA & Pillow** | 🟡 Pendiente Scaffolding | Integración OpenAI Image API + Composer de logos alfa. |
| **Frontend (Vite + React)** | 🟡 Pendiente Scaffolding | Dashboard, Brand Kit y Visor Interactivo 1024x1536. |
| **Integración Google Drive** | ⚪ Planificado (Fase 2) | OAuth2 + Drive API v3 (lectura fotos/escritura outputs). |
| **Integración Metricool** | ⚪ Planificado (Fase 3) | Best times heatmap y programación directa. |
| **Despliegue Ploi `errante`** | ⚪ Planificado | Docker Compose / Daemons en `72.61.34.92`. |

---

## 📋 Próximas Tareas Inmediatas
- [x] Instalación de LLM Wiki y conexión con Capa 1.
- [x] Registro de puertos de desarrollo en `PORT_REGISTRY.md`.
- [ ] Validación de decisiones técnicas con el usuario.
- [ ] Inicialización del repositorio y estructura de directorios (`backend/`, `frontend/`, `docker-compose.yml`).
- [ ] Creación de modelos ORM SQLAlchemy y migraciones Alembic.
