<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\VisualTypeService;

class GenerateVisualTypeSunburstCommand extends Command
{
    protected $signature = 'visual-types:sunburst {--batch=} {--all} {--force}';
    protected $description = 'Genera imágenes de muestra de las 28 estructuras de Tipos Visuales en español exclusivamente con OpenAI gpt-image-2.5-sunburst';

    public function handle()
    {
        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_IMAGE_MODEL', 'gpt-image-2.5-sunburst');
        $force = $this->option('force');

        if (!$apiKey) {
            $this->error("❌ OPENAI_API_KEY no está configurada en .env");
            return 1;
        }

        $this->info("🎨 Iniciando generación del Catálogo de 28 Tipos Visuales con modelo: {$model} (100% en Español)");

        $allVisualTypes = VisualTypeService::getVisualTypesList();

        if ($this->option('all')) {
            $numbers = range(1, 28);
        } elseif ($this->option('batch')) {
            $numbers = array_map('intval', explode(',', $this->option('batch')));
        } else {
            $numbers = range(1, 28);
        }

        $outputDirs = [
            public_path('assets/visual_types'),
            base_path('../public/assets/visual_types'),
        ];

        foreach ($outputDirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        $promptsMap = [
            1 => "Diseño UI editorial médico moderno para una lámina de carrusel sobre salud dental. Estructura: CONCEPTUAL. Explica claramente la anatomía del esmalte dental con un diagrama vectorizado nítido de un diente, señalando 'Esmalte', 'Dentina' y 'Pulpa'. Textos grandes y legibles en español: '¿Qué es el Esmalte Dental?', 'Capa protectora mineralizada'. Estilo SaaS clínico, fondo blanco impecable, acentos azul marino (#16345F) y celeste (#0284C7).",
            
            2 => "Diseño UI comparativo lado a lado para carrusel odontológico. Estructura: COMPARATIVA (A vs B). Dos columnas nítidas comparando 'Carillas de Resina' vs 'Carillas de Porcelana'. Columna izquierda con tarjeta gris suave y columna derecha con tarjeta azul clínica. Filas con 'Durabilidad', 'Estética natural' y 'Tiempo de colocación'. Textos 100% en español. Fondo blanco impoluto, tipografía nítida y moderna.",

            3 => "Infografía secuencial numerada para carrusel clínico. Estructura: SECUENCIAL / PASO A PASO. Tres tarjetas horizontales conectadas por flechas sutiles: 'Paso 1: Diagnóstico Digital 3D', 'Paso 2: Colocación Guiada del Implante', 'Paso 3: Corona Definitiva'. Con números circulares grandes '01', '02', '03' en azul degradado. Textos en español, estética SaaS odontológica de alta gama.",

            4 => "Lámina editorial de storytelling clínico para carrusel. Estructura: NARRATIVA. Tarjeta elegante con retrato fotográfico cálido de paciente sonriendo naturalmente y cita médica en español: 'El viaje de recuperar la confianza: Cómo superamos el miedo al dentista en 3 sesiones'. Tipografía editorial sofisticada, badge 'Caso Clínico #4', fondo blanco y bordes ultra suaves.",

            5 => "Lámina dividida de diagnóstico y solución para carrusel. Estructura: PROBLEMA → SOLUCIÓN. Dos bloques claramente contrastados. Arriba/Izquierda bloque con acento suave coral: 'El Problema: Sensibilidad dental al frío y calor'. Abajo/Derecha bloque azul clínico destacado: 'La Solución: Sellado dentinario y barniz de flúor'. Iconos médicos limpios, textos 100% legibles en español.",

            6 => "Tarjeta de transformación estética de alta fidelidad para carrusel. Estructura: ANTES → DESPUÉS. Comparativa visual dividida por una línea vertical sutil. Lado izquierdo con badge gris oscuro 'Antes: Desalineación y manchas', lado derecho con badge azul brillante 'Después: Sonrisa alineada y blanca'. Textos en español, calidad fotográfica clínica premium, fondo blanco.",

            7 => "Diagrama de causa y consecuencia para carrusel odontológico. Estructura: CAUSA → CONSECUENCIA. Dos bloques conectados por una flecha direccional dinámica. Bloque 1: 'Causa: Apretar los dientes por estrés (Bruxismo)'. Flecha indicadora 'Desencadena en'. Bloque 2: 'Consecuencia: Desgaste del esmalte y dolor mandibular'. Badge de advertencia preventiva en español.",

            8 => "Lámina de lista y consejos para carrusel de redes sociales. Estructura: LISTA / ENUMERACIÓN. Título destacado en español: '4 Claves para encías sanas'. Cuatro ítems limpios con badges numéricos '1.', '2.', '3.', '4.' ('Cepillado suave 2 min', 'Uso de hilo dental diario', 'Enjuague bucal sin alcohol', 'Control cada 6 meses'). Fondo blanco, estética SaaS.",

            9 => "Infografía piramidal y niveles de atención médica. Estructura: JERARQUÍA. Pirámide dividida en 3 niveles de prioridad clínica: Nivel Base 'Prevención & Higiene diaria', Nivel Medio 'Tratamientos Preventivos (Limpiezas, Flúor)', Nivel Cúspide 'Rehabilitación & Cirugía'. Textos grandes en español, colores armónicos azul y verde clínico.",

            10 => "Flujograma horizontal continuo para carrusel médico. Estructura: PROCESO. 4 nodos conectados por líneas punteadas y flechas: '1. Evaluación Inicial' → '2. Escaneo Intraoral 3D' → '3. Fabricación Digital' → '4. Entrega y Ajuste'. Textos 100% en español, estilo interfaz tecnológica moderna, fondo blanco con sombras tenues.",

            11 => "Línea de tiempo cronológica para carrusel odontológico. Estructura: TIMELINE / LÍNEA DE TIEMPO. Eje horizontal con 4 hitos de recuperación post-tratamiento: 'Día 1: Reposo y frío local', 'Día 3: Desinflamación', 'Día 7: Retiro de puntos', 'Día 15: Alta médica'. Puntos cronológicos destacados en azul brillante, textos en español.",

            12 => "Tarjeta de datos duros y porcentaje para carrusel médico. Estructura: ESTADÍSTICA / DATOS. Cifra protagonista gigante en tipografía sans-serif audaz '98.5%' en azul marino (#16345F), subtítulo en español: 'Tasa de éxito en Implantes Dentales de Titanio', acompañado de un micro gráfico de dona circular moderno y badge 'Estudio Clínico 2026'.",

            13 => "Infografía modular clínica para carrusel de salud. Estructura: INFOGRAFÍA MULTIDISCIPLINAR. Composición en cuadrícula limpia de 4 cuadrantes con micro-iconos vectoriales: 'Cepillado', 'Hilo Dental', 'Alimentación Saludable', 'Visita Semestral'. Textos concisos y directos en español, fondo blanco puro, diseño SaaS médico.",

            14 => "Diagrama anatómico técnico y funcional para carrusel. Estructura: DIAGRAMA DESCRIPTIVO. Ilustración vectorial técnica y limpia de un implante dental señalando con líneas e indicadores: 'Corona de Zirconio', 'Pilar de Conexión', 'Tornillo de Titanio integrado al hueso'. Textos 100% en español, diseño ultra nítido.",

            15 => "Formato de lista de verificación interactiva para carrusel. Estructura: CHECKLIST. 4 casillas de verificación marcadas con círculos y tildes verdes (✓): '✓ Cepillado matutino y nocturno', '✓ Uso de seda dental', '✓ Limpieza de lengua', '✓ Hidratación constante'. Título: 'Tu Checklist Dental Diario'. Todo en español.",

            16 => "Tarjeta de preguntas frecuentes clínicas para carrusel. Estructura: PREGUNTA → RESPUESTA (FAQ). Bloque superior estilo burbuja de mensaje: '¿El blanqueamiento desgasta los dientes?'. Bloque inferior destacado en azul suave: 'Respuesta Médica: No, el gel actúa por oxigenación sobre los pigmentos sin alterar el esmalte'. Textos en español.",

            17 => "Lámina de contraste y desmitificación para carrusel. Estructura: MITO → REALIDAD. Dos bloques contrastados: Lado izquierdo con cruz roja (✕) 'Mito: El bicarbonato con limón blanquea seguro', Lado derecho con tilde verde (✓) 'Realidad: El ácido daña el esmalte de forma irreversible'. Textos claros y contundentes en español.",

            18 => "Lámina de beneficios y valor terapéutico para carrusel. Estructura: BENEFICIOS. Título en español: 'Beneficios de la Ortodoncia Invisible'. 3 tarjetas verticales con iconos azules: '1. Estética 100% transparente', '2. Removible para comer', '3. Higiene bucal sin brackets'. Diseño SaaS espacioso y pulcro.",

            19 => "Ficha técnica y especificaciones para carrusel odontológico. Estructura: CARACTERÍSTICAS. Panel de especificaciones con badges y barras de progreso sutiles: 'Material: Disilicato de Litio', 'Resistencia: 500 MPa', 'Estética: Máxima translucidez', 'Garantía: 10 años'. Textos en español, diseño elegante.",

            20 => "Composición editorial emotiva y humana para carrusel. Estructura: STORYTELLING / CASO REAL. Imagen limpia de paciente sonriente de perfil con tipografía elegante: 'De no querer sonreír en fotos a disfrutar cada momento. La historia de superación de Marcela'. Textos en español, calidez y profesionalismo clínico.",

            21 => "Tarjeta tipográfica con cita célebre para carrusel odontológico. Estructura: CITA / FRASE DESTACADA. Comillas gigantes estilizadas en azul claro (\" \"), texto central inspirador en español: 'Una sonrisa saludable no es solo estética, es el reflejo de tu bienestar integral'. Firma: 'Dra. Elena Ramos - Odontología Integral'.",

            22 => "Tarjeta de prueba social y reseña para carrusel médico. Estructura: TESTIMONIO / CASO DE ÉXITO. Ficha de paciente con 5 estrellas doradas (★★★★★), avatar profesional y cita textual en español: 'Excelente atención, el tratamiento con alineadores superó todas mis expectativas sin dolor'. Badge 'Paciente Verificado'.",

            23 => "Matriz de recomendaciones de conducta clínica para carrusel. Estructura: DO'S & DON'TS. Dos columnas balanceadas: Columna verde 'Lo que sí debes hacer: Aplicar frío local, Dieta blanda, Tomar medicación' vs Columna roja 'Lo que debes evitar: Enjuagues bruscos, Fumar, Alimentos calientes'. Textos 100% en español.",

            24 => "Lámina de ilustración médica 3D educativa para carrusel. Estructura: ANATOMÍA CLÍNICA / ZONA AFECTADA. Render limpio de corte transversal de una muela señalando con etiquetas azules: 'Caries superficial', 'Afectación del nervio', 'Zona ósea'. Título: '¿Cuándo se necesita un tratamiento de conducto?'. En español.",

            25 => "Tarjeta lúdica e interactiva para carrusel de redes. Estructura: VERDADERO O FALSO (QUIZ). Pregunta médica: '¿Debes enjuagarte con agua justo después de cepillarte?'. Dos botones interactivos grandes: 'VERDADERO' y 'FALSO' (con indicador de respuesta correcta). Textos en español, diseño llamativo.",

            26 => "Ficha ejecutiva rápida de procedimiento para carrusel. Estructura: FICHA DE TRATAMIENTO. Cuadrícula de 4 métricas clave: 'Duración: 45 minutos', 'Molestia: Mínima (0/5)', 'Anestesia: Local', 'Resultados: Visibles en 24h'. Título: 'Ficha Técnica: Blanqueamiento Láser'. Todo en español.",

            27 => "Tarjeta de advertencia y detección temprana para carrusel. Estructura: SEÑALES DE ALERTA / CUÁNDO CONSULTAR. Badge superior de precaución médica en ámbar suave 'Atención Preventiva'. 3 síntomas de alarma: 'Sangrado al cepillarse', 'Movilidad dental leve', 'Mal aliento persistente'. Textos en español.",

            28 => "Tabla de decisión multicriterio para carrusel médico. Estructura: TABLA DE OPCIONES / GUÍA DE ELECCIÓN. Tabla comparativa elegante de 3 columnas ('Opción', 'Durabilidad', 'Estética') evaluando 'Resina Compuesta', 'Porcelana Feldespática' y 'Zirconio Puro' con tildes y niveles. Textos 100% en español."
        ];

        $total = count($numbers);
        $current = 0;

        foreach ($numbers as $num) {
            $current++;
            if ($num <= 0 || $num > 28) continue;

            $numStr = sprintf('%02d', $num);
            $filename = "visual_type_{$numStr}.png";
            $targetPath = "{$outputDirs[0]}/{$filename}";

            if (!$force && file_exists($targetPath) && filesize($targetPath) > 200000) {
                $this->line("⏩ [#{$numStr} ({$current}/{$total})] Ya existe ({$filename}). Saltando...");
                continue;
            }

            // Buscar datos del tipo visual
            $vtData = null;
            foreach ($allVisualTypes as $vt) {
                if ($vt['number'] === $num) {
                    $vtData = $vt;
                    break;
                }
            }

            if (!$vtData) {
                $this->warn("⚠️ [#{$numStr}] No se encontró definición para el tipo visual {$num}.");
                continue;
            }

            $this->info("\n🚀 [#{$numStr} ({$current}/{$total})] Generando: {$vtData['name']} ({$vtData['category']})...");

            $specificPrompt = $promptsMap[$num] ?? $vtData['prompt_spec'];

            $prompt = "Tarjeta de interfaz UI profesional para lámina de carrusel odontológico y médico en redes sociales. "
                . "Estética: SaaS moderna, ultra limpia, fondo blanco puro (#FFFFFF), bordes sutiles (#E2E8F0), contraste azul marino (#16345F) y acentos celestes (#0284C7). "
                . "TODO EL TEXTO VISIBLE DEBE ESTAR ESTRICTAMENTE EN ESPAÑOL (sin palabras en inglés ni lorem ipsum). "
                . "Estructura visual requerida: {$specificPrompt}. "
                . "El contenido y la estructura diagramática deben ser el foco principal, con tipografía nítida y perfectamente legible. "
                . "Diseño médico de alta gama, luz de día, sin fondos oscuros ni sobrecarga gráfica.";

            try {
                $response = Http::withToken($apiKey)
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => $model,
                        'prompt' => $prompt,
                        'n' => 1,
                        'size' => '1024x1024'
                    ]);

                if (!$response->successful()) {
                    $err = $response->json()['error']['message'] ?? $response->body();
                    $this->error("❌ Error de OpenAI para #{$numStr}: {$err}");
                    continue;
                }

                $data = $response->json();
                $imageBytes = null;

                $b64 = $data['data'][0]['b64_json'] ?? null;
                if ($b64) {
                    $imageBytes = base64_decode($b64);
                } elseif (!empty($data['data'][0]['url'])) {
                    $imageBytes = Http::timeout(60)->get($data['data'][0]['url'])->body();
                }

                if (!$imageBytes) {
                    $this->error("❌ No se recibieron datos binarios para #{$numStr}");
                    continue;
                }

                foreach ($outputDirs as $dir) {
                    file_put_contents("{$dir}/{$filename}", $imageBytes);
                }

                $this->info("✅ [#{$numStr}] Guardado exitosamente: {$filename} (" . strlen($imageBytes) . " bytes)");

            } catch (\Throwable $e) {
                $this->error("❌ Excepción en #{$numStr}: " . $e->getMessage());
            }

            sleep(1);
        }

        $this->info("\n🎉 Generación de las 28 estructuras de Tipos Visuales finalizada con éxito.");
        return 0;
    }
}
