# 🔗 Fuentes de Verdad — TecnoGen Studio

Este archivo consolida las configuraciones, infraestructura, puertos, credenciales y referencias canónicas del proyecto TecnoGen Studio.

---

## 🖥️ Infraestructura y Servidores

| Entorno | Servidor | IP | URL Pública | Tipo de Host |
|:---|:---|:---|:---|:---|
| **Desarrollo (Local)** | Hostinger VPS KVM 4 | `72.62.107.109` | `http://localhost:8018` / `http://localhost:5192` | VPS Linux (Ubuntu) |
| **Producción** | Ploi `errante` | `72.61.34.92` | `https://studio.tecnogen.ar` | Servidor Ploi / Docker / Nginx |

---

## 🔌 Puertos Asignados (Desarrollo)

* **Backend (FastAPI):** Puerto `8018`
* **Frontend (Vite Dev Server):** Puerto `5192`
* **Redis (Workers Celery):** Puerto `6379` (instancia compartida o Docker local)
* **PostgreSQL:** Puerto `5432` (PostgreSQL 16 / SQLite en dev inicial)

---

## 🗄️ Base de Datos & Almacenamiento

* **Motor:** PostgreSQL 16+ con extensión `pgcrypto` (`gen_random_uuid()`)
* **ORM & Migraciones:** SQLAlchemy 2.0 + Alembic
* **Archivos del Cliente:** Google Drive API v3 (Zero-Storage local)
* **Almacenamiento Efímero:** `/tmp` local (eliminado post-subida) / Cloudflare R2 (fallback 30 días TTL)

---

## 🌐 Conexión con Ecosistema Global

* **MetaWiki Global:** [`~/.agent/wiki/index.md`](file:///home/mfmujic/.agent/wiki/index.md)
* **Registro de Puertos:** [`/home/mfmujic/PORT_REGISTRY.md`](file:///home/mfmujic/PORT_REGISTRY.md)
* **Configuración del Servidor:** [`/home/mfmujic/SERVER_CONFIG.md`](file:///home/mfmujic/SERVER_CONFIG.md)
