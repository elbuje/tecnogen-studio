document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // ESTADO GLOBAL DEL ESTUDIO DE CARRUSELES (INDEPENDIENTE)
    // =========================================================================
    const state = {
        currentCarouselId: null,
        networks: ['ig', 'fb', 'tiktok', 'linkedin'],
        slideCountPref: 'auto',
        swipeStylePref: 'auto',
        swipePositionPref: 'auto',
        brandAsset: null,
        brandText: '',
        logoAsset: null,
        subjectAssets: [],
        lastPlan: null,
        slideImages: {}, // Mapa { [slide_number]: { url, ... } }
        selectedImageModel: 'gpt-image-2.5-sunburst-2026-09-08',
        selectedTextModel: 'gpt-4o',
        stampLogoOverlay: false,
        defaultPrompts: {},
        isBusy: false
    };

    const NETWORK_META = {
        ig: { label: '📸 Instagram (IG)', short: 'IG' },
        fb: { label: '📘 Facebook (FB)', short: 'FB' },
        tiktok: { label: '🎵 TikTok (TT)', short: 'TT' },
        linkedin: { label: '💼 LinkedIn (IN)', short: 'IN' }
    };

    const SWIPE_STYLE_META = {
        auto: '✨ Deslizador: Auto',
        intriga: '🔥 Gancho / Intriga',
        interactivo: '❓ Interactivo (Mito/V-F)',
        numerado: '🔢 Numerado / Progreso',
        icono_minimal: '➡️ Solo Flecha / Ícono',
        puente_visual: '🧩 Puente Visual Borde',
        none: '🚫 Sin Deslizador'
    };

    const SWIPE_POSITION_META = {
        auto: '✨ Ubicación: Auto',
        bottom_right: '↘️ Abajo Derecha',
        middle_right: '➡️ Centro-Derecha (Borde)',
        bottom_center: '⬇️ Abajo Centrado',
        top_right: '↗️ Arriba Derecha',
        below_text: '📑 Debajo del Texto',
        none: '🚫 Sin Deslizador'
    };

    const SWIPE_PRESET_GROUPS = [
        {
            label: '🔥 Narrativos / Intriga (Texto + →)',
            options: [
                'Deslizá →',
                'Mirá por qué →',
                'Descubrí la causa →',
                'Pero eso no es todo →',
                'Acá está el secreto →',
                '¿Qué pasó después? →',
                'El error más común →',
                'La solución real →',
                'Seguí leyendo →'
            ]
        },
        {
            label: '❓ Interactivos / Juego (Reto)',
            options: [
                '¿Mito o Realidad? Deslizá →',
                '¿Verdadero o Falso? →',
                '¿Qué harías vos? →',
                'Adiviná la respuesta →',
                'Comprobá si acertaste →'
            ]
        },
        {
            label: '🔢 Secuenciales / Progreso',
            options: [
                'Paso 1 →',
                'Ir al siguiente paso →',
                'Siguiente tip →',
                '01 / 05 →',
                '[ ● ● ○ ○ ] →'
            ]
        },
        {
            label: '➡️ Visuales Puros (Sin texto)',
            options: [
                '[SOLO ÍCONO: Botón circular con flecha →]',
                '[SOLO ÍCONO: Doble chevron minimalista »]',
                '[PUENTE VISUAL: Flecha continua hacia el borde derecho]'
            ]
        },
        {
            label: '🎯 Cierre / CTA (Para última lámina)',
            options: [
                '🔖 Guardá este post para después',
                '📲 Compartilo con alguien que lo necesite',
                '💬 Contanos tu duda en comentarios',
                '📅 Reservá tu turno por link en bio',
                '📩 Escribinos por mensaje directo',
                'Contáctanos ahora →'
            ]
        }
    ];

    // =========================================================================
    // REFERENCIAS DOM
    // =========================================================================
    const sidebar = document.getElementById('sidebar');
    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
    const btnNewCarousel = document.getElementById('btn-new-carousel');
    const carouselsHistoryList = document.getElementById('carousels-history-list');
    const btnClearAllCarousels = document.getElementById('btn-clear-all-carousels');
    const currentCarouselTitle = document.getElementById('current-carousel-title');
    const btnDeleteActiveCarousel = document.getElementById('btn-delete-active-carousel');

    const chatViewport = document.getElementById('chat-viewport');
    const welcomeScreen = document.getElementById('welcome-screen');
    const threadContainer = document.getElementById('thread-container');

    // Controles del Composer
    const netButtons = Array.from(document.querySelectorAll('.net-btn'));
    const selectSlideCount = document.getElementById('select-slide-count');
    const selectSwipeStyle = document.getElementById('select-swipe-style');
    const selectSwipePosition = document.getElementById('select-swipe-position');
    const chkAutoStep2 = document.getElementById('chk-auto-step2');

    // Apartados de Recursos
    const inputUploadBrand = document.getElementById('input-upload-brand');
    const activeBrandPreview = document.getElementById('active-brand-preview');
    const inputUploadLogo = document.getElementById('input-upload-logo');
    const activeLogoPreview = document.getElementById('active-logo-preview');
    const inputUploadSubject = document.getElementById('input-upload-subject');
    const activeSubjectsPreview = document.getElementById('active-subjects-preview');

    // Barra de Variables de Fotos
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

    // Modal Visor Deslizable de Carrusel
    const modalCarouselViewer = document.getElementById('modal-carousel-viewer');
    const viewerCarouselTitle = document.getElementById('viewer-carousel-title');
    const btnViewerOpenPopup = document.getElementById('btn-viewer-open-popup');
    const btnCloseViewer = document.getElementById('btn-close-viewer');
    const btnViewerPrev = document.getElementById('btn-viewer-prev');
    const btnViewerNext = document.getElementById('btn-viewer-next');
    const carouselViewportFrame = document.getElementById('carousel-viewport-frame');
    const viewerCounterPill = document.getElementById('viewer-counter-pill');
    const viewerCarouselTrack = document.getElementById('viewer-carousel-track');
    const viewerCarouselDots = document.getElementById('viewer-carousel-dots');
    const viewerSlideCaption = document.getElementById('viewer-slide-caption');
    const btnViewerDownloadCurrent = document.getElementById('btn-viewer-download-current');

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

    function getAvailablePhotoVariables() {
        const list = [];
        (state.subjectAssets || []).forEach(sa => {
            if (sa && sa.name) {
                list.push({ name: sa.name, preview_url: sa.preview_url });
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

        if (threadContainer) {
            threadContainer.querySelectorAll('.slide-photo-vars-container').forEach(container => {
                renderSlidePhotoVariablesInto(container);
            });
        }
    }

    function renderSlidePhotoVariablesInto(containerEl) {
        if (!containerEl) return;
        const slideCard = containerEl.closest('.slide-plan-card');
        const targetTextarea = slideCard ? slideCard.querySelector('.textarea-slide-scene') : null;
        const vars = getAvailablePhotoVariables();
        if (vars.length === 0 || !targetTextarea) {
            containerEl.style.display = 'none';
            containerEl.innerHTML = '';
            return;
        }
        containerEl.style.display = 'flex';
        containerEl.innerHTML = `
            <span class="photo-vars-label">📌 Insertar foto en esta escena:</span>
            <div class="photo-vars-list">
                ${vars.map((v, idx) => `
                    <button type="button" class="photo-var-btn" data-var-idx="${idx}">
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
    // 1. SIDEBAR Y SESIÓN DE CARRUSEL
    // =========================================================================
    if (btnToggleSidebar && sidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }

    if (btnNewCarousel) {
        btnNewCarousel.addEventListener('click', () => {
            startNewCarouselSession();
        });
    }

    function syncActiveThreadButtons() {
        if (btnDeleteActiveCarousel) {
            btnDeleteActiveCarousel.style.display = state.currentCarouselId ? 'inline-flex' : 'none';
        }
    }

    function startNewCarouselSession() {
        state.currentCarouselId = null;
        state.lastPlan = null;
        state.slideImages = {};
        state.subjectAssets = [];
        renderSubjectPreviews();

        if (currentCarouselTitle) currentCarouselTitle.textContent = 'Nuevo Carrusel';
        syncActiveThreadButtons();
        if (threadContainer) {
            threadContainer.innerHTML = '';
            threadContainer.style.display = 'none';
        }
        if (welcomeScreen) {
            welcomeScreen.style.display = 'block';
        }
        if (chatViewport) {
            chatViewport.scrollTop = 0;
        }
        if (promptInput) {
            promptInput.value = '';
            promptInput.placeholder = "Pegá acá el guion o escribí la idea base del carrusel (podés hacer clic en tus fotos para mencionarlas, ej: 'Carrusel con la Dra. de xxxx.jpg desmintiendo mitos sobre blanqueamiento dental')...";
            promptInput.focus();
        }
        highlightActiveHistoryItem();
    }

    // =========================================================================
    // 2. REDES SOCIALES Y CANTIDAD DE LÁMINAS
    // =========================================================================
    function syncNetworkButtonsUI() {
        netButtons.forEach(btn => {
            const net = btn.getAttribute('data-net');
            btn.classList.toggle('active', state.networks.includes(net));
        });
    }

    netButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const net = btn.getAttribute('data-net');
            if (!net) return;
            if (state.networks.includes(net)) {
                if (state.networks.length > 1) {
                    state.networks = state.networks.filter(n => n !== net);
                }
            } else {
                state.networks.push(net);
            }
            syncNetworkButtonsUI();
        });
    });

    if (selectSlideCount) {
        selectSlideCount.addEventListener('change', () => {
            state.slideCountPref = selectSlideCount.value || 'auto';
        });
    }
    if (selectSwipeStyle) {
        selectSwipeStyle.addEventListener('change', () => {
            state.swipeStylePref = selectSwipeStyle.value || 'auto';
        });
    }
    if (selectSwipePosition) {
        selectSwipePosition.addEventListener('change', () => {
            state.swipePositionPref = selectSwipePosition.value || 'auto';
        });
    }
    if (chkAutoStep2) {
        chkAutoStep2.addEventListener('change', () => {
            const label = chkAutoStep2.closest('.cta-step-toggle');
            if (label) label.classList.toggle('active', chkAutoStep2.checked);
        });
    }

    // =========================================================================
    // 3. RECURSOS (1. MARCA/PALETA · 2. LOGO · 3. FOTOS)
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
                    await fetch('api_carruseles.php?action=save_config', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ default_brand_asset: null })
                    });
                } catch (e) {}
            });
        }
        renderComposerPhotoVariablesBar();
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
                    await fetch('api_carruseles.php?action=save_config', {
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
                        resolve(canvas.toDataURL(outMime, 0.92));
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
            const res = await fetch('api_carruseles.php?action=upload_asset', {
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
        const res = await fetch('api_carruseles.php?action=upload_asset', { method: 'POST', body: fd });
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
            activeSubjectsPreview.innerHTML = `<span style="font-size:0.72rem; color:#0052E0; font-weight:600;">⏳ Procesando foto(s)...</span>`;
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
    // 4. CONFIGURACIÓN Y PROMPTS DE CARRUSELES
    // =========================================================================
    async function loadConfig() {
        try {
            const res = await fetch(`api_carruseles.php?action=get_config&_t=${Date.now()}`, { cache: 'no-store' });
            const data = await res.json();
            if (data.status === 'ok') {
                if (inputApiKey) inputApiKey.value = data.openai_api_key || '';
                state.selectedImageModel = data.selected_model || 'gpt-image-2.5-sunburst-2026-09-08';
                state.selectedTextModel = data.text_model || 'gpt-4o';
                state.stampLogoOverlay = !!data.stamp_logo_overlay;
                state.defaultPrompts = data.default_prompts || {};

                if (selectTextModel) selectTextModel.value = state.selectedTextModel;
                if (chkStampLogo) chkStampLogo.checked = state.stampLogoOverlay;
                if (inputPromptDirector) inputPromptDirector.value = data.prompt_director_carrusel || '';
                if (inputPromptDesigner) inputPromptDesigner.value = data.prompt_disenador_carrusel || '';

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
                if (data.default_logo && data.default_logo.preview_url) {
                    state.logoAsset = data.default_logo;
                }
                renderBrandPreview();
                renderLogoPreview();
                renderSubjectPreviews();
            }
        } catch (err) {
            console.error('Error cargando configuración de carruseles:', err);
        }
    }

    if (btnRestorePrompts) {
        btnRestorePrompts.addEventListener('click', () => {
            if (!state.defaultPrompts) return;
            if (inputPromptDirector && state.defaultPrompts.prompt_director_carrusel) {
                inputPromptDirector.value = state.defaultPrompts.prompt_director_carrusel;
            }
            if (inputPromptDesigner && state.defaultPrompts.prompt_disenador_carrusel) {
                inputPromptDesigner.value = state.defaultPrompts.prompt_disenador_carrusel;
            }
            showConfigAlert('Prompts de carruseles restaurados. Hacé clic en "Guardar Configuración" para aplicar.', 'success');
        });
    }

    if (btnOpenConfig && modalConfig) {
        btnOpenConfig.addEventListener('click', () => {
            if (alertConfig) alertConfig.style.display = 'none';
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
                const res = await fetch('api_carruseles.php?action=get_models');
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
                    prompt_director_carrusel: inputPromptDirector ? inputPromptDirector.value.trim() : '',
                    prompt_disenador_carrusel: inputPromptDesigner ? inputPromptDesigner.value.trim() : ''
                };
                const res = await fetch('api_carruseles.php?action=save_config', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    state.selectedImageModel = payload.selected_model;
                    state.selectedTextModel = payload.text_model;
                    state.stampLogoOverlay = payload.stamp_logo_overlay;
                    showConfigAlert('Configuración de Carruseles guardada correctamente.', 'success');
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
    // 5. PASO 1: PLANIFICACIÓN DEL GUION SLIDE POR SLIDE Y ADN VISUAL
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
            alert('Pegá el guion o escribí la idea base del carrusel.');
            if (promptInput) promptInput.focus();
            return;
        }

        state.isBusy = true;
        if (btnSendPrompt) btnSendPrompt.disabled = true;
        if (promptInput) promptInput.value = '';
        if (threadContainer) threadContainer.style.display = 'flex';

        appendUserTurnCard({
            prompt: promptText,
            networks: [...state.networks],
            slideCountPref: state.slideCountPref,
            swipeStylePref: state.swipeStylePref,
            swipePositionPref: state.swipePositionPref,
            brandAsset: state.brandAsset,
            brandText: state.brandText,
            logoAsset: state.logoAsset,
            subjectAssets: [...state.subjectAssets]
        });

        const loadingStep1El = document.createElement('div');
        loadingStep1El.className = 'step1-card';
        loadingStep1El.innerHTML = `
            <div class="step-header-bar">
                <div class="step-badge-title">
                    <span>📝 Diseñando estructura del carrusel, deslizadores y guion slide por slide...</span>
                </div>
            </div>
            <p style="font-size:0.88rem; color:#475569;">
                El Director Creativo está analizando tu guion${state.subjectAssets.length ? ' y tus fotos (' + state.subjectAssets.map(s => s.name).join(', ') + ')' : ''} para definir el formato dinámico, los deslizadores y cada una de las láminas...
            </p>
        `;
        threadContainer.appendChild(loadingStep1El);
        scrollToBottom();

        try {
            const res = await fetch('api_carruseles.php?action=step1_plan_carousel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    carousel_id: state.currentCarouselId,
                    prompt: promptText,
                    networks: state.networks,
                    slide_count: state.slideCountPref,
                    swipe_style_pref: state.swipeStylePref,
                    swipe_position_pref: state.swipePositionPref,
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
                appendErrorCard(data.message || 'Error al planificar el carrusel.');
                state.isBusy = false;
                if (btnSendPrompt) btnSendPrompt.disabled = false;
                return;
            }

            state.currentCarouselId = data.carousel_id;
            state.lastPlan = data.plan;
            state.slideImages = data.plan.slide_images || {};
            syncActiveThreadButtons();
            if (currentCarouselTitle && data.plan.title) {
                currentCarouselTitle.textContent = data.plan.title;
            }

            const step1CardEl = appendStep1CarouselCard(data.plan);
            await loadCarouselsHistory();
            scrollToBottom();

            const autoRunStep2 = chkAutoStep2 ? chkAutoStep2.checked : true;
            if (autoRunStep2) {
                const btnGenerateAll = step1CardEl.querySelector('.btn-generate-all-slides');
                await executeSequentialCarouselGeneration(step1CardEl, btnGenerateAll);
            }
        } catch (err) {
            loadingStep1El.remove();
            appendErrorCard('Error de conexión: ' + err.message);
        } finally {
            state.isBusy = false;
            if (btnSendPrompt) btnSendPrompt.disabled = false;
            if (promptInput) {
                promptInput.placeholder = 'Pedí cualquier ajuste sobre el guion o las láminas del carrusel...';
            }
        }
    }

    function appendUserTurnCard({ prompt, networks, slideCountPref, swipeStylePref, swipePositionPref, brandAsset, brandText, logoAsset, subjectAssets }) {
        const card = document.createElement('div');
        card.className = 'user-turn-card';

        const countBadge = slideCountPref === 'auto' ? '🎠 Slides: Auto' : `🎠 ${slideCountPref} Láminas`;
        const swipeStyleBadge = SWIPE_STYLE_META[swipeStylePref || 'auto'] || '✨ Deslizador: Auto';
        const swipePosBadge = SWIPE_POSITION_META[swipePositionPref || 'auto'] || '✨ Ubicación: Auto';
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
                <span class="meta-tag">${escapeHtml(countBadge)}</span>
                <span class="meta-tag">${escapeHtml(swipeStyleBadge)}</span>
                <span class="meta-tag">${escapeHtml(swipePosBadge)}</span>
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

    function buildSwipePresetsSelectHtml() {
        const groupsHtml = SWIPE_PRESET_GROUPS.map(g => `
            <optgroup label="${escapeHtml(g.label)}">
                ${g.options.map(opt => `<option value="${escapeHtml(opt)}">${escapeHtml(opt)}</option>`).join('')}
            </optgroup>
        `).join('');
        return `
            <select class="swipe-preset-select" title="Elegí una variante rápida de deslizador/conector para esta lámina">
                <option value="">⚡ Variantes rápidas de deslizador...</option>
                ${groupsHtml}
                <option value="__CLEAR__">🚫 Vaciar (Sin deslizador)</option>
            </select>
        `;
    }

    /**
     * Renderiza la tarjeta del Paso 1 con:
     * 1. Copies por red social
     * 2. ADN Visual Unificado del Carrusel (editable)
     * 3. Lista editable de las N Láminas (Titular, Bajada, Deslizador + Ubicación, Acción y Escena)
     */
    function appendStep1CarouselCard(plan) {
        const card = document.createElement('div');
        card.className = 'step1-card';

        const activeNets = Array.isArray(plan.networks) && plan.networks.length
            ? plan.networks
            : ['ig', 'fb', 'tiktok', 'linkedin'];
        const copies = plan.copies || {};
        const dna = plan.visual_dna || {};
        const slides = Array.isArray(plan.slides) ? plan.slides : [];

        const copiesCardsHtml = activeNets.map(netKey => {
            const netInfo = NETWORK_META[netKey] || { label: netKey.toUpperCase() };
            const copyText = copies[netKey] || '';
            return `
                <div class="copy-network-card">
                    <div class="copy-net-header">
                        <span class="copy-net-name">${escapeHtml(netInfo.label)}</span>
                        <button type="button" class="btn-mini btn-copy-text">📋 Copiar</button>
                    </div>
                    <textarea class="copy-textarea" rows="4">${escapeHtml(copyText)}</textarea>
                </div>
            `;
        }).join('');

        card.innerHTML = `
            <div class="step-header-bar">
                <div class="step-badge-title">
                    <span>📝 Paso 1: Copies + ADN Visual + Guion de ${slides.length} Láminas</span>
                </div>
                <span class="meta-tag">🎠 ${escapeHtml(dna.carousel_format || 'Carrusel Secuencial')}</span>
            </div>

            ${plan.assistant_comment ? `<div class="assistant-note">${escapeHtml(plan.assistant_comment)}</div>` : ''}

            <div class="copies-grid">
                ${copiesCardsHtml}
            </div>

            <!-- ADN Visual Común a todo el carrusel -->
            <div class="visual-dna-box">
                <div class="visual-dna-header">
                    <span class="visual-dna-title">🧬 ADN Visual y Continuidad del Carrusel (Se aplica a todas las láminas)</span>
                    <input type="text" class="design-input input-dna-format" style="max-width: 260px; font-weight:700;" value="${escapeHtml(dna.carousel_format || 'Carrusel Editorial')}" title="Formato dinámico elegido">
                </div>
                <div class="visual-dna-grid">
                    <div class="design-field-group" style="margin-bottom:0;">
                        <label>Sistema Gráfico, Tipografía y Paleta Común:</label>
                        <textarea class="design-textarea input-dna-style" rows="2">${escapeHtml(dna.style_and_typography || '')}</textarea>
                    </div>
                    <div class="design-field-group" style="margin-bottom:0;">
                        <label>Continuidad de Personaje, Vestuario y Escenario:</label>
                        <textarea class="design-textarea input-dna-continuity" rows="2">${escapeHtml(dna.character_and_setting_continuity || '')}</textarea>
                    </div>
                </div>
            </div>

            <!-- Desglose editable de cada Lámina -->
            <div class="slides-plan-header">
                <span class="slides-plan-title">🎬 Secuencia de Láminas (Podés editar textos, elegir variantes de deslizador, su ubicación o escenas antes de crear)</span>
                <button type="button" class="btn-mini btn-add-slide">➕ Agregar Lámina</button>
            </div>

            <div class="slides-plan-list"></div>

            <div class="step2-trigger-bar">
                <div style="font-size:0.77rem; color:#64748b;">
                    🔗 <strong>Anclaje de coherencia activo:</strong> La Lámina 1 define el estilo editorial y se usa como referencia junto con tus fotos originales en las láminas siguientes.
                </div>
                <button type="button" class="btn-sunburst-create btn-generate-all-slides">
                    <span>🎠 Crear Todas las Láminas en Secuencia</span>
                </button>
            </div>
        `;

        // Eventos copiar textos de redes
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

        const slidesListEl = card.querySelector('.slides-plan-list');

        function renderSlidePlanItem(slideObj) {
            const slideEl = document.createElement('div');
            slideEl.className = 'slide-plan-card';
            slideEl.setAttribute('data-slide-num', slideObj.slide_number);

            const slideSwipePos = slideObj.swipe_position
                || (plan.swipe_position_pref && plan.swipe_position_pref !== 'auto' ? plan.swipe_position_pref : 'bottom_right');

            slideEl.innerHTML = `
                <div class="slide-plan-top">
                    <div class="slide-badge-group">
                        <span class="slide-num-pill">Lámina #<span class="lbl-slide-num">${slideObj.slide_number}</span></span>
                        <input type="text" class="slide-role-input input-slide-role" value="${escapeHtml(slideObj.slide_role || '')}" placeholder="Rol (ej. Portada, Mito 1, Verdad, CTA)">
                    </div>
                    <div style="display:flex; gap:0.4rem; align-items:center;">
                        <button type="button" class="btn-mini btn-generate-single-slide" title="Generar o actualizar únicamente esta lámina">
                            🎨 Crear solo esta lámina
                        </button>
                        <button type="button" class="btn-mini btn-remove-slide" title="Eliminar esta lámina de la secuencia" style="color:#be123c;">
                            ✕
                        </button>
                    </div>
                </div>

                <div class="slide-fields-row">
                    <div class="design-field-group" style="margin-bottom:0;">
                        <label>Titular principal en la imagen:</label>
                        <input type="text" class="design-input input-slide-headline" value="${escapeHtml(slideObj.headline || '')}">
                    </div>
                    <div class="design-field-group" style="margin-bottom:0;">
                        <label>Texto secundario / Bajada:</label>
                        <input type="text" class="design-input input-slide-body" value="${escapeHtml(slideObj.body_text || '')}">
                    </div>
                </div>

                <!-- Fila exclusiva para Deslizador / Conector + Variantes rápidas + Ubicación -->
                <div class="slide-swipe-row">
                    <div class="design-field-group" style="margin-bottom:0;">
                        <div class="swipe-label-header">
                            <label>Deslizador / Conector:</label>
                            ${buildSwipePresetsSelectHtml()}
                        </div>
                        <input type="text" class="design-input input-slide-swipe" value="${escapeHtml(slideObj.swipe_indicator || '')}" placeholder="Ej: Deslizá →, ¿Mito o Realidad? →, 01/05 →">
                    </div>
                    <div class="design-field-group" style="margin-bottom:0;">
                        <label>Ubicación del Deslizador en la lámina:</label>
                        <select class="design-input select-slide-swipe-pos">
                            <option value="bottom_right" ${slideSwipePos === 'bottom_right' ? 'selected' : ''}>↘️ Abajo a la derecha (Clásico)</option>
                            <option value="middle_right" ${slideSwipePos === 'middle_right' ? 'selected' : ''}>➡️ Centro-derecha (Pestaña al borde)</option>
                            <option value="bottom_center" ${slideSwipePos === 'bottom_center' ? 'selected' : ''}>⬇️ Abajo centrado (Pie editorial)</option>
                            <option value="top_right" ${slideSwipePos === 'top_right' ? 'selected' : ''}>↗️ Arriba a la derecha (Paginador)</option>
                            <option value="below_text" ${slideSwipePos === 'below_text' ? 'selected' : ''}>📑 Integrado debajo del texto</option>
                            <option value="auto" ${slideSwipePos === 'auto' ? 'selected' : ''}>✨ Libre según composición</option>
                            <option value="none" ${slideSwipePos === 'none' ? 'selected' : ''}>🚫 Sin deslizador en esta lámina</option>
                        </select>
                    </div>
                </div>

                <div class="design-field-group">
                    <label>Plano fotográfico y acción/gesto en esta lámina:</label>
                    <input type="text" class="design-input input-slide-action" value="${escapeHtml(slideObj.camera_and_action || '')}">
                </div>

                <div class="design-field-group" style="margin-bottom:0;">
                    <label>Narrativa visual de la escena para esta lámina (podés mencionar tus fotos como <code>xxxx.jpg</code>):</label>
                    <div class="photo-vars-bar slide-photo-vars-container" style="display:none; margin-bottom:0.4rem;"></div>
                    <textarea class="design-textarea mono textarea-slide-scene" rows="3">${escapeHtml(slideObj.scene_prompt || '')}</textarea>
                </div>
            `;

            const presetSelect = slideEl.querySelector('.swipe-preset-select');
            const swipeInput = slideEl.querySelector('.input-slide-swipe');
            const posSelect = slideEl.querySelector('.select-slide-swipe-pos');
            if (presetSelect && swipeInput) {
                presetSelect.addEventListener('change', () => {
                    const val = presetSelect.value;
                    if (!val) return;
                    if (val === '__CLEAR__') {
                        swipeInput.value = '';
                        if (posSelect) posSelect.value = 'none';
                    } else {
                        swipeInput.value = val;
                        if (posSelect && posSelect.value === 'none') {
                            posSelect.value = 'bottom_right';
                        }
                    }
                    presetSelect.value = '';
                });
            }

            const varsContainer = slideEl.querySelector('.slide-photo-vars-container');
            if (varsContainer) {
                renderSlidePhotoVariablesInto(varsContainer);
            }

            const btnRemove = slideEl.querySelector('.btn-remove-slide');
            if (btnRemove) {
                btnRemove.addEventListener('click', () => {
                    const allCards = slidesListEl.querySelectorAll('.slide-plan-card');
                    if (allCards.length <= 2) {
                        alert('Un carrusel debe tener al menos 2 láminas.');
                        return;
                    }
                    slideEl.remove();
                    renumberSlideCards(card);
                });
            }

            const btnSingle = slideEl.querySelector('.btn-generate-single-slide');
            if (btnSingle) {
                btnSingle.addEventListener('click', async () => {
                    const sNum = parseInt(slideEl.getAttribute('data-slide-num'), 10);
                    await executeSingleSlideFromPlan(card, sNum, btnSingle);
                });
            }

            slidesListEl.appendChild(slideEl);
        }

        slides.forEach(sl => renderSlidePlanItem(sl));

        const btnAddSlide = card.querySelector('.btn-add-slide');
        if (btnAddSlide) {
            btnAddSlide.addEventListener('click', () => {
                const nextNum = slidesListEl.querySelectorAll('.slide-plan-card').length + 1;
                renderSlidePlanItem({
                    slide_number: nextNum,
                    slide_role: `Lámina ${nextNum}`,
                    headline: '',
                    body_text: '',
                    swipe_indicator: 'Deslizá →',
                    swipe_position: state.swipePositionPref !== 'auto' ? state.swipePositionPref : 'bottom_right',
                    camera_and_action: 'Plano medio dinámico manteniendo continuidad con el carrusel',
                    scene_prompt: ''
                });
                renumberSlideCards(card);
            });
        }

        const btnGenAll = card.querySelector('.btn-generate-all-slides');
        if (btnGenAll) {
            btnGenAll.addEventListener('click', async () => {
                await executeSequentialCarouselGeneration(card, btnGenAll);
            });
        }

        threadContainer.appendChild(card);
        return card;
    }

    function renumberSlideCards(step1CardEl) {
        const cards = step1CardEl.querySelectorAll('.slide-plan-card');
        cards.forEach((c, idx) => {
            const num = idx + 1;
            c.setAttribute('data-slide-num', num);
            const lbl = c.querySelector('.lbl-slide-num');
            if (lbl) lbl.textContent = num;
        });
        const btnAll = step1CardEl.querySelector('.btn-generate-all-slides');
        if (btnAll) {
            btnAll.innerHTML = `<span>🎠 Crear las ${cards.length} Láminas en Secuencia</span>`;
        }
    }

    function collectEditedPlanFromStep1Card(step1CardEl) {
        const visualDna = {
            carousel_format: step1CardEl.querySelector('.input-dna-format')?.value.trim() || '',
            style_and_typography: step1CardEl.querySelector('.input-dna-style')?.value.trim() || '',
            character_and_setting_continuity: step1CardEl.querySelector('.input-dna-continuity')?.value.trim() || ''
        };

        const slides = [];
        step1CardEl.querySelectorAll('.slide-plan-card').forEach((c, idx) => {
            const sNum = idx + 1;
            slides.push({
                slide_number: sNum,
                slide_role: c.querySelector('.input-slide-role')?.value.trim() || `Lámina ${sNum}`,
                headline: c.querySelector('.input-slide-headline')?.value.trim() || '',
                body_text: c.querySelector('.input-slide-body')?.value.trim() || '',
                swipe_indicator: c.querySelector('.input-slide-swipe')?.value.trim() || '',
                swipe_position: c.querySelector('.select-slide-swipe-pos')?.value || 'bottom_right',
                camera_and_action: c.querySelector('.input-slide-action')?.value.trim() || '',
                scene_prompt: c.querySelector('.textarea-slide-scene')?.value.trim() || ''
            });
        });

        return { visualDna, slides };
    }

    // =========================================================================
    // 6. PASO 2: ORQUESTADOR SECUENCIAL DE LÁMINAS (1 -> 2 -> ... -> N)
    // =========================================================================
    function ensureStep2GalleryCard(step1CardEl, totalSlides) {
        let galleryCard = step1CardEl._linkedGalleryCard;
        if (!galleryCard || !document.body.contains(galleryCard)) {
            galleryCard = document.createElement('div');
            galleryCard.className = 'step2-card';
            galleryCard.innerHTML = `
                <div class="step-header-bar">
                    <div class="step-badge-title">
                        <span>🎠 Paso 2: Carrusel Generado (<span class="lbl-total-slides">${totalSlides}</span> Láminas — 1080×1350 px)</span>
                    </div>
                    <div class="carousel-header-actions">
                        <button type="button" class="btn-mini btn-open-viewer-modal" style="padding:0.45rem 0.85rem; font-size:0.78rem;" title="Abrir el carrusel en un modal para deslizar las piezas">
                            🎠 Ver en Modal Deslizable
                        </button>
                        <button type="button" class="btn-mini btn-open-viewer-window" style="padding:0.45rem 0.85rem; font-size:0.78rem;" title="Abrir el carrusel deslizable en una nueva ventana independiente">
                            ↗️ Abrir en Nueva Ventana
                        </button>
                        <button type="button" class="btn-primary-action btn-download-all-slides" style="padding:0.45rem 0.95rem; font-size:0.78rem;">
                            ⬇️ Descargar Todo
                        </button>
                    </div>
                </div>

                <div class="carousel-progress-banner">
                    <span class="progress-status-text">Preparando secuencia del carrusel...</span>
                    <span class="progress-counter-badge"></span>
                </div>

                <div class="carousel-showcase-grid"></div>

                <div class="carousel-finished-actions-bar">
                    <div class="carousel-finished-info">
                        <strong>🎠 Vista Previa Deslizable del Carrusel</strong>
                        <span>Abrí el carrusel terminado en un modal o en una nueva ventana para deslizar todas las láminas en secuencia</span>
                    </div>
                    <div class="carousel-finished-buttons">
                        <button type="button" class="btn-primary-action btn-open-viewer-modal-bottom">
                            🎠 Ver Carrusel en Modal (Deslizar Piezas)
                        </button>
                        <button type="button" class="btn-mini btn-open-viewer-window-bottom" style="padding:0.6rem 1.05rem; font-size:0.84rem;">
                            ↗️ Abrir en Nueva Ventana
                        </button>
                        <button type="button" class="btn-mini btn-download-all-bottom" style="padding:0.6rem 1.05rem; font-size:0.84rem;">
                            ⬇️ Descargar Todas
                        </button>
                    </div>
                </div>
            `;

            const bindGalleryActions = () => {
                galleryCard.querySelectorAll('.btn-download-all-slides, .btn-download-all-bottom').forEach(btn => {
                    btn.addEventListener('click', () => downloadAllCarouselSlides(galleryCard));
                });
                galleryCard.querySelectorAll('.btn-open-viewer-modal, .btn-open-viewer-modal-bottom').forEach(btn => {
                    btn.addEventListener('click', () => openCarouselViewerModal(galleryCard, 0));
                });
                galleryCard.querySelectorAll('.btn-open-viewer-window, .btn-open-viewer-window-bottom').forEach(btn => {
                    btn.addEventListener('click', () => openCarouselViewerNewWindow(galleryCard, 0));
                });
            };
            bindGalleryActions();

            threadContainer.appendChild(galleryCard);
            step1CardEl._linkedGalleryCard = galleryCard;
        }

        const lblTotal = galleryCard.querySelector('.lbl-total-slides');
        if (lblTotal) lblTotal.textContent = totalSlides;

        const grid = galleryCard.querySelector('.carousel-showcase-grid');
        for (let i = 1; i <= totalSlides; i++) {
            let slot = grid.querySelector(`.carousel-slide-result-card[data-slide-num="${i}"]`);
            if (!slot) {
                slot = document.createElement('div');
                slot.className = 'carousel-slide-result-card';
                slot.setAttribute('data-slide-num', i);
                slot.innerHTML = `
                    <div class="slide-result-head">
                        <span class="slide-result-title">Lámina #${i}</span>
                        <span class="slide-result-status">Pendiente</span>
                    </div>
                    <div class="slide-frame-box">
                        <div class="slide-placeholder-state">En cola para diseño secuencial...</div>
                    </div>
                    <div class="slide-card-actions" style="display:none;"></div>
                    <div class="slide-direct-edit-box" style="display:none;"></div>
                `;
                grid.appendChild(slot);
            }
        }

        return galleryCard;
    }

    function updateSlideSlotResult(galleryCard, step1CardEl, slideData, imageUrl) {
        const sNum = slideData.slide_number;
        const slot = galleryCard.querySelector(`.carousel-slide-result-card[data-slide-num="${sNum}"]`);
        if (!slot) return;

        const cacheBustUrl = `${imageUrl}?t=${Date.now()}`;
        slot.setAttribute('data-clean-url', imageUrl);
        slot.setAttribute('data-slide-role', slideData.slide_role || '');
        slot.setAttribute('data-slide-headline', slideData.headline || '');

        const headTitle = slot.querySelector('.slide-result-title');
        const headStatus = slot.querySelector('.slide-result-status');
        if (headTitle) headTitle.textContent = `Lámina #${sNum} · ${slideData.slide_role || ''}`;
        if (headStatus) {
            headStatus.textContent = '✅ Lista';
            headStatus.style.color = '#059669';
        }

        const frameBox = slot.querySelector('.slide-frame-box');
        if (frameBox) {
            frameBox.innerHTML = `<img src="${escapeHtml(cacheBustUrl)}" alt="Lámina ${sNum}">`;
            frameBox.classList.add('clickable-slide');
            frameBox.title = `Clic para abrir el visor deslizable en la Lámina #${sNum}`;
            frameBox.onclick = () => {
                const readyList = collectReadySlidesFromGallery(galleryCard);
                const foundIdx = readyList.findIndex(item => item.slideNumber === sNum);
                openCarouselViewerModal(galleryCard, foundIdx >= 0 ? foundIdx : 0);
            };
        }

        const actionsBar = slot.querySelector('.slide-card-actions');
        if (actionsBar) {
            actionsBar.style.display = 'flex';
            actionsBar.innerHTML = `
                <a href="${escapeHtml(cacheBustUrl)}" download="carrusel_slide_0${sNum}.png" class="btn-primary-action">
                    ⬇️ Descargar #${sNum}
                </a>
                <button type="button" class="btn-mini btn-regen-this-slide" title="Generar otra variante de esta lámina manteniendo el estilo del carrusel">
                    🔄 Otra Variante
                </button>
            `;
            const btnRegen = actionsBar.querySelector('.btn-regen-this-slide');
            if (btnRegen) {
                btnRegen.addEventListener('click', async () => {
                    await executeSingleSlideFromPlan(step1CardEl, sNum, btnRegen);
                });
            }
        }

        const editBox = slot.querySelector('.slide-direct-edit-box');
        if (editBox) {
            editBox.style.display = 'flex';
            editBox.innerHTML = `
                <label>✏️ Corregir sobre esta misma Lámina #${sNum}:</label>
                <div class="slide-direct-edit-row">
                    <input type="text" class="design-input input-slide-correction" placeholder="Ej: 'Cambiá tal palabra', 'Mové el texto más arriba'...">
                    <button type="button" class="btn-sunburst-create btn-apply-slide-correction">
                        ✨ Corregir
                    </button>
                </div>
            `;
            const inputCorr = editBox.querySelector('.input-slide-correction');
            const btnCorr = editBox.querySelector('.btn-apply-slide-correction');
            const triggerCorrection = async () => {
                const instr = inputCorr ? inputCorr.value.trim() : '';
                if (!instr) {
                    alert(`Escribí qué ajuste querés aplicar sobre la Lámina #${sNum}.`);
                    if (inputCorr) inputCorr.focus();
                    return;
                }
                await executeSingleSlideFromPlan(step1CardEl, sNum, btnCorr, {
                    isSlideCorrection: true,
                    editInstruction: instr,
                    baseSlideUrl: imageUrl
                });
            };
            if (btnCorr) btnCorr.addEventListener('click', triggerCorrection);
            if (inputCorr) {
                inputCorr.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        triggerCorrection();
                    }
                });
            }
        }
    }

    function setSlideSlotLoading(galleryCard, sNum, message) {
        const slot = galleryCard.querySelector(`.carousel-slide-result-card[data-slide-num="${sNum}"]`);
        if (!slot) return;
        const headStatus = slot.querySelector('.slide-result-status');
        if (headStatus) {
            headStatus.textContent = '⏳ Creando...';
            headStatus.style.color = '#0052E0';
        }
        const frameBox = slot.querySelector('.slide-frame-box');
        if (frameBox) {
            frameBox.innerHTML = `<div class="slide-placeholder-state">🎨 ${escapeHtml(message)}</div>`;
        }
    }

    function setSlideSlotError(galleryCard, sNum, errMsg) {
        const slot = galleryCard.querySelector(`.carousel-slide-result-card[data-slide-num="${sNum}"]`);
        if (!slot) return;
        const headStatus = slot.querySelector('.slide-result-status');
        if (headStatus) {
            headStatus.textContent = '⚠️ Error';
            headStatus.style.color = '#DC2626';
        }
        const frameBox = slot.querySelector('.slide-frame-box');
        if (frameBox) {
            frameBox.innerHTML = `<div class="slide-placeholder-state" style="color:#b91c1c;">⚠️ ${escapeHtml(errMsg)}</div>`;
        }
    }

    async function executeSequentialCarouselGeneration(step1CardEl, triggerButton) {
        const { visualDna, slides } = collectEditedPlanFromStep1Card(step1CardEl);
        if (!slides.length) {
            alert('No hay láminas definidas para generar.');
            return;
        }

        const origBtnHtml = triggerButton ? triggerButton.innerHTML : '';
        if (triggerButton) {
            triggerButton.disabled = true;
        }

        const totalSlides = slides.length;
        const galleryCard = ensureStep2GalleryCard(step1CardEl, totalSlides);
        const statusTextEl = galleryCard.querySelector('.progress-status-text');
        const counterBadgeEl = galleryCard.querySelector('.progress-counter-badge');
        scrollToBottom();

        let anchorSlide1Url = state.slideImages[1]?.url || '';
        let prevSlideUrl = '';

        for (let i = 0; i < totalSlides; i++) {
            const sl = slides[i];
            const sNum = sl.slide_number;

            if (triggerButton) {
                triggerButton.innerHTML = `<span>⏳ Creando Lámina ${sNum} de ${totalSlides}...</span>`;
            }
            if (statusTextEl) {
                statusTextEl.textContent = sNum === 1
                    ? `🎨 Diseñando Lámina 1 de ${totalSlides} (Portada y sistema visual base del carrusel)...`
                    : `🔗 Diseñando Lámina ${sNum} de ${totalSlides} (Anclando estilo de Portada + fotos originales)...`;
            }
            if (counterBadgeEl) {
                counterBadgeEl.textContent = `${sNum} / ${totalSlides}`;
            }

            setSlideSlotLoading(
                galleryCard,
                sNum,
                sNum === 1
                    ? 'Generando Portada (Slide 1)...'
                    : `Generando Lámina #${sNum} manteniendo coherencia con Slide 1...`
            );

            try {
                const res = await fetch('api_carruseles.php?action=step2_generate_slide', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        carousel_id: state.currentCarouselId,
                        slide_number: sNum,
                        total_slides: totalSlides,
                        slide_role: sl.slide_role,
                        headline: sl.headline,
                        body_text: sl.body_text,
                        swipe_indicator: sl.swipe_indicator,
                        swipe_position: sl.swipe_position || state.swipePositionPref || 'bottom_right',
                        swipe_style_pref: state.swipeStylePref || 'auto',
                        camera_and_action: sl.camera_and_action,
                        scene_prompt: sl.scene_prompt,
                        visual_dna: visualDna,
                        all_slides_overview: slides,
                        anchor_slide1_url: sNum > 1 ? anchorSlide1Url : '',
                        prev_slide_url: sNum > 2 ? prevSlideUrl : '',
                        model: state.selectedImageModel,
                        brand_asset: state.brandAsset,
                        brand_text: state.brandText,
                        logo_asset: state.logoAsset,
                        subject_assets: state.subjectAssets,
                        stamp_logo: state.stampLogoOverlay
                    })
                });
                const data = await res.json();

                if (data.status === 'ok' && data.image_url) {
                    state.slideImages[sNum] = {
                        url: data.image_url,
                        slide_number: sNum
                    };
                    if (sNum === 1) {
                        anchorSlide1Url = data.image_url;
                    }
                    prevSlideUrl = data.image_url;
                    updateSlideSlotResult(galleryCard, step1CardEl, sl, data.image_url);
                } else {
                    setSlideSlotError(galleryCard, sNum, data.message || 'Error al generar lámina');
                }
            } catch (err) {
                setSlideSlotError(galleryCard, sNum, 'Error de red: ' + err.message);
            }
        }

        if (statusTextEl) {
            statusTextEl.textContent = `✨ Secuencia completada (${totalSlides} láminas). Podés abrir el carrusel en Modal o Nueva Ventana para deslizar las piezas.`;
        }
        if (counterBadgeEl) {
            counterBadgeEl.textContent = `✅ ${totalSlides} / ${totalSlides}`;
        }
        if (triggerButton) {
            triggerButton.disabled = false;
            triggerButton.innerHTML = origBtnHtml || `<span>🎠 Volver a Generar Todas las Láminas</span>`;
        }

        await loadCarouselsHistory();
    }

    async function executeSingleSlideFromPlan(step1CardEl, slideNumber, triggerButton, correctionOpts = null) {
        const { visualDna, slides } = collectEditedPlanFromStep1Card(step1CardEl);
        const totalSlides = slides.length;
        const sl = slides.find(s => s.slide_number === slideNumber);
        if (!sl) return;

        const origBtnHtml = triggerButton ? triggerButton.innerHTML : '';
        if (triggerButton) {
            triggerButton.disabled = true;
            triggerButton.innerHTML = '⏳...';
        }

        const galleryCard = ensureStep2GalleryCard(step1CardEl, totalSlides);
        const statusTextEl = galleryCard.querySelector('.progress-status-text');
        const isCorr = correctionOpts && correctionOpts.isSlideCorrection;

        if (statusTextEl) {
            statusTextEl.textContent = isCorr
                ? `✏️ Aplicando corrección sobre la Lámina #${slideNumber}...`
                : `🎨 Generando Lámina #${slideNumber} de ${totalSlides} manteniendo coherencia con el carrusel...`;
        }

        setSlideSlotLoading(
            galleryCard,
            slideNumber,
            isCorr ? `Aplicando tu ajuste en Lámina #${slideNumber}...` : `Diseñando Lámina #${slideNumber}...`
        );

        const anchorSlide1Url = state.slideImages[1]?.url || '';
        const prevSlideUrl = slideNumber > 2 ? (state.slideImages[slideNumber - 1]?.url || '') : '';

        try {
            const res = await fetch('api_carruseles.php?action=step2_generate_slide', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    carousel_id: state.currentCarouselId,
                    slide_number: slideNumber,
                    total_slides: totalSlides,
                    slide_role: sl.slide_role,
                    headline: sl.headline,
                    body_text: sl.body_text,
                    swipe_indicator: sl.swipe_indicator,
                    swipe_position: sl.swipe_position || state.swipePositionPref || 'bottom_right',
                    swipe_style_pref: state.swipeStylePref || 'auto',
                    camera_and_action: sl.camera_and_action,
                    scene_prompt: sl.scene_prompt,
                    visual_dna: visualDna,
                    all_slides_overview: slides,
                    is_slide_correction: isCorr ? 1 : 0,
                    edit_instruction: isCorr ? correctionOpts.editInstruction : '',
                    base_slide_url: isCorr ? correctionOpts.baseSlideUrl : '',
                    anchor_slide1_url: slideNumber > 1 ? anchorSlide1Url : '',
                    prev_slide_url: slideNumber > 2 ? prevSlideUrl : '',
                    model: state.selectedImageModel,
                    brand_asset: state.brandAsset,
                    brand_text: state.brandText,
                    logo_asset: state.logoAsset,
                    subject_assets: state.subjectAssets,
                    stamp_logo: state.stampLogoOverlay
                })
            });
            const data = await res.json();
            if (data.status === 'ok' && data.image_url) {
                state.slideImages[slideNumber] = {
                    url: data.image_url,
                    slide_number: slideNumber
                };
                updateSlideSlotResult(galleryCard, step1CardEl, sl, data.image_url);
                if (statusTextEl) {
                    statusTextEl.textContent = `✨ Lámina #${slideNumber} actualizada correctamente.`;
                }
                await loadCarouselsHistory();
            } else {
                setSlideSlotError(galleryCard, slideNumber, data.message || 'Error al generar lámina');
            }
        } catch (err) {
            setSlideSlotError(galleryCard, slideNumber, 'Error de red: ' + err.message);
        } finally {
            if (triggerButton) {
                triggerButton.disabled = false;
                triggerButton.innerHTML = origBtnHtml;
            }
        }
    }

    function downloadAllCarouselSlides(galleryCard) {
        const slots = galleryCard.querySelectorAll('.carousel-slide-result-card');
        let downloaded = 0;
        slots.forEach((slot, idx) => {
            const cleanUrl = slot.getAttribute('data-clean-url');
            if (!cleanUrl) return;
            downloaded++;
            setTimeout(() => {
                const a = document.createElement('a');
                a.href = `${cleanUrl}?t=${Date.now()}`;
                a.download = `carrusel_slide_${String(idx + 1).padStart(2, '0')}.png`;
                document.body.appendChild(a);
                a.click();
                a.remove();
            }, idx * 280);
        });
        if (downloaded === 0) {
            alert('Todavía no hay láminas generadas para descargar.');
        }
    }

    // =========================================================================
    // 6B. VISOR INTERACTIVO DESLIZABLE (MODAL + NUEVA VENTANA)
    // =========================================================================
    let activeViewerState = {
        galleryCard: null,
        slides: [],
        currentIndex: 0,
        dragStartX: null
    };

    function collectReadySlidesFromGallery(galleryCard) {
        const list = [];
        if (!galleryCard) return list;
        const slots = galleryCard.querySelectorAll('.carousel-slide-result-card');
        slots.forEach(slot => {
            const cleanUrl = slot.getAttribute('data-clean-url');
            if (!cleanUrl) return;
            const sNum = parseInt(slot.getAttribute('data-slide-num'), 10) || (list.length + 1);
            const role = slot.getAttribute('data-slide-role') || `Lámina #${sNum}`;
            const headline = slot.getAttribute('data-slide-headline') || '';
            const imgEl = slot.querySelector('.slide-frame-box img');
            const displayUrl = imgEl ? imgEl.src : `${cleanUrl}?t=${Date.now()}`;
            list.push({
                slideNumber: sNum,
                cleanUrl,
                displayUrl,
                role,
                headline
            });
        });
        return list;
    }

    function renderViewerSlideAt(index) {
        const slides = activeViewerState.slides;
        if (!slides.length) return;
        const clamped = Math.max(0, Math.min(slides.length - 1, index));
        activeViewerState.currentIndex = clamped;

        if (viewerCarouselTrack) {
            viewerCarouselTrack.style.transform = `translateX(-${clamped * 100}%)`;
        }
        if (viewerCounterPill) {
            viewerCounterPill.textContent = `${clamped + 1} / ${slides.length}`;
        }
        if (btnViewerPrev) {
            btnViewerPrev.disabled = (clamped === 0);
        }
        if (btnViewerNext) {
            btnViewerNext.disabled = (clamped === slides.length - 1);
        }
        if (viewerCarouselDots) {
            viewerCarouselDots.querySelectorAll('.carousel-dot').forEach((dot, idx) => {
                dot.classList.toggle('active', idx === clamped);
            });
        }
        const cur = slides[clamped];
        if (viewerSlideCaption && cur) {
            viewerSlideCaption.textContent = `Lámina #${cur.slideNumber}${cur.role ? ' · ' + cur.role : ''}${cur.headline ? ' — "' + cur.headline + '"' : ''}`;
        }
        if (btnViewerDownloadCurrent && cur) {
            btnViewerDownloadCurrent.href = cur.displayUrl;
            btnViewerDownloadCurrent.download = `carrusel_slide_${String(cur.slideNumber).padStart(2, '0')}.png`;
        }
    }

    function openCarouselViewerModal(galleryCard, startIndex = 0) {
        const slides = collectReadySlidesFromGallery(galleryCard);
        if (!slides.length) {
            alert('Todavía no hay láminas generadas en este carrusel para visualizar.');
            return;
        }

        activeViewerState.galleryCard = galleryCard;
        activeViewerState.slides = slides;
        activeViewerState.currentIndex = Math.max(0, Math.min(slides.length - 1, startIndex));

        if (viewerCarouselTitle) {
            viewerCarouselTitle.textContent = (currentCarouselTitle ? currentCarouselTitle.textContent : 'Carrusel') + ` (${slides.length} láminas)`;
        }

        if (viewerCarouselTrack) {
            viewerCarouselTrack.innerHTML = slides.map(s => `
                <div class="carousel-slide-item">
                    <img src="${escapeHtml(s.displayUrl)}" alt="Lámina ${s.slideNumber}">
                </div>
            `).join('');
        }

        if (viewerCarouselDots) {
            viewerCarouselDots.innerHTML = slides.map((_, idx) => `
                <button type="button" class="carousel-dot ${idx === activeViewerState.currentIndex ? 'active' : ''}" data-dot-idx="${idx}" title="Ir a lámina #${idx + 1}"></button>
            `).join('');
            viewerCarouselDots.querySelectorAll('.carousel-dot').forEach(dot => {
                dot.addEventListener('click', () => {
                    const idx = parseInt(dot.getAttribute('data-dot-idx'), 10);
                    renderViewerSlideAt(idx);
                });
            });
        }

        renderViewerSlideAt(activeViewerState.currentIndex);
        if (modalCarouselViewer) {
            modalCarouselViewer.style.display = 'flex';
        }
    }

    function closeCarouselViewerModal() {
        if (modalCarouselViewer) {
            modalCarouselViewer.style.display = 'none';
        }
    }

    if (btnCloseViewer) {
        btnCloseViewer.addEventListener('click', closeCarouselViewerModal);
    }
    if (modalCarouselViewer) {
        modalCarouselViewer.addEventListener('click', (e) => {
            if (e.target === modalCarouselViewer) closeCarouselViewerModal();
        });
    }
    if (btnViewerPrev) {
        btnViewerPrev.addEventListener('click', () => {
            renderViewerSlideAt(activeViewerState.currentIndex - 1);
        });
    }
    if (btnViewerNext) {
        btnViewerNext.addEventListener('click', () => {
            renderViewerSlideAt(activeViewerState.currentIndex + 1);
        });
    }
    if (btnViewerOpenPopup) {
        btnViewerOpenPopup.addEventListener('click', () => {
            if (activeViewerState.galleryCard) {
                openCarouselViewerNewWindow(activeViewerState.galleryCard, activeViewerState.currentIndex);
            }
        });
    }

    // Soporte de teclado (← → Esc) y arrastre/swipe con mouse o pantalla táctil en el modal
    document.addEventListener('keydown', (e) => {
        if (!modalCarouselViewer || modalCarouselViewer.style.display === 'none') return;
        if (e.key === 'Escape') {
            closeCarouselViewerModal();
        } else if (e.key === 'ArrowLeft') {
            renderViewerSlideAt(activeViewerState.currentIndex - 1);
        } else if (e.key === 'ArrowRight') {
            renderViewerSlideAt(activeViewerState.currentIndex + 1);
        }
    });

    if (carouselViewportFrame) {
        carouselViewportFrame.addEventListener('pointerdown', (e) => {
            activeViewerState.dragStartX = e.clientX;
        });
        carouselViewportFrame.addEventListener('pointerup', (e) => {
            if (activeViewerState.dragStartX === null) return;
            const deltaX = e.clientX - activeViewerState.dragStartX;
            activeViewerState.dragStartX = null;
            if (Math.abs(deltaX) > 40) {
                if (deltaX < 0) {
                    renderViewerSlideAt(activeViewerState.currentIndex + 1);
                } else {
                    renderViewerSlideAt(activeViewerState.currentIndex - 1);
                }
            }
        });
        carouselViewportFrame.addEventListener('pointerleave', () => {
            activeViewerState.dragStartX = null;
        });
    }

    function openCarouselViewerNewWindow(galleryCard, startIndex = 0) {
        const slides = collectReadySlidesFromGallery(galleryCard);
        if (!slides.length) {
            alert('Todavía no hay láminas generadas en este carrusel para visualizar.');
            return;
        }

        const titleText = currentCarouselTitle ? currentCarouselTitle.textContent : 'Carrusel TecnoGen Studio';
        const baseHref = window.location.href.replace(/\/[^/]*$/, '/');
        const slidesPayload = slides.map(s => ({
            slideNumber: s.slideNumber,
            url: s.displayUrl.startsWith('http') ? s.displayUrl : (baseHref + s.displayUrl.replace(/^\//, '')),
            role: s.role,
            headline: s.headline
        }));

        const win = window.open('', '_blank', 'width=720,height=880,resizable=yes,scrollbars=yes');
        if (!win) {
            alert('Tu navegador bloqueó la ventana emergente. Permití ventanas emergentes para este sitio o usá el botón "Ver en Modal Deslizable".');
            return;
        }

        const htmlContent = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>${escapeHtml(titleText)} — Visor de Carrusel</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #0B132B;
    color: #F8FAFC;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem;
    user-select: none;
  }
  .top-bar {
    width: 100%;
    max-width: 620px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 0.75rem;
  }
  .top-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #FFFFFF;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .stage {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    width: 100%;
    max-width: 640px;
    flex: 1;
  }
  .viewport {
    width: 100%;
    max-width: 470px;
    aspect-ratio: 4 / 5;
    max-height: 74vh;
    background: #050B18;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255,255,255,0.16);
    box-shadow: 0 20px 50px rgba(0,0,0,0.55);
    cursor: grab;
    touch-action: pan-y;
  }
  .viewport:active { cursor: grabbing; }
  .counter-pill {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 10;
    background: rgba(15, 23, 42, 0.82);
    color: #FFFFFF;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.28rem 0.7rem;
    border-radius: 9999px;
    border: 1px solid rgba(255,255,255,0.15);
  }
  .track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.34s cubic-bezier(0.22, 1, 0.36, 1);
  }
  .slide {
    min-width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .slide img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    pointer-events: none;
  }
  .arrow {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.22);
    background: rgba(255,255,255,0.12);
    color: #FFFFFF;
    font-size: 1.7rem;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.16s ease;
    flex-shrink: 0;
  }
  .arrow:hover:not(:disabled) {
    background: linear-gradient(135deg, #0052E0 0%, #1973FF 100%);
    border-color: transparent;
    transform: scale(1.06);
  }
  .arrow:disabled { opacity: 0.28; cursor: not-allowed; }
  .dots {
    display: flex;
    gap: 0.45rem;
    margin: 0.75rem 0 0.5rem 0;
  }
  .dot {
    width: 9px;
    height: 9px;
    border-radius: 9999px;
    border: none;
    background: rgba(255,255,255,0.32);
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .dot.active {
    width: 26px;
    background: #38BDF8;
  }
  .bottom-bar {
    width: 100%;
    max-width: 620px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.65rem;
    padding-top: 0.65rem;
    border-top: 1px solid rgba(255,255,255,0.14);
  }
  .caption {
    font-size: 0.8rem;
    color: #CBD5E1;
    font-weight: 600;
  }
  .btn {
    background: linear-gradient(135deg, #0052E0 0%, #1973FF 100%);
    color: #FFFFFF;
    text-decoration: none;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 9px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }
</style>
</head>
<body>
  <div class="top-bar">
    <div class="top-title">🎠 ${escapeHtml(titleText)}</div>
    <span style="font-size:0.75rem; color:#94A3B8;">Deslizá con el mouse o usá las flechas ← →</span>
  </div>
  <div class="stage">
    <button type="button" id="prevBtn" class="arrow" title="Anterior (←)">‹</button>
    <div class="viewport" id="viewport">
      <div class="counter-pill" id="counterPill">1 / ${slidesPayload.length}</div>
      <div class="track" id="track"></div>
    </div>
    <button type="button" id="nextBtn" class="arrow" title="Siguiente (→)">›</button>
  </div>
  <div class="dots" id="dots"></div>
  <div class="bottom-bar">
    <div class="caption" id="caption"></div>
    <a id="downloadBtn" class="btn" href="#" download="carrusel_slide_01.png">⬇️ Descargar esta lámina</a>
  </div>
  <script>
    const slides = ${JSON.stringify(slidesPayload)};
    let current = ${Math.max(0, Math.min(slidesPayload.length - 1, startIndex))};
    const track = document.getElementById('track');
    const dots = document.getElementById('dots');
    const counterPill = document.getElementById('counterPill');
    const caption = document.getElementById('caption');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const viewport = document.getElementById('viewport');

    track.innerHTML = slides.map(s => '<div class="slide"><img src="' + s.url + '" alt="Slide ' + s.slideNumber + '"></div>').join('');
    dots.innerHTML = slides.map((_, i) => '<button type="button" class="dot" data-idx="' + i + '"></button>').join('');

    function goTo(idx) {
      current = Math.max(0, Math.min(slides.length - 1, idx));
      track.style.transform = 'translateX(-' + (current * 100) + '%)';
      counterPill.textContent = (current + 1) + ' / ' + slides.length;
      prevBtn.disabled = current === 0;
      nextBtn.disabled = current === slides.length - 1;
      Array.from(dots.children).forEach((d, i) => d.classList.toggle('active', i === current));
      const s = slides[current];
      caption.textContent = 'Lámina #' + s.slideNumber + (s.role ? ' · ' + s.role : '') + (s.headline ? ' — "' + s.headline + '"' : '');
      downloadBtn.href = s.url;
      downloadBtn.download = 'carrusel_slide_' + String(s.slideNumber).padStart(2, '0') + '.png';
    }

    prevBtn.addEventListener('click', () => goTo(current - 1));
    nextBtn.addEventListener('click', () => goTo(current + 1));
    Array.from(dots.children).forEach(d => d.addEventListener('click', () => goTo(parseInt(d.dataset.idx, 10))));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') goTo(current - 1);
      if (e.key === 'ArrowRight') goTo(current + 1);
    });
    let startX = null;
    viewport.addEventListener('pointerdown', (e) => { startX = e.clientX; });
    viewport.addEventListener('pointerup', (e) => {
      if (startX === null) return;
      const dx = e.clientX - startX;
      startX = null;
      if (Math.abs(dx) > 40) goTo(current + (dx < 0 ? 1 : -1));
    });
    goTo(current);
  <\/script>
</body>
</html>`;

        win.document.open();
        win.document.write(htmlContent);
        win.document.close();
    }

    // =========================================================================
    // 7. HISTORIAL Y BORRADO DE CARRUSELES
    // =========================================================================
    async function deleteCarouselById(cid) {
        if (!cid) return;
        if (!confirm('¿Eliminar este carrusel y borrar todas sus láminas del servidor?')) {
            return;
        }
        try {
            const res = await fetch('api_carruseles.php?action=delete_carousel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ carousel_id: cid })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                if (state.currentCarouselId === cid) {
                    startNewCarouselSession();
                }
                await loadCarouselsHistory();
            } else {
                alert(data.message || 'No se pudo eliminar el carrusel.');
            }
        } catch (err) {
            alert('Error de red al eliminar carrusel: ' + err.message);
        }
    }

    if (btnDeleteActiveCarousel) {
        btnDeleteActiveCarousel.addEventListener('click', async () => {
            if (!state.currentCarouselId) return;
            await deleteCarouselById(state.currentCarouselId);
        });
    }

    if (btnClearAllCarousels) {
        btnClearAllCarousels.addEventListener('click', async () => {
            if (!confirm('¿Eliminar TODOS los carruseles guardados y borrar todos sus archivos del servidor?')) {
                return;
            }
            try {
                const res = await fetch('api_carruseles.php?action=delete_all_carousels', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    startNewCarouselSession();
                    await loadCarouselsHistory();
                }
            } catch (err) {
                alert('Error al vaciar historial: ' + err.message);
            }
        });
    }

    async function loadCarouselsHistory() {
        if (!carouselsHistoryList) return;
        try {
            const res = await fetch('api_carruseles.php?action=list_carousels');
            const data = await res.json();
            if (data.status !== 'ok' || !Array.isArray(data.carousels)) return;

            if (btnClearAllCarousels) {
                btnClearAllCarousels.style.display = data.carousels.length > 0 ? 'inline-block' : 'none';
            }

            if (data.carousels.length === 0) {
                carouselsHistoryList.innerHTML = `<div style="font-size:0.78rem; color:#64748b; padding:0.5rem;">Todavía no hay carruseles guardados.</div>`;
                return;
            }

            carouselsHistoryList.innerHTML = data.carousels.map(c => {
                const isActive = c.carousel_id === state.currentCarouselId;
                const thumbHtml = c.cover_image
                    ? `<img src="${escapeHtml(c.cover_image)}" class="history-thumb" alt="">`
                    : `<div class="history-thumb" style="display:flex;align-items:center;justify-content:center;font-size:0.85rem;">🎠</div>`;

                return `
                    <div class="history-item ${isActive ? 'active' : ''}" data-carousel-id="${escapeHtml(c.carousel_id)}">
                        <div class="history-item-left">
                            ${thumbHtml}
                            <div class="history-meta">
                                <div class="history-title">${escapeHtml(c.title || 'Carrusel')}</div>
                                <div class="history-sub">${escapeHtml(c.slides_count || 0)} láminas · ${escapeHtml(c.updated_at || '')}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-del-history" data-del-id="${escapeHtml(c.carousel_id)}" title="Eliminar carrusel">🗑️</button>
                    </div>
                `;
            }).join('');

            carouselsHistoryList.querySelectorAll('.history-item').forEach(item => {
                item.addEventListener('click', async (e) => {
                    if (e.target.closest('.btn-del-history')) return;
                    const cid = item.getAttribute('data-carousel-id');
                    if (cid) await openSavedCarousel(cid);
                });
            });

            carouselsHistoryList.querySelectorAll('.btn-del-history').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const cid = btn.getAttribute('data-del-id');
                    if (cid) await deleteCarouselById(cid);
                });
            });
        } catch (err) {
            console.error('Error cargando historial de carruseles:', err);
        }
    }

    function highlightActiveHistoryItem() {
        if (!carouselsHistoryList) return;
        carouselsHistoryList.querySelectorAll('.history-item').forEach(el => {
            el.classList.toggle('active', el.getAttribute('data-carousel-id') === state.currentCarouselId);
        });
    }

    async function openSavedCarousel(carouselId) {
        try {
            const res = await fetch(`api_carruseles.php?action=get_carousel&carousel_id=${encodeURIComponent(carouselId)}`);
            const data = await res.json();
            if (data.status !== 'ok' || !data.carousel) return;

            const c = data.carousel;
            state.currentCarouselId = c.carousel_id;
            state.lastPlan = c;
            state.slideImages = c.slide_images || {};
            syncActiveThreadButtons();

            state.networks = Array.isArray(c.networks) && c.networks.length ? c.networks : ['ig', 'fb', 'tiktok', 'linkedin'];
            syncNetworkButtonsUI();

            state.slideCountPref = c.slide_count_pref || 'auto';
            if (selectSlideCount) selectSlideCount.value = state.slideCountPref;

            state.swipeStylePref = c.swipe_style_pref || 'auto';
            if (selectSwipeStyle) selectSwipeStyle.value = state.swipeStylePref;

            state.swipePositionPref = c.swipe_position_pref || 'auto';
            if (selectSwipePosition) selectSwipePosition.value = state.swipePositionPref;

            if ('brand_asset' in c) state.brandAsset = c.brand_asset || null;
            if ('brand_text' in c) state.brandText = typeof c.brand_text === 'string' ? c.brand_text : '';
            if ('logo_asset' in c) state.logoAsset = c.logo_asset || null;
            state.subjectAssets = Array.isArray(c.subject_assets) ? c.subject_assets : [];

            renderBrandPreview();
            renderLogoPreview();
            renderSubjectPreviews();

            if (currentCarouselTitle) currentCarouselTitle.textContent = c.title || 'Carrusel';
            if (threadContainer) {
                threadContainer.innerHTML = '';
                threadContainer.style.display = 'flex';
            }

            if (c.last_idea) {
                appendUserTurnCard({
                    prompt: c.last_idea,
                    networks: state.networks,
                    slideCountPref: state.slideCountPref,
                    swipeStylePref: state.swipeStylePref,
                    swipePositionPref: state.swipePositionPref,
                    brandAsset: state.brandAsset,
                    brandText: state.brandText,
                    logoAsset: state.logoAsset,
                    subjectAssets: state.subjectAssets
                });
            }

            const step1CardEl = appendStep1CarouselCard(c);

            const slideImagesObj = c.slide_images || {};
            const slideKeys = Object.keys(slideImagesObj);
            if (slideKeys.length > 0 && Array.isArray(c.slides) && c.slides.length > 0) {
                const galleryCard = ensureStep2GalleryCard(step1CardEl, c.slides.length);
                const statusTextEl = galleryCard.querySelector('.progress-status-text');
                const counterBadgeEl = galleryCard.querySelector('.progress-counter-badge');
                if (statusTextEl) {
                    statusTextEl.textContent = `✨ Carrusel guardado (${slideKeys.length} de ${c.slides.length} láminas generadas).`;
                }
                if (counterBadgeEl) {
                    counterBadgeEl.textContent = `${slideKeys.length} / ${c.slides.length}`;
                }
                c.slides.forEach(sl => {
                    const savedImg = slideImagesObj[sl.slide_number];
                    if (savedImg && savedImg.url) {
                        updateSlideSlotResult(galleryCard, step1CardEl, sl, savedImg.url);
                    }
                });
            }

            highlightActiveHistoryItem();
            scrollToBottom();
        } catch (err) {
            console.error('Error abriendo carrusel guardado:', err);
        }
    }

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================
    loadConfig();
    loadCarouselsHistory();
});
