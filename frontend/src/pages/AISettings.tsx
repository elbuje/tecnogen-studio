import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { Sliders, Sparkles, Check, Key, ShieldCheck, AlertCircle, Cpu } from 'lucide-react';

const PROVIDER_MODELS: Record<string, { label: string; models: { id: string; name: string; desc: string }[] }> = {
  openai: {
    label: 'OpenAI',
    models: [
      { id: 'dall-e-3', name: 'DALL-E 3 (Recomendado)', desc: 'Generación fotorrealista de máxima calidad y comprensión de prompts complejos.' },
      { id: 'gpt-image-2.5-sunburst', name: 'GPT Image 2.5 Sunburst', desc: 'Renderizado de alta resolución con tipografía embebida.' },
      { id: 'dall-e-2', name: 'DALL-E 2', desc: 'Generación rápida estándar.' },
    ],
  },
  flux: {
    label: 'Black Forest Labs (FLUX)',
    models: [
      { id: 'flux-1.1-pro', name: 'FLUX 1.1 Pro', desc: 'Máxima fidelidad fotográfica y estética visual de vanguardia.' },
      { id: 'flux-dev', name: 'FLUX.1 Dev', desc: 'Modelo open-weights de calidad profesional.' },
      { id: 'flux-schnell', name: 'FLUX.1 Schnell', desc: 'Generación de ultra alta velocidad.' },
    ],
  },
  stability: {
    label: 'Stability AI',
    models: [
      { id: 'sd3-large', name: 'Stable Diffusion 3 Large', desc: 'Excelente manejo tipográfico y composición espacial.' },
      { id: 'stable-diffusion-xl-1024-v1-0', name: 'SDXL 1.0', desc: 'Clásico probado de alta resolución 1024x1024.' },
    ],
  },
  google: {
    label: 'Google Cloud (Vertex AI / Imagen)',
    models: [
      { id: 'imagen-3.0-generate-001', name: 'Imagen 3 (Vertex)', desc: 'Modelo generativo insignia de Google Cloud.' },
      { id: 'imagen-3.0-fast-generate-001', name: 'Imagen 3 Fast', desc: 'Optimizado para baja latencia.' },
    ],
  },
  anthropic: {
    label: 'Anthropic',
    models: [
      { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet', desc: 'Especialista en estructuración de copy y guiones.' },
    ],
  },
};

export const AISettings: React.FC = () => {
  const [provider, setProvider] = useState('openai');
  const [apiKey, setApiKey] = useState('');
  const [modelName, setModelName] = useState('dall-e-3');
  const [saving, setSaving] = useState(false);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  const fetchSettings = async () => {
    try {
      const res = await api.get('/settings/ai');
      const active = res.data.find((s: any) => s.is_active && s.category === 'image');
      if (active) {
        setProvider(active.provider);
        setModelName(active.model_name);
        setApiKey(active.api_key_override || '');
      }
    } catch (e) {
      console.error('Error fetching AI settings:', e);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const handleProviderChange = (newProvider: string) => {
    setProvider(newProvider);
    const available = PROVIDER_MODELS[newProvider]?.models;
    if (available && available.length > 0) {
      setModelName(available[0].id);
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!apiKey.trim()) {
      alert('Por favor ingresá la API Key de tu proveedor de IA.');
      return;
    }

    try {
      setSaving(true);
      await api.post('/settings/ai', {
        provider,
        category: 'image',
        model_name: modelName,
        api_key_override: apiKey.trim(),
        is_active: true,
        parameters: { size: '1024x1792', quality: 'standard' },
      });
      setSuccessMsg('¡Configuración de IA guardada en tu perfil con éxito!');
      setTimeout(() => setSuccessMsg(null), 4000);
      await fetchSettings();
    } catch (e) {
      alert('Error al guardar la configuración de IA.');
    } finally {
      setSaving(false);
    }
  };

  const currentModels = PROVIDER_MODELS[provider]?.models || [];

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-8">
      <div className="space-y-1">
        <h1 className="text-2xl font-extrabold text-white flex items-center gap-2.5">
          <Cpu className="w-6 h-6 text-cyan-400" /> Configuración de Motores de IA & Modelos
        </h1>
        <p className="text-sm text-slate-400">
          Configurá la empresa proveedora, tu clave privada de API y seleccioná el modelo activo de la lista.
        </p>
      </div>

      {successMsg && (
        <div className="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-2.5">
          <Check className="w-5 h-5 text-emerald-400 flex-shrink-0" />
          <span>{successMsg}</span>
        </div>
      )}

      <form onSubmit={handleSave} className="p-8 rounded-3xl glass-card space-y-8">
        {/* Paso 1: Proveedor */}
        <div className="space-y-3">
          <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
            1. Seleccionar Empresa / Proveedor de IA
          </label>
          <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
            {Object.entries(PROVIDER_MODELS).map(([key, data]) => (
              <button
                key={key}
                type="button"
                onClick={() => handleProviderChange(key)}
                className={`p-4 rounded-2xl border text-left transition-all ${
                  provider === key
                    ? 'bg-cyan-500/15 border-cyan-400 text-white shadow-lg shadow-cyan-500/10'
                    : 'bg-slate-900/80 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'
                }`}
              >
                <div className="text-sm font-bold">{data.label}</div>
                <div className="text-[10px] text-slate-500 mt-1">
                  {data.models.length} modelos disponibles
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Paso 2: API Key Obligatoria */}
        <div className="space-y-3 pt-4 border-t border-slate-800">
          <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
            2. Ingresar tu API Key de {PROVIDER_MODELS[provider]?.label} (Obligatoria)
          </label>
          <div className="relative">
            <Key className="w-5 h-5 text-slate-500 absolute left-4 top-1/2 -translate-y-1/2" />
            <input
              type="password"
              required
              value={apiKey}
              onChange={(e) => setApiKey(e.target.value)}
              placeholder={`Ingresá tu API Key de ${PROVIDER_MODELS[provider]?.label}...`}
              className="w-full pl-12 pr-4 py-3.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm focus:border-cyan-400 focus:outline-none"
            />
          </div>
          <div className="text-[11px] text-slate-400 flex items-center gap-1.5">
            <ShieldCheck className="w-4 h-4 text-emerald-400" />
            Se guardará de forma segura en tu perfil y será la única clave utilizada para todas tus generaciones.
          </div>
        </div>

        {/* Paso 3: Selector de Modelo según la lista dinámica */}
        <div className="space-y-3 pt-4 border-t border-slate-800">
          <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
            3. Seleccionar Modelo de {PROVIDER_MODELS[provider]?.label}
          </label>
          <div className="space-y-2.5">
            {currentModels.map((m) => (
              <label
                key={m.id}
                onClick={() => setModelName(m.id)}
                className={`flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-all ${
                  modelName === m.id
                    ? 'bg-cyan-500/10 border-cyan-400 text-white shadow-md'
                    : 'bg-slate-900/80 border-slate-800 text-slate-400 hover:border-slate-700'
                }`}
              >
                <input
                  type="radio"
                  name="ai_model"
                  value={m.id}
                  checked={modelName === m.id}
                  onChange={() => setModelName(m.id)}
                  className="mt-1 text-cyan-400"
                />
                <div className="space-y-0.5">
                  <div className="text-sm font-bold text-slate-100">{m.name} <code className="text-[11px] text-cyan-400 font-mono ml-2">({m.id})</code></div>
                  <div className="text-xs text-slate-400">{m.desc}</div>
                </div>
              </label>
            ))}
          </div>
        </div>

        {/* Botón Guardar */}
        <div className="pt-4 border-t border-slate-800 flex justify-end">
          <button
            type="submit"
            disabled={saving}
            className="px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all text-sm disabled:opacity-50"
          >
            {saving ? 'Guardando...' : 'Guardar y Activar Modelo en mi Perfil'}
          </button>
        </div>
      </form>
    </div>
  );
};
