@extends('layouts.app')

@section('title', 'Creaciones y Biblioteca - TecnoGen Studio')

@section('content')
<div class="space-y-6 pb-20">
    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Biblioteca</span>
                <span class="text-xs text-slate-500 font-medium">Contenidos Generados & Almacenamiento en Google Drive</span>
            </div>
            <h1 class="text-2xl font-bold font-heading text-slate-900">Mis Creaciones</h1>
            <p class="text-sm text-slate-500 mt-1">Historial de carruseles, posts y piezas renderizadas con IA y sincronizadas con Google Drive.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if(!empty($creationsFolderUrl))
                <a href="{{ $creationsFolderUrl }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-900 font-semibold text-sm flex items-center gap-2 transition-all shadow-xs">
                    <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
                    <span>📁 Abrir Carpeta Google Drive</span>
                </a>
            @endif

            <button type="button" onclick="openDriveConfigModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-sm flex items-center gap-2 transition-all shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>⚙️ Configurar Carpeta Drive</span>
            </button>

            <a href="/app/create" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm flex items-center gap-2 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Nueva Creación</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- BANNER DE GOOGLE DRIVE CONFIGURADO -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 text-white border border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-md">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-sky-400" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-400">Destino de Almacenamiento</span>
                    @if(!empty($creationsFolderUrl))
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Conectado</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Pendiente de enlace</span>
                    @endif
                </div>
                <h3 class="font-bold text-base text-slate-100 font-heading mt-0.5">
                    Carpeta de Creaciones en Google Drive
                </h3>
                <p class="text-xs text-slate-300 mt-0.5">
                    @if(!empty($creationsFolderUrl))
                        <span class="font-mono text-slate-400 truncate max-w-md inline-block align-bottom">{{ $creationsFolderUrl }}</span>
                    @else
                        Configura la URL de tu carpeta de Google Drive para exportar y respaldar automáticamente cada carrusel generado.
                    @endif
                </p>
            </div>
        </div>

        <button type="button" onclick="openDriveConfigModal()" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 transition-colors shrink-0 cursor-pointer">
            {{ !empty($creationsFolderUrl) ? 'Cambiar Carpeta' : 'Vincular Carpeta de Drive' }}
        </button>
    </div>

    <!-- LISTADO DE CREACIONES -->
    @if(empty($contents) || count($contents) === 0)
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto text-2xl">
                🎨
            </div>
            <div class="max-w-md mx-auto">
                <h3 class="font-bold text-lg text-slate-900 font-heading">No tienes creaciones aún</h3>
                <p class="text-xs text-slate-500 mt-1">Comienza generando tu primer carrusel o post clínico con asistencia de IA en cada campo o pegando tu guion.</p>
            </div>
            <a href="/app/create" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs transition-all">
                <span>+ Crear Primera Publicación</span>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($contents as $item)
                @php
                    $renderedCount = 0;
                    foreach($item->slides as $s) {
                        if (!empty($s->image_url)) $renderedCount++;
                    }
                    $isAllRendered = count($item->slides) > 0 && $renderedCount === count($item->slides);
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden" id="card_{{ $item->id }}">
                    <div class="p-5 space-y-4">
                        <!-- Top Badges -->
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 uppercase tracking-wide">
                                @if($item->type === 'carousel')
                                    🎠 Carrusel ({{ $item->total_slides ?? count($item->slides) }} láminas)
                                @elseif($item->type === 'single_post')
                                    🖼️ Post Único
                                @else
                                    📱 Reel / Guion
                                @endif
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if($isAllRendered)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✨ Renderizado
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        📝 Guion Listo
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400 font-mono">
                                    {{ $item->created_at ? $item->created_at->format('d/m H:i') : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Título / Hook -->
                        <div>
                            <h3 class="font-bold text-base text-slate-900 leading-snug line-clamp-2">
                                {{ $item->title ?: $item->hook_text ?: 'Sin título' }}
                            </h3>
                            @if(!empty($item->hook_text) && $item->hook_text !== $item->title)
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2 italic">
                                    "{{ $item->hook_text }}"
                                </p>
                            @endif
                        </div>

                        <!-- Miniatura de Láminas / Imágenes Renderizadas -->
                        @if(!empty($item->slides) && count($item->slides) > 0)
                            <div class="space-y-2">
                                <div class="text-[11px] font-semibold text-slate-500 flex items-center justify-between">
                                    <span>Láminas ({{ count($item->slides) }})</span>
                                    <span class="text-sky-600 cursor-pointer hover:underline" onclick="toggleSlidesPreview('slides_{{ $item->id }}')">Ver texto</span>
                                </div>
                                
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    @foreach($item->slides as $slide)
                                        @if(!empty($slide->image_url))
                                            <!-- Lámina con Imagen Real Renderizada -->
                                            <div class="aspect-4/5 rounded-lg overflow-hidden border border-slate-300 relative group cursor-pointer shadow-2xs" onclick="openLightbox('{{ $slide->image_url }}', '{{ addslashes($slide->headline ?? '') }}')">
                                                <img src="{{ $slide->image_url }}" alt="Slide #{{ $slide->slide_number }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                                <span class="absolute top-1 left-1 bg-slate-900/70 backdrop-blur-xs text-white text-[9px] font-bold px-1 rounded">
                                                    #{{ $slide->slide_number }}
                                                </span>
                                            </div>
                                        @else
                                            <!-- Lámina en Estado Borrador de Texto -->
                                            <div class="aspect-4/5 bg-slate-100 hover:bg-slate-200/80 rounded-lg flex flex-col justify-between p-1.5 border border-slate-200 text-[9px] font-semibold text-slate-700 overflow-hidden transition-colors" title="{{ $slide->headline }}">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-slate-400 font-mono">#{{ $slide->slide_number }}</span>
                                                    <span class="text-[8px] text-amber-600 font-bold">Borrador</span>
                                                </div>
                                                <span class="line-clamp-2 leading-tight text-[8px] text-slate-800">{{ $slide->headline ?: $slide->badge ?: 'Slide' }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Botón de Renderizado Rápido si no están todas generadas -->
                                @if(!$isAllRendered)
                                    <button type="button" onclick="renderAllImages('{{ $item->id }}', this)" class="w-full mt-2 py-2 px-3 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>🎨 Renderizar Imágenes con IA</span>
                                    </button>
                                @endif

                                <!-- Acordeón de Láminas Completo -->
                                <div id="slides_{{ $item->id }}" class="hidden pt-2 space-y-2 text-xs border-t border-slate-100 max-h-48 overflow-y-auto">
                                    @foreach($item->slides as $slide)
                                        <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">
                                            <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                                <span class="px-1.5 py-0.5 bg-sky-100 text-sky-800 rounded-md text-[10px]">#{{ $slide->slide_number }}</span>
                                                @if($slide->badge)
                                                    <span class="text-[10px] text-sky-600 font-mono">[{{ $slide->badge }}]</span>
                                                @endif
                                                <span class="truncate">{{ $slide->headline }}</span>
                                            </div>
                                            @if($slide->body_text)
                                                <p class="text-slate-600 text-[11px] mt-1">{{ $slide->body_text }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Caption Preview -->
                        @if(!empty($item->caption_copy))
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Copy Instagram:</span>
                                <p class="text-xs text-slate-600 line-clamp-2">{{ $item->caption_copy }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer de Acciones -->
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="copyCaption(`{{ addslashes($item->caption_copy . "\n\n" . $item->hashtags) }}`)" class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold flex items-center gap-1 shadow-2xs cursor-pointer" title="Copiar Copy">
                                📋 Copy
                            </button>

                            @if(!empty($creationsFolderUrl))
                                <a href="{{ $creationsFolderUrl }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-amber-50 text-amber-700 text-xs font-semibold flex items-center gap-1 shadow-2xs" title="Ver en Drive">
                                    📁 Drive
                                </a>
                            @endif
                        </div>

                        <form action="/app/contents/{{ $item->id }}/delete" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta creación?');">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 text-xs font-semibold transition-colors cursor-pointer" title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- MODAL LIGHTBOX PARA VER IMAGEN EN ALTA RESOLUCIÓN -->
<div id="lightboxModal" class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4 hidden" onclick="closeLightbox()">
    <div class="relative max-w-2xl w-full flex flex-col items-center" onclick="event.stopPropagation()">
        <button onclick="closeLightbox()" class="absolute -top-12 right-0 text-white/80 hover:text-white text-2xl font-bold cursor-pointer">
            ✕
        </button>
        <img id="lightboxImg" src="" alt="Vista previa" class="max-h-[80vh] w-auto rounded-2xl shadow-2xl border border-white/10">
        <p id="lightboxCaption" class="text-white text-sm font-semibold mt-3 text-center"></p>
    </div>
</div>

<!-- MODAL: CONFIGURAR CARPETA DE GOOGLE DRIVE -->
<div id="driveConfigModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 font-heading">Carpeta de Destino en Google Drive</h3>
                    <p class="text-xs text-slate-500">Pega el link de la carpeta de Google Drive donde almacenarás tus piezas.</p>
                </div>
            </div>
            <button type="button" onclick="closeDriveConfigModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer">
                ✕
            </button>
        </div>

        <form action="/app/brand/save-output-folder" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Enlace a la Carpeta de Google Drive</label>
                <input type="text" name="gdrive_output_folder_url" value="{{ $creationsFolderUrl ?? '' }}" placeholder="https://drive.google.com/drive/folders/1abcXYZ..." required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                <p class="text-[11px] text-slate-500 mt-1">Asegúrate de que la carpeta tenga permisos de lectura/escritura o acceso mediante enlace.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeDriveConfigModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Cancelar</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs cursor-pointer">
                    Guardar Carpeta de Destino
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDriveConfigModal() {
        document.getElementById('driveConfigModal').classList.remove('hidden');
    }

    function closeDriveConfigModal() {
        document.getElementById('driveConfigModal').classList.add('hidden');
    }

    function toggleSlidesPreview(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden');
    }

    function openLightbox(url, caption) {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImg');
        const cap = document.getElementById('lightboxCaption');
        img.src = url;
        cap.innerText = caption || '';
        modal.classList.remove('hidden');
    }

    function closeLightbox() {
        document.getElementById('lightboxModal').classList.add('hidden');
    }

    function copyCaption(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('¡Copy y hashtags copiados al portapapeles!');
        }).catch(() => {
            alert('No se pudo copiar automáticamente.');
        });
    }

    // Renderizar todas las láminas con IA mediante AJAX
    async function renderAllImages(contentId, btnEl) {
        const originalText = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = `<span>⏳ Generando imágenes con OpenAI...</span>`;
        btnEl.classList.add('animate-pulse');

        try {
            const res = await fetch(`/app/contents/${contentId}/generate-images`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();
            if (data.success) {
                alert('¡Láminas generadas exitosamente!');
                window.location.reload();
            } else {
                alert('Error al generar imágenes: ' + (data.error || 'Respuesta inválida'));
                btnEl.disabled = false;
                btnEl.innerHTML = originalText;
                btnEl.classList.remove('animate-pulse');
            }
        } catch (e) {
            alert('Error de conexión con el motor de renderizado.');
            btnEl.disabled = false;
            btnEl.innerHTML = originalText;
            btnEl.classList.remove('animate-pulse');
        }
    }
</script>
@endsection
