# Sesión 2026-09-25 — Integración del Manual de Marca JM, Pipeline IA con gpt-image-2.5-sunburst y Rediseño del Visor

## Contexto & Objetivos
- Extracción de identidad oficial desde el Manual de Marca (`Manual de identidad JM.pdf` en Google Drive) para la marca `JM Odontología Integral`.
- Conexión y descarga directa de activos desde Google Drive: logo transparente en alta resolución (`JM_blanco_negro.png`) y fotografías de personajes en formato iPhone `.HIF` / `.HEIC` (`Jessica.HIF`).
- Configuración dinámica del modelo de IA desde la base de datos (`ai_settings`), permitiendo usar `gpt-image-2.5-sunburst`, `dall-e-3`, etc., según la preferencia del usuario en `/app/ai-settings`.
- Implementación de composición determinista con numeración obligatoria de láminas (`1/8` .. `8/8`), badges en español, encuadre de sujeto con acento `#7DD3FC` y eliminación de texto/carteles en inglés.
- Renovación total del visor de contenidos (`ContentViewer.tsx`) y adición de botones directos para abrir carpetas en `Integrations.tsx`.

---

## Decisiones Técnicas & Implementación

1. **Extracción de Identidad de Marca:**
   - Marca oficial: **`JM Odontología Integral`**
   - Paleta de color:
     - Relax Blue (Primario): `#16345F`
     - Sky Pulse (Acento / Badges): `#7DD3FC`
     - Core Black (Fondo / Base): `#1D1D1B`
     - Pure White / Grey Calm: `#FFFFFF` / `#F2F2F2`

2. **Decodificación de Formatos de Fotografía Móvil (`pillow-heif`):**
   - Integración y registro de `pillow-heif` para abrir y procesar archivos iPhone `.HIF` / `.HEIC` de las carpetas de personajes en Google Drive sin requerir conversión manual previa por parte del usuario.

3. **Carga Dinámica de Modelos desde Base de Datos (`ai_settings`):**
   - El router de contenidos (`contents.py`) y el servicio de imágenes (`ai_image_service.py`) leen dinámicamente el modelo activo (`model_name`) y la API Key (`api_key_override`) desde la tabla `ai_settings` configurada por el usuario en `/app/ai-settings` (`gpt-image-2.5-sunburst`).

4. **Motor de Composición Determinista (`composer_service.py`):**
   - Formato 1080x1350 (Instagram Portrait HD).
   - Pastilla de numeración en esquina superior derecha (`1/8`, `2/8`, etc.).
   - Logo oficial en esquina superior izquierda.
   - Badges temáticos en español.
   - Encuadre de tarjeta para la doctora en láminas clave.
   - Pie de lámina interactivo `Deslizá para leer »`.

5. **Interfáz de Usuario (Frontend):**
   - **`Integrations.tsx`:** Botones "Abrir Carpeta" y "Abrir Hoja" con enlaces externos directos en todas las carpetas de Drive y el Google Sheet.
   - **`ContentViewer.tsx`:** Visor centrado estilo Figma/Canva, tira inferior de diapositivas numeradas, inspector lateral con guion editable, copia de prompts de IA en 1 clic y descarga de PNGs en lote.

---

## Verificación & Despliegue
- Pruebas de generación ejecutadas con éxito con el modelo `gpt-image-2.5-sunburst` y composición determinista de marca.
- Código commiteado en ramas `dev` y `main` (`f9b9f09`, `7238610`, `2dc6f19`, `e9fd5b3`).
- Desplegado y verificado en el servidor de producción Ploi `errante` (`studio.tecnogen.ar`) con daemon FastAPI reiniciado (`id: 226698`).
