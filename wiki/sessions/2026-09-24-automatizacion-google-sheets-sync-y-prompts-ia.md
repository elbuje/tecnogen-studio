# 📝 Sesión: Automatización de Google Sheets, Sincronización Asíncrona y Auditoría de Prompts de IA

**Fecha:** 2026-09-24  
**Objetivo:** Eliminar la selección manual de layouts en el Brand Kit para que el Google Sheet y manual de marca dicten la creación, habilitar el botón de Sincronización directa en Dashboard e Integraciones, mostrar el prompt exacto enviado a la IA en el visor de contenidos, y hacer el proceso 100% asíncrono y resiliente en producción.

---

## 🎯 Avances y Decisiones Clave

1. **Eliminación del selector visual manual de layouts:**
   - Se removió el bloque de 10 tarjetas de selección manual de estilos en `BrandKit.tsx`.
   - La estructura, el tema, la persona/sujeto y los pasos se rigen 100% por los guiones del Google Sheet y el manual de marca.

2. **Auditoría de Prompts en el Visor (`ContentViewer.tsx`):**
   - En cada lámina/slide se agregó una caja destacada con estilo terminal (`mono`) que muestra el **Prompt exacto enviado a la IA**.
   - Se agregó un botón de 1 clic para **"Copiar Prompt"** facilitando la auditoría técnica de los copys e imágenes generadas.

3. **Botón y Flujo de Sincronización de Google Sheets:**
   - Se implementó el botón **"Sincronizar Google Sheet"** en el **Dashboard** (`/app/dashboard`) y en **Integraciones** (`/app/integrations`).
   - Se creó el endpoint libre `/api/v1/integrations/sync-sheet` para cualquier usuario autenticado (sin requerir permisos de SuperAdmin).
   - Se implementó resolución universal de marcas y fallback automático al Google Sheet vinculado (`16LTMacG3WsGa4u6Bn8wgrIhLGpm6G_ki_oR1qn75R88`).

4. **Ejecución Asíncrona (`BackgroundTasks`) y Autonomía:**
   - La generación de imágenes y copy con OpenAI ahora corre en segundo plano mediante `BackgroundTasks` de FastAPI.
   - El usuario recibe respuesta inmediata (HTTP 200) y puede cerrar el navegador o apagar la PC mientras el servidor continúa la generación.
   - Al terminar, el servidor actualiza automáticamente el Google Sheet escribiendo el estado `Listo para Revisión`, el `ID_CONTENIDO` y el enlace de preview (`LINK_PREVIEW`).

5. **Resolución de Errores Técnicos en Producción:**
   - Se corrigió la resolución de la ruta del archivo `google-service-account.json`.
   - Se normalizaron las claves del parser (`row_index`, `topic`, `script`, `row_number`).
   - Se corrigió la llamada a `process_content_generation` pasando la fábrica de sesiones `SessionLocal`.
   - Se corrigieron los campos del modelo ORM `Content` y `Slide`.
   - Se configuró el daemon permanente en Ploi (`id: 226698`) para supervisión continua de FastAPI en el puerto 8028.

---

## 📁 Archivos Modificados
- `backend/app/services/google_automation_service.py`
- `backend/app/routers/integrations.py`
- `backend/app/routers/settings.py`
- `backend/app/seed.py`
- `frontend/src/pages/BrandKit.tsx`
- `frontend/src/pages/ContentViewer.tsx`
- `frontend/src/pages/Dashboard.tsx`
- `frontend/src/pages/Integrations.tsx`
- `frontend/src/pages/AISettings.tsx`

---

## 🚀 Estado en Producción
- **Sitio:** [studio.tecnogen.ar](https://studio.tecnogen.ar) (100% online y operativo).
- **Git:** Ramas `dev` y `main` sincronizadas con GitHub.
