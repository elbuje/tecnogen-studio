---
name: /abrirsesion
description: Inicia la sesión de trabajo cargando la LLM Wiki del proyecto y conectando con la MetaWiki Global del Ecosistema.
---

# Workflow: Abrir Sesión de Programación (LLM Wiki Standard)

Este workflow carga el contexto del proyecto leyendo la LLM Wiki local y conectando con la MetaWiki Global del Ecosistema.

## Pasos de Ejecución

### 1. Evaluación del Estado del Proyecto
El agente verifica la presencia del directorio `wiki/`:

- **Caso A — Proyecto Nuevo (sin `wiki/` ni `masterplan.md`):**
  - El agente **inicializa automáticamente la estructura LLM Wiki limpia**:
    - Crea `wiki/index.md`, `wiki/log.md`, `wiki/sources.md`, `wiki/nodes/`, `wiki/sessions/`.
    - Genera `masterplan.md` y `status.md` base del proyecto.
    - Conecta `wiki/sources.md` con la **MetaWiki Global** (`file:///home/mfmujic/.agent/wiki/index.md`).
  - Reporta que el proyecto ha sido inicializado bajo el nuevo estándar.

- **Caso B — Proyecto Existente no migrado (tiene `masterplan.md` antiguo pero sin `wiki/`):**
  - El agente ejecuta la migración automática `/migrarwiki` manteniendo todo el historial sin perder nada.

- **Caso C — Proyecto con LLM Wiki (Estándar Activo):**
  - Procede directamente a cargar el contexto.

### 2. Carga de Contexto & Conexión Global
1. Lee `wiki/index.md` (Catálogo de Nodos).
2. Lee `wiki/sources.md` para verificar conexiones con la MetaWiki Global (`file:///home/mfmujic/.agent/wiki/index.md`).
3. Lee la última sesión en `wiki/sessions/`.
4. Revisa `git status` y `git log -5`.

### 3. Presentación del Reporte Ejecutivo
El agente presenta al usuario:
1. **Estado del proyecto** (Fase activa y nodos del catálogo).
2. **Conexiones del Ecosistema** (Servicios compartidos/MetaWiki aplicables).
3. **Últimos avances** (de la sesión previa).
4. **Pregunta proactiva:** "¿En qué nodo o tarea del plan nos enfocamos hoy?"
