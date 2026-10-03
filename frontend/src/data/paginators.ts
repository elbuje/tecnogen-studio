export interface PaginatorStyle {
  id: string;
  number: number;
  name: string;
  sheetLabel: string;
  category: 'Numeración & Tipografía' | 'Barras & Timelines' | 'Puntos & UI Móvil' | 'Cápsulas & Badges' | 'Pasos & Storytelling' | 'Direccionales & Dinámicos' | 'Conceptuales & Panorámicos';
  description: string;
  previewType: string;
  formulaPrompt: string;
}

export const PAGINATORS_CATALOG: PaginatorStyle[] = [
  {
    id: 'random',
    number: 0,
    name: '🎲 Modo Aleatorio (Random Selector)',
    sheetLabel: 'Aleatorio',
    category: 'Conceptuales & Panorámicos',
    description: 'El motor de IA y el compositor eligen automáticamente el mejor estilo según el tono del tema (clínico, educativo, testimonial o de impacto).',
    previewType: 'random',
    formulaPrompt: 'Paginador dinámico inteligente seleccionado aleatoriamente según el tono del contenido.'
  },
  {
    id: 'num-simple',
    number: 1,
    name: '01. Numeración Simple',
    sheetLabel: '01. Numeración simple (01 / 07)',
    category: 'Numeración & Tipografía',
    description: 'Formato limpio tradicional "01 / 07" o "1 / 7". Máxima legibilidad, sobrio y editorial.',
    previewType: 'num-simple',
    formulaPrompt: 'Paginador discreto en esquina superior derecha con formato numérico "0{slide_num} / 0{total_slides}".'
  },
  {
    id: 'num-individual',
    number: 2,
    name: '02. Número Individual',
    sheetLabel: '02. Número individual (02)',
    category: 'Numeración & Tipografía',
    description: 'Solo muestra la lámina actual ("02") en tipografía de alto peso. Ideal cuando cada slide tiene entidad propia.',
    previewType: 'num-individual',
    formulaPrompt: 'Paginador de número individual grande "0{slide_num}" en esquina superior derecha sin totalizar.'
  },
  {
    id: 'num-etiqueta',
    number: 3,
    name: '03. Número + Etiqueta',
    sheetLabel: '03. Número + etiqueta (02 · Problema)',
    category: 'Pasos & Storytelling',
    description: 'Asocia el número a la etapa conceptual: "02 · Problema", "03 · Solución". Gran orientación pedagógica.',
    previewType: 'num-etiqueta',
    formulaPrompt: 'Paginador estructurado en encabezado: "0{slide_num} · {slide_topic_or_type}" en tipografía limpia.'
  },
  {
    id: 'puntos-dots',
    number: 4,
    name: '04. Puntos / Dots Clásicos',
    sheetLabel: '04. Puntos / dots (● ○ ○ ○ ○)',
    category: 'Puntos & UI Móvil',
    description: 'Fila de círculos idénticos donde los no visitados son translúcidos y el actual sólido.',
    previewType: 'puntos-dots',
    formulaPrompt: 'Paginador al pie: Fila sutil de dots circulares tipo carrusel móvil donde la lámina activa está rellena.'
  },
  {
    id: 'puntos-destacado',
    number: 5,
    name: '05. Puntos con Activo Destacado',
    sheetLabel: '05. Puntos con activo destacado (● • • • •)',
    category: 'Puntos & UI Móvil',
    description: 'El punto actual tiene mayor escala o se convierte en píldora alargada tipo iOS / Material You.',
    previewType: 'puntos-destacado',
    formulaPrompt: 'Paginador de navegación con dots donde el punto activo se transforma en una píldora alargada luminosa de acento.'
  },
  {
    id: 'barra-progreso',
    number: 6,
    name: '06. Barra de Progreso Continua',
    sheetLabel: '06. Barra de progreso (━━━━━━────────)',
    category: 'Barras & Timelines',
    description: 'Línea horizontal delgada de borde a borde cuyo llenado porcentual refleja el avance exacto.',
    previewType: 'barra-progreso',
    formulaPrompt: 'Barra de progreso delgada al pie de borde a borde con llenado continuo porcentual ({slide_num}/{total_slides}).'
  },
  {
    id: 'barra-segmentada',
    number: 7,
    name: '07. Barra Segmentada (Stories)',
    sheetLabel: '07. Barra segmentada (━━ ━━ ━━ ── ──)',
    category: 'Barras & Timelines',
    description: 'Segmentos horizontales superiores idénticos a las Stories de Instagram. Muy natural en redes.',
    previewType: 'barra-segmentada',
    formulaPrompt: 'Borde superior con barra segmentada estilo Instagram Stories de {total_slides} tramos con el tramo {slide_num} activo.'
  },
  {
    id: 'barra-numeracion',
    number: 8,
    name: '08. Barra + Numeración',
    sheetLabel: '08. Barra + numeración (03 / 08 ━━━━━────)',
    category: 'Barras & Timelines',
    description: 'Fusión de precisión cuantitativa ("03 / 08") con una barra de avance dinámica al costado.',
    previewType: 'barra-numeracion',
    formulaPrompt: 'Paginador al pie combinando texto numérico "0{slide_num}/0{total_slides}" con barra de progreso adyacente.'
  },
  {
    id: 'pildoras-capsulas',
    number: 9,
    name: '09. Píldoras o Cápsulas',
    sheetLabel: '09. Píldoras o cápsulas ([01] [02] [03])',
    category: 'Cápsulas & Badges',
    description: 'Conjunto de pastillas redondeadas: la activa tiene color de acento y las demás borde sutil.',
    previewType: 'pildoras-capsulas',
    formulaPrompt: 'Paginador de cápsulas flotantes [01] [02] [03] con esquinas redondeadas y la cápsula activa rellena.'
  },
  {
    id: 'circulo-numero',
    number: 10,
    name: '10. Círculo con Número',
    sheetLabel: '10. Círculo con número (➊ ➋ ➌)',
    category: 'Cápsulas & Badges',
    description: 'Pastilla circular sólida con el número centrado en contraste. Sensación de hito o insignia.',
    previewType: 'circulo-numero',
    formulaPrompt: 'Insignia circular en esquina con fondo de color corporativo y el número {slide_num} destacado en el centro.'
  },
  {
    id: 'numero-cuadrado',
    number: 11,
    name: '11. Número Dentro de Cuadrado',
    sheetLabel: '11. Número dentro de cuadrado ([01])',
    category: 'Cápsulas & Badges',
    description: 'Caja cuadrada con bordes afilados o radio mínimo. Estética arquitectónica, técnica y de precisión.',
    previewType: 'numero-cuadrado',
    formulaPrompt: 'Paginador en caja cuadrada minimalista con borde técnico de 1px conteniendo "0{slide_num}".'
  },
  {
    id: 'numero-gigante-watermark',
    number: 12,
    name: '12. Número Gigante Watermark',
    sheetLabel: '12. Número gigante integrado (Watermark)',
    category: 'Numeración & Tipografía',
    description: 'Número tipográfico enorme ocupando el fondo en baja opacidad o solo contorno (outline).',
    previewType: 'numero-gigante-watermark',
    formulaPrompt: 'Elemento de fondo: Cifra gigante "0{slide_num}" en transparencia muy sutil (watermark) integrada en la composición.'
  },
  {
    id: 'timeline-horizontal',
    number: 13,
    name: '13. Timeline Horizontal',
    sheetLabel: '13. Timeline (01 ── 02 ── 03 ── 04)',
    category: 'Barras & Timelines',
    description: 'Línea de tiempo continua que conecta las estaciones 01, 02, 03 resaltando la estación actual.',
    previewType: 'timeline-horizontal',
    formulaPrompt: 'Timeline horizontal con línea delgada conectando los puntos "01 ── 02 ── 03" resaltando el punto {slide_num}.'
  },
  {
    id: 'stepper-pasos',
    number: 14,
    name: '14. Stepper / Pasos',
    sheetLabel: '14. Stepper / pasos (✓ 1 ─ ● 2 ─ ○ 3)',
    category: 'Pasos & Storytelling',
    description: 'Indica etapas superadas con tilde (✓), paso actual en foco (●) y etapas pendientes (○).',
    previewType: 'stepper-pasos',
    formulaPrompt: 'Stepper interactivo de proceso: etapas previas con check ✓, paso actual {slide_num} resaltado y siguientes en outline.'
  },
  {
    id: 'tabs-pestanas',
    number: 15,
    name: '15. Tabs o Pestañas Web',
    sheetLabel: '15. Tabs o pestañas (Problema | Solución)',
    category: 'Cápsulas & Badges',
    description: 'Solapas de navegación de software o app en la parte superior (ej: Problema | Causa | Solución).',
    previewType: 'tabs-pestanas',
    formulaPrompt: 'Barra superior con pestañas estilo navegador web (Tabs) donde la pestaña del tema actual está seleccionada.'
  },
  {
    id: 'categorias-semanticas',
    number: 16,
    name: '16. Paginación por Categorías',
    sheetLabel: '16. Paginación por categorías (Mito / Realidad)',
    category: 'Pasos & Storytelling',
    description: 'No utiliza números: categoriza cada slide según el rol (MITO, VERDAD, CONSEJO, RESULTADO).',
    previewType: 'categorias-semanticas',
    formulaPrompt: 'Paginador semántico sin números: Badge superior de categoría destacado (ej: "MITO", "REALIDAD", "DIAGNÓSTICO").'
  },
  {
    id: 'breadcrumb-migas',
    number: 17,
    name: '17. Breadcrumb (Migas de Pan)',
    sheetLabel: '17. Breadcrumb (Inicio › Diagnóstico › Tratamiento)',
    category: 'Pasos & Storytelling',
    description: 'Ruta jerárquica con separadores angulares: "Inicio › Diagnóstico › Tratamiento". Muy formal.',
    previewType: 'breadcrumb-migas',
    formulaPrompt: 'Breadcrumb editorial superior con flechas angulares indicando la ruta del tratamiento: "Inicio › Etapa {slide_num}".'
  },
  {
    id: 'miniaturas-preview',
    number: 18,
    name: '18. Miniaturas / Previews',
    sheetLabel: '18. Miniaturas / preview (Mini-cards)',
    category: 'Puntos & UI Móvil',
    description: 'Micro-tarjetas al pie que simulan la vista de diapositivas de un editor o presentación.',
    previewType: 'miniaturas-preview',
    formulaPrompt: 'Fila inferior de mini-tarjetas proporcionales representando los slides con borde de acento en la lámina {slide_num}.'
  },
  {
    id: 'flechas-decorativas',
    number: 19,
    name: '19. Flechas Direccionales',
    sheetLabel: '19. Flechas (← 03 / 08 →)',
    category: 'Direccionales & Dinámicos',
    description: 'Encapsula la numeración entre dos chevrons elegantes invitando a la lectura lateral: "← 03 / 08 →".',
    previewType: 'flechas-decorativas',
    formulaPrompt: 'Paginador de control con chevrons elegantes laterales: "‹  0{slide_num} / 0{total_slides}  ›".'
  },
  {
    id: 'indicador-desliza',
    number: 20,
    name: '20. Indicador "Deslizá"',
    sheetLabel: '20. Indicador Deslizá (Deslizá →)',
    category: 'Direccionales & Dinámicos',
    description: 'Llamado directo al swipe con micro-copy en español ("Deslizá »" o "Seguí leyendo →") al pie derecho.',
    previewType: 'indicador-desliza',
    formulaPrompt: 'Micro-copy en margen inferior derecho invitando al swipe: "Deslizá para leer »" con flecha sutil.'
  },
  {
    id: 'flecha-progresiva',
    number: 21,
    name: '21. Flecha Progresiva',
    sheetLabel: '21. Flecha progresiva (01 ─────────→)',
    category: 'Direccionales & Dinámicos',
    description: 'Combina el número inicial con una línea directriz que termina en flecha, proyectando continuidad.',
    previewType: 'flecha-progresiva',
    formulaPrompt: 'Línea de avance con flecha directriz horizontal: "0{slide_num} ─────────→" guiando la mirada.'
  },
  {
    id: 'marcador-lateral-vertical',
    number: 22,
    name: '22. Marcador Lateral Vertical',
    sheetLabel: '22. Marcador lateral vertical (● ○ ○)',
    category: 'Barras & Timelines',
    description: 'Columna vertical de pequeños dots o rayitas pegada al margen derecho. Libera el pie para logos.',
    previewType: 'marcador-lateral-vertical',
    formulaPrompt: 'Marcador vertical en el margen lateral derecho con dots alineados indicando la altura del slide {slide_num}.'
  },
  {
    id: 'numero-lateral-vertical',
    number: 23,
    name: '23. Número Lateral Vertical',
    sheetLabel: '23. Número lateral vertical (03 — 08 vertical)',
    category: 'Numeración & Tipografía',
    description: 'Numeración apilada verticalmente en un lateral con rotación o trazo divisorio fino.',
    previewType: 'numero-lateral-vertical',
    formulaPrompt: 'Numeración vertical estilizada en margen lateral con numerador superior y denominador inferior en columna.'
  },
  {
    id: 'paginacion-superior',
    number: 24,
    name: '24. Paginación Superior Fija',
    sheetLabel: '24. Paginación superior (Barra / dots arriba)',
    category: 'Puntos & UI Móvil',
    description: 'Toda la navegación se ancla arriba para dejar el tercio inferior 100% libre para llamadas a la acción.',
    previewType: 'paginacion-superior',
    formulaPrompt: 'Navegación compacta anclada en el encabezado superior: "Lámina 0{slide_num} de 0{total_slides}".'
  },
  {
    id: 'paginacion-inferior-clasica',
    number: 25,
    name: '25. Paginación Inferior Clásica',
    sheetLabel: '25. Paginación inferior clásica (03 / 07)',
    category: 'Numeración & Tipografía',
    description: 'El clásico centrado o lateral en el pie: "03 / 07", discreto, seguro y universal.',
    previewType: 'paginacion-inferior-clasica',
    formulaPrompt: 'Paginador clásico en el footer con tipografía neutra sans-serif centrada: "{slide_num} / {total_slides}".'
  },
  {
    id: 'paginacion-esquina-discreta',
    number: 26,
    name: '26. Paginación en Esquina',
    sheetLabel: '26. Paginación en esquina (03/07 discreto)',
    category: 'Numeración & Tipografía',
    description: 'Ubicación milimétrica en el vértice superior o inferior para no tapar fotografías ni elementos médicos.',
    previewType: 'paginacion-esquina-discreta',
    formulaPrompt: 'Paginador minimalista en vértice exterior de 12px de margen con texto semitransparente "0{slide_num}/0{total_slides}".'
  },
  {
    id: 'footer-integrado',
    number: 27,
    name: '27. Paginado Integrado al Footer',
    sheetLabel: '27. Paginado integrado al footer (Marca | 03 / 07)',
    category: 'Cápsulas & Badges',
    description: 'Una sola línea unificada al pie: "JM ODONTOLOGÍA  |  03 / 07  |  @jmodontologia". Cohesión corporativa.',
    previewType: 'footer-integrado',
    formulaPrompt: 'Línea de pie de página unificada institucional: Nombre de marca a la izquierda, barra separadora y "0{slide_num}/0{total_slides}".'
  },
  {
    id: 'tipografia-editorial-serif',
    number: 28,
    name: '28. Tipografía Editorial Serif',
    sheetLabel: '28. Paginación tipográfica editorial (Serif elegante)',
    category: 'Numeración & Tipografía',
    description: 'Dígitos en tipografía Serif de lujo (Playfair / Bodoni) con filete fino. Estilo revista Vogue o Monocle.',
    previewType: 'tipografia-editorial-serif',
    formulaPrompt: 'Paginador con tipografía serif refinada de revista de lujo, números en cursiva/itálica y fino trazo divisorio.'
  },
  {
    id: 'capitulos-narrativos',
    number: 29,
    name: '29. Capítulos Narrativos',
    sheetLabel: '29. Capítulos (Capítulo 01 / Parte 01)',
    category: 'Pasos & Storytelling',
    description: 'Identifica la sección como entrega serial: "CAP. 02", "PARTE II" o "ETAPA 03".',
    previewType: 'capitulos-narrativos',
    formulaPrompt: 'Paginador tipo serie o libro: "CAPÍTULO 0{slide_num}" en pastilla o tipografía espaciada (tracking ancho).'
  },
  {
    id: 'porcentaje-avance',
    number: 30,
    name: '30. Porcentaje de Avance',
    sheetLabel: '30. Porcentaje de avance (60%)',
    category: 'Barras & Timelines',
    description: 'Indica el avance porcentual del tema ("25%", "50%", "75%", "100%"). Sensación de masterclass o curso.',
    previewType: 'porcentaje-avance',
    formulaPrompt: 'Paginador de progreso porcentual explícito: Cifra de porcentaje calculada en esquina "{percent}% COMPLETADO".'
  },
  {
    id: 'progreso-color',
    number: 31,
    name: '31. Progreso Mediante Color',
    sheetLabel: '31. Progreso mediante color (● ● ● ○ ○ ○)',
    category: 'Puntos & UI Móvil',
    description: 'Todos los elementos ya vistos adoptan el color primario de la marca, acumulando masa cromática.',
    previewType: 'progreso-color',
    formulaPrompt: 'Fila de círculos acumulativos donde todas las láminas ya transitadas se iluminan con el color de marca.'
  },
  {
    id: 'progreso-bloques',
    number: 32,
    name: '32. Progreso Mediante Bloques',
    sheetLabel: '32. Progreso mediante bloques (■■■■□□□)',
    category: 'Puntos & UI Móvil',
    description: 'Micro-cuadrados rellenos y vacíos (■ ■ ■ □ □). Apariencia digital, data-driven y moderna.',
    previewType: 'progreso-bloques',
    formulaPrompt: 'Paginador de bloques rectangulares digitales (estilo medidor de batería) con bloques rellenos para láminas vistas.'
  },
  {
    id: 'progreso-guiones',
    number: 33,
    name: '33. Progreso Mediante Guiones',
    sheetLabel: '33. Progreso mediante guiones (— — — · · ·)',
    category: 'Puntos & UI Móvil',
    description: 'Líneas horizontales cortas separadas ("— — — · · ·"). Extremadamente elegante y silencioso.',
    previewType: 'progreso-guiones',
    formulaPrompt: 'Paginador minimalista de guiones horizontales finos con guion activo alargado y guiones inactivos punteados.'
  },
  {
    id: 'ficha-calendario',
    number: 34,
    name: '34. Paginación Ficha / Calendario',
    sheetLabel: '34. Paginación tipo ficha/calendario (03 SLIDE)',
    category: 'Cápsulas & Badges',
    description: 'Caja con encabezado de color y número abajo emulando una hoja de almanaque o ficha clínica.',
    previewType: 'ficha-calendario',
    formulaPrompt: 'Badge tipo calendario de consultorio: encabezado pequeño con etiqueta y número grande debajo en caja contenida.'
  },
  {
    id: 'sistema-alfanumerico',
    number: 35,
    name: '35. Sistema Alfanumérico',
    sheetLabel: '35. Sistema alfanumérico (A01, A02, A03)',
    category: 'Numeración & Tipografía',
    description: 'Prefijo de letra seguido de número: "A01, A02, B01". Da apariencia de inventario, catálogo o patente.',
    previewType: 'sistema-alfanumerico',
    formulaPrompt: 'Paginador de código de catálogo alfanumérico: "A-0{slide_num}" en tipografía monospace moderna.'
  },
  {
    id: 'codigo-indice-marca',
    number: 36,
    name: '36. Código / Índice de Marca',
    sheetLabel: '36. Código / índice de marca (JM-03, GEN-05)',
    category: 'Cápsulas & Badges',
    description: 'Incorpora la sigla institucional al número: "JM-03", "TG-04". Refuerza la identidad sin ocupar espacio de logo.',
    previewType: 'codigo-indice-marca',
    formulaPrompt: 'Paginador con código interno de la clínica: "JM-0{slide_num}" en pastilla técnica de alto contraste.'
  },
  {
    id: 'iconos-tematicos',
    number: 37,
    name: '37. Paginación Mediante Iconos',
    sheetLabel: '37. Paginación mediante iconos (🦷 ○ ○ ○)',
    category: 'Puntos & UI Móvil',
    description: 'El indicador activo se sustituye por un icono representativo (un diente 🦷, un rayo ⚡, un estetoscopio).',
    previewType: 'iconos-tematicos',
    formulaPrompt: 'Fila de navegación donde el slide activo se representa con un pequeño icono dental vectorial 🦷 en lugar de un círculo.'
  },
  {
    id: 'indicador-narrativo',
    number: 38,
    name: '38. Indicador Narrativo',
    sheetLabel: '38. Indicador narrativo (Problema → Causa → Solución)',
    category: 'Pasos & Storytelling',
    description: 'Describe el arco dramático: "1. Síntoma → 2. Causa Oculta → 3. Solución Médica". Storytelling puro.',
    previewType: 'indicador-narrativo',
    formulaPrompt: 'Encabezado narrativo de storytelling: "{slide_num}. {narrative_stage} →" orientando el flujo argumental.'
  },
  {
    id: 'pregunta-respuesta',
    number: 39,
    name: '39. Pregunta → Respuesta',
    sheetLabel: '39. Pregunta → respuesta (Pregunta 01 / Respuesta 01)',
    category: 'Pasos & Storytelling',
    description: 'Empareja láminas por pares dialécticos: "Pregunta #1", "Respuesta de la Dra.", "Pregunta #2".',
    previewType: 'pregunta-respuesta',
    formulaPrompt: 'Badge identificador de par didáctico: "PREGUNTA #{slide_num}" o "RESPUESTA #{slide_num}" en color distintivo.'
  },
  {
    id: 'panoramico-invisible',
    number: 40,
    name: '40. Paginación Invisible (Panorámica)',
    sheetLabel: '40. Paginación invisible / panorámica (Seamless)',
    category: 'Conceptuales & Panorámicos',
    description: 'Sin cifras ni puntos visibles: la composición panorámica continua y los fondos enlazados guían el swipe.',
    previewType: 'panoramico-invisible',
    formulaPrompt: 'Composición panorámica continua (seamless carousel): sin paginadores artificiales de UI, continuidad guiada por el diseño.'
  },
  {
    id: 'linea-metro-estaciones',
    number: 41,
    name: '41. Línea de Metro / Estaciones',
    sheetLabel: '41. Línea de Metro / estaciones (⚪━━🔘━━⚪)',
    category: 'Barras & Timelines',
    description: 'Línea de metro subterráneo con nodos que tienen nombre de parada temática y transbordo.',
    previewType: 'linea-metro-estaciones',
    formulaPrompt: 'Diagrama de línea de metro horizontal con estaciones nombradas, destacando la parada actual con aro exterior.'
  },
  {
    id: 'circulo-radial-donut',
    number: 42,
    name: '42. Círculo Radial / Activity Ring',
    sheetLabel: '42. Círculo de carga radial / donut (Activity Ring)',
    category: 'Barras & Timelines',
    description: 'Anillo circular de actividad (estilo Apple Watch) donde el arco exterior se va cerrando en 360°.',
    previewType: 'circulo-radial-donut',
    formulaPrompt: 'Indicador circular tipo anillo de actividad (radial donut progress) en esquina completando su arco perimetral.'
  },
  {
    id: 'tiempo-lectura',
    number: 43,
    name: '43. Tiempo Estimado de Lectura',
    sheetLabel: '43. Tiempo estimado de lectura (⏱️ 1 min restante)',
    category: 'Direccionales & Dinámicos',
    description: 'Informa el tiempo remanente en segundos: "⏱️ Quedan 30 seg · Deslizá". Reduce la tasa de rebote.',
    previewType: 'tiempo-lectura',
    formulaPrompt: 'Insignia de micro-tiempo: "⏱️ Quedan {(total_slides - slide_num) * 10} seg de lectura" con flecha directriz.'
  },
  {
    id: 'ventana-app-macos',
    number: 44,
    name: '44. Ventana macOS / App Header',
    sheetLabel: '44. Ventana de app macOS / browser (🔴 🟡 🟢)',
    category: 'Cápsulas & Badges',
    description: 'Barra superior de ventana de sistema operativo con los 3 botones semáforo (🔴 🟡 🟢) y paginado en el centro.',
    previewType: 'ventana-app-macos',
    formulaPrompt: 'Barra superior simulando ventana de sistema macOS con tres botones de control y título de documento "[0{slide_num}/0{total_slides}]".'
  },
  {
    id: 'tag-esquina-ribbon',
    number: 45,
    name: '45. Tag de Esquina Doblada / Ribbon',
    sheetLabel: '45. Tag de esquina doblada / ribbon (Cinta #03)',
    category: 'Cápsulas & Badges',
    description: 'Cinta en diagonal de 45° pegada al vértice superior como marcador de libro o etiqueta física de farmacia.',
    previewType: 'tag-esquina-ribbon',
    formulaPrompt: 'Cinta diagonal de 45 grados (corner ribbon) en el vértice superior derecho con el número #{slide_num} troquelado.'
  },
  {
    id: 'objeto-dividido-split',
    number: 46,
    name: '46. Objeto Dividido 50/50 al Borde',
    sheetLabel: '46. Objeto dividido al borde 50/50 (Split Object)',
    category: 'Conceptuales & Panorámicos',
    description: 'Un elemento clínico (un implante, una herramienta o modelo 3D) queda cortado a la mitad en el borde derecho.',
    previewType: 'objeto-dividido-split',
    formulaPrompt: 'Elemento visual protagónico cortado exactamente por la mitad en el margen derecho, induciendo al swipe para ver la otra mitad.'
  },
  {
    id: 'cliffhanger-curiosidad',
    number: 47,
    name: '47. Cliffhanger de Intriga al Pie',
    sheetLabel: '47. Cliffhanger al pie (Intriga de siguiente lámina)',
    category: 'Direccionales & Dinámicos',
    description: 'Frase de misterio enlazada: "Pero el error que comete el 90% está en la siguiente lámina →".',
    previewType: 'cliffhanger-curiosidad',
    formulaPrompt: 'Micro-copy de suspenso y curiosidad (cliffhanger) al pie de la lámina vendiendo el contenido de la lámina siguiente.'
  },
  {
    id: 'profundidad-3d-layered',
    number: 48,
    name: '48. Número con Profundidad 3D',
    sheetLabel: '48. Número con profundidad 3D (Capa detrás de sujeto)',
    category: 'Numeración & Tipografía',
    description: 'La cifra queda en una capa intermedia: detrás del rostro o silueta de la doctora pero delante del fondo.',
    previewType: 'profundidad-3d-layered',
    formulaPrompt: 'Efecto de profundidad espacial: La cifra tipográfica de la lámina se posiciona detrás de la persona pero por delante del fondo.'
  },
  {
    id: 'solapas-carpeta-archivo',
    number: 49,
    name: '49. Solapas de Carpeta de Archivo',
    sheetLabel: '49. Solapas de carpeta de archivo (Filing Tabs)',
    category: 'Cápsulas & Badges',
    description: 'El borde superior simula solapas escalonadas de expediente médico o legajo físico con fichas clínicas.',
    previewType: 'solapas-carpeta-archivo',
    formulaPrompt: 'Borde superior con lengüetas o solapas de carpeta de archivo médico donde la solapa activa destaca en primer plano.'
  }
];
