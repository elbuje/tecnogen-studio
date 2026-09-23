import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { Sliders, Sparkles, Check, Key, Shield } from 'lucide-react';

export const AISettings: React.FC = () => {
  const [settings, setSettings] = useState<any[]>([]);
  const [provider, setProvider] = useState('openai');
  const [modelName, setModelName] = useState('gpt-image-2.5-sunburst');
  const [apiKeyOverride, setApiKeyOverride] = useState('');
  const [saving, setSaving] = useState(false);

  const fetchSettings = async () => {
    try {
      const res = await api.get('/settings/ai');
      setSettings(res.data);
      const active = res.data.find((s: any) => s.is_active && s.category === 'image');
      if (active) {
        setProvider(active.provider);
        setModelName(active.model_name);
        setApiKeyOverride(active.api_key_override || '');
      }
    } catch (e) {
      console.error('Error fetching AI settings:', e);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setSaving(true);
      await api.post('/settings/ai', {
        provider,
        category: 'image',
        model_name: modelName,
        api_key_override: apiKeyOverride || null,
        is_active: true,
        parameters: { size: '1024x1536', quality: 'high' },
      });
      alert('¡Configuración de IA actualizada con éxito!');
      await fetchSettings();
    } catch (e) {
      alert('Error al actualizar configuración.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-8">
      <div className="space-y-2">
        <h1 className="text-2xl font-extrabold text-white">Configuración de Proveedores & Modelos de IA</h1>
        <p className="text-sm text-slate-400">
          Seleccioná la empresa de IA y el modelo generativo utilizado para las imágenes de los carruseles.
        </p>
      </div>

      <form onSubmit={handleSave} className="p-8 rounded-3xl glass-card space-y-6">
        <div>
          <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
            Empresa / Proveedor de IA
          </label>
          <select
            value={provider}
            onChange={(e) => setProvider(e.target.value)}
            className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
          >
            <option value="openai">OpenAI (Image API / DALL-E / Sunburst)</option>
            <option value="stability">Stability AI / SDXL</option>
            <option value="flux">Black Forest Labs / FLUX</option>
            <option value="google">Google Cloud Vertex AI / Imagen 3</option>
            <option value="anthropic">Anthropic (Copy & Prompting)</option>
          </select>
        </div>

        <div>
          <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
            Modelo de Generación de Imagen
          </label>
          <input
            type="text"
            required
            value={modelName}
            onChange={(e) => setModelName(e.target.value)}
            placeholder="gpt-image-2.5-sunburst"
            className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm focus:border-cyan-400 focus:outline-none"
          />
          <div className="text-[11px] text-slate-400 mt-1.5">
            Modelo activo por defecto: <code className="text-cyan-300">gpt-image-2.5-sunburst</code> (o <code className="text-slate-300">dall-e-3</code>).
          </div>
        </div>

        <div>
          <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
            API Key de la Empresa de IA (Opcional - Reemplazo)
          </label>
          <div className="relative">
            <Key className="w-4 h-4 text-slate-500 absolute left-4 top-1/2 -translate-y-1/2" />
            <input
              type="password"
              value={apiKeyOverride}
              onChange={(e) => setApiKeyOverride(e.target.value)}
              placeholder="sk-proj-••••••••••••••••••••"
              className="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm focus:border-cyan-400 focus:outline-none"
            />
          </div>
          <div className="text-[11px] text-slate-400 mt-1.5">
            Si se deja en blanco, utilizará la variable de entorno del servidor.
          </div>
        </div>

        <div className="pt-4 border-t border-slate-800 flex justify-end">
          <button
            type="submit"
            disabled={saving}
            className="px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all text-sm disabled:opacity-50"
          >
            {saving ? 'Guardando...' : 'Aplicar Configuración de Modelo'}
          </button>
        </div>
      </form>
    </div>
  );
};
