@extends('layouts.app')

@section('title', 'Generador de Contenido - TecnoGen Studio')

@section('content')
<div class="space-y-6 pb-20">
    <!-- Header del Generador -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">Studio Pro</span>
                <span class="text-xs text-slate-500 font-medium">Asistente IA + Plantillas Modulares</span>
            </div>
            <h1 class="text-2xl font-bold font-heading text-slate-900">Generador de Contenido</h1>
            <p class="text-sm text-slate-500 mt-1">Crea carruseles, posts únicos y guiones clínicos con asistencia de IA en cada campo o pegando tu borrador externo.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="openPasteModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-sm flex items-center gap-2 transition-all shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>📋 Pegar Guion Externo</span>
            </button>

            <button type="button" onclick="openFullAiModal()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white font-semibold text-sm flex items-center gap-2 transition-all shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>✨ Redactar con IA</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form id="contentForm" action="/app/contents/create" method="POST" onsubmit="handleFormSubmit(event)" class="space-y-6">
        @csrf


        <!-- 1. BARRA DE CONFIGURACIÓN MODULAR -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-6">
            <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">1. Configuración de Identidad y Formato</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Formato -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Formato de Publicación</label>
                    <select name="type" id="contentType" onchange="handleFormatChange()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden transition-all cursor-pointer">
                        <option value="carousel" selected>🎠 Carrusel Instagram (1080x1350)</option>
                        <option value="single_post">🖼️ Post Único (1080x1080)</option>
                        <option value="reel_script">📱 Reel / Story (1080x1920)</option>
                    </select>
                </div>

                <!-- Personaje -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Personaje / Especialista</label>
                    <select name="character_id" id="characterSelect" onchange="updatePromptPreview()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden transition-all cursor-pointer">
                        <option value="">👤 Sin personaje específico</option>
                        @foreach($characters as $char)
                            <option value="{{ $char['id'] }}" data-name="{{ $char['name'] }}" data-role="{{ $char['role'] }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $char['name'] }} ({{ $char['role'] ?? 'Especialista' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo Visual -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tipo Visual (28 Estructuras)</label>
                    <select name="visual_type" id="visualTypeSelect" onchange="updatePromptPreview()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden transition-all cursor-pointer">
                        @foreach($visualTypes as $vt)
                            <option value="{{ $vt['name'] }}" {{ $vt['number'] == 1 ? 'selected' : '' }}>
                                #{{ sprintf('%02d', $vt['number']) }} · {{ $vt['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Deslizador / Paginador -->
                <div id="paginatorWrapper">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Paginador / Deslizador</label>
                    <select name="paginator_style" id="paginatorSelect" onchange="updatePromptPreview()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden transition-all cursor-pointer">
                        @foreach($paginators as $pag)
                            <option value="{{ $pag['sheetLabel'] }}" {{ ($currentPaginator ?? '') == $pag['sheetLabel'] || $pag['number'] == 1 ? 'selected' : '' }}>
                                #{{ sprintf('%02d', $pag['number']) }} · {{ $pag['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Previsualización de Marca Activa -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-4">
                    <span class="font-semibold text-slate-600">Paleta Oficial:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: {{ $brand->primary_color ?? '#16345F' }}" title="Primario"></span>
                        <span class="font-mono text-slate-700">{{ $brand->primary_color ?? '#16345F' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: {{ $brand->accent_color ?? '#7DD3FC' }}" title="Acento"></span>
                        <span class="font-mono text-slate-700">{{ $brand->accent_color ?? '#7DD3FC' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full border border-slate-200" style="background-color: {{ $brand->bg_color ?? '#1D1D1B' }}" title="Fondo"></span>
                        <span class="font-mono text-slate-700">{{ $brand->bg_color ?? '#1D1D1B' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-600">Logo:</span>
                    @if(!empty($brandLogos))
                        <select name="logo_id" id="logoSelect" class="px-2 py-1 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-800 cursor-pointer">
                            @foreach($brandLogos as $logo)
                                <option value="{{ $logo['id'] }}" {{ ($defaultLogoId ?? '') == $logo['id'] ? 'selected' : '' }}>
                                    {{ $logo['variant_label'] }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <span class="text-slate-400 italic">Logo Predeterminado</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. CONSTRUCTOR DE CONTENIDO & LÁMINAS DINÁMICAS -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="sectionTitle" class="text-lg font-bold text-slate-900 font-heading">Estructura del Guion y Láminas</h2>
                    <p id="sectionSubtitle" class="text-xs text-slate-500">Haz clic en el botón <span class="text-sky-600 font-semibold">✨ IA</span> en cualquier cuadro de texto para generarlo o mejorarlo al instante.</p>
                </div>

                <button type="button" id="btnAddSlide" onclick="addSlide()" class="px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-semibold text-xs border border-sky-200 flex items-center gap-1.5 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Agregar Lámina</span>
                </button>
            </div>

            <!-- LÁMINA PRINCIPAL (SLIDE 1 / PORTADA / POST ÚNICO) -->
            <div class="bg-white p-6 rounded-2xl border-2 border-sky-200 shadow-xs space-y-4 relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span id="slide1NumBadge" class="w-7 h-7 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center">01</span>
                        <span id="slide1TitleLabel" class="font-bold text-sm text-slate-900">Lámina de Portada / Gancho Principal</span>
                    </div>
                    <span id="slide1TagBadge" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                        Slide Clave (Hook)
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Badge / Píldora -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Badge / Pastilla Temática</label>
                            <button type="button" onclick="assistField('slide_badge_1', 'badge')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                <span>✨ IA</span>
                            </button>
                        </div>
                        <input type="text" name="slides[0][badge]" id="slide_badge_1" value="GUÍA CLÍNICA" placeholder="Ej: GUÍA ESPECIALIZADA" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                    </div>

                    <!-- Título / Hook Portada -->
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label id="headline1Label" class="text-xs font-semibold text-slate-700">Título / Gancho Principal</label>
                            <button type="button" onclick="assistField('slide_headline_1', 'hook')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                <span>✨ Generar / Mejorar con IA</span>
                            </button>
                        </div>
                        <input type="text" name="slides[0][headline]" id="slide_headline_1" oninput="updatePromptPreview()" value="¿Por qué los implantes dentales modernos duran más de 20 años?" placeholder="Gancho atractivo que despierte curiosidad..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Subtítulo o Bajada de Portada -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label id="body1Label" class="text-xs font-semibold text-slate-700">Subtítulo / Bajada Explicativa</label>
                        <button type="button" onclick="assistField('slide_body_1', 'subtitle')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                            <span>✨ IA</span>
                        </button>
                    </div>
                    <textarea name="slides[0][body_text]" id="slide_body_1" rows="2" placeholder="Explicación concisa del valor de la pieza..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-normal text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">La tecnología de titanio y la planificación digital 3D han transformado el éxito clínico a largo plazo.</textarea>
                    <input type="hidden" name="slides[0][slide_number]" value="1">
                    <input type="hidden" name="slides[0][slide_type]" id="slide1TypeInput" value="cover">
                </div>
            </div>

            <!-- CONTENEDOR DE LÁMINAS SECUNDARIAS (SLIDES 2..N) - SOLO VISIBLE EN MODO CARRUSEL -->
            <div id="slidesContainer" class="space-y-4">
                <!-- Slide 2 -->
                <div class="slide-card bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4 relative" data-slide-index="2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="slide-num-badge w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">02</span>
                            <span class="font-bold text-sm text-slate-900">Lámina de Desarrollo</span>
                        </div>
                        <button type="button" onclick="removeSlide(this)" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                            🗑️ Eliminar
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Badge / Etapa</label>
                                <button type="button" onclick="assistField('slide_badge_2', 'badge')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                    <span>✨ IA</span>
                                </button>
                            </div>
                            <input type="text" name="slides[1][badge]" id="slide_badge_2" value="01. BIOCOMPATIBILIDAD" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Título de la Lámina</label>
                                <button type="button" onclick="assistField('slide_headline_2', 'headline')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                    <span>✨ Generar / Mejorar</span>
                                </button>
                            </div>
                            <input type="text" name="slides[1][headline]" id="slide_headline_2" value="Osteointegración a Nivel Celular" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Cuerpo del Contenido</label>
                            <button type="button" onclick="assistField('slide_body_2', 'slide_body')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                <span>✨ Mejorar Redacción</span>
                            </button>
                        </div>
                        <textarea name="slides[1][body_text]" id="slide_body_2" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-normal text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">El titanio grado médico permite que el hueso maxilar se fusione naturalmente con el implante, creando una raíz artificial sólida.</textarea>
                        <input type="hidden" name="slides[1][slide_number]" value="2">
                        <input type="hidden" name="slides[1][slide_type]" value="content">
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="slide-card bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4 relative" data-slide-index="3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="slide-num-badge w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">03</span>
                            <span class="font-bold text-sm text-slate-900">Lámina de Desarrollo</span>
                        </div>
                        <button type="button" onclick="removeSlide(this)" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                            🗑️ Eliminar
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Badge / Etapa</label>
                                <button type="button" onclick="assistField('slide_badge_3', 'badge')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                    <span>✨ IA</span>
                                </button>
                            </div>
                            <input type="text" name="slides[2][badge]" id="slide_badge_3" value="02. PRECISIÓN 3D" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Título de la Lámina</label>
                                <button type="button" onclick="assistField('slide_headline_3', 'headline')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                    <span>✨ Generar / Mejorar</span>
                                </button>
                            </div>
                            <input type="text" name="slides[2][headline]" id="slide_headline_3" value="Cirugía Guiada por Computadora" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Cuerpo del Contenido</label>
                            <button type="button" onclick="assistField('slide_body_3', 'slide_body')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                <span>✨ Mejorar Redacción</span>
                            </button>
                        </div>
                        <textarea name="slides[2][body_text]" id="slide_body_3" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-normal text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">Planificamos la posición exacta con tomografía computarizada y férulas impresas en 3D, minimizando molestias y tiempos de cicatrización.</textarea>
                        <input type="hidden" name="slides[2][slide_number]" value="3">
                        <input type="hidden" name="slides[2][slide_type]" value="content">
                    </div>
                </div>
            </div>

            <!-- CIERRE & CTA (LÁMINA FINAL) - OCULTO EN POST ÚNICO -->
            <div id="ctaCardWrapper" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center">CTA</span>
                        <span class="font-bold text-sm text-slate-900">Lámina de Cierre / Llamado a la Acción (CTA)</span>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                        Conversión & Engagement
                    </span>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-slate-700">Llamado a la Acción (CTA)</label>
                        <button type="button" onclick="assistField('ctaText', 'cta')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                            <span>✨ Generar / Mejorar CTA</span>
                        </button>
                    </div>
                    <input type="text" name="cta_text" id="ctaText" value="Guardá esta guía para tu próxima consulta o dejanos tu duda en comentarios." placeholder="Ej: Escribinos al enlace del perfil para evaluar tu caso con tomografía 3D." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                </div>
            </div>

            <!-- 3. COPY PARA INSTAGRAM (CAPTION & HASHTAGS) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 font-heading">3. Copy de Publicación (Instagram Caption & Hashtags)</h2>
                    <button type="button" onclick="assistField('captionText', 'caption')" class="text-xs font-semibold px-3 py-1 bg-sky-50 text-sky-700 border border-sky-200 rounded-lg hover:bg-sky-100 flex items-center gap-1 transition-colors cursor-pointer">
                        <span>✨ Redactar / Pulir Copy Completo con IA</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Texto del Post / Pie de Foto</label>
                        <textarea name="caption_copy" id="captionText" rows="4" placeholder="Redacta el texto que acompañará la publicación en el feed..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-normal text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">¿Sabías que un implante dental bien planificado puede acompañarte toda la vida? ✨ En TecnoGen combinamos diagnóstico digital 3D y materiales de vanguardia para que recuperes tu sonrisa con total seguridad y confort.

Deslizá para conocer las 3 claves de la durabilidad en odontología moderna. 👉

¿Tenés dudas sobre tu tratamiento? Dejanos tu comentario o envianos un mensaje directo.</textarea>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Hashtags Recomendados</label>
                            <button type="button" onclick="assistField('hashtagsText', 'hashtags')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 cursor-pointer">
                                ✨ IA
                            </button>
                        </div>
                        <textarea name="hashtags" id="hashtagsText" rows="4" placeholder="#TecnoGen #Implantes..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono text-slate-700 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">#TecnoGen #ImplantesDentales #OdontologiaDigital #CirugiaGuiada #SonrisaSaludable #EsteticaDental</textarea>
                    </div>
                </div>
            </div>

            <!-- COMPILADOR DE PROMPT PREVIEW -->
            <div class="bg-slate-900 text-slate-200 p-6 rounded-2xl shadow-md space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Compilador de Prompt Visual (OpenAI Sunburst)</span>
                    </div>
                    <button type="button" onclick="updatePromptPreview()" class="text-xs text-sky-400 hover:text-sky-300 font-semibold underline cursor-pointer">
                        Actualizar Vista Previa
                    </button>
                </div>
                <div id="promptPreviewBox" class="p-3 bg-slate-950 rounded-xl text-xs font-mono text-slate-300 leading-relaxed border border-slate-800 max-h-36 overflow-y-auto">
                    Cargando compilador de prompt...
                </div>
            </div>

            <!-- BOTONES DE ACCIÓN FINAL -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="render_now" value="1" checked class="w-5 h-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">🎨 Renderizar Imagen con IA inmediatamente</span>
                        <span class="text-[11px] text-slate-500">Genera la pieza con OpenAI y la exporta a tu Google Drive al guardar.</span>
                    </div>
                </label>

                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <span>🚀 Guardar y Renderizar Pieza</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- MODAL: PEGAR GUION EXTERNO (AUTOPARSER) -->
<div id="pasteModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full border border-slate-200 shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-sky-50 text-sky-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 font-heading">Importar y Parsear Guion Externo</h3>
                    <p class="text-xs text-slate-500">Pega aquí el texto desde ChatGPT, Google Sheets, WhatsApp o Notion.</p>
                </div>
            </div>
            <button type="button" onclick="closePasteModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer">
                ✕
            </button>
        </div>

        <textarea id="rawScriptText" rows="10" placeholder="Pega aquí el contenido en bruto... 
Ejemplo:
Título: 3 Mitos sobre los Implantes Dentales
Lámina 1: Mito: Es doloroso. Realidad: Se usa anestesia local guiada y no duele nada.
Lámina 2: Mito: Se nota artificial. Realidad: El zirconio imita a la perfección el diente natural.
CTA: Guardá este post si estás pensando en colocarte implantes." class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden"></textarea>

        <div class="flex items-center justify-between pt-2">
            <span class="text-xs text-slate-500">La IA distribuirá el gancho, las láminas, los badges y el copy automáticamente.</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closePasteModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Cancelar</button>
                <button type="button" id="btnRunParser" onclick="runScriptParser()" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs flex items-center gap-2 cursor-pointer">
                    <span id="parserBtnText">⚡ Autoparsear e Importar</span>
                    <span id="parserSpinner" class="hidden animate-spin">⏳</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REDACTAR CON IA DESDE CERO -->
<div id="fullAiModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 font-heading">Redactar Contenido con IA</h3>
                    <p class="text-xs text-slate-500">Ingresa la idea o tratamiento y la IA creará toda la estructura.</p>
                </div>
            </div>
            <button type="button" onclick="closeFullAiModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer">
                ✕
            </button>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tema o Tratamiento</label>
            <input type="text" id="fullAiTopic" placeholder="Ej: Carga inmediata: dientes fijos en 24 horas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cantidad de Láminas</label>
                <select id="fullAiSlidesCount" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 cursor-pointer">
                    <option value="4">4 Láminas (Rápido)</option>
                    <option value="5" selected>5 Láminas (Recomendado)</option>
                    <option value="6">6 Láminas</option>
                    <option value="7">7 Láminas (Profundo)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Enfoque / Ángulo</label>
                <select id="fullAiAngle" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 cursor-pointer">
                    <option value="educativo">Educativo / Desmitificador</option>
                    <option value="caso_clinico">Caso Clínico / Resultados</option>
                    <option value="comparativa">Comparativa / Antes y Después</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="closeFullAiModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Cancelar</button>
            <button type="button" id="btnRunFullAi" onclick="runFullAiGenerator()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs flex items-center gap-2 cursor-pointer">
                <span id="fullAiBtnText">✨ Redactar con IA</span>
                <span id="fullAiSpinner" class="hidden animate-spin">⏳</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL: SPINNER DE GENERACIÓN Y GUARDADO -->
<div id="submitLoadingModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-200">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-sky-500/25 animate-pulse">
            🎨
        </div>
        <div>
            <h3 id="submitLoadingTitle" class="text-lg font-bold text-slate-900 font-heading">Renderizando y Guardando...</h3>
            <p id="submitLoadingMsg" class="text-xs text-slate-500 mt-1">Conectando con OpenAI (gpt-image-2.5-sunburst) para generar la pieza clínica y sincronizarla en el estudio. Por favor aguarda unos segundos.</p>
        </div>
        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
            <div class="bg-gradient-to-r from-sky-500 to-indigo-600 h-full w-2/3 animate-pulse rounded-full"></div>
        </div>
        <div class="text-[11px] font-mono text-slate-400">
            ⏳ Procesando solicitud con TecnoGen AI Core
        </div>
    </div>
</div>

<script>
    let slideCount = 3;

    function handleFormSubmit(event) {
        const modal = document.getElementById('submitLoadingModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        return true;
    }


    // Maneja el cambio de formato entre Carrusel, Post Único y Reel
    function handleFormatChange() {
        const type = document.getElementById('contentType').value;
        const slidesContainer = document.getElementById('slidesContainer');
        const btnAddSlide = document.getElementById('btnAddSlide');
        const ctaCardWrapper = document.getElementById('ctaCardWrapper');
        const paginatorWrapper = document.getElementById('paginatorWrapper');

        const slide1NumBadge = document.getElementById('slide1NumBadge');
        const slide1TitleLabel = document.getElementById('slide1TitleLabel');
        const slide1TagBadge = document.getElementById('slide1TagBadge');
        const slide1TypeInput = document.getElementById('slide1TypeInput');
        const headline1Label = document.getElementById('headline1Label');
        const body1Label = document.getElementById('body1Label');

        if (type === 'single_post') {
            // MODO POST ÚNICO
            slidesContainer.classList.add('hidden');
            btnAddSlide.classList.add('hidden');
            ctaCardWrapper.classList.add('hidden');
            paginatorWrapper.classList.add('hidden');

            slide1NumBadge.innerText = '🖼️';
            slide1TitleLabel.innerText = 'Diseño del Post Único (1080x1080)';
            slide1TagBadge.innerText = 'Post Cuadrado Feed';
            slide1TypeInput.value = 'single_post';
            headline1Label.innerText = 'Titular Principal del Post';
            body1Label.innerText = 'Texto / Cuerpo Gráfico del Post';
        } else if (type === 'reel_script') {
            // MODO REEL / GUION DE VIDEO
            slidesContainer.classList.add('hidden');
            btnAddSlide.classList.add('hidden');
            ctaCardWrapper.classList.remove('hidden');
            paginatorWrapper.classList.add('hidden');

            slide1NumBadge.innerText = '📱';
            slide1TitleLabel.innerText = 'Estructura del Reel / Video (1080x1920)';
            slide1TagBadge.innerText = 'Guion Vertical';
            slide1TypeInput.value = 'reel_script';
            headline1Label.innerText = 'Gancho en Cámara (Primeros 3 segundos)';
            body1Label.innerText = 'Desarrollo / Explicación Clínica (3 a 45 segundos)';
        } else {
            // MODO CARRUSEL
            slidesContainer.classList.remove('hidden');
            btnAddSlide.classList.remove('hidden');
            ctaCardWrapper.classList.remove('hidden');
            paginatorWrapper.classList.remove('hidden');

            slide1NumBadge.innerText = '01';
            slide1TitleLabel.innerText = 'Lámina de Portada / Gancho Principal';
            slide1TagBadge.innerText = 'Slide Clave (Hook)';
            slide1TypeInput.value = 'cover';
            headline1Label.innerText = 'Título / Gancho Principal';
            body1Label.innerText = 'Subtítulo / Bajada Explicativa';
        }

        updatePromptPreview();
    }

    function openPasteModal() {
        document.getElementById('pasteModal').classList.remove('hidden');
    }

    function closePasteModal() {
        document.getElementById('pasteModal').classList.add('hidden');
    }

    function openFullAiModal() {
        document.getElementById('fullAiModal').classList.remove('hidden');
    }

    function closeFullAiModal() {
        document.getElementById('fullAiModal').classList.add('hidden');
    }

    // Micro-asistente IA para un campo específico
    async function assistField(fieldId, fieldType) {
        const inputEl = document.getElementById(fieldId);
        if (!inputEl) return;

        const originalVal = inputEl.value;
        const topic = document.getElementById('slide_headline_1')?.value || 'Odontología digital e implantes';
        const charName = document.getElementById('characterSelect')?.selectedOptions[0]?.dataset?.name || 'Especialista';
        const visualType = document.getElementById('visualTypeSelect')?.value || '';

        // Feedback visual en el input
        inputEl.classList.add('animate-pulse', 'bg-sky-50', 'border-sky-400');

        try {
            const res = await fetch('/app/ai/assist-field', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    field_type: fieldType,
                    current_text: originalVal,
                    topic: topic,
                    character: charName,
                    visual_type: visualType
                })
            });

            const data = await res.json();
            if (data.success && data.result) {
                inputEl.value = data.result;
                updatePromptPreview();
            } else {
                alert('No se pudo optimizar el campo: ' + (data.error || 'Error desconocido'));
            }
        } catch (e) {
            alert('Error de conexión con el motor de IA');
        } finally {
            inputEl.classList.remove('animate-pulse', 'bg-sky-50', 'border-sky-400');
        }
    }

    // Autoparser de guion externo
    async function runScriptParser() {
        const rawText = document.getElementById('rawScriptText').value.trim();
        if (!rawText) {
            alert('Por favor pega un texto para parsear.');
            return;
        }

        const btn = document.getElementById('btnRunParser');
        const btnText = document.getElementById('parserBtnText');
        const spinner = document.getElementById('parserSpinner');

        btn.disabled = true;
        btnText.innerText = 'Parseando guion...';
        spinner.classList.remove('hidden');

        try {
            const res = await fetch('/app/ai/parse-script', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ raw_text: rawText })
            });

            const data = await res.json();
            if (data.success && data.data) {
                applyStructuredDataToForm(data.data);
                closePasteModal();
            } else {
                alert('Error al parsear el guion: ' + (data.error || 'Respuesta inválida'));
            }
        } catch (e) {
            alert('Error de conexión al parsear.');
        } finally {
            btn.disabled = false;
            btnText.innerText = '⚡ Autoparsear e Importar';
            spinner.classList.add('hidden');
        }
    }

    // Generador de carrusel completo desde una idea
    async function runFullAiGenerator() {
        const topic = document.getElementById('fullAiTopic').value.trim();
        if (!topic) {
            alert('Por favor ingresa un tema.');
            return;
        }

        const slidesCount = parseInt(document.getElementById('fullAiSlidesCount').value) || 5;
        const charName = document.getElementById('characterSelect')?.selectedOptions[0]?.dataset?.name || 'Especialista';

        const btn = document.getElementById('btnRunFullAi');
        const btnText = document.getElementById('fullAiBtnText');
        const spinner = document.getElementById('fullAiSpinner');

        btn.disabled = true;
        btnText.innerText = 'Generando carrusel...';
        spinner.classList.remove('hidden');

        try {
            const res = await fetch('/app/ai/generate-full-script', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    topic: topic,
                    character: charName,
                    slides_count: slidesCount
                })
            });

            const data = await res.json();
            if (data.success && data.data) {
                applyStructuredDataToForm(data.data);
                closeFullAiModal();
            } else {
                alert('Error al generar: ' + (data.error || 'Respuesta inválida'));
            }
        } catch (e) {
            alert('Error de conexión al generar con IA.');
        } finally {
            btn.disabled = false;
            btnText.innerText = '✨ Redactar con IA';
            spinner.classList.add('hidden');
        }
    }

    // Aplica los datos estructurados en todos los campos del formulario
    function applyStructuredDataToForm(data) {
        if (data.badge) document.getElementById('slide_badge_1').value = data.badge;
        if (data.hook) document.getElementById('slide_headline_1').value = data.hook;
        if (data.subtitle) document.getElementById('slide_body_1').value = data.subtitle;
        if (data.cta) document.getElementById('ctaText').value = data.cta;
        if (data.caption) document.getElementById('captionText').value = data.caption;
        if (data.hashtags) document.getElementById('hashtagsText').value = data.hashtags;

        const type = document.getElementById('contentType').value;

        if (type === 'carousel' && Array.isArray(data.slides) && data.slides.length > 0) {
            const container = document.getElementById('slidesContainer');
            container.innerHTML = '';

            data.slides.forEach((s, idx) => {
                const sNum = idx + 2;
                const card = document.createElement('div');
                card.className = 'slide-card bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4 relative';
                card.dataset.slideIndex = sNum;
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="slide-num-badge w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">${String(sNum).padStart(2, '0')}</span>
                            <span class="font-bold text-sm text-slate-900">Lámina de Desarrollo</span>
                        </div>
                        <button type="button" onclick="removeSlide(this)" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                            🗑️ Eliminar
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Badge / Etapa</label>
                                <button type="button" onclick="assistField('slide_badge_${sNum}', 'badge')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                    <span>✨ IA</span>
                                </button>
                            </div>
                            <input type="text" name="slides[${sNum-1}][badge]" id="slide_badge_${sNum}" value="${s.badge || ''}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-700">Título de la Lámina</label>
                                <button type="button" onclick="assistField('slide_headline_${sNum}', 'headline')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                    <span>✨ Generar / Mejorar</span>
                                </button>
                            </div>
                            <input type="text" name="slides[${sNum-1}][headline]" id="slide_headline_${sNum}" value="${s.headline || ''}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Cuerpo del Contenido</label>
                            <button type="button" onclick="assistField('slide_body_${sNum}', 'slide_body')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                                <span>✨ Mejorar Redacción</span>
                            </button>
                        </div>
                        <textarea name="slides[${sNum-1}][body_text]" id="slide_body_${sNum}" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-normal text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">${s.body_text || ''}</textarea>
                        <input type="hidden" name="slides[${sNum-1}][slide_number]" value="${sNum}">
                        <input type="hidden" name="slides[${sNum-1}][slide_type]" value="content">
                    </div>
                `;
                container.appendChild(card);
            });
            slideCount = data.slides.length + 1;
        }

        updatePromptPreview();
    }

    function addSlide() {
        slideCount++;
        const container = document.getElementById('slidesContainer');
        const card = document.createElement('div');
        card.className = 'slide-card bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4 relative';
        card.dataset.slideIndex = slideCount;
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="slide-num-badge w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">${String(slideCount).padStart(2, '0')}</span>
                    <span class="font-bold text-sm text-slate-900">Lámina de Desarrollo</span>
                </div>
                <button type="button" onclick="removeSlide(this)" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                    🗑️ Eliminar
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-slate-700">Badge / Etapa</label>
                        <button type="button" onclick="assistField('slide_badge_${slideCount}', 'badge')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                            <span>✨ IA</span>
                        </button>
                    </div>
                    <input type="text" name="slides[${slideCount-1}][badge]" id="slide_badge_${slideCount}" placeholder="0${slideCount-1}. PASO" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-slate-700">Título de la Lámina</label>
                        <button type="button" onclick="assistField('slide_headline_${slideCount}', 'headline')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                            <span>✨ Generar / Mejorar</span>
                        </button>
                    </div>
                    <input type="text" name="slides[${slideCount-1}][headline]" id="slide_headline_${slideCount}" placeholder="Concepto o beneficio clave..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-semibold text-slate-700">Cuerpo del Contenido</label>
                    <button type="button" onclick="assistField('slide_body_${slideCount}', 'slide_body')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer">
                        <span>✨ Mejorar Redacción</span>
                    </button>
                </div>
                <textarea name="slides[${slideCount-1}][body_text]" id="slide_body_${slideCount}" rows="2" placeholder="Detalle conciso..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-normal text-slate-800 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-hidden"></textarea>
                <input type="hidden" name="slides[${slideCount-1}][slide_number]" value="${slideCount}">
                <input type="hidden" name="slides[${slideCount-1}][slide_type]" value="content">
            </div>
        `;
        container.appendChild(card);
        updatePromptPreview();
    }

    function removeSlide(btn) {
        const card = btn.closest('.slide-card');
        if (card) {
            card.remove();
            renumberSlides();
            updatePromptPreview();
        }
    }

    function renumberSlides() {
        const cards = document.querySelectorAll('.slide-card');
        cards.forEach((card, idx) => {
            const sNum = idx + 2;
            card.dataset.slideIndex = sNum;
            const badge = card.querySelector('.slide-num-badge');
            if (badge) badge.innerText = String(sNum).padStart(2, '0');
        });
        slideCount = cards.length + 1;
    }

    function updatePromptPreview() {
        const type = document.getElementById('contentType')?.value || 'carousel';
        const hook = document.getElementById('slide_headline_1')?.value || '';
        const visual = document.getElementById('visualTypeSelect')?.value || '';
        const paginator = document.getElementById('paginatorSelect')?.value || '';
        const charName = document.getElementById('characterSelect')?.selectedOptions[0]?.dataset?.name || 'Profesional TecnoGen';
        const primaryColor = '{{ $brand->primary_color ?? "#16345F" }}';
        const accentColor = '{{ $brand->accent_color ?? "#7DD3FC" }}';

        const promptText = `PROMPT COMPILADO [gpt-image-2.5-sunburst]:
Formato: ${type === 'single_post' ? 'Post Único (1080x1080)' : (type === 'reel_script' ? 'Reel Vertical (1080x1920)' : 'Carrusel Instagram (1080x1350)')}
Estructura Visual: ${visual} ${type === 'carousel' ? '| Paginador: ' + paginator : ''}
Identidad de Marca: TecnoGen Dental Studio (Primario: ${primaryColor}, Acento: ${accentColor}, Fondo: Limpio Quirúrgico)
Personaje: ${charName} en atuendo médico contemporáneo
Titular: "${hook}"
Acabados fotorrealistas médicos, tipografía limpia en español y composición editorial premium.`;

        document.getElementById('promptPreviewBox').innerText = promptText;
    }

    // Inicializar formato y prompt preview
    document.addEventListener('DOMContentLoaded', () => {
        handleFormatChange();
        updatePromptPreview();
    });
</script>
@endsection
