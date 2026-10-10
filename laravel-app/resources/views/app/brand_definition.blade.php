@extends('layouts.app')

@section('title', 'Definición de Marca - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-heading font-bold text-slate-900 flex items-center gap-2">
                        Definición de Marca
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800">
                            {{ $brand->name ?? 'JM Odontología Integral' }}
                        </span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Administra los logotipos de <strong>Google Drive</strong>, el Manual de Identidad y las reglas de diseño para la IA.
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

            <form action="/app/brand/sync-from-manual" method="POST" class="inline" onsubmit="return confirm('¿Deseas sincronizar y autocompletar todas las reglas y paleta oficial (#16345F, #7DD3FC, #1D1D1B) desde el Manual de Marca?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Sincronizar desde Manual (1-Clic)</span>
                </button>
            </form>

            <button onclick="openEditBrandModal()" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar Identidad & Colores</span>
            </button>
        </div>
    </div>

    <!-- Grid de Tarjetas Principales (Estilo Personajes) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @php
            $defaultLogoId = $brandRules['default_logo_id'] ?? null;
            $coverLogo = null;
            if (!empty($logos)) {
                if ($defaultLogoId) {
                    foreach ($logos as $l) {
                        if ($l['id'] == $defaultLogoId) {
                            $coverLogo = $l;
                            break;
                        }
                    }
                }
                if (!$coverLogo) {
                    $coverLogo = $logos[0];
                }
            }
        @endphp

        <!-- 1. Tarjeta: Catálogo de Logotipos en Google Drive -->
        <div class="saas-card overflow-hidden flex flex-col justify-between group transition-all hover:border-sky-300">
            <div>
                <!-- Carátula de Logo Clickeable para abrir el Visor Interactivo -->
                <div class="h-64 bg-slate-900 overflow-hidden relative cursor-pointer group flex items-center justify-center p-6"
                     onclick="openLogosViewer()">
                    @if($coverLogo)
                        <img id="brand-cover-logo-img"
                             src="{{ $coverLogo['url'] }}"
                             alt="{{ $brand->name }}"
                             class="max-h-full max-w-full object-contain filter drop-shadow-xl group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 p-4 bg-slate-50">
                            <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-semibold text-slate-500">Carpeta de Logos</span>
                            <span class="text-[10px] text-slate-400 font-mono mt-1">Conectar Google Drive</span>
                        </div>
                    @endif

                    <!-- Badge de cantidad de logos / Google Drive -->
                    <div class="absolute bottom-3 right-3 bg-slate-900/85 backdrop-blur-xs text-white px-2.5 py-1 rounded-md text-[11px] font-bold flex items-center gap-1.5 shadow-sm z-10">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ count($logos) > 0 ? count($logos) . ' Logos' : 'Drive Conectado' }}</span>
                    </div>

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10">
                        <span class="px-3.5 py-2 bg-white text-slate-900 text-xs font-bold rounded-lg shadow-xl flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Abrir Visor de Logos
                        </span>
                    </div>
                </div>

                <!-- Info de la Tarjeta -->
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-heading font-bold text-base text-slate-900 line-clamp-1">
                                Catálogo de Logotipos
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">
                                Variantes vectoriales y PNG transparentes
                            </p>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
                        El agente de IA analiza automáticamente todas las variantes y elige la mejor según el fondo y contraste de cada lámina.
                    </p>

                    @if(!empty($logosFolderUrl))
                        <div class="pt-1">
                            <a href="{{ $logosFolderUrl }}" target="_blank" rel="noopener noreferrer"
                               class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100/70 px-2.5 py-1.5 rounded-lg border border-sky-100 flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-1.5 truncate">
                                    <span>📁</span>
                                    <span class="truncate">Carpeta de Logos en Drive</span>
                                </span>
                                <span class="text-sky-500 font-bold shrink-0">↗</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer de la Tarjeta -->
            <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2">
                <button onclick="openLogosViewer()"
                        class="text-xs font-bold text-sky-600 hover:text-sky-700 py-1.5 flex items-center gap-1 cursor-pointer">
                    <span>Ver Visor de Logos</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div class="flex items-center gap-1">
                    <button onclick="openLogosFolderModal()"
                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100 transition-colors cursor-pointer"
                            title="Cambiar carpeta de Google Drive">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>

                    <form action="/app/brand/refresh-logos" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="p-1.5 text-slate-400 hover:text-sky-600 rounded-md hover:bg-sky-50 transition-colors cursor-pointer"
                                title="Recargar y escanear Google Drive">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Tarjeta: Manual de Identidad de Marca -->
        <div class="saas-card overflow-hidden flex flex-col justify-between group transition-all hover:border-emerald-300">
            <div>
                <!-- Carátula de Manual -->
                <div class="h-64 bg-slate-900 overflow-hidden relative cursor-pointer group flex items-center justify-center p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950"
                     onclick="{{ !empty($brandManualUrl) ? "window.open('{$brandManualUrl}', '_blank')" : "openManualModal()" }}">
                    
                    <div class="text-center space-y-2">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto border border-emerald-500/30 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div class="text-white font-heading font-bold text-sm">
                            Manual de Marca Oficial
                        </div>
                        <div class="text-emerald-400/90 text-xs font-mono">
                            {{ !empty($brandManualUrl) ? 'PDF Conectado' : 'Pendiente de Conexión' }}
                        </div>
                    </div>

                    <!-- Badge de Estado -->
                    <div class="absolute bottom-3 right-3 bg-slate-900/85 backdrop-blur-xs text-white px-2.5 py-1 rounded-md text-[11px] font-bold flex items-center gap-1.5 shadow-sm z-10">
                        <span class="w-2 h-2 rounded-full {{ !empty($brandManualUrl) ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                        <span>{{ !empty($brandManualUrl) ? 'Activo' : 'Sin Enlace' }}</span>
                    </div>

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10">
                        <span class="px-3.5 py-2 bg-white text-slate-900 text-xs font-bold rounded-lg shadow-xl flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Abrir Manual en Drive
                        </span>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-heading font-bold text-base text-slate-900 line-clamp-1">
                                Manual de Identidad
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">
                                Directrices oficiales de estilo y uso
                            </p>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
                        Documento base de donde se extraen las reglas de tono, paleta de colores y pautas fotográficas.
                    </p>

                    @if(!empty($brandManualUrl))
                        <div class="pt-1">
                            <a href="{{ $brandManualUrl }}" target="_blank" rel="noopener noreferrer"
                               class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100/70 px-2.5 py-1.5 rounded-lg border border-emerald-100 flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-1.5 truncate">
                                    <span>📘</span>
                                    <span class="truncate">Ver Manual en Google Drive</span>
                                </span>
                                <span class="text-emerald-600 font-bold shrink-0">↗</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2">
                @if(!empty($brandManualUrl))
                    <a href="{{ $brandManualUrl }}" target="_blank" rel="noopener noreferrer"
                       class="text-xs font-bold text-emerald-600 hover:text-emerald-700 py-1.5 flex items-center gap-1 cursor-pointer">
                        <span>Abrir Documento</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @else
                    <button onclick="openManualModal()"
                            class="text-xs font-bold text-sky-600 hover:text-sky-700 py-1.5 flex items-center gap-1 cursor-pointer">
                        <span>+ Conectar Manual</span>
                    </button>
                @endif

                <button onclick="openManualModal()"
                        class="p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100 transition-colors cursor-pointer"
                        title="Editar enlace del manual">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
            </div>
        </div>

        <!-- 3. Tarjeta: Paleta de Color & Reglas de Estilo -->
        <div class="saas-card overflow-hidden flex flex-col justify-between group transition-all hover:border-indigo-300">
            <div>
                <!-- Muestra Visual de Colores y Tipografías -->
                <div class="h-64 p-5 flex flex-col justify-between rounded-t-2xl relative"
                     style="background-color: {{ $brand->bg_color ?? '#0F172A' }}">
                    
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold font-mono px-2.5 py-1 rounded bg-white/10 text-white backdrop-blur-xs border border-white/20">
                            Fondo Base: {{ $brand->bg_color ?? '#0F172A' }}
                        </span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                              style="background-color: {{ $brand->accent_color ?? '#38BDF8' }}; color: #0F172A">
                            Acento
                        </span>
                    </div>

                    <div class="space-y-2 text-center py-4">
                        <div class="text-2xl font-bold tracking-tight text-white font-heading"
                             style="color: {{ $brand->primary_color ?? '#0E7490' == '#0E7490' ? '#FFFFFF' : $brand->primary_color }}">
                            {{ $brand->name ?? 'JM Odontología Integral' }}
                        </div>
                        <div class="text-xs font-medium text-slate-300">
                            Títulos: <span class="font-mono text-sky-300">{{ $brand->font_style_title ?? 'serif-editorial' }}</span>
                        </div>
                    </div>

                    <!-- Muestra de Colores -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="p-2 rounded-lg bg-white/10 backdrop-blur-xs border border-white/15 text-center">
                            <div class="w-4 h-4 rounded-full mx-auto mb-1 border border-white/30" style="background-color: {{ $brand->primary_color ?? '#0E7490' }}"></div>
                            <div class="text-[9px] text-white/80 font-mono">Primario</div>
                        </div>
                        <div class="p-2 rounded-lg bg-white/10 backdrop-blur-xs border border-white/15 text-center">
                            <div class="w-4 h-4 rounded-full mx-auto mb-1 border border-white/30" style="background-color: {{ $brand->accent_color ?? '#38BDF8' }}"></div>
                            <div class="text-[9px] text-white/80 font-mono">Acento</div>
                        </div>
                        <div class="p-2 rounded-lg bg-white/10 backdrop-blur-xs border border-white/15 text-center">
                            <div class="w-4 h-4 rounded-full mx-auto mb-1 border border-white/30" style="background-color: {{ $brand->bg_color ?? '#0F172A' }}"></div>
                            <div class="text-[9px] text-white/80 font-mono">Fondo</div>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-heading font-bold text-base text-slate-900 line-clamp-1">
                                Reglas Visuales & Tipografía
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">
                                Parámetros inyectados en la IA
                            </p>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100 flex items-center justify-between">
                        <span>Posición Logo: <b>{{ $brand->logo_position ?? 'top-left' }}</b></span>
                        <span>Escala: <b>{{ $brand->logo_width_px ?? 180 }}px</b></span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 gap-2">
                <form action="/app/brand/sync-from-manual" method="POST" class="inline" onsubmit="return confirm('¿Deseas sincronizar y autocompletar todas las reglas y paleta oficial desde el Manual de Marca?');">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 py-1.5 flex items-center gap-1 cursor-pointer" title="Sincronizar automáticamente paleta y tipografía desde el Manual">
                        <span>🔄 Sincronizar Manual</span>
                    </button>
                </form>

                <div class="flex items-center gap-1">
                    <button onclick="openEditBrandModal()"
                            class="text-xs font-bold text-sky-600 hover:text-sky-700 py-1.5 flex items-center gap-1 cursor-pointer">
                        <span>Editar</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <button onclick="openEditBrandModal()"
                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100 transition-colors cursor-pointer"
                            title="Modificar colores y tipografías">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: VISOR INTERACTIVO DE LOGOTIPOS (Slider idéntico a Personajes)     -->
