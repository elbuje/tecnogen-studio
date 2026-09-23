import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { Palette, Check, Sparkles, Layout, FolderOpen, ArrowRight, BookOpen, Layers } from 'lucide-react';
import { Link } from 'react-router-dom';

interface LayoutOption {
  id: string;
  name: string;
  category: string;
  description: string;
  previewType: string;
}

const LAYOUT_PRESETS: LayoutOption[] = [
  {
    id: 'editorial-top',
    name: 'Editorial Hero (Superior)',
    category: 'Portada & Impacto',
    description: 'Logo superior, gran titular editorial y sujeto/foto central con paginador estilizado.',
    previewType: 'editorial-top',
  },
  {
    id: 'split-horizontal',
    name: 'Split Horizontal (50/50)',
    category: 'Estructura Dual',
    description: 'Mitad superior para fotografía de alta fidelidad, mitad inferior para copy y llamada.',
    previewType: 'split-horizontal',
  },
  {
    id: 'split-vertical',
    name: 'Split Vertical (2 Columnas)',
    category: 'Explicativo',
    description: 'Columna izquierda con texto y beneficios clave; columna derecha con imagen del sujeto.',
    previewType: 'split-vertical',
  },
  {
    id: 'minimal-dark',
    name: 'Minimalista Dark (Tipográfico)',
    category: 'Autoridad & Voz',
    description: 'Titular gigante centrado, contraste limpio, espacio negativo y logo sutil inferior.',
    previewType: 'minimal-dark',
  },
  {
    id: 'testimonial-quote',
    name: 'Cita & Testimonio (Social Proof)',
    category: 'Confianza',
    description: 'Comillas destacadas, badge de 5 estrellas, cita del paciente y foto lateral.',
    previewType: 'testimonial-quote',
  },
  {
    id: 'step-by-step',
    name: 'Paso a Paso (Lista / Checklist)',
    category: 'Educativo',
    description: '3 bloques con numeración estilizada (1, 2, 3), checks de acento y logo superior.',
    previewType: 'step-by-step',
  },
  {
    id: 'before-after',
    name: 'Antes y Después (Comparativa)',
    category: 'Casos Clínicos',
    description: 'Dos módulos comparativos simétricos con etiquetas ANTES y DESPUÉS en acento.',
    previewType: 'before-after',
  },
  {
    id: 'stat-hero',
    name: 'Dato Clave / Estadística Hero',
    category: 'Autoridad',
    description: 'Cifra en tamaño XXL (ej. 98%, +1500), anillo/badge visual y copy de soporte.',
    previewType: 'stat-hero',
  },
  {
    id: 'full-bleed',
    name: 'Full Bleed (Foto Completa + Overlay)',
    category: 'Visual Inmersivo',
    description: 'Fotografía a pantalla completa con gradiente oscuro inferior y textos en alto contraste.',
    previewType: 'full-bleed',
  },
  {
    id: 'cta-conversion',
    name: 'Gran CTA & Conversión',
    category: 'Cierre de Carrusel',
    description: 'Titular de cierre, botón visual simulado "Agendá tu Turno", logo central y redes.',
    previewType: 'cta-conversion',
  },
];

