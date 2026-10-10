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
                    <div class="relative">
                        <input type="password" id="password_input" name="password" required placeholder="••••••••"
                               class="w-full pl-3.5 pr-10 py-2.5 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-900 focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
                        <button type="button" onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                                title="Mostrar / Ocultar contraseña">
                            <!-- Eye Open Icon -->
                            <svg id="eye_open" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Slash / Closed Icon -->
                            <svg id="eye_closed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
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

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('password_input');
            const eyeOpen = document.getElementById('eye_open');
            const eyeClosed = document.getElementById('eye_closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