<!-- ========================================================================= -->
<div id="logosViewerModal" class="fixed inset-0 bg-slate-900/85 backdrop-blur-xs hidden z-50 flex items-center justify-center p-3 sm:p-6" onclick="closeLogosViewer()">
    <div class="bg-white rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col relative animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
        
        <!-- Header del Visor -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-white z-10">
            <div>
                <h3 id="viewerLogoTitle" class="text-base font-bold text-slate-900 font-heading">Catálogo de Logotipos</h3>
                <p id="viewerLogoFilename" class="text-xs text-slate-500 font-mono mt-0.5"></p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="toggleViewerBg()" class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold cursor-pointer flex items-center gap-1 transition-colors">
                    <span>Fondo:</span>
                    <span id="viewerBgName" class="font-bold text-sky-600">Oscuro 🌓</span>
                </button>
                <span id="viewerCounter" class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md"></span>
                <button onclick="closeLogosViewer()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm transition-colors cursor-pointer">
                    ✕
                </button>
            </div>
        </div>

        <!-- Área Central de Imagen con Flechas de Navegación -->
        <div id="viewerLogoArea" class="relative p-6 sm:p-10 bg-slate-950 flex items-center justify-center select-none overflow-hidden min-h-[440px] max-h-[70vh] transition-colors">
            
            <!-- Flecha Anterior -->
            <button onclick="prevLogo()"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 hover:bg-white text-slate-900 shadow-xl border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95 cursor-pointer"
                    title="Anterior (Flecha izquierda)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Logo Principal Centrado -->
            <img id="viewerMainLogoImg" src="" alt="Logo de Marca" class="max-h-[50vh] max-w-[85%] object-contain filter drop-shadow-2xl transition-all duration-200 z-10">

            <!-- Flecha Siguiente -->
            <button onclick="nextLogo()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 hover:bg-white text-slate-900 shadow-xl border border-slate-200 flex items-center justify-center transition-all z-20 hover:scale-110 active:scale-95 cursor-pointer"
                    title="Siguiente (Flecha derecha)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Tira de Miniaturas Inferior -->
        <div id="viewerLogoThumbnails" class="p-3 bg-slate-900 flex items-center gap-2 overflow-x-auto border-t border-slate-800 scroll-smooth">
            <!-- Renderizado dinámico en JS -->
        </div>

        <!-- Footer con Acciones -->
        <div class="p-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-[11px] text-slate-400 flex items-center gap-2">
                <span>⌨️ Usa <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-600 font-mono">←</kbd> <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-slate-600 font-mono">→</kbd> para navegar</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="setDefaultLogoAjax()" id="setDefaultLogoBtn" class="w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span id="setDefaultLogoBtnText">Establecer como Logo Principal</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: VINCULAR CARPETA DE LOGOS GOOGLE DRIVE                           -->