export const BrandKit: React.FC = () => {
  const [brands, setBrands] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedBrand, setSelectedBrand] = useState<any>(null);
  const [saving, setSaving] = useState(false);

  const fetchBrands = async () => {
    try {
      setLoading(true);
      const res = await api.get('/brands');
      setBrands(res.data);
      if (res.data.length > 0) {
        setSelectedBrand(res.data[0]);
      }
    } catch (e) {
      console.error('Error fetching brands:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBrands();
  }, []);

  const handleSave = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!selectedBrand) return;
    try {
      setSaving(true);
      await api.put(`/brands/${selectedBrand.id}`, {
        primary_color: selectedBrand.primary_color,
        accent_color: selectedBrand.accent_color,
        bg_color: selectedBrand.bg_color,
        font_style_title: selectedBrand.font_style_title,
        font_style_body: selectedBrand.font_style_body,
        layout_preset: selectedBrand.layout_preset || 'editorial-top',
        logo_position: selectedBrand.logo_position,
        logo_width_px: Number(selectedBrand.logo_width_px || 180),
      });
      alert('¡Identidad y Composición de Marca guardadas con éxito!');
      await fetchBrands();
    } catch (e) {
      alert('Error al guardar cambios de marca.');
    } finally {
      setSaving(false);
    }
  };

  const renderLayoutMiniWireframe = (presetId: string, isSelected: boolean) => {
    const primary = selectedBrand?.primary_color || '#16345F';
    const accent = selectedBrand?.accent_color || '#7DD3FC';
    const bg = selectedBrand?.bg_color || '#0B1E38';

    switch (presetId) {
      case 'editorial-top':
        return (
          <div className="w-full h-32 rounded-xl p-2.5 flex flex-col justify-between" style={{ backgroundColor: bg }}>
            <div className="flex justify-between items-center">
              <div className="w-8 h-2 rounded" style={{ backgroundColor: accent }} />
              <div className="w-4 h-1 rounded bg-slate-500" />
            </div>
            <div className="space-y-1 my-1">
              <div className="w-3/4 h-2.5 rounded font-bold" style={{ backgroundColor: primary }} />
              <div className="w-1/2 h-2 rounded bg-slate-400 opacity-80" />
            </div>
            <div className="w-full h-12 rounded-lg flex items-center justify-center border border-dashed border-slate-600 bg-slate-900/60">
              <span className="text-[9px] text-slate-400">Foto / Sujeto</span>
            </div>
            <div className="flex justify-center gap-1 mt-1">
              <div className="w-2 h-1 rounded-full" style={{ backgroundColor: accent }} />
              <div className="w-1 h-1 rounded-full bg-slate-600" />
              <div className="w-1 h-1 rounded-full bg-slate-600" />
            </div>
          </div>
        );

      case 'split-horizontal':
        return (
          <div className="w-full h-32 rounded-xl overflow-hidden flex flex-col" style={{ backgroundColor: bg }}>
            <div className="w-full h-16 flex items-center justify-center bg-slate-800 border-b border-slate-700">
              <span className="text-[9px] text-slate-300">Imagen Superior (50%)</span>
            </div>
            <div className="p-2.5 flex-1 flex flex-col justify-between">
              <div className="w-3/4 h-2 rounded" style={{ backgroundColor: accent }} />
              <div className="w-full h-1.5 rounded bg-slate-400 opacity-60" />
              <div className="flex justify-between items-center">
                <div className="w-6 h-1.5 rounded" style={{ backgroundColor: primary }} />
                <div className="w-3 h-1 rounded bg-slate-500" />
              </div>
            </div>
          </div>
        );

      case 'split-vertical':
        return (
          <div className="w-full h-32 rounded-xl p-2 flex gap-2" style={{ backgroundColor: bg }}>
            <div className="w-1/2 flex flex-col justify-between py-1">
              <div className="w-6 h-1.5 rounded" style={{ backgroundColor: accent }} />
              <div className="space-y-1">
                <div className="w-full h-2 rounded" style={{ backgroundColor: primary }} />
                <div className="w-4/5 h-1.5 rounded bg-slate-400" />
                <div className="w-3/5 h-1.5 rounded bg-slate-400 opacity-60" />
              </div>
              <div className="w-8 h-2 rounded-full" style={{ backgroundColor: accent }} />
            </div>
            <div className="w-1/2 rounded-lg flex items-center justify-center bg-slate-800 border border-slate-700">
              <span className="text-[9px] text-slate-400 text-center px-1">Foto Sujeto</span>
            </div>
          </div>
        );

      case 'minimal-dark':
        return (
          <div className="w-full h-32 rounded-xl p-3 flex flex-col justify-between items-center text-center" style={{ backgroundColor: bg }}>
            <div className="w-8 h-1.5 rounded" style={{ backgroundColor: accent }} />
            <div className="space-y-1.5 w-full flex flex-col items-center">
              <div className="w-5/6 h-3 rounded font-black" style={{ backgroundColor: primary }} />
              <div className="w-3/4 h-2 rounded bg-slate-300" />
              <div className="w-1/2 h-1.5 rounded bg-slate-500" />
            </div>
            <div className="w-12 h-1 rounded bg-slate-600" />
          </div>
        );

      case 'testimonial-quote':
        return (
          <div className="w-full h-32 rounded-xl p-2.5 flex flex-col justify-between" style={{ backgroundColor: bg }}>
            <div className="flex justify-between items-center">
              <span className="text-xl font-serif leading-none" style={{ color: accent }}>“</span>
              <div className="flex gap-0.5 text-amber-400 text-[8px]">★★★★★</div>
            </div>
            <div className="space-y-1 my-1">
              <div className="w-full h-2 rounded bg-slate-200" />
              <div className="w-5/6 h-2 rounded bg-slate-300 opacity-80" />
            </div>
            <div className="flex items-center gap-2 pt-1 border-t border-slate-700/60">
              <div className="w-5 h-5 rounded-full bg-slate-600 flex-shrink-0" />
              <div className="w-16 h-1.5 rounded" style={{ backgroundColor: accent }} />
            </div>
          </div>
        );

      case 'step-by-step':
        return (
          <div className="w-full h-32 rounded-xl p-2 flex flex-col justify-between" style={{ backgroundColor: bg }}>
            <div className="flex justify-between items-center mb-1">
              <div className="w-10 h-1.5 rounded" style={{ backgroundColor: accent }} />
              <div className="w-4 h-1 rounded bg-slate-500" />
            </div>
            <div className="space-y-1.5 flex-1">
              {[1, 2, 3].map((num) => (
                <div key={num} className="flex items-center gap-1.5 p-1 rounded bg-slate-900/70 border border-slate-800">
                  <div className="w-3 h-3 rounded-full text-[8px] flex items-center justify-center font-bold text-slate-900" style={{ backgroundColor: accent }}>
                    {num}
                  </div>
                  <div className="w-3/4 h-1.5 rounded bg-slate-300" />
                </div>
              ))}
            </div>
          </div>
        );

      case 'before-after':
        return (
          <div className="w-full h-32 rounded-xl p-2 flex flex-col justify-between" style={{ backgroundColor: bg }}>
            <div className="w-12 h-1.5 rounded mx-auto mb-1" style={{ backgroundColor: accent }} />
            <div className="grid grid-cols-2 gap-1.5 flex-1">
              <div className="rounded bg-slate-900/80 border border-red-900/40 p-1 flex flex-col justify-between">
                <span className="text-[7px] font-bold text-red-400 uppercase">Antes</span>
                <div className="w-full h-6 rounded bg-slate-800 flex items-center justify-center text-[7px] text-slate-500">Foto</div>
                <div className="w-3/4 h-1 rounded bg-slate-400" />
              </div>
              <div className="rounded bg-slate-900/80 border border-emerald-900/40 p-1 flex flex-col justify-between">
                <span className="text-[7px] font-bold text-emerald-400 uppercase">Después</span>
                <div className="w-full h-6 rounded bg-slate-800 flex items-center justify-center text-[7px] text-emerald-400">Foto</div>
                <div className="w-3/4 h-1 rounded" style={{ backgroundColor: accent }} />
              </div>
            </div>
          </div>
        );

      case 'stat-hero':
        return (
          <div className="w-full h-32 rounded-xl p-2.5 flex flex-col justify-between items-center text-center" style={{ backgroundColor: bg }}>
            <div className="w-8 h-1.5 rounded" style={{ backgroundColor: primary }} />
            <div className="my-auto">
              <span className="text-2xl font-extrabold tracking-tight" style={{ color: accent }}>98%</span>
              <div className="w-20 h-1.5 rounded bg-slate-300 mx-auto mt-1" />
            </div>
            <div className="w-16 h-1 rounded bg-slate-600" />
          </div>
        );

      case 'full-bleed':
        return (
          <div className="w-full h-32 rounded-xl relative overflow-hidden flex flex-col justify-end p-2.5 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-700/50">
            <div className="absolute top-2 left-2 w-6 h-1.5 rounded" style={{ backgroundColor: accent }} />
            <div className="space-y-1 relative z-10">
              <div className="w-4/5 h-2.5 rounded font-bold" style={{ backgroundColor: accent }} />
              <div className="w-3/5 h-1.5 rounded bg-slate-200" />
            </div>
          </div>
        );

      case 'cta-conversion':
        return (
          <div className="w-full h-32 rounded-xl p-2.5 flex flex-col justify-between items-center text-center" style={{ backgroundColor: bg }}>
            <div className="w-8 h-1.5 rounded" style={{ backgroundColor: primary }} />
            <div className="space-y-1 w-full flex flex-col items-center">
              <div className="w-3/4 h-2.5 rounded font-bold text-white" style={{ backgroundColor: accent }} />
              <div className="w-1/2 h-1.5 rounded bg-slate-400" />
            </div>
            <div className="w-24 h-4 rounded-md flex items-center justify-center text-[8px] font-bold text-slate-900 shadow-md" style={{ backgroundColor: accent }}>
              Agendá tu Turno →
            </div>
            <div className="w-12 h-1 rounded bg-slate-600" />
          </div>
        );

      default:
        return <div className="w-full h-32 rounded-xl bg-slate-800" />;
    }
  };

  if (loading) {
    return <div className="p-8 text-center text-slate-400">Cargando Brand Kit...</div>;
  }

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white flex items-center gap-3">
            <Palette className="w-7 h-7 text-cyan-400" /> Brand Kit & Identidad Visual
          </h1>
          <p className="text-sm text-slate-400">
            Configurá paletas, tipografías y seleccioná la distribución visual de tus láminas extraídas de tu manual de marca.
          </p>
        </div>

        <div className="flex items-center gap-3">
          <Link
            to="/app/integrations"
            className="px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-900/80 hover:bg-slate-800 text-xs font-semibold text-slate-300 flex items-center gap-2 transition-all"
          >
            <FolderOpen className="w-4 h-4 text-cyan-400" /> Carpetas Google Drive
          </Link>
          <button
            onClick={() => handleSave()}
            disabled={saving}
            className="px-6 py-2.5 rounded-xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all text-sm disabled:opacity-50 flex items-center gap-2"
          >
            <Check className="w-4 h-4" /> {saving ? 'Guardando...' : 'Guardar Todo'}
          </button>
        </div>
      </div>

      {selectedBrand ? (
        <form onSubmit={handleSave} className="space-y-8">
          {/* Banner de Sincronización con Manual de Marca de Drive */}
          <div className="p-6 rounded-3xl bg-gradient-to-r from-slate-900 via-cyan-950/30 to-slate-900 border border-cyan-500/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div className="flex items-start gap-4">
              <div className="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center flex-shrink-0">
                <BookOpen className="w-6 h-6 text-cyan-400" />
              </div>
              <div>
                <h3 className="text-sm font-bold text-white flex items-center gap-2">
                  Extraído del Manual de Marca (Google Drive)
                  <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                    Sincronizado
                  </span>
                </h3>
                <p className="text-xs text-slate-400 mt-0.5">
                  Los colores y tipografías se aplican automáticamente a cada carrusel respetando las reglas de diseño y composición.
                </p>
              </div>
            </div>
            <Link
              to="/app/integrations"
              className="text-xs font-semibold text-cyan-400 hover:text-cyan-300 flex items-center gap-1 bg-cyan-950/40 px-3 py-1.5 rounded-lg border border-cyan-800/40 transition-all flex-shrink-0"
            >
              Ver carpeta de Manuales en Drive <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>

          {/* 10 Tarjetas de Distribución Visual y Composición */}
          <div className="p-8 rounded-3xl glass-card space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
              <div>
                <h2 className="text-lg font-bold text-white flex items-center gap-2">
                  <Layout className="w-5 h-5 text-cyan-400" /> Distribución y Composición Gráfica (10 Estilos)
                </h2>
                <p className="text-xs text-slate-400">
                  Seleccioná la estructura de diseño predeterminada que guiará la distribución de textos, imágenes y logos en tus diapositivas.
                </p>
              </div>
              <div className="text-xs font-semibold px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300 self-start sm:self-auto">
                Seleccionado: <span className="text-cyan-400 font-bold">{LAYOUT_PRESETS.find(p => p.id === (selectedBrand.layout_preset || 'editorial-top'))?.name}</span>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
              {LAYOUT_PRESETS.map((preset) => {
                const isSelected = (selectedBrand.layout_preset || 'editorial-top') === preset.id;
                return (
                  <div
                    key={preset.id}
                    onClick={() => setSelectedBrand({ ...selectedBrand, layout_preset: preset.id })}
                    className={`cursor-pointer rounded-2xl p-3 border transition-all duration-200 flex flex-col justify-between group relative overflow-hidden ${
                      isSelected
                        ? 'bg-slate-900 border-cyan-400 shadow-lg shadow-cyan-500/20 ring-1 ring-cyan-400'
                        : 'bg-slate-950/60 border-slate-800 hover:border-slate-700 hover:bg-slate-900/60'
                    }`}
                  >
                    {isSelected && (
                      <div className="absolute top-2 right-2 z-10 w-5 h-5 rounded-full bg-cyan-400 text-slate-950 flex items-center justify-center shadow-md">
                        <Check className="w-3.5 h-3.5 stroke-[3]" />
                      </div>
                    )}

                    <div className="mb-2">
                      <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                        {preset.category}
                      </div>
                      <div className="text-xs font-bold text-white group-hover:text-cyan-300 transition-colors line-clamp-1">
                        {preset.name}
                      </div>
                    </div>

                    {/* Mini Wireframe Preview */}
                    <div className="my-2 shadow-inner rounded-xl overflow-hidden border border-slate-800">
                      {renderLayoutMiniWireframe(preset.id, isSelected)}
                    </div>

                    <p className="text-[11px] text-slate-400 leading-tight mt-1 line-clamp-2">
                      {preset.description}
                    </p>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Paleta de Colores y Tipografías */}
          <div className="p-8 rounded-3xl glass-card space-y-6">
            <h2 className="text-lg font-bold text-white flex items-center gap-2">
              <Palette className="w-5 h-5 text-cyan-400" /> Paleta de Colores de la Marca
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Color Primario
                </label>
                <div className="flex items-center gap-3">
                  <input
                    type="color"
                    value={selectedBrand.primary_color || '#16345F'}
                    onChange={(e) => setSelectedBrand({ ...selectedBrand, primary_color: e.target.value })}
                    className="w-12 h-12 rounded-xl bg-transparent cursor-pointer border border-slate-700"
                  />
                  <input
                    type="text"
                    value={selectedBrand.primary_color}
                    onChange={(e) => setSelectedBrand({ ...selectedBrand, primary_color: e.target.value })}
                    className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm uppercase"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Color de Acento
                </label>
                <div className="flex items-center gap-3">
                  <input
                    type="color"
                    value={selectedBrand.accent_color || '#7DD3FC'}
                    onChange={(e) => setSelectedBrand({ ...selectedBrand, accent_color: e.target.value })}
                    className="w-12 h-12 rounded-xl bg-transparent cursor-pointer border border-slate-700"
                  />
                  <input
                    type="text"
                    value={selectedBrand.accent_color}
                    onChange={(e) => setSelectedBrand({ ...selectedBrand, accent_color: e.target.value })}
                    className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm uppercase"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Color de Fondo
                </label>
                <div className="flex items-center gap-3">
                  <input
                    type="color"
                    value={selectedBrand.bg_color || '#0B1E38'}
                    onChange={(e) => setSelectedBrand({ ...selectedBrand, bg_color: e.target.value })}
                    className="w-12 h-12 rounded-xl bg-transparent cursor-pointer border border-slate-700"
                  />
                  <input
                    type="text"
                    value={selectedBrand.bg_color}
                    onChange={(e) => setSelectedBrand({ ...selectedBrand, bg_color: e.target.value })}
                    className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm uppercase"
                  />
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-800">
              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Tipografía de Títulos
                </label>
                <select
                  value={selectedBrand.font_style_title || 'serif-editorial'}
                  onChange={(e) => setSelectedBrand({ ...selectedBrand, font_style_title: e.target.value })}
                  className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm"
                >
                  <option value="serif-editorial">Serif Editorial (Playfair Display)</option>
                  <option value="sans-bold">Sans Bold Moderna (Plus Jakarta Sans)</option>
                  <option value="display-modern">Display Minimalista (Cinzel / Outfit)</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Posición del Logo Oficial (Pillow RGBA)
                </label>
                <select
                  value={selectedBrand.logo_position || 'top-left'}
                  onChange={(e) => setSelectedBrand({ ...selectedBrand, logo_position: e.target.value })}
                  className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm"
                >
                  <option value="top-left">Superior Izquierda (Top-Left)</option>
                  <option value="top-right">Superior Derecha (Top-Right)</option>
                  <option value="top-center">Superior Centro</option>
                  <option value="bottom-left">Inferior Izquierda</option>
                  <option value="bottom-right">Inferior Derecha</option>
                </select>
              </div>
            </div>
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={saving}
              className="px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all text-sm disabled:opacity-50 flex items-center gap-2"
            >
              <Check className="w-4 h-4" /> {saving ? 'Guardando...' : 'Guardar Cambios de Brand Kit'}
            </button>
          </div>
        </form>
      ) : (
        <div className="text-slate-400">No hay marcas configuradas.</div>
      )}
    </div>
  );
};
