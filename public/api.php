<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$baseDir = dirname(__DIR__);
$configFile = $baseDir . '/data/config.json';
$outputDir = __DIR__ . '/output';

if (!is_dir($outputDir)) {
    @mkdir($outputDir, 0775, true);
}
if (!is_dir($baseDir . '/data')) {
    @mkdir($baseDir . '/data', 0775, true);
}

function getConfig($configFile) {
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        $json = json_decode($content, true);
        if ($json) return $json;
    }
    return [
        'openai_api_key' => '',
        'selected_model' => 'gpt-image-2.5-sunburst',
        'sheet_url' => 'https://docs.google.com/spreadsheets/d/1xvNd-tg2gXnMkXh8T4W8ibbyZE1I29gMjrG3jYwiPDo/export?format=csv&gid=1841829525'
    ];
}

function saveConfig($configFile, $data) {
    file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? '';

// 1. OBTENER CONFIGURACIÓN
if ($action === 'get_config') {
    $cfg = getConfig($configFile);
    $key = $cfg['openai_api_key'] ?? '';
    $maskedKey = (strlen($key) > 12) ? substr($key, 0, 7) . '...' . substr($key, -4) : $key;
    echo json_encode([
        'status' => 'ok',
        'openai_api_key' => $key,
        'masked_key' => $maskedKey,
        'selected_model' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst',
        'sheet_url' => $cfg['sheet_url'] ?? ''
    ]);
    exit;
}

// 2. GUARDAR CONFIGURACIÓN
if ($action === 'save_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit;
    }
    $cfg = getConfig($configFile);
    if (isset($input['openai_api_key'])) $cfg['openai_api_key'] = trim($input['openai_api_key']);
    if (isset($input['selected_model'])) $cfg['selected_model'] = trim($input['selected_model']);
    if (isset($input['sheet_url'])) $cfg['sheet_url'] = trim($input['sheet_url']);
    saveConfig($configFile, $cfg);
    echo json_encode(['status' => 'ok', 'message' => 'Configuración guardada']);
    exit;
}

// 3. CONSULTAR MODELOS DE OPENAI EN VIVO
if ($action === 'get_models') {
    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key no configurada']);
        exit;
    }

    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'User-Agent: TecnoGenStudio/1.0'
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
            if (isset($m['id'])) {
                $modelsList[] = $m['id'];
            }
        }
    }
    sort($modelsList);

    // Filtrar modelos de imagen
    $imageKeywords = ['image', 'dall', 'sunburst', 'flare'];
    $imageModels = array_values(array_filter($modelsList, function($id) use ($imageKeywords) {
        $lower = strtolower($id);
        foreach ($imageKeywords as $kw) {
            if (strpos($lower, $kw) !== false) return true;
        }
        return false;
    }));

    echo json_encode([
        'status' => 'ok',
        'image_models' => $imageModels,
        'all_models' => $modelsList,
        'current_selected' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst'
    ]);
    exit;
}

// 4. OBTENER FILAS DEL GOOGLE SHEET
if ($action === 'get_sheet_rows') {
    $cfg = getConfig($configFile);
    $sheetUrl = $cfg['sheet_url'] ?? '';
    if (!$sheetUrl) {
        echo json_encode(['status' => 'error', 'message' => 'URL de Google Sheet no configurada']);
        exit;
    }

    $ch = curl_init($sheetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0']);
    $csvContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$csvContent) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo descargar el Google Sheet (HTTP ' . $httpCode . ')']);
        exit;
    }

    $lines = str_getcsv($csvContent, "\n");
    if (empty($lines)) {
        echo json_encode(['status' => 'error', 'message' => 'Archivo CSV vacío']);
        exit;
    }

    $headers = str_getcsv(array_shift($lines));
    $headers = array_map('trim', $headers);

    $rows = [];
    foreach ($lines as $lineStr) {
        if (!trim($lineStr)) continue;
        $rowCols = str_getcsv($lineStr);
        if (count($rowCols) < count($headers)) {
            $rowCols = array_pad($rowCols, count($headers), '');
        }
        $row = array_combine($headers, array_slice($rowCols, 0, count($headers)));
        
        $guion = $row['GUION'] ?? '';
        $slideCount = preg_match_all('/(?:^\s*\d+\.|\bSlide\s+\d+)/mi', $guion, $matches);
        if ($slideCount === 0 && !empty($guion)) {
            $slideCount = count(array_filter(explode("\n", $guion)));
        }

        $rows[] = [
            'n' => $row['N°'] ?? '',
            'tema' => $row['TEMA'] ?? '',
            'titulo' => $row['TÍTULO / PORTADA'] ?? '',
            'dra' => $row['DRA'] ?? '',
            'estado' => $row['ESTADO'] ?? '',
            'slides_count' => $slideCount ?: 1,
            'full_data' => $row
        ];
    }

    echo json_encode([
        'status' => 'ok',
        'total' => count($rows),
        'headers' => $headers,
        'rows' => $rows
    ]);
    exit;
}

