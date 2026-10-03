@extends('layouts.app')

@section('title', 'Explorador de Base de Datos MySQL - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-heading font-bold text-slate-900">
                Base de Datos MySQL
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Visualizador en tiempo real de tablas y registros en la base <code class="px-2 py-0.5 rounded bg-slate-200 text-slate-800 font-mono text-xs font-semibold">tecnogen_studio</code>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Conectado a MySQL
            </span>
        </div>
    </div>

    <!-- Main Container -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Tables List Sidebar -->
        <div class="lg:col-span-1 space-y-2">
            <div class="saas-card p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-1">
                    Tablas del Sistema ({{ count($tables) }})
                </div>

                <div class="space-y-1 max-h-[600px] overflow-y-auto">
                    @foreach($tables as $t)
                        <a href="/app/database?table={{ $t->name }}"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors {{ $selectedTable === $t->name ? 'bg-sky-50 text-sky-700 border border-sky-200 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <span class="truncate">{{ $t->name }}</span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 font-mono text-[10px]">
                                {{ $t->count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Table Data Content -->
        <div class="lg:col-span-3">
            <div class="saas-card p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 font-heading">
                            Tabla: <span class="font-mono text-sky-600">{{ $selectedTable }}</span>
                        </h2>
                        <span class="text-xs text-slate-500">
                            {{ $totalRows }} registros encontrados
                        </span>
                    </div>

                    <a href="/app/database?table={{ $selectedTable }}" class="text-xs text-sky-600 hover:text-sky-700 font-semibold flex items-center gap-1">
                        Refrescar
                    </a>
                </div>

                @if(count($rows) === 0)
                    <div class="py-12 text-center text-slate-400 text-sm">
                        Esta tabla no contiene registros actualmente.
                    </div>
                @else
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 text-center text-slate-400">#</th>
                                    @foreach($columns as $col)
                                        <th class="py-3 px-3 uppercase tracking-wider font-mono">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($rows as $idx => $row)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                        @foreach($columns as $col)
                                            @php
                                                $val = $row->$col ?? null;
                                                $display = is_null($val) ? 'NULL' : (is_string($val) ? $val : json_encode($val));
                                            @endphp
                                            <td class="py-2.5 px-3 max-w-[220px] truncate font-mono {{ is_null($val) ? 'text-slate-300 italic' : 'text-slate-700' }}" title="{{ $display }}">
                                                {{ Str::limit($display, 40) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
