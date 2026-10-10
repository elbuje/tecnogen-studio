<?php

namespace App\Services;

class VisualTypeService
{
    /**
     * Alias para compatibilidad
     */
    public static function getAllTypes(): array
    {
        return self::getVisualTypesList();
    }

    /**
     * Catálogo maestro de Definiciones de Tipos Visuales para Carruseles Odontológicos y Médicos
     */
    public static function getVisualTypesList(): array
    {
        return [
            [
                'number' => 1,
                'id' => 'conceptual',
                'name' => 'Conceptual',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Conceptual',
                'description' => 'Explicación clara de un concepto o fundamento odontológico clave (ej: qué es la osteointegración o el esmalte dental).',
                'prompt_spec' => 'Lámina conceptual limpia explicando la estructura del esmalte dental con diagrama visual y tipografía moderna en español.'
            ],
            [
                'number' => 2,
                'id' => 'comparativa',
                'name' => 'Comparativa',
                'category' => 'Comparación & Decisión',
                'sheetLabel' => 'Comparativa',
                'description' => 'Comparación directa lado a lado entre dos opciones, materiales o tratamientos (ej: Carillas de Resina vs Carillas de Porcelana).',
                'prompt_spec' => 'Diseño comparativo lado a lado A vs B con dos columnas limpias comparando tratamientos odontológicos en español.'
            ],
            [
                'number' => 3,
                'id' => 'secuencial-pasos',
                'name' => 'Secuencial / Paso a paso',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Secuencial / Paso a paso',
                'description' => 'Guía numerada en etapas progresivas claras para guiar al paciente a través de un procedimiento.',
                'prompt_spec' => 'Infografía secuencial de 3 pasos numerados (Paso 1, Paso 2, Paso 3) para colocación de implante dental en español.'
            ],
            [
                'number' => 4,
                'id' => 'narrativa',
                'name' => 'Narrativa',
                'category' => 'Narrativa & Caso',
                'sheetLabel' => 'Narrativa',
                'description' => 'Desarrollo en hilo conductor o historia que conecta la experiencia del paciente con el aprendizaje clínico.',
                'prompt_spec' => 'Lámina narrativa editorial moderna con foto de paciente sonriente y texto de relato clínico en español.'
            ],
            [
                'number' => 5,
                'id' => 'problema-solucion',
                'name' => 'Problema → Solución',
                'category' => 'Comparación & Decisión',
                'sheetLabel' => 'Problema → Solución',
                'description' => 'Identificación precisa de una molestia, dolor o patología del paciente y su solución médica definitiva.',
                'prompt_spec' => 'Diseño dividido en dos bloques: bloque rojo claro para Problema (sensibilidad) y bloque azul para Solución médica en español.'
            ],
            [
                'number' => 6,
                'id' => 'antes-despues',
                'name' => 'Antes → Después',
                'category' => 'Narrativa & Caso',
                'sheetLabel' => 'Antes → Después',
                'description' => 'Transformación estética y funcional de alta fidelidad que evidencia los resultados reales de un tratamiento.',
                'prompt_spec' => 'Transformación clínica Antes y Después de diseño de sonrisa con etiquetas elegantes en español y acabado profesional.'
            ],
            [
                'number' => 7,
                'id' => 'causa-consecuencia',
                'name' => 'Causa → Consecuencia',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Causa → Consecuencia',
                'description' => 'Relación causal que advierte sobre los efectos a largo plazo de no tratar un problema (ej: bruxismo no tratado).',
                'prompt_spec' => 'Diagrama de causa y efecto con flecha conectora: Causa clínica a Consecuencia visible en español.'
            ],
            [
                'number' => 8,
                'id' => 'lista-enumeracion',
                'name' => 'Lista / Enumeración',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Lista / Enumeración',
                'description' => 'Compendio de tips o elementos clave numerados para lectura ágil y alto guardado en redes sociales.',
                'prompt_spec' => 'Lámina de lista limpia con viñetas numéricas modernas destacando 4 recomendaciones de higiene bucal en español.'
            ],
            [
                'number' => 9,
                'id' => 'jerarquia',
                'name' => 'Jerarquía',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Jerarquía',
                'description' => 'Estructuración visual por niveles de importancia, urgencia o prioridad de atención odontológica.',
                'prompt_spec' => 'Pirámide o escala jerárquica médica con 3 niveles de prioridad en cuidado dental en español.'
            ],
            [
                'number' => 10,
                'id' => 'proceso',
                'name' => 'Proceso',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Proceso',
                'description' => 'Flujo continuo que describe cómo se realiza un tratamiento clínico desde la primera consulta hasta el alta.',
                'prompt_spec' => 'Flujograma horizontal moderno de proceso clínico con nodos conectados y tipografía limpia en español.'
            ],
            [
                'number' => 11,
                'id' => 'timeline',
                'name' => 'Timeline / Línea de tiempo',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Timeline / Línea de tiempo',
                'description' => 'Evolución temporal que proyecta la recuperación o el progreso de un tratamiento a lo largo de días o semanas.',
                'prompt_spec' => 'Línea de tiempo cronológica horizontal con hitos (Día 1, Semana 2, Mes 3) en recuperación dental en español.'
            ],
            [
                'number' => 12,
                'id' => 'estadistica-datos',
                'name' => 'Estadística / Datos',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Estadística / Datos',
                'description' => 'Cifras, métricas y porcentajes de alto impacto que aportan autoridad médica comprobada a la publicación.',
                'prompt_spec' => 'Tarjeta con dato estadístico destacado en tipografía gigante (ej: 98% de éxito en implantes) y gráfico circular en español.'
            ],
            [
                'number' => 13,
                'id' => 'infografia',
                'name' => 'Infografía',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Infografía',
                'description' => 'Síntesis gráfica multidisciplinar que combina micro-ilustraciones, datos y textos explicativos rápidos.',
                'prompt_spec' => 'Infografía médica moderna con iconos clínicos, anatomía dental y textos explicativos concisos en español.'
            ],
            [
                'number' => 14,
                'id' => 'diagrama',
                'name' => 'Diagrama',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Diagrama',
                'description' => 'Esquema gráfico o técnico que desglosa los componentes de una pieza dental o aparato ortodóncico.',
                'prompt_spec' => 'Diagrama técnico limpio señalando las partes de un implante dental de titanio con flechas e indicadores en español.'
            ],
            [
                'number' => 15,
                'id' => 'checklist',
                'name' => 'Checklist',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Checklist',
                'description' => 'Lista interactiva de casillas de verificación para que el usuario evalúe sus propios hábitos o síntomas.',
                'prompt_spec' => 'Diseño de checklist con casillas marcadas con tildes verdes y textos de rutina de salud dental en español.'
            ],
            [
                'number' => 16,
                'id' => 'pregunta-respuesta',
                'name' => 'Pregunta → Respuesta',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Pregunta → Respuesta',
                'description' => 'Formato FAQ que responde de forma directa y accesible a las dudas y miedos más habituales en consulta.',
                'prompt_spec' => 'Tarjeta de preguntas frecuentes con burbuja de pregunta del paciente y recuadro con respuesta de la doctora en español.'
            ],
            [
                'number' => 17,
                'id' => 'mito-realidad',
                'name' => 'Mito → Realidad',
                'category' => 'Comparación & Decisión',
                'sheetLabel' => 'Mito → Realidad',
                'description' => 'Desmontaje de creencias populares erróneas mediante evidencia científica y respaldo clínico.',
                'prompt_spec' => 'Contraste visual de Mito (cruz roja) vs Realidad (tilde verde) sobre blanqueamiento dental en español.'
            ],
            [
                'number' => 18,
                'id' => 'beneficios',
                'name' => 'Beneficios',
                'category' => 'Narrativa & Caso',
                'sheetLabel' => 'Beneficios',
                'description' => 'Enfoque positivo centrado en la ganancia de calidad de vida, seguridad y estética que obtiene el paciente.',
                'prompt_spec' => 'Lámina destacando 3 beneficios clave de la ortodoncia invisible con iconos minimalistas en azul y español.'
            ],
            [
                'number' => 19,
                'id' => 'caracteristicas',
                'name' => 'Características',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Características',
                'description' => 'Desglose detallado de las propiedades tecnológicas, materiales y ventajas del equipamiento o técnica.',
                'prompt_spec' => 'Ficha descriptiva moderna con especificaciones técnicas de un tratamiento de estética dental en español.'
            ],
            [
                'number' => 20,
                'id' => 'storytelling',
                'name' => 'Storytelling',
                'category' => 'Narrativa & Caso',
                'sheetLabel' => 'Storytelling',
                'description' => 'Relato emocional y humano centrado en la experiencia de superación y recuperación de la sonrisa de una persona.',
                'prompt_spec' => 'Lámina cinematográfica limpia y humana con retrato cálido y cita de experiencia de paciente en español.'
            ],
            [
                'number' => 21,
                'id' => 'cita-destacada',
                'name' => 'Cita / Frase destacada',
                'category' => 'Narrativa & Caso',
                'sheetLabel' => 'Cita / Frase destacada',
                'description' => 'Declaración de autoridad, filosofía médica o frase inspiradora pronunciada por la odontóloga o referente.',
                'prompt_spec' => 'Composición tipográfica sobria de cita célebre entre comillas gigantes elegantes y firma de la especialista en español.'
            ],
            // Tipos visuales adicionales de alto impacto solicitados
            [
                'number' => 22,
                'id' => 'testimonio-caso',
                'name' => 'Testimonio / Caso de Éxito',
                'category' => 'Narrativa & Caso',
                'sheetLabel' => 'Testimonio / Caso de Éxito',
                'description' => 'Validación social con fotografía real de paciente, 5 estrellas y reseña auténtica de su experiencia.',
                'prompt_spec' => 'Tarjeta testimonial médica con 5 estrellas doradas, avatar de paciente y reseña positiva en español.'
            ],
            [
                'number' => 23,
                'id' => 'dos-and-donts',
                'name' => 'Do\'s & Don\'ts (Qué hacer vs Evitar)',
                'category' => 'Comparación & Decisión',
                'sheetLabel' => 'Do\'s & Don\'ts (Qué hacer vs Evitar)',
                'description' => 'Reglas claras y directas de conducta post-operatoria o de cepillado con código de color verde y rojo.',
                'prompt_spec' => 'Matriz de dos columnas de Lo que sí debes hacer vs Lo que debes evitar post extracción dental en español.'
            ],
            [
                'number' => 24,
                'id' => 'anatomia-clinica',
                'name' => 'Anatomía Clínica / Zona Afectada',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Anatomía Clínica / Zona Afectada',
                'description' => 'Ilustración médica 3D de alta precisión que ubica exactamente el área donde actúa el tratamiento.',
                'prompt_spec' => 'Render anatómico clínico limpio y educativo de la cavidad bucal señalando nervio y encía en español.'
            ],
            [
                'number' => 25,
                'id' => 'verdadero-falso',
                'name' => 'Verdadero o Falso (Quiz Rápido)',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Verdadero o Falso (Quiz Rápido)',
                'description' => 'Formato lúdico de micro-evaluación para fomentar la interacción y retención en las redes sociales.',
                'prompt_spec' => 'Pregunta interactiva estilo quiz de odontología con botones de Verdadero o Falso en español.'
            ],
            [
                'number' => 26,
                'id' => 'ficha-tratamiento',
                'name' => 'Ficha de Tratamiento / Guía Rápida',
                'category' => 'Estructura & Lógica',
                'sheetLabel' => 'Ficha de Tratamiento / Guía Rápida',
                'description' => 'Resumen ejecutivo con duración de cita, nivel de dolor (0/5), anestesia y tiempo de recuperación.',
                'prompt_spec' => 'Ficha técnica médica sintetizada con indicadores: Duración: 45 min, Dolor: Ninguno, Resultados: Inmediatos en español.'
            ],
            [
                'number' => 27,
                'id' => 'senales-alerta',
                'name' => 'Señales de Alerta / Cuándo Consultar',
                'category' => 'Didáctica & Datos',
                'sheetLabel' => 'Señales de Alerta / Cuándo Consultar',
                'description' => 'Síntomas de urgencia médica que educan al paciente para no postergar la consulta odontológica.',
                'prompt_spec' => 'Lámina de advertencia médica preventiva con 3 signos de alarma en encías destacadas en badges en español.'
            ],
            [
                'number' => 28,
                'id' => 'tabla-opciones',
                'name' => 'Tabla de Opciones / Guía de Elección',
                'category' => 'Comparación & Decisión',
                'sheetLabel' => 'Tabla de Opciones / Guía de Elección',
                'description' => 'Matriz comparativa multicriterio (durabilidad, costo, estética) para ayudar al paciente a decidir su tratamiento.',
                'prompt_spec' => 'Tabla comparativa clínica elegante con filas y columnas evaluando diferentes tipos de coronas dentales en español.'
            ],
        ];
    }
}
