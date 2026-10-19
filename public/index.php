<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoGen Studio v2.1 — Estudio de Carruseles IA (Sheet + Hilo ChatGPT)</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2.1.<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Barra Superior de Verificación de Versión -->
    <div style="background: linear-gradient(90deg, #065f46, #0284c7); color: #fff; font-size: 0.78rem; font-weight: 700; text-align: center; padding: 0.35rem 1rem; letter-spacing: 0.03em; border-bottom: 1px solid rgba(255,255,255,0.15);">
        🟢 VERSIÓN 2.1 ACTIVA — ESTUDIO HÍBRIDO (GOOGLE SHEET + HILO ESTILO CHATGPT · CERO "FOTO MADRE" EN SERVIDOR · FIX EXIF)
    </div>

    <!-- Header Navigation -->
    <header class="navbar">
        <div class="brand">
            <span class="brand-badge" style="background: linear-gradient(135deg, #10b981, #0284c7);">v2.1 HÍBRIDO</span>
            <div>
                <div class="brand-title">TecnoGen Studio <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">[Build 2.1]</span></div>
                <div class="brand-subtitle">Productor de Carruseles en Secuencia para JM Odontología (OpenAI Sunburst + Drive)</div>
            </div>
        </div>
        <nav class="nav-links">
            <button class="nav-btn active" data-tab="tab-generator">🎬 Estudio de Producción</button>
            <button class="nav-btn" data-tab="tab-sheet">📋 Explorador Google Sheet</button>
            <button class="nav-btn" data-tab="tab-templates">📝 Plantillas Base</button>
            <button class="nav-btn" data-tab="tab-config">⚙️ Configuración</button>
            <button class="nav-btn" data-tab="tab-history">🖼️ Galería Producida</button>
        </nav>
    </header>

    <main class="container">

        <!-- ==================== TAB 1: ESTUDIO DE PRODUCCIÓN ==================== -->
        <section id="tab-generator" class="tab-pane active">

            <!-- PASO 1: ORIGEN DEL CONTENIDO (MODO SHEET O MODO LIBRE CHATGPT) -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>1. Origen del Guion y Datos del Carrusel</span>
                    </div>
                    <div class="view-toggle-group">
                        <button id="btn-mode-sheet" class="view-toggle-btn active" type="button">
                            📊 Desde Fila del Google Sheet
                        </button>
                        <button id="btn-mode-free" class="view-toggle-btn" type="button">
                            ✨ Modo Libre / Estilo ChatGPT
                        </button>
                    </div>
                </div>

                <!-- Sub-panel A: Selector de Fila del Sheet -->
                <div id="source-panel-sheet">
                    <div class="row-selector-bar">
                        <div class="form-group" style="width: 130px; margin-bottom: 0;">
                            <label for="input-row-num">N° de Fila:</label>
                            <input type="number" id="input-row-num" placeholder="Ej: 2" min="1" value="2">
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label for="select-sheet-row">Seleccionar del Google Sheet en vivo:</label>
                            <select id="select-sheet-row">
                                <option value="">Cargando filas del Sheet...</option>
                            </select>
                        </div>
                        <button id="btn-load-prompt" class="btn btn-primary" style="height: 42px;">
                            🔍 Cargar Fila y Activos de Drive
                        </button>
                    </div>
                </div>

                <!-- Sub-panel B: Entrada Manual / Libre (Estilo ChatGPT) -->
                <div id="source-panel-free" style="display: none;">
                    <div class="grid-3" style="margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="free-input-tema">Tema del Carrusel:</label>
                            <input type="text" id="free-input-tema" placeholder="Ej: Implantes dentales sin dolor">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="free-input-titulo">Título / Portada:</label>
                            <input type="text" id="free-input-titulo" placeholder="Ej: ¿Duele ponerse un implante? 🦷">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="free-input-dra">Doctora Protagonista:</label>
                            <input type="text" id="free-input-dra" placeholder="Ej: Jovany" value="Jovany">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label for="free-input-guion">Guion del Carrusel (Escribí un slide por renglón numerado `1.`, `2.`, `3.` o pegá tu guion libre):</label>
                        <textarea id="free-input-guion" rows="6" placeholder="1. Portada: &quot;¿Duele ponerse un implante dental? La verdad sin vueltas 🦷&quot;&#10;2. &quot;Con anestesia local moderna, el procedimiento en sí no duele.&quot;&#10;3. &quot;La molestia posterior es leve y se controla con analgésicos comunes.&quot;&#10;4. Cierre: &quot;¿Tenés dudas? Escribinos por WhatsApp y evaluamos tu caso 👇&quot;"></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button id="btn-init-free-mode" class="btn btn-primary">
                            🎬 Preparar Escaleta y Activos con este Guion
                        </button>
                    </div>
                </div>

                <!-- Ficha Resumen Activa -->
                <div id="panel-row-info" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div class="info-pill-container" style="margin-top: 0;">
                            <div class="pill">Origen: <strong id="info-n">-</strong></div>
                            <div class="pill">Tema: <strong id="info-tema">-</strong></div>
                            <div class="pill">Doctora: <strong id="info-dra">-</strong></div>
                            <div class="pill">Cantidad de Slides: <strong id="info-slides" style="color: var(--primary);">-</strong></div>
                            <div class="pill">Título: <strong id="info-titulo">-</strong></div>
                        </div>
                        <button id="btn-toggle-vars" class="btn btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.78rem;">
                            👁️ Ver Guion Original / Variables
                        </button>
                    </div>

                    <div id="panel-vars-detail" style="display: none; margin-top: 0.85rem; padding-top: 0.85rem; border-top: 1px dashed var(--border-color);">
                        <div class="grid-2">
                            <div>
                                <label style="font-size: 0.78rem;">Guion Base Editable (Si lo modificás, podés actualizar la escaleta):</label>
                                <textarea id="raw-guion-editor" rows="6" style="font-size: 0.82rem;"></textarea>
                                <button id="btn-rebuild-from-guion" class="btn btn-secondary" style="margin-top: 0.4rem; font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                                    🔄 Actualizar Escaleta desde este Guion
                                </button>
                            </div>
                            <div>
                                <label style="font-size: 0.78rem;">Variables Detectadas:</label>
                                <div id="vars-list" style="font-family: monospace; font-size: 0.78rem; background: rgba(0,0,0,0.35); padding: 0.75rem; border-radius: 6px; max-height: 155px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: SELECCIÓN DE ACTIVOS EN DRIVE O SUBIDA MANUAL (SIN FOTO MADRE FIJA) -->
            <div id="panel-drive-assets" class="card" style="display: none;">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                    <span>2. Selección de Fotos de la Doctora, Logo y Manual (Drive o Subida desde tu PC)</span>
                    <span class="badge-clean">✓ Sin "Foto Madre" fija en servidor — Rotación dinámica anti-sesgo</span>
                </div>

                <!-- Selector de Carpeta de Fotos + Acciones de Selección -->
                <div class="asset-section-box" style="margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #fff;">
                                👩‍⚕️ Fotos de Referencia de la Doctora (<span id="count-doctor-photos">0</span> disponibles)
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                Hacé clic en <strong>1 o varias fotos</strong> para usarlas como referencia facial. Si elegís varias, el sistema las irá rotando entre los slides para que no salgan todos iguales.
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                            <button id="btn-random-photos" type="button" class="btn btn-secondary" style="padding: 0.35rem 0.7rem; font-size: 0.78rem;">
                                🎲 Elegir 3 Variadas
                            </button>
                            <button id="btn-clear-photos" type="button" class="btn btn-secondary" style="padding: 0.35rem 0.7rem; font-size: 0.78rem;">
                                🚫 Ninguna (Sin Doctora)
                            </button>
                            <label class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem; margin-bottom: 0; cursor: pointer;">
                                📤 Subir Foto desde PC
                                <input type="file" id="upload-doctor-photo" accept="image/*" multiple style="display: none;">
                            </label>
                        </div>
                    </div>

                    <!-- Barra para cambiar de carpeta de Drive de la Doctora en cualquier momento -->
                    <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.75rem; background: rgba(0,0,0,0.25); padding: 0.5rem 0.75rem; border-radius: 8px;">
                        <span style="font-size: 0.78rem; color: var(--text-muted); white-space: nowrap;">📁 Cambiar carpeta Drive:</span>
                        <select id="select-doctor-folder" style="width: auto; min-width: 220px; padding: 0.35rem 0.6rem; font-size: 0.8rem;">
                            <option value="">-- Carpetas de Doctoras del Sheet --</option>
                        </select>
                        <input type="text" id="input-custom-drive-folder" placeholder="O pegá aquí cualquier link de carpeta de Google Drive..." style="flex: 1; min-width: 200px; padding: 0.35rem 0.6rem; font-size: 0.8rem;">
                        <button id="btn-load-custom-folder" type="button" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem;">
                            Abrir Carpeta
                        </button>
                    </div>

                    <!-- Galería Horizontal de Fotos de la Doctora -->
                    <div id="doctor-photos-gallery" class="doctor-photos-scroll">
                        <!-- Miniaturas cargadas dinámicamente -->
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; font-size: 0.78rem;">
                        <span>Fotos activas seleccionadas: <strong id="lbl-selected-photo" style="color: var(--primary);">Ninguna seleccionada aún</strong></span>
                        <span style="color: var(--text-muted);">💡 Las fotos de cámara profesional se auto-rotan (EXIF) en memoria al enviarse.</span>
                    </div>
                </div>

                <!-- Selector de Logo Oficial y Manual de Marca -->
                <div class="grid-2">
                    <!-- Selector Visual de Logo -->
                    <div class="asset-section-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div>
                                <div style="font-size: 0.88rem; font-weight: 700; color: #fff;">🏷️ Logo Oficial a Incluir</div>
                                <div style="font-size: 0.74rem; color: var(--text-muted);">Seleccionado: <strong id="lbl-selected-logo" style="color: #34d399;">-</strong></div>
                            </div>
                            <label class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; margin-bottom: 0; cursor: pointer;">
                                📤 Subir Logo
                                <input type="file" id="upload-logo-file" accept="image/*" style="display: none;">
                            </label>
                        </div>
                        <div id="logos-gallery" class="doctor-photos-scroll" style="padding-bottom: 0.4rem;">
                            <!-- Logos de Drive y locales -->
                        </div>
                        <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.76rem; color: #cbd5e1; margin-top: 0.4rem; cursor: pointer;">
                            <input type="checkbox" id="chk-stamp-logo" style="width: auto;">
                            Estampar además el logo PNG nítido en esquina al finalizar cada imagen
                        </label>
                    </div>

                    <!-- Manual de Marca y Directivas Maestras -->
                    <div class="asset-section-box" style="display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="font-size: 0.88rem; font-weight: 700; color: #fff;">🎨 Manual de Marca & Directivas</div>
                                <button id="btn-toggle-master-prompt" type="button" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                                    ✍️ Editar Prompt Maestro General
                                </button>
                            </div>
                            <div id="box-brand-manual" style="margin-bottom: 0.6rem;"></div>
                        </div>

                        <!-- Editor Colapsable del Prompt Maestro General -->
                        <div id="box-master-prompt-collapse" style="display: none; margin-top: 0.5rem;">
                            <label style="font-size: 0.75rem;">Instrucción General del Director de Arte:</label>
                            <textarea id="prompt-editor" rows="6" style="font-size: 0.78rem;"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: ESCALETA DE SLIDES EN SECUENCIA + HILO DE CHAT + PRODUCCIÓN SUNBURST -->
            <div id="panel-prompt-editor" class="card" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <div class="card-title" style="margin-bottom: 0.2rem;">
                            <span>3. Escaleta Visual Slide por Slide y Producción en Secuencia</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            Cada slide tiene su propio plano cinematográfico, texto y foto asignada para evitar repeticiones. Podés editar cualquier slide o usar el <strong>Director de Arte IA</strong>.
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                        <button id="btn-ai-plan" type="button" class="btn btn-secondary" title="Usa GPT-4o como en ChatGPT para diseñar planos únicos en cada slide">
                            🧠 Re-planificar Secuencia con Director IA
                        </button>
                        <button id="btn-add-slide" type="button" class="btn btn-secondary">
                            ➕ Agregar Slide
                        </button>
                    </div>
                </div>

                <!-- Contenedor de Tarjetas de cada Slide (1..N) -->
                <div id="slide-plan-container" class="slide-plan-list">
                    <!-- Se renderiza dinámicamente una fila/tarjeta por cada slide del carrusel -->
                </div>

                <!-- HILO CONVERSACIONAL ESTILO CHATGPT PARA REFINAR O CORREGIR -->
                <div class="chat-thread-box">
                    <div class="chat-thread-header">
                        <span>💬 Hilo de Ajustes Conversacionales (Estilo ChatGPT)</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Pedí cambios sobre un slide puntual o sobre todo el carrusel</span>
                    </div>
                    <div id="chat-messages-log" class="chat-messages-log">
                        <div class="chat-msg assistant">
                            <strong>Director de Arte IA:</strong> ¡Escaleta lista! Revisá las tarjetas de cada slide arriba. Podés escribirme acá abajo cualquier cambio (ej: <em>"En el slide 2 hacé que sea un primer plano mostrando alineadores transparentes"</em> o <em>"Hacé que el slide 3 no tenga doctora, solo elementos clínicos"</em>) o darle directo a <strong>Producir Carrusel Completo</strong>.
                        </div>
                    </div>
                    <div class="chat-input-bar">
                        <select id="chat-target-slide" style="width: 190px; flex-shrink: 0;">
                            <option value="all">🎯 Todo el carrusel</option>
                            <option value="add_new">➕ Agregar nuevo slide</option>
                        </select>
                        <input type="text" id="chat-user-input" placeholder="Escribí tu indicación como en ChatGPT (ej: 'En el slide 2 cambiá la pose a plano detalle y regeneralo')...">
                        <button id="btn-chat-send" type="button" class="btn btn-primary" style="white-space: nowrap;">
                            ✨ Aplicar Ajuste
                        </button>
                        <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; margin-bottom: 0; cursor: pointer;">
                            <input type="checkbox" id="chk-chat-autogen" checked style="width: auto;">
                            Auto-regenerar imagen
                        </label>
                    </div>
                </div>

                <!-- BARRA DE PRODUCCIÓN PRINCIPAL -->
                <div class="production-action-bar">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <label for="quick-model-select" style="margin-bottom: 0; white-space: nowrap;">Modelo de Imagen OpenAI:</label>
                        <select id="quick-model-select" style="width: 260px; padding: 0.5rem;">
                            <option value="gpt-image-2.5-sunburst">gpt-image-2.5-sunburst</option>
                            <option value="gpt-image-1">gpt-image-1</option>
                            <option value="dall-e-3">dall-e-3</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button id="btn-stop-produce" type="button" class="btn btn-secondary" style="display: none; border-color: var(--danger); color: var(--danger);">
                            ⏹ Detener Secuencia
                        </button>
                        <button id="btn-produce" type="button" class="btn btn-success" style="padding: 0.8rem 2rem; font-size: 1.05rem;">
                            🚀 Producir Carrusel Completo en Secuencia
                        </button>
                    </div>
                </div>
            </div>

            <!-- PASO 4: ESTADO DE PRODUCCIÓN EN VIVO Y PREVIEW INSTAGRAM -->
            <div id="panel-production-status" class="card" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>⚡ Progreso de Producción en Vivo y Vista Previa Instagram (4:5)</span>
                    </div>
                    <span id="production-progress-badge" class="pill">Esperando...</span>
                </div>

                <!-- Barra de progreso -->
                <div class="progress-bar-outer">
                    <div id="production-progress-fill" class="progress-bar-fill" style="width: 0%;"></div>
                </div>

                <div id="console-logs" class="console-box">
                    Esperando inicio de producción...
                </div>

                <div id="slides-gallery" style="margin-top: 1.5rem;">
                    <!-- Se actualiza en vivo a medida que termina cada slide -->
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
                                <th>Doctora Detectada</th>
                                <th>Slides</th>
                                <th style="width: 170px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="sheet-table-body">
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                    Haz clic para cargar las filas...
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
                📂 <strong>Plantillas Modulares Editables:</strong> Aquí puedes ajustar las reglas maestras del Director de Arte (<code>data/prompt_master_template.txt</code>) y la estructura base de cada slide individual (<code>data/slide_prompt_template.txt</code>).
            </div>

            <div id="alert-template" class="alert"></div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>📝 1. Plantilla Maestra del Director de Arte</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">data/prompt_master_template.txt</span>
                </div>
                <div class="chips-container">
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{N}}">{{N}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{TEMA}}">{{TEMA}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{TITULO}}">{{TITULO}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{GUION}}">{{GUION}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{DRA}}">{{DRA}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{SUBTITULOS}}">{{SUBTITULOS}}</span>
                    <span class="chip-tag" data-target="textarea-master-tpl" data-var="{{CANTIDAD_SLIDES}}">{{CANTIDAD_SLIDES}}</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <textarea id="textarea-master-tpl" rows="14"></textarea>
                </div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div class="card-title" style="margin-bottom: 0;">
                        <span>🎨 2. Plantilla Individual por Slide (Enviada a Sunburst por cada Slide)</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">data/slide_prompt_template.txt</span>
                </div>
                <div class="chips-container">
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SLIDE_NUM}}">{{SLIDE_NUM}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{TOTAL_SLIDES}}">{{TOTAL_SLIDES}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SLIDE_ROLE}}">{{SLIDE_ROLE}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SLIDE_TEXT}}">{{SLIDE_TEXT}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{SHOT_TYPE}}">{{SHOT_TYPE}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{DOCTOR_INSTRUCTION}}">{{DOCTOR_INSTRUCTION}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{LOGO_INSTRUCTION}}">{{LOGO_INSTRUCTION}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{DRA}}">{{DRA}}</span>
                    <span class="chip-tag" data-target="textarea-slide-tpl" data-var="{{TEMA}}">{{TEMA}}</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <textarea id="textarea-slide-tpl" rows="10"></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center;">
                <button id="btn-save-templates" class="btn btn-primary" style="padding: 0.85rem 1.75rem; font-size: 1rem;">
                    💾 Guardar Plantillas
                </button>
                <button id="btn-reset-templates" class="btn btn-secondary">
                    ↺ Restaurar Valores Originales
                </button>
            </div>
        </section>

        <!-- ==================== TAB 4: CONFIGURACIÓN ==================== -->
        <section id="tab-config" class="tab-pane">
            <div class="card" style="max-width: 680px; margin: 0 auto;">
                <div class="card-title">
                    <span>⚙️ Configuración de OpenAI API y Google Drive</span>
                </div>

                <div id="alert-config" class="alert"></div>

                <div class="form-group">
                    <label for="input-api-key">OpenAI API Key:</label>
                    <input type="password" id="input-api-key" placeholder="sk-proj-...">
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label for="select-model" style="margin-bottom: 0;">Modelo Generador de Imágenes por Defecto:</label>
                        <button id="btn-load-models" class="btn btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.8rem;">
                            🔄 Consultar Modelos de mi Cuenta OpenAI
                        </button>
                    </div>
                    <select id="select-model">
                        <option value="gpt-image-2.5-sunburst">gpt-image-2.5-sunburst</option>
                        <option value="gpt-image-1">gpt-image-1</option>
                        <option value="dall-e-3">dall-e-3</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="select-director-model">Modelo Director de Arte / Hilo de Chat (Planificación de Secuencia):</label>
                    <select id="select-director-model">
                        <option value="gpt-4o">gpt-4o (Recomendado — Multimodal)</option>
                        <option value="gpt-4o-mini">gpt-4o-mini (Rápido)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="input-sheet-url">URL de Exportación CSV del Google Sheet:</label>
                    <input type="text" id="input-sheet-url" placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv&gid=...">
                </div>

                <div class="form-group">
                    <label for="input-drive-api-key">Google Drive API Key:</label>
                    <input type="password" id="input-drive-api-key" placeholder="AIzaSy...">
                </div>

                <button id="btn-save-config" class="btn btn-primary btn-large">
                    💾 Guardar Configuración
                </button>
            </div>
        </section>

        <!-- ==================== TAB 5: GALERÍA DE HISTORIAL ==================== -->
        <section id="tab-history" class="tab-pane">
            <div class="gallery-header-bar">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0.25rem;">
                        🖼️ Galería de Carruseles Producidos
                    </h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted);">
                        Visualiza los carruseles exactamente como se ven en Instagram (4:5) o descárgalos en ZIP.
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <div class="view-toggle-group">
                        <button id="view-mode-ig" class="view-toggle-btn active" onclick="setGalleryViewMode('ig')">
                            📱 Vista Instagram Feed
                        </button>
                        <button id="view-mode-grid" class="view-toggle-btn" onclick="setGalleryViewMode('grid')">
                            🔲 Vista Cuadrícula
                        </button>
                    </div>
                    <button onclick="loadHistory()" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.45rem 0.85rem;">
                        🔄 Refrescar
                    </button>
                </div>
            </div>

            <div id="history-container"></div>
        </section>

    </main>

    <footer style="text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--border-color);">
        TecnoGen Studio <strong>v2.1 Híbrido</strong> &copy; 2026 — JM Odontología Integral (Sin Foto Madre · Secuencia + Hilo IA).
    </footer>

    <script src="assets/js/app.js?v=2.1.<?= @filemtime(__DIR__ . '/assets/js/app.js') ?: time() ?>"></script>
</body>
</html>
