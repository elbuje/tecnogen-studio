---
title: "Catálogo de Tipos Visuales & 28 Estructuras Narrativas"
description: "Definición, taxonomía y especificación técnica de las 28 estructuras visuales para carruseles y posts con OpenAI Sunburst (100% en español)."
category: "Estructuras & IA"
tags:
  - visual-types
  - sunburst
  - estructuras
  - carruseles
  - openai
---

# 📐 Catálogo de Tipos Visuales & Estructuras Narrativas

El catálogo de **Tipos Visuales** de TecnoGen Studio establece las 28 plantillas estructurales y diagramáticas utilizadas para organizar la información en las láminas de carruseles clínicos, médicos y odontológicos.

Cada tipo visual define una lógica de composición, distribución de bloques, jerarquía tipográfica y elementos gráficos (iconos, badges, flujos o comparativas) que guían la generación con el motor **`gpt-image-2.5-sunburst`**.

---

## 🗂️ Taxonomía de las 28 Estructuras

### 1. Estructura & Lógica (8 Tipos)
- **01. Conceptual:** Explicación clara de un concepto o fundamento odontológico clave (ej: esmalte dental, osteointegración).
- **03. Secuencial / Paso a paso:** Guía numerada en etapas progresivas claras (`01`, `02`, `03`) para guiar al paciente.
- **08. Lista / Enumeración:** Compendio de tips o elementos clave numerados para lectura ágil y guardado.
- **09. Jerarquía:** Pirámide o escala médica por niveles de importancia o prioridad de atención.
- **10. Proceso:** Flujograma continuo con nodos conectados que describe un tratamiento de inicio a fin.
- **15. Checklist:** Lista interactiva con casillas de verificación (✓) para hábitos o síntomas.
- **19. Características:** Ficha descriptiva moderna con especificaciones técnicas, materiales y propiedades.
- **26. Ficha de Tratamiento / Guía Rápida:** Resumen ejecutivo con duración, molestia (0/5), anestesia y tiempos.

### 2. Comparación & Decisión (6 Tipos)
- **02. Comparativa:** Comparación directa lado a lado A vs B entre dos opciones o materiales (ej: Resina vs Porcelana).
- **05. Problema → Solución:** Contraste en dos bloques: identificación de la molestia (rojo/coral) y solución definitiva (azul).
- **17. Mito → Realidad:** Desmontaje de falsas creencias con código de color (cruz roja ✕ vs tilde verde ✓).
- **23. Do's & Don'ts (Qué hacer vs Evitar):** Matriz de dos columnas de conducta post-operatoria o higiene.
- **28. Tabla de Opciones / Guía de Elección:** Matriz multicriterio evaluando opciones (durabilidad, costo, estética).

### 3. Narrativa & Caso (7 Tipos)
- **04. Narrativa:** Relato clínico editorial que conecta la experiencia del paciente con el aprendizaje.
- **06. Antes → Después:** Transformación estética y funcional de alta fidelidad con split vertical.
- **18. Beneficios:** Enfoque positivo centrado en la ganancia de calidad de vida y estética.
- **20. Storytelling:** Historia humana y emotiva de superación de una persona al recuperar su sonrisa.
- **21. Cita / Frase destacada:** Declaración de autoridad médica entre comillas gigantes elegantes.
- **22. Testimonio / Caso de Éxito:** Validación social con fotografía, 5 estrellas doradas y reseña auténtica.

### 4. Didáctica & Datos (7 Tipos)
- **07. Causa → Consecuencia:** Relación causal que advierte sobre los efectos de no tratar una patología.
- **11. Timeline / Línea de tiempo:** Evolución cronológica horizontal con hitos de recuperación (Día 1, Día 7, etc.).
- **12. Estadística / Datos:** Cifra gigante destacada (ej: 98.5% de éxito) con micrográfico circular.
- **13. Infografía:** Cuadrícula multidisciplinar con micro-iconos clínicos y síntesis rápida.
- **14. Diagrama:** Esquema técnico y funcional señalando partes anatómicas o componentes (implante, corona).
- **16. Pregunta → Respuesta (FAQ):** Formato directo estilo burbuja de pregunta de paciente y respuesta clínica.
- **24. Anatomía Clínica / Zona Afectada:** Render 3D educativo que localiza el área exacta del tratamiento.
- **25. Verdadero o Falso (Quiz Rápido):** Formato interactivo de micro-evaluación para redes sociales.
- **27. Señales de Alerta / Cuándo Consultar:** Síntomas de alarma preventiva destacados en badges de advertencia.

---

## ⚙️ Implementación Técnica

- **Servicio Catálogo:** `App\Services\VisualTypeService` (`getVisualTypesList()`).
- **Comando Generador Sunburst:** `php artisan visual-types:sunburst {--all} {--batch=} {--force}` ([`GenerateVisualTypeSunburstCommand.php`](file:///home/mfmujic/tecnogen-studio/laravel-app/app/Console/Commands/GenerateVisualTypeSunburstCommand.php)).
- **Ubicación de Assets:** `public/assets/visual_types/visual_type_01.png` a `visual_type_28.png`.
- **Persistencia de Selección:** Tabla `brands`, columna JSON `brand_rules` (`tipo_visual`, `default_visual_type`).
- **Vista & Modal Slider:** `resources/views/app/visual_types.blade.php` con swipe táctil, controles por teclado y selección directa.
