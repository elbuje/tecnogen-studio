<?php
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '180');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$reqMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($reqMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$baseDir = dirname(__DIR__);
$mainConfigFile = $baseDir . '/data/config.json';
$carouselConfigFile = $baseDir . '/data/config_carruseles.json';

$outputDir = __DIR__ . '/output';
$carouselsDir = $outputDir . '/carousels';
$uploadsDir = $baseDir . '/data/uploads_carousels';
$publicUploadsDir = $outputDir . '/uploads_carousels';
$tempRefsDir = $baseDir . '/data/temp_refs_carousels';

foreach ([$outputDir, $carouselsDir, $baseDir . '/data', $uploadsDir, $publicUploadsDir, $tempRefsDir] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// --------------------------------------------------------------------------
// PROMPTS DE SISTEMA POR DEFECTO (EXCLUSIVOS PARA CARRUSELES SECUENCIALES)
// --------------------------------------------------------------------------
function getDefaultCarouselPrompts() {
    return [
        'prompt_director_carrusel' =>
            "Actuás como un Director Creativo Publicitario, Guionista de Secuencias y Estratega experto en Carruseles de Alto Impacto para Redes Sociales.\n"
            . "Tu objetivo es analizar la base del guion o idea del usuario junto con los 3 apartados de recursos si están presentes:\n"
            . "1) Definición de Marca / Paleta de Colores,\n"
            . "2) Logo de la marca,\n"
            . "3) Fotos de Personajes, Productos y/o Fondos (escenarios fotográficos),\n"
            . "y respetar las preferencias de Estilo y Ubicación de los Deslizadores seleccionadas por el usuario.\n\n"
            . "REGLAS CREATIVAS Y DE ESTRUCTURA DEL CARRUSEL:\n"
            . "1. FORMATO DINÁMICO ATRACTIVO (LIBERTAD CREATIVA): Según lo que plantee el guion del usuario, elegí libremente la dinámica de carrusel que mejor cuente la historia y más retenga la atención de principio a fin (por ejemplo: Mito vs. Realidad, Verdadero o Falso, Pregunta y Respuesta, Paso a Paso, Errores Comunes vs. Solución, Storytelling secuencial, Checklist, etc.), sin atarte a moldes fijos.\n"
            . "2. VARIANTES DE DESLIZADORES / CONECTORES ('swipe_indicator') Y UBICACIÓN ('swipe_position'):\n"
            . "   - Respetá el estilo de deslizador elegido por el usuario en la barra inicial:\n"
            . "     a) Gancho / Intriga (Texto corto + →): 'Mirá por qué →', 'Descubrí la causa →', 'Pero eso no es todo →', 'Acá está el secreto →', '¿Qué pasó después? →', 'La solución real →'.\n"
            . "     b) Interactivo / Gamificado: '¿Mito o Realidad? Deslizá →', '¿Verdadero o Falso? →', '¿Qué harías vos? →', 'Adiviná la respuesta →'.\n"
            . "     c) Secuencial / Progreso: 'Paso 1 →', 'Siguiente tip →', '01 / 05 →', '[ ● ● ○ ○ ] →'.\n"
            . "     d) Solo Flecha / Ícono minimalista (sin texto): Indicá un botón circular o pastilla con flecha '→' o doble chevron '»' en los colores de marca.\n"
            . "     e) Puente Visual al borde derecho: Indicá un elemento gráfico o flecha que nace en la lámina y toca el borde derecho invitando a arrastrar.\n"
            . "     f) En la ÚLTIMA lámina: colocá siempre un cierre o llamado a la acción claro ('🔖 Guardá este post', '📲 Compartilo', '💬 Contanos en comentarios', '📅 Reservá tu turno por link en bio', etc.).\n"
            . "   - Definí también para cada lámina su ubicación ('swipe_position'): 'bottom_right' (abajo a la derecha), 'middle_right' (centro-derecha al borde), 'bottom_center' (abajo centrado), 'top_right' (arriba a la derecha), 'below_text' (integrado debajo del texto) o 'none'.\n"
            . "3. ADN VISUAL, PALETA DE MARCA Y CONTINUIDAD DE LA HISTORIA:\n"
            . "   - Todas las láminas del carrusel deben pertenecer a una misma serie visual coherente y aplicar estrictamente la Marca / Paleta de Colores subida por el usuario en fondos, acentos, pastillas, titulares y deslizadores.\n"
            . "   - Si hay fotos de personas/protagonistas adjuntas (ej. xxxx.jpg), definí un vestuario y aspecto constante para todo el carrusel, pero variando la postura, gesto, acción o plano fotográfico en cada lámina para que la secuencia sea dinámica.\n"
            . "4. IDENTIFICACIÓN POR NOMBRE DE ARCHIVO: Siempre que haya fotos adjuntas de personajes, productos o fondos (ej.: xxxx.jpg, yyyy.jpg, zzzz.jpg), mencioná explícitamente el nombre exacto de cada archivo dentro del 'scene_prompt' de cada lámina donde participe.",

        'prompt_disenador_carrusel' =>
            "Actuás como un Diseñador Gráfico Editorial Senior y Director de Arte Fotográfico especializado en Carruseles Secuenciales para redes sociales (Formato Vertical 4:5 - 1080x1350 px).\n"
            . "DIRECTRICES DE DISEÑO SECUENCIAL:\n"
            . "1. LÁMINA INDIVIDUAL UNIFICADA: Creá únicamente la lámina actual solicitada como una sola pieza gráfica vertical 4:5 a pantalla completa. Prohibido dividir la imagen en múltiples cuadros, grillas, tiras o collages.\n"
            . "2. COHERENCIA GRÁFICA Y PALETA DE MARCA ENTRE SLIDES DEL CARRUSEL:\n"
            . "   - Cuando se adjunte una imagen de referencia de continuidad del carrusel (como la Portada / Slide 1 ya generada o la lámina anterior), mantené estrictamente el mismo sistema gráfico editorial: misma familia y jerarquía tipográfica, mismo estilo de cajas/pastillas/contenedores, misma paleta de colores de marca subida por el usuario, misma ambientación lumínica y mismo vestuario del personaje protagonista.\n"
            . "3. FIDELIDAD DE IDENTIDAD Y USO DE ARCHIVOS ADJUNTOS:\n"
            . "   - Respetá la correspondencia exacta de cada archivo original adjunto según su nombre real (ej. xxxx.jpg, yyyy.jpg, zzzz.jpg).\n"
            . "   - Mantené estrictamente los rasgos faciales reales, fisonomía, expresión e identidad de cada persona o producto de su foto original respectiva.\n"
            . "   - Si se adjunta logotipo, integralo de forma limpia, nítida y coherente con el resto del carrusel.\n"
            . "4. PROGRESIÓN NARRATIVA Y DESLIZADOR / CONECTOR UBICADO CON PRECISIÓN:\n"
            . "   - Representá la nueva postura, gesto, plano o acción descrita para esta escena del guion.\n"
            . "   - Renderizá ÚNICAMENTE los textos exactos en español indicados para ESTA lámina actual (Titular principal, Texto secundario/bajada si lo tiene, y el Deslizador/Conector de esta lámina). Prohibido repetir textos de láminas anteriores.\n"
            . "   - Colocá el Deslizador / Conector exactamente en la UBICACIÓN indicada para esta lámina (abajo a la derecha, centro-derecha al borde, abajo centrado, arriba a la derecha o debajo del bloque de texto) con diseño atractivo, limpio y coherente con la paleta de marca.\n"
            . "5. DISEÑO EDITORIAL LIMPIO Y CERO CORTES:\n"
            . "   - Asegurate de que absolutamente todos los textos, titulares, subtítulos, deslizadores, íconos y logotipos tengan alto contraste, perfecta legibilidad y queden 100% contenidos dentro del encuadre con buen margen de aire respecto a los bordes del lienzo."
    ];
}

// --------------------------------------------------------------------------
// CONFIGURACIÓN DEL MÓDULO DE CARRUSELES (INDEPENDIENTE)
// --------------------------------------------------------------------------
function getCarouselConfig($carouselConfigFile, $mainConfigFile) {
    $defaultPrompts = getDefaultCarouselPrompts();
    $currentPromptsVersion = 2;

    // Si existe config.json del estudio principal, tomamos su API key como valor inicial por comodidad
    $sharedApiKey = '';
    $sharedLogo = null;
    $sharedBrandAsset = null;
    $sharedBrandText = '';
    if (file_exists($mainConfigFile)) {
        $mainJson = @json_decode(@file_get_contents($mainConfigFile), true);
        if (is_array($mainJson)) {
            $sharedApiKey = $mainJson['openai_api_key'] ?? '';
            $sharedLogo = $mainJson['default_logo'] ?? null;
            $sharedBrandAsset = $mainJson['default_brand_asset'] ?? null;
            $sharedBrandText = $mainJson['default_brand_text'] ?? '';
        }
    }

    $defaults = [
        'openai_api_key' => $sharedApiKey,
        'selected_model' => 'gpt-image-2.5-sunburst-2026-09-08',
        'text_model' => 'gpt-4o',
        'stamp_logo_overlay' => false,
        'default_logo' => $sharedLogo,
        'default_brand_asset' => $sharedBrandAsset,
        'default_brand_text' => $sharedBrandText,
        'prompts_schema_version' => $currentPromptsVersion,
        'prompt_director_carrusel' => $defaultPrompts['prompt_director_carrusel'],
        'prompt_disenador_carrusel' => $defaultPrompts['prompt_disenador_carrusel']
    ];

    if (file_exists($carouselConfigFile)) {
        $content = file_get_contents($carouselConfigFile);
        $json = json_decode($content, true);
        if (is_array($json)) {
            $merged = array_merge($defaults, $json);
            $needsSave = false;

            if (empty($merged['openai_api_key']) && !empty($sharedApiKey)) {
                $merged['openai_api_key'] = $sharedApiKey;
            }
            if ($merged['selected_model'] === 'gpt-image-2.5-sunburst') {
                $merged['selected_model'] = 'gpt-image-2.5-sunburst-2026-09-08';
            }
            $savedVersion = (int)($json['prompts_schema_version'] ?? 1);
            if ($savedVersion < $currentPromptsVersion) {
                $merged['prompt_director_carrusel'] = $defaultPrompts['prompt_director_carrusel'];
                $merged['prompt_disenador_carrusel'] = $defaultPrompts['prompt_disenador_carrusel'];
                $merged['prompts_schema_version'] = $currentPromptsVersion;
                $needsSave = true;
            }
            if (empty(trim((string)($merged['prompt_director_carrusel'] ?? '')))) {
                $merged['prompt_director_carrusel'] = $defaultPrompts['prompt_director_carrusel'];
            }
            if (empty(trim((string)($merged['prompt_disenador_carrusel'] ?? '')))) {
                $merged['prompt_disenador_carrusel'] = $defaultPrompts['prompt_disenador_carrusel'];
            }
            if ($needsSave) {
                saveCarouselConfig($carouselConfigFile, $merged);
            }
            return $merged;
        }
    }
    return $defaults;
}

function saveCarouselConfig($carouselConfigFile, $data) {
    file_put_contents($carouselConfigFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// --------------------------------------------------------------------------
// PROCESAMIENTO DE IMÁGENES
// --------------------------------------------------------------------------
function normalizeCarouselUploadedImage($sourcePath, $targetPngPath, $maxDim = 1536) {
    if (!file_exists($sourcePath)) return false;
    $info = @getimagesize($sourcePath);
    if (!$info) return false;

    $mime = $info['mime'] ?? '';
    $src = null;
    if ($mime === 'image/jpeg') {
        $src = @imagecreatefromjpeg($sourcePath);
        if ($src && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                switch ((int)$exif['Orientation']) {
                    case 3:
                        $src = imagerotate($src, 180, 0);
                        break;
                    case 6:
                        $src = imagerotate($src, -90, 0);
                        break;
                    case 8:
                        $src = imagerotate($src, 90, 0);
                        break;
                }
            }
        }
    } elseif ($mime === 'image/png') {
        $src = @imagecreatefrompng($sourcePath);
    } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $src = @imagecreatefromwebp($sourcePath);
    }

    if (!$src) return false;

    $w = imagesx($src);
    $h = imagesy($src);

    $scale = 1.0;
    if ($w > $maxDim || $h > $maxDim) {
        $scale = min($maxDim / $w, $maxDim / $h);
    }
    $newW = max(1, (int)round($w * $scale));
    $newH = max(1, (int)round($h * $scale));

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($src);

    $ok = imagepng($dst, $targetPngPath, 6);
    imagedestroy($dst);
    return $ok;
}

function createCarouselVisionDataUrl($imagePath, $maxDim = 512) {
    if (!file_exists($imagePath)) return null;
    $info = @getimagesize($imagePath);
    if (!$info) return null;

    $mime = $info['mime'] ?? '';
    $src = null;
    if ($mime === 'image/png') $src = @imagecreatefrompng($imagePath);
    elseif ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($imagePath);
    elseif ($mime === 'image/webp') $src = @imagecreatefromwebp($imagePath);
    if (!$src) return null;

    $w = imagesx($src);
    $h = imagesy($src);
    $scale = min(1.0, $maxDim / max($w, $h));
    $nw = max(1, (int)round($w * $scale));
    $nh = max(1, (int)round($h * $scale));

    $dst = imagecreatetruecolor($nw, $nh);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($src);

    ob_start();
    imagejpeg($dst, null, 80);
    $jpegBytes = ob_get_clean();
    imagedestroy($dst);

    if (!$jpegBytes) return null;
    return 'data:image/jpeg;base64,' . base64_encode($jpegBytes);
}

/**
 * Adapta cada lámina del carrusel al formato vertical 4:5 (1080x1350 px)
 * sin recortar textos ni deslizadores en los bordes.
 */
function finalizeCarouselSlideOutput($rawImagePath, $finalPath, $logoPath = null) {
    if (!file_exists($rawImagePath)) return false;
    $info = @getimagesize($rawImagePath);
    if (!$info) return false;

    $mime = $info['mime'] ?? '';
    $src = null;
    if ($mime === 'image/png') $src = @imagecreatefrompng($rawImagePath);
    elseif ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($rawImagePath);
    elseif ($mime === 'image/webp') $src = @imagecreatefromwebp($rawImagePath);
    if (!$src) return false;

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $targetW = 1080;
    $targetH = 1350; // 4:5 estándar de carrusel editorial

    $dst = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($dst, true);
    imagesavealpha($dst, true);

    $targetRatio = $targetW / $targetH;
    $srcRatio = $srcW / $srcH;

    if (abs($srcRatio - $targetRatio) < 0.02) {
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
    } else {
        $idealCropH = (int)round($srcW / $targetRatio);
        $gentleCropH = (int)round($srcH - (($srcH - $idealCropH) * 0.35));
        $cropW = $srcW;
        $cropH = min($srcH, max($idealCropH, $gentleCropH));
        $cropX = 0;
        $cropY = (int)round(($srcH - $cropH) / 2);
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
    }
    imagedestroy($src);

    if ($logoPath && file_exists($logoPath)) {
        $lInfo = @getimagesize($logoPath);
        if ($lInfo) {
            $lMime = $lInfo['mime'] ?? '';
            $lImg = null;
            if ($lMime === 'image/png') $lImg = @imagecreatefrompng($logoPath);
            elseif ($lMime === 'image/jpeg') $lImg = @imagecreatefromjpeg($logoPath);
            if ($lImg) {
                $lw = imagesx($lImg);
                $lh = imagesy($lImg);
                $maxLogoW = 145;
                $maxLogoH = 105;
                $lScale = min($maxLogoW / $lw, $maxLogoH / $lh);
                $destLW = (int)round($lw * $lScale);
                $destLH = (int)round($lh * $lScale);
                $destLX = $targetW - $destLW - 42;
                $destLY = 42;
                imagealphablending($dst, true);
                imagecopyresampled($dst, $lImg, $destLX, $destLY, 0, 0, $destLW, $destLH, $lw, $lh);
                imagedestroy($lImg);
            }
        }
    }

    $ok = imagepng($dst, $finalPath, 7);
    imagedestroy($dst);
    return $ok;
}

$action = $_GET['action'] ?? '';

// --------------------------------------------------------------------------
// 1. OBTENER Y GUARDAR CONFIGURACIÓN DE CARRUSELES
// --------------------------------------------------------------------------
if ($action === 'get_config') {
    $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
    $defaultPrompts = getDefaultCarouselPrompts();
    $key = $cfg['openai_api_key'] ?? '';
    $maskedKey = (strlen($key) > 12) ? substr($key, 0, 7) . '...' . substr($key, -4) : $key;
    echo json_encode([
        'status' => 'ok',
        'openai_api_key' => $key,
        'masked_key' => $maskedKey,
        'has_api_key' => !empty($key),
        'selected_model' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst-2026-09-08',
        'text_model' => $cfg['text_model'] ?? 'gpt-4o',
        'stamp_logo_overlay' => !empty($cfg['stamp_logo_overlay']),
        'default_logo' => $cfg['default_logo'] ?? null,
        'default_brand_asset' => $cfg['default_brand_asset'] ?? null,
        'default_brand_text' => $cfg['default_brand_text'] ?? '',
        'prompt_director_carrusel' => $cfg['prompt_director_carrusel'] ?? $defaultPrompts['prompt_director_carrusel'],
        'prompt_disenador_carrusel' => $cfg['prompt_disenador_carrusel'] ?? $defaultPrompts['prompt_disenador_carrusel'],
        'default_prompts' => $defaultPrompts
    ]);
    exit;
}

if ($action === 'save_config' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit;
    }
    $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
    if (isset($input['openai_api_key'])) $cfg['openai_api_key'] = trim($input['openai_api_key']);
    if (isset($input['selected_model'])) $cfg['selected_model'] = trim($input['selected_model']);
    if (isset($input['text_model'])) $cfg['text_model'] = trim($input['text_model']);
    if (isset($input['stamp_logo_overlay'])) $cfg['stamp_logo_overlay'] = (bool)$input['stamp_logo_overlay'];
    if (array_key_exists('default_logo', $input)) $cfg['default_logo'] = $input['default_logo'];
    if (array_key_exists('default_brand_asset', $input)) $cfg['default_brand_asset'] = $input['default_brand_asset'];
    if (array_key_exists('default_brand_text', $input)) $cfg['default_brand_text'] = trim((string)$input['default_brand_text']);
    if (isset($input['prompt_director_carrusel'])) $cfg['prompt_director_carrusel'] = trim($input['prompt_director_carrusel']);
    if (isset($input['prompt_disenador_carrusel'])) $cfg['prompt_disenador_carrusel'] = trim($input['prompt_disenador_carrusel']);
    saveCarouselConfig($carouselConfigFile, $cfg);
    echo json_encode(['status' => 'ok', 'message' => 'Configuración de Carruseles guardada correctamente']);
    exit;
}

// --------------------------------------------------------------------------
// 2. CONSULTAR MODELOS DE OPENAI EN VIVO
// --------------------------------------------------------------------------
if ($action === 'get_models') {
    $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key de OpenAI no configurada']);
        exit;
    }

    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'User-Agent: TecnoGenStudioCarousels/1.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al conectar con OpenAI (HTTP ' . $httpCode . '): ' . ($curlErr ?: $response)
        ]);
        exit;
    }

    $json = json_decode($response, true);
    $modelsList = [];
    if (isset($json['data']) && is_array($json['data'])) {
        foreach ($json['data'] as $m) {
            if (isset($m['id'])) $modelsList[] = $m['id'];
        }
    }
    sort($modelsList);

    $imageKeywords = ['image', 'dall', 'sunburst', 'flare'];
    $imageModels = array_values(array_filter($modelsList, function($id) use ($imageKeywords) {
        $lower = strtolower($id);
        foreach ($imageKeywords as $kw) {
            if (strpos($lower, $kw) !== false) return true;
        }
        return false;
    }));

    foreach (['gpt-image-2.5-sunburst-2026-09-08', 'gpt-image-1'] as $preset) {
        if (!in_array($preset, $imageModels, true)) {
            array_unshift($imageModels, $preset);
        }
    }

    echo json_encode([
        'status' => 'ok',
        'image_models' => $imageModels,
        'all_models' => $modelsList,
        'current_selected' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst-2026-09-08'
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 3. SUBIR RECURSOS PARA CARRUSELES (MARCA / LOGO / FOTOS)
// --------------------------------------------------------------------------
if ($action === 'upload_asset' && $reqMethod === 'POST') {
    $rawInput = file_get_contents('php://input');
    $jsonBody = ($rawInput && substr(trim($rawInput), 0, 1) === '{') ? json_decode($rawInput, true) : null;

    $assetType = preg_replace('/[^a-z_]/', '', $_POST['type'] ?? ($jsonBody['type'] ?? 'subject'));
    $saveDefault = !empty($_POST['save_default']) || !empty($jsonBody['save_default']);

    $tmpSourceFile = null;
    $cleanupTemp = false;
    $origName = 'imagen.png';

    $dataUrl = $_POST['data_url'] ?? ($jsonBody['data_url'] ?? '');
    if (!empty($dataUrl) && preg_match('/^data:image\/([a-zA-Z0-9+.-]+);base64,(.+)$/', $dataUrl, $matches)) {
        $rawBytes = base64_decode($matches[2]);
        if ($rawBytes && strlen($rawBytes) > 0) {
            $origName = basename($_POST['filename'] ?? ($jsonBody['filename'] ?? 'imagen.png'));
            $tmpSourceFile = $tempRefsDir . '/upload_' . uniqid() . '.tmp';
            file_put_contents($tmpSourceFile, $rawBytes);
            $cleanupTemp = true;
        }
    }

    if (!$tmpSourceFile) {
        if (empty($_FILES['file'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'El archivo supera el límite POST del servidor o no se pudo leer.'
            ]);
            exit;
        }
        $errCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($errCode !== UPLOAD_ERR_OK) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al recibir el archivo (código ' . $errCode . ')'
            ]);
            exit;
        }
        $tmpSourceFile = $_FILES['file']['tmp_name'];
        $origName = basename($_FILES['file']['name']);
    }

    $uniqueId = $assetType . '_' . time() . '_' . substr(md5($origName . mt_rand()), 0, 6);
    $destPng = $uploadsDir . '/' . $uniqueId . '.png';

    $convertedOk = normalizeCarouselUploadedImage($tmpSourceFile, $destPng, 1600);

    if (!$convertedOk && file_exists('/usr/bin/sips')) {
        $cmd = '/usr/bin/sips -s format png -Z 1600 ' . escapeshellarg($tmpSourceFile) . ' --out ' . escapeshellarg($destPng) . ' >/dev/null 2>&1';
        @exec($cmd);
        if (file_exists($destPng) && filesize($destPng) > 0) {
            $convertedOk = true;
        }
    }

    if (!$convertedOk) {
        $info = @getimagesize($tmpSourceFile);
        if (!$info) {
            if ($cleanupTemp) @unlink($tmpSourceFile);
            echo json_encode(['status' => 'error', 'message' => 'El archivo no es una imagen compatible']);
            exit;
        }
        @copy($tmpSourceFile, $destPng);
    }

    if ($cleanupTemp && $tmpSourceFile && file_exists($tmpSourceFile)) {
        @unlink($tmpSourceFile);
    }

    $publicPath = $publicUploadsDir . '/' . $uniqueId . '.png';
    @copy($destPng, $publicPath);

    $assetObj = [
        'id' => $uniqueId,
        'name' => $origName,
        'type' => $assetType,
        'local_path' => 'data/uploads_carousels/' . $uniqueId . '.png',
        'preview_url' => 'output/uploads_carousels/' . $uniqueId . '.png'
    ];

    if ($assetType === 'logo' && $saveDefault) {
        $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
        $cfg['default_logo'] = $assetObj;
        saveCarouselConfig($carouselConfigFile, $cfg);
    } elseif ($assetType === 'brand' && $saveDefault) {
        $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
        $cfg['default_brand_asset'] = $assetObj;
        saveCarouselConfig($carouselConfigFile, $cfg);
    }

    echo json_encode([
        'status' => 'ok',
        'asset' => $assetObj
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 4. PASO 1: DIRECTOR CREATIVO DE CARRUSELES (PLANIFICA COPIES, ADN VISUAL
//    Y SECUENCIA DE LAS N SLIDES DEL GUION)
// --------------------------------------------------------------------------
if ($action === 'step1_plan_carousel' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    $textModel = $input['text_model'] ?? ($cfg['text_model'] ?? 'gpt-4o');

    if (!$apiKey) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Falta configurar tu OpenAI API Key. Hacé clic en "⚙️ Configuración" abajo a la izquierda.'
        ]);
        exit;
    }

    $carouselId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['carousel_id'] ?? ''));
    if (!$carouselId) {
        $carouselId = 'carousel_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 5);
    }
    $carouselFolder = $carouselsDir . '/' . $carouselId;
    if (!is_dir($carouselFolder)) {
        @mkdir($carouselFolder, 0775, true);
    }

    $userPrompt = trim((string)($input['prompt'] ?? ''));
    $networks = $input['networks'] ?? ['ig', 'fb', 'tiktok', 'linkedin'];
    if (empty($networks) || !is_array($networks)) {
        $networks = ['ig', 'fb', 'tiktok', 'linkedin'];
    }
    $slideCountPref = trim((string)($input['slide_count'] ?? 'auto')); // 'auto' o número '3'..'10'
    $swipeStylePref = trim((string)($input['swipe_style_pref'] ?? 'auto'));
    $swipePositionPref = trim((string)($input['swipe_position_pref'] ?? 'auto'));
    $brandAsset = array_key_exists('brand_asset', $input) ? $input['brand_asset'] : ($cfg['default_brand_asset'] ?? null);
    $brandText = array_key_exists('brand_text', $input) ? trim((string)$input['brand_text']) : trim((string)($cfg['default_brand_text'] ?? ''));
    $logoAsset = array_key_exists('logo_asset', $input) ? $input['logo_asset'] : ($cfg['default_logo'] ?? null);
    $subjectAssets = $input['subject_assets'] ?? [];
    $previousState = $input['previous_state'] ?? null;

    if ($userPrompt === '') {
        echo json_encode(['status' => 'error', 'message' => 'Pegá el guion o escribí la idea base para el carrusel.']);
        exit;
    }

    $networkNamesMap = [
        'ig' => 'Instagram',
        'fb' => 'Facebook',
        'tiktok' => 'TikTok',
        'linkedin' => 'LinkedIn'
    ];
    $selectedNetworkNames = [];
    foreach ($networks as $netKey) {
        if (isset($networkNamesMap[$netKey])) {
            $selectedNetworkNames[] = $networkNamesMap[$netKey];
        }
    }

    $hasBrandAsset = is_array($brandAsset) && !empty($brandAsset['local_path']);
    $hasLogo = is_array($logoAsset) && !empty($logoAsset['local_path']);
    $hasSubject = is_array($subjectAssets) && count($subjectAssets) > 0;

    $subjectFileNames = [];
    if ($hasSubject) {
        foreach ($subjectAssets as $idx => $sa) {
            $subjectFileNames[] = $sa['name'] ?? ('foto_' . ($idx + 1) . '.png');
        }
    }

    $stateFile = $carouselFolder . '/carousel_state.json';
    $existingState = file_exists($stateFile) ? (json_decode(file_get_contents($stateFile), true) ?: []) : [];

    $defaultPrompts = getDefaultCarouselPrompts();
    $directorPrompt = trim((string)($cfg['prompt_director_carrusel'] ?? $defaultPrompts['prompt_director_carrusel']));

    $brandSummaryLine = 'NO ESPECIFICADA';
    if ($brandText !== '' && $hasBrandAsset) {
        $brandSummaryLine = "SÍ (Texto: \"{$brandText}\" + Imagen de Paleta/Marca: " . ($brandAsset['name'] ?? 'paleta.png') . ")";
    } elseif ($brandText !== '') {
        $brandSummaryLine = "SÍ (Definición: \"{$brandText}\")";
    } elseif ($hasBrandAsset) {
        $brandSummaryLine = "SÍ (Imagen de Paleta/Marca: " . ($brandAsset['name'] ?? 'paleta.png') . ")";
    }

    $slideCountRule = ($slideCountPref !== 'auto' && is_numeric($slideCountPref) && (int)$slideCountPref >= 2 && (int)$slideCountPref <= 12)
        ? "CANTIDAD EXACTA DE LÁMINAS SOLICITADA: " . (int)$slideCountPref . " láminas (slides). Desarrollá exactamente " . (int)$slideCountPref . " objetos dentro del array 'slides'."
        : "CANTIDAD DE LÁMINAS (AUTOMÁTICA SEGÚN GUION): Si el guion del usuario ya especifica cuántas láminas/slides tiene, respetá exactamente esa cantidad. Si no lo especifica, definí vos la cantidad ideal de láminas (entre 4 y 7 slides) para narrar toda la secuencia con ritmo ágil y alto impacto.";

    $swipeStyleMap = [
        'auto' => "ESTILO DE DESLIZADOR SELECCIONADO: AUTOMÁTICO SEGÚN GUION — Variá creativamente el conector ('swipe_indicator') de cada lámina según lo que se cuenta en esa slide (ganchos de intriga, preguntas interactivas, pasos o flechas) y poné un CTA de cierre en la última lámina.",
        'intriga' => "ESTILO DE DESLIZADOR SELECCIONADO: GANCHO / INTRIGA (Texto corto + →) — En cada lámina (salvo la última que lleva CTA de cierre) usá conectores narrativos intrigantes que despierten curiosidad por deslizar (ej. 'Mirá por qué →', 'Descubrí la causa →', 'Pero eso no es todo →', 'Acá está el secreto →', '¿Qué pasó después? →', 'La solución real →').",
        'interactivo' => "ESTILO DE DESLIZADOR SELECCIONADO: INTERACTIVO / GAMIFICADO — En las láminas que plantean una incógnita, mito o reto usá conectores participativos (ej. '¿Mito o Realidad? Deslizá →', '¿Verdadero o Falso? →', '¿Qué harías vos? →', 'Comprobá tu respuesta →'), y un CTA en la última.",
        'numerado' => "ESTILO DE DESLIZADOR SELECCIONADO: NUMERADO / PROGRESO EDITORIAL — Usá indicadores secuenciales o de paginación con flecha (ej. '01 / N →', '02 / N →', 'Paso 1 →', 'Siguiente paso →' o '[ ● ● ○ ○ ] →'), y cierre/CTA en la última lámina.",
        'icono_minimal' => "ESTILO DE DESLIZADOR SELECCIONADO: SOLO FLECHA / ÍCONO MINIMALISTA (SIN TEXTO) — En 'swipe_indicator' colocá '[SOLO ÍCONO: Botón circular con flecha →]' para que el diseñador dibuje únicamente un botón/ícono gráfico limpio con flecha sin palabras adicionales (salvo en la última lámina si requiere cierre).",
        'puente_visual' => "ESTILO DE DESLIZADOR SELECCIONADO: PUENTE VISUAL AL BORDE DERECHO — En 'swipe_indicator' colocá '[PUENTE VISUAL: Flecha continua hacia el borde derecho]' (o combinalo con 2 palabras cortas) e indicá en 'scene_prompt' un elemento gráfico o flecha que asoma y continúa hacia el margen derecho conectando visualmente con la siguiente lámina.",
        'none' => "ESTILO DE DESLIZADOR SELECCIONADO: SIN DESLIZADOR — El usuario eligió NO incluir deslizadores. Dejá 'swipe_indicator' vacío ('') y 'swipe_position' en 'none'."
    ];
    $swipeStyleRule = $swipeStyleMap[$swipeStylePref] ?? $swipeStyleMap['auto'];

    $swipePosMap = [
        'auto' => "UBICACIÓN DEL DESLIZADOR: AUTOMÁTICA SEGÚN ESCENA — Elegí en cada lámina la mejor ubicación ('bottom_right', 'middle_right', 'bottom_center', 'top_right' o 'below_text') según dónde quede mejor equilibrio visual sin tapar al protagonista.",
        'bottom_right' => "UBICACIÓN DEL DESLIZADOR: ABAJO A LA DERECHA ('bottom_right') — Asigná 'swipe_position': 'bottom_right' en las láminas e integrá el deslizador en el rincón inferior derecho con margen seguro.",
        'middle_right' => "UBICACIÓN DEL DESLIZADOR: CENTRO-DERECHA AL BORDE ('middle_right') — Asigná 'swipe_position': 'middle_right' en las láminas para ubicar el deslizador a mitad de altura sobre el margen derecho (estilo pestaña/flecha lateral).",
        'bottom_center' => "UBICACIÓN DEL DESLIZADOR: ABAJO CENTRADO ('bottom_center') — Asigná 'swipe_position': 'bottom_center' en las láminas para ubicar el deslizador centrado en el pie editorial inferior.",
        'top_right' => "UBICACIÓN DEL DESLIZADOR: ARRIBA A LA DERECHA ('top_right') — Asigná 'swipe_position': 'top_right' en las láminas para ubicar el deslizador en la esquina superior derecha.",
        'below_text' => "UBICACIÓN DEL DESLIZADOR: DEBAJO DEL TEXTO ('below_text') — Asigná 'swipe_position': 'below_text' en las láminas para ubicar el deslizador inmediatamente debajo del bloque de titular/bajada como remate de lectura."
    ];
    $swipePositionRule = ($swipeStylePref === 'none')
        ? "UBICACIÓN DEL DESLIZADOR: 'none' (Sin deslizador)."
        : ($swipePosMap[$swipePositionPref] ?? $swipePosMap['auto']);

    $systemInstructions = $directorPrompt . "\n\n"
        . "CONTEXTO Y SELECCIONES DEL USUARIO PARA ESTE CARRUSEL:\n"
        . "- Redes destino para los copies: " . implode(', ', $selectedNetworkNames) . "\n"
        . "- Formato de cada lámina: Carrusel Editorial Vertical 4:5 (1080x1350 px)\n"
        . "- {$slideCountRule}\n"
        . "- {$swipeStyleRule}\n"
        . "- {$swipePositionRule}\n"
        . "- 1) Marca / Paleta de Colores: {$brandSummaryLine}\n"
        . "- 2) ¿Subió Logo?: " . ($hasLogo ? "SÍ (Archivo: " . ($logoAsset['name'] ?? 'Logo') . ")" : "NO") . "\n"
        . "- 3) ¿Subió Fotos de Personajes / Productos / Fondos?: " . ($hasSubject ? "SÍ (" . count($subjectAssets) . " archivo/s: " . implode(', ', $subjectFileNames) . ")" : "NO") . "\n"
        . ($hasSubject
            ? "- REGLA DE VARIABLES DE FOTOS: En el campo 'scene_prompt' de cada lámina donde aparezca un personaje, producto o fondo subido, referite siempre a él usando su nombre exacto de archivo (" . implode(', ', $subjectFileNames) . ").\n"
            : "")
        . "\nFORMATO DE RESPUESTA OBLIGATORIO (JSON válido):\n"
        . "Devolvé ÚNICAMENTE un objeto JSON con esta estructura exacta:\n"
        . "{\n"
        . "  \"assistant_comment\": \"Breve explicación de la dinámica elegida para este carrusel, estilo/ubicación de deslizadores, cantidad de láminas y enfoque narrativo (2-3 oraciones).\",\n"
        . "  \"title_summary\": \"Título corto de 3 a 5 palabras para identificar este carrusel en el historial\",\n"
        . "  \"copies\": {\n"
        . "    \"ig\": \"Copy completo para acompañar el carrusel en Instagram (si fue seleccionada, sino cadena vacía)\",\n"
        . "    \"fb\": \"Copy completo para Facebook (si fue seleccionada, sino cadena vacía)\",\n"
        . "    \"tiktok\": \"Copy y gancho para TikTok (si fue seleccionada, sino cadena vacía)\",\n"
        . "    \"linkedin\": \"Copy profesional para LinkedIn (si fue seleccionada, sino cadena vacía)\"\n"
        . "  },\n"
        . "  \"visual_dna\": {\n"
        . "    \"carousel_format\": \"Nombre del formato dinámico elegido según el guion (ej. Mito vs. Realidad, Verdadero o Falso, Paso a Paso, Storytelling Clínico, etc.)\",\n"
        . "    \"style_and_typography\": \"Regla unificada de diseño editorial, estilo de cajas/contenedores, diseño de los deslizadores, tipografías y aplicación de la paleta cromática de la marca que se mantendrá constante en todas las láminas\",\n"
        . "    \"character_and_setting_continuity\": \"Descripción del vestuario exacto del personaje (si hay foto como " . ($hasSubject ? implode(', ', $subjectFileNames) : 'xxxx.jpg') . ") y del ambiente/escenario e iluminación común para que todas las láminas tengan continuidad perfecta\"\n"
        . "  },\n"
        . "  \"slides\": [\n"
        . "    {\n"
        . "      \"slide_number\": 1,\n"
        . "      \"slide_role\": \"Rol narrativo de la lámina (ej. Portada / Gancho, Mito #1, La Verdad, Explicación, Cierre / CTA)\",\n"
        . "      \"headline\": \"Texto titular principal en español que irá escrito dentro de esta lámina\",\n"
        . "      \"body_text\": \"Texto secundario breve o bajada que irá escrito dentro de esta lámina (puede ser vacío en la portada si no hace falta)\",\n"
        . "      \"swipe_indicator\": \"Texto o indicación visual del deslizador/conector para esta lámina respetando el Estilo elegido por el usuario\",\n"
        . "      \"swipe_position\": \"Ubicación del deslizador en esta lámina: 'bottom_right' | 'middle_right' | 'bottom_center' | 'top_right' | 'below_text' | 'none'\",\n"
        . "      \"camera_and_action\": \"Plano fotográfico y postura/acción/gesto específico en esta lámina para darle dinamismo a la secuencia\",\n"
        . "      \"scene_prompt\": \"Descripción narrativa y visual completa de esta lámina puntual (mencionando los nombres exactos de archivo como " . ($hasSubject ? implode(', ', $subjectFileNames) : 'xxxx.jpg') . "), indicando la escena, la expresión/pose del personaje y cómo se integran su titular, su bajada y su deslizador en la ubicación indicada manteniendo el ADN visual y paleta del carrusel\"\n"
        . "    }\n"
        . "  ]\n"
        . "}";

    $userContentParts = [];

    $textBlock = "GUION / IDEA BASE DEL USUARIO PARA EL CARRUSEL:\n" . $userPrompt . "\n\n"
        . "REDES SOLICITADAS: " . implode(', ', $selectedNetworkNames) . "\n"
        . "PREFERENCIA DE CANTIDAD DE SLIDES: " . ($slideCountPref === 'auto' ? 'Automático según el guion' : $slideCountPref . ' láminas') . "\n"
        . "ESTILO DE DESLIZADOR ELEGIDO POR EL USUARIO: " . $swipeStylePref . "\n"
        . "UBICACIÓN DE DESLIZADOR ELEGIDA POR EL USUARIO: " . $swipePositionPref;

    if ($brandText !== '') {
        $textBlock .= "\nDEFINICIÓN DE MARCA / PALETA DE COLORES: " . $brandText;
    }
    if ($hasSubject) {
        $textBlock .= "\nARCHIVOS DE FOTOS ADJUNTOS (Personajes / Productos / Fondos): " . implode(', ', $subjectFileNames);
    }

    if ($previousState && !empty($previousState['slides'])) {
        $textBlock .= "\n\nPLAN ANTERIOR DEL CARRUSEL (El usuario está pidiendo un ajuste o modificación sobre esta secuencia previa):\n"
            . json_encode([
                'visual_dna' => $previousState['visual_dna'] ?? [],
                'slides' => $previousState['slides'] ?? []
            ], JSON_UNESCAPED_UNICODE);
    }

    $userContentParts[] = [
        'type' => 'text',
        'text' => $textBlock
    ];

    // 1. Vista previa de Marca / Paleta
    if ($hasBrandAsset) {
        $brandFile = $baseDir . '/' . ltrim($brandAsset['local_path'], '/');
        $brandUri = createCarouselVisionDataUrl($brandFile, 512);
        if ($brandUri) {
            $userContentParts[] = [
                'type' => 'text',
                'text' => "APARTADO 1 — IMAGEN DE MARCA / PALETA DE COLORES (Archivo: " . ($brandAsset['name'] ?? 'paleta.png') . "):"
            ];
            $userContentParts[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $brandUri, 'detail' => 'low']
            ];
        }
    }

    // 2. Vista previa del Logo
    if ($hasLogo) {
        $logoFile = $baseDir . '/' . ltrim($logoAsset['local_path'], '/');
        $logoUri = createCarouselVisionDataUrl($logoFile, 512);
        if ($logoUri) {
            $userContentParts[] = [
                'type' => 'text',
                'text' => "APARTADO 2 — LOGO DE LA MARCA (Archivo: " . ($logoAsset['name'] ?? 'logo.png') . "):"
            ];
            $userContentParts[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $logoUri, 'detail' => 'low']
            ];
        }
    }

    // 3. Vista previa de cada Personaje / Producto / Fondo
    if ($hasSubject) {
        foreach ($subjectAssets as $idx => $sa) {
            $localFile = $baseDir . '/' . ltrim($sa['local_path'] ?? '', '/');
            $dataUri = createCarouselVisionDataUrl($localFile, 512);
            if ($dataUri) {
                $fName = $sa['name'] ?? ('foto_' . ($idx + 1) . '.png');
                $userContentParts[] = [
                    'type' => 'text',
                    'text' => "APARTADO 3 — FOTO ADJUNTA DE PERSONAJE / PRODUCTO / FONDO — NOMBRE DE ARCHIVO: \"{$fName}\":"
                ];
                $userContentParts[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $dataUri, 'detail' => 'low']
                ];
            }
        }
    }

    $payload = json_encode([
        'model' => $textModel,
        'response_format' => ['type' => 'json_object'],
        'messages' => [
            ['role' => 'system', 'content' => $systemInstructions],
            ['role' => 'user', 'content' => $userContentParts]
        ],
        'temperature' => 0.7
    ]);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 75);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$res) {
        $errJson = @json_decode($res, true);
        $errMsg = $errJson['error']['message'] ?? ($curlErr ?: "HTTP {$httpCode}");
        echo json_encode([
            'status' => 'error',
            'message' => 'Error en Paso 1 (Director de Carrusel): ' . $errMsg
        ]);
        exit;
    }

    $apiData = json_decode($res, true);
    $rawContent = $apiData['choices'][0]['message']['content'] ?? '';
    $plan = json_decode($rawContent, true);

    if (!$plan || empty($plan['slides']) || !is_array($plan['slides'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El modelo no devolvió una secuencia de láminas válida. Intentá nuevamente.'
        ]);
        exit;
    }

    // Normalizar numeración de slides y preservar imágenes previas si ya existían para ese índice
    $existingSlideImages = $existingState['slide_images'] ?? [];
    $validSwipePositions = ['bottom_right', 'middle_right', 'bottom_center', 'top_right', 'below_text', 'auto', 'none'];
    $normalizedSlides = [];
    foreach (array_values($plan['slides']) as $idx => $sl) {
        $sNum = $idx + 1;
        $rawSwipeInd = ($swipeStylePref === 'none') ? '' : trim((string)($sl['swipe_indicator'] ?? ''));
        $rawSwipePos = trim((string)($sl['swipe_position'] ?? ''));
        if ($swipeStylePref === 'none' || $rawSwipeInd === '') {
            $rawSwipePos = 'none';
        } elseif ($swipePositionPref !== 'auto' && in_array($swipePositionPref, $validSwipePositions, true)) {
            $rawSwipePos = $swipePositionPref;
        } elseif (!in_array($rawSwipePos, $validSwipePositions, true)) {
            $rawSwipePos = 'bottom_right';
        }

        $normalizedSlides[] = [
            'slide_number' => $sNum,
            'slide_role' => trim((string)($sl['slide_role'] ?? ("Lámina {$sNum}"))),
            'headline' => trim((string)($sl['headline'] ?? '')),
            'body_text' => trim((string)($sl['body_text'] ?? '')),
            'swipe_indicator' => $rawSwipeInd,
            'swipe_position' => $rawSwipePos,
            'camera_and_action' => trim((string)($sl['camera_and_action'] ?? '')),
            'scene_prompt' => trim((string)($sl['scene_prompt'] ?? ''))
        ];
    }

    $turns = isset($existingState['turns']) && is_array($existingState['turns']) ? $existingState['turns'] : [];
    $turns[] = [
        'user_prompt' => $userPrompt,
        'networks' => $networks,
        'slide_count_pref' => $slideCountPref,
        'swipe_style_pref' => $swipeStylePref,
        'swipe_position_pref' => $swipePositionPref,
        'brand_asset' => $brandAsset,
        'brand_text' => $brandText,
        'logo_asset' => $logoAsset,
        'subject_assets' => $subjectAssets,
        'assistant_comment' => $plan['assistant_comment'] ?? '',
        'copies' => $plan['copies'] ?? [],
        'visual_dna' => $plan['visual_dna'] ?? [],
        'slides' => $normalizedSlides,
        'created_at' => date('d/m/Y H:i:s'),
        'slide_images' => []
    ];

    $carouselRecord = [
        'carousel_id' => $carouselId,
        'title' => $existingState['title'] ?? ($plan['title_summary'] ?? mb_substr($userPrompt, 0, 45)),
        'last_idea' => $userPrompt,
        'networks' => $networks,
        'slide_count_pref' => $slideCountPref,
        'swipe_style_pref' => $swipeStylePref,
        'swipe_position_pref' => $swipePositionPref,
        'brand_asset' => $brandAsset,
        'brand_text' => $brandText,
        'logo_asset' => $logoAsset,
        'subject_assets' => $subjectAssets,
        'assistant_comment' => $plan['assistant_comment'] ?? '',
        'copies' => $plan['copies'] ?? [],
        'visual_dna' => $plan['visual_dna'] ?? [],
        'slides' => $normalizedSlides,
        'turns' => $turns,
        'slide_images' => $existingSlideImages,
        'cover_image' => $existingState['cover_image'] ?? null,
        'updated_at' => date('d/m/Y H:i'),
        'timestamp' => time()
    ];

    file_put_contents($stateFile, json_encode($carouselRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([
        'status' => 'ok',
        'carousel_id' => $carouselId,
        'text_model_used' => $textModel,
        'plan' => $carouselRecord
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 5. PASO 2: GENERAR UNA LÁMINA (SLIDE k DE N) CON ANCLAJE VISUAL DE
//    COHERENCIA (SLIDE 1 + SLIDE ANTERIOR + ARCHIVOS ORIGINALES)
// --------------------------------------------------------------------------
if ($action === 'step2_generate_slide' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $cfg = getCarouselConfig($carouselConfigFile, $mainConfigFile);
    $apiKey = $cfg['openai_api_key'] ?? '';

    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key de OpenAI no configurada.']);
        exit;
    }

    $carouselId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['carousel_id'] ?? ''));
    if (!$carouselId) {
        $carouselId = 'carousel_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 5);
    }
    $carouselFolder = $carouselsDir . '/' . $carouselId;
    if (!is_dir($carouselFolder)) {
        @mkdir($carouselFolder, 0775, true);
    }

    $stateFile = $carouselFolder . '/carousel_state.json';
    $existingState = file_exists($stateFile) ? (json_decode(file_get_contents($stateFile), true) ?: []) : [];

    $slideNumber = max(1, (int)($input['slide_number'] ?? 1));
    $totalSlides = max($slideNumber, (int)($input['total_slides'] ?? count($existingState['slides'] ?? [1])));
    $slideRole = trim((string)($input['slide_role'] ?? "Lámina {$slideNumber}"));
    $headline = trim((string)($input['headline'] ?? ''));
    $bodyText = trim((string)($input['body_text'] ?? ''));
    $swipeIndicator = trim((string)($input['swipe_indicator'] ?? ''));
    $swipePosition = trim((string)($input['swipe_position'] ?? ($existingState['swipe_position_pref'] ?? 'bottom_right')));
    $swipeStylePref = trim((string)($input['swipe_style_pref'] ?? ($existingState['swipe_style_pref'] ?? 'auto')));
    $cameraAndAction = trim((string)($input['camera_and_action'] ?? ''));
    $scenePrompt = trim((string)($input['scene_prompt'] ?? ''));

    $visualDna = $input['visual_dna'] ?? ($existingState['visual_dna'] ?? []);
    $allSlidesOverview = $input['all_slides_overview'] ?? ($existingState['slides'] ?? []);

    // Modo corrección directa sobre esta misma lámina
    $isSlideCorrection = !empty($input['is_slide_correction']);
    $editInstruction = trim((string)($input['edit_instruction'] ?? ''));
    $baseSlideUrl = trim((string)($input['base_slide_url'] ?? ''));
    if ($baseSlideUrl !== '') {
        $baseSlideUrl = preg_replace('/\?.*$/', '', $baseSlideUrl);
    }

    // Anclas de continuidad secuencial (Slide 1 portada y/o Slide anterior)
    $anchorSlide1Url = trim((string)($input['anchor_slide1_url'] ?? ''));
    if ($anchorSlide1Url !== '') {
        $anchorSlide1Url = preg_replace('/\?.*$/', '', $anchorSlide1Url);
    }
    $prevSlideUrl = trim((string)($input['prev_slide_url'] ?? ''));
    if ($prevSlideUrl !== '') {
        $prevSlideUrl = preg_replace('/\?.*$/', '', $prevSlideUrl);
    }

    $model = trim((string)($input['model'] ?? ($cfg['selected_model'] ?? 'gpt-image-2.5-sunburst-2026-09-08')));
    if ($model === 'gpt-image-2.5-sunburst') {
        $model = 'gpt-image-2.5-sunburst-2026-09-08';
    }

    $brandAsset = array_key_exists('brand_asset', $input) ? $input['brand_asset'] : ($existingState['brand_asset'] ?? ($cfg['default_brand_asset'] ?? null));
    $brandText = array_key_exists('brand_text', $input) ? trim((string)$input['brand_text']) : trim((string)($existingState['brand_text'] ?? ($cfg['default_brand_text'] ?? '')));
    $logoAsset = array_key_exists('logo_asset', $input) ? $input['logo_asset'] : ($existingState['logo_asset'] ?? null);
    $subjectAssets = $input['subject_assets'] ?? ($existingState['subject_assets'] ?? []);
    $stampLogo = isset($input['stamp_logo']) ? (bool)$input['stamp_logo'] : !empty($cfg['stamp_logo_overlay']);

    if ($scenePrompt === '' && $headline === '') {
        echo json_encode(['status' => 'error', 'message' => "La descripción de la Lámina {$slideNumber} está vacía."]);
        exit;
    }

    $defaultPrompts = getDefaultCarouselPrompts();
    $designerMasterPrompt = trim((string)($cfg['prompt_disenador_carrusel'] ?? $defaultPrompts['prompt_disenador_carrusel']));

    // ----------------------------------------------------------------------
    // RECOLECCIÓN DE ARCHIVOS DE REFERENCIA EN ORDEN ESTRATÉGICO
    // ----------------------------------------------------------------------
    $referenceFiles = [];
    $referenceNames = [];

    // Caso 1: Corrección puntual sobre una lámina ya generada (ej. retocar la Slide 3)
    $baseSlideFull = $baseSlideUrl ? (__DIR__ . '/' . ltrim($baseSlideUrl, '/')) : null;
    $useSelfAsCorrectionBase = ($isSlideCorrection && $baseSlideFull && file_exists($baseSlideFull));

    if ($useSelfAsCorrectionBase) {
        $referenceFiles[] = [
            'path' => $baseSlideFull,
            'role' => "Lámina {$slideNumber} actual a corregir (mantener composición, estética y personaje idénticos y aplicar únicamente el cambio pedido)",
            'name' => "slide_{$slideNumber}_actual.png"
        ];
        $referenceNames[] = "Base Slide {$slideNumber}";
    }

    // Caso 2: Fotos originales de Personajes / Productos / Fondos (SIEMPRE presentes para fidelidad facial 100%)
    if (is_array($subjectAssets)) {
        foreach ($subjectAssets as $sa) {
            if (!empty($sa['local_path'])) {
                $fullPath = $baseDir . '/' . ltrim($sa['local_path'], '/');
                if (file_exists($fullPath)) {
                    $fName = $sa['name'] ?? 'subject.png';
                    $referenceFiles[] = [
                        'path' => $fullPath,
                        'role' => 'Foto original de Identidad (Personaje / Producto / Fondo)',
                        'name' => $fName
                    ];
                    $referenceNames[] = 'Foto: ' . $fName;
                }
            }
        }
    }

    // Caso 3: Ancla de Continuidad del Carrusel (Slide 1 Portada ya creada) cuando generamos Slides 2..N
    if (!$useSelfAsCorrectionBase && $slideNumber > 1) {
        if ($anchorSlide1Url === '' && !empty($existingState['slide_images'][1]['url'])) {
            $anchorSlide1Url = preg_replace('/\?.*$/', '', $existingState['slide_images'][1]['url']);
        }
        $anchor1Full = $anchorSlide1Url ? (__DIR__ . '/' . ltrim($anchorSlide1Url, '/')) : null;
        if ($anchor1Full && file_exists($anchor1Full)) {
            $referenceFiles[] = [
                'path' => $anchor1Full,
                'role' => 'ANCLA DE ESTILO EDITORIAL DEL CARRUSEL (Slide 1 / Portada ya creada): Mantener exactamente esta misma estética gráfica, tipografías, diseño de contenedores/tarjetas, paleta de colores, ambiente y vestuario del personaje, pero cambiando la pose/escena y los textos a los de la Lámina ' . $slideNumber,
                'name' => 'slide_1_estilo_carrusel.png'
            ];
            $referenceNames[] = 'Ancla Estilo (Slide 1)';
        }

        // Si estamos en Slide 3 o superior y existe la lámina inmediata anterior (Slide k-1), adjuntarla también como referencia de transición
        if ($slideNumber > 2) {
            $prevIdx = $slideNumber - 1;
            if ($prevSlideUrl === '' && !empty($existingState['slide_images'][$prevIdx]['url'])) {
                $prevSlideUrl = preg_replace('/\?.*$/', '', $existingState['slide_images'][$prevIdx]['url']);
            }
            $prevFull = $prevSlideUrl ? (__DIR__ . '/' . ltrim($prevSlideUrl, '/')) : null;
            if ($prevFull && file_exists($prevFull) && $prevFull !== $anchor1Full) {
                $referenceFiles[] = [
                    'path' => $prevFull,
                    'role' => "Lámina inmediata anterior (Slide {$prevIdx}) para mantener el hilo narrativo y visual de la secuencia",
                    'name' => "slide_{$prevIdx}_anterior.png"
                ];
                $referenceNames[] = "Continuidad (Slide {$prevIdx})";
            }
        }
    }

    // Caso 4: Logotipo de la marca
    $logoFullPath = null;
    if (is_array($logoAsset) && !empty($logoAsset['local_path'])) {
        $candidateLogo = $baseDir . '/' . ltrim($logoAsset['local_path'], '/');
        if (file_exists($candidateLogo)) {
            $logoFullPath = $candidateLogo;
            $fName = $logoAsset['name'] ?? 'logo.png';
            $referenceFiles[] = [
                'path' => $candidateLogo,
                'role' => 'Logotipo oficial de la marca',
                'name' => $fName
            ];
            $referenceNames[] = 'Logo: ' . $fName;
        }
    }

    // Caso 5: Imagen de Marca / Paleta de Colores
    if (is_array($brandAsset) && !empty($brandAsset['local_path'])) {
        $candidateBrand = $baseDir . '/' . ltrim($brandAsset['local_path'], '/');
        if (file_exists($candidateBrand)) {
            $fName = $brandAsset['name'] ?? 'paleta_marca.png';
            $referenceFiles[] = [
                'path' => $candidateBrand,
                'role' => 'Referencia de Identidad de Marca / Paleta de Colores',
                'name' => $fName
            ];
            $referenceNames[] = 'Marca/Paleta: ' . $fName;
        }
    }

    // Construir el Mapa Exacto de Imágenes Adjuntas
    $referenceMapBlock = '';
    if (!empty($referenceFiles)) {
        $mapLines = [];
        foreach ($referenceFiles as $idx => $rf) {
            $num = $idx + 1;
            $mapLines[] = "- IMAGEN ADJUNTA #{$num} (image[{$idx}]): Archivo \"{$rf['name']}\" — Rol: {$rf['role']}";
        }
        $referenceMapBlock = "\n\nMAPA EXACTO DE IMÁGENES ADJUNTAS:\n" . implode("\n", $mapLines);
    }

    // Construir bloque de ADN Visual Común del Carrusel
    $dnaLines = [];
    if (!empty($visualDna['carousel_format'])) {
        $dnaLines[] = "- Dinámica del Carrusel: " . $visualDna['carousel_format'];
    }
    if (!empty($visualDna['style_and_typography'])) {
        $dnaLines[] = "- Sistema Gráfico y Tipográfico Común: " . $visualDna['style_and_typography'];
    }
    if (!empty($visualDna['character_and_setting_continuity'])) {
        $dnaLines[] = "- Continuidad de Personaje, Vestuario y Ambiente: " . $visualDna['character_and_setting_continuity'];
    }
    if ($brandText !== '') {
        $dnaLines[] = "- Paleta e Identidad de Marca: " . $brandText;
    }
    $dnaBlock = !empty($dnaLines)
        ? "\n\nADN VISUAL UNIFICADO DE ESTE CARRUSEL (OBLIGATORIO MANTENER EN TODAS LAS SLIDES):\n" . implode("\n", $dnaLines)
        : '';

    // Construir resumen de contexto narrativo (hilo conductor entre lámina anterior, actual y siguiente)
    $narrativeContextLines = [];
    if (is_array($allSlidesOverview) && count($allSlidesOverview) > 1) {
        foreach ($allSlidesOverview as $ov) {
            $n = (int)($ov['slide_number'] ?? 0);
            if ($n === $slideNumber - 1) {
                $narrativeContextLines[] = "- Lo que se contó en la lámina anterior (Slide {$n}): \"" . ($ov['headline'] ?? '') . "\" (" . ($ov['slide_role'] ?? '') . ")";
            } elseif ($n === $slideNumber + 1) {
                $narrativeContextLines[] = "- Lo que vendrá en la lámina siguiente (Slide {$n}): \"" . ($ov['headline'] ?? '') . "\" (" . ($ov['slide_role'] ?? '') . ")";
            }
        }
    }
    $narrativeContextBlock = !empty($narrativeContextLines)
        ? "\n\nHILO NARRATIVO DE LA SECUENCIA:\n" . implode("\n", $narrativeContextLines)
        : '';

    // Directriz exacta de ubicación y diseño del Deslizador / Conector en esta lámina
    $positionDescriptions = [
        'bottom_right' => 'en la ESQUINA INFERIOR DERECHA de la lámina (con margen de seguridad respecto a los bordes para que no se corte)',
        'middle_right' => 'en el CENTRO-DERECHA sobre el margen derecho a mitad de altura de la lámina (como pestaña, botón lateral o flecha hacia la derecha)',
        'bottom_center' => 'CENTRADO EN LA PARTE INFERIOR (pie editorial centrado al fondo de la lámina con buen margen inferior)',
        'top_right' => 'en la ESQUINA SUPERIOR DERECHA de la lámina (como etiqueta o paginador editorial limpio)',
        'below_text' => 'INTEGRADO JUSTO DEBAJO DEL BLOQUE DE TEXTO PRINCIPAL como remate visual de lectura',
        'auto' => 'en una zona limpia y equilibrada de la composición (preferentemente hacia el sector derecho o inferior) sin tapar el rostro ni elementos clave'
    ];
    $posDesc = $positionDescriptions[$swipePosition] ?? $positionDescriptions['bottom_right'];

    // Textos exactos que deben renderizarse en ESTA lámina
    $slideTextsLines = [];
    if ($headline !== '') {
        $slideTextsLines[] = "- TITULAR PRINCIPAL EN LA IMAGEN: \"{$headline}\"";
    }
    if ($bodyText !== '') {
        $slideTextsLines[] = "- TEXTO SECUNDARIO / BAJADA EN LA IMAGEN: \"{$bodyText}\"";
    }
    if ($swipePosition === 'none' || $swipeIndicator === '') {
        $slideTextsLines[] = "- DESLIZADOR / CONECTOR: NO incluir ningún deslizador ni flecha de avance en esta lámina.";
    } elseif (stripos($swipeIndicator, '[SOLO ÍCONO') !== false || $swipeStylePref === 'icono_minimal') {
        $slideTextsLines[] = "- DESLIZADOR VISUAL MINIMALISTA (SIN TEXTO): Dibujá únicamente un botón circular o pastilla moderna con los colores de la marca y un ícono de flecha '→' (sin escribir palabras dentro del deslizador), ubicado {$posDesc}.";
    } elseif (stripos($swipeIndicator, '[PUENTE VISUAL') !== false || $swipeStylePref === 'puente_visual') {
        $cleanSwipeText = trim(preg_replace('/\[PUENTE VISUAL[^\]]*\]/i', '', $swipeIndicator));
        $slideTextsLines[] = "- DESLIZADOR TIPO PUENTE VISUAL CONTINUO: Diseñá una flecha, pastilla o acento gráfico con la paleta de la marca ubicado {$posDesc} que se extienda hacia el borde derecho de la lámina invitando a deslizar"
            . ($cleanSwipeText !== '' ? ", con el texto \"{$cleanSwipeText}\"" : "") . ".";
    } else {
        $slideTextsLines[] = "- DESLIZADOR / CONECTOR EN LA IMAGEN: \"{$swipeIndicator}\" — UBICACIÓN OBLIGATORIA: Colocalo {$posDesc}, diseñado como una pastilla, botón o etiqueta editorial atractiva con la paleta de la marca y una flecha nítida.";
    }
    $slideTextsBlock = !empty($slideTextsLines)
        ? "\n\nTEXTOS Y DESLIZADOR A INCLUIR EN ESTA LÁMINA {$slideNumber} DE {$totalSlides} (No incluir textos de otras láminas):\n" . implode("\n", $slideTextsLines)
        : '';

    if ($useSelfAsCorrectionBase) {
        $finalPromptForSlide = $designerMasterPrompt
            . "\n\nFORMATO: LÁMINA {$slideNumber} DE {$totalSlides} DE CARRUSEL VERTICAL (4:5 - 1080x1350 px)."
            . $dnaBlock
            . $referenceMapBlock
            . "\n\nMODO CORRECCIÓN PUNTUAL SOBRE ESTA LÁMINA {$slideNumber} (IMAGEN ADJUNTA #1):\n"
            . "Tomá como base exactamente la lámina adjunta como IMAGEN ADJUNTA #1. Mantené idénticos el diseño, el personaje, el fondo, la iluminación y la estética general, y aplicá únicamente este ajuste:\n"
            . ($editInstruction !== '' ? $editInstruction : $scenePrompt)
            . $slideTextsBlock;
    } else {
        $continuityDirective = ($slideNumber > 1)
            ? "\n\nREGLA DE CONTINUIDAD DE CARRUSEL PARA ESTA LÁMINA {$slideNumber} DE {$totalSlides}:\n"
              . "- Mantené EXACTAMENTE el mismo estilo de diseño editorial, familia tipográfica, estilo de cajas/contenedores, paleta de colores, iluminación y vestimenta del personaje que ves en 'slide_1_estilo_carrusel.png'.\n"
              . "- Tomá el rostro y fisonomía real directamente de la foto original del personaje adjunta.\n"
              . "- CAMBIÁ la postura/gesto/acción o ángulo de la escena según se describe abajo para esta Lámina {$slideNumber}, y colocá ÚNICAMENTE los textos y el deslizador correspondientes a esta Lámina {$slideNumber}."
            : "\n\nESTA ES LA LÁMINA 1 DE {$totalSlides} (PORTADA DEL CARRUSEL):\n"
              . "- Establecé un diseño editorial limpio, impactante y moderno que servirá como sistema visual para todo el carrusel.";

        $finalPromptForSlide = $designerMasterPrompt
            . "\n\nPIEZA A CREAR: LÁMINA {$slideNumber} DE {$totalSlides} ({$slideRole}) — FORMATO VERTICAL 4:5 (1080x1350 px)."
            . $dnaBlock
            . $narrativeContextBlock
            . $referenceMapBlock
            . $continuityDirective
            . ($cameraAndAction !== '' ? "\n\nPLANO Y ACCIÓN EN ESTA LÁMINA {$slideNumber}: {$cameraAndAction}" : '')
            . "\n\nESCENA Y COMPOSICIÓN DE LA LÁMINA {$slideNumber}:\n" . $scenePrompt
            . $slideTextsBlock;
    }

    $rawTempOut = $tempRefsDir . '/raw_slide_' . $slideNumber . '_' . uniqid() . '.png';
    $success = false;
    $endpointUsed = '';
    $modelUsed = $model;
    $apiDiagnostics = [];

    $extractImage = function($resBody, $httpCode, $outFile) use (&$apiDiagnostics) {
        if ($httpCode !== 200 || !$resBody) {
            $errJson = @json_decode($resBody, true);
            $errMsg = $errJson['error']['message'] ?? substr((string)$resBody, 0, 220);
            $apiDiagnostics[] = "HTTP {$httpCode}: {$errMsg}";
            return false;
        }
        $apiData = json_decode($resBody, true);
        if (isset($apiData['data'][0])) {
            $item = $apiData['data'][0];
            if (!empty($item['b64_json'])) {
                file_put_contents($outFile, base64_decode($item['b64_json']));
                return file_exists($outFile) && filesize($outFile) > 0;
            } elseif (!empty($item['url'])) {
                $imgContent = @file_get_contents($item['url']);
                if ($imgContent) {
                    file_put_contents($outFile, $imgContent);
                    return file_exists($outFile) && filesize($outFile) > 0;
                }
            }
        }
        $apiDiagnostics[] = "Respuesta 200 sin datos de imagen";
        return false;
    };

    if (!empty($referenceFiles)) {
        $editAttempts = [
            ['model' => $model, 'size' => '1024x1536', 'multi' => true],
            ['model' => $model, 'size' => '1024x1024', 'multi' => false]
        ];
        if ($model !== 'gpt-image-1') {
            $editAttempts[] = ['model' => 'gpt-image-1', 'size' => '1024x1536', 'multi' => true];
            $editAttempts[] = ['model' => 'gpt-image-1', 'size' => '1024x1024', 'multi' => false];
        }

        foreach ($editAttempts as $att) {
            if ($success) break;

            $postFields = [
                'model' => $att['model'],
                'prompt' => $finalPromptForSlide,
                'n' => 1,
                'size' => $att['size']
            ];

            if ($att['multi'] && count($referenceFiles) > 1) {
                foreach ($referenceFiles as $idx => $rf) {
                    $safeUploadName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $rf['name'] ?? "ref_{$idx}.png");
                    $postFields["image[{$idx}]"] = new CURLFile($rf['path'], 'image/png', $safeUploadName);
                }
            } else {
                $primary = $referenceFiles[0];
                $safeUploadName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $primary['name'] ?? 'reference.png');
                $postFields['image'] = new CURLFile($primary['path'], 'image/png', $safeUploadName);
            }

            $ch = curl_init('https://api.openai.com/v1/images/edits');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 95);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($extractImage($res, $httpCode, $rawTempOut)) {
                $success = true;
                $endpointUsed = '/v1/images/edits (' . $att['size'] . ')';
                $modelUsed = $att['model'];
                break;
            }
        }
    }

    if (!$success) {
        $genAttempts = [
            ['model' => $model, 'size' => '1024x1536'],
            ['model' => $model, 'size' => '1024x1024'],
            ['model' => 'gpt-image-1', 'size' => '1024x1536'],
            ['model' => 'dall-e-3', 'size' => '1024x1792']
        ];

        foreach ($genAttempts as $att) {
            if ($success) break;

            $postJson = json_encode([
                'model' => $att['model'],
                'prompt' => $finalPromptForSlide,
                'n' => 1,
                'size' => $att['size']
            ]);

            $ch = curl_init('https://api.openai.com/v1/images/generations');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 95);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($extractImage($res, $httpCode, $rawTempOut)) {
                $success = true;
                $endpointUsed = '/v1/images/generations (' . $att['size'] . ')';
                $modelUsed = $att['model'];
                break;
            }
        }
    }

    if (!$success || !file_exists($rawTempOut)) {
        echo json_encode([
            'status' => 'error',
            'message' => "No se pudo generar la Lámina {$slideNumber}. Detalle OpenAI: " . implode(' | ', $apiDiagnostics),
            'diagnostics' => $apiDiagnostics
        ]);
        exit;
    }

    $imgFilename = 'slide_' . str_pad((string)$slideNumber, 2, '0', STR_PAD_LEFT) . '_' . time() . '.png';
    $finalImgPath = $carouselFolder . '/' . $imgFilename;

    if (!finalizeCarouselSlideOutput($rawTempOut, $finalImgPath, $stampLogo ? $logoFullPath : null)) {
        @copy($rawTempOut, $finalImgPath);
    }
    @unlink($rawTempOut);

    $publicImgUrl = 'output/carousels/' . $carouselId . '/' . $imgFilename;

    // Actualizar estado guardado del carrusel
    $carouselRecord = file_exists($stateFile) ? (json_decode(file_get_contents($stateFile), true) ?: []) : ['carousel_id' => $carouselId];
    $carouselRecord['brand_asset'] = $brandAsset;
    $carouselRecord['brand_text'] = $brandText;
    $carouselRecord['logo_asset'] = $logoAsset;
    $carouselRecord['subject_assets'] = $subjectAssets;
    if (!empty($visualDna)) {
        $carouselRecord['visual_dna'] = $visualDna;
    }

    // Actualizar los datos editados de esta slide en el array de slides
    if (isset($carouselRecord['slides']) && is_array($carouselRecord['slides'])) {
        $foundSlide = false;
        foreach ($carouselRecord['slides'] as &$slRef) {
            if ((int)($slRef['slide_number'] ?? 0) === $slideNumber) {
                $slRef['slide_role'] = $slideRole;
                $slRef['headline'] = $headline;
                $slRef['body_text'] = $bodyText;
                $slRef['swipe_indicator'] = $swipeIndicator;
                $slRef['camera_and_action'] = $cameraAndAction;
                $slRef['scene_prompt'] = $scenePrompt;
                $foundSlide = true;
                break;
            }
        }
        unset($slRef);
        if (!$foundSlide) {
            $carouselRecord['slides'][] = [
                'slide_number' => $slideNumber,
                'slide_role' => $slideRole,
                'headline' => $headline,
                'body_text' => $bodyText,
                'swipe_indicator' => $swipeIndicator,
                'camera_and_action' => $cameraAndAction,
                'scene_prompt' => $scenePrompt
            ];
        }
    }

    if (!isset($carouselRecord['slide_images']) || !is_array($carouselRecord['slide_images'])) {
        $carouselRecord['slide_images'] = [];
    }

    $slideImgEntry = [
        'slide_number' => $slideNumber,
        'slide_role' => $slideRole,
        'headline' => $headline,
        'body_text' => $bodyText,
        'swipe_indicator' => $swipeIndicator,
        'scene_prompt' => $scenePrompt,
        'url' => $publicImgUrl,
        'dimensions' => '1080 × 1350 px (4:5)',
        'model_used' => $modelUsed,
        'endpoint_used' => $endpointUsed,
        'references_used' => $referenceNames,
        'created_at' => date('H:i:s')
    ];

    $carouselRecord['slide_images'][$slideNumber] = $slideImgEntry;
    if ($slideNumber === 1 || empty($carouselRecord['cover_image'])) {
        $carouselRecord['cover_image'] = $publicImgUrl;
    }

    if (isset($carouselRecord['turns']) && is_array($carouselRecord['turns']) && count($carouselRecord['turns']) > 0) {
        $lastTurnIdx = count($carouselRecord['turns']) - 1;
        if (!isset($carouselRecord['turns'][$lastTurnIdx]['slide_images']) || !is_array($carouselRecord['turns'][$lastTurnIdx]['slide_images'])) {
            $carouselRecord['turns'][$lastTurnIdx]['slide_images'] = [];
        }
        $carouselRecord['turns'][$lastTurnIdx]['slide_images'][$slideNumber] = $slideImgEntry;
    }

    $carouselRecord['updated_at'] = date('d/m/Y H:i');
    $carouselRecord['timestamp'] = time();
    file_put_contents($stateFile, json_encode($carouselRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([
        'status' => 'ok',
        'carousel_id' => $carouselId,
        'slide_number' => $slideNumber,
        'total_slides' => $totalSlides,
        'image_url' => $publicImgUrl,
        'slide_entry' => $slideImgEntry,
        'carousel_record' => $carouselRecord
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 6. HISTORIAL DE CARRUSELES (GUARDADO Y BORRADO INDEPENDIENTE)
// --------------------------------------------------------------------------
if ($action === 'list_carousels') {
    $list = [];
    if (is_dir($carouselsDir)) {
        $dirs = scandir($carouselsDir, SCANDIR_SORT_DESCENDING);
        foreach ($dirs as $d) {
            if ($d === '.' || $d === '..') continue;
            $stateFile = $carouselsDir . '/' . $d . '/carousel_state.json';
            if (file_exists($stateFile)) {
                $data = json_decode(file_get_contents($stateFile), true);
                if (is_array($data)) {
                    $slidesCount = isset($data['slides']) && is_array($data['slides']) ? count($data['slides']) : 0;
                    $list[] = [
                        'carousel_id' => $data['carousel_id'] ?? $d,
                        'title' => $data['title'] ?? 'Carrusel sin título',
                        'slides_count' => $slidesCount,
                        'cover_image' => $data['cover_image'] ?? null,
                        'updated_at' => $data['updated_at'] ?? '',
                        'timestamp' => $data['timestamp'] ?? filemtime($stateFile)
                    ];
                }
            }
        }
    }
    usort($list, function($a, $b) {
        return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
    });
    echo json_encode(['status' => 'ok', 'carousels' => $list]);
    exit;
}

if ($action === 'get_carousel') {
    $carouselId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['carousel_id'] ?? '');
    $stateFile = $carouselsDir . '/' . $carouselId . '/carousel_state.json';
    if (!$carouselId || !file_exists($stateFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Carrusel no encontrado']);
        exit;
    }
    $data = json_decode(file_get_contents($stateFile), true);
    echo json_encode(['status' => 'ok', 'carousel' => $data]);
    exit;
}

function removeCarouselDirRecursive($dirPath) {
    if (!is_dir($dirPath)) return 0;
    $deletedCount = 0;
    $items = scandir($dirPath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dirPath . '/' . $item;
        if (is_dir($full)) {
            $deletedCount += removeCarouselDirRecursive($full);
        } else {
            if (@unlink($full)) {
                $deletedCount++;
            }
        }
    }
    @rmdir($dirPath);
    return $deletedCount;
}

function collectCarouselSubjectPaths(array $cData) {
    $paths = [];
    if (!empty($cData['subject_assets']) && is_array($cData['subject_assets'])) {
        foreach ($cData['subject_assets'] as $sa) {
            if (!empty($sa['local_path'])) {
                $paths[] = $sa['local_path'];
            }
        }
    }
    if (!empty($cData['turns']) && is_array($cData['turns'])) {
        foreach ($cData['turns'] as $turn) {
            if (!empty($turn['subject_assets']) && is_array($turn['subject_assets'])) {
                foreach ($turn['subject_assets'] as $sa) {
                    if (!empty($sa['local_path'])) {
                        $paths[] = $sa['local_path'];
                    }
                }
            }
        }
    }
    return array_values(array_unique($paths));
}

if ($action === 'delete_carousel' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $carouselId = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['carousel_id'] ?? '');
    if (!$carouselId) {
        echo json_encode(['status' => 'error', 'message' => 'ID de carrusel inválido']);
        exit;
    }

    $target = $carouselsDir . '/' . $carouselId;
    $deletedFilesCount = 0;

    if (is_dir($target)) {
        $stateFile = $target . '/carousel_state.json';
        $subjectPathsToClean = [];
        if (file_exists($stateFile)) {
            $cData = json_decode(file_get_contents($stateFile), true);
            if (is_array($cData)) {
                $subjectPathsToClean = collectCarouselSubjectPaths($cData);
            }
        }

        if (!empty($subjectPathsToClean) && is_dir($carouselsDir)) {
            $usedByOther = [];
            foreach (scandir($carouselsDir) as $d) {
                if ($d === '.' || $d === '..' || $d === $carouselId) continue;
                $otherStateFile = $carouselsDir . '/' . $d . '/carousel_state.json';
                if (file_exists($otherStateFile)) {
                    $otherData = json_decode(file_get_contents($otherStateFile), true);
                    if (is_array($otherData)) {
                        foreach (collectCarouselSubjectPaths($otherData) as $p) {
                            $usedByOther[$p] = true;
                        }
                    }
                }
            }

            foreach ($subjectPathsToClean as $relPath) {
                if (isset($usedByOther[$relPath])) continue;
                $baseName = basename($relPath);
                if (strpos($baseName, 'subject_') === 0) {
                    $fileInData = $uploadsDir . '/' . $baseName;
                    $fileInPublic = $publicUploadsDir . '/' . $baseName;
                    if (file_exists($fileInData) && @unlink($fileInData)) {
                        $deletedFilesCount++;
                    }
                    if (file_exists($fileInPublic) && @unlink($fileInPublic)) {
                        $deletedFilesCount++;
                    }
                }
            }
        }

        $deletedFilesCount += removeCarouselDirRecursive($target);
    }

    echo json_encode([
        'status' => 'ok',
        'deleted_carousel_id' => $carouselId,
        'deleted_files_count' => $deletedFilesCount
    ]);
    exit;
}

if ($action === 'delete_all_carousels' && $reqMethod === 'POST') {
    $deletedThreads = 0;
    $deletedFiles = 0;

    if (is_dir($carouselsDir)) {
        foreach (scandir($carouselsDir) as $d) {
            if ($d === '.' || $d === '..') continue;
            $fullDir = $carouselsDir . '/' . $d;
            if (is_dir($fullDir)) {
                $deletedFiles += removeCarouselDirRecursive($fullDir);
                $deletedThreads++;
            }
        }
    }

    foreach ([$uploadsDir, $publicUploadsDir] as $uDir) {
        if (is_dir($uDir)) {
            foreach (glob($uDir . '/subject_*') as $subFile) {
                if (is_file($subFile) && @unlink($subFile)) {
                    $deletedFiles++;
                }
            }
        }
    }

    echo json_encode([
        'status' => 'ok',
        'deleted_threads' => $deletedThreads,
        'deleted_files' => $deletedFiles
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
