---
title: "Frontend SPA (Vite + React 18)"
description: "Estructura de la aplicación web, visor interactivo de carruseles 1024x1536, Brand Kit y panel de control."
tags:
  - frontend
  - react
  - vite
  - tailwind
  - shadcn
---

# 💻 Frontend SPA — TecnoGen Studio

La interfaz de TecnoGen Studio está construida con **Vite + React 18 + Tailwind CSS + Lucide Icons** y sigue principios de alta calidad estética (dark mode editorial, glassmorphism sutil y microinteracciones fluidas).

---

## 📱 Pantallas Principales

1. **Landing Page (`/`):** Presentación del producto, visor interactivo embebido de demostración, calculadora de ROI/precios y FAQ.
2. **Dashboard (`/app/dashboard`):** Contador de créditos disponibles, accesos directos, estadísticas de piezas generadas y lista de contenidos recientes con badges de estado.
3. **Brand Kit (`/app/brands`):** Configuración de paletas de color (primario, acento, fondo), tipografías, posición de logo, biblioteca de assets conectados a Google Drive y reglas de diseño (paginador, presencia de sujeto).
4. **Visor de Aprobación Interactivo (`/app/viewer/{content_id}`):**
   - Renderizado en viewport nativo `1024x1536` (proporción 2:3 `object-fit: contain` sin recortes).
   - Navegación táctil y por teclado entre slides.
   - Acciones por lámina: Aprobar slide individual o **Regenerar slide con feedback** (consume 1 crédito sin alterar las demás láminas).
   - Acciones globales: Descargar ZIP completo o Programar directamente en Metricool.
5. **Programador Metricool (`ScheduleModal.tsx`):**
   - Integración directa con el heatmap de Metricool para mostrar los 3 mejores horarios recomendados (por ejemplo: "Hoy 19:30 (96%)").
   - Selección de redes sociales destino (Instagram, Facebook, LinkedIn).
