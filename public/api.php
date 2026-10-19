<?php
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
$uploadsDir = $baseDir . '/data/uploads';
$tempRefsDir = $baseDir . '/data/temp_refs';
$masterTemplateFile = $baseDir . '/data/prompt_master_template.txt';
$slideTemplateFile = $baseDir . '/data/slide_prompt_template.txt';

foreach ([$outputDir, $baseDir . '/data', $uploadsDir, $tempRefsDir] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Constantes por defecto del Sheet de JM Odontología
define('DEFAULT_SHEET_URL', 'https://docs.google.com/spreadsheets/d/1xvNd-tg2gXnMkXh8T4W8ibbyZE1I29gMjrG3jYwiPDo/export?format=csv&gid=1841829525');
define('DEFAULT_LOGO_FOLDER_ID', '1Nb7c7NW_vd8zXN1V1jjb1zNDO0wn0jxJ');
define('DEFAULT_BRAND_MANUAL_ID', '1j-b3F4hr_LCcl9z1Tgzmliz5eE9ArPbZ');

// --------------------------------------------------------------------------
// PLANTILLAS POR DEFECTO (MODULARES EN ARCHIVOS .TXT)
// --------------------------------------------------------------------------
function getDefaultMasterTemplate() {
    return <<<'EOT'
ACTUÁ COMO DIRECTOR/A DE ARTE, DISEÑADOR/A GRÁFICO/A PROFESIONAL Y PRODUCTOR/A DE CARRUSELES PARA INSTAGRAM DE JM ODONTOLOGÍA INTEGRAL.

DATOS DEL CONTENIDO ACTUAL:
- FILA N°: {{N}}
- TEMA: {{TEMA}}
- TÍTULO / PORTADA: {{TITULO}}
- DOCTORA PROTAGONISTA: Dra. {{DRA}}
- SUBTÍTULOS / PALABRAS CLAVE: {{SUBTITULOS}}
- TOTAL DE SLIDES EN SECUENCIA: {{CANTIDAD_SLIDES}}

GUION COMPLETO SLIDE POR SLIDE:
{{GUION}}

REGLAS DE DIRECCIÓN DE ARTE Y CONTINUIDAD DE SECUENCIA:
1. Cada slide es una pieza editorial vertical 4:5 (1080 × 1350 px) INDIVIDUAL y COMPLETA, nunca un collage ni una grilla de múltiples cuadros.
2. CERO REPETICIÓN / ANTI-SESGO DE FOTO DE REFERENCIA:
   - Si se adjunta foto de referencia de la Dra. {{DRA}}, se debe tomar ÚNICAMENTE su rostro, fisonomía, cabello y rasgos reales para mantener la identidad de la protagonista.
   - PROHIBIDO copiar la misma pose estática, encuadre o fondo de la foto de referencia en todos los slides. Cada slide debe tener una puesta en escena, postura corporal y plano cinematográfico nuevo y orgánico.
3. VARIACIÓN CINEMATOGRÁFICA ENTRE SLIDES:
   - Slide 1 (Portada): Plano medio corto o plano medio luminoso en consultorio moderno escandinavo, contacto visual cálido y empático con cámara, titular editorial de alto impacto y señalizador sutil «Deslizá →».
   - Slides Intermedios (Desarrollo): Alternar planos dinámicos (plano 3/4 conversando espontáneamente, plano sobre el hombro mostrando un elemento clínico/pantalla/modelo dental, plano detalle o macro luminoso de estética/higiene dental, plano medio en recepción o escritorio moderno).
   - Último Slide (Cierre / CTA): Plano cercano cálido y confiable de la Dra. {{DRA}} invitando a la acción (comentarios / consulta por WhatsApp).
4. IDENTIDAD VISUAL Y MARCA (JM ODONTOLOGÍA INTEGRAL):
   - Paleta Oficial: Relax Blue (#16345F), Sky Pulse (#7DD3FC), Core Black (#1D1D1B), Grey Calm (#F2F2F2), White (#FFFFFF).
   - Tipografía editorial sans-serif moderna, perfectamente legible en español, integrada con alto contraste sobre áreas limpias de la composición.
   - Integrar el logotipo oficial seleccionado en una esquina con área de respiro limpia.
EOT;
}

function getDefaultSlideTemplate() {
    return <<<'EOT'
Pieza editorial individual para carrusel de Instagram (Formato vertical 4:5, 1080x1350 px) — Slide {{SLIDE_NUM}} de {{TOTAL_SLIDES}} ({{SLIDE_ROLE}}).
Marca: JM Odontología Integral. Tema: {{TEMA}}.

TEXTO EDITORIAL A RENDERIZAR EN ESTE SLIDE (exacto, en español, tipografía sans-serif moderna, nítida y de alto contraste):
"{{SLIDE_TEXT}}"
{{NAV_INDICATOR}}

PUESTA EN ESCENA Y DIRECCIÓN DE FOTOGRAFÍA PARA ESTE SLIDE:
- Plano y encuadre asignado: {{SHOT_TYPE}}
- Protagonista e Identidad: {{DOCTOR_INSTRUCTION}}
- Ambiente: Consultorio odontológico premium de diseño escandinavo moderno, luminoso, limpio y cálido, con profundidad de campo cinematográfica real (fotografía hiperrealista 8k, iluminación suave natural).
- Paleta cromática de marca: Azul institucional Relax Blue (#16345F), acentos Sky Pulse (#7DD3FC), blanco clínico (#FFFFFF) y gris suave (#F2F2F2).
- Marca y Logo: {{LOGO_INSTRUCTION}}
- Restricción estricta: Generar UNA SOLA escena continua a pantalla completa para este slide individual (prohibido dividir en grillas, collages o múltiples recuadros).
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
        if (is_array($val) || is_object($val)) continue;
        $strVal = (string)$val;
        $template = str_replace('{{' . $key . '}}', $strVal, $template);
        $template = str_replace('{{' . strtolower($key) . '}}', $strVal, $template);
        $template = str_replace('{{' . strtoupper($key) . '}}', $strVal, $template);
    }
    return $template;
}

function getConfig($configFile) {
    $defaults = [
        'openai_api_key' => '',
        'selected_model' => 'gpt-image-2.5-sunburst',
        'director_model' => 'gpt-4o',
        'sheet_url' => DEFAULT_SHEET_URL,
        'google_drive_api_key' => 'AIzaSyDwuvpt_Dhrf8uZgF-DHPQPXn_Mr4piVMo',
        'stamp_logo_overlay' => false
    ];
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        $json = json_decode($content, true);
        if (is_array($json)) {
            return array_merge($defaults, $json);
        }
    }
    return $defaults;
}

function saveConfig($configFile, $data) {
    file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// --------------------------------------------------------------------------
// UTILIDADES DE GOOGLE DRIVE Y NORMALIZACIÓN DE IMÁGENES (CON FIX EXIF)
// --------------------------------------------------------------------------
function extractDriveId($url) {
    if (!$url) return '';
    $url = trim($url);
    if (preg_match('/folders\/([a-zA-Z0-9_-]+)/', $url, $m)) return $m[1];
    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $m)) return $m[1];
    if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $m)) return $m[1];
    if (preg_match('/^[a-zA-Z0-9_-]{20,}$/', $url)) return $url;
    return '';
}

function extractDoctorAndUrl($rawCell, $explicitDra = '') {
    $rawCell = trim((string)$rawCell);
    $dra = trim((string)$explicitDra);
    $url = $rawCell;

    // En filas 6..31 el Sheet tiene formato "Melina: https://drive.google.com/..."
    if (preg_match('/^([^:]+):\s*(https?:\/\/.+)$/i', $rawCell, $m)) {
        if ($dra === '') {
            $dra = trim($m[1]);
        }
        $url = trim($m[2]);
    }
    return [
        'dra' => $dra ?: 'Jovany',
        'url' => $url,
        'folder_id' => extractDriveId($url)
    ];
}

