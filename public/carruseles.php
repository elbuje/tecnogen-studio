<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoGen Studio — Estudio de Carruseles con IA</title>
    <link rel="stylesheet" href="assets/css/carruseles.css?v=1.0.<?= @filemtime(__DIR__ . '/assets/css/carruseles.css') ?: time() ?>">
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

            <!-- Menú de navegación entre aplicaciones independientes -->
            <nav class="studio-mode-nav" aria-label="Modo de estudio">
                <a href="index.php" class="studio-mode-link">
                    <span>🖼️</span> Publicación Única
                </a>
                <a href="carruseles.php" class="studio-mode-link active">
                    <span>🎠</span> Estudio de Carruseles
                </a>
            </nav>

            <button id="btn-new-carousel" type="button" class="btn-new-chat">
                <span>＋</span> Nuevo Carrusel
            </button>
        </div>

        <div class="sidebar-history-section">
            <div class="sidebar-section-header">
                <span class="sidebar-section-label">Carruseles Recientes</span>
                <button id="btn-clear-all-carousels" type="button" class="btn-clear-all" title="Eliminar todos los carruseles y sus archivos del servidor" style="display: none;">
                    🗑️ Borrar todo
                </button>
            </div>
            <div id="carousels-history-list" class="history-list">
                <!-- Se carga dinámicamente -->
            </div>
        </div>

        <div class="sidebar-footer">
            <button id="btn-open-config" type="button" class="btn-sidebar-config">
                ⚙️ Configuración Carruseles
            </button>
        </div>
    </aside>

    <!-- ==================== ÁREA PRINCIPAL ==================== -->
    <div class="main-workspace">

        <!-- Topbar limpia -->
        <header class="workspace-topbar">
            <div class="topbar-left">
                <button id="btn-toggle-sidebar" type="button" class="btn-icon-only" title="Mostrar/Ocultar barra lateral">☰</button>
                <span class="mode-pill-badge">🎠 Estudio de Carruseles</span>
                <span class="topbar-title" id="current-carousel-title">Nuevo Carrusel</span>
            </div>
            <div class="topbar-right">
                <button id="btn-delete-active-carousel" type="button" class="btn-delete-thread" style="display: none;" title="Eliminar este carrusel y todas sus láminas del servidor">
                    🗑️ Eliminar carrusel
                </button>
            </div>
        </header>

        <!-- Hilo de Conversación y Resultados del Carrusel -->
        <main class="chat-viewport" id="chat-viewport">

            <!-- Cabecera de Bienvenida -->
            <div id="welcome-screen" class="welcome-screen">
                <div class="welcome-logo-wrap">
                    <img src="assets/img/logo-tecnogen-studio.jpg" alt="TecnoGen Studio" class="welcome-logo-img">
                </div>
                <h1>¿Qué <span class="brand-highlight">carrusel secuencial</span> vamos a crear hoy?</h1>
                <p>Subí tu <strong>1. Marca / Paleta</strong>, tu <strong>2. Logo</strong> y tus <strong>3. Fotos de Protagonista, Productos o Fondos</strong>, y pegá la base de tu guion o idea abajo.</p>

                <div class="steps-explainer-grid">
                    <div class="step-card">
                        <div class="step-title">📝 Paso 1: Guion Slide por Slide + ADN Visual</div>
                        <div class="step-text">El Director Creativo analiza tu guion, elige el formato ideal (Mito/Verdad, Verdadero/Falso, Paso a Paso, Storytelling, etc.), define los deslizadores y arma la narrativa de cada lámina.</div>
                    </div>
                    <div class="step-card">
                        <div class="step-title">🎠 Paso 2: Creación Secuencial con Coherencia</div>
                        <div class="step-text">El Diseñador IA genera las <strong>N láminas en secuencia (4:5 — 1080×1350)</strong> usando anclaje visual entre slides para mantener el mismo estilo, vestuario, rostro e hilo conductor.</div>
                    </div>
                </div>
            </div>

            <!-- Contenedor del Hilo Activo -->
            <div id="thread-container" class="thread-container" style="display: none;">
                <!-- Se insertan dinámicamente el pedido, el desglose de láminas y el carrusel generado -->
            </div>

        </main>

        <!-- ==================== BARRA INFERIOR DE COMPOSICIÓN ==================== -->
        <section class="composer-wrapper">
            <div class="composer-box">

                <!-- FILA 1: Selección de Redes + Cantidad de Láminas + Auto-crear carrusel -->
                <div class="composer-controls-row">
                    <!-- Redes Sociales -->
                    <div class="control-group">
                        <span class="control-label">Redes:</span>
                        <button type="button" id="btn-net-all" class="chip-select active" title="Seleccionar todas las redes">
                            🌐 Todas
                        </button>
                        <button type="button" class="chip-select net-btn active" data-net="ig">📸 IG</button>
                        <button type="button" class="chip-select net-btn active" data-net="fb">📘 FB</button>
                        <button type="button" class="chip-select net-btn active" data-net="tiktok">🎵 TikTok</button>
                        <button type="button" class="chip-select net-btn active" data-net="linkedin">💼 LinkedIn</button>
                    </div>

                    <div class="controls-right-group">
                        <!-- Selector de Cantidad de Slides -->
                        <div class="control-group">
                            <span class="control-label">Láminas:</span>
                            <select id="select-slide-count" class="slide-count-select" title="Cantidad de láminas del carrusel">
                                <option value="auto" selected>✨ Automático (según guion)</option>
                                <option value="3">3 láminas</option>
                                <option value="4">4 láminas</option>
                                <option value="5">5 láminas</option>
                                <option value="6">6 láminas</option>
                                <option value="7">7 láminas</option>
                                <option value="8">8 láminas</option>
                            </select>
                        </div>

                        <!-- Modo Automático -->
                        <label class="auto-step-toggle" title="Si está activado, al terminar el guion genera todas las láminas en secuencia automáticamente">
                            <input type="checkbox" id="chk-auto-step2" checked>
                            <span>⚡ Auto-crear láminas</span>
                        </label>
                    </div>
                </div>

                <!-- FILA 2: LOS 3 APARTADOS DE RECURSOS -->
                <div class="composer-assets-row three-sections-row">
                    <!-- APARTADO 1: Marca / Paleta de Colores -->
                    <div class="asset-section-box">
                        <div class="asset-section-head">
                            <span class="asset-section-num">1. Marca / Paleta</span>
                            <label class="btn-upload-chip compact-chip" for="input-upload-brand" title="Subir imagen de definición de marca o paleta de colores">
                                <span>🎨 + Subir Marca</span>
                                <input type="file" id="input-upload-brand" accept="image/*" style="display: none;">
                            </label>
                        </div>
                        <div id="active-brand-preview" class="attached-items-row"></div>
                    </div>

                    <!-- APARTADO 2: Logo -->
                    <div class="asset-section-box">
                        <div class="asset-section-head">
                            <span class="asset-section-num">2. Logo</span>
                            <label class="btn-upload-chip compact-chip" for="input-upload-logo" title="Subir logotipo de la marca">
                                <span>🏷️ + Subir Logo</span>
                                <input type="file" id="input-upload-logo" accept="image/*" style="display: none;">
                            </label>
                        </div>
                        <div id="active-logo-preview" class="attached-items-row"></div>
                    </div>

                    <!-- APARTADO 3: Personajes, Productos y Fondos -->
                    <div class="asset-section-box">
                        <div class="asset-section-head">
                            <span class="asset-section-num">3. Personajes, Productos y Fondos</span>
                            <label class="btn-upload-chip compact-chip" for="input-upload-subject" title="Subir fotos de la doctora, personajes, productos o fondos">
                                <span>📸 + Subir Fotos</span>
                                <input type="file" id="input-upload-subject" accept="image/*" multiple style="display: none;">
                            </label>
                        </div>
                        <div id="active-subjects-preview" class="attached-items-row"></div>
                    </div>
                </div>

                <!-- BARRA DE VARIABLES DE FOTOS -->
                <div id="composer-photo-vars-bar" class="photo-vars-bar" style="display: none;">
                    <span class="photo-vars-label">📌 Insertar foto como variable en tu guion:</span>
                    <div id="composer-photo-vars-list" class="photo-vars-list"></div>
                </div>

                <!-- FILA 3: Área amplia para pegar la base del guion o idea + Botón Enviar -->
                <div class="composer-input-row">
                    <textarea id="prompt-input" class="composer-textarea" rows="3" placeholder="Pegá acá el guion o escribí la idea base del carrusel (podés hacer clic en tus fotos para mencionarlas, ej: 'Carrusel con la Dra. de xxxx.jpg desmintiendo mitos sobre blanqueamiento dental')..."></textarea>
                    <button id="btn-send-prompt" type="button" class="btn-send-chat" title="Armar carrusel">
                        <span>Enviar</span>
                        <span class="send-arrow">↑</span>
                    </button>
                </div>

            </div>
        </section>

    </div>

    <!-- ==================== MODAL DE CONFIGURACIÓN DE CARRUSELES ==================== -->
    <div id="modal-config" class="modal-backdrop" style="display: none;">
        <div class="modal-card" style="max-width: 760px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3>⚙️ Configuración y Prompts — Estudio de Carruseles</h3>
                <button type="button" id="btn-close-config" class="btn-close-modal">✕</button>
            </div>

            <div id="alert-config" class="alert" style="display: none;"></div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div class="form-group">
                    <label for="input-api-key">OpenAI API Key:</label>
                    <input type="password" id="input-api-key" placeholder="sk-proj-..." autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="select-text-model">Modelo Paso 1 (Director de Carrusel / Guion):</label>
                    <select id="select-text-model">
                        <option value="gpt-4o">gpt-4o (Multimodal con Visión)</option>
                        <option value="gpt-4o-mini">gpt-4o-mini (Rápido)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="select-model" style="margin-bottom: 0;">Modelo Paso 2 (Generador Secuencial de Láminas):</label>
                    <button type="button" id="btn-load-models" class="btn-mini">🔄 Listar de mi API</button>
                </div>
                <select id="select-model">
                    <option value="gpt-image-2.5-sunburst-2026-09-08">gpt-image-2.5-sunburst-2026-09-08</option>
                    <option value="gpt-image-1">gpt-image-1</option>
                    <option value="dall-e-3">dall-e-3</option>
                </select>
            </div>

            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 1rem 0;">

            <!-- PROMPT PASO 1: DIRECTOR CREATIVO DE CARRUSELES -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="input-prompt-director" style="margin-bottom: 0; color: #0052E0; font-weight: 700;">
                        🧠 Prompt de Sistema — Director Creativo de Carruseles (Paso 1: Guion Slide por Slide y ADN Visual)
                    </label>
                </div>
                <textarea id="input-prompt-director" class="design-textarea mono" rows="5" placeholder="Instrucciones para el Director Creativo de Carruseles..."></textarea>
            </div>

            <!-- PROMPT PASO 2: DISEÑADOR DE SECUENCIAS IA -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="input-prompt-designer" style="margin-bottom: 0; color: #041638; font-weight: 700;">
                        🎨 Prompt de Sistema — Diseñador Experto de Carruseles (Paso 2: Coherencia Secuencial de Láminas)
                    </label>
                </div>
                <textarea id="input-prompt-designer" class="design-textarea mono" rows="5" placeholder="Instrucciones para el Diseñador de Carruseles..."></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #1e293b; font-weight: 600;">
                    <input type="checkbox" id="chk-stamp-logo" style="width: auto;">
                    Estampar también el archivo PNG del logo en esquina al finalizar cada lámina
                </label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-top: 1.25rem;">
                <button type="button" id="btn-restore-prompts" class="btn-mini" title="Restaurar ambos prompts de carruseles a su texto original por defecto">
                    ↺ Restaurar Prompts por Defecto
                </button>
                <button type="button" id="btn-save-config" class="btn-primary-action">
                    💾 Guardar Configuración
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/carruseles.js?v=1.0.<?= @filemtime(__DIR__ . '/assets/js/carruseles.js') ?: time() ?>"></script>
</body>
</html>
