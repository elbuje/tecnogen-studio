@extends('layouts.app')

@section('title', 'Definición de Marca - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header Principal -->
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
                            {{ $brand->name ?? 'Marca Oficial' }}
                        </span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Catálogo de logotipos en <strong>Google Drive</strong>, Manual de Marca y reglas de estilo para la IA.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
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

            <button onclick="openLogosModal()" class="px-3.5 py-2 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Conectar Logos (Drive)</span>
            </button>

            <button onclick="openManualModal()" class="px-3.5 py-2 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Manual de Marca (Drive)</span>
            </button>
        </div>
    </div>

    <!-- Banner Informativo del Agente de IA y Selección de Logos -->
    <div class="saas-card p-5 bg-gradient-to-r from-sky-50/70 via-indigo-50/40 to-slate-50 border-sky-200">
        <div class="flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-lg bg-sky-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex-1 text-xs space-y-1">
                <div class="flex items-center justify-between">
                    <h3 class="font-heading font-bold text-slate-900 text-sm">
                        🤖 Selección Inteligente de Logos por el Agente en Generación
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">
                        ✓ Pipeline gpt-image-2.5-sunburst Activo
                    </span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    El agente de IA <strong>analiza la carpeta completa de Google Drive</strong> con todas las variantes registradas (logo blanco sobre fondos oscuros, logo color en fondos claros, isotipo compacto en slides de desarrollo y logo institucional en portadas).
                    Durante la composición del canvas de referencia enviado a <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 font-mono text-sky-700">images.edit</code>, el agente <strong>elige y posiciona la variante idónea</strong> garantizando máximo contraste y fidelidad de marca.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda / Central: Catálogo de Logos y Manual (2 columnas) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card: Catálogo de Logos en Google Drive -->
            <div class="saas-card p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-heading font-bold text-lg text-slate-900">
                                Catálogo de Logotipos
                            </h2>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                {{ count($logos) }} {{ count($logos) === 1 ? 'Variante' : 'Variantes' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Variantes detectadas en la carpeta de Google Drive configurada.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!empty($logosFolderUrl))
                            <a href="{{ $logosFolderUrl }}" target="_blank" rel="noopener noreferrer"
                               class="text-xs font-semibold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100/70 px-3 py-1.5 rounded-lg border border-sky-100 flex items-center gap-1.5 transition-colors">
                                <span>📁 Ver en Google Drive</span>
                                <span class="text-sky-500 font-bold">↗</span>
                            </a>
                        @endif

                        <form action="/app/brand/refresh-logos" method="POST">
                            @csrf
                            <button type="submit" title="Recargar y escanear Google Drive"
                                    class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer text-xs flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span class="hidden sm:inline">Actualizar</span>
                            </button>
                        </form>
                    </div>
                </div>

                @if(count($logos) === 0)
                    <div class="p-8 text-center rounded-xl bg-slate-50 border border-dashed border-slate-300">
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 font-heading">No se han detectado logotipos todavía</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                            Ingresa el enlace a la carpeta de Google Drive donde tienes guardados los archivos PNG o SVG transparentes de tu marca.
                        </p>
                        <button onclick="openLogosModal()" class="mt-4 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-colors cursor-pointer">
                            Vincular Carpeta de Drive
                        </button>
                    </div>
                @else
                    <!-- Grid de Logos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($logos as $index => $logo)
                            @php
                                $isCover = (!empty($brandRules['default_logo_id']) && $brandRules['default_logo_id'] == $logo['id']) || ($index === 0 && empty($brandRules['default_logo_id']));
                            @endphp
                            <div class="saas-card overflow-hidden border {{ $isCover ? 'border-sky-500 ring-2 ring-sky-500/20' : 'border-slate-200' }} flex flex-col justify-between group transition-all">
                                <!-- Área de Visualización con Fondo de Contraste Alternable -->
                                <div class="h-36 bg-slate-900 relative p-4 flex items-center justify-center overflow-hidden checkerboard-bg group"
                                     id="logo-box-{{ $index }}">
                                    
                                    <img src="{{ $logo['url'] }}"
                                         alt="{{ $logo['filename'] }}"
                                         class="max-h-full max-w-full object-contain filter drop-shadow-md group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                                         onclick="openLogoLightbox('{{ $logo['url'] }}', '{{ $logo['filename'] }}', '{{ $logo['variant_label'] }}')">

                                    <!-- Badge de Estado Predeterminado -->
                                    @if($isCover)
                                        <div class="absolute top-2 left-2 bg-sky-600 text-white px-2 py-0.5 rounded text-[10px] font-bold shadow-xs flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <span>Predeterminado</span>
                                        </div>
                                    @endif

                                    <!-- Botón para alternar fondo blanco / oscuro -->
                                    <button type="button"
                                            onclick="toggleBackground('logo-box-{{ $index }}')"
                                            title="Alternar fondo Claro / Oscuro"
                                            class="absolute top-2 right-2 p-1.5 rounded-md bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-[10px] backdrop-blur-xs transition-colors cursor-pointer">
                                        🌓
                                    </button>
                                </div>

                                <!-- Metadatos y Acciones -->
                                <div class="p-3.5 space-y-2 bg-white flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 truncate" title="{{ $logo['filename'] }}">
                                            {{ $logo['variant_label'] }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono truncate">
                                            {{ $logo['filename'] }}
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1">
                                        @if(!$isCover)
                                            <form action="/app/brand/set-cover-logo" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="logo_id" value="{{ $logo['id'] }}">
                                                <button type="submit" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 hover:underline cursor-pointer">
                                                    Usar Principal
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                                                <span>✓</span> Principal
                                            </span>
                                        @endif

                                        <button onclick="openLogoLightbox('{{ $logo['url'] }}', '{{ $logo['filename'] }}', '{{ $logo['variant_label'] }}')"
                                                class="p-1 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
                                                title="Ver en Grande">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Card: Manual de Marca en Google Drive -->
            <div class="saas-card p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-heading font-bold text-lg text-slate-900">
                                Manual de Identidad de Marca
                            </h2>
                            @if(!empty($brandManualUrl))
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                    Conectado
                                </span>
                            @else
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                    Pendiente
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Documento oficial de identidad visual utilizado para orientar al motor de IA y diseño.
                        </p>
                    </div>

                    <button onclick="openManualModal()" class="text-xs font-bold text-sky-600 hover:text-sky-700 bg-sky-50 px-3 py-1.5 rounded-lg border border-sky-100 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>{{ !empty($brandManualUrl) ? 'Cambiar Enlace' : 'Vincular Manual' }}</span>
                    </button>
                </div>

                @if(!empty($brandManualUrl))
                    <div class="p-5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-heading font-bold text-slate-900 text-sm">
                                    Manual de Marca Vinculado
                                </h4>
                                <p class="text-xs text-slate-500 font-mono truncate max-w-xs sm:max-w-md mt-0.5">
                                    {{ $brandManualUrl }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ $brandManualUrl }}" target="_blank" rel="noopener noreferrer"
                           class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <span>Abrir en Google Drive</span>
                            <span class="font-bold">↗</span>
                        </a>
                    </div>
                @else
                    <div class="p-6 text-center rounded-xl bg-slate-50 border border-dashed border-slate-300">
                        <p class="text-xs text-slate-500">
                            No has registrado el enlace a tu Manual de Marca. Puedes enlazar un archivo PDF o carpeta de Google Drive.
                        </p>
                        <button onclick="openManualModal()" class="mt-3 px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer">
                            + Vincular Manual en Google Drive
                        </button>
                    </div>
                @endif
            </div>

        </div>

        <!-- Columna Derecha: Configuración de Colores & Identidad Visual -->
        <div class="space-y-6">
            <form action="/app/brand/save-identity" method="POST" class="saas-card p-6 space-y-5">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-bold text-base text-slate-900">
                        Paleta & Reglas Visuales
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Colores y tipografía inyectados en los prompts y canvas de IA.
                    </p>
                </div>

                <!-- Nombre de Marca -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700">Nombre de la Marca</label>
                    <input type="text" name="name" value="{{ $brand->name ?? 'JM Odontología Integral' }}" required
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>

                <!-- Colores de Marca -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div class="text-xs font-bold text-slate-800">Paleta Cromática</div>

                    <!-- Color Primario -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <input type="color" id="primary_picker" value="{{ $brand->primary_color ?? '#16345F' }}"
                                   onchange="document.getElementById('primary_hex').value = this.value"
                                   class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white">
                            <div>
                                <div class="text-xs font-semibold text-slate-800">Color Primario</div>
                                <div class="text-[10px] text-slate-500">Fondos principales y base</div>
                            </div>
                        </div>
                        <input type="text" id="primary_hex" name="primary_color" value="{{ $brand->primary_color ?? '#16345F' }}"
                               onchange="document.getElementById('primary_picker').value = this.value"
                               class="w-24 text-xs font-mono px-2 py-1.5 rounded-md border border-slate-200 text-center uppercase">
                    </div>

                    <!-- Color de Acento -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <input type="color" id="accent_picker" value="{{ $brand->accent_color ?? '#7DD3FC' }}"
                                   onchange="document.getElementById('accent_hex').value = this.value"
                                   class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white">
                            <div>
                                <div class="text-xs font-semibold text-slate-800">Color de Acento</div>
                                <div class="text-[10px] text-slate-500">Badges, luces y destacados</div>
                            </div>
                        </div>
                        <input type="text" id="accent_hex" name="accent_color" value="{{ $brand->accent_color ?? '#7DD3FC' }}"
                               onchange="document.getElementById('accent_picker').value = this.value"
                               class="w-24 text-xs font-mono px-2 py-1.5 rounded-md border border-slate-200 text-center uppercase">
                    </div>

                    <!-- Color de Fondo Base -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <input type="color" id="bg_picker" value="{{ $brand->bg_color ?? '#0F172A' }}"
                                   onchange="document.getElementById('bg_hex').value = this.value"
                                   class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white">
                            <div>
                                <div class="text-xs font-semibold text-slate-800">Color de Fondo Base</div>
                                <div class="text-[10px] text-slate-500">Dark / Canvas contrast</div>
                            </div>
                        </div>
                        <input type="text" id="bg_hex" name="bg_color" value="{{ $brand->bg_color ?? '#0F172A' }}"
                               onchange="document.getElementById('bg_picker').value = this.value"
                               class="w-24 text-xs font-mono px-2 py-1.5 rounded-md border border-slate-200 text-center uppercase">
                    </div>
                </div>

                <!-- Posición y Tamaño de Logo -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div class="text-xs font-bold text-slate-800">Composición de Logotipo</div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-700">Posición Predeterminada</label>
                        <select name="logo_position" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 bg-white">
                            <option value="top-left" {{ ($brand->logo_position ?? '') === 'top-left' ? 'selected' : '' }}>Superior Izquierda (Recomendado)</option>
                            <option value="top-right" {{ ($brand->logo_position ?? '') === 'top-right' ? 'selected' : '' }}>Superior Derecha</option>
                            <option value="bottom-left" {{ ($brand->logo_position ?? '') === 'bottom-left' ? 'selected' : '' }}>Inferior Izquierda</option>
                            <option value="bottom-right" {{ ($brand->logo_position ?? '') === 'bottom-right' ? 'selected' : '' }}>Inferior Derecha</option>
                            <option value="center" {{ ($brand->logo_position ?? '') === 'center' ? 'selected' : '' }}>Centrado</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700">Ancho en Canvas</span>
                            <span class="font-mono text-slate-500" id="width_label">{{ $brand->logo_width_px ?? 180 }}px</span>
                        </div>
                        <input type="range" name="logo_width_px" min="100" max="350" step="10" value="{{ $brand->logo_width_px ?? 180 }}"
                               oninput="document.getElementById('width_label').innerText = this.value + 'px'"
                               class="w-full accent-sky-600">
                    </div>
                </div>

                <!-- Tipografías -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div class="text-xs font-bold text-slate-800">Tipografía de Marca</div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-700">Estilo para Títulos</label>
                        <select name="font_style_title" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 bg-white">
                            <option value="serif-editorial" {{ ($brand->font_style_title ?? '') === 'serif-editorial' ? 'selected' : '' }}>Serif Editorial (Elegante / Médico)</option>
                            <option value="sans-modern" {{ ($brand->font_style_title ?? '') === 'sans-modern' ? 'selected' : '' }}>Sans-Serif Moderna (Limpia)</option>
                            <option value="bold-tech" {{ ($brand->font_style_title ?? '') === 'bold-tech' ? 'selected' : '' }}>Bold Tech (Impacto)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-700">Estilo para Cuerpos</label>
                        <select name="font_style_body" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 bg-white">
                            <option value="sans-modern" {{ ($brand->font_style_body ?? '') === 'sans-modern' ? 'selected' : '' }}>Sans-Serif Moderna (Legible)</option>
                            <option value="clean-neutral" {{ ($brand->font_style_body ?? '') === 'clean-neutral' ? 'selected' : '' }}>Neutral / Minimalista</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full py-2.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                        Guardar Configuración de Marca
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 1: Vincular Carpeta de Logos en Google Drive -->
<div id="logosModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    📁
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Carpeta de Logos en Google Drive
                </h3>
            </div>
            <button onclick="closeLogosModal()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
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
                    Asegúrate de que la carpeta de Google Drive tenga permisos de <strong>"Cualquier persona con el enlace puede ver"</strong>. El sistema indexará automáticamente todas las imágenes PNG, SVG y JPG con transparencia.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeLogosModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs cursor-pointer shadow-xs">
                    Vincular y Escanear Logos
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Vincular Manual de Marca en Google Drive -->
<div id="manualModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-150">
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
                <label class="text-xs font-semibold text-slate-700">Enlace del Manual (Archivo PDF o Carpeta)</label>
                <input type="text" name="gdrive_brand_manual_url"
                       placeholder="https://drive.google.com/file/d/1ABCxyz... o /drive/folders/..."
                       value="{{ $brandManualUrl ?? '' }}"
                       required
                       class="w-full text-xs px-3 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:border-sky-500 font-mono">
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Pega el enlace público o compartido de Google Drive donde está alojado el PDF del manual de identidad de tu marca.
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

<!-- Modal 3: Visor Lightbox para Logos -->
<div id="lightboxModal" class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex flex-col items-center justify-center p-6 hidden">
    <div class="absolute top-4 right-6 flex items-center gap-3">
        <button onclick="toggleLightboxBg()" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-200 text-xs font-semibold hover:bg-slate-700 cursor-pointer">
            Fondo: <span id="lightbox-bg-name">Oscuro</span>
        </button>
        <button onclick="closeLogoLightbox()" class="text-slate-400 hover:text-white text-2xl cursor-pointer">✕</button>
    </div>

    <div class="max-w-3xl max-h-[75vh] p-8 rounded-2xl flex items-center justify-center transition-colors bg-slate-900" id="lightbox-container">
        <img id="lightbox-img" src="" alt="" class="max-h-[60vh] max-w-full object-contain filter drop-shadow-2xl">
    </div>

    <div class="mt-4 text-center text-white space-y-1">
        <div id="lightbox-title" class="text-sm font-bold font-heading"></div>
        <div id="lightbox-filename" class="text-xs text-slate-400 font-mono"></div>
    </div>
</div>

<script>
    function openLogosModal() {
        document.getElementById('logosModal').classList.remove('hidden');
    }
    function closeLogosModal() {
        document.getElementById('logosModal').classList.add('hidden');
    }

    function openManualModal() {
        document.getElementById('manualModal').classList.remove('hidden');
    }
    function closeManualModal() {
        document.getElementById('manualModal').classList.add('hidden');
    }

    function toggleBackground(elementId) {
        const el = document.getElementById(elementId);
        if (el.classList.contains('bg-slate-900')) {
            el.classList.remove('bg-slate-900');
            el.classList.add('bg-slate-100');
        } else {
            el.classList.remove('bg-slate-100');
            el.classList.add('bg-slate-900');
        }
    }

    function openLogoLightbox(url, filename, title) {
        document.getElementById('lightbox-img').src = url;
        document.getElementById('lightbox-title').innerText = title;
        document.getElementById('lightbox-filename').innerText = filename;
        document.getElementById('lightboxModal').classList.remove('hidden');
    }

    function closeLogoLightbox() {
        document.getElementById('lightboxModal').classList.add('hidden');
    }

    let isDarkLightbox = true;
    function toggleLightboxBg() {
        const container = document.getElementById('lightbox-container');
        const name = document.getElementById('lightbox-bg-name');
        if (isDarkLightbox) {
            container.classList.remove('bg-slate-900');
            container.classList.add('bg-white');
            name.innerText = 'Claro';
            isDarkLightbox = false;
        } else {
            container.classList.remove('bg-white');
            container.classList.add('bg-slate-900');
            name.innerText = 'Oscuro';
            isDarkLightbox = true;
        }
    }
</script>
@endsection