<!-- ========================================================================= -->
<div id="logosFolderModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" onclick="closeLogosFolderModal()">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    📁
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Carpeta de Logos en Google Drive
                </h3>
            </div>
            <button onclick="closeLogosFolderModal()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
        </div>

        <form action="/app/brand/save-logos-folder" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-700">Enlace de la Carpeta en Google Drive</label>
                <input type="text" name="gdrive_logos_folder_url"
                       placeholder="https://drive.google.com/drive/folders/1ABCxyz..."
                       value="{{ $logosFolderUrl ?? '' }}"
                       required
                       class="w-full text-xs px-3 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 font-mono">
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Asegúrate de que la carpeta de Google Drive tenga permisos públicos de lectura. El sistema indexará automáticamente los archivos PNG y SVG transparentes.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeLogosFolderModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs cursor-pointer shadow-xs">
                    Vincular y Escanear Logos
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: VINCULAR MANUAL DE MARCA GOOGLE DRIVE                            -->
<!-- ========================================================================= -->
<div id="manualModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" onclick="closeManualModal()">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    📘
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Manual de Marca en Google Drive
                </h3>
            </div>
            <button onclick="closeManualModal()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
        </div>

        <form action="/app/brand/save-manual" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-700">Enlace del Archivo PDF o Carpeta</label>
                <input type="text" name="gdrive_brand_manual_url"
                       placeholder="https://drive.google.com/file/d/1ABCxyz... o /drive/folders/..."
                       value="{{ $brandManualUrl ?? '' }}"
                       required
                       class="w-full text-xs px-3 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 font-mono">
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Pega el enlace compartido de Google Drive donde está alojado el PDF del manual de identidad corporativa.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeManualModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer shadow-xs">
                    Guardar Enlace del Manual
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: EDITAR IDENTIDAD Y COLORES DE MARCA                              -->
<!-- ========================================================================= -->
<div id="editBrandModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" onclick="closeEditBrandModal()">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-150 overflow-y-auto max-h-[90vh]" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    🎨
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Editar Identidad & Colores
                </h3>
            </div>
            <button onclick="closeEditBrandModal()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
        </div>

        <form action="/app/brand/save-identity" method="POST" class="space-y-4">
            @csrf

            <!-- Nombre de Marca -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-700">Nombre Oficial de la Marca</label>
                <input type="text" name="name" value="{{ $brand->name ?? 'JM Odontología Integral' }}" required
                       class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500">
            </div>

            <!-- Paleta Cromática -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-800">Paleta Cromática Oficial</div>

                <!-- Primario -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <input type="color" id="modal_primary_picker" value="{{ $brand->primary_color ?? '#16345F' }}"
                               onchange="document.getElementById('modal_primary_hex').value = this.value"
                               class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white">
                        <div>
                            <div class="text-xs font-semibold text-slate-800">Color Primario</div>
                            <div class="text-[10px] text-slate-500">Fondos y bases de láminas</div>
                        </div>
                    </div>
                    <input type="text" id="modal_primary_hex" name="primary_color" value="{{ $brand->primary_color ?? '#16345F' }}"
                           onchange="document.getElementById('modal_primary_picker').value = this.value"
                           class="w-24 text-xs font-mono px-2 py-1.5 rounded-md border border-slate-200 text-center uppercase">
                </div>

                <!-- Acento -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <input type="color" id="modal_accent_picker" value="{{ $brand->accent_color ?? '#7DD3FC' }}"
                               onchange="document.getElementById('modal_accent_hex').value = this.value"
                               class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white">
                        <div>
                            <div class="text-xs font-semibold text-slate-800">Color de Acento</div>
                            <div class="text-[10px] text-slate-500">Badges y luces de iluminación</div>
                        </div>
                    </div>
                    <input type="text" id="modal_accent_hex" name="accent_color" value="{{ $brand->accent_color ?? '#7DD3FC' }}"
                           onchange="document.getElementById('modal_accent_picker').value = this.value"
                           class="w-24 text-xs font-mono px-2 py-1.5 rounded-md border border-slate-200 text-center uppercase">
                </div>

                <!-- Fondo Base -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <input type="color" id="modal_bg_picker" value="{{ $brand->bg_color ?? '#0F172A' }}"
                               onchange="document.getElementById('modal_bg_hex').value = this.value"
                               class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white">
                        <div>
                            <div class="text-xs font-semibold text-slate-800">Color de Fondo Base</div>
                            <div class="text-[10px] text-slate-500">Contraste oscuro de referencia</div>
                        </div>
                    </div>
                    <input type="text" id="modal_bg_hex" name="bg_color" value="{{ $brand->bg_color ?? '#0F172A' }}"
                           onchange="document.getElementById('modal_bg_picker').value = this.value"
                           class="w-24 text-xs font-mono px-2 py-1.5 rounded-md border border-slate-200 text-center uppercase">
                </div>
            </div>

            <!-- Composición de Logo -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-800">Composición de Logotipo</div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-700">Posición en Láminas</label>
                    <select name="logo_position" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 bg-white">
                        <option value="top-left" {{ ($brand->logo_position ?? '') === 'top-left' ? 'selected' : '' }}>Superior Izquierda (Recomendado)</option>
                        <option value="top-right" {{ ($brand->logo_position ?? '') === 'top-right' ? 'selected' : '' }}>Superior Derecha</option>
                        <option value="bottom-left" {{ ($brand->logo_position ?? '') === 'bottom-left' ? 'selected' : '' }}>Inferior Izquierda</option>
                        <option value="bottom-right" {{ ($brand->logo_position ?? '') === 'bottom-right' ? 'selected' : '' }}>Inferior Derecha</option>
                        <option value="center" {{ ($brand->logo_position ?? '') === 'center' ? 'selected' : '' }}>Centrado</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">Ancho en Canvas</span>
                        <span class="font-mono text-slate-500" id="modal_width_label">{{ $brand->logo_width_px ?? 180 }}px</span>
                    </div>
                    <input type="range" name="logo_width_px" min="100" max="350" step="10" value="{{ $brand->logo_width_px ?? 180 }}"
                           oninput="document.getElementById('modal_width_label').innerText = this.value + 'px'"
                           class="w-full accent-sky-600">
                </div>
            </div>

            <!-- Tipografías -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-800">Estilos Tipográficos</div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-700">Títulos</label>
                    <select name="font_style_title" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 bg-white">
                        <option value="serif-editorial" {{ ($brand->font_style_title ?? '') === 'serif-editorial' ? 'selected' : '' }}>Serif Editorial (Elegante / Médico)</option>
                        <option value="sans-modern" {{ ($brand->font_style_title ?? '') === 'sans-modern' ? 'selected' : '' }}>Sans-Serif Moderna (Limpia)</option>
                        <option value="bold-tech" {{ ($brand->font_style_title ?? '') === 'bold-tech' ? 'selected' : '' }}>Bold Tech (Impacto)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-700">Cuerpos de Texto</label>
                    <select name="font_style_body" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 bg-white">
                        <option value="sans-modern" {{ ($brand->font_style_body ?? '') === 'sans-modern' ? 'selected' : '' }}>Sans-Serif Moderna (Legible)</option>
                        <option value="clean-neutral" {{ ($brand->font_style_body ?? '') === 'clean-neutral' ? 'selected' : '' }}>Neutral / Minimalista</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditBrandModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs cursor-pointer shadow-xs">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT DEL VISOR INTERACTIVO Y MODALES                                -->
