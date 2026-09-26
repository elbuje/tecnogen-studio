# Sesión 2026-09-26 — Pipeline con Assets Reales en OpenAI Sunburst (`images.edit`)

## Contexto & Objetivos
- Reemplazo del método text-to-image puro (`client.images.generate`) por el endpoint de edición con referencia real (`client.images.edit` con modelo `gpt-image-2.5-sunburst`).
- Paso de los archivos binarios reales de fotografías de profesionales (`Karina1.HIF` decodificada con `pillow-heif`) y logotipos transparentes oficiales (`JM_blanco_negro.png`) desde Google Drive.
- Generación completa de las 8 diapositivas del Carrusel #2 («3 frases sobre sacarte una muela. ¿Mito o verdad? 🦷») y sincronización directa con producción (`studio.tecnogen.ar`).

---

## Implementación Técnica
1. **`AIImageService` (`backend/app/services/ai_image_service.py`):**
   - Incorporación de `build_reference_canvas(...)` para estructurar la composición base de referencia en memoria RGBA.
   - Envío de buffers binarios directamente a `client.images.edit(model="gpt-image-2.5-sunburst", image=buf, prompt=prompt)`.
2. **Soporte de Endpoints en Producción:**
   - Agregado de `POST /api/v1/contents/sync-import` para sincronización bidireccional inmediata.
   - Endpoint público `GET /api/v1/contents/public/{id}` para vista previa sin fricción.
3. **Generación del Carrusel #2:**
   - ID: `57536732-f7dc-41ee-9008-b37ee4b9ea2f`
   - URL Producción: `https://studio.tecnogen.ar/app/viewer/57536732-f7dc-41ee-9008-b37ee4b9ea2f`
   - Estado en Google Sheet: **Ya realizado**.
