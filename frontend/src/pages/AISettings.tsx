import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { Sliders, Sparkles, Check, Key, ShieldCheck, AlertCircle, Cpu, FlaskConical, RefreshCw, XCircle, CheckCircle2 } from 'lucide-react';

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

  // Testing states
  const [testing, setTesting] = useState(false);
  const [testResult, setTestResult] = useState<{
    status: 'success' | 'error';
    message: string;
    image_url?: string;
    model_used?: string;
    duration?: number;
  } | null>(null);

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
    setTestResult(null);
    const available = PROVIDER_MODELS[newProvider]?.models;
    if (available && available.length > 0) {
      setModelName(available[0].id);
    }
  };

  const handleTestModel = async () => {
    if (!apiKey.trim()) {
      alert('Por favor ingresá tu API Key para probar la conexión con el modelo.');
      return;
    }

    try {
      setTesting(true);
      setTestResult(null);
      const res = await api.post('/settings/ai/test', {
        provider,
        model_name: modelName,
        api_key: apiKey.trim(),
        prompt: 'Close-up portrait of a cheerful person with a radiant natural healthy smile, soft studio lighting, high resolution dental editorial photography.'
      });

      setTestResult({
        status: 'success',
        message: res.data.message || 'Prueba completada con éxito.',
        image_url: res.data.image_url,
        model_used: res.data.model_used,
        duration: res.data.duration_seconds
      });
    } catch (err: any) {
      const errMsg = err.response?.data?.detail || err.message || 'Error al comunicarse con el proveedor de IA.';
      setTestResult({
        status: 'error',
        message: errMsg,
        model_used: modelName
      });
    } finally {
      setTesting(false);
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
    } catch (err: any) {
      alert(err.response?.data?.detail || 'Error al guardar la configuración de IA.');
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
              onChange={(e) => {
                setApiKey(e.target.value);
                setTestResult(null);
              }}
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
                onClick={() => {
                  setModelName(m.id);
                  setTestResult(null);
                }}
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
                  onChange={() => {
                    setModelName(m.id);
                    setTestResult(null);
                  }}
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

        {/* Test Result Card */}
        {testResult && (
          <div
            className={`p-6 rounded-3xl border transition-all ${
              testResult.status === 'success'
                ? 'bg-emerald-950/40 border-emerald-500/40 shadow-xl shadow-emerald-500/10'
                : 'bg-rose-950/40 border-rose-500/40 shadow-xl shadow-rose-500/10'
            }`}
          >
            <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
              <div className="flex items-start gap-4">
                {testResult.status === 'success' ? (
                  <div className="p-3 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <CheckCircle2 className="w-6 h-6" />
                  </div>
                ) : (
                  <div className="p-3 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30">
                    <XCircle className="w-6 h-6" />
                  </div>
                )}
                <div className="space-y-1">
                  <div className="flex items-center gap-2 flex-wrap">
                    <h4 className="text-base font-bold text-white">
                      {testResult.status === 'success' ? '¡Conexión y Modelo Operativos!' : 'Error al Probar Modelo'}
                    </h4>
                    <span
                      className={`px-2.5 py-0.5 rounded-full text-xs font-mono font-bold ${
                        testResult.status === 'success'
                          ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                          : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'
                      }`}
                    >
                      Modelo: {testResult.model_used}
                    </span>
                    {testResult.duration && (
                      <span className="text-xs text-slate-400 font-medium">
                        ⏱️ {testResult.duration}s
                      </span>
                    )}
                  </div>
                  <p className="text-xs text-slate-300 max-w-xl">
                    {testResult.message}
                  </p>
                </div>
              </div>

              {/* Imagen de prueba generada (Persona Sonriente) */}
              {testResult.image_url && (
                <div className="relative group self-center md:self-auto">
                  <img
                    src={testResult.image_url}
                    alt="Prueba de Modelo - Persona Sonriente"
                    className="w-24 h-24 md:w-28 md:h-28 rounded-2xl object-cover border-2 border-emerald-400/50 shadow-lg shadow-emerald-500/20"
                  />
                  <div className="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-full bg-black/80 text-[10px] text-emerald-300 font-bold border border-emerald-500/40">
                    OK
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Botones de Acción: Probar y Guardar */}
        <div className="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
          <button
            type="button"
            onClick={handleTestModel}
            disabled={testing || saving || !apiKey.trim()}
            className="w-full sm:w-auto px-6 py-3.5 rounded-2xl font-bold bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-cyan-500/30 hover:border-cyan-400 transition-all flex items-center justify-center gap-2 text-sm disabled:opacity-40"
          >
            {testing ? (
              <>
                <RefreshCw className="w-4 h-4 animate-spin text-cyan-400" />
                <span>Generando imagen de prueba...</span>
              </>
            ) : (
              <>
                <FlaskConical className="w-4 h-4 text-cyan-400" />
                <span>Probar Modelo Seleccionado</span>
              </>
            )}
          </button>

          <button
            type="submit"
            disabled={saving || testing}
            className="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all text-sm disabled:opacity-50"
          >
            {saving ? 'Guardando...' : 'Guardar y Activar Modelo en mi Perfil'}
          </button>
        </div>
      </form>
    </div>
  );
};
