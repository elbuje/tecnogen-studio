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
$masterTemplateFile = $baseDir . '/data/prompt_master_template.txt';
$slideTemplateFile = $baseDir . '/data/slide_prompt_template.txt';

if (!is_dir($outputDir)) {
    @mkdir($outputDir, 0775, true);
}
if (!is_dir($baseDir . '/data')) {
    @mkdir($baseDir . '/data', 0775, true);
}

// --------------------------------------------------------------------------
// PLANTILLAS POR DEFECTO
// --------------------------------------------------------------------------
function getDefaultMasterTemplate() {
    return <<<'EOT'
ACTUÁ COMO DIRECTOR/A DE ARTE, DISEÑADOR/A GRÁFICO/A PROFESIONAL Y PRODUCTOR/A DE CARRUSELES PARA INSTAGRAM DE JM ODONTOLOGÍA INTEGRAL.

FUENTE DE DATOS
Trabajá exclusivamente con la fila seleccionada del Google Sheet:
- FILA N°: {{N}}
- TEMA: {{TEMA}}
- TÍTULO / PORTADA: {{TITULO}}
- DOCTORA PROTAGONISTA: Dra. {{DRA}}
- SUBTÍTULOS / PALABRAS CLAVE: {{SUBTITULOS}}
- LINK A CARPETA DE FOTOS: {{LINK_FOTOS}}
- LINK A CARPETA DE LOGOS: {{LINK_LOGOS}}
- LINK A MANUAL DE MARCA: {{LINK_MANUAL}}
- ESTADO: {{ESTADO}}

OBJETIVO Y ALCANCE
Crear un carrusel de Instagram para “JM Odontología Integral”, formado por imágenes finales INDIVIDUALES: una imagen por cada slide enumerado dentro de la columna “GUION”.
Total de piezas a generar: exactamente {{CANTIDAD_SLIDES}} imágenes independientes.

FORMATO OBLIGATORIO
- Una pieza final independiente por slide (slide_01, slide_02, etc.).
- Proporción vertical 4:5 (1080 × 1350 px).
- Sin grillas, collages ni plantillas repetitivas.
- Cada slide debe ser una pieza editorial completa y lista para publicar.

GUION COMPLETO SLIDE POR SLIDE:
{{GUION}}

DIRECCIÓN DE FOTOGRAFÍA, ACTUACIÓN Y ESCENOGRAFÍA
- Espacio real y tridimensional: consultorio moderno, limpio, luminoso y acogedor, con estilo escandinavo y elementos de alta gama.
- Actuación orgánica y espontánea: la doctora habita el espacio naturalmente (sentada al borde de un sillón, conversando distendida, apoyada en una mesada o mostrando un procedimiento con soltura).
- Mirada y conexión: contacto visual directo, cálido y empático con la lente cuando converse con el usuario, o concentración profesional en el paciente/pantalla/elemento clínico.
- Variación cinematográfica de planos: alternar planos medios de conversación, planos detalle/macro de elementos dentales o instrumental, y primeros planos cálidos y cercanos.

IDENTIDAD VISUAL Y APLICACIÓN DE MARCA
- Paleta oficial del Manual de Marca:
  * Relax Blue: #16345F
  * Sky Pulse: #7DD3FC
  * Core Black: #1D1D1B
  * Grey Calm: #F2F2F2
  * White: #FFFFFF
- Tipografía editorial sans-serif limpia, con jerarquía visual clara y legible en smartphones sobre el espacio negativo de cada toma.
- Indicador sutil de navegación («Deslizá →» en portada y progreso discreto en slides intermedios).
- Logo oficial ubicado en posición armónica respetando su área de protección.

Generá las piezas individuales correspondientes a cada slide del guion.
EOT;
}

function getDefaultSlideTemplate() {
    return <<<'EOT'
High-end professional cinematic photograph for Instagram carousel slide {{SLIDE_NUM}} of {{TOTAL_SLIDES}}.
Topic: {{TEMA}}.
Content to visualize: {{SLIDE_TEXT}}.
Scene and subject: Female dentist Dra. {{DRA}} in modern, bright, premium dental clinic with clean Scandinavian architectural design.
Natural, authentic posture and spontaneous body language. Warm, empathetic expression with direct eye contact to camera lens or natural professional focus.
High-end editorial aesthetic, soft balanced lighting, ample clean negative space. Colors: Relax Blue (#16345F), Sky Pulse (#7DD3FC), pristine clinical whites. Highly detailed, photorealistic 4k.
EOT;
}

function getMasterTemplate($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (trim($content) !== '') return $content;
    }
    $default = getDefaultMasterTemplate();
    file_put_contents($file, $default);
    return $default;
}

function getSlideTemplate($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (trim($content) !== '') return $content;
    }
    $default = getDefaultSlideTemplate();
    file_put_contents($file, $default);
    return $default;
}

