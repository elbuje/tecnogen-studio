# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-10-10  
> **Fase Actual:** Fase 4 — Generador con Micro-IA por Campo, Autoparser de Guiones y Creaciones en Google Drive  
> **URL Producción:** `https://studio.tecnogen.ar`  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** `http://localhost:8018/` (Laravel SaaS)

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS multi-tenant que genera carruseles y posts visuales odontológicos con IA (`gpt-image-2.5-sunburst` y `gpt-4o-mini`). Cuenta con:
1. **Generador de Contenido Unificado (`/app/create`):** Autoparseo de guiones externos (ChatGPT, Sheets, Notion, WhatsApp) y micro-asistente interactivo `✨ IA` en cada campo de texto individual (título, gancho, láminas, badges, CTA y caption).
2. **Biblioteca de Creaciones (`/app/creations`):** Gestión de publicaciones y enlace directo con la carpeta de Google Drive del usuario.
3. **Definición de Marca (`/app/brand`):** Integración con el Manual de Identidad y scraping inteligente de logotipos desde Google Drive.
4. **Catálogo de Personajes (`/app/characters`):** Conectado a carpetas de fotos en Google Drive con selector de carátula interactivo.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Generador de Contenido** | 🟢 100% Operativo | `/app/create` con asistencia de IA por campo y parser de guiones. |
| **Biblioteca de Creaciones** | 🟢 100% Operativo | `/app/creations` conectado a la carpeta de Google Drive del usuario. |
| **Sitio en Producción** | 🟢 100% Online | **`https://studio.tecnogen.ar`** en Ploi con SSL Let's Encrypt. |
| **Repositorio Oficial** | 🟢 Sincronizado | `https://github.com/elbuje/tecnogen-studio` (Ramas `main` y `dev`). |
| **Catálogo de 28 Tipos Visuales** | 🟢 100% Generado | 28 demos visuales en español generadas con `gpt-image-2.5-sunburst` y modal slider activo. |
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
