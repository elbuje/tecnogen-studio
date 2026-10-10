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
        $topic = $context['topic'] ?? 'Odontología digital y rehabilitación oral con implantes';
        $character = $context['character'] ?? 'Dra. Karina';
        $visualType = $context['visual_type'] ?? 'Infografía Clínica';
        $brandTone = $context['tone'] ?? 'Profesional, empático, médico y de alta precisión';

        $systemPrompt = "Eres el director creativo y redactor médico senior de \"{$brandName}\" (centro de odontología digital, implantes dentales de alta precisión y estética clínica).
Tu misión es redactar o pulir textos para publicaciones en redes sociales (Instagram/LinkedIn/TikTok) en español neutro rioplatense o latinoamericano elegante para la marca {$brandName}.
Directrices:
- Claridad, autoridad médica y persuasión.
- Sin rodeos ni clichés innecesarios.
- Responde ÚNICAMENTE con el texto sugerido sin comillas extras ni explicaciones alrededor.";

        $action = empty(trim($currentText ?? '')) ? "Generar desde cero" : "Mejorar y optimizar el borrador actual";

        $prompt = "Contexto de la marca y publicación:
- Marca: {$brandName}
- Tema general: {$topic}
- Profesional que firma: {$character}
- Estructura visual: {$visualType}
- Tono de marca: {$brandTone}
- Campo a trabajar: {$fieldType}
- Acción: {$action}

";

        if (!empty(trim($currentText ?? '')) ) {
            $prompt .= "Borrador actual del usuario:\n\"" . trim($currentText) . "\"\n\nInstrucción: Reescribe y mejora este texto para que sea más claro, profesional, directo e impactante según el campo '{$fieldType}'.";
        } else {
            $prompt .= "Instrucción: Genera un texto nuevo y altamente efectivo para el campo '{$fieldType}' que complemente el tema '{$topic}' para la marca {$brandName}.";
        }

        if ($fieldType === 'badge') {
            $prompt .= "\nNota especial: El badge es una pastilla temática corta (máximo 2 a 4 palabras), ej: 'GUÍA CLÍNICA', 'MITO VS REALIDAD', 'CASO CLÍNICO', 'TECNOLOGÍA 3D', 'SALUD ORAL'.";
        } elseif ($fieldType === 'hook' || $fieldType === 'title') {
            $prompt .= "\nNota especial: El gancho o título de portada debe atrapar en 1 segundo, despertar curiosidad o resolver una duda frecuente del paciente.";
        } elseif ($fieldType === 'cta') {
            $prompt .= "\nNota especial: El llamado a la acción debe invitar a guardar el carrusel, agendar un turno o consultar en {$brandName}.";
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
    public function buildReferenceCanvas(?string $charPhotoBytes, ?string $logoBytes, string $brandColor = '#16345F', int $width = 1024, int $height = 1024): string
    {
        $canvas = imagecreatetruecolor($width, $height);
        
        // Fondo blanco/gris clínico con sutil degradé o tono de marca
        $bg = imagecolorallocate($canvas, 246, 249, 252);
        imagefill($canvas, 0, 0, $bg);

        // 1. Pegar fotografía real del profesional a la derecha
        if (!empty($charPhotoBytes)) {
            $charImg = @imagecreatefromstring($charPhotoBytes);
            if ($charImg) {
                $cw = imagesx($charImg);
                $ch = imagesy($charImg);
                $targetH = (int) ($height * 0.88);
                $targetW = (int) ($cw * ($targetH / $ch));
                if ($targetW > (int) ($width * 0.58)) {
                    $targetW = (int) ($width * 0.58);
                }
                $posX = $width - $targetW;
                $posY = $height - $targetH;
                imagecopyresampled($canvas, $charImg, $posX, $posY, 0, 0, $targetW, $targetH, $cw, $ch);
                imagedestroy($charImg);
            }
        }

        // 2. Pegar logotipo oficial transparente de la marca en la esquina superior izquierda
        if (!empty($logoBytes)) {
            $logoImg = @imagecreatefromstring($logoBytes);
            if ($logoImg) {
                $lw = imagesx($logoImg);
                $lh = imagesy($logoImg);
                $targetLw = (int) ($width * 0.28);
                $targetLh = (int) ($lh * ($targetLw / $lw));
                if ($targetLh > 130) $targetLh = 130;
                imagecopyresampled($canvas, $logoImg, 40, 40, 0, 0, $targetLw, $targetLh, $lw, $lh);
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
        $visualType = $meta['visual_type'] ?? 'Infografía Clínica';
        $paginator = $meta['paginator'] ?? 'Numeración simple';
        $primaryColor = $meta['primary_color'] ?? '#16345F';
        $accentColor = $meta['accent_color'] ?? '#7DD3FC';
        $isSinglePost = ($meta['type'] ?? 'carousel') === 'single_post';

        // 1. Obtener bytes reales de la profesional y del logo oficial
        $charBytes = $this->getCharacterPhotoBytes($charId, $charName);
        $logoBytes = $this->getBrandLogoBytes($brandId, $logoId);

        // 2. Construir Canvas de Referencia compuesto
        $referenceCanvasPng = $this->buildReferenceCanvas($charBytes, $logoBytes, $primaryColor);

        // 3. Prompt estructurado con fidelidad estricta de marca y persona
        $prompt = "Diseño gráfico profesional y fotorrealista para publicación odontológica de alta gama de la marca \"{$brandName}\".\n";
        $prompt .= "REGLA CRÍTICA DE IDENTIDAD: Mantén con máxima fidelidad el rostro, vestimenta médica y rasgos de la profesional ({$charName}) y el logotipo oficial de la marca ({$brandName}) que aparecen en la imagen de referencia provista. No alteres el rostro de la profesional ni inventes logotipos falsos.\n";
        $prompt .= "Formato: " . ($isSinglePost ? "Post Cuadrado (1080x1080)" : "Lámina #{$slideNumber} de Carrusel Instagram (1080x1350)") . ".\n";
        $prompt .= "Estructura Visual: {$visualType}. Estilo Paginador: {$paginator}.\n";
        $prompt .= "Paleta de Marca: Primario {$primaryColor}, Acento {$accentColor}, Fondo Limpio de Consultorio Odontológico de Alta Tecnología.\n";
        if (!empty($badge)) {
            $prompt .= "Pastilla Temática / Badge: \"{$badge}\".\n";
        }
        $prompt .= "Título Principal en Español: \"{$headline}\".\n";
        if (!empty($bodyText)) {
            $prompt .= "Texto de Apoyo en Español: \"{$bodyText}\".\n";
        }
        $prompt .= "Acabado de estudio clínico de prestigio, iluminación suave difusa, tipografía limpia y nítida en español sin errores ortográficos.";

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

