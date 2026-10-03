import React from 'react';

interface PaginatorPreviewProps {
  type: string;
  primaryColor?: string;
  accentColor?: string;
  bgColor?: string;
}

export const PaginatorMiniPreview: React.FC<PaginatorPreviewProps> = ({
  type,
  primaryColor = '#16345F',
  accentColor = '#38BDF8',
  bgColor = '#0B132B'
}) => {
  const containerClass = "w-full h-20 rounded-xl p-2.5 flex items-center justify-center relative overflow-hidden select-none border border-slate-700/60 shadow-inner";
  const bgStyle = { backgroundColor: bgColor };

  switch (type) {
    case 'random':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gradient-to-r from-purple-500/20 via-cyan-500/20 to-blue-500/20 border border-cyan-400/40 animate-pulse">
            <span className="text-base">🎲</span>
            <span className="text-[11px] font-bold tracking-wider text-cyan-300 uppercase">Auto Selector</span>
          </div>
        </div>
      );

    case 'num-simple':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute top-2.5 right-3 text-xs font-mono font-bold tracking-wider" style={{ color: accentColor }}>
            03 / 08
          </div>
          <div className="w-16 h-1 rounded bg-slate-700/60" />
        </div>
      );

    case 'num-individual':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute top-2.5 right-3 text-base font-black tracking-tight" style={{ color: accentColor }}>
            03
          </div>
          <div className="w-12 h-1 rounded bg-slate-700/60" />
        </div>
      );

    case 'num-etiqueta':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="px-2.5 py-1 rounded-md text-[10px] font-semibold flex items-center gap-1.5" style={{ backgroundColor: `${primaryColor}CC`, color: '#FFF' }}>
            <span className="font-bold" style={{ color: accentColor }}>02</span>
            <span className="text-slate-400">·</span>
            <span className="tracking-wide">Problema</span>
          </div>
        </div>
      );

    case 'puntos-dots':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5">
            {[0, 1, 2, 3, 4].map((i) => (
              <div
                key={i}
                className="w-2 h-2 rounded-full transition-all"
                style={{
                  backgroundColor: i === 2 ? accentColor : 'rgba(255,255,255,0.2)',
                  transform: i === 2 ? 'scale(1.2)' : 'scale(1)'
                }}
              />
            ))}
          </div>
        </div>
      );

    case 'puntos-destacado':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5">
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-5 h-2 rounded-full shadow-sm" style={{ backgroundColor: accentColor }} />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
          </div>
        </div>
      );

    case 'barra-progreso':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-4/5 h-1.5 rounded-full bg-slate-800 overflow-hidden relative border border-slate-700/50">
            <div className="h-full rounded-full" style={{ width: '60%', backgroundColor: accentColor }} />
          </div>
        </div>
      );

    case 'barra-segmentada':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-11/12 flex items-center gap-1">
            {[0, 1, 2, 3, 4, 5].map((i) => (
              <div
                key={i}
                className="flex-1 h-1 rounded-full"
                style={{
                  backgroundColor: i < 3 ? accentColor : 'rgba(255,255,255,0.18)'
                }}
              />
            ))}
          </div>
        </div>
      );

    case 'barra-numeracion':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-2.5 w-5/6">
            <span className="text-[10px] font-mono font-bold" style={{ color: accentColor }}>03/08</span>
            <div className="flex-1 h-1.5 rounded-full bg-slate-800 overflow-hidden">
              <div className="h-full rounded-full" style={{ width: '45%', backgroundColor: accentColor }} />
            </div>
          </div>
        </div>
      );

    case 'pildoras-capsulas':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5">
            {['01', '02', '03', '04'].map((num, i) => (
              <div
                key={num}
                className="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold"
                style={{
                  backgroundColor: i === 2 ? accentColor : 'rgba(255,255,255,0.06)',
                  color: i === 2 ? '#070D1E' : '#94A3B8',
                  border: i === 2 ? 'none' : '1px solid rgba(255,255,255,0.1)'
                }}
              >
                {num}
              </div>
            ))}
          </div>
        </div>
      );

    case 'circulo-numero':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-md" style={{ backgroundColor: accentColor, color: '#070D1E' }}>
            3
          </div>
        </div>
      );

    case 'numero-cuadrado':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-7 h-7 rounded-md flex items-center justify-center font-mono font-bold text-xs border" style={{ borderColor: accentColor, color: accentColor, backgroundColor: `${primaryColor}66` }}>
            03
          </div>
        </div>
      );

    case 'numero-gigante-watermark':
      return (
        <div className={containerClass} style={bgStyle}>
          <span className="text-5xl font-black opacity-15 select-none font-mono" style={{ color: accentColor }}>
            03
          </span>
          <div className="absolute text-[10px] text-slate-300 font-medium">Contenido Principal</div>
        </div>
      );

    case 'timeline-horizontal':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center w-5/6 relative">
            <div className="absolute left-0 right-0 h-0.5 bg-slate-700 z-0" />
            <div className="w-full flex justify-between relative z-10">
              {['01', '02', '03', '04'].map((step, idx) => (
                <div
                  key={step}
                  className="w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-bold"
                  style={{
                    backgroundColor: idx <= 2 ? accentColor : '#1E293B',
                    color: idx <= 2 ? '#070D1E' : '#64748B'
                  }}
                >
                  {idx + 1}
                </div>
              ))}
            </div>
          </div>
        </div>
      );

    case 'stepper-pasos':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5 text-[9px] font-bold">
            <span className="text-emerald-400">✓ 1</span>
            <span className="text-slate-600">─</span>
            <span className="px-1.5 py-0.5 rounded text-slate-900" style={{ backgroundColor: accentColor }}>● 2</span>
            <span className="text-slate-600">─</span>
            <span className="text-slate-500">○ 3</span>
          </div>
        </div>
      );

    case 'tabs-pestanas':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex border-b border-slate-700 w-full px-2 text-[9px]">
            <div className="px-2 py-1 text-slate-500">Mito</div>
            <div className="px-2 py-1 font-bold border-b-2" style={{ borderColor: accentColor, color: accentColor }}>
              Explicación
            </div>
            <div className="px-2 py-1 text-slate-500">Resultado</div>
          </div>
        </div>
      );

    case 'categorias-semanticas':
      return (
        <div className={containerClass} style={bgStyle}>
          <span className="px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase shadow-sm" style={{ backgroundColor: accentColor, color: '#070D1E' }}>
            VERDAD CLÍNICA
          </span>
        </div>
      );

    case 'breadcrumb-migas':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-[10px] text-slate-400 flex items-center gap-1 font-medium">
            <span>Inicio</span>
            <span className="text-slate-600">›</span>
            <span>Tratamiento</span>
            <span className="text-slate-600">›</span>
            <span className="font-bold" style={{ color: accentColor }}>Paso 3</span>
          </div>
        </div>
      );

    case 'miniaturas-preview':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5">
            {[1, 2, 3, 4].map((n) => (
              <div
                key={n}
                className="w-4 h-6 rounded-sm border"
                style={{
                  borderColor: n === 3 ? accentColor : 'rgba(255,255,255,0.15)',
                  backgroundColor: n === 3 ? `${accentColor}33` : 'rgba(0,0,0,0.3)'
                }}
              />
            ))}
          </div>
        </div>
      );

    case 'flechas-decorativas':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-xs font-mono font-bold tracking-wider flex items-center gap-1.5" style={{ color: accentColor }}>
            <span className="text-slate-500">‹</span>
            <span>03 / 08</span>
            <span className="text-slate-500">›</span>
          </div>
        </div>
      );

    case 'indicador-desliza':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1 text-[11px] font-bold tracking-wide" style={{ color: accentColor }}>
            <span>Deslizá</span>
            <span className="text-sm">»</span>
          </div>
        </div>
      );

    case 'flecha-progresiva':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5 text-xs font-mono font-bold" style={{ color: accentColor }}>
            <span>03</span>
            <div className="w-14 h-0.5 bg-current relative">
              <span className="absolute -right-1 -top-1.5 text-xs">›</span>
            </div>
          </div>
        </div>
      );

    case 'marcador-lateral-vertical':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute right-3 flex flex-col gap-1.5">
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-1.5 h-3 rounded-full" style={{ backgroundColor: accentColor }} />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
          </div>
          <div className="text-[10px] text-slate-500">Margen Lateral</div>
        </div>
      );

    case 'numero-lateral-vertical':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute right-3 flex flex-col items-center text-[10px] font-mono font-bold leading-none" style={{ color: accentColor }}>
            <span>03</span>
            <span className="text-slate-600 my-0.5">─</span>
            <span className="text-slate-500">08</span>
          </div>
        </div>
      );

    case 'paginacion-superior':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute top-2 left-3 right-3 flex justify-between text-[10px] border-b border-slate-800 pb-1">
            <span className="text-slate-400 font-bold uppercase tracking-wider">Odontología</span>
            <span className="font-mono font-bold" style={{ color: accentColor }}>03 / 08</span>
          </div>
        </div>
      );

    case 'paginacion-inferior-clasica':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute bottom-2 text-xs font-mono font-bold" style={{ color: accentColor }}>
            03 / 07
          </div>
        </div>
      );

    case 'paginacion-esquina-discreta':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute top-2 right-2 text-[9px] font-mono text-slate-400 opacity-80">
            03/07
          </div>
        </div>
      );

    case 'footer-integrado':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute bottom-2 left-3 right-3 flex justify-between items-center text-[9px] text-slate-400 border-t border-slate-800 pt-1">
            <span className="font-bold tracking-wider">JM ODONTO</span>
            <span className="font-mono" style={{ color: accentColor }}>03 / 07</span>
          </div>
        </div>
      );

    case 'tipografia-editorial-serif':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex flex-col items-center font-serif text-sm italic" style={{ color: accentColor }}>
            <span>03</span>
            <div className="w-4 h-px bg-slate-500 my-0.5" />
            <span className="text-slate-400 text-xs">07</span>
          </div>
        </div>
      );

    case 'capitulos-narrativos':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="px-2 py-1 rounded bg-slate-800/80 border border-slate-700 text-[10px] font-black tracking-widest uppercase" style={{ color: accentColor }}>
            CAP. 03
          </div>
        </div>
      );

    case 'porcentaje-avance':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-xs font-black font-mono tracking-tight" style={{ color: accentColor }}>
            60% <span className="text-[9px] font-normal text-slate-400">AVANCE</span>
          </div>
        </div>
      );

    case 'progreso-color':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex gap-1">
            <div className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: accentColor }} />
            <div className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: accentColor }} />
            <div className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: accentColor }} />
            <div className="w-2.5 h-2.5 rounded-full bg-slate-700" />
            <div className="w-2.5 h-2.5 rounded-full bg-slate-700" />
          </div>
        </div>
      );

    case 'progreso-bloques':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex gap-1 text-xs" style={{ color: accentColor }}>
            <span>■</span>
            <span>■</span>
            <span>■</span>
            <span className="text-slate-600">□</span>
            <span className="text-slate-600">□</span>
          </div>
        </div>
      );

    case 'progreso-guiones':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-xs font-mono tracking-widest" style={{ color: accentColor }}>
            — — — <span className="text-slate-600">· · ·</span>
          </div>
        </div>
      );

    case 'ficha-calendario':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-10 rounded border border-slate-700 overflow-hidden text-center shadow-sm">
            <div className="text-[7px] font-black uppercase text-slate-900 py-0.5" style={{ backgroundColor: accentColor }}>
              SLIDE
            </div>
            <div className="text-xs font-bold py-1 bg-slate-900 text-white font-mono">03</div>
          </div>
        </div>
      );

    case 'sistema-alfanumerico':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-800/90 border border-slate-700" style={{ color: accentColor }}>
            A-03
          </div>
        </div>
      );

    case 'codigo-indice-marca':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="font-mono text-[10px] font-bold px-2 py-1 rounded bg-slate-900/90 border border-cyan-800/60" style={{ color: accentColor }}>
            JM-03
          </div>
        </div>
      );

    case 'iconos-tematicos':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1.5 text-xs">
            <span className="text-sm">🦷</span>
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
            <div className="w-1.5 h-1.5 rounded-full bg-slate-600" />
          </div>
        </div>
      );

    case 'indicador-narrativo':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-[9px] font-bold flex items-center gap-1" style={{ color: accentColor }}>
            <span>Solución</span>
            <span className="text-slate-500">→</span>
          </div>
        </div>
      );

    case 'pregunta-respuesta':
      return (
        <div className={containerClass} style={bgStyle}>
          <span className="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">
            RESPUESTA #2
          </span>
        </div>
      );

    case 'panoramico-invisible':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-full flex items-center justify-between opacity-50 px-2">
            <div className="w-1/3 h-8 bg-gradient-to-r from-transparent to-cyan-500/30 rounded" />
            <span className="text-[9px] text-slate-400 font-mono">Seamless 360°</span>
            <div className="w-1/3 h-8 bg-gradient-to-l from-transparent to-cyan-500/30 rounded" />
          </div>
        </div>
      );

    case 'linea-metro-estaciones':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-5/6 flex items-center relative">
            <div className="absolute left-0 right-0 h-1 rounded bg-slate-700" />
            <div className="w-full flex justify-between relative z-10">
              <div className="w-2.5 h-2.5 rounded-full bg-slate-400" />
              <div className="w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm" style={{ backgroundColor: accentColor }} />
              <div className="w-2.5 h-2.5 rounded-full bg-slate-400" />
            </div>
          </div>
        </div>
      );

    case 'circulo-radial-donut':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="relative w-8 h-8 flex items-center justify-center">
            <svg className="w-8 h-8 transform -rotate-90">
              <circle cx="16" cy="16" r="13" stroke="currentColor" strokeWidth="2.5" className="text-slate-700" fill="transparent" />
              <circle cx="16" cy="16" r="13" stroke={accentColor} strokeWidth="2.5" strokeDasharray="81.6" strokeDashoffset="32" strokeLinecap="round" fill="transparent" />
            </svg>
            <span className="absolute text-[8px] font-mono font-bold text-white">3/5</span>
          </div>
        </div>
      );

    case 'tiempo-lectura':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="flex items-center gap-1 text-[10px] font-medium text-slate-300">
            <span>⏱️</span>
            <span>Quedan 30s</span>
          </div>
        </div>
      );

    case 'ventana-app-macos':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-full h-full flex flex-col">
            <div className="flex items-center justify-between border-b border-slate-700 pb-1">
              <div className="flex gap-1">
                <div className="w-2 h-2 rounded-full bg-red-500" />
                <div className="w-2 h-2 rounded-full bg-yellow-500" />
                <div className="w-2 h-2 rounded-full bg-green-500" />
              </div>
              <span className="text-[8px] font-mono text-slate-400">[03/08]</span>
            </div>
          </div>
        </div>
      );

    case 'tag-esquina-ribbon':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="absolute top-0 right-0 w-8 h-8 overflow-hidden">
            <div className="absolute top-1.5 -right-3 w-10 text-center text-[7px] font-black text-slate-900 transform rotate-45 shadow-sm" style={{ backgroundColor: accentColor }}>
              #03
            </div>
          </div>
          <div className="text-[10px] text-slate-400">Ribbon Esquina</div>
        </div>
      );

    case 'objeto-dividido-split':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-full flex items-center justify-end relative">
            <div className="text-[9px] text-slate-400 mr-2">Corte 50%</div>
            <div className="w-6 h-10 rounded-l-full border-l-2 border-y-2" style={{ borderColor: accentColor, backgroundColor: `${accentColor}33` }} />
          </div>
        </div>
      );

    case 'cliffhanger-curiosidad':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-[9px] text-center font-medium italic text-slate-300">
            "El error principal viene en la lámina 4 →"
          </div>
        </div>
      );

    case 'profundidad-3d-layered':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="relative flex items-center justify-center">
            <span className="text-4xl font-black opacity-30 select-none absolute -top-4 font-mono" style={{ color: accentColor }}>
              03
            </span>
            <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-600 to-blue-800 border border-cyan-400 relative z-10 flex items-center justify-center text-[9px] font-bold text-white shadow-md">
              Dra
            </div>
          </div>
        </div>
      );

    case 'solapas-carpeta-archivo':
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="w-full flex items-end gap-1 px-1">
            <div className="px-2 py-0.5 rounded-t text-[8px] bg-slate-800 text-slate-500">Doc 1</div>
            <div className="px-2.5 py-1 rounded-t text-[9px] font-bold text-slate-900 shadow-sm" style={{ backgroundColor: accentColor }}>
              Caso 03
            </div>
            <div className="px-2 py-0.5 rounded-t text-[8px] bg-slate-800 text-slate-500">Doc 4</div>
          </div>
        </div>
      );

    default:
      return (
        <div className={containerClass} style={bgStyle}>
          <div className="text-xs font-mono font-bold" style={{ color: accentColor }}>03 / 08</div>
        </div>
      );
  }
};