<!-- ========================================================================= -->
<script>
    const brandLogos = @json($logos ?? []);
    let currentLogoIndex = 0;
    let isDarkBg = true;
    let activeDefaultLogoId = '{{ $brandRules['default_logo_id'] ?? '' }}';

    function openLogosViewer(startIndex = 0) {
        if (!brandLogos || brandLogos.length === 0) {
            openLogosFolderModal();
            return;
        }

        currentLogoIndex = startIndex;
        renderLogoViewerState();
        document.getElementById('logosViewerModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeLogosViewer() {
        document.getElementById('logosViewerModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function renderLogoViewerState() {
        if (!brandLogos || brandLogos.length === 0) return;

        const logo = brandLogos[currentLogoIndex];
        const mainImg = document.getElementById('viewerMainLogoImg');
        const title = document.getElementById('viewerLogoTitle');
        const filename = document.getElementById('viewerLogoFilename');
        const counter = document.getElementById('viewerCounter');
        const setCoverBtn = document.getElementById('setDefaultLogoBtn');
        const setCoverBtnText = document.getElementById('setDefaultLogoBtnText');

        mainImg.src = logo.url;
        title.innerText = logo.variant_label || 'Variante de Logotipo';
        filename.innerText = logo.filename || 'logo.png';
        counter.innerText = `${currentLogoIndex + 1} / ${brandLogos.length}`;

        // Estado del botón predeterminado
        const isCurrentDefault = (activeDefaultLogoId && activeDefaultLogoId === logo.id) || (!activeDefaultLogoId && currentLogoIndex === 0);
        if (isCurrentDefault) {
            setCoverBtn.className = "w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-bold bg-emerald-600 text-white transition-colors shadow-sm flex items-center justify-center gap-1.5";
            setCoverBtnText.innerText = "✓ Logo Principal Actual";
        } else {
            setCoverBtn.className = "w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm flex items-center justify-center gap-1.5 cursor-pointer";
            setCoverBtnText.innerText = "Establecer como Logo Principal";
        }

        // Renderizar tira de miniaturas inferior
        renderThumbnails();
    }

    function renderThumbnails() {
        const container = document.getElementById('viewerLogoThumbnails');
        container.innerHTML = '';

        brandLogos.forEach((logo, idx) => {
            const isSelected = (idx === currentLogoIndex);
            const isDefault = (activeDefaultLogoId && activeDefaultLogoId === logo.id) || (!activeDefaultLogoId && idx === 0);

            const thumb = document.createElement('div');
            thumb.className = `w-14 h-14 rounded-lg overflow-hidden shrink-0 cursor-pointer border-2 transition-all p-1 flex items-center justify-center relative ${
                isSelected ? 'border-sky-400 ring-2 ring-sky-400/40 bg-slate-800 scale-105' : 'border-slate-700 hover:border-slate-500 bg-slate-900 opacity-70 hover:opacity-100'
            }`;
            thumb.onclick = () => {
                currentLogoIndex = idx;
                renderLogoViewerState();
            };

            const img = document.createElement('img');
            img.src = logo.thumb || logo.url;
            img.className = "max-h-full max-w-full object-contain";
            thumb.appendChild(img);

            if (isDefault) {
                const dot = document.createElement('span');
                dot.className = "absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 border border-slate-900";
                thumb.appendChild(dot);
            }

            container.appendChild(thumb);
        });

        // Scroll automático al thumbnail activo
        const activeThumb = container.children[currentLogoIndex];
        if (activeThumb) {
            activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    function prevLogo() {
        if (currentLogoIndex > 0) {
            currentLogoIndex--;
        } else {
            currentLogoIndex = brandLogos.length - 1;
        }
        renderLogoViewerState();
    }

    function nextLogo() {
        if (currentLogoIndex < brandLogos.length - 1) {
            currentLogoIndex++;
        } else {
            currentLogoIndex = 0;
        }
        renderLogoViewerState();
    }

    function toggleViewerBg() {
        const area = document.getElementById('viewerLogoArea');
        const bgName = document.getElementById('viewerBgName');

        if (isDarkBg) {
            area.classList.remove('bg-slate-950');
            area.classList.add('bg-slate-100');
            bgName.innerText = "Claro ☀️";
            isDarkBg = false;
        } else {
            area.classList.remove('bg-slate-100');
            area.classList.add('bg-slate-950');
            bgName.innerText = "Oscuro 🌓";
            isDarkBg = true;
        }
    }

    async function setDefaultLogoAjax() {
        const logo = brandLogos[currentLogoIndex];
        if (!logo) return;

        const btnText = document.getElementById('setDefaultLogoBtnText');
        btnText.innerText = "Guardando...";

        try {
            const res = await fetch('/app/brand/set-cover-logo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ logo_id: logo.id })
            });

            activeDefaultLogoId = logo.id;
            renderLogoViewerState();

            // Actualizar imagen en la tarjeta principal
            const coverImg = document.getElementById('brand-cover-logo-img');
            if (coverImg) {
                coverImg.src = logo.url;
            }
        } catch (e) {
            btnText.innerText = "Establecer como Logo Principal";
        }
    }

    // Navegación por teclado (Flechas izquierda / derecha / Escape)
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('logosViewerModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'ArrowLeft') prevLogo();
            if (e.key === 'ArrowRight') nextLogo();
            if (e.key === 'Escape') closeLogosViewer();
        }
    });

    // Control de Modales
    function openLogosFolderModal() {
        document.getElementById('logosFolderModal').classList.remove('hidden');
    }
    function closeLogosFolderModal() {
        document.getElementById('logosFolderModal').classList.add('hidden');
    }

    function openManualModal() {
        document.getElementById('manualModal').classList.remove('hidden');
    }
    function closeManualModal() {
        document.getElementById('manualModal').classList.add('hidden');
    }

    function openEditBrandModal() {
        document.getElementById('editBrandModal').classList.remove('hidden');
    }
    function closeEditBrandModal() {
        document.getElementById('editBrandModal').classList.add('hidden');
    }
</script>
@endsection
