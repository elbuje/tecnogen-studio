# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-09-24  
> **Fase Actual:** Fase 3 — Producción Activa en Ploi `errante` & GitHub Oficial  
> **URL Producción:** `https://studio.tecnogen.ar`  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** Hostinger VPS (`72.62.107.109`) — Backend: `:8018` / Frontend: `:5192`

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS B2B multi-tenant que genera carruseles y posts visuales con IA generativa, composición determinista de logos (Pillow RGBA), 10 estilos de distribución y maquetación gráfica, almacenamiento Zero-Storage (Google Drive con 5 carpetas especializadas) y gestión granular de regeneración slide por slide.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Sitio en Producción** | 🟢 100% Online | **`https://studio.tecnogen.ar`** con SSL Let's Encrypt y HTTP/2 activo. |
| **Repositorio Oficial** | 🟢 Sincronizado | `https://github.com/elbuje/tecnogen-studio` (Ramas `main` y `dev`). |
| **Backend en Producción** | 🟢 Activo (:8028) | FastAPI + SQLite persistente gestionado como Daemon supervisado en Ploi (`id: 226698`). |
| **Frontend en Producción** | 🟢 Compilado | SPA React 18 + Vite + Tailwind CSS servido directamente por Nginx en `/public`. |
| **API Proxy Reverso** | 🟢 Conectado | Peticiones `/api/*` derivadas al puerto `8028` con autenticación JWT y CORS. |
| **Sincronización Sheets** | 🟢 Operativo | Botón de 1 clic en Dashboard e Integraciones con lectura de filas, guiones y estados. |
| **Generación Asíncrona IA** | 🟢 Activa | Desacople con `BackgroundTasks` en el servidor (autonomía total sin depender de la PC del cliente). |
| **Auditoría de Prompts** | 🟢 Completado | Visor de contenidos muestra el prompt exacto enviado a la IA para cada slide con botón de copiado. |
| **Brand Kit 100% Automatizado** | 🟢 Completado | Eliminadas las tarjetas de layout manual. Todo el diseño y contenido se rige por el Excel y Manual de Marca. |
| **Google Drive 5 Carpetas** | 🟢 Completado | Carpetas de Logos, Sujetos, Manuales, Plantillas, Productos (`/app/integrations`). |
| **Mi Biblioteca & Visor** | 🟢 Completado | Visor con polling automático, regeneración granular slide por slide y exportación (`/app/viewer/:id`). |
| **Multi-Look & Feel (Themes)** | 🟢 Completado | Selector instantáneo de 4 apariencias (Cyber Glow, HeyGen/Canva Studio, Claude Cowork, ChatGPT Canvas). |

---

## 🔑 Credenciales de Acceso en Producción (`studio.tecnogen.ar`)
1. **Administrador:** `mfmujic@gmail.com` / `AdminTecnoGen2026!` (Plan Agency, 9999 créditos).
2. **Cliente Preconfigurado:** `mmujica@tecnobrain.com.ar` / `JMOdonto2026!` (Marca: JM Odontología Integral, 250 créditos).