function fetchDriveFilesList($folderId, $apiKey, $onlyImages = true) {
    if (!$folderId || !$apiKey) return [];
    $query = "'" . $folderId . "' in parents and trashed = false";
    if ($onlyImages) {
        $query .= " and mimeType contains 'image/'";
    }
    $url = 'https://www.googleapis.com/drive/v3/files?q=' . urlencode($query)
        . '&fields=' . urlencode('files(id,name,mimeType,thumbnailLink,webContentLink,size)')
        . '&orderBy=name&pageSize=100&key=' . urlencode($apiKey);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0']);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$res) return [];
    $json = json_decode($res, true);
    $files = $json['files'] ?? [];

    // Filtrar solo imágenes reales
    $out = [];
    foreach ($files as $f) {
        $mime = strtolower($f['mimeType'] ?? '');
        if ($onlyImages && strpos($mime, 'image/') !== 0) continue;
        $out[] = [
            'id' => $f['id'],
            'name' => $f['name'],
            'mimeType' => $f['mimeType'] ?? 'image/jpeg',
            'size' => (int)($f['size'] ?? 0),
            'thumbnail' => 'api.php?action=proxy_drive_image&id=' . urlencode($f['id']) . '&sz=w400',
            'highres_preview' => 'api.php?action=proxy_drive_image&id=' . urlencode($f['id']) . '&sz=w1200',
            'source' => 'drive'
        ];
    }
    return $out;
}

function getDriveFileMetadata($fileId, $apiKey) {
    if (!$fileId || !$apiKey) return null;
    $url = 'https://www.googleapis.com/drive/v3/files/' . urlencode($fileId)
        . '?fields=' . urlencode('id,name,mimeType,thumbnailLink,size')
        . '&key=' . urlencode($apiKey);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || !$res) return null;
    return json_decode($res, true);
}

/**
 * Descarga una imagen de Google Drive de forma rápida y confiable.
 * Usa primero el endpoint de alta resolución de Drive (w1600) que ya viene con rotación EXIF aplicada
 * y pesa ~250KB en lugar de 8MB, y si falla usa alt=media.
 */
function downloadDriveImageFresh($fileId, $targetPath, $apiKey, $preferThumbnailSize = 'w1600') {
    if (!$fileId) return false;

    // 1. Intentar desde el endpoint thumbnail de alta resolución de Google Drive (rápido y con EXIF ya derecho)
    if ($preferThumbnailSize) {
        $thumbUrl = 'https://drive.google.com/thumbnail?id=' . urlencode($fileId) . '&sz=' . urlencode($preferThumbnailSize);
        $ch = curl_init($thumbUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0']);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode === 200 && $data && strlen($data) > 1024 && stripos((string)$contentType, 'image/') !== false) {
            file_put_contents($targetPath, $data);
            return true;
        }
    }

    // 2. Fallback al archivo binario directo vía Drive API v3
    if ($apiKey) {
        $url = 'https://www.googleapis.com/drive/v3/files/' . urlencode($fileId) . '?alt=media&key=' . urlencode($apiKey);
        $fp = fopen($targetPath, 'w+');
        if ($fp) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0']);
            $success = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ($success && $httpCode === 200 && file_exists($targetPath) && filesize($targetPath) > 512) {
                return true;
            }
            @unlink($targetPath);
        }
    }
    return false;
}

/**
 * Carga una imagen corrigiendo automáticamente su orientación EXIF (para cámaras Sony/iPhone)
 * y la exporta como PNG limpio sin recortar agresivamente la escena ni deformarla.
 */
function normalizeReferenceImage($sourcePath, $targetPngPath, $maxDim = 1536) {
    if (!file_exists($sourcePath)) return false;
    $info = @getimagesize($sourcePath);
    if (!$info) return false;

    $mime = $info['mime'] ?? '';
    if ($mime === 'image/jpeg') {
        $src = @imagecreatefromjpeg($sourcePath);
        // Corregir orientación EXIF si está rotada (ej. Sony ILCE-7M5 Orientation=6)
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
    } else {
        return false;
    }

    if (!$src) return false;

    $w = imagesx($src);
    $h = imagesy($src);

    // Escalar manteniendo proporción real (sin recorte cuadrado forzado)
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
 * Normaliza un slide generado al formato oficial de Instagram 4:5 (1080 x 1350 px)
 * y opcionalmente estampa el logo oficial en una esquina con área de protección limpia.
 */
function finalizeSlideImage4x5($rawImagePath, $finalPath, $logoPath = null) {
    if (!file_exists($rawImagePath)) return false;
    $info = @getimagesize($rawImagePath);
    if (!$info) return false;

    $mime = $info['mime'] ?? '';
    if ($mime === 'image/png') $src = @imagecreatefrompng($rawImagePath);
    elseif ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($rawImagePath);
    elseif ($mime === 'image/webp') $src = @imagecreatefromwebp($rawImagePath);
    else return false;

    if (!$src) return false;

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $targetW = 1080;
    $targetH = 1350; // 4:5

    $dst = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($dst, true);
    imagesavealpha($dst, true);

    // Calcular crop centrado suave para llevar a proporción 4:5 (0.8)
    $targetRatio = $targetW / $targetH; // 0.8
    $srcRatio = $srcW / $srcH;

    if (abs($srcRatio - $targetRatio) < 0.02) {
        $cropX = 0;
        $cropY = 0;
        $cropW = $srcW;
        $cropH = $srcH;
    } elseif ($srcRatio > $targetRatio) {
        // Imagen más ancha que 4:5 (ej. 1:1) -> si es 1:1, para no cortar texto en bordes,
        // hacemos un cover suave o adaptamos
        $cropH = $srcH;
        $cropW = (int)round($srcH * $targetRatio);
        $cropX = (int)round(($srcW - $cropW) / 2);
        $cropY = 0;
    } else {
        // Imagen más alta que 4:5 (ej. 1024x1536 = 2:3 = 0.666)
        $cropW = $srcW;
        $cropH = (int)round($srcW / $targetRatio);
        $cropX = 0;
        $cropY = (int)round(($srcH - $cropH) / 2);
    }

    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
    imagedestroy($src);

    // Si el usuario activó estampado programático de logo además de IA
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

// --------------------------------------------------------------------------
// PARSER DE CSV Y DE GUION MULTILÍNEA (SIN PÉRDIDA DE RENGLONES)
// --------------------------------------------------------------------------
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
        if (empty(array_filter($r, function($v) { return trim((string)$v) !== ''; }))) continue;
        if (count($r) < count($headers)) {
            $r = array_pad($r, count($headers), '');
        }
        $row = array_combine($headers, array_slice($r, 0, count($headers)));
        $rows[] = $row;
    }
    fclose($stream);
    return $rows;
}

/**
 * Parsea un bloque de GUION extrayendo todos los slides (1..N) y conservando
 * múltiples renglones dentro de cada slide.
 */
