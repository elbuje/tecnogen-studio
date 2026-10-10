@extends('layouts.app')

@section('title', 'Tipos Visuales - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-heading font-bold text-slate-900">
                        Catálogo de Tipos Visuales
                        <span class="ml-2 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800">
                            {{ count($visualTypes) }} Estructuras
                        </span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Formatos conceptuales y layouts de contenido para carruseles de alto impacto y retención.
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
        </div>
    </div>

    <!-- Active Setting Banner -->
    <div class="saas-card p-4 bg-sky-50/50 border-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                ✓
            </span>
            <div>
                <div class="text-xs font-bold text-slate-900">
                    Tipo Visual Seleccionado: <span class="text-sky-700 font-mono">{{ $currentVisualType }}</span>
                </div>
                <div class="text-[11px] text-slate-500">
                    Define la estructura y lógica visual predominante al generar contenidos de carrusel.
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Types Grid (4 a 5 por fila) -->
    @php
        $galleryItems = [];
        foreach($visualTypes as $vt) {
            $numStr = sprintf('%02d', $vt['number']);
            $relPath = "assets/visual_types/visual_type_{$numStr}.png";
            $hasImage = (file_exists(public_path($relPath)) || file_exists(base_path("../public/{$relPath}")));
            $galleryItems[] = [
                'id' => $vt['id'],
                'number' => $vt['number'],
                'name' => $vt['name'],
                'description' => $vt['description'],
                'category' => $vt['category'],
                'sheetLabel' => $vt['sheetLabel'],
                'hasImage' => $hasImage,
                'src' => '/' . $relPath,
                'isActive' => ($vt['sheetLabel'] === $currentVisualType)
            ];
        }
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($visualTypes as $idx => $vt)
            @php
                $isActive = ($vt['sheetLabel'] === $currentVisualType);
                $numStr = sprintf('%02d', $vt['number']);
                $relPath = "assets/visual_types/visual_type_{$numStr}.png";
                $hasImage = (file_exists(public_path($relPath)) || file_exists(base_path("../public/{$relPath}")));
            @endphp
            <div class="saas-card p-4 flex flex-col justify-between relative transition-all {{ $isActive ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/30' : 'bg-white' }}">
                @if($isActive)
                    <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold shadow-sm z-10">
                        ✓
                    </div>
                @endif

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $vt['category'] }}
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-400">
                            #{{ sprintf('%02d', $vt['number']) }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-slate-900 font-heading">
                        {{ $vt['name'] }}
                    </h3>

                    @if($hasImage)
                        <div class="h-32 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 relative group cursor-pointer"
                             onclick="openVisualModal({{ $idx }})">
                            <img src="/{{ $relPath }}" alt="{{ $vt['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-all flex items-center justify-center">
                                <span class="opacity-0 group-hover:opacity-100 px-2.5 py-1 bg-white/90 text-slate-800 text-[10px] font-bold rounded-md shadow-xs transition-opacity flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Ver & Deslizar
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="h-28 bg-slate-50 border border-slate-200 rounded-lg p-3 flex flex-col items-center justify-center relative cursor-pointer hover:bg-slate-100/60 transition-colors"
                             onclick="openVisualModal({{ $idx }})">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center justify-center text-sky-600 font-bold mb-2">
                                {{ sprintf('%02d', $vt['number']) }}
                            </div>
                            <span class="text-[11px] font-semibold text-slate-600 text-center">
                                {{ $vt['name'] }}
                            </span>
                        </div>
                    @endif

                    <p class="text-xs text-slate-500 line-clamp-2">
                        {{ $vt['description'] }}
                    </p>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400 font-mono">
                        {{ Str::limit($vt['sheetLabel'], 16) }}
                    </span>

                    <form action="/app/visual-types/select" method="POST">
                        @csrf
                        <input type="hidden" name="visual_type" value="{{ $vt['sheetLabel'] }}">
                        <button type="submit"
                                class="text-xs font-bold px-3 py-1 rounded-lg transition-colors {{ $isActive ? 'bg-sky-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $isActive ? 'Activo' : 'Elegir' }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Slider para Visual Types sin tener que cerrar -->
