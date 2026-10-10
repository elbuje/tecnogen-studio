@extends('layouts.app')

@section('title', 'Personajes - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-heading font-bold text-slate-900">
                        Personajes y Profesionales
                        <span class="ml-2 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800">
                            {{ count($characters) }} Registrados
                        </span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Administra los profesionales vinculados directamente a sus carpetas en <strong>Google Drive</strong>.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(session('success'))
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                    {{ session('success') }}
                </span>
            @endif

            @if(session('error'))
                <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-200">
                    {{ session('error') }}
                </span>
            @endif

            <button onclick="openNewCharacterModal()" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Personaje</span>
            </button>
        </div>
    </div>

    <!-- Grid de Personajes -->
    @if(count($characters) === 0)
        <div class="saas-card p-12 text-center max-w-lg mx-auto">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 font-heading">No tienes personajes registrados</h3>
            <p class="text-xs text-slate-500 mt-1">
                Registra un profesional ingresando su nombre y el enlace a su carpeta de fotos en Google Drive.
            </p>
            <button onclick="openNewCharacterModal()" class="mt-4 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-colors cursor-pointer">
                Dar de Alta Personaje
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($characters as $char)
                <div class="saas-card overflow-hidden flex flex-col justify-between group transition-all hover:border-sky-300">
                    <div>
                        <!-- Carátula Fotográfica Clickeable para abrir el Visor -->
                        <div class="h-64 bg-slate-900 overflow-hidden relative cursor-pointer group"
                             onclick="openCharacterViewer('{{ $char['id'] }}')">
                            @if(!empty($char['cover_url']))
                                <img id="card-cover-{{ $char['id'] }}"
                                     src="{{ $char['cover_url'] }}"
                                     alt="{{ $char['name'] }}"
                                     class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 p-4 bg-slate-50">
                                    <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-semibold text-slate-500">Carpeta de Google Drive</span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-1">Fotos en la nube</span>
                                </div>
                            @endif

                            <!-- Badge de cantidad de fotos / Google Drive -->
                            <div class="absolute bottom-3 right-3 bg-slate-900/85 backdrop-blur-xs text-white px-2.5 py-1 rounded-md text-[11px] font-bold flex items-center gap-1.5 shadow-sm z-10">
                                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ count($char['photos']) > 0 ? count($char['photos']) . ' Fotos' : 'Drive Conectado' }}</span>
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10">
                                <span class="px-3 py-1.5 bg-white/95 text-slate-800 text-xs font-bold rounded-lg shadow-md flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Ver Visor de Fotos
                                </span>
                            </div>
                        </div>

                        <!-- Información del Personaje -->
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-heading font-bold text-base text-slate-900 line-clamp-1">
                                        {{ $char['name'] }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium line-clamp-1">
                                        {{ $char['role'] ?? 'Profesional Clínico' }}
                                    </p>
                                </div>
                            </div>

                            @if(!empty($char['notes']))
                                <p class="text-[11px] text-slate-600 line-clamp-2 bg-slate-50 p-2 rounded-lg border border-slate-100 italic">
                                    "{{ $char['notes'] }}"
                                </p>
                            @endif

                            @if(!empty($char['gdrive_folder_url']))
                                <div class="pt-1">
                                    <a href="{{ $char['gdrive_folder_url'] }}" target="_blank" rel="noopener noreferrer"
                                       class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100/70 px-2.5 py-1.5 rounded-lg border border-sky-100 flex items-center justify-between transition-colors">
                                        <span class="flex items-center gap-1.5 truncate">
                                            <span>📁</span>
                                            <span class="truncate">Google Drive</span>
                                        </span>
                                        <span class="text-sky-500 font-bold shrink-0">↗</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Acciones en el pie de tarjeta -->
                    <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2">
                        <button onclick="openCharacterViewer('{{ $char['id'] }}')"
                                class="text-xs font-bold text-sky-600 hover:text-sky-700 py-1.5 flex items-center gap-1 cursor-pointer">
                            <span>Visor de Fotos</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <div class="flex items-center gap-1">
                            <button onclick="openEditCharacterModal('{{ $char['id'] }}', '{{ addslashes($char['name']) }}', '{{ addslashes($char['role'] ?? '') }}', '{{ addslashes($char['gdrive_folder_url'] ?? '') }}', '{{ addslashes($char['notes'] ?? '') }}')"
                                    class="p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100 transition-colors cursor-pointer"
                                    title="Editar datos del personaje">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <form action="/app/characters/{{ $char['id'] }}/delete" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a {{ $char['name'] }}?');">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors cursor-pointer"
                                        title="Eliminar personaje">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Visor Interactivo de Fotos del Personaje (Slider con Desplazador) -->
<div id="characterViewerModal" class="fixed inset-0 bg-slate-900/85 backdrop-blur-xs hidden z-50 flex items-center justify-center p-3 sm:p-6" onclick="closeCharacterViewer()">
    <div class="bg-white rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col relative animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
        <!-- Header del Visor -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-white z-10">
            <div>
                <h3 id="viewerCharName" class="text-base font-bold text-slate-900 font-heading"></h3>
                <p id="viewerPhotoFilename" class="text-xs text-slate-500 font-mono mt-0.5"></p>
            </div>

            <div class="flex items-center gap-3">
                <span id="viewerCounter" class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md"></span>
                <button onclick="closeCharacterViewer()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm transition-colors cursor-pointer">
                    ✕
                </button>
            </div>
        </div>

        <!-- Área Central de Imagen con Flechas de Navegación -->
        <div id="viewerSwipeArea" class="relative p-2 sm:p-4 bg-slate-950 flex items-center justify-center select-none overflow-hidden min-h-[440px] max-h-[70vh]">
            <!-- Spinner de Carga -->
            <div id="viewerLoadingSpinner" class="absolute inset-0 flex items-center justify-center bg-slate-950/50 z-10 hidden">
                <div class="w-8 h-8 border-3 border-sky-400 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <!-- Flecha Anterior -->
            <button onclick="prevCharacterPhoto()"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 hover:bg-white text-slate-900 shadow-xl border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95 cursor-pointer"
                    title="Anterior (Flecha izquierda)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Foto Principal -->
            <img id="viewerMainImg" src="" alt="Foto de Personaje" class="max-h-[65vh] w-auto rounded-lg shadow-2xl object-contain transition-opacity duration-150 z-10">

            <!-- Flecha Siguiente -->
            <button onclick="nextCharacterPhoto()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 hover:bg-white text-slate-900 shadow-xl border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95 cursor-pointer"
                    title="Siguiente (Flecha derecha)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Tira de Miniaturas Inferior -->
        <div id="viewerThumbnails" class="p-3 bg-slate-900 flex items-center gap-2 overflow-x-auto border-t border-slate-800 scroll-smooth">
            <!-- Renderizado dinámico en JS -->
        </div>

        <!-- Footer con Acciones y Atajos -->
        <div class="p-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-[11px] text-slate-400 flex items-center gap-2">
                <span>⌨️ Usa <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-600 font-mono">←</kbd> <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-600 font-mono">→</kbd> o desliza con el dedo</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="setAsCoverPhotoAjax()" id="setCoverBtn" class="w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span id="setCoverBtnText">Establecer como Carátula</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Dar de Alta o Editar Personaje -->
<div id="characterModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs hidden z-50 flex items-center justify-center p-4" onclick="closeCharacterModal()">
    <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200" onclick="event.stopPropagation()">
        <form id="characterForm" action="/app/characters/save" method="POST" onsubmit="handleCharacterFormSubmit(event)">
            @csrf
            <input type="hidden" name="character_id" id="charFormId" value="">

            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <h3 id="charModalTitle" class="text-base font-bold text-slate-900 font-heading">
                    Nuevo Personaje
                </h3>
                <button type="button" onclick="closeCharacterModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Nombre del Personaje / Doctor *
                    </label>
                    <input type="text" name="name" id="charFormName" required placeholder="Ej: Dra. Luciana Galbiati"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Rol o Especialidad
                    </label>
                    <input type="text" name="role" id="charFormRole" placeholder="Ej: Odontología General"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                </div>

                <!-- Link a Carpeta de Google Drive -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Carpeta de Google Drive (Fotos) *</span>
                        <span class="text-[10px] text-sky-600 font-semibold">Obligatorio</span>
                    </label>
                    <input type="text" name="gdrive_folder_url" id="charFormDriveUrl" required placeholder="https://drive.google.com/drive/folders/1PNC5gKeTv... o ID"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-mono text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    <p class="text-[11px] text-slate-500 mt-1">
                        Pega el enlace compartido o el ID de la carpeta de Google Drive donde están las fotos del profesional.
                    </p>
                </div>

                <!-- Notas Adicionales -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Notas / Descripción de Sesión
                    </label>
                    <textarea name="notes" id="charFormNotes" rows="2" placeholder="Ej: Fotos oficiales de sesión clínica con ambo azul marino..."
                              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeCharacterModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" id="charFormSubmitBtn" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <span>Guardar Personaje</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let allCharacters = @json($characters);
let activeCharacter = null;
let currentPhotoIdx = 0;

function openCharacterViewer(charId) {
    activeCharacter = allCharacters.find(c => String(c.id) === String(charId));
    if (!activeCharacter || !activeCharacter.photos || activeCharacter.photos.length === 0) {
        alert('No se encontraron fotos en la carpeta de Google Drive configurada. Verifica que la carpeta sea compartida o tenga imágenes.');
        return;
    }

    // Si tiene carátula seleccionada en BD, abrir directamente en esa foto
    const currentCover = activeCharacter.cover_url || activeCharacter.cover_photo;
    let foundIdx = -1;
    if (currentCover) {
        foundIdx = activeCharacter.photos.findIndex(p => p.url === currentCover || p.relPath === currentCover);
    }
    currentPhotoIdx = foundIdx !== -1 ? foundIdx : 0;

    renderViewerThumbnails();
    renderCharacterPhoto();
    document.getElementById('characterViewerModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function renderCharacterPhoto() {
    if (!activeCharacter || !activeCharacter.photos || activeCharacter.photos.length === 0) return;
    const photo = activeCharacter.photos[currentPhotoIdx];
    if (!photo) return;

    const img = document.getElementById('viewerMainImg');
    const spinner = document.getElementById('viewerLoadingSpinner');

    spinner.classList.remove('hidden');
    img.style.opacity = '0.4';

    img.onload = function() {
        spinner.classList.add('hidden');
        img.style.opacity = '1';
    };

    img.onerror = function() {
        spinner.classList.add('hidden');
        if (photo.thumb && img.src !== photo.thumb) {
            img.src = photo.thumb;
        } else {
            img.style.opacity = '1';
        }
    };

    img.src = photo.url;

    document.getElementById('viewerCharName').innerText = activeCharacter.name;
    document.getElementById('viewerPhotoFilename').innerText = photo.filename;
    document.getElementById('viewerCounter').innerText = `${currentPhotoIdx + 1} / ${activeCharacter.photos.length}`;

    // Estado del botón de carátula
    updateCoverButtonUI();

    // Actualizar clase activa en miniaturas y hacer scroll visible
    document.querySelectorAll('.thumb-btn').forEach((btn, idx) => {
        if (idx === currentPhotoIdx) {
            btn.classList.add('ring-3', 'ring-sky-400', 'scale-105', 'opacity-100');
            btn.classList.remove('opacity-50');
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            btn.classList.remove('ring-3', 'ring-sky-400', 'scale-105', 'opacity-100');
            btn.classList.add('opacity-50');
        }
    });
}

function updateCoverButtonUI() {
    if (!activeCharacter || !activeCharacter.photos) return;
    const photo = activeCharacter.photos[currentPhotoIdx];
    if (!photo) return;

    const setCoverBtn = document.getElementById('setCoverBtn');
    const setCoverBtnText = document.getElementById('setCoverBtnText');
    const currentCover = activeCharacter.cover_url || activeCharacter.cover_photo;
    const isCurrentCover = (currentCover && (currentCover === photo.url || currentCover === photo.relPath));

    if (isCurrentCover) {
        setCoverBtn.classList.remove('bg-sky-600', 'hover:bg-sky-700');
        setCoverBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        setCoverBtnText.innerText = '✓ Carátula Actual';
    } else {
        setCoverBtn.classList.add('bg-sky-600', 'hover:bg-sky-700');
        setCoverBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
        setCoverBtnText.innerText = 'Establecer como Carátula';
    }
}

function renderViewerThumbnails() {
    const container = document.getElementById('viewerThumbnails');
    container.innerHTML = '';

    activeCharacter.photos.forEach((photo, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `thumb-btn w-14 h-14 rounded-lg overflow-hidden shrink-0 transition-all border border-slate-700 cursor-pointer ${idx === currentPhotoIdx ? 'ring-3 ring-sky-400 scale-105 opacity-100' : 'opacity-50 hover:opacity-100'}`;
        btn.innerHTML = `<img src="${photo.thumb || photo.url}" alt="Miniatura ${idx + 1}" class="w-full h-full object-cover">`;
        btn.onclick = () => {
            currentPhotoIdx = idx;
            renderCharacterPhoto();
        };
        container.appendChild(btn);
    });
}

function prevCharacterPhoto() {
    if (!activeCharacter || !activeCharacter.photos || activeCharacter.photos.length === 0) return;
    currentPhotoIdx = (currentPhotoIdx - 1 + activeCharacter.photos.length) % activeCharacter.photos.length;
    renderCharacterPhoto();
}

function nextCharacterPhoto() {
    if (!activeCharacter || !activeCharacter.photos || activeCharacter.photos.length === 0) return;
    currentPhotoIdx = (currentPhotoIdx + 1) % activeCharacter.photos.length;
    renderCharacterPhoto();
}

function closeCharacterViewer() {
    document.getElementById('characterViewerModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Acción Instantánea AJAX para fijar carátula en Base de Datos
function setAsCoverPhotoAjax() {
    if (!activeCharacter) return;
    const photo = activeCharacter.photos[currentPhotoIdx];
    if (!photo) return;

    const btn = document.getElementById('setCoverBtn');
    const btnText = document.getElementById('setCoverBtnText');
    btn.disabled = true;
    btnText.innerText = 'Guardando en BD...';

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    fetch('/app/characters/set-cover', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            _token: token,
            character_id: activeCharacter.id,
            cover_photo: photo.url
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            activeCharacter.cover_url = photo.url;
            activeCharacter.cover_photo = photo.url;

            // Actualizar botón
            btn.classList.remove('bg-sky-600', 'hover:bg-sky-700');
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            btnText.innerText = '✓ ¡Carátula Guardada en BD!';

            // Actualizar la foto en la tarjeta de la página inmediatamente
            const cardImg = document.getElementById(`card-cover-${activeCharacter.id}`);
            if (cardImg) {
                cardImg.src = photo.url;
            }

            // Actualizar en el arreglo allCharacters
            const charInList = allCharacters.find(c => String(c.id) === String(activeCharacter.id));
            if (charInList) {
                charInList.cover_url = photo.url;
                charInList.cover_photo = photo.url;
            }
        } else {
            alert('Error al guardar carátula en BD: ' + (data.error || data.message || 'Error desconocido'));
            updateCoverButtonUI();
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        alert('Error de conexión al guardar carátula en la base de datos.');
        updateCoverButtonUI();
    });
}

// Navegación por Teclado
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('characterViewerModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (e.key === 'ArrowLeft') {
            prevCharacterPhoto();
        } else if (e.key === 'ArrowRight') {
            nextCharacterPhoto();
        } else if (e.key === 'Escape') {
            closeCharacterViewer();
        }
    }
});