// 5. GENERAR PROMPT PARA UNA FILA
if ($action === 'get_row_prompt') {
    $nTarget = trim($_GET['n'] ?? '');
    if ($nTarget === '') {
        echo json_encode(['status' => 'error', 'message' => 'Debe indicar el N° de fila']);
        exit;
    }

    $cfg = getConfig($configFile);
    $sheetUrl = $cfg['sheet_url'] ?? '';

    $ch = curl_init($sheetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0']);
    $csvContent = curl_exec($ch);
    curl_close($ch);

    $lines = str_getcsv($csvContent, "\n");
    $headers = str_getcsv(array_shift($lines));
    $headers = array_map('trim', $headers);

    $targetRow = null;
    foreach ($lines as $lineStr) {
        if (!trim($lineStr)) continue;
        $rowCols = str_getcsv($lineStr);
        if (count($rowCols) < count($headers)) $rowCols = array_pad($rowCols, count($headers), '');
        $row = array_combine($headers, array_slice($rowCols, 0, count($headers)));
        if (trim($row['N°'] ?? '') === $nTarget) {
            $targetRow = $row;
            break;
        }
    }

    if (!$targetRow) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la fila N° ' . $nTarget]);
        exit;
    }

    $n = $targetRow['N°'] ?? '';
    $tema = $targetRow['TEMA'] ?? '';
    $titulo = $targetRow['TÍTULO / PORTADA'] ?? '';
    $guion = $targetRow['GUION'] ?? '';
    $subtitulos = $targetRow['SUBTÍTULOS / PALABRAS CLAVE'] ?? '';
    $dra = $targetRow['DRA'] ?? 'Jovany';
    $linkFotos = $targetRow['Link a carpeta de fotos'] ?? ($targetRow['Elegir una foto dentro de este link'] ?? '');
    $linkLogo = $targetRow['Link a carpeta donde elegir el logo'] ?? '';
    $linkManual = $targetRow['Link a manual de marca'] ?? '';

    $prompt = "ACTUÁ COMO DIRECTOR/A DE ARTE, DISEÑADOR/A GRÁFICO/A PROFESIONAL Y PRODUCTOR/A DE CARRUSELES PARA INSTAGRAM DE JM ODONTOLOGÍA INTEGRAL.\n\n"
        . "FUENTE DE DATOS (FILA N° {$n})\n"
        . "- TEMA: {$tema}\n"
        . "- TÍTULO / PORTADA: {$titulo}\n"
        . "- DOCTORA PROTAGONISTA: Dra. {$dra}\n"
        . "- SUBTÍTULOS / PALABRAS CLAVE: {$subtitulos}\n"
        . "- LINK A CARPETA DE FOTOS: {$linkFotos}\n"
        . "- LINK A CARPETA DE LOGOS: {$linkLogo}\n"
        . "- LINK A MANUAL DE MARCA: {$linkManual}\n\n"
        . "OBJETIVO Y FORMATO\n"
        . "Crear un carrusel de Instagram formado por imágenes finales INDIVIDUALES: una imagen por cada slide enumerado dentro de la columna GUION.\n"
        . "- Formato: 1080 × 1350 px (proporción vertical 4:5).\n"
        . "- Una pieza final independiente por slide (slide_01, slide_02, etc.).\n"
        . "- Sin grillas, collages ni plantillas repetitivas.\n\n"
        . "GUION COMPLETO SLIDE POR SLIDE:\n"
        . "{$guion}\n\n"
        . "DIRECCIÓN CINEMATOGRÁFICA Y ACTUACIÓN ORGÁNICA (CERO FOTO DE STOCK)\n"
        . "- La doctora habita un espacio tridimensional real en el consultorio: sentada al borde del sillón, conversando distendida, agachada junto al paciente, caminando o apoyada en una mesada con naturalidad.\n"
        . "- Gestos y miradas auténticas: mirada directa y conectada a la cámara cuando converse con el usuario, o concentrada en la acción médica/paciente/pantalla.\n"
        . "- Variar planos: medios de conversación, escorzos con profundidad de campo, primeros planos cálidos y planos detalle/macro de elementos clínicos (dientes, esmalte, instrumental).\n\n"
        . "DIRECCIÓN DE ARTE Y APLICACIÓN DE MARCA\n"
        . "- Aplicar estrictamente la identidad visual extraída de 'Link a manual de marca': Relax Blue (#16345F), Sky Pulse (#7DD3FC), Core Black (#1D1D1B), Grey Calm (#F2F2F2), White (#FFFFFF).\n"
        . "- Logo oficial colocado en posición fija con su área de protección.\n"
        . "- Tipografía sans-serif editorial limpia y legible desde el celular en el espacio negativo natural de cada fotografía.\n"
        . "- Navegación: 'Deslizá →' sutil en portada, indicador de progreso discreto (ej. 2/8) en intermedios, y foco en llamado a la acción al final.\n\n"
        . "Generá las imágenes finales individuales según los slides del guion.";

    echo json_encode([
        'status' => 'ok',
        'row' => $targetRow,
        'prompt' => $prompt
    ]);
    exit;
}

