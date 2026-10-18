// TecnoGen Studio — App Frontend Logic
document.addEventListener('DOMContentLoaded', () => {
    // State
    let currentRowData = null;
    let currentRowVars = null;
    let configData = {};
    let sheetRowsData = [];
    let currentGalleryItems = [];
    let galleryViewMode = 'ig'; // 'ig' | 'grid'

    // Elements
    const navBtns = document.querySelectorAll('.nav-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    // Switch tabs
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-tab');
            navBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(targetTab).classList.add('active');

            if (targetTab === 'tab-sheet') loadSheetExplorer();
            if (targetTab === 'tab-history') loadHistory();
            if (targetTab === 'tab-templates') loadTemplates();
        });
    });

    // --------------------------------------------------------------------------
    // 1. CARGAR CONFIGURACIÓN
    // --------------------------------------------------------------------------
    async function loadConfig() {
        try {
            const res = await fetch('api.php?action=get_config');
            const data = await res.json();
            if (data.status === 'ok') {
                configData = data;
                document.getElementById('input-api-key').value = data.openai_api_key || '';
                document.getElementById('input-sheet-url').value = data.sheet_url || '';
                
                // Populate model selector with current selected
                const modelSelect = document.getElementById('select-model');
                const quickSelect = document.getElementById('quick-model-select');
                
                if (data.selected_model) {
                    modelSelect.innerHTML = `<option value="${data.selected_model}">${data.selected_model} (Guardado)</option>`;
                    quickSelect.innerHTML = `<option value="${data.selected_model}">${data.selected_model}</option>`;
                }
            }
        } catch (e) {
            console.error('Error cargando config:', e);
        }
    }

    // GUARDAR CONFIGURACIÓN
    document.getElementById('btn-save-config').addEventListener('click', async () => {
        const apiKey = document.getElementById('input-api-key').value.trim();
        const model = document.getElementById('select-model').value.trim();
        const sheetUrl = document.getElementById('input-sheet-url').value.trim();
        const alertBox = document.getElementById('alert-config');

        try {
            const res = await fetch('api.php?action=save_config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    openai_api_key: apiKey,
                    selected_model: model,
                    sheet_url: sheetUrl
                })
            });
            const data = await res.json();
            alertBox.style.display = 'block';
            if (data.status === 'ok') {
                alertBox.className = 'alert alert-success';
                alertBox.textContent = '✓ Configuración guardada correctamente.';
                loadConfig();
            } else {
                alertBox.className = 'alert alert-error';
                alertBox.textContent = 'Error: ' + data.message;
            }
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        } catch (e) {
            alert('Error al guardar configuración: ' + e);
        }
    });

    // PROBAR CONEXIÓN Y CARGAR MODELOS DE OPENAI
    document.getElementById('btn-load-models').addEventListener('click', async () => {
        const btn = document.getElementById('btn-load-models');
        btn.disabled = true;
        btn.textContent = 'Consultando OpenAI...';
        const modelSelect = document.getElementById('select-model');
        const quickSelect = document.getElementById('quick-model-select');
        const alertBox = document.getElementById('alert-config');

        try {
            const res = await fetch('api.php?action=get_models');
            const data = await res.json();
            if (data.status === 'ok') {
                modelSelect.innerHTML = '';
                quickSelect.innerHTML = '';

                // Agrupamos: Modelos de imagen primero
                if (data.image_models && data.image_models.length > 0) {
                    const groupImg = document.createElement('optgroup');
                    groupImg.label = '🎨 Modelos de Imagen (Recomendados)';
                    data.image_models.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m;
                        opt.textContent = m;
                        if (m === data.current_selected) opt.selected = true;
                        groupImg.appendChild(opt);

                        const qOpt = document.createElement('option');
                        qOpt.value = m;
                        qOpt.textContent = m;
                        if (m === data.current_selected) qOpt.selected = true;
                        quickSelect.appendChild(qOpt);
                    });
                    modelSelect.appendChild(groupImg);
                }

                // Todos los modelos
                const groupAll = document.createElement('optgroup');
                groupAll.label = '🌐 Todos los Modelos Disponibles';
                data.all_models.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    groupAll.appendChild(opt);
                });
                modelSelect.appendChild(groupAll);

                alertBox.style.display = 'block';
                alertBox.className = 'alert alert-success';
                alertBox.textContent = `✓ Conexión exitosa. Se detectaron ${data.all_models.length} modelos en tu cuenta.`;
            } else {
                alertBox.style.display = 'block';
                alertBox.className = 'alert alert-error';
                alertBox.textContent = 'Error: ' + data.message;
            }
        } catch (e) {
            alert('Error consultando modelos: ' + e);
        } finally {
            btn.disabled = false;
            btn.textContent = '🔄 Cargar Modelos de mi API';
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        }
    });

    // --------------------------------------------------------------------------
    // 2. GESTIÓN DE PLANTILLAS DE INSTRUCCIÓN
    // --------------------------------------------------------------------------
    async function loadTemplates() {
        try {
            const res = await fetch('api.php?action=get_templates');
            const data = await res.json();
            if (data.status === 'ok') {
                document.getElementById('textarea-master-tpl').value = data.master_template || '';
                document.getElementById('textarea-slide-tpl').value = data.slide_template || '';
            }
        } catch (e) {
            console.error('Error cargando plantillas:', e);
        }
    }

    // Insertar variables al hacer clic en un chip
    document.querySelectorAll('.chip-tag').forEach(chip => {
        chip.addEventListener('click', () => {
            const targetId = chip.getAttribute('data-target');
            const variableTag = chip.getAttribute('data-var');
            const textarea = document.getElementById(targetId);
            if (!textarea) return;

            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const text = textarea.value;

            textarea.value = text.substring(0, startPos) + variableTag + text.substring(endPos, text.length);
            textarea.focus();
            textarea.selectionStart = startPos + variableTag.length;
            textarea.selectionEnd = startPos + variableTag.length;
        });
    });

    // Guardar plantillas
    document.getElementById('btn-save-templates').addEventListener('click', async () => {
        const masterTpl = document.getElementById('textarea-master-tpl').value;
        const slideTpl = document.getElementById('textarea-slide-tpl').value;
        const alertBox = document.getElementById('alert-template');

        try {
            const res = await fetch('api.php?action=save_templates', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    master_template: masterTpl,
                    slide_template: slideTpl
                })
            });
            const data = await res.json();
            alertBox.style.display = 'block';
            if (data.status === 'ok') {
                alertBox.className = 'alert alert-success';
                alertBox.textContent = '✓ ' + data.message;
            } else {
                alertBox.className = 'alert alert-error';
                alertBox.textContent = 'Error: ' + data.message;
            }
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        } catch (e) {
            alert('Error al guardar plantillas: ' + e);
        }
    });

    // Restaurar plantillas por defecto
    document.getElementById('btn-reset-templates').addEventListener('click', async () => {
        if (!confirm('¿Estás seguro de restaurar las plantillas a sus valores originales de fábrica? Se sobrescribirán los archivos.')) {
            return;
        }
        const alertBox = document.getElementById('alert-template');
        try {
            const res = await fetch('api.php?action=reset_templates', { method: 'POST' });
            const data = await res.json();
            alertBox.style.display = 'block';
            if (data.status === 'ok') {
                document.getElementById('textarea-master-tpl').value = data.master_template;
                document.getElementById('textarea-slide-tpl').value = data.slide_template;
                alertBox.className = 'alert alert-success';
                alertBox.textContent = '✓ ' + data.message;
            } else {
                alertBox.className = 'alert alert-error';
                alertBox.textContent = 'Error: ' + data.message;
            }
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        } catch (e) {
            alert('Error al restaurar plantillas: ' + e);
        }
    });

    // --------------------------------------------------------------------------
    // 3. CARGAR FILAS DEL GOOGLE SHEET EN SELECTOR
    // --------------------------------------------------------------------------
    async function loadSheetRowsToSelect() {
        try {
            const res = await fetch('api.php?action=get_sheet_rows');
            const data = await res.json();
            if (data.status === 'ok') {
                sheetRowsData = data.rows;
                const selectRow = document.getElementById('select-sheet-row');
                selectRow.innerHTML = '<option value="">-- Seleccionar fila del Sheet --</option>';
                data.rows.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.n;
                    opt.textContent = `Fila N° ${r.n} — ${r.tema || 'Sin tema'} (${r.slides_count} slides)`;
                    selectRow.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Error cargando filas:', e);
        }
    }

    // Sincronizar input numérico con dropdown
    document.getElementById('select-sheet-row').addEventListener('change', (e) => {
        if (e.target.value) {
            document.getElementById('input-row-num').value = e.target.value;
        }
    });

    // --------------------------------------------------------------------------
    // 4. BOTÓN: CARGAR DATOS Y ARMAR PROMPT
    // --------------------------------------------------------------------------
    document.getElementById('btn-load-prompt').addEventListener('click', async () => {
        const rowNum = document.getElementById('input-row-num').value.trim();
        if (!rowNum) {
            alert('Por favor ingresa un número de fila (ej: 1, 2, 4...)');
            return;
        }

        const btn = document.getElementById('btn-load-prompt');
        btn.disabled = true;
        btn.textContent = 'Consultando Sheet...';

        try {
            const res = await fetch(`api.php?action=get_row_prompt&n=${encodeURIComponent(rowNum)}`);
            const data = await res.json();

            if (data.status === 'ok') {
                currentRowData = data.row;
                currentRowVars = data.vars;
                
                // Mostrar Ficha Técnica
                document.getElementById('info-n').textContent = data.row['N°'] || rowNum;
                document.getElementById('info-tema').textContent = data.row['TEMA'] || '-';
                document.getElementById('info-titulo').textContent = data.row['TÍTULO / PORTADA'] || '-';
                document.getElementById('info-dra').textContent = data.vars['DRA'] || 'Jovany';
                document.getElementById('info-slides').textContent = `${data.vars['CANTIDAD_SLIDES'] || 1} slides`;
                
                // Mostrar variables reemplazadas
                const varsList = document.getElementById('vars-list');
                varsList.innerHTML = '';
                const mainVars = ['N', 'TEMA', 'TITULO', 'DRA', 'SUBTITULOS', 'CANTIDAD_SLIDES', 'LINK_FOTOS', 'LINK_LOGOS', 'LINK_MANUAL'];
                mainVars.forEach(v => {
                    const rowDiv = document.createElement('div');
                    rowDiv.style.marginBottom = '3px';
                    const rawVal = (data.vars && data.vars[v] !== undefined && data.vars[v] !== null) ? data.vars[v] : '';
                    const val = (rawVal !== '') ? String(rawVal) : '(vacío)';
                    rowDiv.innerHTML = `<span style="color: var(--primary);">{{${v}}}:</span> <span style="color: #e2e8f0;">${escapeHtml(val.substring(0, 120))}${val.length > 120 ? '...' : ''}</span>`;
                    varsList.appendChild(rowDiv);
                });

                document.getElementById('panel-row-info').style.display = 'block';

                // Mostrar y cargar el Prompt en el Editor
                const textarea = document.getElementById('prompt-editor');
                textarea.value = data.prompt;
                document.getElementById('char-count').textContent = `${data.prompt.length} caracteres`;
                document.getElementById('panel-prompt-editor').style.display = 'block';
                
                // Scroll suave al editor
                document.getElementById('panel-prompt-editor').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            alert('Error al obtener datos: ' + e);
        } finally {
            btn.disabled = false;
            btn.textContent = '🔍 Cargar Datos y Armar Prompt';
        }
    });

    // Toggle detalle de variables
    document.getElementById('btn-toggle-vars').addEventListener('click', () => {
        const p = document.getElementById('panel-vars-detail');
        p.style.display = (p.style.display === 'none') ? 'block' : 'none';
    });

    // Contador de caracteres del editor
    document.getElementById('prompt-editor').addEventListener('input', (e) => {
        document.getElementById('char-count').textContent = `${e.target.value.length} caracteres`;
    });

    // Botón Copiar Prompt
    document.getElementById('btn-copy-prompt').addEventListener('click', () => {
        const text = document.getElementById('prompt-editor').value;
        navigator.clipboard.writeText(text);
        const btn = document.getElementById('btn-copy-prompt');
        const originalText = btn.textContent;
        btn.textContent = '✓ ¡Copiado!';
        setTimeout(() => { btn.textContent = originalText; }, 2000);
    });

    // --------------------------------------------------------------------------
    // 5. BOTÓN: ENVIAR A PRODUCIR
    // --------------------------------------------------------------------------
    document.getElementById('btn-produce').addEventListener('click', async () => {
        if (!currentRowData) {
            alert('Primero carga una fila del Sheet.');
            return;
        }

        const editedPrompt = document.getElementById('prompt-editor').value.trim();
        const selectedModel = document.getElementById('quick-model-select').value;
        const rowNum = document.getElementById('input-row-num').value.trim();

        const btn = document.getElementById('btn-produce');
        btn.disabled = true;
        btn.textContent = '⏳ Produciendo carrusel en OpenAI...';

        const statusPanel = document.getElementById('panel-production-status');
        const consoleBox = document.getElementById('console-logs');
        const galleryContainer = document.getElementById('slides-gallery');
        
        statusPanel.style.display = 'block';
        consoleBox.textContent = `[${new Date().toLocaleTimeString()}] Iniciando llamado con modelo: ${selectedModel}...\n`;
        galleryContainer.innerHTML = '';
        statusPanel.scrollIntoView({ behavior: 'smooth' });

        try {
            const res = await fetch('api.php?action=produce', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    row_num: rowNum,
                    model: selectedModel,
                    edited_prompt: editedPrompt,
                    row_data: currentRowData
                })
            });
            const data = await res.json();

            if (data.status === 'ok') {
                consoleBox.textContent += `[${new Date().toLocaleTimeString()}] ¡Producción finalizada exitosamente!\n`;
                consoleBox.textContent += `Slides generados: ${data.generated_count} de ${data.total_slides}\n`;

                if (data.images && data.images.length > 0) {
                    // Render Instagram Live Carousel Preview for the newly generated post
                    const previewItem = {
                        folder: data.folder,
                        slides: data.images,
                        total_slides: data.images.length,
                        row_data: currentRowData,
                        created_at: new Date().toLocaleTimeString()
                    };

                    galleryContainer.innerHTML = `
                        <div style="width: 100%; margin-bottom: 2rem;">
                            <div style="text-align: center; margin-bottom: 1rem; color: var(--primary); font-weight: 700; font-size: 1.05rem;">
                                📱 Vista Previa en Feed de Instagram
                            </div>
                            ${renderInstagramCard(previewItem, 'prod_preview')}
                        </div>
                        <div style="width: 100%;">
                            <div style="margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">
                                🔲 Descarga de Slides Individuales (${data.images.length} imágenes)
                            </div>
                            <div class="slides-grid">
                                ${data.images.map((imgUrl, i) => `
                                    <div class="slide-card">
                                        <div class="slide-img-wrapper">
                                            <img src="${imgUrl}" alt="Slide ${i+1}">
                                        </div>
                                        <div class="slide-info">
                                            <span class="slide-badge">Slide ${i+1}</span>
                                            <a href="${imgUrl}" download class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">⬇ Descargar</a>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;

                    // Attach gesture listeners to the preview card
                    setTimeout(() => {
                        const vp = document.getElementById('viewport-prod_preview');
                        if (vp) attachSwipeListeners(vp, 'prod_preview', previewItem.slides);
                    }, 50);
                }
            } else {
                consoleBox.textContent += `[${new Date().toLocaleTimeString()}] ❌ ERROR: ${data.message}\n`;
                alert('Error al producir: ' + data.message);
            }
        } catch (e) {
            consoleBox.textContent += `[${new Date().toLocaleTimeString()}] Error de red: ${e}\n`;
            alert('Error en la llamada: ' + e);
        } finally {
            btn.disabled = false;
            btn.textContent = '🚀 Enviar a Producir';
        }
    });

    // --------------------------------------------------------------------------
    // 6. EXPLORADOR DEL GOOGLE SHEET
    // --------------------------------------------------------------------------
    window.loadSheetExplorer = async function() {
        const tbody = document.getElementById('sheet-table-body');
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Cargando filas del Sheet en tiempo real...</td></tr>`;

        try {
            const res = await fetch('api.php?action=get_sheet_rows');
            const data = await res.json();
            if (data.status === 'ok' && data.rows) {
                tbody.innerHTML = '';
                data.rows.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${r.n}</strong></td>
                        <td>${r.tema || '<span style="color:var(--text-muted)">-</span>'}</td>
                        <td>${r.titulo || '<span style="color:var(--text-muted)">-</span>'}</td>
                        <td>${r.dra || 'Jovany'}</td>
                        <td><span class="pill" style="font-size: 0.75rem;">${r.slides_count} slides</span></td>
                        <td>
                            <button class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;" onclick="selectRowFromTable('${r.n}')">
                                🚀 Cargar Fila
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--danger); padding: 2rem;">Error: ${data.message}</td></tr>`;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--danger); padding: 2rem;">Error cargando filas: ${e}</td></tr>`;
        }
    };

    window.selectRowFromTable = function(rowNum) {
        document.getElementById('input-row-num').value = rowNum;
        document.querySelector('[data-tab="tab-generator"]').click();
        document.getElementById('btn-load-prompt').click();
    };

    // --------------------------------------------------------------------------
    // 7. GALERÍA DE HISTORIAL — INSTAGRAM CAROUSEL & GRID
    // --------------------------------------------------------------------------
    async function loadHistory() {
        const container = document.getElementById('history-container');
        container.innerHTML = '<div style="color: var(--text-muted); text-align: center; padding: 3rem;">Cargando producciones anteriores...</div>';

        try {
            const res = await fetch('api.php?action=get_history');
            const data = await res.json();
            if (data.status === 'ok') {
                currentGalleryItems = data.history || [];
                renderGallery();
            } else {
                container.innerHTML = `<div style="color: var(--danger); text-align: center; padding: 3rem;">Error: ${data.message}</div>`;
            }
        } catch (e) {
            container.innerHTML = `<div style="color: var(--danger); text-align: center; padding: 3rem;">Error cargando historial: ${e}</div>`;
        }
    }

    window.setGalleryViewMode = function(mode) {
        galleryViewMode = mode;
        const btnIg = document.getElementById('view-mode-ig');
        const btnGrid = document.getElementById('view-mode-grid');

        if (btnIg && btnGrid) {
            if (mode === 'ig') {
                btnIg.classList.add('active');
                btnGrid.classList.remove('active');
            } else {
                btnGrid.classList.add('active');
                btnIg.classList.remove('active');
            }
        }
        renderGallery();
    };

    function renderGallery() {
        const container = document.getElementById('history-container');
        if (!container) return;

        if (!currentGalleryItems || currentGalleryItems.length === 0) {
            container.innerHTML = `
                <div style="color: var(--text-muted); text-align: center; padding: 4rem 1rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📸</div>
                    <div style="font-size: 1.1rem; font-weight: 600; color: #fff; margin-bottom: 0.5rem;">Aún no se han producido carruseles</div>
                    <div style="font-size: 0.85rem;">Ve a la pestaña <strong>🚀 Generador</strong> para crear tu primer carrusel de Instagram.</div>
                </div>
            `;
            return;
        }

        if (galleryViewMode === 'ig') {
            // Instagram Feed Layout
            let html = '<div class="ig-feed-container">';
            currentGalleryItems.forEach((item, idx) => {
                html += renderInstagramCard(item, idx);
            });
            html += '</div>';
            container.innerHTML = html;

            // Attach touch swipe & mouse drag listeners to each card viewport
            currentGalleryItems.forEach((item, idx) => {
                const vp = document.getElementById(`viewport-${idx}`);
                if (vp) attachSwipeListeners(vp, idx, item.slides);
            });
        } else {
            // Grid Layout
            let html = '';
            currentGalleryItems.forEach((item, idx) => {
                let slidesHtml = '';
                item.slides.forEach((s, sIdx) => {
                    slidesHtml += `
                        <div class="slide-card">
                            <div class="slide-img-wrapper">
                                <img src="${s}" alt="Slide ${sIdx+1}" loading="lazy">
                            </div>
                            <div class="slide-info">
                                <span class="slide-badge">Slide ${sIdx+1}</span>
                                <a href="${s}" download class="btn btn-secondary" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;">⬇</a>
                            </div>
                        </div>
                    `;
                });

                const topic = item.row_data ? (item.row_data['TEMA'] || item.folder) : item.folder;
                const rowN = item.row_data ? (item.row_data['N°'] || '') : '';

                html += `
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <span>📁 Fila ${rowN ? `N° ${rowN}: ` : ''}${escapeHtml(topic)}</span>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <span class="pill">${item.total_slides} slides</span>
                                <a href="api.php?action=download_zip&folder=${encodeURIComponent(item.folder)}" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                    📦 Descargar ZIP
                                </a>
                            </div>
                        </div>
                        <div class="slides-grid">
                            ${slidesHtml}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }
    }

    // --------------------------------------------------------------------------
    // INSTAGRAM CARD COMPONENT BUILDER
    // --------------------------------------------------------------------------
    function renderInstagramCard(item, postIndex) {
        const totalSlides = item.slides.length;
        const title = item.row_data ? (item.row_data['TÍTULO / PORTADA'] || item.row_data['TEMA'] || item.folder) : item.folder;
        const topic = item.row_data ? (item.row_data['TEMA'] || '') : '';
        const dra = item.row_data ? (item.row_data['DRA'] || 'Jovany') : 'Jovany';
        const rowNum = item.row_data ? (item.row_data['N°'] || '') : '';
        const dateStr = item.created_at || 'Reciente';

        let slidesHtml = '';
        item.slides.forEach((s, i) => {
            slidesHtml += `
                <div class="ig-slide">
                    <img src="${s}" alt="Slide ${i+1}" loading="lazy">
                </div>
            `;
        });

        let dotsHtml = '';
        for (let i = 0; i < totalSlides; i++) {
            dotsHtml += `<div class="ig-dot ${i === 0 ? 'active' : ''}" data-idx="${i}" onclick="jumpToSlide('${postIndex}', ${i})"></div>`;
        }

        return `
            <article class="ig-post-card" id="ig-post-${postIndex}" data-current="0" data-total="${totalSlides}">
                <!-- Instagram Header -->
                <div class="ig-post-header">
                    <div class="ig-profile-info">
                        <div class="ig-avatar-ring">
                            <div class="ig-avatar-inner">JM</div>
                        </div>
                        <div class="ig-username-box">
                            <div class="ig-username-line">
                                jm.odontologiaintegral
                                <span class="ig-badge-verified">✓</span>
                                <span style="color: var(--text-muted); font-size: 0.78rem; font-weight: normal; margin-left: 2px;">• Siguiendo</span>
                            </div>
                            <div class="ig-subtitle-line">
                                ${topic ? escapeHtml(topic) : 'Consultorio JM Odontología'} ${rowNum ? `• Fila ${rowNum}` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="ig-more-options" title="Opciones de publicación">•••</div>
                </div>

                <!-- 4:5 Slider Viewport -->
                <div class="ig-viewport" id="viewport-${postIndex}">
                    <div class="ig-counter-badge" id="counter-${postIndex}">1/${totalSlides}</div>

                    ${totalSlides > 1 ? `
                        <button class="ig-arrow-btn ig-arrow-prev hidden" id="prev-${postIndex}" onclick="moveSlide('${postIndex}', -1)" aria-label="Slide anterior">‹</button>
                        <button class="ig-arrow-btn ig-arrow-next" id="next-${postIndex}" onclick="moveSlide('${postIndex}', 1)" aria-label="Slide siguiente">›</button>
                    ` : ''}

                    <div class="ig-track" id="track-${postIndex}">
                        ${slidesHtml}
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="ig-actions-bar">
                    <div class="ig-actions-left">
                        <button class="ig-action-btn" id="like-${postIndex}" onclick="toggleLike('${postIndex}')" title="Me gusta">🤍</button>
                        <button class="ig-action-btn" title="Comentar">💬</button>
                        <button class="ig-action-btn" title="Compartir">↗️</button>
                    </div>

                    <div class="ig-dots-box" id="dots-${postIndex}">
                        ${dotsHtml}
                    </div>

                    <div>
                        <button class="ig-action-btn" title="Guardar">🔖</button>
                    </div>
                </div>

                <!-- Caption & Metadata -->
                <div class="ig-caption-box">
                    <div class="ig-likes-label" id="likes-count-${postIndex}">1.248 Me gusta</div>
                    <div class="ig-caption-body">
                        <strong>jm.odontologiaintegral</strong>
                        <span>${escapeHtml(title)}</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #38bdf8; margin-bottom: 0.5rem; word-break: break-word;">
                        #JMOdontologia #SaludBucal #Dra${escapeHtml(dra)} #EsteticaDental #OdontologiaIntegral
                    </div>
                    <div class="ig-time-meta">
                        Producido: ${dateStr} • ${item.folder}
                    </div>

                    <!-- Toolbar de Descargas -->
                    <div class="ig-post-toolbar">
                        <a id="btn-download-slide-${postIndex}" href="${item.slides[0]}" download class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.85rem; flex: 1;">
                            ⬇ Descargar Slide Actual
                        </a>
                        <a href="api.php?action=download_zip&folder=${encodeURIComponent(item.folder)}" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.85rem;">
                            📦 Descargar Todos (.ZIP)
                        </a>
                    </div>
                </div>
            </article>
        `;
    }

    // --------------------------------------------------------------------------
    // INSTAGRAM SLIDER CONTROLS & GESTURES
    // --------------------------------------------------------------------------
    window.moveSlide = function(postIndex, direction) {
        const card = document.getElementById(`ig-post-${postIndex}`);
        if (!card) return;
        const total = parseInt(card.getAttribute('data-total'), 10);
        let current = parseInt(card.getAttribute('data-current'), 10);

        let next = current + direction;
        if (next < 0) next = 0;
        if (next >= total) next = total - 1;

        updateSlidePosition(postIndex, next);
    };

    window.jumpToSlide = function(postIndex, slideIdx) {
        updateSlidePosition(postIndex, slideIdx);
    };

    function updateSlidePosition(postIndex, newIdx) {
        const card = document.getElementById(`ig-post-${postIndex}`);
        if (!card) return;
        const total = parseInt(card.getAttribute('data-total'), 10);
        card.setAttribute('data-current', newIdx);

        // Move track
        const track = document.getElementById(`track-${postIndex}`);
        if (track) {
            track.style.transform = `translateX(-${newIdx * 100}%)`;
        }

        // Update badge 1/8
        const counter = document.getElementById(`counter-${postIndex}`);
        if (counter) {
            counter.textContent = `${newIdx + 1}/${total}`;
        }

        // Update dots
        const dotsBox = document.getElementById(`dots-${postIndex}`);
        if (dotsBox) {
            const dots = dotsBox.querySelectorAll('.ig-dot');
            dots.forEach((d, i) => {
                if (i === newIdx) d.classList.add('active');
                else d.classList.remove('active');
            });
        }

        // Update arrow visibility
        const prevBtn = document.getElementById(`prev-${postIndex}`);
        const nextBtn = document.getElementById(`next-${postIndex}`);
        if (prevBtn) {
            if (newIdx === 0) prevBtn.classList.add('hidden');
            else prevBtn.classList.remove('hidden');
        }
        if (nextBtn) {
            if (newIdx === total - 1) nextBtn.classList.add('hidden');
            else nextBtn.classList.remove('hidden');
        }

        // Update current slide download button href
        const dlBtn = document.getElementById(`btn-download-slide-${postIndex}`);
        if (dlBtn) {
            let slideUrl = '';
            if (postIndex === 'prod_preview') {
                const img = track ? track.querySelectorAll('.ig-slide img')[newIdx] : null;
                if (img) slideUrl = img.getAttribute('src');
            } else if (currentGalleryItems[postIndex] && currentGalleryItems[postIndex].slides[newIdx]) {
                slideUrl = currentGalleryItems[postIndex].slides[newIdx];
            }
            if (slideUrl) dlBtn.href = slideUrl;
        }
    }

    // Touch and mouse drag swipe support
    function attachSwipeListeners(viewportEl, postIndex, slides) {
        let startX = 0;
        let currentX = 0;
        let isTouching = false;
        let isMouseDown = false;

        // Touch gestures
        viewportEl.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isTouching = true;
        }, { passive: true });

        viewportEl.addEventListener('touchmove', (e) => {
            if (!isTouching) return;
            currentX = e.touches[0].clientX;
        }, { passive: true });

        viewportEl.addEventListener('touchend', () => {
            if (!isTouching) return;
            const diffX = currentX - startX;
            if (Math.abs(diffX) > 40 && currentX !== 0) {
                if (diffX < 0) moveSlide(postIndex, 1);
                else moveSlide(postIndex, -1);
            }
            isTouching = false;
            startX = 0;
            currentX = 0;
        });

        // Mouse drag gestures
        viewportEl.addEventListener('mousedown', (e) => {
            isMouseDown = true;
            startX = e.clientX;
        });

        viewportEl.addEventListener('mouseup', (e) => {
            if (!isMouseDown) return;
            const diffX = e.clientX - startX;
            if (Math.abs(diffX) > 45) {
                if (diffX < 0) moveSlide(postIndex, 1);
                else moveSlide(postIndex, -1);
            }
            isMouseDown = false;
        });

        viewportEl.addEventListener('mouseleave', () => {
            isMouseDown = false;
        });

        // Double click to like effect
        viewportEl.addEventListener('dblclick', () => {
            toggleLike(postIndex);
        });
    }

    // Like button toggle
    window.toggleLike = function(postIndex) {
        const btn = document.getElementById(`like-${postIndex}`);
        const count = document.getElementById(`likes-count-${postIndex}`);
        if (!btn) return;

        if (btn.classList.contains('liked')) {
            btn.classList.remove('liked');
            btn.textContent = '🤍';
            if (count) count.textContent = '1.248 Me gusta';
        } else {
            btn.classList.add('liked');
            btn.textContent = '❤️';
            if (count) count.textContent = '1.249 Me gusta';
        }
    };

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Export loadHistory to window
    window.loadHistory = loadHistory;

    // Inicialización al cargar la página
    loadConfig();
    loadSheetRowsToSelect();
    loadTemplates();
});
