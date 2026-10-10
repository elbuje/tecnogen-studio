<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaginatorImageService
{
    protected string $apiKey;
    protected string $primaryModel;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->primaryModel = env('OPENAI_IMAGE_MODEL', 'gpt-image-2.5-sunburst');
    }

    /**
     * Catálogo maestro de definiciones de los 50 paginadores
     */
    public static function getPaginatorsList(): array
    {
        return [
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
    }

    /**
     * Genera la imagen de muestra de un paginador individual
     */
    public function generateSingle(int $number): array
    {
        $paginators = self::getPaginatorsList();
        $target = null;
        foreach ($paginators as $p) {
            if ($p['number'] === $number) {
                $target = $p;
                break;
            }
        }

        if (!$target) {
            return ['success' => false, 'error' => "Paginador #{$number} no encontrado."];
        }

        $numStr = sprintf('%02d', $number);
        $prompt = $this->buildPrompt($target);

        // Intento 1: Modelo Sunburst primario
        $result = $this->callOpenAI($this->primaryModel, $prompt);

        // Fallback a dall-e-3 si el modelo específico no está disponible en generations
        if (!$result['success']) {
            Log::warning("Fallo con {$this->primaryModel}: {$result['error']}. Reintentando con dall-e-3...");
            $result = $this->callOpenAI('dall-e-3', $prompt);
        }

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error']];
        }

        // Guardar la imagen en disco
        $saveDirs = [
            public_path("assets/paginators"),
            base_path("../public/assets/paginators"),
        ];

        $imageBytes = $result['image_data'];
        foreach ($saveDirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents("{$dir}/paginator_{$numStr}.png", $imageBytes);
        }

        return [
            'success' => true,
            'number' => $number,
            'file' => "/assets/paginators/paginator_{$numStr}.png",
            'model' => $result['model_used']
        ];
    }

    /**
     * Genera un lote de paginadores clave
     */
    public function generateBatch(array $numbers = [1, 4, 7, 9, 13, 23]): array
    {
        $results = [];
        foreach ($numbers as $num) {
            $res = $this->generateSingle($num);
            $results[$num] = $res;
        }
        return $results;
    }

    /**
     * Construye un prompt hiper-específico de diseño UI para la muestra del paginador
     */
    protected function buildPrompt(array $p): string
    {
        return "Professional UI design preview card for an ultra-modern medical social media carousel slide. "
            . "Style: Clean White SaaS aesthetic, minimalist, daylight clinical feel, crisp Apple-like finish. "
            . "Background: Pure clean white (#FFFFFF) with very subtle off-white dental slide mockup and soft drop shadow. "
            . "Focal Point: Prominently showcase the pagination element style: '{$p['name']}'. "
            . "Design specifications: {$p['description']}. "
            . "The pagination widget must be clearly visible, perfectly rendered, sharp typography, high contrast dark blue (#16345F) and slate gray accents. "
            . "No messy background clutter. Crisp product design UI component mockup showing exactly how carousel indicator #{$p['number']} looks in action.";
    }

    /**
     * Realiza la llamada a la API de OpenAI
     */
    protected function callOpenAI(string $model, string $prompt): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(90)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                    'response_format' => 'b64_json',
                ]);

            if (!$response->successful()) {
                $err = $response->json()['error']['message'] ?? $response->body();
                return ['success' => false, 'error' => $err];
            }

            $b64 = $response->json()['data'][0]['b64_json'] ?? null;
            if (!$b64) {
                // Si devuelve URL en lugar de b64
                $url = $response->json()['data'][0]['url'] ?? null;
                if ($url) {
                    $imageBytes = Http::timeout(30)->get($url)->body();
                    return ['success' => true, 'image_data' => $imageBytes, 'model_used' => $model];
                }
                return ['success' => false, 'error' => 'No image data returned'];
            }

            return [
                'success' => true,
                'image_data' => base64_decode($b64),
                'model_used' => $model
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
