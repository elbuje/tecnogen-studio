<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoGen Studio — Generador de Carruseles IA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header Navigation -->
    <header class="navbar">
        <div class="brand">
            <span class="brand-badge">PRO</span>
            <div>
                <div class="brand-title">TecnoGen Studio</div>
                <div class="brand-subtitle">Generador de Carruseles de Instagram para JM Odontología</div>
            </div>
        </div>
        <nav class="nav-links">
            <button class="nav-btn active" data-tab="tab-generator">🚀 Generador</button>
            <button class="nav-btn" data-tab="tab-sheet">📋 Explorador Sheet</button>
            <button class="nav-btn" data-tab="tab-templates">📝 Plantillas de Instrucción</button>
            <button class="nav-btn" data-tab="tab-config">⚙️ Configuración</button>
            <button class="nav-btn" data-tab="tab-history">🖼️ Galería</button>
        </nav>
    </header>

    <main class="container">

        <!-- ==================== TAB 1: GENERADOR ==================== -->
        <section id="tab-generator" class="tab-pane active">
            
            <!-- Selector de Fila -->
            <div class="card">
                <div class="card-title">
                    <span>1. Seleccionar Contenido desde Google Sheet</span>
                </div>

                <div class="info-callout">
                    💡 <strong>¿Cómo se arma el prompt?</strong> El sistema toma la <strong>Plantilla Maestra</strong> (almacenada en <code>data/prompt_master_template.txt</code>), extrae las columnas de la fila del Sheet y reemplaza automáticamente variables como <code>{{N}}</code>, <code>{{TEMA}}</code>, <code>{{TITULO}}</code>, <code>{{GUION}}</code>, <code>{{DRA}}</code>, etc. Nada está quemado en el código: puedes ver o editar la instrucción madre en la pestaña <a href="#tab-templates" style="color: var(--primary); text-decoration: underline;" onclick="document.querySelector('[data-tab=tab-templates]').click(); return false;">📝 Plantillas de Instrucción</a>.
                </div>

                <div class="row-selector-bar">
                    <div class="form-group" style="width: 140px; margin-bottom: 0;">
                        <label for="input-row-num">N° de Fila:</label>
                        <input type="number" id="input-row-num" placeholder="Ej: 2" min="1" value="2">
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label for="select-sheet-row">O seleccionar del listado:</label>
                        <select id="select-sheet-row">
                            <option value="">Cargando filas del Sheet...</option>
                        </select>
                    </div>
                    <button id="btn-load-prompt" class="btn btn-primary" style="height: 42px;">
                        🔍 Cargar Datos y Armar Prompt
                    </button>
                </div>
            </div>

            <!-- Ficha Técnica Resumen (Oculta hasta cargar) -->
            <div id="panel-row-info" class="card" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>📋 Ficha del Contenido Cargado</span>
                    </div>
                    <button id="btn-toggle-vars" class="btn btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.8rem;">
                        👁️ Ver Variables Inyectadas
                    </button>
                </div>
                
                <div class="info-pill-container">
                    <div class="pill">Fila N°: <strong id="info-n">-</strong></div>
                    <div class="pill">Tema: <strong id="info-tema">-</strong></div>
                    <div class="pill">Doctora: <strong id="info-dra">-</strong></div>
                    <div class="pill">Slides Detectados: <strong id="info-slides">-</strong></div>
                    <div class="pill">Título: <strong id="info-titulo">-</strong></div>
                </div>

                <!-- Detalle de variables inyectadas (colapsable) -->
                <div id="panel-vars-detail" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                        <strong>Variables reemplazadas en la plantilla:</strong>
                    </div>
                    <div id="vars-list" style="font-family: monospace; font-size: 0.8rem; background: rgba(0,0,0,0.3); padding: 0.75rem; border-radius: 6px; max-height: 160px; overflow-y: auto;">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Editor de Prompt y Envío a Producción (Oculto hasta cargar) -->
            <div id="panel-prompt-editor" class="card" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>✍️ Prompt Ensamblado (Revisa y modifica libremente antes de producir)</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span id="char-count" style="font-size: 0.8rem; color: var(--text-muted);">0 caracteres</span>
                        <button id="btn-copy-prompt" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                            📋 Copiar Prompt
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <textarea id="prompt-editor" rows="18" placeholder="El prompt generado aparecerá aquí..."></textarea>
                </div>

                <!-- Barra de Acción de Producción -->
                <div style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <label for="quick-model-select" style="margin-bottom: 0; white-space: nowrap;">Modelo para producción:</label>
                        <select id="quick-model-select" style="width: 280px; padding: 0.5rem;">
                            <option value="gpt-image-2.5-sunburst">gpt-image-2.5-sunburst</option>
                        </select>
                    </div>
                    <button id="btn-produce" class="btn btn-success" style="padding: 0.75rem 2rem; font-size: 1.05rem;">
                        🚀 Enviar a Producir
                    </button>
                </div>
            </div>

            <!-- Estado de Producción y Resultados -->
            <div id="panel-production-status" class="card" style="display: none;">
                <div class="card-title">
                    <span>⚡ Estado de la Producción y Galería de Slides</span>
                </div>
                <div id="console-logs" class="console-box">
                    Esperando inicio de producción...
                </div>
                <div id="slides-gallery" class="slides-grid">
                    <!-- Se cargan dinámicamente las imágenes generadas -->
                </div>
            </div>

        </section>

        <!-- ==================== TAB 2: EXPLORADOR SHEET ==================== -->
        <section id="tab-sheet" class="tab-pane">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>📊 Filas del Google Sheet en Tiempo Real</span>
                    </div>
                    <button onclick="loadSheetExplorer()" class="btn btn-secondary" style="font-size: 0.8rem;">
                        🔄 Refrescar Sheet
                    </button>
                </div>
                <div style="overflow-x: auto;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">N°</th>
                                <th>Tema</th>
                                <th>Título / Portada</th>
                                <th>Doctora</th>
                                <th>Slides</th>
                                <th style="width: 160px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="sheet-table-body">
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                    Haz clic en la pestaña para cargar las filas...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ==================== TAB 3: PLANTILLAS DE INSTRUCCIÓN ==================== -->
        <section id="tab-templates" class="tab-pane">
            
            <div class="info-callout">
                📂 <strong>Transparencia Total de Instrucciones:</strong> En este sistema no existen instrucciones ocultas ni grabadas dentro de scripts PHP o JavaScript. Las instrucciones residen en archivos de texto editables dentro del directorio <code>data/</code>. Puedes modificarlas, agregar variables dinámicas y guardarlas directamente desde aquí.
            </div>

            <div id="alert-template" class="alert"></div>

            <!-- 1. Plantilla Maestra -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>📝 Plantilla Maestra del Carrusel (Instrucción General)</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">data/prompt_master_template.txt</span>
                </div>
                <div class="chip-help">Haz clic en una etiqueta para insertarla en la posición del cursor:</div>
                <div class="chips-container" id="chips-master">
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{N}}">{{N}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{TEMA}}">{{TEMA}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{TITULO}}">{{TITULO}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{GUION}}">{{GUION}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{DRA}}">{{DRA}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{SUBTITULOS}}">{{SUBTITULOS}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{LINK_FOTOS}}">{{LINK_FOTOS}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{LINK_LOGOS}}">{{LINK_LOGOS}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{LINK_MANUAL}}">{{LINK_MANUAL}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{ESTADO}}">{{ESTADO}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{CANTIDAD_SLIDES}}">{{CANTIDAD_SLIDES}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{SHEET_URL}}">{{SHEET_URL}}</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <textarea id="textarea-master-tpl" rows="16" placeholder="Cargando plantilla maestra..."></textarea>
                </div>
            </div>

            <!-- 2. Plantilla de Slide -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>🎨 Plantilla de Prompt por Slide (Enviado al API de Imágenes)</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">data/slide_prompt_template.txt</span>
                </div>
                <div class="chip-help">Haz clic en una etiqueta para insertarla:</div>
                <div class="chips-container" id="chips-slide">
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SLIDE_NUM}}">{{SLIDE_NUM}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{TOTAL_SLIDES}}">{{TOTAL_SLIDES}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SLIDE_TEXT}}">{{SLIDE_TEXT}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{DRA}}">{{DRA}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{TEMA}}">{{TEMA}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{TITULO}}">{{TITULO}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SUBTITULOS}}">{{SUBTITULOS}}</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <textarea id="textarea-slide-tpl" rows="8" placeholder="Cargando plantilla de slide..."></textarea>
                </div>
            </div>

            <!-- Botones de Acción de Plantillas -->
            <div style="display: flex; gap: 1rem; align-items: center;">
                <button id="btn-save-templates" class="btn btn-primary" style="padding: 0.85rem 1.75rem; font-size: 1rem;">
                    💾 Guardar Plantillas en Archivos
                </button>
                <button id="btn-reset-templates" class="btn btn-secondary">
                    ↺ Restaurar Valores de Fábrica
                </button>
            </div>

        </section>

        <!-- ==================== TAB 4: CONFIGURACIÓN ==================== -->
        <section id="tab-config" class="tab-pane">
            <div class="card" style="max-width: 680px; margin: 0 auto;">
                <div class="card-title">
                    <span>⚙️ Configuración del Sistema y API</span>
                </div>

                <div id="alert-config" class="alert"></div>

                <div class="form-group">
                    <label for="input-api-key">OpenAI API Key:</label>
                    <input type="password" id="input-api-key" placeholder="sk-proj-...">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.3rem;">
                        Clave segura almacenada localmente en el servidor.
                    </small>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label for="select-model" style="margin-bottom: 0;">Modelo de Imágenes por Defecto:</label>
                        <button id="btn-load-models" class="btn btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.8rem;">
                            🔄 Cargar Modelos de mi API
                        </button>
                    </div>
                    <select id="select-model">
                        <option value="gpt-image-2.5-sunburst">gpt-image-2.5-sunburst</option>
                    </select>
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.3rem;">
                        Haz clic en "Cargar Modelos de mi API" para listar en vivo los modelos habilitados en tu cuenta de OpenAI.
                    </small>
                </div>

                <div class="form-group">
                    <label for="input-sheet-url">URL de Exportación CSV del Google Sheet:</label>
                    <input type="text" id="input-sheet-url" placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv&gid=...">
                </div>

                <button id="btn-save-config" class="btn btn-primary btn-large">
                    💾 Guardar Configuración
                </button>
            </div>
        </section>

        <!-- ==================== TAB 5: GALERÍA DE HISTORIAL ==================== -->
        <section id="tab-history" class="tab-pane">
            <div id="history-container">
                <!-- Se cargan dinámicamente los carruseles producidos -->
            </div>
        </section>

    </main>

    <footer style="text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--border-color);">
        TecnoGen Studio &copy; 2026 — JM Odontología Integral.
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