function parseGuionSlides($guionText, $fallbackTitle = 'Portada') {
    $guionText = trim((string)$guionText);
    if ($guionText === '') {
        return [
            ['num' => 1, 'role' => 'Portada', 'text' => $fallbackTitle]
        ];
    }

    $lines = preg_split('/\r\n|\r|\n/', $guionText);
    $slides = [];
    $currentSlide = null;

    foreach ($lines as $rawLine) {
        $line = trim($rawLine);
        if ($line === '') continue;

        // Detectar inicio de slide: "1. ...", "Slide 1: ...", "SLIDE 2 - ...", "1) ..."
        if (preg_match('/^(?:slide\s*)?(\d+)[\.\:\-\)\s]+(.*)$/i', $line, $m)) {
            if ($currentSlide !== null) {
                $slides[] = $currentSlide;
            }
            $num = (int)$m[1];
            $rest = trim($m[2], " \t\n\r\0\x0B\"'");
            $currentSlide = [
                'num' => $num,
                'text' => $rest
            ];
        } else {
            // Línea de continuación dentro del mismo slide
            $cleanLine = trim($line, " \t\n\r\0\x0B\"'");
            if ($currentSlide !== null) {
                $currentSlide['text'] .= ($currentSlide['text'] !== '' ? "\n" : '') . $cleanLine;
            } else {
                $currentSlide = [
                    'num' => count($slides) + 1,
                    'text' => $cleanLine
                ];
            }
        }
    }

    if ($currentSlide !== null) {
        $slides[] = $currentSlide;
    }

    if (empty($slides)) {
        $slides[] = ['num' => 1, 'text' => $fallbackTitle];
    }

    // Asignar roles y normalizar numeración 1..N
    $total = count($slides);
    foreach ($slides as $idx => &$s) {
        $s['num'] = $idx + 1;
        // Limpiar prefijos redundantes como "Portada:" o "Cierre:" manteniendo el sentido
        $text = $s['text'];
        if ($idx === 0) {
            $s['role'] = 'Portada';
            $text = preg_replace('/^portada\s*[\:\-]\s*/i', '', $text);
        } elseif ($idx === $total - 1 && $total > 1) {
            $s['role'] = 'Cierre / CTA';
            $text = preg_replace('/^cierre\s*[\:\-]\s*/i', '', $text);
        } else {
            $s['role'] = 'Desarrollo';
        }
        $s['text'] = trim($text, " \t\n\r\0\x0B\"'");
    }
    unset($s);

    return $slides;
}

/**
 * Construye una escaleta cinematográfica variada para los N slides
 * evitando que todos los slides tengan la misma pose o encuadre.
 */
function buildVariedSlidePlan($slides, $meta, $slideTemplate) {
    $total = count($slides);
    $dra = $meta['dra'] ?: 'Jovany';
    $tema = $meta['tema'] ?: 'Salud Bucal';
    $titulo = $meta['titulo'] ?: '';
    $subtitulos = $meta['subtitulos'] ?: '';

    // Repertorio de planos cinematográficos distintos para alternar en el carrusel
    $middleShots = [
        "Plano 3/4 dinámico: la Dra. {$dra} conversando con expresión empática y cercana al borde del sillón odontológico moderno, gesticulando con naturalidad, iluminación cálida lateral.",
        "Plano sobre el hombro / demostrativo: la Dra. {$dra} en el consultorio mostrando un modelo dental anatómico o tablet clínica con sonrisa profesional, desenfoque suave de fondo escandinavo.",
        "Plano detalle / clínico editorial: composición limpia y luminosa de instrumental odontológico moderno o elemento de cuidado bucal sobre mesada blanca minimalista, con la Dra. {$dra} suavemente desenfocada en segundo plano.",
        "Plano medio lateral en área de escritorio moderno del consultorio: la Dra. {$dra} tomando notas o explicando un diagnóstico con calidez y serenidad, luz natural de ventanal.",
        "Primer plano medio cálido: la Dra. {$dra} mirando a cámara con gesto tranquilizador y profesional, ambiente clínico impecable con acentos en azul Relax Blue (#16345F) y Sky Pulse (#7DD3FC)."
    ];

    $plan = [];
    foreach ($slides as $idx => $s) {
        $num = $idx + 1;
        $role = $s['role'] ?? ($num === 1 ? 'Portada' : ($num === $total ? 'Cierre / CTA' : 'Desarrollo'));

        if ($num === 1) {
            $shotType = "Plano medio de Portada Editorial: la Dra. {$dra} en el consultorio moderno e iluminado, postura segura, cálida y acogedora mirando directamente a la lente, con amplio espacio limpio superior/inferior para el titular de portada.";
            $navIndicator = "Incluir indicador sutil de navegación en esquina inferior: «Deslizá →».";
            $includeDoctor = true;
        } elseif ($num === $total && $total > 1) {
            $shotType = "Plano medio corto de Cierre (Call to Action): la Dra. {$dra} sonriendo con calidez y confianza mirando a cámara en recepción/consultorio luminoso, transmitiendo cercanía e invitando a comentar o consultar.";
            $navIndicator = "Diseño de cierre limpio con llamado a la acción claro.";
            $includeDoctor = true;
        } else {
            $shotType = $middleShots[($idx - 1) % count($middleShots)];
            $navIndicator = "Incluir indicador discreto de secuencia «{$num}/{$total}» y flecha sutil «→».";
            $includeDoctor = true;
        }

        $doctorInstruction = "Usar la fotografía adjunta de la Dra. {$dra} EXCLUSIVAMENTE como referencia de identidad facial (mantener sus rasgos faciales reales, ojos, sonrisa y cabello). OBLIGATORIO: Generar una POSE CORPORAL, ÁNGULO DE CÁMARA y ESCENA 100% NUEVA acorde al plano '{$shotType}'. NO copiar la pose estática ni el fondo de la foto de referencia.";
        $logoInstruction = "Integrar el logotipo oficial de JM Odontología Integral de forma limpia y armónica en una esquina respetando su área de protección.";

        $vars = [
            'SLIDE_NUM' => $num,
            'TOTAL_SLIDES' => $total,
            'SLIDE_ROLE' => $role,
            'SLIDE_TEXT' => $s['text'],
            'SHOT_TYPE' => $shotType,
            'NAV_INDICATOR' => $navIndicator,
            'DOCTOR_INSTRUCTION' => $doctorInstruction,
            'LOGO_INSTRUCTION' => $logoInstruction,
            'DRA' => $dra,
            'TEMA' => $tema,
            'TITULO' => $titulo,
            'SUBTITULOS' => $subtitulos
        ];

        $prompt = renderTemplate($slideTemplate, $vars);

        $plan[] = [
            'num' => $num,
            'role' => $role,
            'text' => $s['text'],
            'shot_type' => $shotType,
            'include_doctor' => $includeDoctor,
            'assigned_photo_index' => -1, // -1 = rotar automáticamente entre las fotos seleccionadas por el usuario
            'prompt' => $prompt
        ];
    }

    return $plan;
}

/**
 * Descarga y parsea el Google Sheet completo, devolviendo tanto las filas enriquecidas
 * como el catálogo de todas las carpetas de Drive de doctoras encontradas.
 */
