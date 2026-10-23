<?php
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '180');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$reqMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($reqMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$baseDir = dirname(__DIR__);
$configFile = $baseDir . '/data/config.json';
$outputDir = __DIR__ . '/output';
$postsDir = $outputDir . '/posts';
$uploadsDir = $baseDir . '/data/uploads';
$publicUploadsDir = $outputDir . '/uploads';
$tempRefsDir = $baseDir . '/data/temp_refs';

foreach ([$outputDir, $postsDir, $baseDir . '/data', $uploadsDir, $publicUploadsDir, $tempRefsDir] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// --------------------------------------------------------------------------
// PROMPTS DE SISTEMA POR DEFECTO (PASO 1 Y PASO 2 SEPARADOS)
// --------------------------------------------------------------------------
function getDefaultPrompts() {
    return [
        'prompt_director_creativo' =>
            "Actuás como un Director Creativo Publicitario y Estratega de Contenidos para Redes Sociales.\n"
            . "Tu objetivo es analizar la idea del usuario (junto con el logo y el profesional, personaje o producto adjuntos, si los hay) y desarrollar:\n"
            . "1. Los COPIES publicitarios adaptados al estilo, tono y extensión de cada red social seleccionada, con ganchos atractivos, estructura clara y llamado a la acción.\n"
            . "2. La NARRATIVA DE LA IMAGEN: definir el titular corto y potente en español que llevará la pieza y describir en forma narrativa la escena visual (qué debe transmitir la imagen, cómo debe verse el profesional o producto en contexto real y qué atmósfera o entorno acompaña la idea).",

        'prompt_disenador_ia' =>
            "Actuás como un Diseñador Gráfico Editorial Senior y Director de Arte Fotográfico especializado en piezas para redes sociales.\n"
            . "DIRECTRICES DE DISEÑO:\n"
            . "1. PIEZA ÚNICA: Creá una sola pieza gráfica publicitaria unificada y coherente. No dividas la imagen en cuadros, grillas, viñetas ni collages.\n"
            . "2. FIDELIDAD DE PERSONAJE / PROFESIONAL O PRODUCTO: Si se adjunta foto de un profesional, personaje o producto, mantené estrictamente sus rasgos faciales reales, identidad, fisonomía o empaque original, integrándolo de forma fotorrealista en una escena, pose, ángulo e iluminación natural acorde a la narrativa del posteo.\n"
            . "3. INTEGRACIÓN DE MARCA Y LOGO: Si se adjunta un logotipo, integralo de forma limpia, nítida y armónica dentro de la composición respetando la identidad visual y colores de la marca.\n"
            . "4. DISEÑO EDITORIAL Y TIPOGRAFÍA: Integrá el titular principal en español con tipografía moderna, limpia, de alto contraste y excelente legibilidad."
    ];
}

// --------------------------------------------------------------------------
// CONFIGURACIÓN DEL SISTEMA
// --------------------------------------------------------------------------
function getConfig($configFile) {
    $defaultPrompts = getDefaultPrompts();
    $defaults = [
        'openai_api_key' => '',
        'selected_model' => 'gpt-image-2.5-sunburst-2026-09-08',
        'text_model' => 'gpt-4o',
        'google_drive_api_key' => 'AIzaSyDwuvpt_Dhrf8uZgF-DHPQPXn_Mr4piVMo',
        'stamp_logo_overlay' => false,
        'default_logo' => null,
        'prompt_director_creativo' => $defaultPrompts['prompt_director_creativo'],
        'prompt_disenador_ia' => $defaultPrompts['prompt_disenador_ia']
    ];
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        $json = json_decode($content, true);
        if (is_array($json)) {
            $merged = array_merge($defaults, $json);
            if ($merged['selected_model'] === 'gpt-image-2.5-sunburst') {
                $merged['selected_model'] = 'gpt-image-2.5-sunburst-2026-09-08';
            }
            if (empty(trim((string)($merged['prompt_director_creativo'] ?? '')))) {
                $merged['prompt_director_creativo'] = $defaultPrompts['prompt_director_creativo'];
            }
            if (empty(trim((string)($merged['prompt_disenador_ia'] ?? '')))) {
                $merged['prompt_disenador_ia'] = $defaultPrompts['prompt_disenador_ia'];
            }
            return $merged;
        }
    }
    return $defaults;
}

function saveConfig($configFile, $data) {
    file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// --------------------------------------------------------------------------
// PROCESAMIENTO DE IMÁGENES (CON AUTO-ROTACIÓN EXIF PARA CÁMARAS Y CELULARES)
// --------------------------------------------------------------------------
function normalizeUploadedImage($sourcePath, $targetPngPath, $maxDim = 1536) {
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

/**
 * Crea un data URI base64 ligero (máx 512px) para que el modelo de texto/visión (Paso 1)
 * pueda ver el personaje/producto y el logo antes de redactar el diseño narrativo.
 */
function createVisionDataUrl($imagePath, $maxDim = 512) {
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
 * Ajusta automáticamente la imagen generada por Sunburst a las dimensiones exactas
 * según si el usuario eligió POST (1080x1350 - 4:5) o HISTORIA (1080x1920 - 9:16).
 */
function finalizeOutputByPlacement($rawImagePath, $finalPath, $placement = 'post', $logoPath = null) {
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

    // Dimensiones automáticas según Post o Historia
    if ($placement === 'story') {
        $targetW = 1080;
        $targetH = 1920; // 9:16
    } else {
        $targetW = 1080;
        $targetH = 1350; // 4:5
    }

    $dst = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($dst, true);
    imagesavealpha($dst, true);

    $targetRatio = $targetW / $targetH;
    $srcRatio = $srcW / $srcH;

    if (abs($srcRatio - $targetRatio) < 0.02) {
        $cropX = 0;
        $cropY = 0;
        $cropW = $srcW;
        $cropH = $srcH;
    } elseif ($srcRatio > $targetRatio) {
        $cropH = $srcH;
        $cropW = (int)round($srcH * $targetRatio);
        $cropX = (int)round(($srcW - $cropW) / 2);
        $cropY = 0;
    } else {
        $cropW = $srcW;
        $cropH = (int)round($srcW / $targetRatio);
        $cropX = 0;
        $cropY = (int)round(($srcH - $cropH) / 2);
    }

    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
    imagedestroy($src);

    // Si se activó estampado adicional de logo nítido
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
                $maxLogoW = 150;
                $maxLogoH = 110;
                $lScale = min($maxLogoW / $lw, $maxLogoH / $lh);
                $destLW = (int)round($lw * $lScale);
                $destLH = (int)round($lh * $lScale);
                $marginY = ($placement === 'story') ? 140 : 42;
                $destLX = $targetW - $destLW - 42;
                $destLY = $marginY;
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
// 1. OBTENER Y GUARDAR CONFIGURACIÓN
// --------------------------------------------------------------------------
if ($action === 'get_config') {
    $cfg = getConfig($configFile);
    $defaultPrompts = getDefaultPrompts();
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
        'prompt_director_creativo' => $cfg['prompt_director_creativo'] ?? $defaultPrompts['prompt_director_creativo'],
        'prompt_disenador_ia' => $cfg['prompt_disenador_ia'] ?? $defaultPrompts['prompt_disenador_ia'],
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
    $cfg = getConfig($configFile);
    if (isset($input['openai_api_key'])) $cfg['openai_api_key'] = trim($input['openai_api_key']);
    if (isset($input['selected_model'])) $cfg['selected_model'] = trim($input['selected_model']);
    if (isset($input['text_model'])) $cfg['text_model'] = trim($input['text_model']);
    if (isset($input['stamp_logo_overlay'])) $cfg['stamp_logo_overlay'] = (bool)$input['stamp_logo_overlay'];
    if (array_key_exists('default_logo', $input)) $cfg['default_logo'] = $input['default_logo'];
    if (isset($input['prompt_director_creativo'])) $cfg['prompt_director_creativo'] = trim($input['prompt_director_creativo']);
    if (isset($input['prompt_disenador_ia'])) $cfg['prompt_disenador_ia'] = trim($input['prompt_disenador_ia']);
    saveConfig($configFile, $cfg);
    echo json_encode(['status' => 'ok', 'message' => 'Configuración guardada correctamente']);
    exit;
}

// --------------------------------------------------------------------------
// 2. CONSULTAR MODELOS DE OPENAI EN VIVO
// --------------------------------------------------------------------------
if ($action === 'get_models') {
    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key de OpenAI no configurada']);
        exit;
    }

    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'User-Agent: TecnoGenStudio/3.0'
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
// 3. SUBIR LOGO O PERSONAJE / PRODUCTO DESDE LA COMPUTADORA
// --------------------------------------------------------------------------
if ($action === 'upload_asset' && $reqMethod === 'POST') {
    $rawInput = file_get_contents('php://input');
    $jsonBody = ($rawInput && substr(trim($rawInput), 0, 1) === '{') ? json_decode($rawInput, true) : null;

    $assetType = preg_replace('/[^a-z_]/', '', $_POST['type'] ?? ($jsonBody['type'] ?? 'subject'));
    $saveDefault = !empty($_POST['save_default']) || !empty($jsonBody['save_default']);

    $tmpSourceFile = null;
    $cleanupTemp = false;
    $origName = 'imagen.png';

    // Caso A: Viene pre-optimizada desde el navegador en base64 (evita límites upload_max_filesize de PHP)
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

    // Caso B: Viene por $_FILES tradicional
    if (!$tmpSourceFile) {
        if (empty($_FILES['file'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'El archivo supera el límite POST del servidor o no se pudo leer. Intentá de nuevo (ya está activo el optimizador automático).'
            ]);
            exit;
        }
        $errCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($errCode !== UPLOAD_ERR_OK) {
            $errMap = [
                UPLOAD_ERR_INI_SIZE => 'La foto supera el límite de tamaño de PHP (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE => 'La foto supera el límite del formulario.',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió solo parcialmente.',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo.'
            ];
            echo json_encode([
                'status' => 'error',
                'message' => $errMap[$errCode] ?? ('Error al recibir el archivo (código ' . $errCode . ')')
            ]);
            exit;
        }
        $tmpSourceFile = $_FILES['file']['tmp_name'];
        $origName = basename($_FILES['file']['name']);
    }

    $uniqueId = $assetType . '_' . time() . '_' . substr(md5($origName . mt_rand()), 0, 6);
    $destPng = $uploadsDir . '/' . $uniqueId . '.png';

    $convertedOk = normalizeUploadedImage($tmpSourceFile, $destPng, 1600);

    // Fallback en macOS (sips) por si el usuario sube HEIC/AVIF/TIFF o un JPEG especial de cámara
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
            echo json_encode(['status' => 'error', 'message' => 'El archivo no es una imagen compatible (usá PNG, JPG, WEBP o HEIC)']);
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
        'local_path' => 'data/uploads/' . $uniqueId . '.png',
        'preview_url' => 'output/uploads/' . $uniqueId . '.png'
    ];

    if ($assetType === 'logo' && $saveDefault) {
        $cfg = getConfig($configFile);
        $cfg['default_logo'] = $assetObj;
        saveConfig($configFile, $cfg);
    }

    echo json_encode([
        'status' => 'ok',
        'asset' => $assetObj
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 4. PASO 1: MODELO DE TEXTO / VISIÓN CREA LOS COPIES POR RED Y EL DISEÑO
//    NARRATIVO DE LA IMAGEN PARA SUNBURST
// --------------------------------------------------------------------------
if ($action === 'step1_plan_post' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    $textModel = $input['text_model'] ?? ($cfg['text_model'] ?? 'gpt-4o');

    if (!$apiKey) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Falta configurar tu OpenAI API Key. Hacé clic en "⚙️ Configurar API" abajo a la izquierda.'
        ]);
        exit;
    }

    $postId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['post_id'] ?? ''));
    if (!$postId) {
        $postId = 'post_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 5);
    }
    $postFolder = $postsDir . '/' . $postId;
    if (!is_dir($postFolder)) {
        @mkdir($postFolder, 0775, true);
    }

    $userPrompt = trim((string)($input['prompt'] ?? ''));
    $networks = $input['networks'] ?? ['ig', 'fb', 'tiktok', 'linkedin'];
    if (empty($networks) || !is_array($networks)) {
        $networks = ['ig', 'fb', 'tiktok', 'linkedin'];
    }
    $placement = ($input['placement'] ?? 'post') === 'story' ? 'story' : 'post'; // 'post' | 'story'
    $logoAsset = $input['logo_asset'] ?? null;
    $subjectAssets = $input['subject_assets'] ?? []; // Array de fotos de personaje / producto
    $previousState = $input['previous_state'] ?? null; // Si es una corrección en el mismo hilo

    if ($userPrompt === '') {
        echo json_encode(['status' => 'error', 'message' => 'Escribí la idea de lo que querés reflejar en el posteo.']);
        exit;
    }

    $placementLabel = ($placement === 'story')
        ? 'HISTORIA VERTICAL (Formato Pantalla Completa 9:16 - 1080x1920 px, con zonas seguras libres arriba y abajo)'
        : 'POST DE FEED (Formato Vertical Editorial 4:5 - 1080x1350 px)';

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

    $hasLogo = is_array($logoAsset) && !empty($logoAsset['local_path']);
    $hasSubject = is_array($subjectAssets) && count($subjectAssets) > 0;

    $postStateFile = $postFolder . '/post_state.json';
    $existingState = file_exists($postStateFile) ? (json_decode(file_get_contents($postStateFile), true) ?: []) : [];
    $latestExistingImage = $existingState['latest_image'] ?? null;

    $defaultPrompts = getDefaultPrompts();
    $directorPrompt = trim((string)($cfg['prompt_director_creativo'] ?? $defaultPrompts['prompt_director_creativo']));

    $systemInstructions = $directorPrompt . "\n\n"
        . "CONTEXTO DE ESTA PUBLICACIÓN:\n"
        . "- Redes destino: " . implode(', ', $selectedNetworkNames) . "\n"
        . "- Ubicación / Formato: {$placementLabel}\n"
        . "- ¿Subió Logo?: " . ($hasLogo ? "SÍ (Nombre: " . ($logoAsset['name'] ?? 'Logo') . ")" : "NO") . "\n"
        . "- ¿Subió Personaje / Profesional o Producto?: " . ($hasSubject ? "SÍ (" . count($subjectAssets) . " imagen/es adjunta/s)" : "NO") . "\n"
        . ($latestExistingImage
            ? "- ¿Ya existe una pieza gráfica creada en este hilo?: SÍ. Si el usuario está pidiendo corregir un texto, una letra, un color o un detalle puntual sobre la imagen ya generada, activá 'is_image_correction': true e indicá en 'edit_instruction' exactamente qué cambio puntual aplicar manteniendo idéntico el resto de la pieza.\n\n"
            : "\n")
        . "FORMATO DE RESPUESTA OBLIGATORIO (JSON válido):\n"
        . "Devolvé ÚNICAMENTE un objeto JSON con esta estructura exacta:\n"
        . "{\n"
        . "  \"assistant_comment\": \"Breve explicación amigable de tu propuesta creativa o de la corrección aplicada (2-3 oraciones).\",\n"
        . "  \"title_summary\": \"Título corto de 3 a 5 palabras para identificar este post en el historial\",\n"
        . "  \"copies\": {\n"
        . "    \"ig\": \"Copy completo para Instagram (si fue seleccionada, sino cadena vacía) adaptado a {$placement}\",\n"
        . "    \"fb\": \"Copy completo para Facebook (si fue seleccionada, sino cadena vacía) adaptado a {$placement}\",\n"
        . "    \"tiktok\": \"Copy y gancho para TikTok (si fue seleccionada, sino cadena vacía) adaptado a {$placement}\",\n"
        . "    \"linkedin\": \"Copy profesional para LinkedIn (si fue seleccionada, sino cadena vacía) adaptado a {$placement}\"\n"
        . "  },\n"
        . "  \"visual_design\": {\n"
        . "    \"headline_in_image\": \"Texto exacto breve en español que irá escrito dentro del diseño gráfico\",\n"
        . "    \"scene_summary\": \"Resumen claro en español del concepto visual de la escena\",\n"
        . "    \"sunburst_prompt\": \"Descripción narrativa y visual detallada de la escena para que el Diseñador Experto IA construya la pieza gráfica\",\n"
        . "    \"is_image_correction\": " . ($latestExistingImage ? "true" : "false") . ",\n"
        . "    \"edit_instruction\": \"Si es una corrección sobre la imagen ya creada, detallá exactamente qué modificar manteniendo intacto todo el resto del diseño\"\n"
        . "  }\n"
        . "}";

    // Construir contenido multimodal para que el modelo de texto VEA el logo, el personaje/producto y la última imagen si es corrección
    $userContentParts = [];

    $textBlock = "IDEA / PEDIDO DEL USUARIO:\n" . $userPrompt . "\n\n"
        . "REDES SOLICITADAS: " . implode(', ', $selectedNetworkNames) . "\n"
        . "TIPO DE PUBLICACIÓN: " . ($placement === 'story' ? 'Historia (Vertical 9:16)' : 'Post de Feed (Vertical 4:5)');

    if ($previousState && !empty($previousState['visual_design'])) {
        $textBlock .= "\n\nESTADO ANTERIOR DEL POST (El usuario está pidiendo un ajuste o corrección sobre esta propuesta previa):\n"
            . json_encode([
                'copies' => $previousState['copies'] ?? [],
                'visual_design' => $previousState['visual_design'] ?? []
            ], JSON_UNESCAPED_UNICODE);
    }

    $userContentParts[] = [
        'type' => 'text',
        'text' => $textBlock
    ];

    // Si ya hay una imagen generada en este hilo, mostrársela también a gpt-4o para que vea qué corregir
    if ($latestExistingImage) {
        $existingImgFull = __DIR__ . '/' . ltrim($latestExistingImage, '/');
        $existingImgUri = createVisionDataUrl($existingImgFull, 512);
        if ($existingImgUri) {
            $userContentParts[] = [
                'type' => 'text',
                'text' => "PIEZA GRÁFICA ACTUAL YA GENERADA EN ESTE HILO (Referencia para aplicar correcciones manteniendo la coherencia):"
            ];
            $userContentParts[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $existingImgUri, 'detail' => 'low']
            ];
        }
    }

    // Adjuntar vista previa del Personaje / Producto al modelo de visión
    if ($hasSubject) {
        foreach ($subjectAssets as $idx => $sa) {
            $localFile = $baseDir . '/' . ltrim($sa['local_path'] ?? '', '/');
            $dataUri = createVisionDataUrl($localFile, 512);
            if ($dataUri) {
                $userContentParts[] = [
                    'type' => 'text',
                    'text' => "IMAGEN ADJUNTA — PERSONAJE O PRODUCTO (" . ($sa['name'] ?? ('Referencia ' . ($idx + 1))) . "):"
                ];
                $userContentParts[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $dataUri, 'detail' => 'low']
                ];
            }
        }
    }

    // Adjuntar vista previa del Logo al modelo de visión
    if ($hasLogo) {
        $logoFile = $baseDir . '/' . ltrim($logoAsset['local_path'], '/');
        $logoUri = createVisionDataUrl($logoFile, 512);
        if ($logoUri) {
            $userContentParts[] = [
                'type' => 'text',
                'text' => "IMAGEN ADJUNTA — LOGO DE LA MARCA (" . ($logoAsset['name'] ?? 'Logo') . "):"
            ];
            $userContentParts[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $logoUri, 'detail' => 'low']
            ];
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$res) {
        $errJson = @json_decode($res, true);
        $errMsg = $errJson['error']['message'] ?? ($curlErr ?: "HTTP {$httpCode}");
        echo json_encode([
            'status' => 'error',
            'message' => 'Error en Paso 1 (Director Creativo): ' . $errMsg
        ]);
        exit;
    }

    $apiData = json_decode($res, true);
    $rawContent = $apiData['choices'][0]['message']['content'] ?? '';
    $plan = json_decode($rawContent, true);

    if (!$plan || !isset($plan['visual_design'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El modelo de texto no devolvió un formato válido. Intentá nuevamente.'
        ]);
        exit;
    }

    $turns = isset($existingState['turns']) && is_array($existingState['turns']) ? $existingState['turns'] : [];
    $turns[] = [
        'user_prompt' => $userPrompt,
        'networks' => $networks,
        'placement' => $placement,
        'assistant_comment' => $plan['assistant_comment'] ?? '',
        'copies' => $plan['copies'] ?? [],
        'visual_design' => $plan['visual_design'],
        'created_at' => date('d/m/Y H:i:s'),
        'images' => []
    ];

    $postRecord = [
        'post_id' => $postId,
        'title' => $existingState['title'] ?? ($plan['title_summary'] ?? mb_substr($userPrompt, 0, 45)),
        'last_idea' => $userPrompt,
        'networks' => $networks,
        'placement' => $placement,
        'logo_asset' => $logoAsset,
        'subject_assets' => $subjectAssets,
        'assistant_comment' => $plan['assistant_comment'] ?? '',
        'copies' => $plan['copies'] ?? [],
        'visual_design' => $plan['visual_design'],
        'turns' => $turns,
        'images' => $existingState['images'] ?? [],
        'latest_image' => $latestExistingImage,
        'updated_at' => date('d/m/Y H:i'),
        'timestamp' => time()
    ];

    file_put_contents($postStateFile, json_encode($postRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([
        'status' => 'ok',
        'post_id' => $postId,
        'text_model_used' => $textModel,
        'plan' => $postRecord
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 5. PASO 2: COMBINAR PROMPT DEL DISEÑADOR EXPERTO IA + ESCENA + ARCHIVOS
// --------------------------------------------------------------------------
if ($action === 'step2_generate_image' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';

    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key de OpenAI no configurada.']);
        exit;
    }

    $postId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['post_id'] ?? ''));
    if (!$postId) {
        $postId = 'post_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 5);
    }
    $postFolder = $postsDir . '/' . $postId;
    if (!is_dir($postFolder)) {
        @mkdir($postFolder, 0775, true);
    }

    $postStateFile = $postFolder . '/post_state.json';
    $existingState = file_exists($postStateFile) ? (json_decode(file_get_contents($postStateFile), true) ?: []) : [];

    $sunburstPrompt = trim((string)($input['sunburst_prompt'] ?? ''));
    $headlineInImage = trim((string)($input['headline_in_image'] ?? ''));
    $editInstruction = trim((string)($input['edit_instruction'] ?? ($existingState['visual_design']['edit_instruction'] ?? '')));
    $isCorrection = !empty($input['is_image_correction']) || !empty($existingState['visual_design']['is_image_correction']);
    $forceNewVariant = !empty($input['force_new_variant']);
    $placement = ($input['placement'] ?? 'post') === 'story' ? 'story' : 'post';
    $model = trim((string)($input['model'] ?? ($cfg['selected_model'] ?? 'gpt-image-2.5-sunburst-2026-09-08')));
    if ($model === 'gpt-image-2.5-sunburst') {
        $model = 'gpt-image-2.5-sunburst-2026-09-08';
    }
    $logoAsset = $input['logo_asset'] ?? null;
    $subjectAssets = $input['subject_assets'] ?? [];
    $stampLogo = isset($input['stamp_logo']) ? (bool)$input['stamp_logo'] : !empty($cfg['stamp_logo_overlay']);

    if ($sunburstPrompt === '') {
        echo json_encode(['status' => 'error', 'message' => 'El prompt de diseño de imagen está vacío.']);
        exit;
    }

    $defaultPrompts = getDefaultPrompts();
    $designerMasterPrompt = trim((string)($cfg['prompt_disenador_ia'] ?? $defaultPrompts['prompt_disenador_ia']));
    $placementRule = ($placement === 'story')
        ? 'FORMATO DE SALIDA: HISTORIA VERTICAL PANTALLA COMPLETA (9:16 - 1080x1920 px). Dejá libres de texto crítico el 15% superior y el 18% inferior.'
        : 'FORMATO DE SALIDA: POST EDITORIAL DE FEED (4:5 - 1080x1350 px).';

    // Verificar si ya existe una imagen generada en este hilo para editar sobre ella manteniendo coherencia
    $latestImgRelative = $existingState['latest_image'] ?? null;
    $latestImgFull = $latestImgRelative ? (__DIR__ . '/' . ltrim($latestImgRelative, '/')) : null;
    $useExistingPieceAsBase = (!$forceNewVariant && $isCorrection && $latestImgFull && file_exists($latestImgFull));

    $referenceFiles = [];
    $referenceNames = [];

    if ($useExistingPieceAsBase) {
        // MODO CORRECCIÓN COHERENTE: La imagen base [0] es la última pieza gráfica ya generada
        $referenceFiles[] = ['path' => $latestImgFull, 'role' => 'base_piece', 'name' => basename($latestImgFull)];
        $referenceNames[] = 'Pieza anterior para corrección coherente';

        $finalPromptForSunburst = $designerMasterPrompt
            . "\n\nMODO EDICIÓN COHERENTE SOBRE LA PIEZA EXISTENTE (IMAGEN ADJUNTA 1):\n"
            . "Tomá como base exactamente la pieza gráfica ya generada que se adjunta como primera imagen. "
            . "MANTENÉ IDÉNTICOS la composición, la foto/pose del profesional o producto, el fondo, la iluminación, el logo y el estilo visual general. "
            . "Aplicá ÚNICAMENTE la siguiente modificación solicitada:\n"
            . ($editInstruction !== '' ? $editInstruction : $sunburstPrompt)
            . "\n\nTEXTO / TITULAR EXACTO QUE DEBE QUEDAR EN LA IMAGEN: \"{$headlineInImage}\""
            . "\n\nDESCRIPCIÓN COMPLETA DE REFERENCIA DE LA PIEZA:\n" . $sunburstPrompt;
    } else {
        // MODO CREACIÓN: Construir el prompt final uniendo el Prompt del Diseñador Experto IA + Escena del Paso 1 + Titular
        $finalPromptForSunburst = $designerMasterPrompt
            . "\n\n" . $placementRule
            . "\n\nDISEÑO Y ESCENA A CREAR:\n" . $sunburstPrompt;

        if ($headlineInImage !== '' && stripos($sunburstPrompt, $headlineInImage) === false) {
            $finalPromptForSunburst .= "\n\nTEXTO PRINCIPAL A INTEGRAR EN EL DISEÑO GRÁFICO: \"{$headlineInImage}\"";
        }
    }

    // Recolectar archivos de referencia reales (Personaje/Producto + Logo)
    if (is_array($subjectAssets)) {
        foreach ($subjectAssets as $sa) {
            if (!empty($sa['local_path'])) {
                $fullPath = $baseDir . '/' . ltrim($sa['local_path'], '/');
                if (file_exists($fullPath)) {
                    $referenceFiles[] = ['path' => $fullPath, 'role' => 'subject', 'name' => $sa['name'] ?? 'subject.png'];
                    $referenceNames[] = 'Personaje/Producto: ' . ($sa['name'] ?? 'imagen');
                }
            }
        }
    }

    $logoFullPath = null;
    if (is_array($logoAsset) && !empty($logoAsset['local_path'])) {
        $candidateLogo = $baseDir . '/' . ltrim($logoAsset['local_path'], '/');
        if (file_exists($candidateLogo)) {
            $logoFullPath = $candidateLogo;
            $referenceFiles[] = ['path' => $candidateLogo, 'role' => 'logo', 'name' => $logoAsset['name'] ?? 'logo.png'];
            $referenceNames[] = 'Logo: ' . ($logoAsset['name'] ?? 'logo');
        }
    }

    $rawTempOut = $tempRefsDir . '/raw_sunburst_' . uniqid() . '.png';
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

    // SI EL USUARIO SUBIÓ PERSONAJE/PRODUCTO Y/O LOGO -> ENVIAR A /v1/images/edits
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
                'prompt' => $finalPromptForSunburst,
                'n' => 1,
                'size' => $att['size']
            ];

            if ($att['multi'] && count($referenceFiles) > 1) {
                foreach ($referenceFiles as $idx => $rf) {
                    $postFields["image[{$idx}]"] = new CURLFile($rf['path'], 'image/png', "ref_{$idx}.png");
                }
            } else {
                $primary = $referenceFiles[0];
                $postFields['image'] = new CURLFile($primary['path'], 'image/png', 'reference.png');
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

    // SI NO SE SUBIERON IMÁGENES O EL MODELO SOLO SOPORTA GENERATIONS
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
                'prompt' => $finalPromptForSunburst,
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
            'message' => 'No se pudo generar la imagen en Sunburst. Detalle OpenAI: ' . implode(' | ', $apiDiagnostics),
            'diagnostics' => $apiDiagnostics
        ]);
        exit;
    }

    $imgFilename = 'pieza_' . $placement . '_' . time() . '.png';
    $finalImgPath = $postFolder . '/' . $imgFilename;

    if (!finalizeOutputByPlacement($rawTempOut, $finalImgPath, $placement, $stampLogo ? $logoFullPath : null)) {
        @copy($rawTempOut, $finalImgPath);
    }
    @unlink($rawTempOut);

    $publicImgUrl = 'output/posts/' . $postId . '/' . $imgFilename;
    $dimensionsLabel = ($placement === 'story') ? '1080 × 1920 px (Historia 9:16)' : '1080 × 1350 px (Post 4:5)';

    // Actualizar estado guardado del post
    $postStateFile = $postFolder . '/post_state.json';
    $postRecord = file_exists($postStateFile) ? (json_decode(file_get_contents($postStateFile), true) ?: []) : ['post_id' => $postId];
    $postRecord['latest_image'] = $publicImgUrl;
    $postRecord['placement'] = $placement;
    if (!isset($postRecord['images']) || !is_array($postRecord['images'])) {
        $postRecord['images'] = [];
    }
    $imgEntry = [
        'url' => $publicImgUrl,
        'placement' => $placement,
        'dimensions' => $dimensionsLabel,
        'model_used' => $modelUsed,
        'endpoint_used' => $endpointUsed,
        'references_used' => $referenceNames,
        'prompt_used' => $finalPromptForSunburst,
        'created_at' => date('H:i:s')
    ];
    array_unshift($postRecord['images'], $imgEntry);

    if (isset($postRecord['turns']) && is_array($postRecord['turns']) && count($postRecord['turns']) > 0) {
        $lastIdx = count($postRecord['turns']) - 1;
        if (!isset($postRecord['turns'][$lastIdx]['images']) || !is_array($postRecord['turns'][$lastIdx]['images'])) {
            $postRecord['turns'][$lastIdx]['images'] = [];
        }
        $postRecord['turns'][$lastIdx]['images'][] = $imgEntry;
    }

    $postRecord['updated_at'] = date('d/m/Y H:i');
    $postRecord['timestamp'] = time();
    file_put_contents($postStateFile, json_encode($postRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([
        'status' => 'ok',
        'post_id' => $postId,
        'image_url' => $publicImgUrl,
        'placement' => $placement,
        'dimensions' => $dimensionsLabel,
        'model_used' => $modelUsed,
        'endpoint_used' => $endpointUsed,
        'references_used' => $referenceNames,
        'post_record' => $postRecord
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 6. HISTORIAL DE POSTS / HILOS (SIDEBAR ESTILO CHATGPT)
// --------------------------------------------------------------------------
if ($action === 'list_posts') {
    $posts = [];
    if (is_dir($postsDir)) {
        $dirs = scandir($postsDir, SCANDIR_SORT_DESCENDING);
        foreach ($dirs as $d) {
            if ($d === '.' || $d === '..') continue;
            $stateFile = $postsDir . '/' . $d . '/post_state.json';
            if (file_exists($stateFile)) {
                $data = json_decode(file_get_contents($stateFile), true);
                if (is_array($data)) {
                    $posts[] = [
                        'post_id' => $data['post_id'] ?? $d,
                        'title' => $data['title'] ?? 'Publicación sin título',
                        'placement' => $data['placement'] ?? 'post',
                        'networks' => $data['networks'] ?? [],
                        'latest_image' => $data['latest_image'] ?? null,
                        'updated_at' => $data['updated_at'] ?? '',
                        'timestamp' => $data['timestamp'] ?? filemtime($stateFile)
                    ];
                }
            }
        }
    }
    usort($posts, function($a, $b) {
        return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
    });
    echo json_encode(['status' => 'ok', 'posts' => $posts]);
    exit;
}

if ($action === 'get_post') {
    $postId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['post_id'] ?? '');
    $stateFile = $postsDir . '/' . $postId . '/post_state.json';
    if (!$postId || !file_exists($stateFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Publicación no encontrada']);
        exit;
    }
    $data = json_decode(file_get_contents($stateFile), true);
    echo json_encode(['status' => 'ok', 'post' => $data]);
    exit;
}

if ($action === 'delete_post' && $reqMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $postId = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['post_id'] ?? '');
    $target = $postsDir . '/' . $postId;
    if ($postId && is_dir($target)) {
        foreach (glob($target . '/*') as $f) {
            @unlink($f);
        }
        @rmdir($target);
    }
    echo json_encode(['status' => 'ok']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
