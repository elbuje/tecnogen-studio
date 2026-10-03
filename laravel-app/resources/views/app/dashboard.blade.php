@extends('layouts.app')

@section('title', 'Dashboard - TecnoGen Studio')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="saas-card p-6 bg-gradient-to-r from-sky-600 to-indigo-700 text-white border-0 shadow-md">
        <div class="max-w-2xl space-y-2">
            <span class="text-xs font-bold tracking-wider uppercase px-2.5 py-1 rounded-full bg-white/20 text-white">
                Espacio de Trabajo
            </span>
            <h1 class="text-3xl font-heading font-black tracking-tight">
                Bienvenido a TecnoGen Studio
            </h1>
            <p class="text-sky-100 text-sm">
                Plataforma autónoma de carruseles publicitarios y producción con Inteligencia Artificial. Conectada directamente a MySQL y Google Sheets.
            </p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="saas-card p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                Créditos Disponibles
            </div>
            <div class="text-2xl font-heading font-bold text-slate-900 mt-2">
                {{ session('user')->credits_balance ?? 1000 }}
            </div>
            <div class="text-xs text-emerald-600 font-semibold mt-1">
                Plan Activo
            </div>
        </div>

        <div class="saas-card p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                Estilo de Paginador
            </div>
            <div class="text-sm font-heading font-bold text-slate-900 mt-2 truncate">
                {{ $brandRules['default_paginator'] ?? '01. Numeración Simple' }}
            </div>
            <a href="/app/paginators" class="text-xs text-sky-600 font-semibold mt-1 inline-block hover:underline">
                Cambiar en Galería →
            </a>
        </div>

        <div class="saas-card p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                Base de Datos
            </div>
            <div class="text-sm font-mono font-bold text-slate-900 mt-2">
                MySQL (tecnogen_studio)
            </div>
            <a href="/app/database" class="text-xs text-sky-600 font-semibold mt-1 inline-block hover:underline">
                Explorar Tablas →
            </a>
        </div>
    </div>

    <!-- Links Directos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="/app/paginators" class="saas-card p-6 block hover:border-sky-300 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold mb-3 group-hover:scale-105 transition-transform">
                📑
            </div>
            <h3 class="text-base font-bold text-slate-900 font-heading">
                Galería de 50 Paginadores
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Visualiza los 50 estilos de paginación disponibles para carruseles de Instagram y sincronizados con Google Sheets.
            </p>
        </a>

        <a href="/app/database" class="saas-card p-6 block hover:border-sky-300 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mb-3 group-hover:scale-105 transition-transform">
                🗄️
            </div>
            <h3 class="text-base font-bold text-slate-900 font-heading">
                Visor MySQL en Tiempo Real
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Inspecciona directamente los registros de usuarios, marcas, contenidos, diapositivas y balances en la base de datos de producción.
            </p>
        </a>
    </div>
</div>
@endsection
