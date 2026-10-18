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
                <div class="card-title">
                    <span>📋 Ficha del Contenido Cargado</span>
                </div>
                <div class="info-pill-container">
                    <div class="pill">N°: <strong id="info-n">-</strong></div>
                    <div class="pill">Tema: <strong id="info-tema">-</strong></div>
                    <div class="pill">Doctora: <strong id="info-dra">-</strong></div>
                    <div class="pill">Título: <strong id="info-titulo">-</strong></div>
                </div>
            </div>

            <!-- Editor de Prompt y Envío a Producción (Oculto hasta cargar) -->
            <div id="panel-prompt-editor" class="card" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>✍️ Editor del Prompt (Revisa y modifica antes de producir)</span>
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

        <!-- ==================== TAB 3: CONFIGURACIÓN ==================== -->
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

        <!-- ==================== TAB 4: GALERÍA DE HISTORIAL ==================== -->
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
