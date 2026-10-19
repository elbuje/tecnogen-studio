// TecnoGen Studio 2.0 — Estudio Híbrido (Google Sheet + Hilo Estilo ChatGPT + Sunburst)
document.addEventListener('DOMContentLoaded', () => {
    // --------------------------------------------------------------------------
    // ESTADO GLOBAL DEL ESTUDIO (SIN "FOTO MADRE" FIJA EN SERVIDOR)
    // --------------------------------------------------------------------------
    let currentRowData = null;
    let currentRowSummary = {
        n: '2',
        tema: '',
        titulo: '',
        dra: 'Jovany',
        guion: '',
        subtitulos: '',
        instagram_caption: ''
    };
    let configData = {};
    let sheetRowsData = [];
    let doctorCatalog = [];

    // Activos disponibles y seleccionados en memoria del navegador
    let availableDoctorPhotos = [];
    let selectedDoctorPhotoIds = []; // Array de IDs seleccionados para rotar entre slides
    let availableLogos = [];
    let selectedLogo = null;
    let brandManual = null;

    // Escaleta de slides (1..N) y sesión de producción activa
    let slidePlan = [];
    let activeCarouselFolder = null;
    let isProducingSequence = false;
    let stopSequenceRequested = false;

    // Galería de historial
    let currentGalleryItems = [];
    let galleryViewMode = 'ig'; // 'ig' | 'grid'

    // --------------------------------------------------------------------------
    // NAVEGACIÓN DE PESTAÑAS Y MODO DE ORIGEN (SHEET VS LIBRE CHATGPT)
    // --------------------------------------------------------------------------
    const navBtns = document.querySelectorAll('.nav-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

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

    const btnModeSheet = document.getElementById('btn-mode-sheet');
    const btnModeFree = document.getElementById('btn-mode-free');
    const panelSheet = document.getElementById('source-panel-sheet');
    const panelFree = document.getElementById('source-panel-free');

    if (btnModeSheet && btnModeFree) {
        btnModeSheet.addEventListener('click', () => {
            btnModeSheet.classList.add('active');
            btnModeFree.classList.remove('active');
            panelSheet.style.display = 'block';
            panelFree.style.display = 'none';
        });
        btnModeFree.addEventListener('click', () => {
            btnModeFree.classList.add('active');
            btnModeSheet.classList.remove('active');
            panelFree.style.display = 'block';
            panelSheet.style.display = 'none';
        });
    }

    // --------------------------------------------------------------------------
    // 1. CONFIGURACIÓN Y MODELOS DE OPENAI
    // --------------------------------------------------------------------------
    async function loadConfig() {
        try {
            const res = await fetch('api.php?action=get_config');
            const data = await res.json();
            if (data.status === 'ok') {
                configData = data;
                document.getElementById('input-api-key').value = data.openai_api_key || '';
                document.getElementById('input-sheet-url').value = data.sheet_url || '';
                if (document.getElementById('input-drive-api-key')) {
                    document.getElementById('input-drive-api-key').value = data.google_drive_api_key || '';
                }
                if (document.getElementById('select-director-model') && data.director_model) {
                    document.getElementById('select-director-model').value = data.director_model;
                }
                if (document.getElementById('chk-stamp-logo')) {
                    document.getElementById('chk-stamp-logo').checked = !!data.stamp_logo_overlay;
                }

                const modelSelect = document.getElementById('select-model');
                const quickSelect = document.getElementById('quick-model-select');
                const savedModel = data.selected_model || 'gpt-image-2.5-sunburst';

                [modelSelect, quickSelect].forEach(sel => {
                    if (!sel) return;
                    if (!Array.from(sel.options).some(o => o.value === savedModel)) {
                        const opt = document.createElement('option');
                        opt.value = savedModel;
                        opt.textContent = savedModel;
                        sel.insertBefore(opt, sel.firstChild);
                    }
                    sel.value = savedModel;
                });
            }
        } catch (e) {
            console.error('Error cargando config:', e);
        }
    }

    document.getElementById('btn-save-config').addEventListener('click', async () => {
        const apiKey = document.getElementById('input-api-key').value.trim();
        const model = document.getElementById('select-model').value.trim();
        const directorModel = document.getElementById('select-director-model') ? document.getElementById('select-director-model').value.trim() : 'gpt-4o';
        const sheetUrl = document.getElementById('input-sheet-url').value.trim();
        const driveApiKey = document.getElementById('input-drive-api-key') ? document.getElementById('input-drive-api-key').value.trim() : '';
        const alertBox = document.getElementById('alert-config');

        try {
            const res = await fetch('api.php?action=save_config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    openai_api_key: apiKey,
                    selected_model: model,
                    director_model: directorModel,
                    sheet_url: sheetUrl,
                    google_drive_api_key: driveApiKey
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

                if (data.image_models && data.image_models.length > 0) {
                    const groupImg = document.createElement('optgroup');
                    groupImg.label = '🎨 Modelos de Imagen';
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

                const groupAll = document.createElement('optgroup');
                groupAll.label = '🌐 Todos los Modelos de la Cuenta';
                data.all_models.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    groupAll.appendChild(opt);
                });
                modelSelect.appendChild(groupAll);

                alertBox.style.display = 'block';
                alertBox.className = 'alert alert-success';
                alertBox.textContent = `✓ Conexión exitosa. Se detectaron ${data.all_models.length} modelos habilitados.`;
            } else {
                alertBox.style.display = 'block';
                alertBox.className = 'alert alert-error';
                alertBox.textContent = 'Error: ' + data.message;
            }
        } catch (e) {
            alert('Error consultando modelos: ' + e);
        } finally {
            btn.disabled = false;
            btn.textContent = '🔄 Consultar Modelos de mi Cuenta OpenAI';
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        }
    });

    // --------------------------------------------------------------------------
    // 2. PLANTILLAS MODULARES (.TXT)
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

    document.getElementById('btn-save-templates').addEventListener('click', async () => {
        const masterTpl = document.getElementById('textarea-master-tpl').value;
        const slideTpl = document.getElementById('textarea-slide-tpl').value;
        const alertBox = document.getElementById('alert-template');

        try {
            const res = await fetch('api.php?action=save_templates', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ master_template: masterTpl, slide_template: slideTpl })
            });
            const data = await res.json();
            alertBox.style.display = 'block';
            alertBox.className = data.status === 'ok' ? 'alert alert-success' : 'alert alert-error';
            alertBox.textContent = (data.status === 'ok' ? '✓ ' : 'Error: ') + data.message;
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        } catch (e) {
            alert('Error al guardar plantillas: ' + e);
        }
    });

    document.getElementById('btn-reset-templates').addEventListener('click', async () => {
        if (!confirm('¿Restaurar las plantillas a sus valores originales?')) return;
        const alertBox = document.getElementById('alert-template');
        try {
            const res = await fetch('api.php?action=reset_templates', { method: 'POST' });
            const data = await res.json();
            if (data.status === 'ok') {
                document.getElementById('textarea-master-tpl').value = data.master_template;
                document.getElementById('textarea-slide-tpl').value = data.slide_template;
                alertBox.style.display = 'block';
                alertBox.className = 'alert alert-success';
                alertBox.textContent = '✓ ' + data.message;
                setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
            }
        } catch (e) {
            alert('Error al restaurar plantillas: ' + e);
        }
    });

    // --------------------------------------------------------------------------
    // 3. EXPLORADOR Y SELECTOR DEL GOOGLE SHEET + CATÁLOGO DE CARPETAS DRIVE
    // --------------------------------------------------------------------------
    async function loadSheetRowsToSelect() {
        try {
            const res = await fetch('api.php?action=get_sheet_rows');
            const data = await res.json();
            if (data.status === 'ok') {
                sheetRowsData = data.rows || [];
                doctorCatalog = data.doctor_catalog || [];

                const selectRow = document.getElementById('select-sheet-row');
                selectRow.innerHTML = '<option value="">-- Seleccionar fila del Sheet --</option>';
                sheetRowsData.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.n;
                    opt.textContent = `Fila N° ${r.n} — ${r.tema || 'Sin tema'} | Dra. ${r.dra} (${r.slides_count} slides)`;
                    selectRow.appendChild(opt);
                });

                populateDoctorFolderCatalog();
            }
        } catch (e) {
            console.error('Error cargando filas del Sheet:', e);
        }
    }

    function populateDoctorFolderCatalog(activeFolderId = '') {
        const sel = document.getElementById('select-doctor-folder');
        if (!sel) return;
        sel.innerHTML = '<option value="">-- Cambiar a carpeta de otra Doctora del Sheet --</option>';
        doctorCatalog.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.folder_id;
            opt.textContent = `📁 Dra. ${c.dra} (${c.folder_id.substring(0, 8)}...)`;
            if (activeFolderId && c.folder_id === activeFolderId) {
                opt.selected = true;
            }
            sel.appendChild(opt);
        });
    }

    document.getElementById('select-sheet-row').addEventListener('change', (e) => {
        if (e.target.value) {
            document.getElementById('input-row-num').value = e.target.value;
            document.getElementById('btn-load-prompt').click();
        }
    });

    // --------------------------------------------------------------------------
    // 4. CARGAR FILA DEL SHEET O INICIAR MODO LIBRE (CHATGPT STYLE)
    // --------------------------------------------------------------------------
    document.getElementById('btn-load-prompt').addEventListener('click', async () => {
        const rowNum = document.getElementById('input-row-num').value.trim();
        if (!rowNum) {
            alert('Ingresa un número de fila válido.');
            return;
        }

        const btn = document.getElementById('btn-load-prompt');
        btn.disabled = true;
        btn.textContent = '⏳ Cargando Sheet y Drive...';

        try {
            const res = await fetch(`api.php?action=get_row_context&n=${encodeURIComponent(rowNum)}`);
            const data = await res.json();

            if (data.status === 'ok') {
                activeCarouselFolder = null; // Nueva sesión limpia
                currentRowData = data.row;
                currentRowSummary = data.row_summary;
                if (data.doctor_catalog) {
                    doctorCatalog = data.doctor_catalog;
                }

                updateSummaryBar(currentRowSummary, data.vars);
                populateDoctorFolderCatalog(currentRowSummary.photo_folder_id);

                // Cargar activos disponibles desde Drive (¡sin pre-fijar foto madre en el servidor!)
                availableDoctorPhotos = (data.assets && data.assets.photos) ? data.assets.photos : [];
                // Pre-seleccionar en la UI hasta 3 fotos espaciadas para rotar entre slides y evitar sesgo
                selectVariedPhotosAutomatically(availableDoctorPhotos, 3);

                availableLogos = (data.assets && data.assets.logos) ? data.assets.logos : [];
                if (!selectedLogo && availableLogos.length > 0) {
                    selectedLogo = availableLogos[0];
                }
                brandManual = (data.assets && data.assets.brand_manual) ? data.assets.brand_manual : null;

                renderDoctorPhotosGallery();
                renderLogosGallery();
                renderBrandManualBox();

                document.getElementById('panel-drive-assets').style.display = 'block';

                // Prompt maestro general y Escaleta de Slides
                document.getElementById('prompt-editor').value = data.prompt || '';
                slidePlan = data.slide_plan || [];
                renderSlidePlanCards();

                document.getElementById('panel-prompt-editor').style.display = 'block';
                document.getElementById('panel-drive-assets').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            alert('Error al obtener datos de la fila: ' + e);
        } finally {
            btn.disabled = false;
            btn.textContent = '🔍 Cargar Fila y Activos de Drive';
        }
    });

    // Modo Libre / Estilo ChatGPT
    document.getElementById('btn-init-free-mode').addEventListener('click', async () => {
        const tema = document.getElementById('free-input-tema').value.trim() || 'Odontología Integral';
        const titulo = document.getElementById('free-input-titulo').value.trim() || tema;
        const dra = document.getElementById('free-input-dra').value.trim() || 'Jovany';
        const guion = document.getElementById('free-input-guion').value.trim();

        if (!guion) {
            alert('Escribe o pega el guion del carrusel para armar la secuencia de slides.');
            return;
        }

        const btn = document.getElementById('btn-init-free-mode');
        btn.disabled = true;
        btn.textContent = '⏳ Armando escaleta...';

        try {
            // Si aún no hemos cargado activos base de Drive, cargamos la fila 1 de fondo para tener catálogo de fotos y logos
            if (availableLogos.length === 0 && availableDoctorPhotos.length === 0) {
                const initRes = await fetch('api.php?action=get_row_context&n=1');
                const initData = await initRes.json();
                if (initData.status === 'ok' && initData.assets) {
                    availableDoctorPhotos = initData.assets.photos || [];
                    selectVariedPhotosAutomatically(availableDoctorPhotos, 3);
                    availableLogos = initData.assets.logos || [];
                    if (availableLogos.length > 0) selectedLogo = availableLogos[0];
                    brandManual = initData.assets.brand_manual || null;
                    if (initData.doctor_catalog) doctorCatalog = initData.doctor_catalog;
                    populateDoctorFolderCatalog();
                }
            }

            // Pedir al planificador la escaleta para este guion libre
            const res = await fetch('api.php?action=plan_carousel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    guion,
                    tema,
                    titulo,
                    dra,
                    subtitulos: '',
                    use_ai_director: false
                })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                activeCarouselFolder = null;
                currentRowSummary = {
                    n: 'Libre',
                    tema,
                    titulo,
                    dra,
                    guion,
                    subtitulos: '',
                    instagram_caption: `🦷 ${titulo} — JM Odontología Integral #JMOdontologia`,
                    slides_count: data.total_slides
                };
                currentRowData = {
                    'N°': 'Libre',
                    'TEMA': tema,
                    'TÍTULO / PORTADA': titulo,
                    'DRA': dra,
                    'GUION': guion
                };

                updateSummaryBar(currentRowSummary, { N: 'Libre', TEMA: tema, TITULO: titulo, DRA: dra, CANTIDAD_SLIDES: data.total_slides });
                renderDoctorPhotosGallery();
                renderLogosGallery();
                renderBrandManualBox();

                slidePlan = data.slide_plan || [];
                renderSlidePlanCards();

                document.getElementById('panel-drive-assets').style.display = 'block';
                document.getElementById('panel-prompt-editor').style.display = 'block';
                document.getElementById('panel-prompt-editor').scrollIntoView({ behavior: 'smooth' });
            }
        } catch (e) {
            alert('Error iniciando modo libre: ' + e);
        } finally {
            btn.disabled = false;
            btn.textContent = '🎬 Preparar Escaleta y Activos con este Guion';
        }
    });

    function updateSummaryBar(summary, vars) {
        document.getElementById('info-n').textContent = summary.n || '-';
        document.getElementById('info-tema').textContent = summary.tema || '-';
        document.getElementById('info-titulo').textContent = summary.titulo || '-';
        document.getElementById('info-dra').textContent = summary.dra || 'Jovany';
        document.getElementById('info-slides').textContent = `${summary.slides_count || 1} slides`;
        document.getElementById('raw-guion-editor').value = summary.guion || '';

        const varsList = document.getElementById('vars-list');
        varsList.innerHTML = '';
        Object.entries(vars || {}).forEach(([k, v]) => {
            if (k === 'GUION') return;
            const div = document.createElement('div');
            div.style.marginBottom = '3px';
            div.innerHTML = `<span style="color: var(--primary);">{{${escapeHtml(k)}}}:</span> <span style="color: #e2e8f0;">${escapeHtml(String(v || '(vacío)'))}</span>`;
            varsList.appendChild(div);
        });

        document.getElementById('panel-row-info').style.display = 'block';
    }

    document.getElementById('btn-toggle-vars').addEventListener('click', () => {
        const p = document.getElementById('panel-vars-detail');
        p.style.display = (p.style.display === 'none') ? 'block' : 'none';
    });

    document.getElementById('btn-toggle-master-prompt').addEventListener('click', () => {
        const b = document.getElementById('box-master-prompt-collapse');
        b.style.display = (b.style.display === 'none') ? 'block' : 'none';
    });

    // Actualizar escaleta si el usuario edita el Guion crudo
    document.getElementById('btn-rebuild-from-guion').addEventListener('click', async () => {
        const newGuion = document.getElementById('raw-guion-editor').value.trim();
        if (!newGuion) return;
        currentRowSummary.guion = newGuion;
        await replanCarouselSequence(false);
    });

    // --------------------------------------------------------------------------
    // 5. GESTIÓN DE FOTOS DE DOCTORA, LOGOS Y SUBIDAS DESDE PC (SIN FOTO MADRE)
    // --------------------------------------------------------------------------
    function selectVariedPhotosAutomatically(photos, count = 3) {
        selectedDoctorPhotoIds = [];
        if (!photos || photos.length === 0) return;
        if (photos.length <= count) {
            selectedDoctorPhotoIds = photos.map(p => p.id);
            return;
        }
        // Elegir fotos distribuidas (ej. inicio, medio, final) para que tengan distintos ángulos
        const step = Math.max(1, Math.floor(photos.length / count));
        for (let i = 0; i < count; i++) {
            const idx = Math.min(photos.length - 1, i * step);
            if (!selectedDoctorPhotoIds.includes(photos[idx].id)) {
                selectedDoctorPhotoIds.push(photos[idx].id);
            }
        }
    }

    function renderDoctorPhotosGallery() {
        const gallery = document.getElementById('doctor-photos-gallery');
        const countEl = document.getElementById('count-doctor-photos');
        const lblSelected = document.getElementById('lbl-selected-photo');

        countEl.textContent = availableDoctorPhotos.length;
        gallery.innerHTML = '';

        if (availableDoctorPhotos.length === 0) {
            gallery.innerHTML = '<div style="color: var(--text-muted); font-size: 0.8rem; padding: 0.75rem;">No hay fotos en esta carpeta. Puedes elegir otra carpeta arriba o subir fotos desde tu PC.</div>';
            lblSelected.textContent = 'Ninguna (Sin foto de doctora)';
            return;
        }

        availableDoctorPhotos.forEach((p) => {
            const selOrder = selectedDoctorPhotoIds.indexOf(p.id);
            const isSelected = selOrder !== -1;

            const card = document.createElement('div');
            card.className = `doctor-photo-card ${isSelected ? 'selected' : ''}`;
            card.innerHTML = `
                <span class="selected-badge">#${selOrder + 1}</span>
                <img src="${p.thumbnail}" alt="${escapeHtml(p.name)}" loading="lazy">
                <span class="photo-name" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</span>
            `;

            card.addEventListener('click', () => {
                const pos = selectedDoctorPhotoIds.indexOf(p.id);
                if (pos !== -1) {
                    selectedDoctorPhotoIds.splice(pos, 1);
                } else {
                    selectedDoctorPhotoIds.push(p.id);
                }
                renderDoctorPhotosGallery();
                renderSlidePlanCards(); // Actualizar asignación visual en cada slide
            });

            gallery.appendChild(card);
        });

        if (selectedDoctorPhotoIds.length === 0) {
            lblSelected.textContent = 'Ninguna seleccionada (Se generará sin foto de referencia)';
        } else if (selectedDoctorPhotoIds.length === 1) {
            const single = availableDoctorPhotos.find(x => x.id === selectedDoctorPhotoIds[0]);
            lblSelected.textContent = `1 foto (${single ? single.name : ''})`;
        } else {
            const names = selectedDoctorPhotoIds.map(id => {
                const found = availableDoctorPhotos.find(x => x.id === id);
                return found ? found.name : id;
            });
            lblSelected.textContent = `${selectedDoctorPhotoIds.length} fotos en rotación (${names.join(', ')})`;
        }
    }

    // Botón: Elegir 3 fotos al azar de la carpeta
    document.getElementById('btn-random-photos').addEventListener('click', () => {
        if (availableDoctorPhotos.length === 0) return;
        const shuffled = [...availableDoctorPhotos].sort(() => 0.5 - Math.random());
        selectedDoctorPhotoIds = shuffled.slice(0, Math.min(3, shuffled.length)).map(p => p.id);
        renderDoctorPhotosGallery();
        renderSlidePlanCards();
    });

    // Botón: Ninguna foto (Sin doctora)
    document.getElementById('btn-clear-photos').addEventListener('click', () => {
        selectedDoctorPhotoIds = [];
        renderDoctorPhotosGallery();
        renderSlidePlanCards();
    });

    // Cambiar de carpeta de Drive desde el desplegable de Doctoras del Sheet
    document.getElementById('select-doctor-folder').addEventListener('change', async (e) => {
        const folderId = e.target.value;
        if (!folderId) return;
        const docEntry = doctorCatalog.find(d => d.folder_id === folderId);
        if (docEntry && docEntry.dra) {
            currentRowSummary.dra = docEntry.dra;
            document.getElementById('info-dra').textContent = docEntry.dra;
        }
        await loadDriveFolderPhotos(folderId);
    });

    // Abrir cualquier link de carpeta de Drive pegado manualmente
    document.getElementById('btn-load-custom-folder').addEventListener('click', async () => {
        const val = document.getElementById('input-custom-drive-folder').value.trim();
        if (!val) {
            alert('Pega el link o ID de una carpeta compartida de Google Drive.');
            return;
        }
        await loadDriveFolderPhotos(val);
    });

    async function loadDriveFolderPhotos(folderUrlOrId) {
        const gallery = document.getElementById('doctor-photos-gallery');
        gallery.innerHTML = '<div style="color: var(--primary); font-size: 0.82rem; padding: 1rem;">⏳ Consultando imágenes en Google Drive...</div>';
        try {
            const res = await fetch(`api.php?action=list_drive_folder&folder=${encodeURIComponent(folderUrlOrId)}`);
            const data = await res.json();
            if (data.status === 'ok') {
                // Conservar las subidas manualmente por el usuario en esta sesión y sumar las de la carpeta Drive
                const uploaded = availableDoctorPhotos.filter(p => p.source === 'upload');
                availableDoctorPhotos = [...uploaded, ...(data.files || [])];
                selectVariedPhotosAutomatically(availableDoctorPhotos, 3);
                renderDoctorPhotosGallery();
                renderSlidePlanCards();
            } else {
                alert('Error leyendo carpeta de Drive: ' + data.message);
                renderDoctorPhotosGallery();
            }
        } catch (e) {
            alert('Error conectando con Drive: ' + e);
            renderDoctorPhotosGallery();
        }
    }

    // Subir foto(s) de la doctora desde la PC
    document.getElementById('upload-doctor-photo').addEventListener('change', async (e) => {
        const files = Array.from(e.target.files || []);
        if (files.length === 0) return;

        for (const file of files) {
            const formData = new FormData();
            formData.append('type', 'photo');
            formData.append('file', file);

            try {
                const res = await fetch('api.php?action=upload_asset', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'ok' && data.asset) {
                    availableDoctorPhotos.unshift(data.asset);
                    if (!selectedDoctorPhotoIds.includes(data.asset.id)) {
                        selectedDoctorPhotoIds.unshift(data.asset.id);
                    }
                } else {
                    alert('Error subiendo imagen: ' + (data.message || 'Desconocido'));
                }
            } catch (err) {
                alert('Error subiendo archivo: ' + err);
            }
        }
        e.target.value = '';
        renderDoctorPhotosGallery();
        renderSlidePlanCards();
    });

    // Galería y Selección Visual de Logos
    function renderLogosGallery() {
        const gallery = document.getElementById('logos-gallery');
        const lblLogo = document.getElementById('lbl-selected-logo');
        if (!gallery) return;

        gallery.innerHTML = '';

        // Opción "Sin Logo"
        const noneCard = document.createElement('div');
        const isNone = !selectedLogo || selectedLogo.id === 'none';
        noneCard.className = `doctor-photo-card ${isNone ? 'selected' : ''}`;
        noneCard.style.flex = '0 0 90px';
        noneCard.innerHTML = `
            <span class="selected-badge">✓</span>
            <div style="width:100%; aspect-ratio:1; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.4); border-radius:6px; margin-bottom:0.35rem; font-size:1.3rem;">🚫</div>
            <span class="photo-name">Sin Logo</span>
        `;
        noneCard.addEventListener('click', () => {
            selectedLogo = { id: 'none', name: 'Sin Logo', source: 'none' };
            renderLogosGallery();
        });
        gallery.appendChild(noneCard);

        availableLogos.forEach(lg => {
            const isSel = selectedLogo && selectedLogo.id === lg.id;
            const card = document.createElement('div');
            card.className = `doctor-photo-card ${isSel ? 'selected' : ''}`;
            card.style.flex = '0 0 95px';
            card.innerHTML = `
                <span class="selected-badge">✓ Activo</span>
                <img src="${lg.thumbnail}" alt="${escapeHtml(lg.name)}" style="object-fit: contain; background: rgba(0,0,0,0.55); padding: 4px;" loading="lazy">
                <span class="photo-name" title="${escapeHtml(lg.name)}">${escapeHtml(lg.name)}</span>
            `;
            card.addEventListener('click', () => {
                selectedLogo = lg;
                renderLogosGallery();
            });
            gallery.appendChild(card);
        });

        lblLogo.textContent = selectedLogo ? selectedLogo.name : 'Sin Logo';
    }

    // Subir un Logo propio desde la PC
    document.getElementById('upload-logo-file').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('type', 'logo');
        formData.append('file', file);

        try {
            const res = await fetch('api.php?action=upload_asset', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'ok' && data.asset) {
                availableLogos.unshift(data.asset);
                selectedLogo = data.asset;
                renderLogosGallery();
            } else {
                alert('Error subiendo logo: ' + data.message);
            }
        } catch (err) {
            alert('Error subiendo logo: ' + err);
        }
        e.target.value = '';
    });

    function renderBrandManualBox() {
        const box = document.getElementById('box-brand-manual');
        if (!box) return;
        if (brandManual) {
            box.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.75rem; background: rgba(0,0,0,0.25); padding: 0.5rem 0.75rem; border-radius: 8px;">
                    <img src="${brandManual.thumbnail}" style="width: 48px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
                    <div>
                        <div style="color: #fff; font-weight: 600; font-size: 0.82rem;">${escapeHtml(brandManual.name)}</div>
                        <div style="color: #38bdf8; font-size: 0.74rem;">Paleta oficial: #16345F (Relax Blue) · #7DD3FC (Sky Pulse) · #FFFFFF</div>
                    </div>
                </div>
            `;
        } else {
            box.innerHTML = '<span style="color: var(--text-muted); font-size: 0.78rem;">Usando paleta oficial JM Odontología</span>';
        }
    }

    // --------------------------------------------------------------------------
    // 6. ESCALETA VISUAL DE SLIDES (TARJETAS EDITABLES 1..N + ROTACIÓN DE FOTOS)
    // --------------------------------------------------------------------------
    function getDoctorPhotoForSlide(slideIndex, slideObj) {
        if (!slideObj.include_doctor) return null;
        if (slideObj.assigned_photo_id && slideObj.assigned_photo_id !== 'rotate') {
            if (slideObj.assigned_photo_id === 'none') return null;
            return availableDoctorPhotos.find(p => p.id === slideObj.assigned_photo_id) || null;
        }
        // Rotar entre las fotos seleccionadas por el usuario
        if (selectedDoctorPhotoIds.length === 0) return null;
        const chosenId = selectedDoctorPhotoIds[slideIndex % selectedDoctorPhotoIds.length];
        return availableDoctorPhotos.find(p => p.id === chosenId) || null;
    }

    function renderSlidePlanCards() {
        const container = document.getElementById('slide-plan-container');
        const chatSelect = document.getElementById('chat-target-slide');
        if (!container) return;

        container.innerHTML = '';
        if (chatSelect) {
            const prevVal = chatSelect.value;
            chatSelect.innerHTML = `
                <option value="all">🎯 Todo el carrusel (${slidePlan.length} slides)</option>
                <option value="add_new">➕ Agregar nuevo slide al final</option>
            `;
            slidePlan.forEach(s => {
                const opt = document.createElement('option');
                opt.value = String(s.num);
                opt.textContent = `🎬 Solo Slide ${s.num} (${s.role})`;
                chatSelect.appendChild(opt);
            });
            if (Array.from(chatSelect.options).some(o => o.value === prevVal)) {
                chatSelect.value = prevVal;
            }
        }

        document.getElementById('info-slides').textContent = `${slidePlan.length} slides`;

        slidePlan.forEach((slide, idx) => {
            const assignedPhoto = getDoctorPhotoForSlide(idx, slide);
            const card = document.createElement('div');
            card.className = `slide-plan-card ${slide.generated_url ? 'done' : ''}`;
            card.id = `slide-plan-card-${slide.num}`;

            // Opciones del selector de foto para este slide individual
            let photoOptionsHtml = `<option value="rotate" ${(!slide.assigned_photo_id || slide.assigned_photo_id === 'rotate') ? 'selected' : ''}>🔄 Rotación automática (${assignedPhoto ? assignedPhoto.name : 'Sin foto'})</option>`;
            photoOptionsHtml += `<option value="none" ${(!slide.include_doctor || slide.assigned_photo_id === 'none') ? 'selected' : ''}>🚫 Sin doctora (Escena / Detalle clínico)</option>`;
            availableDoctorPhotos.forEach(p => {
                const isSel = slide.assigned_photo_id === p.id;
                photoOptionsHtml += `<option value="${escapeHtml(p.id)}" ${isSel ? 'selected' : ''}>👩‍⚕️ Fijar: ${escapeHtml(p.name)}</option>`;
            });

            card.innerHTML = `
                <div>
                    <div class="slide-plan-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="slide-num-pill">SLIDE ${slide.num} / ${slidePlan.length}</span>
                            <span class="slide-role-badge">${escapeHtml(slide.role || 'Desarrollo')}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <select class="slide-photo-select" data-idx="${idx}" style="width: auto; max-width: 260px; padding: 0.25rem 0.5rem; font-size: 0.76rem;">
                                ${photoOptionsHtml}
                            </select>
                            ${slidePlan.length > 1 ? `
                                <button type="button" class="btn btn-secondary btn-remove-slide" data-idx="${idx}" style="padding: 0.2rem 0.5rem; font-size: 0.72rem; color: var(--danger);" title="Eliminar este slide">✕</button>
                            ` : ''}
                        </div>
                    </div>

                    <div class="grid-2" style="gap: 0.75rem; margin-bottom: 0.6rem;">
                        <div>
                            <label style="font-size: 0.74rem; margin-bottom: 0.2rem;">Texto Editorial en el Slide:</label>
                            <textarea class="slide-text-input" data-idx="${idx}" rows="2" style="font-size: 0.82rem; padding: 0.45rem 0.65rem;">${escapeHtml(slide.text)}</textarea>
                        </div>
                        <div>
                            <label style="font-size: 0.74rem; margin-bottom: 0.2rem;">Plano Cinematográfico / Puesta en Escena (Anti-repetición):</label>
                            <textarea class="slide-shot-input" data-idx="${idx}" rows="2" style="font-size: 0.8rem; padding: 0.45rem 0.65rem;">${escapeHtml(slide.shot_type)}</textarea>
                        </div>
                    </div>

                    <details style="font-size: 0.76rem; color: var(--text-muted);">
                        <summary style="cursor: pointer; user-select: none; color: #94a3b8;">
                            🔍 Ver / Editar prompt completo que se enviará a OpenAI para el Slide ${slide.num}
                        </summary>
                        <textarea class="slide-prompt-input" data-idx="${idx}" rows="5" style="margin-top: 0.4rem; font-size: 0.76rem;">${escapeHtml(slide.prompt)}</textarea>
                    </details>
                </div>

                <!-- Columna Derecha: Vista Previa 4:5 de este Slide y Botón Individual -->
                <div class="slide-preview-column">
                    <div class="slide-mini-viewport" id="mini-viewport-${slide.num}">
                        ${slide.generated_url
                            ? `<img src="${slide.generated_url}" alt="Slide ${slide.num}">`
                            : `<div class="slide-mini-placeholder">
                                 ${assignedPhoto
                                     ? `<img src="${assignedPhoto.thumbnail}" style="width:42px; height:42px; border-radius:50%; object-fit:cover; margin:0 auto 6px auto; border:1px solid var(--primary);"><div style="font-size:0.68rem; color:#cbd5e1;">Ref: ${escapeHtml(assignedPhoto.name)}</div>`
                                     : `<div>🎬 Escena sin foto ref</div>`
                                 }
                               </div>`
                        }
                    </div>
                    <button type="button" class="btn btn-primary btn-gen-single" data-idx="${idx}" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.78rem;">
                        ${slide.generated_url ? `🔄 Regenerar Slide ${slide.num}` : `⚡ Generar Slide ${slide.num}`}
                    </button>
                </div>
            `;

            container.appendChild(card);
        });

        // Listeners para edición en vivo de cada tarjeta
        container.querySelectorAll('.slide-text-input').forEach(el => {
            el.addEventListener('input', (e) => {
                const idx = parseInt(e.target.getAttribute('data-idx'), 10);
                const oldText = slidePlan[idx].text;
                const newText = e.target.value;
                slidePlan[idx].text = newText;
                if (oldText && slidePlan[idx].prompt.includes(oldText)) {
                    slidePlan[idx].prompt = slidePlan[idx].prompt.replace(oldText, newText);
                }
                const promptBox = container.querySelector(`.slide-prompt-input[data-idx="${idx}"]`);
                if (promptBox) promptBox.value = slidePlan[idx].prompt;
            });
        });

        container.querySelectorAll('.slide-shot-input').forEach(el => {
            el.addEventListener('input', (e) => {
                const idx = parseInt(e.target.getAttribute('data-idx'), 10);
                const oldShot = slidePlan[idx].shot_type;
                const newShot = e.target.value;
                slidePlan[idx].shot_type = newShot;
                if (oldShot && slidePlan[idx].prompt.includes(oldShot)) {
                    slidePlan[idx].prompt = slidePlan[idx].prompt.replace(oldShot, newShot);
                }
                const promptBox = container.querySelector(`.slide-prompt-input[data-idx="${idx}"]`);
                if (promptBox) promptBox.value = slidePlan[idx].prompt;
            });
        });

        container.querySelectorAll('.slide-prompt-input').forEach(el => {
            el.addEventListener('input', (e) => {
                const idx = parseInt(e.target.getAttribute('data-idx'), 10);
                slidePlan[idx].prompt = e.target.value;
            });
        });

        container.querySelectorAll('.slide-photo-select').forEach(el => {
            el.addEventListener('change', (e) => {
                const idx = parseInt(e.target.getAttribute('data-idx'), 10);
                const val = e.target.value;
                slidePlan[idx].assigned_photo_id = val;
                slidePlan[idx].include_doctor = (val !== 'none');
                renderSlidePlanCards();
            });
        });

        container.querySelectorAll('.btn-remove-slide').forEach(el => {
            el.addEventListener('click', (e) => {
                const idx = parseInt(e.currentTarget.getAttribute('data-idx'), 10);
                slidePlan.splice(idx, 1);
                slidePlan.forEach((s, i) => { s.num = i + 1; });
                renderSlidePlanCards();
            });
        });

        container.querySelectorAll('.btn-gen-single').forEach(el => {
            el.addEventListener('click', async (e) => {
                const idx = parseInt(e.currentTarget.getAttribute('data-idx'), 10);
                await generateSingleSlide(idx);
            });
        });
    }

    // Re-planificar escaleta con Director de Arte IA (OpenAI GPT-4o)
    async function replanCarouselSequence(useAiDirector = true) {
        const btn = document.getElementById('btn-ai-plan');
        if (btn) {
            btn.disabled = true;
            btn.textContent = '🧠 Planificando secuencia con IA...';
        }
        try {
            const res = await fetch('api.php?action=plan_carousel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    guion: currentRowSummary.guion,
                    tema: currentRowSummary.tema,
                    titulo: currentRowSummary.titulo,
                    dra: currentRowSummary.dra,
                    subtitulos: currentRowSummary.subtitulos,
                    master_prompt: document.getElementById('prompt-editor').value,
                    use_ai_director: useAiDirector
                })
            });
            const data = await res.json();
            if (data.status === 'ok' && data.slide_plan) {
                slidePlan = data.slide_plan;
                renderSlidePlanCards();
                appendChatMessage('assistant', `Escaleta de ${slidePlan.length} slides diseñada (${data.mode === 'ai_director' ? 'Director de Arte IA ' + data.director_model : 'Secuencia Cinematográfica'}). Cada slide tiene un plano diferente asignado.`);
            } else {
                alert('Error planificando secuencia: ' + (data.message || ''));
            }
        } catch (e) {
            alert('Error al planificar secuencia: ' + e);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = '🧠 Re-planificar Secuencia con Director IA';
            }
        }
    }

    document.getElementById('btn-ai-plan').addEventListener('click', () => replanCarouselSequence(true));

    document.getElementById('btn-add-slide').addEventListener('click', () => {
        const newNum = slidePlan.length + 1;
        const dra = currentRowSummary.dra || 'Jovany';
        const shot = `Plano medio cercano cálido de la Dra. ${dra} en el consultorio moderno mirando a cámara.`;
        slidePlan.push({
            num: newNum,
            role: 'Cierre / Extra',
            text: 'Escribinos por WhatsApp para coordinar tu consulta 🦷',
            shot_type: shot,
            include_doctor: true,
            assigned_photo_id: 'rotate',
            prompt: `Pieza editorial individual para carrusel de Instagram (4:5, 1080x1350 px) — Slide ${newNum}.\nTexto en imagen: "Escribinos por WhatsApp para coordinar tu consulta 🦷"\nPlano: ${shot}\nUsar foto de referencia de la Dra. ${dra} únicamente por su rostro real, generando una pose y escena 100% nueva. Paleta JM Odontología (#16345F, #7DD3FC, #FFFFFF).`
        });
        renderSlidePlanCards();
    });

    // --------------------------------------------------------------------------
    // 7. HILO DE CHAT CONVERSACIONAL (ESTILO CHATGPT PARA AJUSTAR SLIDES)
    // --------------------------------------------------------------------------
    function appendChatMessage(role, htmlText) {
        const log = document.getElementById('chat-messages-log');
        if (!log) return;
        const div = document.createElement('div');
        div.className = `chat-msg ${role}`;
        div.innerHTML = role === 'user'
            ? `<strong>Vos:</strong> ${escapeHtml(htmlText)}`
            : `<strong>Director de Arte IA:</strong> ${htmlText}`;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
    }

    async function handleChatSend() {
        const inputEl = document.getElementById('chat-user-input');
        const targetSlide = document.getElementById('chat-target-slide').value;
        const autoGen = document.getElementById('chk-chat-autogen').checked;
        const msg = inputEl.value.trim();

        if (!msg) return;
        if (slidePlan.length === 0) {
            alert('Primero carga una fila del Sheet o prepara un guion.');
            return;
        }

        inputEl.value = '';
        appendChatMessage('user', msg);

        const btn = document.getElementById('btn-chat-send');
        btn.disabled = true;
        btn.textContent = '⏳ Pensando...';

        try {
            const res = await fetch('api.php?action=chat_refine', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: msg,
                    target_slide: targetSlide,
                    slide_plan: slidePlan,
                    dra: currentRowSummary.dra,
                    tema: currentRowSummary.tema
                })
            });
            const data = await res.json();
            if (data.status === 'ok' && data.slide_plan) {
                // Conservar las URLs de imágenes ya generadas en los slides que no cambiaron
                const prevUrls = {};
                slidePlan.forEach(s => { if (s.generated_url) prevUrls[s.num] = s.generated_url; });

                slidePlan = data.slide_plan.map((s, idx) => ({
                    ...s,
                    num: idx + 1,
                    generated_url: prevUrls[idx + 1] || null
                }));

                renderSlidePlanCards();
                appendChatMessage('assistant', escapeHtml(data.reply || 'Escaleta actualizada.'));

                // Si está marcado "Auto-regenerar imagen" y se modificó 1 o pocos slides, regenerarlos en vivo
                const modSlides = data.modified_slides || [];
                if (autoGen && modSlides.length > 0 && modSlides.length <= 3) {
                    for (const sNum of modSlides) {
                        const sIdx = slidePlan.findIndex(x => x.num === parseInt(sNum, 10));
                        if (sIdx !== -1) {
                            await generateSingleSlide(sIdx);
                        }
                    }
                }
            } else {
                appendChatMessage('assistant', '⚠️ Error: ' + escapeHtml(data.message || 'No se pudo aplicar el cambio.'));
            }
        } catch (e) {
            appendChatMessage('assistant', '⚠️ Error de red: ' + escapeHtml(String(e)));
        } finally {
            btn.disabled = false;
            btn.textContent = '✨ Aplicar Ajuste';
        }
    }

    document.getElementById('btn-chat-send').addEventListener('click', handleChatSend);
    document.getElementById('chat-user-input').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleChatSend();
        }
    });

    // --------------------------------------------------------------------------
    // 8. PRODUCCIÓN SECUENCIAL EN VIVO Y GENERACIÓN POR SLIDE (SUNBURST)
    // --------------------------------------------------------------------------
    async function ensureCarouselSessionFolder() {
        if (activeCarouselFolder) return activeCarouselFolder;
        const res = await fetch('api.php?action=init_carousel_session', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                row_num: currentRowSummary.n || 'libre',
                master_prompt: document.getElementById('prompt-editor').value,
                row_data: currentRowData || currentRowSummary,
                slide_plan: slidePlan
            })
        });
        const data = await res.json();
        if (data.status === 'ok' && data.folder) {
            activeCarouselFolder = data.folder;
            return activeCarouselFolder;
        }
        throw new Error('No se pudo inicializar carpeta de sesión');
    }

    async function generateSingleSlide(slideIdx) {
        const slide = slidePlan[slideIdx];
        if (!slide) return false;

        const statusPanel = document.getElementById('panel-production-status');
        const consoleBox = document.getElementById('console-logs');
        statusPanel.style.display = 'block';

        const cardEl = document.getElementById(`slide-plan-card-${slide.num}`);
        const miniVp = document.getElementById(`mini-viewport-${slide.num}`);
        const singleBtn = cardEl ? cardEl.querySelector('.btn-gen-single') : null;

        if (cardEl) {
            cardEl.classList.remove('done');
            cardEl.classList.add('generating');
        }
        if (singleBtn) {
            singleBtn.disabled = true;
            singleBtn.textContent = `⏳ Generando Slide ${slide.num}...`;
        }
        if (miniVp) {
            miniVp.innerHTML = `<div class="slide-mini-placeholder" style="color: var(--primary);">🎨 Renderizando en OpenAI...</div>`;
        }

        try {
            const folder = await ensureCarouselSessionFolder();
            const selectedModel = document.getElementById('quick-model-select').value;
            const stampLogo = document.getElementById('chk-stamp-logo').checked;
            const doctorPhoto = getDoctorPhotoForSlide(slideIdx, slide);

            const timeNow = new Date().toLocaleTimeString();
            consoleBox.textContent += `[${timeNow}] ▶ Enviando Slide ${slide.num}/${slidePlan.length} (${slide.role}) a OpenAI [${selectedModel}]...\n`;
            consoleBox.textContent += `   • Foto Dra. asignada: ${doctorPhoto ? doctorPhoto.name : 'Ninguna (Escena sin doctora)'} | Logo: ${selectedLogo ? selectedLogo.name : 'Ninguno'}\n`;
            consoleBox.scrollTop = consoleBox.scrollHeight;

            const res = await fetch('api.php?action=generate_slide', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    folder,
                    slide_num: slide.num,
                    total_slides: slidePlan.length,
                    prompt: slide.prompt,
                    model: selectedModel,
                    include_doctor: !!doctorPhoto,
                    doctor_photo: doctorPhoto,
                    logo_asset: selectedLogo,
                    brand_manual: brandManual,
                    stamp_logo: stampLogo
                })
            });
            const data = await res.json();

            if (data.status === 'ok' && data.image_url) {
                slidePlan[slideIdx].generated_url = data.image_url;
                slidePlan[slideIdx].clean_url = data.clean_image_url;

                consoleBox.textContent += `[${new Date().toLocaleTimeString()}] ✓ Slide ${slide.num} completado vía ${data.endpoint_used} (${data.model_used}).\n`;
                consoleBox.scrollTop = consoleBox.scrollHeight;

                if (cardEl) {
                    cardEl.classList.remove('generating');
                    cardEl.classList.add('done');
                }
                if (miniVp) {
                    miniVp.innerHTML = `<img src="${data.image_url}" alt="Slide ${slide.num}">`;
                }
                if (singleBtn) {
                    singleBtn.disabled = false;
                    singleBtn.textContent = `🔄 Regenerar Slide ${slide.num}`;
                }

                renderLiveCarouselPreview();
                return true;
            } else {
                consoleBox.textContent += `[${new Date().toLocaleTimeString()}] ❌ Error en Slide ${slide.num}: ${data.message}\n`;
                consoleBox.scrollTop = consoleBox.scrollHeight;
                if (miniVp) {
                    miniVp.innerHTML = `<div class="slide-mini-placeholder" style="color: var(--danger);">❌ Error al generar</div>`;
                }
                return false;
            }
        } catch (err) {
            consoleBox.textContent += `[${new Date().toLocaleTimeString()}] ❌ Error de red en Slide ${slide.num}: ${err}\n`;
            return false;
        } finally {
            if (cardEl) cardEl.classList.remove('generating');
            if (singleBtn) {
                singleBtn.disabled = false;
                if (!slidePlan[slideIdx].generated_url) {
                    singleBtn.textContent = `⚡ Generar Slide ${slide.num}`;
                }
            }
        }
    }

    // Botón: Producir Carrusel Completo en Secuencia (Slide 1 -> N)
    document.getElementById('btn-produce').addEventListener('click', async () => {
        if (slidePlan.length === 0) {
            alert('Primero carga una fila del Sheet o prepara un guion.');
            return;
        }
        if (isProducingSequence) return;

        isProducingSequence = true;
        stopSequenceRequested = false;

        const btnProduce = document.getElementById('btn-produce');
        const btnStop = document.getElementById('btn-stop-produce');
        const statusPanel = document.getElementById('panel-production-status');
        const consoleBox = document.getElementById('console-logs');
        const progressFill = document.getElementById('production-progress-fill');
        const progressBadge = document.getElementById('production-progress-badge');

        btnProduce.disabled = true;
        btnProduce.textContent = '⏳ Produciendo Secuencia en Vivo...';
        btnStop.style.display = 'inline-flex';
        statusPanel.style.display = 'block';
        statusPanel.scrollIntoView({ behavior: 'smooth' });

        consoleBox.textContent = `[${new Date().toLocaleTimeString()}] 🚀 Iniciando producción secuencial de ${slidePlan.length} slides...\n`;
        progressFill.style.width = '5%';
        progressBadge.textContent = `0 / ${slidePlan.length} completados`;

        let completed = 0;
        for (let i = 0; i < slidePlan.length; i++) {
            if (stopSequenceRequested) {
                consoleBox.textContent += `[${new Date().toLocaleTimeString()}] ⏹ Secuencia detenida por el usuario.\n`;
                break;
            }
            progressBadge.textContent = `Generando Slide ${i + 1} de ${slidePlan.length}...`;
            const ok = await generateSingleSlide(i);
            if (ok) completed++;
            const pct = Math.round(((i + 1) / slidePlan.length) * 100);
            progressFill.style.width = `${pct}%`;
            progressBadge.textContent = `${completed} / ${slidePlan.length} slides listos`;
        }

        consoleBox.textContent += `[${new Date().toLocaleTimeString()}] 🎉 Proceso finalizado: ${completed} de ${slidePlan.length} slides generados.\n`;
        consoleBox.scrollTop = consoleBox.scrollHeight;

        isProducingSequence = false;
        btnProduce.disabled = false;
        btnProduce.textContent = '🚀 Producir Carrusel Completo en Secuencia';
        btnStop.style.display = 'none';
    });

    document.getElementById('btn-stop-produce').addEventListener('click', () => {
        stopSequenceRequested = true;
    });

    function renderLiveCarouselPreview() {
        const galleryContainer = document.getElementById('slides-gallery');
        if (!galleryContainer) return;

        const readySlides = slidePlan.filter(s => !!s.generated_url);
        if (readySlides.length === 0) return;

        const slideUrls = readySlides.map(s => s.generated_url);
        const previewItem = {
            folder: activeCarouselFolder || 'sesion_actual',
            slides: slideUrls,
            total_slides: slideUrls.length,
            row_data: currentRowData || {
                'N°': currentRowSummary.n,
                'TEMA': currentRowSummary.tema,
                'TÍTULO / PORTADA': currentRowSummary.titulo,
                'DRA': currentRowSummary.dra
            },
            created_at: 'En vivo'
        };

        galleryContainer.innerHTML = `
            <div style="width: 100%; margin-bottom: 2rem;">
                <div style="text-align: center; margin-bottom: 1rem; color: var(--primary); font-weight: 700; font-size: 1.05rem;">
                    📱 Vista Previa Interactiva en Feed de Instagram (${readySlides.length} de ${slidePlan.length} slides listos)
                </div>
                ${renderInstagramCard(previewItem, 'prod_preview')}
            </div>
            <div style="width: 100%;">
                <div style="margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">
                    🔲 Slides Generados (${readySlides.length} imágenes en 4:5 — 1080×1350 px)
                </div>
                <div class="slides-grid">
                    ${readySlides.map((s) => `
                        <div class="slide-card">
                            <div class="slide-img-wrapper">
                                <img src="${s.generated_url}" alt="Slide ${s.num}">
                            </div>
                            <div class="slide-info">
                                <span class="slide-badge">Slide ${s.num} (${escapeHtml(s.role)})</span>
                                <a href="${s.clean_url || s.generated_url}" download="slide_${String(s.num).padStart(2, '0')}.png" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">⬇ Descargar</a>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        setTimeout(() => {
            const vp = document.getElementById('viewport-prod_preview');
            if (vp) attachSwipeListeners(vp, 'prod_preview', previewItem.slides);
        }, 50);
    }

    // --------------------------------------------------------------------------
    // 9. EXPLORADOR DEL GOOGLE SHEET EN PESTAÑA 2
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
                        <td><strong>${escapeHtml(r.n)}</strong></td>
                        <td>${escapeHtml(r.tema || '-')}</td>
                        <td>${escapeHtml(r.titulo || '-')}</td>
                        <td><span class="pill" style="color:#38bdf8;">Dra. ${escapeHtml(r.dra || 'Jovany')}</span></td>
                        <td><span class="pill" style="font-size: 0.75rem;">${r.slides_count} slides</span></td>
                        <td>
                            <button class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;" onclick="selectRowFromTable('${escapeHtml(r.n)}')">
                                🎬 Abrir en Estudio
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--danger); padding: 2rem;">Error: ${escapeHtml(data.message)}</td></tr>`;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--danger); padding: 2rem;">Error cargando filas: ${escapeHtml(String(e))}</td></tr>`;
        }
    };

    window.selectRowFromTable = function(rowNum) {
        if (btnModeSheet) btnModeSheet.click();
        document.getElementById('input-row-num').value = rowNum;
        document.querySelector('[data-tab="tab-generator"]').click();
        document.getElementById('btn-load-prompt').click();
    };

    // --------------------------------------------------------------------------
    // 10. GALERÍA DE HISTORIAL — INSTAGRAM CAROUSEL & GRID
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
                container.innerHTML = `<div style="color: var(--danger); text-align: center; padding: 3rem;">Error: ${escapeHtml(data.message)}</div>`;
            }
        } catch (e) {
            container.innerHTML = `<div style="color: var(--danger); text-align: center; padding: 3rem;">Error cargando historial: ${escapeHtml(String(e))}</div>`;
        }
    }

    window.setGalleryViewMode = function(mode) {
        galleryViewMode = mode;
        const btnIg = document.getElementById('view-mode-ig');
        const btnGrid = document.getElementById('view-mode-grid');
        if (btnIg && btnGrid) {
            btnIg.classList.toggle('active', mode === 'ig');
            btnGrid.classList.toggle('active', mode === 'grid');
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
                    <div style="font-size: 0.85rem;">Ve a la pestaña <strong>🎬 Estudio de Producción</strong> para crear tu primer carrusel.</div>
                </div>
            `;
            return;
        }

        if (galleryViewMode === 'ig') {
            let html = '<div class="ig-feed-container">';
            currentGalleryItems.forEach((item, idx) => {
                html += renderInstagramCard(item, idx);
            });
            html += '</div>';
            container.innerHTML = html;

            currentGalleryItems.forEach((item, idx) => {
                const vp = document.getElementById(`viewport-${idx}`);
                if (vp) attachSwipeListeners(vp, idx, item.slides);
            });
        } else {
            let html = '';
            currentGalleryItems.forEach((item) => {
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
                            <span>📁 Fila ${rowN ? `N° ${escapeHtml(rowN)}: ` : ''}${escapeHtml(topic)}</span>
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
                <div class="ig-post-header">
                    <div class="ig-profile-info">
                        <div class="ig-avatar-ring">
                            <div class="ig-avatar-inner">JM</div>
                        </div>
                        <div class="ig-username-box">
                            <div class="ig-username-line">
                                jm.odontologiaintegral
                                <span class="ig-badge-verified">✓</span>
                            </div>
                            <div class="ig-subtitle-line">
                                ${topic ? escapeHtml(topic) : 'JM Odontología Integral'} ${rowNum ? `• Fila ${escapeHtml(rowNum)}` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="ig-more-options">•••</div>
                </div>

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

                <div class="ig-actions-bar">
                    <div class="ig-actions-left">
                        <button class="ig-action-btn" id="like-${postIndex}" onclick="toggleLike('${postIndex}')">🤍</button>
                        <button class="ig-action-btn">💬</button>
                        <button class="ig-action-btn">↗️</button>
                    </div>
                    <div class="ig-dots-box" id="dots-${postIndex}">
                        ${dotsHtml}
                    </div>
                    <div>
                        <button class="ig-action-btn">🔖</button>
                    </div>
                </div>

                <div class="ig-caption-box">
                    <div class="ig-likes-label" id="likes-count-${postIndex}">1.248 Me gusta</div>
                    <div class="ig-caption-body">
                        <strong>jm.odontologiaintegral</strong>
                        <span>${escapeHtml(title)}</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #38bdf8; margin-bottom: 0.5rem;">
                        #JMOdontologia #SaludBucal #Dra${escapeHtml(dra.replace(/\s+/g, ''))} #OdontologiaIntegral
                    </div>
                    <div class="ig-time-meta">
                        Producido: ${escapeHtml(dateStr)} • ${escapeHtml(item.folder)}
                    </div>
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

    window.moveSlide = function(postIndex, direction) {
        const card = document.getElementById(`ig-post-${postIndex}`);
        if (!card) return;
        const total = parseInt(card.getAttribute('data-total'), 10);
        let current = parseInt(card.getAttribute('data-current'), 10);
        let next = Math.max(0, Math.min(total - 1, current + direction));
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

        const track = document.getElementById(`track-${postIndex}`);
        if (track) track.style.transform = `translateX(-${newIdx * 100}%)`;

        const counter = document.getElementById(`counter-${postIndex}`);
        if (counter) counter.textContent = `${newIdx + 1}/${total}`;

        const dotsBox = document.getElementById(`dots-${postIndex}`);
        if (dotsBox) {
            dotsBox.querySelectorAll('.ig-dot').forEach((d, i) => {
                d.classList.toggle('active', i === newIdx);
            });
        }

        const prevBtn = document.getElementById(`prev-${postIndex}`);
        const nextBtn = document.getElementById(`next-${postIndex}`);
        if (prevBtn) prevBtn.classList.toggle('hidden', newIdx === 0);
        if (nextBtn) nextBtn.classList.toggle('hidden', newIdx === total - 1);

        const dlBtn = document.getElementById(`btn-download-slide-${postIndex}`);
        if (dlBtn && track) {
            const imgs = track.querySelectorAll('.ig-slide img');
            if (imgs[newIdx]) dlBtn.href = imgs[newIdx].getAttribute('src');
        }
    }

    function attachSwipeListeners(viewportEl, postIndex) {
        let startX = 0;
        let currentX = 0;
        let isTouching = false;
        let isMouseDown = false;

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
                moveSlide(postIndex, diffX < 0 ? 1 : -1);
            }
            isTouching = false;
            startX = 0;
            currentX = 0;
        });

        viewportEl.addEventListener('mousedown', (e) => {
            isMouseDown = true;
            startX = e.clientX;
        });

        viewportEl.addEventListener('mouseup', (e) => {
            if (!isMouseDown) return;
            const diffX = e.clientX - startX;
            if (Math.abs(diffX) > 45) {
                moveSlide(postIndex, diffX < 0 ? 1 : -1);
            }
            isMouseDown = false;
        });

        viewportEl.addEventListener('mouseleave', () => {
            isMouseDown = false;
        });

        viewportEl.addEventListener('dblclick', () => {
            toggleLike(postIndex);
        });
    }

    window.toggleLike = function(postIndex) {
        const btn = document.getElementById(`like-${postIndex}`);
        const count = document.getElementById(`likes-count-${postIndex}`);
        if (!btn) return;
        const liked = btn.classList.toggle('liked');
        btn.textContent = liked ? '❤️' : '🤍';
        if (count) count.textContent = liked ? '1.249 Me gusta' : '1.248 Me gusta';
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

    window.loadHistory = loadHistory;

    // Inicializar aplicación
    loadConfig();
    loadSheetRowsToSelect();
    loadTemplates();
});
