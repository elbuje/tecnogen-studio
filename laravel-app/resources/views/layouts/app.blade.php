<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TecnoGen Studio - Plataforma SaaS')</title>
    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#F0F9FF',
                            100: '#E0F2FE',
                            500: '#0284C7',
                            600: '#0369A1',
                            700: '#075985',
                            900: '#0C4A6E',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F8FAFC;
            color: #0F172A;
        }
        .saas-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            border-radius: 1rem;
        }
        .saas-card:hover {
            border-color: #CBD5E1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans antialiased bg-slate-50">
    <!-- Navbar SaaS Blanca Ultra Limpia -->
    <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 px-6 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-6">
            <a href="/app" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-600 flex items-center justify-center text-white font-heading font-black text-lg shadow-sm">
                    T
                </div>
                <span class="font-heading font-bold text-lg tracking-tight text-slate-900">
                    TecnoGen <span class="text-sky-600">Studio</span>
                </span>
            </a>

            <div class="hidden md:flex items-center gap-1 pl-4 border-l border-slate-200">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    SaaS Cloud v2.0
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            @if(session('user'))
                <!-- Badges de Usuario -->
                <div class="hidden sm:flex items-center gap-3">
                    <span class="text-xs font-medium px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ session('user')->credits_balance ?? 1000 }} Créditos
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-800">
                        {{ session('user')->full_name ?? session('user')->email }}
                    </span>
                </div>

                <a href="/logout" class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-3 py-1.5 rounded-lg hover:bg-rose-50 border border-transparent hover:border-rose-100 transition-colors">
                    Cerrar Sesión
                </a>
            @else
                <a href="/login" class="text-xs font-bold px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white transition-colors shadow-sm">
                    Iniciar Sesión
                </a>
            @endif
        </div>
    </header>

    <div class="flex-1 flex">
        <!-- Sidebar SaaS Blanca -->
        <aside class="w-64 bg-white border-r border-slate-200 p-4 hidden md:flex flex-col justify-between shrink-0">
            <div class="space-y-6">
                <div>
                    <div class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        Plataforma
                    </div>
                    <nav class="space-y-1">
                        <a href="/app" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="/app/paginators" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            <span>Paginadores (50)</span>
                        </a>

                        <a href="/app/database" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                            <span>Visor MySQL</span>
                        </a>
                    </nav>
                </div>

                <div>
                    <div class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        Configuración
                    </div>
                    <nav class="space-y-1">
                        <a href="/app/brands" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            <span>Mis Marcas</span>
                        </a>

                        <a href="/app/integrations" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span>Google Sheets / Drive</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Footer de Servidor -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <div class="text-[11px] font-semibold text-slate-700 flex items-center justify-between">
                    <span>Motor SaaS</span>
                    <span class="text-sky-600 font-mono">PHP 8.2 + MySQL</span>
                </div>
                <div class="text-[10px] text-slate-500 mt-1">
                    Cero librerías externas ni Python
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
