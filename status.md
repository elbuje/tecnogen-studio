# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-09-26  
> **Fase Actual:** Fase 3 — Producción Activa en Ploi `errante` & GitHub Oficial  
> **URL Producción:** `https://studio.tecnogen.ar`  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** Hostinger VPS (`72.62.107.109`) — Backend: `:8018` / Frontend: `:5192`

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS B2B multi-tenant que genera carruseles y posts visuales con IA generativa (`gpt-image-2.5-sunburst`), transmisión de assets reales mediante `client.images.edit` (fotos de profesionales en `.HIF` / `.PNG` decodificadas con `pillow-heif` y logotipos vectoriales/PNG transparentes), almacenamiento Zero-Storage en Google Drive, sincronización bidireccional con Google Sheets y visor interactivo de diseño tipo Figma/Canva.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Sitio en Producción** | 🟢 100% Online | **`https://studio.tecnogen.ar`** con SSL Let's Encrypt y HTTP/2 activo. |
| **Repositorio Oficial** | 🟢 Sincronizado | `https://github.com/elbuje/tecnogen-studio` (Ramas `main` y `dev`). |
| **Backend en Producción** | 🟢 Activo (:8028) | FastAPI + MySQL gestionado como Daemon supervisado en Ploi (`id: 226698`). |
| **Frontend en Producción** | 🟢 Compilado | SPA React 18 + Vite + Tailwind CSS servido directamente por Nginx en `/public`. |
| **Pipeline IA con Assets Reales** | 🟢 Operativo | `client.images.edit(model="gpt-image-2.5-sunburst", image=buf, prompt=prompt)` enviando la foto real y logo transparente oficial. |
| **Generación Carrusel #2** | 🟢 Completado | Carrusel "3 frases sobre sacarte una muela. ¿Mito o verdad? 🦷" (Dra. Karina) generado con 8 láminas en producción. |
| **Visor & Preview Público** | 🟢 Operativo | Acceso directo a previews vía `/app/viewer/:id` y endpoint `/api/v1/contents/public/:id`. |
| **Sincronización Google Sheets** | 🟢 Operativo | Lectura de columnas `PRESENCIA DOCTORA`, `ESTILO PAGINADOR`, `BADGE ESTILO`, `IDIOMA PROMPTS` y actualización de estado a `Ya realizado`. |

---

## 🔑 Credenciales de Acceso en Producción (`studio.tecnogen.ar`)
1. **Administrador:** `mfmujic@gmail.com` / `AdminTecnoGen2026!` (Plan Agency, 9999 créditos).
2. **Cliente Preconfigurado:** `mmujica@tecnobrain.com.ar` / `JMOdonto2026!` (Marca: JM Odontología Integral, 250 créditos).