function renderTemplate($template, $variables) {
    foreach ($variables as $key => $val) {
        if (is_array($val)) continue;
        $strVal = (string)$val;
        $template = str_replace('{{' . $key . '}}', $strVal, $template);
        $template = str_replace('{{' . strtolower($key) . '}}', $strVal, $template);
        $template = str_replace('{{' . strtoupper($key) . '}}', $strVal, $template);
    }
    return $template;
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
        'sheet_url' => 'https://docs.google.com/spreadsheets/d/1xvNd-tg2gXnMkXh8T4W8ibbyZE1I29gMjrG3jYwiPDo/export?format=csv&gid=1841829525',
        'generation_mode' => 'edits'
    ];
}

function saveConfig($configFile, $data) {
    file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function parseCsvRows($csvContent) {
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $csvContent);
    rewind($stream);

    $headers = fgetcsv($stream);
    if (!$headers) {
        fclose($stream);
        return [];
    }
    $headers = array_map('trim', $headers);

    $rows = [];
    while (($r = fgetcsv($stream)) !== false) {
        if (empty(array_filter($r))) continue;
        if (count($r) < count($headers)) {
            $r = array_pad($r, count($headers), '');
        }
        $row = array_combine($headers, array_slice($r, 0, count($headers)));
        $rows[] = $row;
    }
    fclose($stream);
    return $rows;
}

$action = $_GET['action'] ?? '';

