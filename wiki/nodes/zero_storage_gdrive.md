---
title: "Estrategia de Almacenamiento Zero-Storage y Google Drive"
description: "Manejo de archivos del cliente en su propio Google Drive sin costo de almacenamiento permanente para el operador."
tags:
  - storage
  - gdrive
  - google-api
  - zero-storage
  - cloudflare-r2
---

# ☁️ Estrategia de Almacenamiento (Zero-Storage)

TecnoGen Studio no retiene archivos generados de forma permanente en los discos del servidor, reduciendo el costo de infraestructura a \$0 en almacenamiento.

---

## 🔄 Ciclo de Vida de los Archivos

1. **Lectura (Input):**
   - El cliente selecciona fotos de equipo o productos desde su Google Drive (`gdrive_input_folder_id`).
   - El worker descarga temporalmente los bytes requeridos en `/tmp/` o memoria RAM.
2. **Generación & Composición:**
   - La imagen resultante es procesada en memoria.
3. **Escritura (Output):**
   - La imagen final compuesta con logo se sube de inmediato a la carpeta de salida del cliente (`gdrive_output_folder_id`).
   - Se almacena el `gdrive_file_id` y URL pública/compartida en la base de datos PostgreSQL.
4. **Purga Inmediata:**
   - Los archivos temporales locales en `/tmp/` son destruidos.

---

## 🛡️ Modo Fallback (Sin Google Drive)

* Si el cliente no ha conectado su cuenta de Google, los assets y piezas se guardan temporalmente en Cloudflare R2 con una política de auto-eliminación (TTL) a los 30 días.
* La interfaz UI muestra un aviso recomendando conectar Google Drive para conservación ilimitada.
