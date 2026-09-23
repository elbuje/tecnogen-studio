import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { Palette, Plus, Check, HardDrive, Share2, Image as ImageIcon } from 'lucide-react';

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

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBrand) return;
    try {
      setSaving(true);
      await api.put(`/brands/${selectedBrand.id}`, {
        primary_color: selectedBrand.primary_color,
        accent_color: selectedBrand.accent_color,
        bg_color: selectedBrand.bg_color,
        font_style_title: selectedBrand.font_style_title,
        font_style_body: selectedBrand.font_style_body,
        logo_position: selectedBrand.logo_position,
        logo_width_px: Number(selectedBrand.logo_width_px),
      });
      alert('¡Identidad de marca actualizada con éxito!');
      await fetchBrands();
    } catch (e) {
      alert('Error al guardar cambios de marca.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="p-8 text-center text-slate-400">Cargando Brand Kit...</div>;
  }

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-extrabold text-white">Brand Kit & Identidad de Marca</h1>
          <p className="text-sm text-slate-400">Configurá logos, paletas y reglas de diseño para tus carruseles.</p>
        </div>
      </div>

      {selectedBrand ? (
        <form onSubmit={handleSave} className="space-y-8">
          {/* Visual Identity Palette */}
          <div className="p-8 rounded-3xl glass-card space-y-6">
            <h2 className="text-lg font-bold text-white flex items-center gap-2">
              <Palette className="w-5 h-5 text-cyan-400" /> Identidad Visual — {selectedBrand.name}
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
                  value={selectedBrand.font_style_title}
                  onChange={(e) => setSelectedBrand({ ...selectedBrand, font_style_title: e.target.value })}
                  className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm"
                >
                  <option value="serif-editorial">Serif Editorial (Playfair Display)</option>
                  <option value="sans-bold">Sans Bold Moderna (Plus Jakarta Sans)</option>
                  <option value="display-modern">Display Minimalista</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Posición del Logo Oficial (Pillow RGBA)
                </label>
                <select
                  value={selectedBrand.logo_position}
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

          {/* Connected Services Info */}
          <div className="p-8 rounded-3xl glass-card space-y-6">
            <h2 className="text-lg font-bold text-white flex items-center gap-2">
              <HardDrive className="w-5 h-5 text-cyan-400" /> Almacenamiento Google Drive (Zero-Storage)
            </h2>
            <div className="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 text-sm text-slate-300 space-y-2">
              <div className="font-semibold text-white">¿Cómo conectar carpetas de Google Drive?</div>
              <p className="text-xs text-slate-400 leading-relaxed">
                Creá una carpeta en tu Google Drive y compartila con permisos de editor a la cuenta de servicio de TecnoGen:
                <code className="text-cyan-300 block my-1 font-mono">drive-bot@tecnogen-studio.iam.gserviceaccount.com</code>
                Luego pegá el ID de la carpeta aquí para que el sistema lea tus fotos y deposite tus carruseles automáticamente.
              </p>
            </div>
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={saving}
              className="px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all text-sm disabled:opacity-50"
            >
              {saving ? 'Guardando...' : 'Guardar Cambios de Marca'}
            </button>
          </div>
        </form>
      ) : (
        <div className="text-slate-400">No hay marcas configuradas.</div>
      )}
    </div>
  );
};