// --------------------------------------------------------------------------
// 1. OBTENER CONFIGURACIÓN
// --------------------------------------------------------------------------
if ($action === 'get_config') {
    $cfg = getConfig($configFile);
    $key = $cfg['openai_api_key'] ?? '';
    $maskedKey = (strlen($key) > 12) ? substr($key, 0, 7) . '...' . substr($key, -4) : $key;
    echo json_encode([
        'status' => 'ok',
        'openai_api_key' => $key,
        'masked_key' => $maskedKey,
        'selected_model' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst',
        'sheet_url' => $cfg['sheet_url'] ?? '',
        'generation_mode' => $cfg['generation_mode'] ?? 'edits'
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 2. GUARDAR CONFIGURACIÓN
// --------------------------------------------------------------------------
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
    if (isset($input['generation_mode'])) $cfg['generation_mode'] = trim($input['generation_mode']);
    saveConfig($configFile, $cfg);
    echo json_encode(['status' => 'ok', 'message' => 'Configuración guardada exitosamente']);
    exit;
}

// --------------------------------------------------------------------------
// 3. GESTIÓN DE PLANTILLAS DE PROMPT (NO HARDCODED)
// --------------------------------------------------------------------------
if ($action === 'get_templates') {
    $masterTpl = getMasterTemplate($masterTemplateFile);
    $slideTpl = getSlideTemplate($slideTemplateFile);
    echo json_encode([
        'status' => 'ok',
        'master_template' => $masterTpl,
        'slide_template' => $slideTpl,
        'files' => [
            'master' => 'data/prompt_master_template.txt',
            'slide' => 'data/slide_prompt_template.txt'
        ]
    ]);
    exit;
}

if ($action === 'save_templates' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Payload JSON inválido']);
        exit;
    }
    if (isset($input['master_template'])) {
        file_put_contents($masterTemplateFile, $input['master_template']);
    }
    if (isset($input['slide_template'])) {
        file_put_contents($slideTemplateFile, $input['slide_template']);
    }
    echo json_encode(['status' => 'ok', 'message' => 'Plantillas guardadas en archivos de texto exitosamente']);
    exit;
}

if ($action === 'reset_templates' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents($masterTemplateFile, getDefaultMasterTemplate());
    file_put_contents($slideTemplateFile, getDefaultSlideTemplate());
    echo json_encode([
        'status' => 'ok',
        'message' => 'Plantillas restauradas a sus valores por defecto',
        'master_template' => getDefaultMasterTemplate(),
        'slide_template' => getDefaultSlideTemplate()
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 4. CONSULTAR MODELOS DE OPENAI EN VIVO
// --------------------------------------------------------------------------
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

// --------------------------------------------------------------------------
// 5. OBTENER FILAS DEL GOOGLE SHEET
// --------------------------------------------------------------------------
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

    $rawRows = parseCsvRows($csvContent);
    if (empty($rawRows)) {
        echo json_encode(['status' => 'error', 'message' => 'Archivo CSV vacío o sin filas válidas']);
        exit;
    }

    $headers = array_keys($rawRows[0]);
    $rows = [];
    foreach ($rawRows as $row) {
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

// --------------------------------------------------------------------------
// 6. GENERAR PROMPT PARA UNA FILA (APLICANDO LA PLANTILLA EXTERNA)
// --------------------------------------------------------------------------
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

    $rawRows = parseCsvRows($csvContent);
    $targetRow = null;
    foreach ($rawRows as $row) {
        if (trim($row['N°'] ?? '') === $nTarget) {
            $targetRow = $row;
            break;
        }
    }

    if (!$targetRow) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la fila N° ' . $nTarget]);
        exit;
    }

    $guion = $targetRow['GUION'] ?? '';
    $slideCount = preg_match_all('/(?:^\s*\d+\.|\bSlide\s+\d+)/mi', $guion, $matches);
    if ($slideCount === 0 && !empty($guion)) {
        $slideCount = count(array_filter(explode("\n", $guion)));
    }
    $slideCount = $slideCount ?: 1;

    $linkFotos = $targetRow['Link a carpeta de fotos'] ?? ($targetRow['Elegir una foto dentro de este link'] ?? '');
    $linkLogo = $targetRow['Link a carpeta donde elegir el logo'] ?? '';
    $linkManual = $targetRow['Link a manual de marca'] ?? '';

    // Mapeo completo de variables para el template
    $vars = [
        'N' => $targetRow['N°'] ?? '',
        'TEMA' => $targetRow['TEMA'] ?? '',
        'TITULO' => $targetRow['TÍTULO / PORTADA'] ?? '',
        'GUION' => $guion,
        'SUBTITULOS' => $targetRow['SUBTÍTULOS / PALABRAS CLAVE'] ?? '',
        'DRA' => !empty($targetRow['DRA']) ? $targetRow['DRA'] : 'Jovany',
        'LINK_FOTOS' => $linkFotos,
        'LINK_LOGOS' => $linkLogo,
        'LINK_MANUAL' => $linkManual,
        'ESTADO' => $targetRow['ESTADO'] ?? '',
        'CANTIDAD_SLIDES' => $slideCount,
        'SHEET_URL' => $sheetUrl
    ];

    // Permitir cualquier otra columna del Sheet como variable dinámica
    foreach ($targetRow as $k => $v) {
        $safeKey = preg_replace('/[^A-Za-z0-9_]/', '_', strtoupper($k));
        if (!isset($vars[$safeKey])) {
            $vars[$safeKey] = $v;
        }
    }

    // Leemos la plantilla desde el archivo data/prompt_master_template.txt
    $masterTemplate = getMasterTemplate($masterTemplateFile);
    $prompt = renderTemplate($masterTemplate, $vars);

    echo json_encode([
        'status' => 'ok',
        'row' => $targetRow,
        'vars' => $vars,
        'template_file' => 'data/prompt_master_template.txt',
        'prompt' => $prompt
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 7. ENVIAR A PRODUCIR (LLAMADO AL API DE OPENAI USANDO LA PLANTILLA DE SLIDES)
// --------------------------------------------------------------------------
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

    // 1. Extraer los slides directamente del prompt que el usuario editó en pantalla
    $guionBlock = '';
    if (preg_match('/GUION COMPLETO SLIDE POR SLIDE:\s*\n(.*?)(?=\n[A-ZÁÉÍÓÚÑ\s]{4,}:|\z)/si', $editedPrompt, $gm)) {
        $guionBlock = $gm[1];
    } else {
        $guionBlock = $editedPrompt;
    }

    $slides = [];
    $lines = explode("\n", $guionBlock);
    foreach ($lines as $line) {
        $l = trim($line);
        if (!$l) continue;
        if (preg_match('/^(\d+)[\.\:\-\)]\s*(.*)$/', $l, $m)) {
            $slides[] = [
                'num' => (int)$m[1],
                'text' => trim($m[2], " \t\n\r\0\x0B\"'")
            ];
        }
    }

    // Fallback: si no se detectó numeración en el prompt editado, buscar en rowData
    if (empty($slides)) {
        $rawGuion = $rowData['GUION'] ?? '';
        $rawLines = explode("\n", $rawGuion);
        foreach ($rawLines as $line) {
            $l = trim($line);
            if (!$l) continue;
            if (preg_match('/^(\d+)[\.\:\-\)]\s*(.*)$/', $l, $m)) {
                $slides[] = [
                    'num' => (int)$m[1],
                    'text' => trim($m[2], " \t\n\r\0\x0B\"'")
                ];
            }
        }
    }
    if (empty($slides)) {
        $slides[] = ['num' => 1, 'text' => $rowData['TÍTULO / PORTADA'] ?? 'Portada'];
    }

    // 2. Extraer directivas de arte, fotografía y estilo del prompt editado por el usuario
    $artDirection = $editedPrompt;
    if (!empty($guionBlock)) {
        $artDirection = str_replace("GUION COMPLETO SLIDE POR SLIDE:\n" . $guionBlock, '', $artDirection);
        $artDirection = str_replace($guionBlock, '', $artDirection);
    }
    $artDirection = trim(preg_replace('/\n{3,}/', "\n\n", $artDirection));

    $fallbackSlideTemplate = getSlideTemplate($slideTemplateFile);

    $totalSlides = count($slides);
    $results = [];
    $errors = [];
    $promptsSent = [];

    // Foto madre si existe en servidor para consistencia facial
    $fotoMadrePath = $baseDir . '/carrusel_fila2/foto_madre_1024.png';
    if (!file_exists($fotoMadrePath)) {
        $fotoMadrePath = $baseDir . '/carrusel_fila4/foto_madre_1024.png';
    }

    foreach ($slides as $idx => $s) {
        $slideNum = $idx + 1;
        $slideFilename = sprintf('slide_%02d.png', $slideNum);

        // Construir el prompt específico usando TODO el texto y modificaciones que el usuario tiene en pantalla
        if (!empty($artDirection)) {
            $slidePrompt = "Professional photograph for Instagram Carousel - Slide {$slideNum} of {$totalSlides}:\n"
                . "SLIDE CONTENT: {$s['text']}\n\n"
                . "ART DIRECTION AND VISUAL GUIDELINES (apply strictly from user):\n"
                . $artDirection;
        } else {
            $slideVars = [
                'SLIDE_NUM' => $slideNum,
                'TOTAL_SLIDES' => $totalSlides,
                'SLIDE_TEXT' => $s['text'],
                'DRA' => !empty($rowData['DRA']) ? $rowData['DRA'] : 'Jovany',
                'TEMA' => $rowData['TEMA'] ?? '',
                'TITULO' => $rowData['TÍTULO / PORTADA'] ?? '',
                'SUBTITULOS' => $rowData['SUBTÍTULOS / PALABRAS CLAVE'] ?? ''
            ];
            $slidePrompt = renderTemplate($fallbackSlideTemplate, $slideVars);
        }

        // Guardar el prompt exacto enviado para este slide
        file_put_contents($targetDir . "/slide_{$slideNum}_prompt.txt", $slidePrompt);
        $promptsSent["slide_{$slideNum}"] = $slidePrompt;

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
        'prompts_sent' => $promptsSent,
        'errors' => $errors
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 8. HISTORIAL DE PRODUCCIONES
// --------------------------------------------------------------------------
if ($action === 'get_history') {
    $history = [];
    if (is_dir($outputDir)) {
        $dirs = scandir($outputDir, SCANDIR_SORT_DESCENDING);
        foreach ($dirs as $d) {
            if ($d === '.' || $d === '..' || !is_dir($outputDir . '/' . $d)) continue;
            if (strpos($d, 'carrusel_') === 0) {
                $targetPath = $outputDir . '/' . $d;
                $files = glob($targetPath . '/slide_*.png');
                if (!empty($files)) {
                    sort($files);
                    $slideUrls = array_map(function($f) use ($d) {
                        return 'output/' . $d . '/' . basename($f);
                    }, $files);

                    $metaFile = $targetPath . '/row_data.json';
                    $rowData = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
                    $promptFile = $targetPath . '/prompt_utilizado.txt';
                    $promptUsed = file_exists($promptFile) ? file_get_contents($promptFile) : '';
                    $mtime = filemtime($targetPath);

                    $history[] = [
                        'folder' => $d,
                        'total_slides' => count($slideUrls),
                        'thumbnail' => $slideUrls[0] ?? '',
                        'slides' => $slideUrls,
                        'row_data' => $rowData,
                        'prompt_used' => $promptUsed,
                        'created_at' => date('d/m/Y H:i', $mtime),
                        'timestamp' => $mtime
                    ];
                }
            }
        }
    }
    echo json_encode(['status' => 'ok', 'history' => $history]);
    exit;
}

// --------------------------------------------------------------------------
// 9. DESCARGAR TODOS LOS SLIDES EN ARCHIVO ZIP
// --------------------------------------------------------------------------
if ($action === 'download_zip') {
    $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['folder'] ?? '');
    $targetPath = $outputDir . '/' . $folder;
    if (!$folder || !is_dir($targetPath)) {
        http_response_code(404);
        echo 'Carpeta no encontrada';
        exit;
    }

    $files = glob($targetPath . '/slide_*.png');
    if (empty($files)) {
        http_response_code(404);
        echo 'No hay slides en este carrusel';
        exit;
    }

    $zipPath = $targetPath . '/carrusel_' . $folder . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($files as $f) {
            $zip->addFile($f, basename($f));
        }
        if (file_exists($targetPath . '/prompt_utilizado.txt')) {
            $zip->addFile($targetPath . '/prompt_utilizado.txt', 'prompt_utilizado.txt');
        }
        $zip->close();
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="carrusel_' . $folder . '.zip"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
