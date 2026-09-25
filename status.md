# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-09-25  
> **Fase Actual:** Fase 3 — Producción Activa en Ploi `errante` & GitHub Oficial  
> **URL Producción:** `https://studio.tecnogen.ar`  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** Hostinger VPS (`72.62.107.109`) — Backend: `:8018` / Frontend: `:5192`

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS B2B multi-tenant que genera carruseles y posts visuales con IA generativa (`gpt-image-2.5-sunburst`), composición determinista de logos y fotos de sujetos reales (Pillow RGBA + `pillow-heif`), numeración de láminas obligatoria (`1/8` .. `8/8`), almacenamiento Zero-Storage en Google Drive y visor interactivo de diseño tipo Figma/Canva.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Sitio en Producción** | 🟢 100% Online | **`https://studio.tecnogen.ar`** con SSL Let's Encrypt y HTTP/2 activo. |
| **Repositorio Oficial** | 🟢 Sincronizado | `https://github.com/elbuje/tecnogen-studio` (Ramas `main` y `dev`). |
| **Backend en Producción** | 🟢 Activo (:8028) | FastAPI + SQLite persistente gestionado como Daemon supervisado en Ploi (`id: 226698`). |
| **Frontend en Producción** | 🟢 Compilado | SPA React 18 + Vite + Tailwind CSS servido directamente por Nginx en `/public`. |
| **Pipeline IA & Modelos BD** | 🟢 Operativo | Resolución dinámica desde `ai_settings` en BD con soporte nativo para `gpt-image-2.5-sunburst` y API Key del usuario. |
| **Manual de Marca JM** | 🟢 Integrado | Paleta oficial (`#16345F`, `#7DD3FC`, `#1D1D1B`), logo oficial y fotos iPhone `.HIF` de Dra. Jessica (`pillow-heif`). |
| **Compositor HD (1080x1350)** | 🟢 Operativo | Numeración de láminas (`1/8`, etc.), badges en español, encuadre de profesional y botón de deslizamiento. |
| **Visor Rediseñado** | 🟢 Completado | Canvas centralizado 1080x1350, filmstrip inferior numerado, inspector con guion editable y descarga masiva de PNGs. |
| **Enlaces a Drive en UI** | 🟢 Completado | Botones "Abrir Carpeta" y "Abrir Hoja" con enlaces externos directos en `Integrations.tsx`. |

---

## 🔑 Credenciales de Acceso en Producción (`studio.tecnogen.ar`)
1. **Administrador:** `mfmujic@gmail.com` / `AdminTecnoGen2026!` (Plan Agency, 9999 créditos).
2. **Cliente Preconfigurado:** `mmujica@tecnobrain.com.ar` / `JMOdonto2026!` (Marca: JM Odontología Integral, 250 créditos).
