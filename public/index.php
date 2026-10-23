<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoGen Studio — Plataforma de Contenido con IA</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.2.<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="chatgpt-layout">

    <!-- ==================== SIDEBAR IZQUIERDA ==================== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <div class="brand-header">
                <img src="assets/img/logo-tecnogen-studio.jpg" alt="TecnoGen Studio — Plataforma de contenido con IA" class="brand-logo-img">
            </div>

            <button id="btn-new-post" type="button" class="btn-new-chat">
                <span>＋</span> Nueva Publicación
            </button>
        </div>

        <div class="sidebar-history-section">
            <div class="sidebar-section-label">Publicaciones Recientes</div>
            <div id="posts-history-list" class="history-list">
                <!-- Se carga dinámicamente -->
            </div>
        </div>

        <div class="sidebar-footer">
            <button id="btn-open-config" type="button" class="btn-sidebar-config">
                ⚙️ Configuración
            </button>
        </div>
    </aside>

    <!-- ==================== ÁREA PRINCIPAL ==================== -->
    <div class="main-workspace">

        <!-- Topbar limpia -->
        <header class="workspace-topbar">
            <div class="topbar-left">
                <button id="btn-toggle-sidebar" type="button" class="btn-icon-only" title="Mostrar/Ocultar barra lateral">☰</button>
                <span class="topbar-title" id="current-post-title">Nueva Publicación</span>
            </div>
        </header>

        <!-- Hilo de Conversación y Resultados -->
        <main class="chat-viewport" id="chat-viewport">

            <!-- Pantalla de Bienvenida (Visible al iniciar un nuevo post) -->
            <div id="welcome-screen" class="welcome-screen">
                <div class="welcome-logo-wrap">
                    <img src="assets/img/logo-tecnogen-studio.jpg" alt="TecnoGen Studio" class="welcome-logo-img">
                </div>
                <h1>¿Qué <span class="brand-highlight">post o historia</span> vamos a crear hoy?</h1>
                <p>Subí tu <strong>Logo</strong>, agregá si querés un <strong>Personaje o Producto</strong>, elegí las redes y contame tu idea abajo.</p>

                <div class="steps-explainer-grid">
                    <div class="step-card">
                        <div class="step-title">📝 Textos + Narrativa de la Imagen</div>
                        <div class="step-text">Redactamos los textos listos para publicar en cada red social que elijas y armamos el guion narrativo de lo que va a mostrar la imagen.</div>
                    </div>
                    <div class="step-card">
                        <div class="step-title">🎨 Creación de la Pieza Gráfica</div>
                        <div class="step-text">Con esa narrativa visual, tu logo y tu personaje o producto, generamos la pieza gráfica final lista para descargar como Post o Historia.</div>
                    </div>
                </div>
            </div>

            <!-- Contenedor del Hilo Activo -->
            <div id="thread-container" class="thread-container" style="display: none;">
                <!-- Se insertan dinámicamente el pedido, los textos y la imagen generada -->
            </div>

        </main>

        <!-- ==================== BARRA INFERIOR DE COMPOSICIÓN ==================== -->
        <section class="composer-wrapper">
            <div class="composer-box">

                <!-- FILA 1: Selección de Redes + Post o Historia + Modo de Ejecución -->
                <div class="composer-controls-row">
                    <!-- Redes Sociales -->
                    <div class="control-group">
                        <span class="control-label">Redes:</span>
                        <button type="button" id="btn-net-all" class="chip-select active" title="Seleccionar todas las redes">
                            🌐 Todas
                        </button>
                        <button type="button" class="chip-select net-btn active" data-net="ig">📸 Instagram</button>
                        <button type="button" class="chip-select net-btn active" data-net="fb">📘 Facebook</button>
                        <button type="button" class="chip-select net-btn active" data-net="tiktok">🎵 TikTok</button>
                        <button type="button" class="chip-select net-btn active" data-net="linkedin">💼 LinkedIn</button>
                    </div>

                    <div class="controls-right-group">
                        <!-- Tipo de Publicación: Post o Historia -->
                        <div class="control-group">
                            <span class="control-label">Ubicación:</span>
                            <div class="segmented-switch">
                                <button type="button" id="btn-place-post" class="seg-btn active" data-placement="post">
                                    🖼️ Post
                                </button>
                                <button type="button" id="btn-place-story" class="seg-btn" data-placement="story">
                                    📱 Historia
                                </button>
                            </div>
                        </div>

                        <!-- Modo Automático -->
                        <label class="auto-step-toggle" title="Si está activado, genera los textos y la imagen de forma continua">
                            <input type="checkbox" id="chk-auto-step2" checked>
                            <span>⚡ Crear imagen automáticamente</span>
                        </label>
                    </div>
                </div>

                <!-- FILA 2: Subida de Logo y Personaje/Producto (Con miniaturas activas) -->
                <div class="composer-assets-row">
                    <!-- Subir Logo -->
                    <div class="asset-upload-slot">
                        <label class="btn-upload-chip" for="input-upload-logo">
                            <span>🏷️ + Subir Logo</span>
                            <input type="file" id="input-upload-logo" accept="image/*" style="display: none;">
                        </label>
                        <div id="active-logo-preview" class="attached-items-row"></div>
                    </div>

                    <div class="divider-vertical"></div>

                    <!-- Subir Personaje o Producto -->
                    <div class="asset-upload-slot" style="flex: 1;">
                        <label class="btn-upload-chip" for="input-upload-subject">
                            <span>👤/📦 + Subir Personaje o Producto</span>
                            <input type="file" id="input-upload-subject" accept="image/*" multiple style="display: none;">
                        </label>
                        <div id="active-subjects-preview" class="attached-items-row"></div>
                    </div>
                </div>

                <!-- FILA 3: Textarea del Prompt + Botón Enviar -->
                <div class="composer-input-row">
                    <textarea id="prompt-input" rows="2" placeholder="Escribí la idea de lo que querés reflejar en el posteo (o pedí ajustes sobre el diseño actual)..."></textarea>
                    <button id="btn-send-prompt" type="button" class="btn-send-chat" title="Enviar idea">
                        <span>Enviar</span>
                        <span class="send-arrow">↑</span>
                    </button>
                </div>

            </div>
        </section>

    </div>

    <!-- ==================== MODAL DE CONFIGURACIÓN API Y PROMPTS ==================== -->
    <div id="modal-config" class="modal-backdrop" style="display: none;">
        <div class="modal-card" style="max-width: 740px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3>⚙️ Configuración, Modelos y Prompts de Sistema</h3>
                <button type="button" id="btn-close-config" class="btn-close-modal">✕</button>
            </div>

            <div id="alert-config" class="alert" style="display: none;"></div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div class="form-group">
                    <label for="input-api-key">OpenAI API Key:</label>
                    <input type="password" id="input-api-key" placeholder="sk-proj-...">
                </div>

                <div class="form-group">
                    <label for="select-text-model">Modelo Paso 1 (Director Creativo / Textos):</label>
                    <select id="select-text-model">
                        <option value="gpt-4o">gpt-4o (Multimodal con Visión)</option>
                        <option value="gpt-4o-mini">gpt-4o-mini (Rápido)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="select-model" style="margin-bottom: 0;">Modelo Paso 2 (Generador de Imagen):</label>
                    <button type="button" id="btn-load-models" class="btn-mini">🔄 Listar de mi API</button>
                </div>
                <select id="select-model">
                    <option value="gpt-image-2.5-sunburst-2026-09-08">gpt-image-2.5-sunburst-2026-09-08</option>
                    <option value="gpt-image-1">gpt-image-1</option>
                    <option value="dall-e-3">dall-e-3</option>
                </select>
            </div>

            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 1rem 0;">

            <!-- PROMPT PASO 1: DIRECTOR CREATIVO -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="input-prompt-director" style="margin-bottom: 0; color: #0052E0; font-weight: 700;">
                        🧠 Prompt de Sistema — Director Creativo (Paso 1: Copies y Concepto Narrativo)
                    </label>
                </div>
                <textarea id="input-prompt-director" class="design-textarea mono" rows="5" placeholder="Instrucciones para el Director Creativo..."></textarea>
            </div>

            <!-- PROMPT PASO 2: DISEÑADOR EXPERTO PARA IA -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="input-prompt-designer" style="margin-bottom: 0; color: #041638; font-weight: 700;">
                        🎨 Prompt de Sistema — Diseñador Experto para IA (Paso 2: Creación de la Imagen)
                    </label>
                </div>
                <textarea id="input-prompt-designer" class="design-textarea mono" rows="5" placeholder="Instrucciones para el Diseñador Experto para IA..."></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #1e293b; font-weight: 600;">
                    <input type="checkbox" id="chk-stamp-logo" style="width: auto;">
                    Estampar también el archivo PNG del logo en esquina al finalizar la imagen
                </label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-top: 1.25rem;">
                <button type="button" id="btn-restore-prompts" class="btn-mini" title="Restaurar ambos prompts a su texto original por defecto">
                    ↺ Restaurar Prompts por Defecto
                </button>
                <button type="button" id="btn-save-config" class="btn-primary-action">
                    💾 Guardar Configuración
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=3.2.<?= @filemtime(__DIR__ . '/assets/js/app.js') ?: time() ?>"></script>
</body>
</html>
