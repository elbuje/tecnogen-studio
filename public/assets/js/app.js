// TecnoGen Studio — App Frontend Logic
document.addEventListener('DOMContentLoaded', () => {
    // State
    let currentRowData = null;
    let configData = {};
    let sheetRowsData = [];

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
        });
    });

    // 1. CARGAR CONFIGURACIÓN
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

    // 2. CARGAR FILAS DEL GOOGLE SHEET EN SELECTOR
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

    // 3. BOTÓN: CARGAR DATOS Y ARMAR PROMPT
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
                
                // Mostrar Ficha Técnica
                document.getElementById('info-n').textContent = data.row['N°'] || rowNum;
                document.getElementById('info-tema').textContent = data.row['TEMA'] || '-';
                document.getElementById('info-titulo').textContent = data.row['TÍTULO / PORTADA'] || '-';
                document.getElementById('info-dra').textContent = data.row['DRA'] || 'Jovany';
                
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

    // 4. BOTÓN: ENVIAR A PRODUCIR
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
                    data.images.forEach((imgUrl, i) => {
                        const card = document.createElement('div');
                        card.className = 'slide-card';
                        card.innerHTML = `
                            <div class="slide-img-wrapper">
                                <img src="${imgUrl}" alt="Slide ${i+1}">
                            </div>
                            <div class="slide-info">
                                <span class="slide-badge">Slide ${i+1}</span>
                                <a href="${imgUrl}" download class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">⬇ Descargar</a>
                            </div>
                        `;
                        galleryContainer.appendChild(card);
                    });
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

    // 5. EXPLORADOR DEL SHEET
    async function loadSheetExplorer() {
        const tbody = document.getElementById('sheet-table-body');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 2rem;">Cargando Google Sheet en tiempo real...</td></tr>';
        
        try {
            const res = await fetch('api.php?action=get_sheet_rows');
            const data = await res.json();
            if (data.status === 'ok' && data.rows) {
                tbody.innerHTML = '';
                data.rows.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${r.n}</strong></td>
                        <td>${r.tema || '-'}</td>
                        <td>${r.titulo || '-'}</td>
                        <td>${r.dra || 'Jovany'}</td>
                        <td><span class="pill">${r.slides_count} slides</span></td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="selectRowFromTable('${r.n}')">
                                Cargar al Generador
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="color:var(--danger); text-align:center;">Error: ${data.message}</td></tr>`;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" style="color:var(--danger); text-align:center;">Error de conexión</td></tr>`;
        }
    }

    window.selectRowFromTable = function(rowNum) {
        document.getElementById('input-row-num').value = rowNum;
        document.querySelector('[data-tab="tab-generator"]').click();
        document.getElementById('btn-load-prompt').click();
    };

    // 6. HISTORIAL
    async function loadHistory() {
        const container = document.getElementById('history-container');
        container.innerHTML = '<p style="color:var(--text-muted);">Cargando historial...</p>';

        try {
            const res = await fetch('api.php?action=get_history');
            const data = await res.json();
            if (data.status === 'ok' && data.history.length > 0) {
                container.innerHTML = '';
                data.history.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'card';
                    div.style.marginBottom = '1.5rem';
                    div.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem;">
                            <h3 style="font-size: 1rem; color: #fff;">📁 ${item.folder}</h3>
                            <span class="pill"><strong>${item.total_slides}</strong> imágenes</span>
                        </div>
                        <div class="slides-grid">
                            ${item.slides.map((s, i) => `
                                <div class="slide-card">
                                    <div class="slide-img-wrapper"><img src="${s}"></div>
                                    <div class="slide-info">
                                        <span class="slide-badge">Slide ${i+1}</span>
                                        <a href="${s}" download class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">⬇</a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<p style="color:var(--text-muted);">Aún no hay producciones previas guardadas.</p>';
            }
        } catch (e) {
            container.innerHTML = '<p style="color:var(--danger);">Error cargando historial.</p>';
        }
    }

    // Init
    loadConfig();
    loadSheetRowsToSelect();
});
