document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // ESTADO GLOBAL DE LA APLICACIÓN (ESTILO CHATGPT EN 2 PASOS)
    // =========================================================================
    const state = {
        currentPostId: null,
        networks: ['ig', 'fb', 'tiktok', 'linkedin'],
        placement: 'post', // 'post' (4:5 automático) | 'story' (9:16 automático)
        brandAsset: null,  // Apartado 1: Imagen de paleta de colores / manual de marca
        brandText: '',     // Apartado 1: Definición textual de marca / colores
        logoAsset: null,   // Apartado 2: Logo
        subjectAssets: [], // Apartado 3: Personajes, Productos y Fondos (inicia limpio en cada nuevo post)
        lastPlan: null,
        selectedImageModel: 'gpt-image-2.5-sunburst-2026-09-08',
        selectedTextModel: 'gpt-4o',
        stampLogoOverlay: false,
        isBusy: false
    };

    const NETWORK_META = {
        ig: { label: '📸 Instagram', short: 'IG' },
        fb: { label: '📘 Facebook', short: 'FB' },
        tiktok: { label: '🎵 TikTok', short: 'TikTok' },
        linkedin: { label: '💼 LinkedIn', short: 'LinkedIn' }
    };

    // =========================================================================
    // REFERENCIAS DOM
    // =========================================================================
    const sidebar = document.getElementById('sidebar');
    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
    const btnNewPost = document.getElementById('btn-new-post');
    const postsHistoryList = document.getElementById('posts-history-list');
    const btnClearAllPosts = document.getElementById('btn-clear-all-posts');
    const currentPostTitle = document.getElementById('current-post-title');
    const btnDeleteActivePost = document.getElementById('btn-delete-active-post');
    const lblActiveModel = document.getElementById('lbl-active-model');

    const chatViewport = document.getElementById('chat-viewport');
    const welcomeScreen = document.getElementById('welcome-screen');
    const threadContainer = document.getElementById('thread-container');

    // Controles del Composer
    const btnNetAll = document.getElementById('btn-net-all');
    const netButtons = Array.from(document.querySelectorAll('.net-btn'));
    const btnPlacePost = document.getElementById('btn-place-post');
    const btnPlaceStory = document.getElementById('btn-place-story');
    const chkAutoStep2 = document.getElementById('chk-auto-step2');

    // Apartado 1: Marca / Paleta
    const inputUploadBrand = document.getElementById('input-upload-brand');
    const inputBrandText = document.getElementById('input-brand-text');
    const activeBrandPreview = document.getElementById('active-brand-preview');

    // Apartado 2: Logo
    const inputUploadLogo = document.getElementById('input-upload-logo');
    const activeLogoPreview = document.getElementById('active-logo-preview');

    // Apartado 3: Personajes, Productos y Fondos
    const inputUploadSubject = document.getElementById('input-upload-subject');
    const activeSubjectsPreview = document.getElementById('active-subjects-preview');

    // Barra de Variables de Fotos en el Composer
    const composerPhotoVarsBar = document.getElementById('composer-photo-vars-bar');
    const composerPhotoVarsList = document.getElementById('composer-photo-vars-list');

    const promptInput = document.getElementById('prompt-input');
    const btnSendPrompt = document.getElementById('btn-send-prompt');

    // Modal Configuración
    const btnOpenConfig = document.getElementById('btn-open-config');
    const btnCloseConfig = document.getElementById('btn-close-config');
    const modalConfig = document.getElementById('modal-config');
    const alertConfig = document.getElementById('alert-config');
    const inputApiKey = document.getElementById('input-api-key');
    const selectTextModel = document.getElementById('select-text-model');
    const selectModel = document.getElementById('select-model');
    const btnLoadModels = document.getElementById('btn-load-models');
    const inputPromptDirector = document.getElementById('input-prompt-director');
    const inputPromptDesigner = document.getElementById('input-prompt-designer');
    const btnRestorePrompts = document.getElementById('btn-restore-prompts');
    const chkStampLogo = document.getElementById('chk-stamp-logo');
    const btnSaveConfig = document.getElementById('btn-save-config');

    // =========================================================================
    // UTILIDADES
    // =========================================================================
    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function scrollToBottom() {
        if (!chatViewport) return;
        setTimeout(() => {
            chatViewport.scrollTop = chatViewport.scrollHeight;
        }, 60);
    }

    function showConfigAlert(msg, type = 'success') {
        if (!alertConfig) return;
        alertConfig.textContent = msg;
        alertConfig.className = 'alert ' + (type === 'error' ? 'alert-error' : 'alert-success');
        alertConfig.style.display = 'block';
    }

    /**
     * Inserta el nombre de una foto como variable (ej: "anthony.jpg") exactamente donde está el cursor
     */
    function insertVariableAtCursor(textareaEl, varName) {
        if (!textareaEl || !varName) return;
        const start = textareaEl.selectionStart ?? textareaEl.value.length;
        const end = textareaEl.selectionEnd ?? textareaEl.value.length;
        const val = textareaEl.value;
        const before = val.substring(0, start);
        const after = val.substring(end);
        const needSpaceBefore = before.length > 0 && !/\s$/.test(before);
        const needSpaceAfter = after.length > 0 && !/^\s/.test(after);
        const insertStr = (needSpaceBefore ? ' ' : '') + varName + (needSpaceAfter ? ' ' : '');

        textareaEl.value = before + insertStr + after;
        const newPos = before.length + insertStr.length;
        textareaEl.focus();
        textareaEl.setSelectionRange(newPos, newPos);
    }

    /**
     * Devuelve las fotos subidas en el apartado 3 (Personajes, Productos y Fondos) para usar como variables
     */
    function getAvailablePhotoVariables() {
        const list = [];
        (state.subjectAssets || []).forEach(sa => {
            if (sa && sa.name) {
                list.push({ name: sa.name, preview_url: sa.preview_url, role: 'Foto' });
            }
        });
        return list;
    }

    function renderComposerPhotoVariablesBar() {
        if (!composerPhotoVarsBar || !composerPhotoVarsList) return;
        const vars = getAvailablePhotoVariables();
        if (vars.length === 0) {
            composerPhotoVarsBar.style.display = 'none';
            composerPhotoVarsList.innerHTML = '';
            return;
        }
        composerPhotoVarsBar.style.display = 'flex';
        composerPhotoVarsList.innerHTML = vars.map((v, idx) => `
            <button type="button" class="photo-var-btn" data-var-idx="${idx}" title="Clic para insertar '${escapeHtml(v.name)}' donde está el cursor">
                <img src="${escapeHtml(v.preview_url)}" alt="">
                <span>➕ ${escapeHtml(v.name)}</span>
            </button>
        `).join('');

        composerPhotoVarsList.querySelectorAll('.photo-var-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-var-idx'), 10);
                const item = vars[idx];
                if (item && promptInput) {
                    insertVariableAtCursor(promptInput, item.name);
                }
            });
        });

        // También actualizar las barras de variables de cualquier tarjeta Paso 1 abierta en el hilo
        if (threadContainer) {
            threadContainer.querySelectorAll('.step1-photo-vars-container').forEach(container => {
                renderStep1PhotoVariablesInto(container);
            });
        }
    }

    function renderStep1PhotoVariablesInto(containerEl) {
        if (!containerEl) return;
        const card = containerEl.closest('.step1-card');
        const targetTextarea = card ? card.querySelector('.textarea-sunburst-prompt') : null;
        const vars = getAvailablePhotoVariables();
        if (vars.length === 0 || !targetTextarea) {
            containerEl.style.display = 'none';
            containerEl.innerHTML = '';
            return;
        }
        containerEl.style.display = 'flex';
        containerEl.innerHTML = `
            <span class="photo-vars-label">📌 Variables de fotos (clic para insertar en la narrativa):</span>
            <div class="photo-vars-list">
                ${vars.map((v, idx) => `
                    <button type="button" class="photo-var-btn" data-var-idx="${idx}" title="Clic para insertar '${escapeHtml(v.name)}' en la descripción de la imagen">
                        <img src="${escapeHtml(v.preview_url)}" alt="">
                        <span>➕ ${escapeHtml(v.name)}</span>
                    </button>
                `).join('')}
            </div>
        `;
        containerEl.querySelectorAll('.photo-var-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-var-idx'), 10);
                const item = vars[idx];
                if (item && targetTextarea) {
                    insertVariableAtCursor(targetTextarea, item.name);
                }
            });
        });
    }

    // =========================================================================
    // 1. SIDEBAR Y NAVEGACIÓN
    // =========================================================================
    if (btnToggleSidebar && sidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }

    if (btnNewPost) {
        btnNewPost.addEventListener('click', () => {
            startNewPostSession();
        });
    }

    function syncActiveThreadButtons() {
        if (btnDeleteActivePost) {
            btnDeleteActivePost.style.display = state.currentPostId ? 'inline-flex' : 'none';
        }
    }

    function startNewPostSession() {
        state.currentPostId = null;
        state.lastPlan = null;
        // Limpiamos personajes/productos/fondos previos para evitar sesgos entre posteos
        state.subjectAssets = [];
        renderSubjectPreviews();

        if (currentPostTitle) currentPostTitle.textContent = 'Nueva Publicación';
        syncActiveThreadButtons();
        if (threadContainer) {
            threadContainer.innerHTML = '';
            threadContainer.style.display = 'none';
        }
        if (welcomeScreen) {
            welcomeScreen.style.display = 'block';
            welcomeScreen.classList.remove('in-chat');
        }
        if (chatViewport) {
            chatViewport.scrollTop = 0;
        }
        if (promptInput) {
            promptInput.value = '';
            promptInput.placeholder = "Escribí tu idea acá (podés mencionar o hacer clic en tus fotos, ej: 'la persona de xxxx.jpg saluda a yyyy.jpg en el fondo zzzz.jpg')...";
            promptInput.focus();
        }
        highlightActiveHistoryItem();
    }

    // =========================================================================
    // 2. SELECCIÓN DE REDES SOCIALES Y UBICACIÓN (POST vs HISTORIA)
    // =========================================================================
    function syncNetworkButtonsUI() {
        const allNets = ['ig', 'fb', 'tiktok', 'linkedin'];
        const isAllSelected = allNets.every(n => state.networks.includes(n));

        if (btnNetAll) {
            btnNetAll.classList.toggle('active', isAllSelected);
        }
        netButtons.forEach(btn => {
            const net = btn.getAttribute('data-net');
            btn.classList.toggle('active', state.networks.includes(net));
        });
    }

    if (btnNetAll) {
        btnNetAll.addEventListener('click', () => {
            state.networks = ['ig', 'fb', 'tiktok', 'linkedin'];
            syncNetworkButtonsUI();
        });
    }

    netButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const net = btn.getAttribute('data-net');
            if (!net) return;

            const allSelected = state.networks.length === 4;
            if (allSelected) {
                state.networks = [net];
            } else if (state.networks.includes(net)) {
                if (state.networks.length > 1) {
                    state.networks = state.networks.filter(n => n !== net);
                }
            } else {
                state.networks.push(net);
            }
            syncNetworkButtonsUI();
        });
    });

    function setPlacement(placement) {
        state.placement = placement === 'story' ? 'story' : 'post';
        if (btnPlacePost) btnPlacePost.classList.toggle('active', state.placement === 'post');
        if (btnPlaceStory) btnPlaceStory.classList.toggle('active', state.placement === 'story');
    }

    if (btnPlacePost) {
        btnPlacePost.addEventListener('click', () => setPlacement('post'));
    }
    if (btnPlaceStory) {
        btnPlaceStory.addEventListener('click', () => setPlacement('story'));
    }

    // =========================================================================
    // 3. LOS 3 APARTADOS: 1. MARCA/PALETA · 2. LOGO · 3. PERSONAJES/PRODUCTOS/FONDOS
    // =========================================================================
    function renderBrandPreview() {
        if (!activeBrandPreview) return;
        if (!state.brandAsset) {
            activeBrandPreview.innerHTML = `<span style="font-size:0.71rem; color:#64748b;">Sin paleta / marca</span>`;
            renderComposerPhotoVariablesBar();
            return;
        }
        activeBrandPreview.innerHTML = `
            <div class="attached-chip" title="Imagen de Marca/Paleta: ${escapeHtml(state.brandAsset.name)}">
                <img src="${escapeHtml(state.brandAsset.preview_url)}?t=${Date.now()}" alt="Marca">
                <span>${escapeHtml(state.brandAsset.name)}</span>
                <button type="button" id="btn-clear-brand" title="Quitar imagen de paleta">✕</button>
            </div>
        `;
        const btnClearBrand = document.getElementById('btn-clear-brand');
        if (btnClearBrand) {
            btnClearBrand.addEventListener('click', async () => {
                state.brandAsset = null;
                renderBrandPreview();
                try {
                    await fetch('api.php?action=save_config', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ default_brand_asset: null })
                    });
                } catch (e) {}
            });
        }
        renderComposerPhotoVariablesBar();
    }

    if (inputBrandText) {
        inputBrandText.addEventListener('change', async () => {
            state.brandText = inputBrandText.value.trim();
            try {
                await fetch('api.php?action=save_config', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ default_brand_text: state.brandText })
                });
            } catch (e) {}
        });
        inputBrandText.addEventListener('input', () => {
            state.brandText = inputBrandText.value.trim();
        });
    }

    function renderLogoPreview() {
        if (!activeLogoPreview) return;
        if (!state.logoAsset) {
            activeLogoPreview.innerHTML = `<span style="font-size:0.71rem; color:#64748b;">Sin logo</span>`;
            renderComposerPhotoVariablesBar();
            return;
        }
        activeLogoPreview.innerHTML = `
            <div class="attached-chip" title="Logo activo: ${escapeHtml(state.logoAsset.name)}">
                <img src="${escapeHtml(state.logoAsset.preview_url)}?t=${Date.now()}" alt="Logo">
                <span>${escapeHtml(state.logoAsset.name)}</span>
                <button type="button" id="btn-clear-logo" title="Quitar logo">✕</button>
            </div>
        `;
        const btnClear = document.getElementById('btn-clear-logo');
        if (btnClear) {
            btnClear.addEventListener('click', async () => {
                state.logoAsset = null;
                renderLogoPreview();
                try {
                    await fetch('api.php?action=save_config', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ default_logo: null })
                    });
                } catch (e) {}
            });
        }
        renderComposerPhotoVariablesBar();
    }

    function renderSubjectPreviews() {
        if (!activeSubjectsPreview) return;
        if (!state.subjectAssets.length) {
            activeSubjectsPreview.innerHTML = `<span style="font-size:0.71rem; color:#64748b;">Sin fotos cargadas</span>`;
            renderComposerPhotoVariablesBar();
            return;
        }
        activeSubjectsPreview.innerHTML = state.subjectAssets.map((item, idx) => `
            <div class="attached-chip" title="Foto adjunta: ${escapeHtml(item.name)}">
                <img src="${escapeHtml(item.preview_url)}?t=${Date.now()}" alt="Ref">
                <span>${escapeHtml(item.name)}</span>
                <button type="button" class="btn-remove-subject" data-idx="${idx}" title="Quitar">✕</button>
            </div>
        `).join('');

        activeSubjectsPreview.querySelectorAll('.btn-remove-subject').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-idx'), 10);
                state.subjectAssets.splice(idx, 1);
                renderSubjectPreviews();
            });
        });
        renderComposerPhotoVariablesBar();
    }

    /**
     * Optimiza cualquier foto grande (ej. cámara profesional de 10-25 MB) directamente en el navegador
     * a máx 1600px antes de enviarla, evitando cualquier límite upload_max_filesize de PHP.
     */
    function fileToOptimizedDataUrl(file, maxDim = 1600, keepTransparency = false) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                const img = new Image();
                img.onload = () => {
                    try {
                        let w = img.naturalWidth || img.width;
                        let h = img.naturalHeight || img.height;
                        if (!w || !h) {
                            resolve(null);
                            return;
                        }
                        if (w > maxDim || h > maxDim) {
                            const scale = Math.min(maxDim / w, maxDim / h);
                            w = Math.max(1, Math.round(w * scale));
                            h = Math.max(1, Math.round(h * scale));
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);

                        const isPng = keepTransparency || (file.type && file.type.includes('png'));
                        const outMime = isPng ? 'image/png' : 'image/jpeg';
                        const dataUrl = canvas.toDataURL(outMime, 0.92);
                        resolve(dataUrl);
                    } catch (e) {
                        resolve(null);
                    }
                };
                img.onerror = () => resolve(null);
                img.src = ev.target.result;
            };
            reader.onerror = () => resolve(null);
            reader.readAsDataURL(file);
        });
    }

    async function uploadSingleAsset(file, assetType, saveDefault = false) {
        const optimizedDataUrl = await fileToOptimizedDataUrl(file, 1600, assetType === 'logo');

        if (optimizedDataUrl) {
            const res = await fetch('api.php?action=upload_asset', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: assetType,
                    save_default: saveDefault ? 1 : 0,
                    filename: file.name,
                    data_url: optimizedDataUrl
                })
            });
            return await res.json();
        }

        const fd = new FormData();
        fd.append('type', assetType);
        if (saveDefault) fd.append('save_default', '1');
        fd.append('file', file);
        const res = await fetch('api.php?action=upload_asset', { method: 'POST', body: fd });
        return await res.json();
    }

    if (inputUploadBrand) {
        inputUploadBrand.addEventListener('change', async () => {
            if (!inputUploadBrand.files || !inputUploadBrand.files[0]) return;
            const file = inputUploadBrand.files[0];

            activeBrandPreview.innerHTML = `<span style="font-size:0.72rem; color:#0052E0; font-weight:600;">⏳ Subiendo paleta...</span>`;
            try {
                const data = await uploadSingleAsset(file, 'brand', true);
                if (data.status === 'ok' && data.asset) {
                    state.brandAsset = data.asset;
                } else {
                    alert(data.message || 'Error al subir imagen de marca/paleta');
                }
            } catch (err) {
                alert('Error de red al subir paleta: ' + err.message);
            }
            inputUploadBrand.value = '';
            renderBrandPreview();
        });
    }

    if (inputUploadLogo) {
        inputUploadLogo.addEventListener('change', async () => {
            if (!inputUploadLogo.files || !inputUploadLogo.files[0]) return;
            const file = inputUploadLogo.files[0];

            activeLogoPreview.innerHTML = `<span style="font-size:0.72rem; color:#0052E0; font-weight:600;">⏳ Subiendo logo...</span>`;
            try {
                const data = await uploadSingleAsset(file, 'logo', true);
                if (data.status === 'ok' && data.asset) {
                    state.logoAsset = data.asset;
                } else {
                    alert(data.message || 'Error al subir el logo');
                }
            } catch (err) {
                alert('Error de red al subir el logo: ' + err.message);
            }
            inputUploadLogo.value = '';
            renderLogoPreview();
        });
    }

    if (inputUploadSubject) {
        inputUploadSubject.addEventListener('change', async () => {
            if (!inputUploadSubject.files || !inputUploadSubject.files.length) return;
            const files = Array.from(inputUploadSubject.files);

            activeSubjectsPreview.innerHTML = `<span style="font-size:0.72rem; color:#0052E0; font-weight:600;">⏳ Procesando y subiendo foto(s)...</span>`;
            for (const file of files) {
                try {
                    const data = await uploadSingleAsset(file, 'subject', false);
                    if (data.status === 'ok' && data.asset) {
                        state.subjectAssets.push(data.asset);
                    } else {
                        alert(data.message || 'Error al subir imagen');
                    }
                } catch (err) {
                    alert('Error al subir archivo: ' + err.message);
                }
            }
            inputUploadSubject.value = '';
            renderSubjectPreviews();
        });
    }

    // =========================================================================
    // 4. CONFIGURACIÓN, MODELOS Y PROMPTS DE SISTEMA
    // =========================================================================
    async function loadConfig() {
        try {
            const res = await fetch('api.php?action=get_config&t=' + Date.now(), { cache: 'no-store' });
            const data = await res.json();
            if (data.status === 'ok') {
                if (inputApiKey) inputApiKey.value = data.openai_api_key || '';
                state.selectedImageModel = data.selected_model || 'gpt-image-2.5-sunburst-2026-09-08';
                state.selectedTextModel = data.text_model || 'gpt-4o';
                state.stampLogoOverlay = !!data.stamp_logo_overlay;
                state.defaultPrompts = data.default_prompts || {};

                if (lblActiveModel) lblActiveModel.textContent = state.selectedImageModel;
                if (selectTextModel) selectTextModel.value = state.selectedTextModel;
                if (chkStampLogo) chkStampLogo.checked = state.stampLogoOverlay;

                let directorPromptVal = data.prompt_director_creativo || '';
                if (state.defaultPrompts.prompt_director_creativo && directorPromptVal.indexOf('IMAGEN DE MARCA Y PALETA SUBIDA') === -1) {
                    directorPromptVal = state.defaultPrompts.prompt_director_creativo;
                }
                let designerPromptVal = data.prompt_disenador_ia || '';
                if (state.defaultPrompts.prompt_disenador_ia && designerPromptVal.indexOf('IMAGEN DE MARCA Y PALETA DE COLORES SUBIDA') === -1) {
                    designerPromptVal = state.defaultPrompts.prompt_disenador_ia;
                }

                if (inputPromptDirector) inputPromptDirector.value = directorPromptVal;
                if (inputPromptDesigner) inputPromptDesigner.value = designerPromptVal;

                if (selectModel) {
                    let exists = Array.from(selectModel.options).some(o => o.value === state.selectedImageModel);
                    if (!exists) {
                        const opt = document.createElement('option');
                        opt.value = state.selectedImageModel;
                        opt.textContent = state.selectedImageModel;
                        selectModel.insertBefore(opt, selectModel.firstChild);
                    }
                    selectModel.value = state.selectedImageModel;
                }

                if (data.default_brand_asset && data.default_brand_asset.preview_url) {
                    state.brandAsset = data.default_brand_asset;
                }
                state.brandText = data.default_brand_text || '';
                if (inputBrandText) {
                    inputBrandText.value = state.brandText;
                }

                if (data.default_logo && data.default_logo.preview_url) {
                    state.logoAsset = data.default_logo;
                }
                renderBrandPreview();
                renderLogoPreview();
                renderSubjectPreviews();
            }
        } catch (err) {
            console.error('Error cargando configuración:', err);
        }
    }

    if (btnRestorePrompts) {
        btnRestorePrompts.addEventListener('click', () => {
            if (!state.defaultPrompts) return;
            if (inputPromptDirector && state.defaultPrompts.prompt_director_creativo) {
                inputPromptDirector.value = state.defaultPrompts.prompt_director_creativo;
            }
            if (inputPromptDesigner && state.defaultPrompts.prompt_disenador_ia) {
                inputPromptDesigner.value = state.defaultPrompts.prompt_disenador_ia;
            }
            showConfigAlert('Prompts restaurados a los valores originales. Hacé clic en "Guardar Configuración" para aplicar.', 'success');
        });
    }

    if (btnOpenConfig && modalConfig) {
        btnOpenConfig.addEventListener('click', async () => {
            if (alertConfig) alertConfig.style.display = 'none';
            await loadConfig();
            modalConfig.style.display = 'flex';
        });
    }
    if (btnCloseConfig && modalConfig) {
        btnCloseConfig.addEventListener('click', () => {
            modalConfig.style.display = 'none';
        });
    }

    if (btnLoadModels) {
        btnLoadModels.addEventListener('click', async () => {
            btnLoadModels.disabled = true;
            btnLoadModels.textContent = '⏳ Consultando...';
            try {
                const res = await fetch('api.php?action=get_models');
                const data = await res.json();
                if (data.status === 'ok' && Array.isArray(data.image_models)) {
                    selectModel.innerHTML = '';
                    data.image_models.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m;
                        opt.textContent = m;
                        if (m === state.selectedImageModel) opt.selected = true;
                        selectModel.appendChild(opt);
                    });
                    showConfigAlert(`Modelos cargados (${data.image_models.length} encontrados).`, 'success');
                } else {
                    showConfigAlert(data.message || 'No se pudieron listar los modelos.', 'error');
                }
            } catch (err) {
                showConfigAlert('Error de conexión: ' + err.message, 'error');
            } finally {
                btnLoadModels.disabled = false;
                btnLoadModels.textContent = '🔄 Listar de mi API';
            }
        });
    }

    if (btnSaveConfig) {
        btnSaveConfig.addEventListener('click', async () => {
            btnSaveConfig.disabled = true;
            try {
                const payload = {
                    openai_api_key: inputApiKey ? inputApiKey.value.trim() : '',
                    text_model: selectTextModel ? selectTextModel.value : 'gpt-4o',
                    selected_model: selectModel ? selectModel.value : 'gpt-image-2.5-sunburst-2026-09-08',
                    stamp_logo_overlay: chkStampLogo ? chkStampLogo.checked : false,
                    prompt_director_creativo: inputPromptDirector ? inputPromptDirector.value.trim() : '',
                    prompt_disenador_ia: inputPromptDesigner ? inputPromptDesigner.value.trim() : ''
                };
                const res = await fetch('api.php?action=save_config', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    state.selectedImageModel = payload.selected_model;
                    state.selectedTextModel = payload.text_model;
                    state.stampLogoOverlay = payload.stamp_logo_overlay;
                    if (lblActiveModel) lblActiveModel.textContent = state.selectedImageModel;
                    showConfigAlert('Configuración y Prompts guardados correctamente.', 'success');
                    setTimeout(() => {
                        modalConfig.style.display = 'none';
                    }, 700);
                } else {
                    showConfigAlert(data.message || 'Error al guardar.', 'error');
                }
            } catch (err) {
                showConfigAlert('Error: ' + err.message, 'error');
            } finally {
                btnSaveConfig.disabled = false;
            }
        });
    }

    // =========================================================================
    // 5. FLUJO CONVERSACIONAL EN 2 PASOS (PASO 1: TEXTO/VISIÓN -> PASO 2: SUNBURST)
    // =========================================================================
    if (promptInput) {
        promptInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSendPrompt();
            }
        });
    }
    if (btnSendPrompt) {
        btnSendPrompt.addEventListener('click', () => handleSendPrompt());
    }

    async function handleSendPrompt() {
        if (state.isBusy) return;
        const promptText = promptInput ? promptInput.value.trim() : '';
        if (!promptText) {
            alert('Escribí la idea de lo que querés reflejar en el posteo.');
            if (promptInput) promptInput.focus();
            return;
        }

        state.isBusy = true;
        if (btnSendPrompt) btnSendPrompt.disabled = true;
        if (promptInput) promptInput.value = '';

        if (welcomeScreen) {
            welcomeScreen.style.display = 'block';
            welcomeScreen.classList.add('in-chat');
        }
        if (threadContainer) threadContainer.style.display = 'flex';

        // 1. Mostrar burbuja del pedido del usuario en el hilo
        appendUserTurnCard({
            prompt: promptText,
            networks: [...state.networks],
            placement: state.placement,
            brandAsset: state.brandAsset,
            brandText: state.brandText,
            logoAsset: state.logoAsset,
            subjectAssets: [...state.subjectAssets]
        });

        // 2. Mostrar indicador de carga de textos y narrativa visual
        const loadingStep1El = document.createElement('div');
        loadingStep1El.className = 'step1-card';
        loadingStep1El.innerHTML = `
            <div class="step-header-bar">
                <div class="step-badge-title">
                    <span>📝 Redactando textos y armando la narrativa de la imagen...</span>
                </div>
            </div>
            <p style="font-size:0.88rem; color:#475569;">
                Analizando tu idea${(state.brandAsset || state.brandText) ? ', tu marca/paleta' : ''}${state.logoAsset ? ', tu logo' : ''}${state.subjectAssets.length ? ' y tus fotos (' + state.subjectAssets.map(s => s.name).join(', ') + ')' : ''} para preparar los textos de cada red y el guion narrativo de la escena...
            </p>
        `;
        threadContainer.appendChild(loadingStep1El);
        scrollToBottom();

        try {
            const res = await fetch('api.php?action=step1_plan_post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    post_id: state.currentPostId,
                    prompt: promptText,
                    networks: state.networks,
                    placement: state.placement,
                    brand_asset: state.brandAsset,
                    brand_text: state.brandText,
                    logo_asset: state.logoAsset,
                    subject_assets: state.subjectAssets,
                    text_model: state.selectedTextModel,
                    previous_state: state.lastPlan
                })
            });
            const data = await res.json();
            loadingStep1El.remove();

            if (data.status !== 'ok' || !data.plan) {
                appendErrorCard(data.message || 'Error al generar la propuesta.');
                state.isBusy = false;
                if (btnSendPrompt) btnSendPrompt.disabled = false;
                return;
            }

            state.currentPostId = data.post_id;
            state.lastPlan = data.plan;
            syncActiveThreadButtons();
            if (currentPostTitle && data.plan.title) {
                currentPostTitle.textContent = data.plan.title;
            }

            // Renderizar tarjeta de Textos + Narrativa editable
            const step1CardEl = appendStep1Card(data.plan);
            await loadPostsHistory();
            scrollToBottom();

            // Si está activo el check "Crear imagen automáticamente", disparamos la creación de la pieza gráfica
            const autoRunStep2 = chkAutoStep2 ? chkAutoStep2.checked : true;
            if (autoRunStep2) {
                const headlineInput = step1CardEl.querySelector('.input-headline-image');
                const promptTextarea = step1CardEl.querySelector('.textarea-sunburst-prompt');
                const btnTriggerStep2 = step1CardEl.querySelector('.btn-sunburst-create');
                await executeStep2ImageGeneration({
                    sunburstPrompt: promptTextarea ? promptTextarea.value : (data.plan.visual_design?.sunburst_prompt || ''),
                    headlineInImage: headlineInput ? headlineInput.value : (data.plan.visual_design?.headline_in_image || ''),
                    triggerButton: btnTriggerStep2
                });
            }
        } catch (err) {
            loadingStep1El.remove();
            appendErrorCard('Error de conexión: ' + err.message);
        } finally {
            state.isBusy = false;
            if (btnSendPrompt) btnSendPrompt.disabled = false;
            if (promptInput) {
                promptInput.placeholder = 'Pedí cualquier ajuste sobre los textos o la escena (ej: "que xxxx.jpg salude a yyyy.jpg en el fondo zzzz.jpg" o "hacé el titular más corto")...';
            }
        }
    }

    function appendUserTurnCard({ prompt, networks, placement, brandAsset, brandText, logoAsset, subjectAssets }) {
        const card = document.createElement('div');
        card.className = 'user-turn-card';

        const placementBadge = placement === 'story' ? '📱 Historia' : '🖼️ Post';
        const netBadges = (networks || []).map(n => NETWORK_META[n]?.short || n).join(' · ');

        let assetsHtml = '';
        if (brandAsset || brandText || logoAsset || (subjectAssets && subjectAssets.length)) {
            const pills = [];
            if (brandAsset) {
                pills.push(`
                    <div class="user-asset-pill">
                        <img src="${escapeHtml(brandAsset.preview_url)}" alt="Marca">
                        <span>1️⃣ Marca: ${escapeHtml(brandAsset.name)}</span>
                    </div>
                `);
            } else if (brandText) {
                pills.push(`
                    <div class="user-asset-pill">
                        <span>1️⃣ Paleta: ${escapeHtml(brandText)}</span>
                    </div>
                `);
            }
            if (logoAsset) {
                pills.push(`
                    <div class="user-asset-pill">
                        <img src="${escapeHtml(logoAsset.preview_url)}" alt="Logo">
                        <span>2️⃣ Logo: ${escapeHtml(logoAsset.name)}</span>
                    </div>
                `);
            }
            (subjectAssets || []).forEach(sa => {
                pills.push(`
                    <div class="user-asset-pill">
                        <img src="${escapeHtml(sa.preview_url)}" alt="Ref">
                        <span>3️⃣ ${escapeHtml(sa.name)}</span>
                    </div>
                `);
            });
            assetsHtml = `<div class="user-turn-assets">${pills.join('')}</div>`;
        }

        card.innerHTML = `
            <div class="user-turn-meta">
                <span class="meta-tag">${escapeHtml(placementBadge)}</span>
                <span class="meta-tag">🌐 ${escapeHtml(netBadges)}</span>
            </div>
            <div class="user-turn-text">${escapeHtml(prompt)}</div>
            ${assetsHtml}
        `;
        threadContainer.appendChild(card);
    }

    function appendErrorCard(message) {
        const errEl = document.createElement('div');
        errEl.className = 'alert alert-error';
        errEl.innerHTML = `<strong>⚠️ Atención:</strong> ${escapeHtml(message)}`;
        threadContainer.appendChild(errEl);
        scrollToBottom();
    }

    function appendStep1Card(plan) {
        const card = document.createElement('div');
        card.className = 'step1-card';

        const activeNets = Array.isArray(plan.networks) && plan.networks.length
            ? plan.networks
            : ['ig', 'fb', 'tiktok', 'linkedin'];
        const copies = plan.copies || {};
        const vd = plan.visual_design || {};
        const placementText = plan.placement === 'story'
            ? '📱 Historia'
            : '🖼️ Post';

        const copiesCardsHtml = activeNets.map(netKey => {
            const netInfo = NETWORK_META[netKey] || { label: netKey.toUpperCase() };
            const copyText = copies[netKey] || '';
            return `
                <div class="copy-network-card">
                    <div class="copy-net-header">
                        <span class="copy-net-name">${escapeHtml(netInfo.label)}</span>
                        <button type="button" class="btn-mini btn-copy-text">📋 Copiar</button>
                    </div>
                    <textarea class="copy-textarea" rows="5">${escapeHtml(copyText)}</textarea>
                </div>
            `;
        }).join('');

        card.innerHTML = `
            <div class="step-header-bar">
                <div class="step-badge-title">
                    <span>📝 Textos por Red Social + Narrativa de la Imagen</span>
                </div>
                <span class="meta-tag">${escapeHtml(placementText)}</span>
            </div>

            ${plan.assistant_comment ? `<div class="assistant-note">${escapeHtml(plan.assistant_comment)}</div>` : ''}

            <div class="copies-grid">
                ${copiesCardsHtml}
            </div>

            <div class="narrative-design-box">
                <div class="narrative-title">
                    <span>🎬 Narrativa de la Imagen (Guion de Escena)</span>
                    <span style="font-size:0.74rem; color:#64748b; font-weight:500;">Podés editar el titular o insertar variables de tus fotos antes de crear la pieza gráfica</span>
                </div>

                ${vd.scene_summary ? `
                    <p style="font-size:0.84rem; color:#334155; margin-bottom:0.85rem; line-height:1.45;">
                        <strong>Idea de la escena:</strong> ${escapeHtml(vd.scene_summary)}
                    </p>
                ` : ''}

                <div class="design-field-group">
                    <label>Texto / Titular que llevará la imagen:</label>
                    <input type="text" class="design-input input-headline-image" value="${escapeHtml(vd.headline_in_image || '')}">
                </div>

                <div class="design-field-group" style="margin-bottom:0;">
                    <label>Descripción narrativa de lo que se verá en la imagen (podés mencionar tus fotos como <code>xxxx.jpg</code>, <code>yyyy.jpg</code>):</label>
                    <div class="photo-vars-bar step1-photo-vars-container" style="display:none; margin-bottom:0.45rem;"></div>
                    <textarea class="design-textarea mono textarea-sunburst-prompt" rows="4">${escapeHtml(vd.sunburst_prompt || '')}</textarea>
                </div>

                <div class="step2-trigger-bar">
                    <div style="font-size:0.76rem; color:#64748b;">
                        ${(state.brandAsset || state.brandText) ? '1️⃣ Marca/Paleta activa · ' : ''}
                        ${state.logoAsset ? '2️⃣ Logo incluido' : 'Sin logo'}
                        ${state.subjectAssets.length ? ` · 3️⃣ ${state.subjectAssets.length} foto(s): ${escapeHtml(state.subjectAssets.map(s => s.name).join(', '))}` : ''}
                    </div>
                    <button type="button" class="btn-sunburst-create">
                        <span>🎨 Crear Pieza Gráfica</span>
                    </button>
                </div>
            </div>
        `;

        // Eventos para copiar cada texto de red social
        card.querySelectorAll('.copy-network-card').forEach(netCard => {
            const btnCopy = netCard.querySelector('.btn-copy-text');
            const txtArea = netCard.querySelector('.copy-textarea');
            if (btnCopy && txtArea) {
                btnCopy.addEventListener('click', () => {
                    navigator.clipboard.writeText(txtArea.value);
                    const oldText = btnCopy.textContent;
                    btnCopy.textContent = '✅ Copiado';
                    setTimeout(() => { btnCopy.textContent = oldText; }, 1400);
                });
            }
        });

        // Evento para disparar la creación de la imagen manualmente (o re-generar con cambios)
        const btnCreate = card.querySelector('.btn-sunburst-create');
        const headlineInput = card.querySelector('.input-headline-image');
        const promptTextarea = card.querySelector('.textarea-sunburst-prompt');
        const step1VarsContainer = card.querySelector('.step1-photo-vars-container');

        if (step1VarsContainer && promptTextarea) {
            renderStep1PhotoVariablesInto(step1VarsContainer, promptTextarea);
        }

        if (btnCreate) {
            btnCreate.addEventListener('click', async () => {
                await executeStep2ImageGeneration({
                    sunburstPrompt: promptTextarea ? promptTextarea.value : '',
                    headlineInImage: headlineInput ? headlineInput.value : '',
                    triggerButton: btnCreate
                });
            });
        }

        threadContainer.appendChild(card);
        return card;
    }

    // =========================================================================
    // 6. GENERACIÓN DE LA IMAGEN
    // =========================================================================
    async function executeStep2ImageGeneration({
        sunburstPrompt,
        headlineInImage,
        triggerButton,
        forceNewVariant = false,
        isCorrectionOverride = null,
        editInstructionOverride = null,
        baseImageUrl = ''
    }) {
        if (!sunburstPrompt || !sunburstPrompt.trim()) {
            alert('La descripción de diseño visual está vacía.');
            return;
        }

        const originalBtnHtml = triggerButton ? triggerButton.innerHTML : '';
        if (triggerButton) {
            triggerButton.disabled = true;
            triggerButton.innerHTML = '<span>⏳ Procesando imagen...</span>';
        }

        const vd = state.lastPlan?.visual_design || {};
        const isCorrection = isCorrectionOverride !== null ? !!isCorrectionOverride : !!vd.is_image_correction;
        const editInstruction = editInstructionOverride !== null ? editInstructionOverride : (vd.edit_instruction || '');

        const loadingStep2El = document.createElement('div');
        loadingStep2El.className = 'step2-card';
        loadingStep2El.innerHTML = `
            <div class="step-header-bar">
                <div class="step-badge-title">
                    <span>${isCorrection && !forceNewVariant ? '✏️ Aplicando corrección sobre la misma pieza gráfica...' : '🎨 Diseñando pieza gráfica...'}</span>
                </div>
            </div>
            <p style="font-size:0.88rem; color:#475569;">
                ${isCorrection && !forceNewVariant
                    ? 'Manteniendo la imagen base y aplicando tu ajuste (' + escapeHtml(editInstruction) + ')...'
                    : 'Generando la imagen final' + ((state.brandAsset || state.brandText) ? ' aplicando tu Marca/Paleta' : '') + (state.logoAsset ? ', integrando tu Logo' : '') + (state.subjectAssets.length ? ' y tus fotos (' + escapeHtml(state.subjectAssets.map(s => s.name).join(', ')) + ')' : '') + '. Esto puede demorar unos segundos...'
                }
            </p>
        `;
        threadContainer.appendChild(loadingStep2El);
        scrollToBottom();

        try {
            const res = await fetch('api.php?action=step2_generate_image', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    post_id: state.currentPostId,
                    sunburst_prompt: sunburstPrompt.trim(),
                    headline_in_image: (headlineInImage || '').trim(),
                    is_image_correction: isCorrection,
                    edit_instruction: editInstruction,
                    base_image_url: baseImageUrl || '',
                    force_new_variant: !!forceNewVariant,
                    placement: state.placement,
                    model: state.selectedImageModel,
                    brand_asset: state.brandAsset,
                    brand_text: state.brandText,
                    logo_asset: state.logoAsset,
                    subject_assets: state.subjectAssets,
                    stamp_logo: state.stampLogoOverlay
                })
            });
            const data = await res.json();
            loadingStep2El.remove();

            if (data.status !== 'ok' || !data.image_url) {
                appendErrorCard(data.message || 'No se pudo generar la imagen.');
                return;
            }

            if (data.post_record) {
                state.lastPlan = data.post_record;
            }

            appendStep2ResultCard({
                imageUrl: data.image_url,
                placement: data.placement || state.placement,
                dimensions: data.dimensions || '',
                modelUsed: data.model_used || state.selectedImageModel,
                referencesUsed: data.references_used || [],
                sunburstPrompt: sunburstPrompt.trim(),
                headlineInImage: (headlineInImage || '').trim()
            });

            await loadPostsHistory();
            scrollToBottom();
        } catch (err) {
            loadingStep2El.remove();
            appendErrorCard('Error de red al crear la imagen: ' + err.message);
        } finally {
            if (triggerButton) {
                triggerButton.disabled = false;
                triggerButton.innerHTML = originalBtnHtml || '<span>🎨 Crear / Actualizar Pieza Gráfica</span>';
            }
        }
    }

    function appendStep2ResultCard({ imageUrl, placement, dimensions, modelUsed, referencesUsed, sunburstPrompt, headlineInImage }) {
        const card = document.createElement('div');
        card.className = 'step2-card';

        const cacheBustUrl = `${imageUrl}?t=${Date.now()}`;
        const frameClass = placement === 'story' ? 'piece-frame story-frame' : 'piece-frame';
        const placementBadge = placement === 'story' ? '📱 Historia' : '🖼️ Post';

        card.innerHTML = `
            <div class="step-header-bar">
                <div class="step-badge-title">
                    <span>✨ Pieza Gráfica Lista</span>
                </div>
                <span class="meta-tag">${escapeHtml(placementBadge)}</span>
            </div>

            <div class="generated-piece-showcase">
                <div class="${frameClass}">
                    <img src="${escapeHtml(cacheBustUrl)}" alt="Pieza generada">
                </div>

                <div class="piece-actions-bar">
                    <a href="${escapeHtml(cacheBustUrl)}" download="tecnogen_${escapeHtml(placement)}_${Date.now()}.png" class="btn-primary-action">
                        ⬇️ Descargar PNG (${escapeHtml( placement === 'story' ? 'Historia' : 'Post' )})
                    </a>
                    <button type="button" class="btn-mini btn-regenerate-piece" style="padding:0.55rem 1rem; font-size:0.84rem;" title="Crea una variante completamente nueva desde cero">
                        🔄 Generar Otra Variante Nueva
                    </button>
                </div>

                <div class="piece-direct-edit-box">
                    <label class="piece-direct-edit-label">✏️ Corregir / Modificar sobre esta misma imagen (mantiene el diseño y aplica tu ajuste):</label>
                    <div class="piece-direct-edit-row">
                        <input type="text" class="design-input input-direct-correction" placeholder="Ej: 'Reubicá los textos más adentro para que no se corten', 'Cambiá el botón por Reservá hoy', 'Achicá el logo'...">
                        <button type="button" class="btn-sunburst-create btn-apply-correction">
                            <span>✨ Corregir esta Imagen</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        const btnRegen = card.querySelector('.btn-regenerate-piece');
        if (btnRegen) {
            btnRegen.addEventListener('click', async () => {
                await executeStep2ImageGeneration({
                    sunburstPrompt,
                    headlineInImage,
                    triggerButton: btnRegen,
                    forceNewVariant: true
                });
            });
        }

        const inputCorrection = card.querySelector('.input-direct-correction');
        const btnApplyCorrection = card.querySelector('.btn-apply-correction');
        const triggerDirectCorrection = async () => {
            const instruction = inputCorrection ? inputCorrection.value.trim() : '';
            if (!instruction) {
                alert('Escribí qué corrección o cambio querés aplicar sobre esta misma imagen.');
                if (inputCorrection) inputCorrection.focus();
                return;
            }
            await executeStep2ImageGeneration({
                sunburstPrompt,
                headlineInImage,
                triggerButton: btnApplyCorrection,
                forceNewVariant: false,
                isCorrectionOverride: true,
                editInstructionOverride: instruction,
                baseImageUrl: imageUrl
            });
        };

        if (btnApplyCorrection) {
            btnApplyCorrection.addEventListener('click', triggerDirectCorrection);
        }
        if (inputCorrection) {
            inputCorrection.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerDirectCorrection();
                }
            });
        }

        threadContainer.appendChild(card);
    }

    // =========================================================================
    // 7. HISTORIAL LATERAL Y BORRADO COMPLETO DE CONVERSACIONES / HILOS
    // =========================================================================
    async function deleteThreadById(pid) {
        if (!pid) return;
        if (!confirm('¿Eliminar esta conversación/hilo y borrar todas sus imágenes y archivos del servidor?')) {
            return;
        }
        try {
            const res = await fetch('api.php?action=delete_post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: pid })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                if (state.currentPostId === pid) {
                    startNewPostSession();
                }
                await loadPostsHistory();
            } else {
                alert(data.message || 'No se pudo eliminar el hilo.');
            }
        } catch (err) {
            alert('Error de red al eliminar el hilo: ' + err.message);
        }
    }

    if (btnDeleteActivePost) {
        btnDeleteActivePost.addEventListener('click', async () => {
            if (!state.currentPostId) return;
            await deleteThreadById(state.currentPostId);
        });
    }

    if (btnClearAllPosts) {
        btnClearAllPosts.addEventListener('click', async () => {
            if (!confirm('¿Eliminar TODAS las conversaciones/hilos guardadas y borrar todos sus archivos del servidor?')) {
                return;
            }
            try {
                const res = await fetch('api.php?action=delete_all_posts', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    startNewPostSession();
                    await loadPostsHistory();
                }
            } catch (err) {
                alert('Error al vaciar el historial: ' + err.message);
            }
        });
    }

    async function loadPostsHistory() {
        if (!postsHistoryList) return;
        try {
            const res = await fetch('api.php?action=list_posts');
            const data = await res.json();
            if (data.status !== 'ok' || !Array.isArray(data.posts)) return;

            if (btnClearAllPosts) {
                btnClearAllPosts.style.display = data.posts.length > 0 ? 'inline-block' : 'none';
            }

            if (data.posts.length === 0) {
                postsHistoryList.innerHTML = `<div style="font-size:0.78rem; color:#64748b; padding:0.5rem;">Todavía no hay publicaciones guardadas.</div>`;
                return;
            }

            postsHistoryList.innerHTML = data.posts.map(p => {
                const isActive = p.post_id === state.currentPostId;
                const badge = p.placement === 'story' ? 'Historia' : 'Post';
                const thumbHtml = p.latest_image
                    ? `<img src="${escapeHtml(p.latest_image)}" class="history-thumb" alt="">`
                    : `<div class="history-thumb" style="display:flex;align-items:center;justify-content:center;font-size:0.85rem;">📝</div>`;

                return `
                    <div class="history-item ${isActive ? 'active' : ''}" data-post-id="${escapeHtml(p.post_id)}">
                        <div class="history-item-left">
                            ${thumbHtml}
                            <div class="history-meta">
                                <div class="history-title">${escapeHtml(p.title || 'Publicación')}</div>
                                <div class="history-sub">${escapeHtml(badge)} · ${escapeHtml(p.updated_at || '')}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-del-history" data-del-id="${escapeHtml(p.post_id)}" title="Eliminar hilo y borrar todo del servidor">🗑️</button>
                    </div>
                `;
            }).join('');

            postsHistoryList.querySelectorAll('.history-item').forEach(item => {
                item.addEventListener('click', async (e) => {
                    if (e.target.closest('.btn-del-history')) return;
                    const pid = item.getAttribute('data-post-id');
                    if (pid) await openSavedPost(pid);
                });
            });

            postsHistoryList.querySelectorAll('.btn-del-history').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const pid = btn.getAttribute('data-del-id');
                    if (pid) await deleteThreadById(pid);
                });
            });
        } catch (err) {
            console.error('Error cargando historial:', err);
        }
    }

    function highlightActiveHistoryItem() {
        if (!postsHistoryList) return;
        postsHistoryList.querySelectorAll('.history-item').forEach(el => {
            el.classList.toggle('active', el.getAttribute('data-post-id') === state.currentPostId);
        });
    }

    async function openSavedPost(postId) {
        try {
            const res = await fetch(`api.php?action=get_post&post_id=${encodeURIComponent(postId)}`);
            const data = await res.json();
            if (data.status !== 'ok' || !data.post) return;

            const p = data.post;
            state.currentPostId = p.post_id;
            state.lastPlan = p;
            syncActiveThreadButtons();
            state.networks = Array.isArray(p.networks) && p.networks.length ? p.networks : ['ig', 'fb', 'tiktok', 'linkedin'];
            syncNetworkButtonsUI();
            setPlacement(p.placement || 'post');

            if ('brand_asset' in p) {
                state.brandAsset = p.brand_asset || null;
            }
            if ('brand_text' in p) {
                state.brandText = typeof p.brand_text === 'string' ? p.brand_text : '';
                if (inputBrandText) inputBrandText.value = state.brandText;
            }
            if ('logo_asset' in p) {
                state.logoAsset = p.logo_asset || null;
            }
            state.subjectAssets = Array.isArray(p.subject_assets) ? p.subject_assets : [];
            renderBrandPreview();
            renderLogoPreview();
            renderSubjectPreviews();

            if (currentPostTitle) currentPostTitle.textContent = p.title || 'Publicación';
            if (welcomeScreen) {
                welcomeScreen.style.display = 'block';
                welcomeScreen.classList.add('in-chat');
            }
            if (threadContainer) {
                threadContainer.innerHTML = '';
                threadContainer.style.display = 'flex';
            }

            // Si tiene historial completo de turnos (turns), renderizar cada turno paso a paso
            if (Array.isArray(p.turns) && p.turns.length > 0) {
                p.turns.forEach(turn => {
                    appendUserTurnCard({
                        prompt: turn.user_prompt || '',
                        networks: turn.networks || state.networks,
                        placement: turn.placement || state.placement,
                        brandAsset: state.brandAsset,
                        brandText: state.brandText,
                        logoAsset: state.logoAsset,
                        subjectAssets: state.subjectAssets
                    });
                    appendStep1Card({
                        networks: turn.networks || state.networks,
                        placement: turn.placement || state.placement,
                        assistant_comment: turn.assistant_comment || '',
                        copies: turn.copies || {},
                        visual_design: turn.visual_design || {}
                    });
                    if (Array.isArray(turn.images) && turn.images.length > 0) {
                        turn.images.forEach(img => {
                            appendStep2ResultCard({
                                imageUrl: img.url,
                                placement: img.placement || turn.placement || 'post',
                                dimensions: img.dimensions || '',
                                modelUsed: img.model_used || state.selectedImageModel,
                                referencesUsed: img.references_used || [],
                                sunburstPrompt: img.prompt_used || (turn.visual_design?.sunburst_prompt || ''),
                                headlineInImage: turn.visual_design?.headline_in_image || ''
                            });
                        });
                    }
                });
            } else {
                // Compatibilidad con publicaciones guardadas antes de 'turns'
                if (p.last_idea) {
                    appendUserTurnCard({
                        prompt: p.last_idea,
                        networks: state.networks,
                        placement: state.placement,
                        brandAsset: state.brandAsset,
                        brandText: state.brandText,
                        logoAsset: state.logoAsset,
                        subjectAssets: state.subjectAssets
                    });
                }
                appendStep1Card(p);
                if (Array.isArray(p.images) && p.images.length) {
                    const chronological = [...p.images].reverse();
                    chronological.forEach(img => {
                        appendStep2ResultCard({
                            imageUrl: img.url,
                            placement: img.placement || p.placement || 'post',
                            dimensions: img.dimensions || '',
                            modelUsed: img.model_used || state.selectedImageModel,
                            referencesUsed: img.references_used || [],
                            sunburstPrompt: img.prompt_used || (p.visual_design?.sunburst_prompt || ''),
                            headlineInImage: p.visual_design?.headline_in_image || ''
                        });
                    });
                }
            }

            highlightActiveHistoryItem();
            scrollToBottom();
        } catch (err) {
            console.error('Error al abrir publicación:', err);
        }
    }

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================
    loadConfig();
    loadPostsHistory();
});
