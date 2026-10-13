# 📊 Estado Operativo — TecnoGen Studio

> **Última actualización:** 2026-10-13  
> **Fase Actual:** Fase 5 — Selección Visual de Fotos de Especialistas, Prevención de Paginadores en Post Único, Overlay Vectorial de Logo y API OpenAI Activa  
> **URL Producción:** `https://studio.tecnogen.ar`  
> **Servidor Destino:** Ploi `errante` (`72.61.34.92`) | Dominio: `studio.tecnogen.ar`  
> **Dev Local:** `http://localhost:8018/` (Laravel SaaS)

---

## 🎯 Resumen Ejecutivo

TecnoGen Studio es una plataforma SaaS multi-tenant que genera carruseles y posts visuales odontológicos con IA (`gpt-image-2.5-sunburst` y `gpt-4o-mini`). Cuenta con:
1. **Generador de Contenido Unificado (`/app/create`):**
   - Selector dinámico de formato (Carrusel, Post Único 1080x1080, Reel 1080x1920).
   - **Selector Visual de Fotos Reales del Especialista:** Ahora se puede elegir exactamente qué fotografía de la doctora usar (con miniatura interactiva y modal `#charPhotosModal` con galería completa de fotos desde Drive), o dejar la opción de selección aleatoria rotativa.
   - **Catálogo de Paginadores Depurado:** Eliminada la duplicación de numeración (`#38 · Hélice de ADN` en lugar de `#38 · 38. ...`).
   - **Prevención Estricta de Paginadores en Post Único:** Desactivación de paginadores y directrices negativas explícitas para eliminar barras de progreso, 7 líneas o indicadores '1/1' en piezas únicas.
   - **Overlay Vectorial de Logos:** Composición de logo en alta definición sin deformación ni estiramiento mediante GD alpha-channel.
   - **OpenAI API Activa:** Migrada la clave a cuenta con saldo disponible (resuelve HTTP 429 quota).
2. **Renderizado de Imágenes con IA:** Motor OpenAI (`gpt-image-2.5-sunburst` y `dall-e-3`) integrado tanto al guardar como bajo demanda lámina por lámina desde la biblioteca.
3. **Biblioteca de Creaciones (`/app/creations`):** Gestión visual de piezas, visor Lightbox full-size, visor de Prompts Maestro y por lámina individual con copiado rápido (`#promptViewerModal`), renderizado individual/batch y enlace directo con la carpeta de Google Drive del usuario.
4. **Definición de Marca (`/app/brand`):** Integración con Manual de Identidad y scraping inteligente de logotipos desde Google Drive.
5. **Catálogo de Personajes (`/app/characters`):** Conectado a carpetas de fotos en Google Drive con selector de carátula interactivo y lista completa de fotos.

---

## 🚦 Semáforo de Componentes

| Componente | Estado | Descripción |
|:---|:---|:---|
| **Generador de Contenido** | 🟢 100% Operativo | `/app/create` con selector visual de fotos de doctoras, sin paginadores en post único, y catálogo sin duplicación. |
| **API OpenAI (Textos & Copys)** | 🟢 100% Operativo | Clave activa con créditos (`gpt-4o-mini` y `gpt-image-2.5-sunburst`), error 429 resuelto en dev y producción. |
| **Renderizado de Imágenes IA** | 🟢 100% Operativo | Generación de piezas con logo vector nítido y preservación de aspecto. |
| **Biblioteca de Creaciones** | 🟢 100% Operativo | `/app/creations` con visor de prompts, renderizado por lámina y conexión a Google Drive. |
| **Sitio en Producción** | 🟢 100% Online | **`https://studio.tecnogen.ar`** desplegado en Ploi con SSL y almacenamiento symlinkeado. |
| **Repositorio Oficial** | 🟢 Sincronizado | `https://github.com/elbuje/tecnogen-studio` (Ramas `main` y `dev`). |

---

## 🔑 Credenciales de Acceso en Producción (`studio.tecnogen.ar`)
1. **Administrador:** `mfmujic@gmail.com` / `AdminTecnoGen2026!` (Plan Agency, 9999 créditos).
2. **Cliente Preconfigurado:** `mmujica@tecnobrain.com.ar` / `JMOdonto2026!` (Marca: JM Odontología Integral, 250 créditos).

