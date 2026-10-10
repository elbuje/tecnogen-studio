@extends('layouts.app')

@section('title', 'Tipos Visuales & Estructuras - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-heading font-bold text-slate-900">
                        Tipos Visuales & Estructuras
                        <span class="ml-2 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">
                            {{ count($visualTypes) }} Formatos
                        </span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Estructuras narrativas, comparativas y esquemáticas para las láminas de carruseles de tu marca.
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
    <div class="saas-card p-4 bg-purple-50/50 border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                ✓
            </span>
            <div>
                <div class="text-xs font-bold text-slate-900">
                    Tipo Visual Predeterminado: <span class="text-purple-700 font-mono">{{ $currentVisualType }}</span>
                </div>
                <div class="text-[11px] text-slate-500">
                    Define la estructura y composición predominante para el contenido de tus carruseles.
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Tipos Visuales (4 a 5 por fila) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($visualTypes as $v)
            @php
                $isActive = ($v['sheetLabel'] === $currentVisualType) || ($v['id'] === 'conceptual' && $currentVisualType === 'Conceptual');
                $numStr = sprintf('%02d', $v['number']);
                $relPath = "assets/visual_types/visual_type_{$numStr}.png";
                $hasImage = file_exists(public_path($relPath)) || file_exists(base_path("../public/{$relPath}"));
            @endphp
            <div class="saas-card p-4 flex flex-col justify-between relative transition-all {{ $isActive ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/30' : 'bg-white' }}">
                @if($isActive)
                    <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs font-bold shadow-xs z-10">
                        ✓
                    </div>
                @endif

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $v['category'] }}
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-400">
                            #{{ $v['number'] }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-slate-900 font-heading">
                        {{ $v['name'] }}
                    </h3>

                    @if($hasImage)
                        <div class="h-32 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 relative group">
                            <img src="/{{ $relPath }}" alt="{{ $v['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="h-28 bg-slate-50 border border-slate-200 rounded-lg p-3 flex flex-col items-center justify-center relative overflow-hidden">
                            <div class="text-xs font-mono font-bold text-purple-700 bg-white px-2.5 py-1 rounded border border-purple-100 shadow-xs text-center">
                                {{ $v['sheetLabel'] }}
                            </div>
                        </div>
                    @endif

                    <p class="text-xs text-slate-500 line-clamp-3">
                        {{ $v['description'] }}
                    </p>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400 font-mono">
                        {{ Str::limit($v['sheetLabel'], 16) }}
                    </span>

                    <form action="/app/visual-types/select" method="POST">
                        @csrf
                        <input type="hidden" name="visual_type" value="{{ $v['sheetLabel'] }}">
                        <button type="submit"
                                class="text-xs font-bold px-3 py-1 rounded-lg transition-colors {{ $isActive ? 'bg-purple-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $isActive ? 'Activo' : 'Elegir' }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
