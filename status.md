# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-10-13  
> **Fase Actual:** Fase 4 — Generador con Micro-IA Fiel, Selección Visual de Logos, Prompts Editables, Copy Alineado y Visor de Prompts en Biblioteca  
> **URL Producción:** `https://studio.tecnogen.ar`  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** `http://localhost:8018/` (Laravel SaaS)

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS multi-tenant que genera carruseles y posts visuales odontológicos con IA (`gpt-image-2.5-sunburst` y `gpt-4o-mini`). Cuenta con:
1. **Generador de Contenido Unificado (`/app/create`):** Selector dinámico de formato (Carrusel, Post Único 1080x1080, Reel 1080x1920), selector dinámico de fondos de marca (`optgroups`), selector visual de variantes de logo con modal interactivo (`#logosModal`), micro-asistente interactivo `✨ IA` fiel al tema del usuario sin sesgos fijos, generación de copy/hashtags alineados al titular y compilador de prompt maestro editable (`<textarea>`) y copiable.
2. **Renderizado de Imágenes con IA:** Motor OpenAI (`gpt-image-2.5-sunburst` y `dall-e-3`) integrado tanto al guardar como bajo demanda lámina por lámina desde la biblioteca.
3. **Biblioteca de Creaciones (`/app/creations`):** Gestión visual de piezas, visor Lightbox full-size, visor de Prompts Maestro y por lámina individual con copiado rápido (`#promptViewerModal`), renderizado individual/batch y enlace directo con la carpeta de Google Drive del usuario.
4. **Definición de Marca (`/app/brand`):** Integración con Manual de Identidad y scraping inteligente de logotipos desde Google Drive.
5. **Catálogo de Personajes (`/app/characters`):** Conectado a carpetas de fotos en Google Drive con selector de carátula interactivo.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Generador de Contenido** | 🟢 100% Operativo | `/app/create` con modos Carrusel, Post Único y Reel, logos visuales, micro-IA fiel y prompt editable. |
| **Renderizado de Imágenes IA** | 🟢 100% Operativo | Generación de piezas 1024x1024 y almacenamiento en `storage/creations/`. |
| **Biblioteca de Creaciones** | 🟢 100% Operativo | `/app/creations` con visor de prompts, renderizado por lámina y conexión a Google Drive. |
| **Sitio en Producción** | 🟢 100% Online | **`https://studio.tecnogen.ar`** desplegado en Ploi con SSL y almacenamiento symlinkeado. |
| **Repositorio Oficial** | 🟢 Sincronizado | `https://github.com/elbuje/tecnogen-studio` (Ramas `main` y `dev`). |
| **Catálogo de 28 Tipos Visuales** | 🟢 100% Generado | 28 demos visuales en español generadas con `gpt-image-2.5-sunburst`. |
| **Catálogo de 49 Paginadores** | 🟢 100% Generado | 49 estilos de paginadores y deslizadores interactivos. |

---

## 🔑 Credenciales de Acceso en Producción (`studio.tecnogen.ar`)
1. **Administrador:** `mfmujic@gmail.com` / `AdminTecnoGen2026!` (Plan Agency, 9999 créditos).
2. **Cliente Preconfigurado:** `mmujica@tecnobrain.com.ar` / `JMOdonto2026!` (Marca: JM Odontología Integral, 250 créditos).

