<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $textModel;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->textModel = env('OPENAI_TEXT_MODEL', 'gpt-4o-mini');
    }

    /**
     * Mejora o genera el texto para un campo puntual (micro-asistente)
     */
    public function assistField(string $fieldType, ?string $currentText = '', array $context = []): string
    {
        $brandName = $context['brand_name'] ?? 'JM Odontología Integral';
        $currentText = trim($currentText ?? '');
        $contextTopic = trim($context['topic'] ?? '');

        // Si el usuario escribió un texto o tema específico, esa es la prioridad absoluta
        if (!empty($currentText) && !empty($contextTopic) && strcasecmp($currentText, $contextTopic) !== 0) {
            $topic = "{$contextTopic} (Enfoque específico: {$currentText})";
        } elseif (!empty($currentText)) {
            $topic = $currentText;
        } elseif (!empty($contextTopic)) {
            $topic = $contextTopic;
        } else {
            $topic = 'Salud, prevención y bienestar bucodental';
        }

        $character = $context['character'] ?? 'Especialista';
        $visualType = $context['visual_type'] ?? 'Conceptual';
        $brandTone = $context['tone'] ?? 'Profesional, empático, cercano y claro';

        $systemPrompt = "Eres un director creativo y redactor senior especializado en contenido para redes sociales de la marca \"{$brandName}\".
Tu tarea es redactar o pulir textos breves y atractivos para publicaciones (Instagram/TikTok/LinkedIn) en español natural, profesional y empático.
DIRECTRICES ESTRICTAS E INQUEBRANTABLES:
1. Respeta 100% la intención y la temática exacta propuesta por el usuario: \"{$topic}\".
2. Queda TERMINANTEMENTE PROHIBIDO desviar el tema hacia implantes dentales, cirugías u otros procedimientos invasivos si el usuario está hablando de miedos, ansiedad en el consultorio, prevención, cepillado, hábitos o cualquier otro tema diferente.
3. Devuelve ÚNICAMENTE el texto redactado. Sin introducciones, sin explicaciones ni comillas.";

        $action = empty($currentText) ? "Generar texto para el campo" : "Mejorar y optimizar el borrador actual";

        $prompt = "Contexto de la pieza:
- Marca: {$brandName}
- Tema exacto solicitado por el usuario: \"{$topic}\"
- Profesional: {$character}
- Estructura visual: {$visualType}
- Campo a redactar: {$fieldType}
- Acción solicitada: {$action}

";

        if (!empty($currentText)) {
            $prompt .= "Borrador o idea escrita por el usuario:\n\"{$currentText}\"\n\nInstrucción: Reescribe, pule y mejora este borrador para que sea más atractivo, claro y persuasivo, MANTENIENDO estrictamente la temática de \"{$topic}\". NO cambies a temas de implantes ni cirugías.";
        } else {
            $prompt .= "Instrucción: Genera un texto conciso, impactante y coherente con el tema \"{$topic}\" para el campo '{$fieldType}'.";
        }

        if ($fieldType === 'badge') {
            $prompt .= "\nNota de formato: Debe ser una etiqueta o categoría corta de 2 a 4 palabras vinculada al tema (ej: si el tema es 'miedos en el consultorio', redacta 'ADIÓS AL TEMOR', 'EXPERIENCIA CUIDADA' o 'CONFORT CLÍNICO').";
        } elseif ($fieldType === 'hook' || $fieldType === 'title') {
            $prompt .= "\nNota de formato: Titular de impacto que despierte curiosidad o empatía con el paciente.";
        } elseif ($fieldType === 'subtitle' || $fieldType === 'slide_body') {
            $prompt .= "\nNota de formato: Explicación clara, empática y tranquilizadora de 1 o 2 oraciones concisas.";
        } elseif ($fieldType === 'cta') {
            $prompt .= "\nNota de formato: Llamado a la acción invitando a comentar, guardar o agendar una consulta en {$brandName}.";
        }

        return $this->callOpenAIText($systemPrompt, $prompt);
    }

    /**
     * Parsea un guion pegado en bruto (desde Sheets, ChatGPT, Notion, WhatsApp) a formato estructurado
     */
    public function parseScript(string $rawText, ?string $brandName = 'JM Odontología Integral'): array
    {
        $systemPrompt = "Eres un asistente de parsing estructurado para publicaciones de redes sociales en {$brandName}.
Tu tarea es leer cualquier texto libre o guion pegado por el usuario y extraerlo en un JSON estructurado válido.
El JSON DEBE tener esta estructura exacta:
{
  \"topic\": \"Tema principal deducido\",
  \"badge\": \"Badge o pastilla temática corta (ej: GUÍA CLÍNICA)\",
  \"hook\": \"Título o gancho principal de la portada\",
  \"subtitle\": \"Subtítulo de portada si existe\",
  \"slides\": [
    {
      \"slide_number\": 1,
      \"headline\": \"Título de la lámina\",
      \"body_text\": \"Cuerpo o puntos clave de la lámina (conciso y claro)\",
      \"badge\": \"Badge opcional de la lámina\"
    }
  ],
  \"cta\": \"Texto del llamado a la acción final (ej: Guardá este post o escribinos para agendar tu consulta en {$brandName})\",
  \"caption\": \"Copy completo para el pie de foto de Instagram con emojis sutiles\",
  \"hashtags\": \"#JMOdontologia #OdontologiaIntegral #ImplantesDentales #SaludDental #EsteticaDental\"
}
Responde EXCLUSIVAMENTE con el bloque JSON válido, sin markdown ```json alrededor si es posible, o asegúrate de que sea JSON parseable.";

        $userPrompt = "A continuación tienes el texto pegado por el usuario. Por favor extráelo y estructúralo en el formato JSON para la marca {$brandName}:\n\n" . $rawText;

        $response = $this->callOpenAIText($systemPrompt, $userPrompt, true);
        
        $json = json_decode($response, true);
        if (!$json) {
            // Intentar limpiar posibles backticks
            $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($response));
            $clean = preg_replace('/\s*```$/', '', $clean);
            $json = json_decode($clean, true);
        }

        if (!$json || !is_array($json)) {
            // Fallback heurístico básico si la API no responde JSON
            return [
                'topic' => 'Contenido Odontológico',
                'badge' => 'GUÍA CLÍNICA',
                'hook' => 'Todo lo que necesitás saber sobre tu salud dental',
                'subtitle' => "Explicado por especialistas de {$brandName}",
                'slides' => [
                    [
                        'slide_number' => 1,
                        'headline' => 'Diagnóstico Inicial',
                        'body_text' => mb_substr($rawText, 0, 150),
                        'badge' => 'PASO 1'
                    ]
                ],
                'cta' => "¿Tenés dudas? Dejanos tu comentario o enviá un mensaje para coordinar tu evaluación en {$brandName}.",
                'caption' => $rawText,
                'hashtags' => '#JMOdontologia #OdontologiaIntegral #Implantes'
            ];
        }

        return $json;
    }

    /**
     * Genera un guion completo de carrusel desde una sola idea temática
     */
    public function generateFullScript(string $topic, ?string $character = 'Dra. Karina', ?string $format = 'carousel', int $slidesCount = 5, ?string $brandName = 'JM Odontología Integral'): array
    {
        $systemPrompt = "Eres el estratega de contenidos y director odontológico de {$brandName}.
Genera un guion completo para un carrusel de Instagram de {$slidesCount} láminas sobre el tema especificado.
Responde ÚNICAMENTE con un JSON válido con esta estructura:
{
  \"topic\": \"{$topic}\",
  \"badge\": \"GUÍA ESPECIALIZADA\",
  \"hook\": \"Título gancho de portada impactante\",
  \"subtitle\": \"Subtítulo que contextualiza el valor del carrusel\",
  \"slides\": [
    {
      \"slide_number\": 1,
      \"headline\": \"Título de lámina 1\",
      \"body_text\": \"Texto explicativo claro, conciso (máximo 35 palabras)\",
      \"badge\": \"01. PASO / CONCEPTO\"
    }
  ],
  \"cta\": \"Llamado a la acción claro para guardar o agendar consulta en {$brandName}\",
  \"caption\": \"Copy persuasivo y educativo para el feed de Instagram con emojis acordes\",
  \"hashtags\": \"#JMOdontologia #Implantes #OdontologiaAvanzada #EsteticaDental\"
}";

        $userPrompt = "Marca: {$brandName}\nTema: {$topic}\nProfesional firmante: {$character}\nFormato: {$format}\nCantidad de láminas: {$slidesCount}";

        $response = $this->callOpenAIText($systemPrompt, $userPrompt, true);
        
        $json = json_decode($response, true);
        if (!$json) {
            $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($response));
            $clean = preg_replace('/\s*```$/', '', $clean);
            $json = json_decode($clean, true);
        }

        return is_array($json) ? $json : [];
    }

    /**
     * Llamada genérica a OpenAI Chat Completions
     */
    protected function callOpenAIText(string $systemPrompt, string $userPrompt, bool $isJson = false): string
    {
        if (empty($this->apiKey)) {
            return "API Key de OpenAI no configurada.";
        }

        try {
            $payload = [
                'model' => $this->textModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.7,
            ];

            if ($isJson) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return trim($data['choices'][0]['message']['content'] ?? '');
            }

            Log::error("AIService callOpenAIText error: " . $response->body());
            return "Error al procesar con IA: " . $response->status();
        } catch (\Throwable $e) {
            Log::error("AIService Exception: " . $e->getMessage());
            return "Error de conexión con OpenAI: " . $e->getMessage();
        }
    }

    /**
     * Construye el canvas de referencia en memoria con la foto real del profesional y el logo oficial
     */
    public function buildReferenceCanvas(?string $charPhotoBytes, ?string $logoBytes, string $bgColor = '#FDFBF7', int $width = 1024, int $height = 1024): string
    {
        $canvas = imagecreatetruecolor($width, $height);
        
        // Fondo dinámico seleccionado por el usuario o basado en la marca (ej: Cálido #FDFBF7, Blanco #FFFFFF, etc.)
        $hex = ltrim($bgColor, '#');
        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            $r = 253; $g = 251; $b = 247; // #FDFBF7 Cálido por defecto
        }
        $bg = imagecolorallocate($canvas, $r, $g, $b);
        imagefill($canvas, 0, 0, $bg);

        // 1. Pegar fotografía real del profesional a la derecha
        if (!empty($charPhotoBytes)) {
            $charImg = @imagecreatefromstring($charPhotoBytes);
            if ($charImg) {
                $cw = imagesx($charImg);
                $ch = imagesy($charImg);
                $targetH = (int) ($height * 0.90);
                $targetW = (int) ($cw * ($targetH / $ch));
                if ($targetW > (int) ($width * 0.55)) {
                    $targetW = (int) ($width * 0.55);
                }
                $posX = $width - $targetW;
                $posY = $height - $targetH;
                imagecopyresampled($canvas, $charImg, $posX, $posY, 0, 0, $targetW, $targetH, $cw, $ch);
                imagedestroy($charImg);
            }
        }

        // 2. Pegar logotipo oficial transparente de la marca
        if (!empty($logoBytes)) {
            $logoImg = @imagecreatefromstring($logoBytes);
            if ($logoImg) {
                $lw = imagesx($logoImg);
                $lh = imagesy($logoImg);
                $targetLw = (int) ($width * 0.30);
                $targetLh = (int) ($lh * ($targetLw / $lw));
                if ($targetLh > 140) $targetLh = 140;
                // Ubicar en esquina inferior izquierda como en la composición oficial
                $posY = $height - $targetLh - 35;
                imagecopyresampled($canvas, $logoImg, 35, $posY, 0, 0, $targetLw, $targetLh, $lw, $lh);
                imagedestroy($logoImg);
            }
        }

        ob_start();
        imagepng($canvas);
        $pngData = ob_get_clean();
        imagedestroy($canvas);

        return $pngData;
    }

    /**
     * Obtiene los bytes de la fotografía real del personaje seleccionado
     */
    public function getCharacterPhotoBytes(?string $characterId, ?string $charName = null): ?string
    {
        $char = null;
        if ($characterId) {
            $char = \App\Models\Character::find($characterId);
        }
        if (!$char && $charName) {
            $char = \App\Models\Character::where('name', 'like', "%{$charName}%")->first();
        }
        if (!$char) {
            $char = \App\Models\Character::first();
        }

        if (!$char) return null;

        // 1. Prioridad: Obtener directamente desde la carpeta de Google Drive del personaje
        $drivePhotos = $char->getDrivePhotos();
        if (!empty($drivePhotos)) {
            foreach ($drivePhotos as $photo) {
                $driveId = $photo['id'] ?? null;
                if ($driveId && strlen($driveId) > 20) {
                    try {
                        $url = "https://lh3.googleusercontent.com/d/{$driveId}=w1200";
                        $res = Http::timeout(8)->get($url);
                        if ($res->successful() && strlen($res->body()) > 2000) {
                            return $res->body();
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        // 2. Intentar desde cover_photo si tiene ID de Google Drive
        if (!empty($char->cover_photo)) {
            if (preg_match('/(?:gdrive-proxy|d)\/([a-zA-Z0-9_-]+)/', $char->cover_photo, $m)) {
                $driveId = $m[1];
                $url = "https://lh3.googleusercontent.com/d/{$driveId}=w1200";
                try {
                    $res = Http::timeout(8)->get($url);
                    if ($res->successful() && strlen($res->body()) > 2000) {
                        return $res->body();
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 3. Fallback a archivos locales si existen
        $localCandidates = [
            public_path("assets/characters/dra_karina/IMG_4609.HIF.jpg"),
            base_path("../public/assets/characters/dra_karina/IMG_4609.HIF.jpg"),
            public_path("assets/characters/dra_karina/IMG_4615.HIF.jpg"),
            base_path("../public/assets/characters/dra_karina/IMG_4615.HIF.jpg"),
        ];
        foreach ($localCandidates as $path) {
            if (file_exists($path) && filesize($path) > 1000) {
                return file_get_contents($path);
            }
        }

        return null;
    }

    /**
     * Obtiene los bytes del logotipo oficial de la marca
     */
    public function getBrandLogoBytes(?string $brandId, ?string $logoId = null): ?string
    {
        $brand = $brandId ? \App\Models\Brand::find($brandId) : (\App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::first());
        if (!$brand) return null;

        $logos = $brand->getLogosList();
        if (empty($logos)) return null;

        $selectedLogo = null;
        if ($logoId) {
            foreach ($logos as $l) {
                if (($l['id'] ?? '') === $logoId) {
                    $selectedLogo = $l;
                    break;
                }
            }
        }
        if (!$selectedLogo) {
            $selectedLogo = $logos[0];
        }

        $logoDriveId = $selectedLogo['id'] ?? null;
        if ($logoDriveId && strlen($logoDriveId) > 20) {
            try {
                $url = "https://lh3.googleusercontent.com/d/{$logoDriveId}=w800";
                $res = Http::timeout(8)->get($url);
                if ($res->successful() && strlen($res->body()) > 1000) {
                    return $res->body();
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }

    /**
     * Genera la imagen real de un slide usando OpenAI (gpt-image-2.5-sunburst con assets reales via images.edit)
     */
    public function generateSlideImage(string $contentId, int $slideNumber, string $headline, ?string $bodyText, ?string $badge, array $meta = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'API Key de OpenAI no configurada'];
        }

        $imageModel = env('OPENAI_IMAGE_MODEL', 'gpt-image-2.5-sunburst');
        $brandId = $meta['brand_id'] ?? null;
        $brandName = $meta['brand_name'] ?? 'JM Odontologia Integral';
        $charId = $meta['character_id'] ?? null;
        $charName = $meta['character_name'] ?? 'Dra. Karina';
        $logoId = $meta['logo_id'] ?? null;
        $visualType = $meta['visual_type'] ?? 'Conceptual';
        $paginator = $meta['paginator'] ?? '01. Numeración simple (01 / 07)';
        $primaryColor = $meta['primary_color'] ?? '#16345F';
        $accentColor = $meta['accent_color'] ?? '#7DD3FC';
        $backgroundStyle = $meta['background_style'] ?? 'Cálido / Crema Editorial (Luz natural difusa)';
        $backgroundHex = $meta['background_hex'] ?? '#FDFBF7';
        $isSinglePost = ($meta['type'] ?? 'carousel') === 'single_post';

        // Obtener modelos y URLs oficiales de Google Drive
        $charModel = $charId ? \App\Models\Character::find($charId) : (\App\Models\Character::where('name', 'like', "%{$charName}%")->first() ?? \App\Models\Character::first());
        $charGdriveUrl = $charModel ? ($charModel->gdrive_folder_url ?: ($charModel->gdrive_folder_id ? "https://drive.google.com/drive/folders/{$charModel->gdrive_folder_id}" : null)) : null;
        $charRole = $charModel ? ($charModel->role ?? 'Especialista') : 'Especialista';

        $brandModel = $brandId ? \App\Models\Brand::find($brandId) : (\App\Models\Brand::where('name', 'like', '%JM%')->first() ?? \App\Models\Brand::first());
        $logosGdriveUrl = $brandModel ? $brandModel->logos_folder_url : null;
        $manualGdriveUrl = $brandModel ? $brandModel->brand_manual_url : null;

        // Obtener especificación técnica del Tipo Visual seleccionado
        $visualTypesList = \App\Services\VisualTypeService::getAllTypes();
        $visualTypeSpec = '';
        foreach ($visualTypesList as $vt) {
            if (strcasecmp($vt['name'], $visualType) === 0 || strcasecmp($vt['sheetLabel'], $visualType) === 0) {
                $visualTypeSpec = $vt['description'] . (!empty($vt['prompt_spec']) ? " Especificación de composición: " . $vt['prompt_spec'] : '');
                break;
            }
        }

        // 1. Obtener bytes reales de la profesional y del logo oficial
        $charBytes = $this->getCharacterPhotoBytes($charId, $charName);
        $logoBytes = $this->getBrandLogoBytes($brandId, $logoId);

        // 2. Construir Canvas de Referencia compuesto usando el color de fondo elegido
        $referenceCanvasPng = $this->buildReferenceCanvas($charBytes, $logoBytes, $backgroundHex);

        // 3. Contexto narrativo del carrusel completo
        $allSlides = $meta['all_slides'] ?? [];
        $totalSlides = $meta['total_slides'] ?? (count($allSlides) ?: 1);
        $carouselTitle = $meta['carousel_title'] ?? $headline;

        $carouselNarrative = "";
        if (!empty($allSlides) && count($allSlides) > 1) {
            $carouselNarrative = "GUION SECUENCIAL Y NARRATIVA DEL CARRUSEL COMPLETO ({$totalSlides} Láminas):\n";
            $carouselNarrative .= "Tema Principal: \"{$carouselTitle}\"\n";
            foreach ($allSlides as $idx => $s) {
                $num = $s['slide_number'] ?? ($idx + 1);
                $h = $s['headline'] ?? '';
                $b = $s['badge'] ?? '';
                $t = $s['body_text'] ?? '';
                $carouselNarrative .= "  - Lámina {$num}: [{$b}] \"{$h}\" - {$t}\n";
            }
            $carouselNarrative .= "\nOBJETIVO CRÍTICO: Esta pieza es la LÁMINA #{$slideNumber} DE {$totalSlides}. Debe mantener coherencia cromática absoluta, mismo estilo visual, iluminación armónica y la misma identidad de marca a lo largo de todo el carrusel.\n\n";
        }

        // 4. Si el usuario editó o personalizó el prompt manualmente en la interfaz, respetar su versión exacta
        if (!empty($meta['custom_prompt']) && is_string($meta['custom_prompt']) && strlen(trim($meta['custom_prompt'])) > 20) {
            $prompt = trim($meta['custom_prompt']);
        } else {
            // Prompt maestro estructurado para OpenAI Sunburst
            $prompt = "PROMPT MAESTRO DE PRODUCCIÓN VISUAL [gpt-image-2.5-sunburst via Reference Canvas]:\n";
            $prompt .= "FORMATO: " . ($isSinglePost ? "Post Cuadrado para Feed (1080x1080 px, 1 Pieza Única)" : "Lámina #{$slideNumber} de {$totalSlides} para Carrusel de Instagram (1080x1350 px, proporción 4:5)") . ".\n";
            $prompt .= "ESTRUCTURA VISUAL SELECCIONADA: {$visualType}\n";
            if (!empty($visualTypeSpec)) {
                $prompt .= "- Guía de Estructura: {$visualTypeSpec}\n";
            }
            if (!$isSinglePost) {
                $prompt .= "ESTILO DE PAGINADOR / DESLIZADOR: {$paginator}\n";
            }
            $prompt .= "\n";

            $prompt .= "FUENTES OFICIALES Y ACTIVOS EN GOOGLE DRIVE:\n";
            if ($charGdriveUrl) {
                $prompt .= "- Carpeta Oficial de Fotos Reales del Personaje ({$charName}): {$charGdriveUrl}\n";
            }
            if ($logosGdriveUrl) {
                $prompt .= "- Carpeta Oficial de Logotipos de Marca: {$logosGdriveUrl}\n";
            }
            if ($manualGdriveUrl) {
                $prompt .= "- Manual de Identidad de Marca: {$manualGdriveUrl}\n";
            }
            $prompt .= "\n";

            $prompt .= "IDENTIDAD DE MARCA Y PALETA CROMÁTICA:\n";
            $prompt .= "- Marca: \"{$brandName}\"\n";
            $prompt .= "- Color Primario Institucional: {$primaryColor}\n";
            $prompt .= "- Color Acento: {$accentColor}\n";
            $prompt .= "- Fondo y Atmósfera Visual Seleccionada: {$backgroundStyle} (Tono base: {$backgroundHex})\n";
            $prompt .= "- Regla de Color: El fondo debe respetar el estilo seleccionado ({$backgroundStyle}) con acabado fotorrealista luminoso y bokeh difuso. Los títulos y detalles gráficos principales deben aplicar el color primario ({$primaryColor}) y el acento ({$accentColor}).\n\n";

            $prompt .= "DIRECCIÓN DE ARTE Y PRODUCCIÓN CREATIVA DEL PERSONAJE:\n";
            $prompt .= "- Personaje: {$charName} ({$charRole})\n";
            $prompt .= "- INSTRUCCIÓN AL AGENTE / MODELO: Acceder a la carpeta de Google Drive del personaje ({$charName}), analizar las fotografías reales disponibles y utilizar toda la creatividad artística para componer la imagen con la pose, actitud, encuadre e interacción más conveniente y atractiva según el contenido textual de esta lámina y la estructura visual seleccionada ({$visualType}).\n";
            $prompt .= "- FIDELIDAD ESTRICTA: Mantén fielmente el rostro, rasgos faciales y tono real de {$charName} según la fotografía provista. Prohibido sustituir el rostro o generar a otra persona con IA; acabado fotorrealista de alto nivel editorial médico.\n";
            $prompt .= "- LOGOTIPO OFICIAL: Integra el logotipo oficial de \"{$brandName}\" en la composición sin deformar su proporción ni redibujarlo.\n\n";

            if (!$isSinglePost && !empty($carouselNarrative)) {
                $prompt .= $carouselNarrative;
            }

            $prompt .= "CONTENIDO TEXTUAL ESPECÍFICO DE ESTA PIEZA (" . ($isSinglePost ? "POST ÚNICO 1080x1080" : "LÁMINA {$slideNumber}/{$totalSlides}") . "):\n";
            if (!empty($badge)) {
                $prompt .= "- Categoría / Kicker Superior: \"{$badge}\"\n";
            }
            $prompt .= "- Titular Principal: \"{$headline}\"\n";
            if (!empty($bodyText)) {
                $prompt .= "- Texto / Cuerpo de Apoyo: \"{$bodyText}\"\n";
            }
            $prompt .= "\nACABADO EDITORIAL:\n";
            $prompt .= "Composición fotográfica de estudio clínico internacional de prestigio. Iluminación suave difusa, acabado fotorrealista y tipografía en español limpia, moderna, elegante y de fácil lectura sin errores ortográficos.";
        }

        // Directorios de almacenamiento
        $storageDir = public_path("storage/creations/{$contentId}");
        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }

        $fileName = "slide_{$slideNumber}.png";
        $filePath = "{$storageDir}/{$fileName}";
        $publicUrl = "/storage/creations/{$contentId}/{$fileName}";

        // Asegurar copia también en el public raíz si está estructurado en subcarpetas
        $rootPublicDir = base_path("../public/storage/creations/{$contentId}");
        if (is_dir(base_path('../public/storage')) && !file_exists($rootPublicDir)) {
            @mkdir($rootPublicDir, 0777, true);
        }

        try {
            // Transmisión directa a OpenAI images.edit (gpt-image-2.5-sunburst)
            $response = Http::withToken($this->apiKey)
                ->timeout(90)
                ->attach('image', $referenceCanvasPng, 'reference_input.png')
                ->post('https://api.openai.com/v1/images/edits', [
                    'model' => $imageModel,
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                ]);

            if (!$response->successful()) {
                // Fallback a generations puro si edits no estuviera disponible temporalmente
                Log::warning("OpenAI images/edits warning: " . $response->body() . ". Trying fallback.");
                $response = Http::withToken($this->apiKey)
                    ->timeout(90)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => $imageModel,
                        'prompt' => $prompt,
                        'n' => 1,
                        'size' => '1024x1024',
                    ]);
            }

            if ($response->successful()) {
                $b64 = $response->json()['data'][0]['b64_json'] ?? null;
                if ($b64) {
                    $imgBinary = base64_decode($b64);
                    file_put_contents($filePath, $imgBinary);
                    if (is_dir($rootPublicDir)) {
                        @file_put_contents("{$rootPublicDir}/{$fileName}", $imgBinary);
                    }
                    return [
                        'success' => true,
                        'url' => $publicUrl,
                        'path' => $filePath,
                        'prompt' => $prompt
                    ];
                }

                $rawUrl = $response->json()['data'][0]['url'] ?? null;
                if ($rawUrl) {
                    $imgData = Http::timeout(30)->get($rawUrl)->body();
                    file_put_contents($filePath, $imgData);
                    if (is_dir($rootPublicDir)) {
                        @file_put_contents("{$rootPublicDir}/{$fileName}", $imgData);
                    }
                    return [
                        'success' => true,
                        'url' => $publicUrl,
                        'path' => $filePath,
                        'prompt' => $prompt
                    ];
                }
            }

            return ['success' => false, 'error' => $response->json()['error']['message'] ?? 'Error al procesar con OpenAI'];
        } catch (\Throwable $e) {
            Log::error("AIService generateSlideImage exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