function fetchSheetDataAndCatalog($sheetUrl) {
    $ch = curl_init($sheetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0']);
    $csvContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$csvContent) {
        return ['error' => 'No se pudo descargar el Google Sheet (HTTP ' . $httpCode . ')'];
    }

    $rawRows = parseCsvRows($csvContent);
    if (empty($rawRows)) {
        return ['error' => 'El Google Sheet está vacío o no tiene filas válidas'];
    }

    $doctorCatalog = [];
    $logoFolderId = DEFAULT_LOGO_FOLDER_ID;
    $brandManualId = DEFAULT_BRAND_MANUAL_ID;

    // Primera pasada: detectar carpetas globales de logo, manual y doctoras
    foreach ($rawRows as $r) {
        $rawFotoCell = $r['Link a carpeta de fotos'] ?? ($r['Elegir una foto dentro de este link'] ?? '');
        $rawLogoCell = $r['Link a carpeta donde elegir el logo'] ?? '';
        $rawManualCell = $r['Link a manual de marca'] ?? '';
        $explicitDra = trim($r['DRA'] ?? '');

        $parsedDoc = extractDoctorAndUrl($rawFotoCell, $explicitDra);
        $docName = $parsedDoc['dra'];
        $docFolderId = $parsedDoc['folder_id'];

        // Caso especial Fila 4 del Sheet: pusieron la carpeta de Luciana en la columna de al lado
        $logoCandidateId = extractDriveId($rawLogoCell);
        if ($explicitDra !== '' && strcasecmp($explicitDra, 'Jovany') !== 0 && $logoCandidateId && $logoCandidateId !== DEFAULT_LOGO_FOLDER_ID) {
            $docFolderId = $logoCandidateId;
            $parsedDoc['url'] = $rawLogoCell;
            $parsedDoc['folder_id'] = $logoCandidateId;
        } elseif ($logoCandidateId === DEFAULT_LOGO_FOLDER_ID) {
            $logoFolderId = $logoCandidateId;
        }

        $manualCandidateId = extractDriveId($rawManualCell);
        if ($manualCandidateId) {
            $brandManualId = $manualCandidateId;
        }

        if ($docFolderId) {
            $key = $docFolderId;
            if (!isset($doctorCatalog[$key])) {
                $doctorCatalog[$key] = [
                    'folder_id' => $docFolderId,
                    'dra' => $docName,
                    'label' => "Dra. {$docName} (" . substr($docFolderId, 0, 6) . "...)",
                    'url' => "https://drive.google.com/drive/folders/{$docFolderId}"
                ];
            }
        }
    }

    // Segunda pasada: construir filas normalizadas
    $rows = [];
    foreach ($rawRows as $r) {
        $n = trim($r['N°'] ?? '');
        $guion = $r['GUION'] ?? '';
        $titulo = $r['TÍTULO / PORTADA'] ?? '';
        $slides = parseGuionSlides($guion, $titulo);

        $rawFotoCell = $r['Link a carpeta de fotos'] ?? ($r['Elegir una foto dentro de este link'] ?? '');
        $rawLogoCell = $r['Link a carpeta donde elegir el logo'] ?? '';
        $rawManualCell = $r['Link a manual de marca'] ?? '';
        $explicitDra = trim($r['DRA'] ?? '');

        $parsedDoc = extractDoctorAndUrl($rawFotoCell, $explicitDra);
        $logoCandidateId = extractDriveId($rawLogoCell);
        if ($explicitDra !== '' && strcasecmp($explicitDra, 'Jovany') !== 0 && $logoCandidateId && $logoCandidateId !== DEFAULT_LOGO_FOLDER_ID) {
            $parsedDoc['folder_id'] = $logoCandidateId;
            $parsedDoc['url'] = $rawLogoCell;
        }

        $rows[] = [
            'n' => $n,
            'tema' => $r['TEMA'] ?? '',
            'titulo' => $titulo,
            'dra' => $parsedDoc['dra'],
            'guion' => $guion,
            'subtitulos' => $r['SUBTÍTULOS / PALABRAS CLAVE'] ?? '',
            'instagram_caption' => $r['INSTAGRAM'] ?? '',
            'estado' => $r['ESTADO'] ?? '',
            'slides_count' => count($slides),
            'slides_parsed' => $slides,
            'photo_folder_id' => $parsedDoc['folder_id'],
            'photo_folder_url' => $parsedDoc['url'],
            'logo_folder_id' => $logoFolderId,
            'brand_manual_id' => extractDriveId($rawManualCell) ?: $brandManualId,
            'full_data' => $r
        ];
    }

    return [
        'rows' => $rows,
        'headers' => array_keys($rawRows[0]),
        'doctor_catalog' => array_values($doctorCatalog),
        'default_logo_folder_id' => $logoFolderId,
        'default_brand_manual_id' => $brandManualId
    ];
}

$action = $_GET['action'] ?? '';

// --------------------------------------------------------------------------
// 0. PROXY DE MINIATURAS E IMÁGENES DE GOOGLE DRIVE (EVITA BLOQUEOS DE CORS/EXIF)
// --------------------------------------------------------------------------
if ($action === 'proxy_drive_image') {
    $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '');
    $sz = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['sz'] ?? 'w400');
    if (!$fileId) {
        http_response_code(400);
        exit;
    }

    $cfg = getConfig($configFile);
    $driveKey = $cfg['google_drive_api_key'] ?? '';

    $thumbFile = $tempRefsDir . "/thumb_{$fileId}_{$sz}.jpg";
    if (!file_exists($thumbFile) || (time() - filemtime($thumbFile)) > 3600) {
        downloadDriveImageFresh($fileId, $thumbFile, $driveKey, $sz);
    }

    if (file_exists($thumbFile) && filesize($thumbFile) > 0) {
        $info = @getimagesize($thumbFile);
        $mime = $info['mime'] ?? 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=3600');
        readfile($thumbFile);
        exit;
    }

    http_response_code(404);
    exit;
}

