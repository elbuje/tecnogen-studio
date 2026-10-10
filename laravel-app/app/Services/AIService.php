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
        $topic = $context['topic'] ?? 'Odontología digital y rehabilitación oral con implantes';
        $character = $context['character'] ?? 'Especialista Odontológica';
        $visualType = $context['visual_type'] ?? 'Infografía Clínica';
        $brandTone = $context['tone'] ?? 'Profesional, empático, médico y de alta precisión';

        $systemPrompt = "Eres el director creativo y redactor médico senior de TecnoGen Studio (centro de odontología digital, implantes dentales de alta precisión y estética clínica).
Tu misión es redactar o pulir textos para publicaciones en redes sociales (Instagram/LinkedIn/TikTok) en español neutro rioplatense o latinoamericano elegante.
Directrices:
- Claridad, autoridad médica y persuasión.
- Sin rodeos ni clichés innecesarios.
- Responde ÚNICAMENTE con el texto sugerido sin comillas extras ni explicaciones alrededor.";

        $action = empty(trim($currentText ?? '')) ? "Generar desde cero" : "Mejorar y optimizar el borrador actual";

        $prompt = "Contexto del contenido:
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
            $prompt .= "Instrucción: Genera un texto nuevo y altamente efectivo para el campo '{$fieldType}' que complemente el tema '{$topic}'.";
        }

        if ($fieldType === 'badge') {
            $prompt .= "\nNota especial: El badge es una pastilla temática corta (máximo 2 a 4 palabras), ej: 'GUÍA CLÍNICA', 'MITO VS REALIDAD', 'CASO CLÍNICO', 'TECNOLOGÍA 3D', 'SALUD ORAL'.";
        } elseif ($fieldType === 'hook' || $fieldType === 'title') {
            $prompt .= "\nNota especial: El gancho o título de portada debe atrapar en 1 segundo, despertar curiosidad o resolver una duda frecuente del paciente.";
        } elseif ($fieldType === 'cta') {
            $prompt .= "\nNota especial: El llamado a la acción debe invitar a guardar el carrusel, agendar un turno o comentar una palabra clave.";
        }

        return $this->callOpenAIText($systemPrompt, $prompt);
    }

    /**
     * Parsea un guion pegado en bruto (desde Sheets, ChatGPT, Notion, WhatsApp) a formato estructurado
     */
    public function parseScript(string $rawText): array
    {
        $systemPrompt = "Eres un asistente de parsing estructurado para publicaciones de redes sociales en TecnoGen Studio.
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
  \"cta\": \"Texto del llamado a la acción final (ej: Guardá este post o escribinos para agendar tu consulta)\",
  \"caption\": \"Copy completo para el pie de foto de Instagram con emojis sutiles\",
  \"hashtags\": \"#TecnoGen #ImplantesDentales #OdontologiaDigital #SaludDental\"
}
Responde EXCLUSIVAMENTE con el bloque JSON válido, sin markdown ```json alrededor si es posible, o asegúrate de que sea JSON parseable.";

        $userPrompt = "A continuación tienes el texto pegado por el usuario. Por favor extráelo y estructúralo en el formato JSON:\n\n" . $rawText;

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
                'subtitle' => 'Explicado por especialistas de TecnoGen',
                'slides' => [
                    [
                        'slide_number' => 1,
                        'headline' => 'Diagnóstico Inicial',
                        'body_text' => mb_substr($rawText, 0, 150),
                        'badge' => 'PASO 1'
                    ]
                ],
                'cta' => '¿Tenés dudas? Dejanos tu comentario o enviá un mensaje para coordinar tu evaluación.',
                'caption' => $rawText,
                'hashtags' => '#TecnoGen #OdontologiaDigital #Implantes'
            ];
        }

        return $json;
    }

    /**
     * Genera un guion completo de carrusel desde una sola idea temática
     */
    public function generateFullScript(string $topic, ?string $character = 'Dra. Karina', ?string $format = 'carousel', int $slidesCount = 5): array
    {
        $systemPrompt = "Eres el estratega de contenidos y director odontológico de TecnoGen Studio.
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
  \"cta\": \"Llamado a la acción claro para guardar o agendar consulta en TecnoGen\",
  \"caption\": \"Copy persuasivo y educativo para el feed de Instagram con emojis acordes\",
  \"hashtags\": \"#TecnoGen #Implantes #OdontologiaAvanzada #EsteticaDental\"
}";

        $userPrompt = "Tema: {$topic}\nProfesional firmante: {$character}\nFormato: {$format}\nCantidad de láminas: {$slidesCount}";

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
     * Genera la imagen real de un slide usando OpenAI (gpt-image-2.5-sunburst o fallback)
     */
    public function generateSlideImage(string $contentId, int $slideNumber, string $headline, ?string $bodyText, ?string $badge, array $meta = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'API Key de OpenAI no configurada'];
        }

        $imageModel = env('OPENAI_IMAGE_MODEL', 'gpt-image-2.5-sunburst');
        $charName = $meta['character_name'] ?? 'Especialista Odontológica';
        $visualType = $meta['visual_type'] ?? 'Infografía Médica';
        $paginator = $meta['paginator'] ?? 'Numeración simple';
        $primaryColor = $meta['primary_color'] ?? '#16345F';
        $accentColor = $meta['accent_color'] ?? '#7DD3FC';
        $isSinglePost = ($meta['type'] ?? 'carousel') === 'single_post';

        $prompt = "Diseño gráfico profesional y fotorrealista para publicación odontológica de alta gama de TecnoGen Dental Studio.\n";
        $prompt .= "Formato: " . ($isSinglePost ? "Post Cuadrado 1080x1080" : "Lámina #{$slideNumber} de Carrusel Instagram (1080x1350)") . ".\n";
        $prompt .= "Estructura Visual: {$visualType}. Estilo Paginador: {$paginator}.\n";
        $prompt .= "Paleta de Marca: Color Primario {$primaryColor} (Azul Médico Profundo), Acento {$accentColor} (Cyan Pulso Tecnológico), Fondo Blanco/Gris Claro Quirúrgico.\n";
        $prompt .= "Personaje o Especialista: {$charName} con ambo médico contemporáneo, sonrisa cálida y profesional.\n";
        if (!empty($badge)) {
            $prompt .= "Pastilla Temática / Badge: \"{$badge}\".\n";
        }
        $prompt .= "Título Principal en Español: \"{$headline}\".\n";
        if (!empty($bodyText)) {
            $prompt .= "Texto de Apoyo: \"{$bodyText}\".\n";
        }
        $prompt .= "Acabado de estudio clínico, iluminación difusa suave, tipografía limpia y sin errores ortográficos en español, diseño editorial de prestigio internacional.";

        // Directorio de salida
        $storageDir = public_path("storage/creations/{$contentId}");
        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }

        $fileName = "slide_{$slideNumber}.png";
        $filePath = "{$storageDir}/{$fileName}";
        $publicUrl = "/storage/creations/{$contentId}/{$fileName}";

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(90)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => $imageModel,
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                ]);

            if (!$response->successful()) {
                // Fallback a dall-e-3 si gpt-image-2.5-sunburst diera error puntual
                $response = Http::withToken($this->apiKey)
                    ->timeout(90)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => 'dall-e-3',
                        'prompt' => $prompt,
                        'n' => 1,
                        'size' => '1024x1024',
                    ]);
            }

            if ($response->successful()) {
                $b64 = $response->json()['data'][0]['b64_json'] ?? null;
                if ($b64) {
                    file_put_contents($filePath, base64_decode($b64));
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
                    return [
                        'success' => true,
                        'url' => $publicUrl,
                        'path' => $filePath,
                        'prompt' => $prompt
                    ];
                }
            }

            return ['success' => false, 'error' => $response->json()['error']['message'] ?? 'Error desconocido al generar imagen'];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