// Soporte Swipe (Touch + Mouse Drag) en el visor
let charTouchStartX = 0;
let charIsMouseDown = false;
let charMouseStartX = 0;
const charSwipeArea = document.getElementById('viewerSwipeArea');

if (charSwipeArea) {
    charSwipeArea.style.cursor = 'grab';

    // Touch
    charSwipeArea.addEventListener('touchstart', function(e) {
        charTouchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    charSwipeArea.addEventListener('touchend', function(e) {
        const touchEndX = e.changedTouches[0].screenX;
        if (Math.abs(touchEndX - charTouchStartX) > 40) {
            if (touchEndX - charTouchStartX < 0) {
                nextCharacterPhoto();
            } else {
                prevCharacterPhoto();
            }
        }
    }, { passive: true });

    // Mouse Drag
    charSwipeArea.addEventListener('mousedown', function(e) {
        if (e.target.closest('button')) return;
        charIsMouseDown = true;
        charMouseStartX = e.clientX;
        charSwipeArea.style.cursor = 'grabbing';
    });

    window.addEventListener('mouseup', function(e) {
        if (!charIsMouseDown) return;
        charIsMouseDown = false;
        if (charSwipeArea) charSwipeArea.style.cursor = 'grab';
        const mouseEndX = e.clientX;
        if (Math.abs(mouseEndX - charMouseStartX) > 40) {
            if (mouseEndX - charMouseStartX < 0) {
                nextCharacterPhoto();
            } else {
                prevCharacterPhoto();
            }
        }
    });
}

// Funciones de Modal de Creación / Edición
function openNewCharacterModal() {
    document.getElementById('charFormId').value = '';
    document.getElementById('charFormName').value = '';
    document.getElementById('charFormRole').value = '';
    document.getElementById('charFormDriveUrl').value = '';
    document.getElementById('charFormNotes').value = '';
    document.getElementById('charModalTitle').innerText = 'Nuevo Personaje';
    document.getElementById('characterModal').classList.remove('hidden');
}

function openEditCharacterModal(id, name, role, driveUrl, notes) {
    document.getElementById('charFormId').value = id;
    document.getElementById('charFormName').value = name;
    document.getElementById('charFormRole').value = role || '';
    document.getElementById('charFormDriveUrl').value = driveUrl || '';
    document.getElementById('charFormNotes').value = notes || '';
    document.getElementById('charModalTitle').innerText = 'Editar Personaje';
    document.getElementById('characterModal').classList.remove('hidden');
}

function closeCharacterModal() {
    document.getElementById('characterModal').classList.add('hidden');
}

function handleCharacterFormSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('characterForm');
    const btn = document.getElementById('charFormSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span><span>Guardando...</span>';

    const formData = new FormData(form);

    fetch('/app/characters/save', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeCharacterModal();
            window.location.reload();
        } else {
            alert(data.message || 'Error al guardar personaje');
            btn.disabled = false;
            btn.innerHTML = '<span>Guardar Personaje</span>';
        }
    })
    .catch(err => {
        console.error(err);
        form.submit();
    });
}
</script>
@endsection
