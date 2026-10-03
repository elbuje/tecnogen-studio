<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - TecnoGen Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans text-slate-900 antialiased">
    <div class="max-w-md w-full space-y-6">
        <!-- Logo -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-sky-600 items-center justify-center text-white font-heading font-black text-2xl shadow-sm">
                T
            </div>
            <h1 class="text-2xl font-heading font-bold text-slate-900">
                TecnoGen <span class="text-sky-600">Studio</span>
            </h1>
            <p class="text-xs text-slate-500">
                Plataforma SaaS de Producción de Contenidos IA
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm space-y-5">
            @if(session('error'))
                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <form action="/login" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="tu@empresa.com"
                           class="w-full px-3.5 py-2.5 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-900 focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-900 focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm transition-colors shadow-sm">
                    Acceder a la Plataforma
                </button>
            </form>

            <div class="pt-4 border-t border-slate-100 space-y-2">
                <div class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider text-center">
                    Cuentas Configuradas
                </div>
                <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg space-y-1">
                    <div><b>Cliente:</b> mmujica@tecnobrain.com.ar / marcelo</div>
                    <div><b>Admin:</b> mfmujic@gmail.com / marcelo</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