// --------------------------------------------------------------------------
// 1. OBTENER CONFIGURACIÓN
// --------------------------------------------------------------------------
if ($action === 'get_config') {
    $cfg = getConfig($configFile);
    $key = $cfg['openai_api_key'] ?? '';
    $maskedKey = (strlen($key) > 12) ? substr($key, 0, 7) . '...' . substr($key, -4) : $key;
    $driveKey = $cfg['google_drive_api_key'] ?? '';
    $maskedDriveKey = (strlen($driveKey) > 12) ? substr($driveKey, 0, 7) . '...' . substr($driveKey, -4) : $driveKey;
    echo json_encode([
        'status' => 'ok',
        'openai_api_key' => $key,
        'masked_key' => $maskedKey,
        'google_drive_api_key' => $driveKey,
        'masked_drive_key' => $maskedDriveKey,
        'selected_model' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst',
        'director_model' => $cfg['director_model'] ?? 'gpt-4o',
        'sheet_url' => $cfg['sheet_url'] ?? DEFAULT_SHEET_URL,
        'stamp_logo_overlay' => !empty($cfg['stamp_logo_overlay'])
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
    if (isset($input['google_drive_api_key'])) $cfg['google_drive_api_key'] = trim($input['google_drive_api_key']);
    if (isset($input['selected_model'])) $cfg['selected_model'] = trim($input['selected_model']);
    if (isset($input['director_model'])) $cfg['director_model'] = trim($input['director_model']);
    if (isset($input['sheet_url'])) $cfg['sheet_url'] = trim($input['sheet_url']);
    if (isset($input['stamp_logo_overlay'])) $cfg['stamp_logo_overlay'] = (bool)$input['stamp_logo_overlay'];
    saveConfig($configFile, $cfg);
    echo json_encode(['status' => 'ok', 'message' => 'Configuración guardada exitosamente']);
    exit;
}

// --------------------------------------------------------------------------
// 3. GESTIÓN DE PLANTILLAS DE PROMPT
// --------------------------------------------------------------------------
if ($action === 'get_templates') {
    echo json_encode([
        'status' => 'ok',
        'master_template' => getMasterTemplate($masterTemplateFile),
        'slide_template' => getSlideTemplate($slideTemplateFile),
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
    echo json_encode(['status' => 'ok', 'message' => 'Plantillas guardadas exitosamente']);
    exit;
}

if ($action === 'reset_templates' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents($masterTemplateFile, getDefaultMasterTemplate());
    file_put_contents($slideTemplateFile, getDefaultSlideTemplate());
    echo json_encode([
        'status' => 'ok',
        'message' => 'Plantillas restauradas a sus valores originales',
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
        echo json_encode(['status' => 'error', 'message' => 'API Key de OpenAI no configurada']);
        exit;
    }

    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'User-Agent: TecnoGenStudio/2.0'
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

    // Asegurar que gpt-image-2.5-sunburst y gpt-image-1 estén siempre visibles como opción rápida
    foreach (['gpt-image-2.5-sunburst', 'gpt-image-1'] as $preset) {
        if (!in_array($preset, $imageModels, true)) {
            array_unshift($imageModels, $preset);
        }
    }

    echo json_encode([
        'status' => 'ok',
        'image_models' => $imageModels,
        'all_models' => $modelsList,
        'current_selected' => $cfg['selected_model'] ?? 'gpt-image-2.5-sunburst'
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 5. OBTENER FILAS DEL GOOGLE SHEET Y CATÁLOGO DE CARPETAS DE DRIVE
// --------------------------------------------------------------------------
if ($action === 'get_sheet_rows') {
    $cfg = getConfig($configFile);
    $sheetUrl = $cfg['sheet_url'] ?: DEFAULT_SHEET_URL;
    $sheetData = fetchSheetDataAndCatalog($sheetUrl);

    if (isset($sheetData['error'])) {
        echo json_encode(['status' => 'error', 'message' => $sheetData['error']]);
        exit;
    }

    echo json_encode([
        'status' => 'ok',
        'total' => count($sheetData['rows']),
        'headers' => $sheetData['headers'],
        'rows' => $sheetData['rows'],
        'doctor_catalog' => $sheetData['doctor_catalog'],
        'default_logo_folder_id' => $sheetData['default_logo_folder_id'],
        'default_brand_manual_id' => $sheetData['default_brand_manual_id']
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 6. LISTAR IMÁGENES DE CUALQUIER CARPETA DE GOOGLE DRIVE EN VIVO
// --------------------------------------------------------------------------
if ($action === 'list_drive_folder') {
    $folderInput = trim($_GET['folder'] ?? '');
    $folderId = extractDriveId($folderInput);
    if (!$folderId) {
        echo json_encode(['status' => 'error', 'message' => 'ID o URL de carpeta de Google Drive inválido']);
        exit;
    }

    $cfg = getConfig($configFile);
    $driveKey = $cfg['google_drive_api_key'] ?? '';
    $files = fetchDriveFilesList($folderId, $driveKey, true);

    echo json_encode([
        'status' => 'ok',
        'folder_id' => $folderId,
        'count' => count($files),
        'files' => $files
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 7. SUBIR ACTIVOS PROPIOS DESDE LA COMPUTADORA (MODO CHATGPT / VERSIÓN A)
// --------------------------------------------------------------------------
if ($action === 'upload_asset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetType = preg_replace('/[^a-z_]/', '', $_POST['type'] ?? 'photo'); // photo | logo | manual
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'No se recibió un archivo válido']);
        exit;
    }

    $tmpName = $_FILES['file']['tmp_name'];
    $origName = basename($_FILES['file']['name']);
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $uniqueId = $assetType . '_' . time() . '_' . substr(md5($origName . mt_rand()), 0, 6);
    $destPng = $uploadsDir . '/' . $uniqueId . '.png';

    if (!normalizeReferenceImage($tmpName, $destPng, 1600)) {
        // Si no se pudo procesar con GD, copiar directo si es imagen
        $info = @getimagesize($tmpName);
        if (!$info) {
            echo json_encode(['status' => 'error', 'message' => 'El archivo subido no es una imagen válida (use JPG, PNG o WEBP)']);
            exit;
        }
        move_uploaded_file($tmpName, $destPng);
    }

    // Copiar también una miniatura pública accesible para previsualizar en UI
    $publicUploadsDir = __DIR__ . '/output/uploads';
    if (!is_dir($publicUploadsDir)) @mkdir($publicUploadsDir, 0775, true);
    $publicPath = $publicUploadsDir . '/' . $uniqueId . '.png';
    @copy($destPng, $publicPath);

    echo json_encode([
        'status' => 'ok',
        'asset' => [
            'id' => $uniqueId,
            'name' => $origName,
            'source' => 'upload',
            'type' => $assetType,
            'local_path' => 'data/uploads/' . $uniqueId . '.png',
            'thumbnail' => 'output/uploads/' . $uniqueId . '.png',
            'highres_preview' => 'output/uploads/' . $uniqueId . '.png'
        ]
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 8. CARGAR CONTEXTO DE UNA FILA DEL SHEET (O INICIALIZAR RECURSOS DE DRIVE)
//    ¡SIN GUARDAR NI FORZAR NINGUNA "FOTO MADRE" EN EL SERVIDOR!
// --------------------------------------------------------------------------
if ($action === 'get_row_prompt' || $action === 'get_row_context') {
    $nTarget = trim($_GET['n'] ?? '1');
    $customPhotoFolder = trim($_GET['photo_folder'] ?? '');

    $cfg = getConfig($configFile);
    $sheetUrl = $cfg['sheet_url'] ?: DEFAULT_SHEET_URL;
    $driveApiKey = $cfg['google_drive_api_key'] ?? '';

    $sheetData = fetchSheetDataAndCatalog($sheetUrl);
    if (isset($sheetData['error'])) {
        echo json_encode(['status' => 'error', 'message' => $sheetData['error']]);
        exit;
    }

    $targetRowObj = null;
    foreach ($sheetData['rows'] as $rObj) {
        if ((string)$rObj['n'] === (string)$nTarget) {
            $targetRowObj = $rObj;
            break;
        }
    }

    if (!$targetRowObj) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la fila N° ' . $nTarget]);
        exit;
    }

    $folderFotosId = $customPhotoFolder ? extractDriveId($customPhotoFolder) : $targetRowObj['photo_folder_id'];
    $folderLogosId = $targetRowObj['logo_folder_id'] ?: DEFAULT_LOGO_FOLDER_ID;
    $manualId = $targetRowObj['brand_manual_id'] ?: DEFAULT_BRAND_MANUAL_ID;

    // 1. Listar fotos disponibles en la carpeta de Drive de la doctora (sin pre-guardar foto madre)
    $availablePhotos = [];
    if ($folderFotosId && $driveApiKey) {
        $availablePhotos = fetchDriveFilesList($folderFotosId, $driveApiKey, true);
    }

    // 2. Listar logos disponibles (Drive + locales en assets/)
    $availableLogos = [
        [
            'id' => 'local_blanco',
            'name' => 'Logo JM Vertical Blanco (Local)',
            'source' => 'local',
            'local_path' => 'assets/logo_jm_vertical_blanco.png',
            'thumbnail' => 'api.php?action=local_asset&file=logo_jm_vertical_blanco.png'
        ],
        [
            'id' => 'local_azul',
            'name' => 'Logo JM Vertical Azul (Local)',
            'source' => 'local',
            'local_path' => 'assets/logo_jm_vertical_azul.png',
            'thumbnail' => 'api.php?action=local_asset&file=logo_jm_vertical_azul.png'
        ]
    ];
    if ($folderLogosId && $driveApiKey) {
        $driveLogos = fetchDriveFilesList($folderLogosId, $driveApiKey, true);
        foreach ($driveLogos as $dl) {
            $availableLogos[] = $dl;
        }
    }

    // 3. Manual de Marca
    $brandManualInfo = [
        'id' => $manualId ?: 'local_brand_board',
        'name' => 'BRAND_BOARD_JM.png',
        'source' => $manualId ? 'drive' : 'local',
        'thumbnail' => $manualId
            ? ('api.php?action=proxy_drive_image&id=' . urlencode($manualId) . '&sz=w400')
            : 'api.php?action=local_asset&file=BRAND_BOARD_JM.png'
    ];

    $slides = $targetRowObj['slides_parsed'];
    $slideCount = count($slides);

    $vars = [
        'N' => $targetRowObj['n'],
        'TEMA' => $targetRowObj['tema'],
        'TITULO' => $targetRowObj['titulo'],
        'GUION' => $targetRowObj['guion'],
        'SUBTITULOS' => $targetRowObj['subtitulos'],
        'DRA' => $targetRowObj['dra'],
        'ESTADO' => $targetRowObj['estado'],
        'CANTIDAD_SLIDES' => $slideCount
    ];

    $masterTemplate = getMasterTemplate($masterTemplateFile);
    $slideTemplate = getSlideTemplate($slideTemplateFile);
    $masterPrompt = renderTemplate($masterTemplate, $vars);

    // Armar escaleta inicial variada slide por slide
    $slidePlan = buildVariedSlidePlan($slides, [
        'dra' => $targetRowObj['dra'],
        'tema' => $targetRowObj['tema'],
        'titulo' => $targetRowObj['titulo'],
        'subtitulos' => $targetRowObj['subtitulos']
    ], $slideTemplate);

    echo json_encode([
        'status' => 'ok',
        'row' => $targetRowObj['full_data'],
        'row_summary' => [
            'n' => $targetRowObj['n'],
            'tema' => $targetRowObj['tema'],
            'titulo' => $targetRowObj['titulo'],
            'dra' => $targetRowObj['dra'],
            'guion' => $targetRowObj['guion'],
            'subtitulos' => $targetRowObj['subtitulos'],
            'instagram_caption' => $targetRowObj['instagram_caption'],
            'slides_count' => $slideCount,
            'photo_folder_id' => $folderFotosId
        ],
        'vars' => $vars,
        'prompt' => $masterPrompt,
        'slide_plan' => $slidePlan,
        'doctor_catalog' => $sheetData['doctor_catalog'],
        'assets' => [
            'photos' => $availablePhotos,
            'logos' => $availableLogos,
            'brand_manual' => $brandManualInfo
        ]
    ]);
    exit;
}

// Servir activos locales de la carpeta /assets de forma segura
if ($action === 'local_asset') {
    $file = basename($_GET['file'] ?? '');
    $path = $baseDir . '/assets/' . $file;
    if ($file && file_exists($path)) {
        $info = @getimagesize($path);
        header('Content-Type: ' . ($info['mime'] ?? 'image/png'));
        readfile($path);
        exit;
    }
    http_response_code(404);
    exit;
}

// --------------------------------------------------------------------------
// 9. DIRECTOR DE ARTE IA: PLANIFICAR / REFINAR ESCALETA DE SLIDES (COMO CHATGPT)
// --------------------------------------------------------------------------
if ($action === 'plan_carousel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $guion = trim($input['guion'] ?? '');
    $tema = trim($input['tema'] ?? 'Odontología Integral');
    $titulo = trim($input['titulo'] ?? '');
    $dra = trim($input['dra'] ?? 'Jovany');
    $subtitulos = trim($input['subtitulos'] ?? '');
    $masterPrompt = trim($input['master_prompt'] ?? '');
    $userInstruction = trim($input['user_instruction'] ?? '');
    $useAiDirector = !empty($input['use_ai_director']);

    $slides = parseGuionSlides($guion, $titulo ?: $tema);
    $slideTemplate = getSlideTemplate($slideTemplateFile);
    $basePlan = buildVariedSlidePlan($slides, [
        'dra' => $dra,
        'tema' => $tema,
        'titulo' => $titulo,
        'subtitulos' => $subtitulos
    ], $slideTemplate);

    if (!$useAiDirector) {
        echo json_encode([
            'status' => 'ok',
            'mode' => 'cinematic_rules',
            'total_slides' => count($basePlan),
            'slide_plan' => $basePlan
        ]);
        exit;
    }

    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    $directorModel = $cfg['director_model'] ?? 'gpt-4o';

    if (!$apiKey) {
        echo json_encode([
            'status' => 'ok',
            'mode' => 'fallback_no_key',
            'total_slides' => count($basePlan),
            'slide_plan' => $basePlan
        ]);
        exit;
    }

    $totalSlides = count($slides);
    $systemMessage = "Sos el Director de Arte de JM Odontología Integral (igual que el cerebro de ChatGPT cuando planifica una secuencia de imágenes coherente para un carrusel de Instagram).\n"
        . "Tu misión es diseñar la escaleta visual de exactamente {$totalSlides} slides individuales en formato 4:5 (1080x1350).\n"
        . "REGLAS CRÍTICAS:\n"
        . "1. Cada slide debe tener una puesta en escena, ángulo de cámara (shot_type) y postura DIFERENTE para que el carrusel sea dinámico y JAMÁS repita la misma pose estática de la foto de referencia.\n"
        . "2. La foto de referencia de la Dra. {$dra} se usa SOLO para mantener su rostro e identidad real, pero el modelo de imagen debe crear una escena y pose 100% nueva en cada slide.\n"
        . "3. Algunos slides intermedios pueden ser planos demostrativos (mostrando un elemento clínico, modelo dental, pantalla) o planos detalle si el texto lo amerita.\n"
        . "4. Devolvé ÚNICAMENTE un objeto JSON válido con la clave 'slides' que contenga un array de {$totalSlides} objetos con las claves:\n"
        . "   - 'num' (entero 1..{$totalSlides})\n"
        . "   - 'role' ('Portada', 'Desarrollo', o 'Cierre / CTA')\n"
        . "   - 'text' (el texto exacto en español que va escrito en la imagen del slide)\n"
        . "   - 'shot_type' (descripción detallada del plano cinematográfico, pose nueva de la Dra. {$dra}, acción e iluminación)\n"
        . "   - 'include_doctor' (boolean: true si aparece la doctora, false si es un plano detalle/macro sin persona)\n"
        . "   - 'prompt' (el prompt completo, rico y específico para enviar al generador de imágenes sunburst para ESTE único slide, incluyendo el texto exacto entre comillas, la descripción de escena nueva, la instrucción de usar la foto adjunta solo por su rostro sin copiar la pose, y la paleta de marca #16345F, #7DD3FC, #FFFFFF).";

    $userMessage = "DATOS DEL CARRUSEL:\n"
        . "- Tema: {$tema}\n"
        . "- Título: {$titulo}\n"
        . "- Doctora protagonista: Dra. {$dra}\n"
        . "- Palabras clave: {$subtitulos}\n\n"
        . "GUION DESGLOSADO ({$totalSlides} slides):\n" . json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n"
        . ($masterPrompt ? "DIRECTIVAS GENERALES DEL USUARIO:\n{$masterPrompt}\n\n" : "")
        . ($userInstruction ? "PEDIDO ESPECIAL EN EL HILO DE CHAT:\n{$userInstruction}\n\n" : "");

    $payload = json_encode([
        'model' => $directorModel,
        'response_format' => ['type' => 'json_object'],
        'messages' => [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $userMessage]
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $res) {
        $data = json_decode($res, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        $parsedJson = json_decode($content, true);
        if (!empty($parsedJson['slides']) && is_array($parsedJson['slides'])) {
            $aiPlan = [];
            foreach ($parsedJson['slides'] as $idx => $s) {
                $aiPlan[] = [
                    'num' => $idx + 1,
                    'role' => $s['role'] ?? ($idx === 0 ? 'Portada' : 'Desarrollo'),
                    'text' => $s['text'] ?? ($slides[$idx]['text'] ?? ''),
                    'shot_type' => $s['shot_type'] ?? ($basePlan[$idx]['shot_type'] ?? ''),
                    'include_doctor' => isset($s['include_doctor']) ? (bool)$s['include_doctor'] : true,
                    'assigned_photo_index' => -1,
                    'prompt' => $s['prompt'] ?? ($basePlan[$idx]['prompt'] ?? '')
                ];
            }
            echo json_encode([
                'status' => 'ok',
                'mode' => 'ai_director',
                'director_model' => $directorModel,
                'total_slides' => count($aiPlan),
                'slide_plan' => $aiPlan
            ]);
            exit;
        }
    }

    // Fallback seguro a la escaleta cinematográfica si el modelo de chat no respondió JSON
    echo json_encode([
        'status' => 'ok',
        'mode' => 'cinematic_rules_fallback',
        'total_slides' => count($basePlan),
        'slide_plan' => $basePlan
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 10. HILO DE CHAT (REFINAR UN SLIDE O EL CARRUSEL CONVERSACIONALMENTE)
// --------------------------------------------------------------------------
if ($action === 'chat_refine' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $message = trim($input['message'] ?? '');
    $targetSlide = $input['target_slide'] ?? 'all'; // 'all', 'add_new', o número de slide 1..N
    $currentPlan = $input['slide_plan'] ?? [];
    $dra = trim($input['dra'] ?? 'Jovany');
    $tema = trim($input['tema'] ?? '');

    if (!$message || empty($currentPlan)) {
        echo json_encode(['status' => 'error', 'message' => 'Falta el mensaje o la escaleta actual']);
        exit;
    }

    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    $directorModel = $cfg['director_model'] ?? 'gpt-4o';

    // Si pide agregar un slide nuevo
    if ($targetSlide === 'add_new') {
        $newNum = count($currentPlan) + 1;
        $slideTemplate = getSlideTemplate($slideTemplateFile);
        $newSlide = buildVariedSlidePlan([
            ['num' => $newNum, 'role' => 'Cierre / Extra', 'text' => $message]
        ], ['dra' => $dra, 'tema' => $tema, 'titulo' => '', 'subtitulos' => ''], $slideTemplate)[0];
        $newSlide['num'] = $newNum;
        $currentPlan[] = $newSlide;

        echo json_encode([
            'status' => 'ok',
            'reply' => "Se agregó el Slide {$newNum} a la secuencia. Ya podés generarlo.",
            'slide_plan' => $currentPlan,
            'modified_slides' => [$newNum]
        ]);
        exit;
    }

    // Si hay API key, usar el LLM para refinar inteligentemente el/los slides pedidos
    if ($apiKey) {
        $sys = "Sos el Director de Arte de un carrusel de Instagram para JM Odontología Integral (Dra. {$dra}).\n"
            . "El usuario te envía una corrección en el hilo de chat (igual que en ChatGPT).\n"
            . "Recibís la escaleta actual ('current_plan') y a qué slide apunta ('target_slide': puede ser un número de slide específico o 'all').\n"
            . "Debés modificar únicamente el/los slides solicitados aplicando el cambio de escena, pose, texto o estilo pedido, manteniendo las reglas anti-sesgo (no copiar pose estática de la foto de referencia, formato vertical 4:5 individual).\n"
            . "Devolvé un JSON válido con:\n"
            . "- 'reply': breve respuesta en español confirmando qué ajustaste.\n"
            . "- 'modified_slides': array de enteros con los números de slides que modificaste (ej. [3]).\n"
            . "- 'slides': el array completo actualizado de slides.";

        $usr = "TARGET SLIDE: {$targetSlide}\n"
            . "INSTRUCCIÓN DEL USUARIO: {$message}\n\n"
            . "ESCALETA ACTUAL:\n" . json_encode($currentPlan, JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $directorModel,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $sys],
                ['role' => 'user', 'content' => $usr]
            ],
            'temperature' => 0.6
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $parsed = json_decode($data['choices'][0]['message']['content'] ?? '', true);
            if (!empty($parsed['slides']) && is_array($parsed['slides'])) {
                echo json_encode([
                    'status' => 'ok',
                    'reply' => $parsed['reply'] ?? 'Escaleta actualizada según tu indicación.',
                    'slide_plan' => $parsed['slides'],
                    'modified_slides' => $parsed['modified_slides'] ?? ($targetSlide === 'all' ? range(1, count($parsed['slides'])) : [(int)$targetSlide])
                ]);
                exit;
            }
        }
    }

    // Fallback directo si no se pudo usar LLM: anexar la directiva de corrección directamente al prompt del slide
    $modified = [];
    foreach ($currentPlan as &$s) {
        if ($targetSlide === 'all' || (int)$s['num'] === (int)$targetSlide) {
            $s['shot_type'] .= " | Corrección solicitada: " . $message;
            $s['prompt'] .= "\n\nAJUSTE PRIORITARIO SOLICITADO POR EL USUARIO:\n" . $message;
            $modified[] = (int)$s['num'];
        }
    }
    unset($s);

    echo json_encode([
        'status' => 'ok',
        'reply' => 'Se aplicó tu instrucción a ' . (count($modified) === 1 ? "el Slide {$modified[0]}" : "los slides seleccionados") . '.',
        'slide_plan' => $currentPlan,
        'modified_slides' => $modified
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 11. INICIALIZAR SESIÓN DE PRODUCCIÓN DE CARRUSEL
// --------------------------------------------------------------------------
if ($action === 'init_carousel_session' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $rowNum = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['row_num'] ?? 'libre'));
    $existingFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['folder'] ?? ''));

    if ($existingFolder && is_dir($outputDir . '/' . $existingFolder)) {
        $folderName = $existingFolder;
    } else {
        $folderName = 'carrusel_N' . ($rowNum ?: 'libre') . '_' . time();
    }

    $targetDir = $outputDir . '/' . $folderName;
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0775, true);
    }

    if (!empty($input['master_prompt'])) {
        file_put_contents($targetDir . '/prompt_utilizado.txt', $input['master_prompt']);
    }
    if (!empty($input['row_data'])) {
        file_put_contents($targetDir . '/row_data.json', json_encode($input['row_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    if (!empty($input['slide_plan'])) {
        file_put_contents($targetDir . '/slide_plan.json', json_encode($input['slide_plan'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    echo json_encode([
        'status' => 'ok',
        'folder' => $folderName
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 12. GENERAR UN SLIDE INDIVIDUAL CON SUNBURST / OPENAI IMAGE API
//     (CON REFERENCIAS REALES DINÁMICAS, SIN "FOTO MADRE" PEGADA EN SERVIDOR)
// --------------------------------------------------------------------------
if ($action === 'generate_slide' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $cfg = getConfig($configFile);
    $apiKey = $cfg['openai_api_key'] ?? '';
    $driveKey = $cfg['google_drive_api_key'] ?? '';

    if (!$apiKey) {
        echo json_encode(['status' => 'error', 'message' => 'API Key de OpenAI no configurada. Ve a la pestaña Configuración.']);
        exit;
    }

    $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['folder'] ?? ''));
    if (!$folder) {
        $folder = 'carrusel_libre_' . time();
    }
    $targetDir = $outputDir . '/' . $folder;
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0775, true);
    }

    $slideNum = max(1, (int)($input['slide_num'] ?? 1));
    $totalSlides = max(1, (int)($input['total_slides'] ?? 1));
    $slidePrompt = trim((string)($input['prompt'] ?? ''));
    $model = trim((string)($input['model'] ?? ($cfg['selected_model'] ?? 'gpt-image-2.5-sunburst')));
    $includeDoctor = isset($input['include_doctor']) ? (bool)$input['include_doctor'] : true;
    $stampLogo = isset($input['stamp_logo']) ? (bool)$input['stamp_logo'] : !empty($cfg['stamp_logo_overlay']);

    // Activos seleccionados explícitamente por el usuario en la interfaz para este slide
    $doctorPhotoAsset = $input['doctor_photo'] ?? null; // ['id'=>..., 'source'=>'drive'|'upload', 'name'=>...]
    $logoAsset = $input['logo_asset'] ?? null;          // ['id'=>..., 'source'=>'drive'|'local'|'upload', 'name'=>...]
    $brandManualAsset = $input['brand_manual'] ?? null;

    $slideFilename = sprintf('slide_%02d.png', $slideNum);
    file_put_contents($targetDir . "/slide_{$slideNum}_prompt.txt", $slidePrompt);

    // 1. Preparar en un archivo temporal efímero la foto de la doctora elegida (¡NUNCA caché fijo de foto madre!)
    $tempFilesToClean = [];
    $preparedDoctorPath = null;
    $doctorPhotoLabel = 'Sin foto de doctora (Escena / Detalle)';

    if ($includeDoctor && is_array($doctorPhotoAsset) && !empty($doctorPhotoAsset['id'])) {
        $srcType = $doctorPhotoAsset['source'] ?? 'drive';
        $assetId = $doctorPhotoAsset['id'];
        $doctorPhotoLabel = $doctorPhotoAsset['name'] ?? $assetId;

        $tmpRaw = $tempRefsDir . '/raw_doc_' . uniqid() . '.img';
        $tmpPng = $tempRefsDir . '/prep_doc_' . uniqid() . '.png';
        $tempFilesToClean[] = $tmpRaw;
        $tempFilesToClean[] = $tmpPng;

        if ($srcType === 'upload' && !empty($doctorPhotoAsset['local_path'])) {
            $candidate = $baseDir . '/' . ltrim($doctorPhotoAsset['local_path'], '/');
            if (file_exists($candidate)) {
                normalizeReferenceImage($candidate, $tmpPng, 1536);
            }
        } elseif ($srcType === 'drive') {
            if (downloadDriveImageFresh($assetId, $tmpRaw, $driveKey, 'w1600')) {
                normalizeReferenceImage($tmpRaw, $tmpPng, 1536);
            }
        }

        if (file_exists($tmpPng) && filesize($tmpPng) > 0) {
            $preparedDoctorPath = $tmpPng;
        }
    }

    // 2. Preparar el logo seleccionado (si se eligió uno)
    $preparedLogoPath = null;
    $logoLabel = 'Ninguno';
    if (is_array($logoAsset) && !empty($logoAsset['id']) && $logoAsset['id'] !== 'none') {
        $lSource = $logoAsset['source'] ?? 'local';
        $lId = $logoAsset['id'];
        $logoLabel = $logoAsset['name'] ?? $lId;

        $tmpLogoRaw = $tempRefsDir . '/raw_logo_' . uniqid() . '.img';
        $tmpLogoPng = $tempRefsDir . '/prep_logo_' . uniqid() . '.png';
        $tempFilesToClean[] = $tmpLogoRaw;
        $tempFilesToClean[] = $tmpLogoPng;

        if ($lSource === 'local' && !empty($logoAsset['local_path'])) {
            $candidate = $baseDir . '/' . ltrim($logoAsset['local_path'], '/');
            if (file_exists($candidate)) {
                normalizeReferenceImage($candidate, $tmpLogoPng, 1024);
            }
        } elseif ($lSource === 'upload' && !empty($logoAsset['local_path'])) {
            $candidate = $baseDir . '/' . ltrim($logoAsset['local_path'], '/');
            if (file_exists($candidate)) {
                normalizeReferenceImage($candidate, $tmpLogoPng, 1024);
            }
        } elseif ($lSource === 'drive') {
            if (downloadDriveImageFresh($lId, $tmpLogoRaw, $driveKey, 'w1200')) {
                normalizeReferenceImage($tmpLogoRaw, $tmpLogoPng, 1024);
            }
        }

        if (file_exists($tmpLogoPng) && filesize($tmpLogoPng) > 0) {
            $preparedLogoPath = $tmpLogoPng;
        }
    }

    // 3. Llamar a OpenAI Image API
    $rawSlideOut = $tempRefsDir . '/out_raw_' . uniqid() . '.png';
    $tempFilesToClean[] = $rawSlideOut;

    $success = false;
    $endpointUsed = '';
    $modelUsed = $model;
    $apiDiagnostics = [];

    // Helper para extraer imagen de respuesta de OpenAI
    $extractOpenAiImage = function($resBody, $httpCode, $outFile) use (&$apiDiagnostics) {
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
        $apiDiagnostics[] = "Respuesta 200 sin imagen en data[0]";
        return false;
    };

    // SI TENEMOS IMAGEN DE REFERENCIA DE LA DOCTORA (O LOGO) -> USAR /v1/images/edits
    if ($preparedDoctorPath || $preparedLogoPath) {
        $primaryRefPath = $preparedDoctorPath ?: $preparedLogoPath;

        // Intentamos primero con el modelo elegido y tamaño vertical 1024x1536 (soportado por gpt-image / sunburst)
        $editAttempts = [
            ['model' => $model, 'size' => '1024x1536', 'multi' => true],
            ['model' => $model, 'size' => '1024x1024', 'multi' => false]
        ];
        // Si el usuario tiene puesto gpt-image-2.5-sunburst pero su organización de OpenAI lo expone como gpt-image-1
        if ($model !== 'gpt-image-1') {
            $editAttempts[] = ['model' => 'gpt-image-1', 'size' => '1024x1536', 'multi' => true];
            $editAttempts[] = ['model' => 'gpt-image-1', 'size' => '1024x1024', 'multi' => false];
        }

        foreach ($editAttempts as $att) {
            if ($success) break;

            $postFields = [
                'model' => $att['model'],
                'prompt' => $slidePrompt,
                'n' => 1,
                'size' => $att['size']
            ];

            if ($att['multi'] && $preparedDoctorPath && $preparedLogoPath) {
                $postFields['image[0]'] = new CURLFile($preparedDoctorPath, 'image/png', 'doctor_reference.png');
                $postFields['image[1]'] = new CURLFile($preparedLogoPath, 'image/png', 'brand_logo.png');
            } else {
                $postFields['image'] = new CURLFile($primaryRefPath, 'image/png', 'reference.png');
            }

            $ch = curl_init('https://api.openai.com/v1/images/edits');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($extractOpenAiImage($res, $httpCode, $rawSlideOut)) {
                $success = true;
                $endpointUsed = '/v1/images/edits (' . $att['size'] . ')';
                $modelUsed = $att['model'];
                break;
            }
        }
    }

    // SI EL SLIDE NO LLEVA DOCTORA O SI EL MODELO SOLO SOPORTA GENERATIONS
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
                'prompt' => $slidePrompt,
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($extractOpenAiImage($res, $httpCode, $rawSlideOut)) {
                $success = true;
                $endpointUsed = '/v1/images/generations (' . $att['size'] . ')';
                $modelUsed = $att['model'];
                break;
            }
        }
    }

    // Finalizar a 1080x1350 (4:5) y limpiar temporales
    $finalSlidePath = $targetDir . '/' . $slideFilename;
    if ($success && file_exists($rawSlideOut)) {
        if (!finalizeSlideImage4x5($rawSlideOut, $finalSlidePath, $stampLogo ? $preparedLogoPath : null)) {
            @copy($rawSlideOut, $finalSlidePath);
        }
    }

    foreach ($tempFilesToClean as $tf) {
        if (file_exists($tf)) @unlink($tf);
    }

    if (!$success || !file_exists($finalSlidePath)) {
        echo json_encode([
            'status' => 'error',
            'slide_num' => $slideNum,
            'message' => 'No se pudo generar el Slide ' . $slideNum . '. Detalle OpenAI: ' . implode(' | ', $apiDiagnostics),
            'diagnostics' => $apiDiagnostics
        ]);
        exit;
    }

    $cacheBuster = time();
    echo json_encode([
        'status' => 'ok',
        'slide_num' => $slideNum,
        'total_slides' => $totalSlides,
        'folder' => $folder,
        'image_url' => 'output/' . $folder . '/' . $slideFilename . '?v=' . $cacheBuster,
        'clean_image_url' => 'output/' . $folder . '/' . $slideFilename,
        'model_used' => $modelUsed,
        'endpoint_used' => $endpointUsed,
        'doctor_photo_used' => $doctorPhotoLabel,
        'logo_used' => $logoLabel,
        'prompt_sent' => $slidePrompt
    ]);
    exit;
}

// --------------------------------------------------------------------------
// 13. HISTORIAL DE PRODUCCIONES
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
// 14. DESCARGAR TODOS LOS SLIDES EN ARCHIVO ZIP
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