<div id="visualModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs hidden z-50 flex items-center justify-center p-3 sm:p-6" onclick="closeVisualModal()">
    <div class="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col relative animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
        <!-- Header con Contador y Botón Cerrar -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-white z-10">
            <div class="flex items-center gap-3">
                <span id="visualModalCategory" class="text-[10px] font-bold uppercase tracking-wider text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-100"></span>
                <div>
                    <h3 id="visualModalTitle" class="text-sm sm:text-base font-bold text-slate-900 font-heading"></h3>
                    <p id="visualModalDesc" class="text-xs text-slate-500 mt-0.5 line-clamp-1"></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span id="visualModalCounter" class="text-xs font-mono font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded-md"></span>
                <button onclick="closeVisualModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm transition-colors">
                    ✕
                </button>
            </div>
        </div>

        <!-- Área Central con Flechas Laterales de Navegación -->
        <div id="visualSwipeArea" class="relative p-4 bg-slate-100/70 flex items-center justify-center select-none overflow-hidden min-h-[350px]">
            <!-- Flecha Anterior -->
            <button onclick="prevVisualItem()"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-800 shadow-md border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95"
                    title="Anterior (Flecha izquierda)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Imagen o Placeholder -->
            <div id="visualContentArea" class="w-full flex items-center justify-center">
                <img id="visualModalImg" src="" alt="Muestra de Tipo Visual" class="max-h-[65vh] w-auto rounded-xl shadow-md border border-slate-200 object-contain transition-opacity duration-150">
                <div id="visualModalPlaceholder" class="hidden flex-col items-center justify-center p-12 text-center bg-white rounded-xl border border-slate-200 shadow-xs max-w-sm">
                    <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold mb-3 font-heading" id="visualModalPlaceholderNum">
                        01
                    </div>
                    <h4 class="font-bold text-slate-900 text-base" id="visualModalPlaceholderTitle"></h4>
                    <p class="text-xs text-slate-500 mt-2" id="visualModalPlaceholderDesc"></p>
                </div>
            </div>

            <!-- Flecha Siguiente -->
            <button onclick="nextVisualItem()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-800 shadow-md border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95"
                    title="Siguiente (Flecha derecha)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Footer con Acción Directa y Atajos -->
        <div class="p-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-[11px] text-slate-400 flex items-center gap-2">
                <span>⌨️ Usa <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-600 font-mono">←</kbd> <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-600 font-mono">→</kbd> o desliza con el dedo</span>
            </div>

            <form id="visualSelectForm" action="/app/visual-types/select" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="hidden" name="visual_type" id="visualSelectInput" value="">
                <button type="submit" id="visualSelectBtn" class="w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm flex items-center justify-center gap-1.5">
                    <span>Elegir este Tipo Visual</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const visualGalleryItems = @json($galleryItems);
let currentVisualIdx = 0;

function openVisualModal(index) {
    if (!visualGalleryItems || visualGalleryItems.length === 0) return;
    currentVisualIdx = (index >= 0 && index < visualGalleryItems.length) ? index : 0;
    renderVisualItem();
    document.getElementById('visualModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function renderVisualItem() {
    const item = visualGalleryItems[currentVisualIdx];
    if (!item) return;

    const img = document.getElementById('visualModalImg');
    const placeholder = document.getElementById('visualModalPlaceholder');

    if (item.hasImage) {
        placeholder.classList.add('hidden');
        img.classList.remove('hidden');
        img.style.opacity = '0.3';
        setTimeout(() => {
            img.src = item.src;
            img.style.opacity = '1';
        }, 50);
    } else {
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
        document.getElementById('visualModalPlaceholderNum').innerText = String(item.number).padStart(2, '0');
        document.getElementById('visualModalPlaceholderTitle').innerText = item.name;
        document.getElementById('visualModalPlaceholderDesc').innerText = item.description;
    }

    document.getElementById('visualModalTitle').innerText = item.name;
    document.getElementById('visualModalDesc').innerText = item.description;
    document.getElementById('visualModalCategory').innerText = item.category || 'ESTRUCTURA';
    document.getElementById('visualModalCounter').innerText = `${currentVisualIdx + 1} / ${visualGalleryItems.length}`;
    document.getElementById('visualSelectInput').value = item.sheetLabel;

    const selectBtn = document.getElementById('visualSelectBtn');
    if (item.isActive) {
        selectBtn.classList.remove('bg-sky-600', 'hover:bg-sky-700');
        selectBtn.classList.add('bg-emerald-600');
        selectBtn.innerHTML = '<span>✓ Tipo Visual Activo</span>';
    } else {
        selectBtn.classList.add('bg-sky-600', 'hover:bg-sky-700');
        selectBtn.classList.remove('bg-emerald-600');
        selectBtn.innerHTML = '<span>Elegir este Tipo Visual</span>';
    }
}

function prevVisualItem() {
    if (visualGalleryItems.length === 0) return;
    currentVisualIdx = (currentVisualIdx - 1 + visualGalleryItems.length) % visualGalleryItems.length;
    renderVisualItem();
}

function nextVisualItem() {
    if (visualGalleryItems.length === 0) return;
    currentVisualIdx = (currentVisualIdx + 1) % visualGalleryItems.length;
    renderVisualItem();
}

function closeVisualModal() {
    document.getElementById('visualModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Teclado
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('visualModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (e.key === 'ArrowLeft') {
            prevVisualItem();
        } else if (e.key === 'ArrowRight') {
            nextVisualItem();
        } else if (e.key === 'Escape') {
            closeVisualModal();
        }
    }
});

// Deslizar con el dedo (Swipe)
let vTouchStartX = 0;
let vTouchEndX = 0;
const vSwipeArea = document.getElementById('visualSwipeArea');

if (vSwipeArea) {
    vSwipeArea.addEventListener('touchstart', function(e) {
        vTouchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    vSwipeArea.addEventListener('touchend', function(e) {
        vTouchEndX = e.changedTouches[0].screenX;
        if (Math.abs(vTouchEndX - vTouchStartX) > 40) {
            if (vTouchEndX - vTouchStartX < 0) {
                nextVisualItem();
            } else {
                prevVisualItem();
            }
        }
    }, { passive: true });
}
</script>
@endsection