// 6. ENVIAR A PRODUCIR (LLAMADO AL API DE OPENAI)
if ($action === 'produce' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Payload JSON inválido']);
        exit;
    }

    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key no configurada']);
        exit;
    }

    $rowNum = $input['row_num'] ?? '1';
    $model = $input['model'] ?? ($cfg['selected_model'] ?? 'gpt-image-2.5-sunburst');
    $editedPrompt = $input['edited_prompt'] ?? '';
    $rowData = $input['row_data'] ?? [];

    $folderName = 'carrusel_N' . $rowNum . '_' . time();
    $targetDir = $outputDir . '/' . $folderName;
    @mkdir($targetDir, 0775, true);

    file_put_contents($targetDir . '/prompt_utilizado.txt', $editedPrompt);
    file_put_contents($targetDir . '/row_data.json', json_encode($rowData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Parsear slides desde el guion
    $guion = $rowData['GUION'] ?? '';
    $lines = explode("\n", $guion);
    $slides = [];
    $sIdx = 1;
    foreach ($lines as $line) {
        $l = trim($line);
        if (!$l) continue;
        if (preg_match('/^(\d+)\.\s*(.*)$/', $l, $m)) {
            $slides[] = ['num' => (int)$m[1], 'text' => trim($m[2], " \t\n\r\0\x0B\"'")];
            $sIdx = (int)$m[1] + 1;
        } else {
            $slides[] = ['num' => $sIdx, 'text' => trim($l, " \t\n\r\0\x0B\"'")];
            $sIdx++;
        }
    }
    if (empty($slides)) {
        $slides[] = ['num' => 1, 'text' => $rowData['TÍTULO / PORTADA'] ?? 'Portada'];
    }

    $totalSlides = count($slides);
    $results = [];
    $errors = [];

    // Foto madre si existe en servidor
    $fotoMadrePath = $baseDir . '/carrusel_fila2/foto_madre_1024.png';
    if (!file_exists($fotoMadrePath)) {
        $fotoMadrePath = $baseDir . '/carrusel_fila4/foto_madre_1024.png';
    }

    foreach ($slides as $idx => $s) {
        $slideNum = $idx + 1;
        $slideFilename = sprintf('slide_%02d.png', $slideNum);
        $slidePrompt = "Professional high-end cinematic dental clinic photograph. " . $s['text'] 
            . ". Female dentist Dra Jovany in modern clean luminous dental clinic, natural authentic posture and expressions, direct eye contact or professional focus, soft Scandinavian lighting, ample negative space. 4k detail.";

        $success = false;
        // Modo Edits si sunburst y foto madre disponible
        if (file_exists($fotoMadrePath) && strpos($model, 'sunburst') !== false) {
            $cfile = new CURLFile($fotoMadrePath, 'image/png', 'foto_madre.png');
            $postFields = [
                'model' => $model,
                'image' => $cfile,
                'prompt' => $slidePrompt,
                'n' => 1,
                'size' => '1024x1024'
            ];

            $ch = curl_init('https://api.openai.com/v1/images/edits');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $res = curl_exec($ch);
            curl_close($ch);

            $apiData = json_decode($res, true);
            if (isset($apiData['data'][0])) {
                $item = $apiData['data'][0];
                if (!empty($item['b64_json'])) {
                    file_put_contents($targetDir . '/' . $slideFilename, base64_decode($item['b64_json']));
                    $results[] = 'output/' . $folderName . '/' . $slideFilename;
                    $success = true;
                } elseif (!empty($item['url'])) {
                    $imgContent = @file_get_contents($item['url']);
                    if ($imgContent) {
                        file_put_contents($targetDir . '/' . $slideFilename, $imgContent);
                        $results[] = 'output/' . $folderName . '/' . $slideFilename;
                        $success = true;
                    }
                }
            }
        }

        // Si no se usó edits o falló, usar generations como fallback
        if (!$success) {
            $postJson = json_encode([
                'model' => (strpos($model, 'sunburst') !== false || strpos($model, 'dall') !== false) ? $model : 'dall-e-3',
                'prompt' => $slidePrompt,
                'n' => 1,
                'size' => '1024x1024'
            ]);

            $ch = curl_init('https://api.openai.com/v1/images/generations');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $res = curl_exec($ch);
            curl_close($ch);

            $apiData = json_decode($res, true);
            if (isset($apiData['data'][0])) {
                $item = $apiData['data'][0];
                if (!empty($item['b64_json'])) {
                    file_put_contents($targetDir . '/' . $slideFilename, base64_decode($item['b64_json']));
                    $results[] = 'output/' . $folderName . '/' . $slideFilename;
                    $success = true;
                } elseif (!empty($item['url'])) {
                    $imgContent = @file_get_contents($item['url']);
                    if ($imgContent) {
                        file_put_contents($targetDir . '/' . $slideFilename, $imgContent);
                        $results[] = 'output/' . $folderName . '/' . $slideFilename;
                        $success = true;
                    }
                }
            }
        }

        if (!$success) {
            $errors[] = "Slide {$slideNum}: no se pudo generar.";
        }
    }

    echo json_encode([
        'status' => 'ok',
        'message' => 'Producción completada',
        'folder' => $folderName,
        'total_slides' => $totalSlides,
        'generated_count' => count($results),
        'images' => $results,
        'errors' => $errors
    ]);
    exit;
}

// 7. HISTORIAL DE PRODUCCIONES
if ($action === 'get_history') {
    $history = [];
    if (is_dir($outputDir)) {
        $dirs = scandir($outputDir, SCANDIR_SORT_DESCENDING);
        foreach ($dirs as $d) {
            if ($d === '.' || $d === '..' || !is_dir($outputDir . '/' . $d)) continue;
            if (strpos($d, 'carrusel_') === 0) {
                $files = glob($outputDir . '/' . $d . '/slide_*.png');
                if (!empty($files)) {
                    sort($files);
                    $slideUrls = array_map(function($f) use ($d) {
                        return 'output/' . $d . '/' . basename($f);
                    }, $files);
                    $history[] = [
                        'folder' => $d,
                        'total_slides' => count($slideUrls),
                        'thumbnail' => $slideUrls[0] ?? '',
                        'slides' => $slideUrls
                    ];
                }
            }
        }
    }
    echo json_encode(['status' => 'ok', 'history' => $history]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
