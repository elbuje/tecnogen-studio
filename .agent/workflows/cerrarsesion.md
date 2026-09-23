---
name: /cerrarsesion
description: Cierra la sesión de trabajo guardando los avances en la LLM Wiki local y registrando en el log.
---

# Workflow: Cerrar Sesión de Programación (LLM Wiki Standard)

Este workflow consolida los avances de la sesión en la LLM Wiki del proyecto.

## Pasos

1. **Crear Nodo de Sesión (`wiki/sessions/YYYY-MM-DD-tema.md`):**
   - Registrar contexto, decisiones tomadas, outputs producidos y pendientes.
2. **Actualizar Nodos Conceptuales Afectados (`wiki/nodes/`):**
   - Si hubo cambios en arquitectura, APIs o modelos, actualizar los nodos correspondientes.
3. **Actualizar Catálogo & Log (`wiki/index.md` y `wiki/log.md`):**
   - Agregar el enlace de la nueva sesión a `wiki/index.md`.
   - Registrar la entrada en `wiki/log.md` (`UPDATE` / `FEAT`).
4. **Actualizar `status.md`**:
   - Marcar tareas completadas y actualizar el backlog.
5. **Git Commit (opcional):**
   - Realizar o proponer el commit de los cambios realizados.
6. **Reportar al usuario:**
   - Confirmar la sincronización limpia de la wiki.
