# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-09-23  
> **Fase Actual:** Fase 2 — Refactorización UI/UX, Brand Kit 10 Layouts, 5 Carpetas Drive & Motor Estricto de IA  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** Hostinger VPS (`72.62.107.109`) — Backend: `:8018` / Frontend: `:5192`

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS B2B multi-tenant que genera carruseles y posts visuales con IA generativa, composición determinista de logos (Pillow RGBA), 10 estilos de distribución y maquetación gráfica, almacenamiento Zero-Storage (Google Drive con 5 carpetas especializadas) y gestión granular de regeneración slide por slide.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Documentación & PRD** | 🟢 Completado | PRD y Wiki de 3 capas sincronizadas con MetaWiki Global. |
| **Mi Perfil Editable** | 🟢 Completado | Edición de Nombre, Email, Nombre de Marca y Contraseña (`/app/profile`). |
| **Modelos & IA Dinámica** | 🟢 Completado | Flujo Empresa -> Key -> Modelos dinámicos. Uso estricto de API Key de usuario (`/app/ai-settings`). |
| **Brand Kit (10 Layout Cards)** | 🟢 Completado | 10 tarjetas con mini-wireframes interactivos de distribución de texto, logo e imagen (`/app/brands`). |
| **Google Drive 5 Carpetas** | 🟢 Completado | Carpetas de Logos, Sujetos, Manuales, Plantillas, Productos + tags `[TAG:nombre]` para Excel/Sheets (`/app/integrations`). |
| **Mi Biblioteca & Visor** | 🟢 Completado | Visor 1024x1536 con polling automático, borrado de carruseles y regeneración slide por slide / todo (`/app/library`, `/app/viewer/:id`). |
| **Backend & Motor OpenAI** | 🟢 Activo (:8018) | Validación estricta con OpenAI Image API, prompts basados en layout y reembolso atómico. |
| **Frontend React + Vite** | 🟢 Activo (:5192) | Interfaz dark mode con Tailwind CSS, Lucide icons y estado reactivo. |

---

## 📋 Próximas Tareas Inmediatas
- [x] Perfil de usuario editable completo.
- [x] Flujo de selección de IA estricto por API Key del usuario.
- [x] 5 ranuras de carpetas Google Drive con ayudas contextuales `(?)` y convenciones de nombres.
- [x] 10 tarjetas de composición y distribución visual en Brand Kit.
- [x] Biblioteca de carruseles con eliminación y visor con regeneración individual.
