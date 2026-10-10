@extends('layouts.app')

@section('title', 'Catálogo de Paginadores - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-heading font-bold text-slate-900">
                        Catálogo de Paginadores
                        <span class="ml-2 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800">
                            {{ count($paginators) }} Estilos
                        </span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Selecciona el estilo de paginación para los carruseles de tu marca. Conectado directamente a Google Sheets (Columna U).
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
                    Estilo Activo para tu Marca: <span class="text-sky-700 font-mono">{{ $currentPaginator }}</span>
                </div>
                <div class="text-[11px] text-slate-500">
                    Este estilo se utilizará por defecto en las publicaciones generadas para tu perfil.
                </div>
            </div>
        </div>
    </div>

    <!-- Paginators Grid (4 a 5 por fila) -->
    @php
        $galleryItems = [];
        foreach($paginators as $p) {
            $numStr = sprintf('%02d', $p['number']);
            $relPath = "assets/paginators/paginator_{$numStr}.png";
            $hasImage = $p['number'] > 0 && (file_exists(public_path($relPath)) || file_exists(base_path("../public/{$relPath}")));
            if ($hasImage) {
                $galleryItems[] = [
                    'id' => $p['id'],
                    'number' => $p['number'],
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'src' => '/' . $relPath,
                    'sheetLabel' => $p['sheetLabel'],
                    'category' => $p['category'],
                    'isActive' => ($p['sheetLabel'] === $currentPaginator) || ($p['id'] === 'random' && $currentPaginator === 'Aleatorio')
                ];
            }
        }
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($paginators as $p)
            @php
                $isActive = ($p['sheetLabel'] === $currentPaginator) || ($p['id'] === 'random' && $currentPaginator === 'Aleatorio');
                $numStr = sprintf('%02d', $p['number']);
                $relPath = "assets/paginators/paginator_{$numStr}.png";
                $hasImage = $p['number'] > 0 && (file_exists(public_path($relPath)) || file_exists(base_path("../public/{$relPath}")));
                $galleryIndex = null;
                if ($hasImage) {
                    foreach($galleryItems as $gIdx => $gItem) {
                        if ($gItem['number'] === $p['number']) {
                            $galleryIndex = $gIdx;
                            break;
                        }
                    }
                }
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
                            {{ $p['category'] }}
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-400">
                            #{{ $p['number'] }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-slate-900 font-heading">
                        {{ $p['name'] }}
                    </h3>

                    <!-- Mini Preview Visual Blanca o Imagen Generada -->
                    @if($hasImage)
                        <div class="h-32 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 relative group cursor-pointer"
                             onclick="openGalleryModal({{ $galleryIndex }})">
                            <img src="/{{ $relPath }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-all flex items-center justify-center">
                                <span class="opacity-0 group-hover:opacity-100 px-2.5 py-1 bg-white/90 text-slate-800 text-[10px] font-bold rounded-md shadow-xs transition-opacity flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Ver & Deslizar
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="h-28 bg-slate-50 border border-slate-200 rounded-lg p-3 flex flex-col items-center justify-center relative overflow-hidden">
                            @if($p['id'] === 'random')
                                <div class="flex items-center gap-1.5 px-3 py-1 rounded-md bg-white border border-slate-300 shadow-xs">
                                    <span>🎲</span>
                                    <span class="text-xs font-bold text-slate-800">Auto Selector</span>
                                </div>
                            @elseif($p['id'] === 'num-simple')
                                <div class="text-xs font-mono font-bold text-sky-700 bg-white px-3 py-1 rounded border border-slate-200 shadow-xs">
                                    03 / 08
                                </div>
                            @elseif($p['id'] === 'num-individual')
                                <div class="text-lg font-black text-slate-900 font-heading">
                                    03
                                </div>
                            @elseif($p['id'] === 'num-etiqueta')
                                <div class="text-[11px] font-medium text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200 shadow-xs">
                                    <span class="font-bold text-sky-600">02</span> · Problema
                                </div>
                            @elseif($p['id'] === 'puntos-dots')
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                </div>
                            @elseif($p['id'] === 'barra-progreso')
                                <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                    <div class="w-3/5 h-full bg-sky-600 rounded-full"></div>
                                </div>
                            @elseif($p['id'] === 'barra-segmentada')
                                <div class="w-full flex items-center gap-1">
                                    <div class="flex-1 h-1 rounded bg-sky-600"></div>
                                    <div class="flex-1 h-1 rounded bg-sky-600"></div>
                                    <div class="flex-1 h-1 rounded bg-sky-600"></div>
                                    <div class="flex-1 h-1 rounded bg-slate-200"></div>
                                    <div class="flex-1 h-1 rounded bg-slate-200"></div>
                                </div>
                            @else
                                <div class="text-xs font-mono font-bold text-slate-700 bg-white px-2.5 py-1 rounded border border-slate-200">
                                    {{ Str::limit($p['sheetLabel'], 18) }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <p class="text-xs text-slate-500 line-clamp-2">
                        {{ $p['description'] }}
                    </p>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400 font-mono">
                        {{ Str::limit($p['sheetLabel'], 16) }}
                    </span>

                    <form action="/app/paginators/select" method="POST">
                        @csrf
                        <input type="hidden" name="paginator" value="{{ $p['sheetLabel'] }}">
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

<!-- Modal Slider para navegar sin cerrar -->
<div id="imageModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs hidden z-50 flex items-center justify-center p-3 sm:p-6" onclick="closePreviewModal()">
    <div class="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col relative animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
        <!-- Header del Modal con Contador y Botón Cerrar -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-white z-10">
            <div class="flex items-center gap-3">
                <span id="modalCategory" class="text-[10px] font-bold uppercase tracking-wider text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-100"></span>
                <div>
                    <h3 id="modalTitle" class="text-sm sm:text-base font-bold text-slate-900 font-heading"></h3>
                    <p id="modalDesc" class="text-xs text-slate-500 mt-0.5 line-clamp-1"></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span id="modalCounter" class="text-xs font-mono font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded-md"></span>
                <button onclick="closePreviewModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm transition-colors">
                    ✕
                </button>
            </div>
        </div>

        <!-- Área de la Imagen con Flechas Laterales de Navegación -->
        <div id="modalSwipeArea" class="relative p-2 sm:p-4 bg-slate-100/70 flex items-center justify-center select-none overflow-hidden min-h-[350px]">
            <!-- Flecha Anterior -->
            <button onclick="prevGalleryImage()"
                    id="prevBtn"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-800 shadow-md border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95"
                    title="Anterior (Flecha izquierda)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Imagen -->
            <img id="modalImg" src="" alt="Muestra de Paginador" class="max-h-[65vh] w-auto rounded-xl shadow-md border border-slate-200 object-contain transition-opacity duration-150">

            <!-- Flecha Siguiente -->
            <button onclick="nextGalleryImage()"
                    id="nextBtn"
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

            <form id="modalSelectForm" action="/app/paginators/select" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="hidden" name="paginator" id="modalSelectPaginatorInput" value="">
                <button type="submit" id="modalSelectBtn" class="w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm flex items-center justify-center gap-1.5">
                    <span>Elegir este Estilo</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const galleryItems = @json($galleryItems);
let currentGalleryIdx = 0;

function openGalleryModal(index) {
    if (!galleryItems || galleryItems.length === 0) return;
    currentGalleryIdx = (index >= 0 && index < galleryItems.length) ? index : 0;
    renderGalleryItem();
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function renderGalleryItem() {
    const item = galleryItems[currentGalleryIdx];
    if (!item) return;

    const img = document.getElementById('modalImg');
    img.style.opacity = '0.3';
    setTimeout(() => {
        img.src = item.src;
        img.style.opacity = '1';
    }, 50);

    document.getElementById('modalTitle').innerText = item.name;
    document.getElementById('modalDesc').innerText = item.description;
    document.getElementById('modalCategory').innerText = item.category || 'ESTILO';
    document.getElementById('modalCounter').innerText = `${currentGalleryIdx + 1} / ${galleryItems.length}`;
    document.getElementById('modalSelectPaginatorInput').value = item.sheetLabel;

    const selectBtn = document.getElementById('modalSelectBtn');
    if (item.isActive) {
        selectBtn.classList.remove('bg-sky-600', 'hover:bg-sky-700', 'text-white');
        selectBtn.classList.add('bg-emerald-600', 'text-white');
        selectBtn.innerHTML = '<span>✓ Estilo Activo</span>';
    } else {
        selectBtn.classList.add('bg-sky-600', 'hover:bg-sky-700', 'text-white');
        selectBtn.classList.remove('bg-emerald-600', 'text-white');
        selectBtn.innerHTML = '<span>Elegir este Estilo</span>';
    }
}

function prevGalleryImage() {
    if (galleryItems.length === 0) return;
    currentGalleryIdx = (currentGalleryIdx - 1 + galleryItems.length) % galleryItems.length;
    renderGalleryItem();
}

function nextGalleryImage() {
    if (galleryItems.length === 0) return;
    currentGalleryIdx = (currentGalleryIdx + 1) % galleryItems.length;
    renderGalleryItem();
}

function closePreviewModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Navegación por Teclado
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (e.key === 'ArrowLeft') {
            prevGalleryImage();
        } else if (e.key === 'ArrowRight') {
            nextGalleryImage();
        } else if (e.key === 'Escape') {
            closePreviewModal();
        }
    }
});

// Soporte Táctil / Deslizar (Swipe)
let touchStartX = 0;
let touchEndX = 0;
const swipeArea = document.getElementById('modalSwipeArea');

if (swipeArea) {
    swipeArea.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    swipeArea.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipeGesture();
    }, { passive: true });
}

function handleSwipeGesture() {
    const deltaX = touchEndX - touchStartX;
    if (Math.abs(deltaX) > 40) {
        if (deltaX < 0) {
            nextGalleryImage(); // Deslizar hacia la izquierda -> Siguiente
        } else {
            prevGalleryImage(); // Deslizar hacia la derecha -> Anterior
        }
    }
}
</script>
@endsection
