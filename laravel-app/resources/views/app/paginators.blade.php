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
            <span class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs">
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

    <!-- Paginators Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($paginators as $p)
            @php
                $isActive = ($p['sheetLabel'] === $currentPaginator) || ($p['id'] === 'random' && $currentPaginator === 'Aleatorio');
            @endphp
            <div class="saas-card p-4 flex flex-col justify-between relative transition-all {{ $isActive ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/30' : 'bg-white' }}">
                @if($isActive)
                    <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold shadow-sm">
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

                    <!-- Mini Preview Visual Blanca Limpia -->
                    <div class="h-20 bg-slate-50 border border-slate-200 rounded-lg p-3 flex items-center justify-center relative overflow-hidden">
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
                                <span class="w-2 h-2 rounded-full bg-slate-300"></span>
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
@endsection
