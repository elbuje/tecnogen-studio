---
name: /instalarwiki
description: Instala, audita o repara la LLM Wiki del proyecto actual, crea los flujos de sesión y conecta el nodo en la MetaWiki Global.
---

# Workflow: Instalar / Auto-Curar LLM Wiki

Este workflow asegura que el proyecto actual cuente con la estructura completa de LLM Wiki bajo el estándar de 3 capas.

## Pasos de Ejecución

1. **Asegurar Workflows en `.agent/workflows/`:**
   - Copiar `abrirsesion.md`, `cerrarsesion.md` e `instalarwiki.md` desde `~/.agent/workflows/` hacia `.agent/workflows/` del proyecto.

2. **Auditoría & Creación de `wiki/`:**
   - Si no existe `wiki/`:
     - Analizar framework, rutas, base de datos, puertos (`PORT_REGISTRY.md`), `.env` y git log.
     - Crear `wiki/nodes/`, `wiki/sessions/`, `wiki/guides/`.
     - Crear `wiki/index.md` con frontmatter YAML.
     - Crear `wiki/sources.md` (con enlace a MetaWiki Global `file:///home/mfmujic/.agent/wiki/index.md`).
     - Crear `wiki/log.md`.
     - Crear nodos base en `wiki/nodes/`.
     - Crear/actualizar `status.md`.
   - Si ya existe `wiki/`:
     - Verificar y actualizar enlaces rotos y frontmatters.
     - Actualizar `sources.md` con Hostinger VPS (`72.62.107.109`) y puertos reales.

3. **Conexión en MetaWiki Global (`~/.agent/wiki/`):**
   - Crear o actualizar `~/.agent/wiki/nodes/<nombre_proyecto>.md`.
   - Registrar el proyecto en `~/.agent/wiki/index.md`.

4. **Reporte Ejecutivo:**
   - Presentar al usuario los nodos creados y confirmar que el visualizador 3D (`:5190`) ya tiene el proyecto sincronizado.
