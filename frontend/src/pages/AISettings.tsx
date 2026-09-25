import React, { useState, useEffect } from 'react';
import api from '../api/client';
import {
  Sparkles,
  Check,
  Key,
  ShieldCheck,
  AlertCircle,
  Cpu,
  FlaskConical,
  RefreshCw,
  XCircle,
  CheckCircle2,
  ChevronDown,
  Layers,
  Image as ImageIcon,
  MessageSquare,
  Search
} from 'lucide-react';

interface AIModel {
  id: string;
  name: string;
  type: 'image' | 'chat' | 'audio' | 'embedding' | 'other';
  description?: string;
  owned_by?: string;
}

const PROVIDERS: Record<string, { label: string; defaultModel: string; placeholder: string }> = {
  openai: {
    label: 'OpenAI (Platform)',
    defaultModel: 'dall-e-3',
    placeholder: 'sk-proj-... o tu clave de API de OpenAI',
  },
  flux: {
    label: 'Black Forest Labs (FLUX)',
    defaultModel: 'flux-1.1-pro',
    placeholder: 'bfl_... clave de API de Black Forest Labs',
  },
  stability: {
    label: 'Stability AI',
    defaultModel: 'sd3-large',
    placeholder: 'sk-... clave de API de Stability AI',
  },
  google: {
    label: 'Google Cloud (Vertex AI / Gemini)',
    defaultModel: 'imagen-3.0-generate-001',
    placeholder: 'API Key de Google AI Studio / Vertex',
  },
  anthropic: {
    label: 'Anthropic',
    defaultModel: 'claude-3-5-sonnet-20241022',
    placeholder: 'sk-ant-... clave de Anthropic',
  },
};

export const AISettings: React.FC = () => {
  const [provider, setProvider] = useState('openai');
  const [apiKey, setApiKey] = useState('');
  const [modelName, setModelName] = useState('dall-e-3');
  const [modelsList, setModelsList] = useState<AIModel[]>([]);
  const [loadingModels, setLoadingModels] = useState(false);
  const [modelsSource, setModelsSource] = useState<'live_api' | 'catalog' | null>(null);
  const [filterType, setFilterType] = useState<string>('all');
  const [searchFilter, setSearchFilter] = useState('');
  const [isCustomModel, setIsCustomModel] = useState(false);

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
        // Cargar modelos con la clave guardada
        if (active.api_key_override) {
          fetchModelsForProvider(active.provider, active.api_key_override);
        } else {
          fetchModelsForProvider(active.provider, '');
        }
      } else {
        fetchModelsForProvider('openai', '');
      }
    } catch (e) {
      console.error('Error fetching AI settings:', e);
      fetchModelsForProvider('openai', '');
    }
  };

  const fetchModelsForProvider = async (targetProvider: string, targetKey: string) => {
    try {
      setLoadingModels(true);
      const res = await api.post('/settings/ai/fetch-models', {
        provider: targetProvider,
        api_key: targetKey.trim() || undefined,
      });

      if (res.data?.models) {
        setModelsList(res.data.models);
        setModelsSource(res.data.source);
        
        // Si el modelo actual no está en la lista y no es custom, seleccionar el primero de tipo imagen o el primero de la lista
        const exists = res.data.models.some((m: AIModel) => m.id === modelName);
        if (!exists && !isCustomModel) {
          const firstImage = res.data.models.find((m: AIModel) => m.type === 'image');
          if (firstImage) {
            setModelName(firstImage.id);
          } else if (res.data.models.length > 0) {
            setModelName(res.data.models[0].id);
          }
        }
      }
    } catch (e: any) {
      console.warn('No se pudieron consultar modelos en vivo:', e);
      setModelsSource('catalog');
    } finally {
      setLoadingModels(false);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const handleProviderChange = (newProvider: string) => {
    setProvider(newProvider);
    setTestResult(null);
    setIsCustomModel(false);
    const def = PROVIDERS[newProvider]?.defaultModel || '';
    setModelName(def);
    fetchModelsForProvider(newProvider, apiKey);
  };

  const handleSyncModels = () => {
    fetchModelsForProvider(provider, apiKey);
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

  // Filtrar modelos
  const filteredModels = modelsList.filter((m) => {
    const matchesType = filterType === 'all' || m.type === filterType;
    const matchesSearch =
      m.id.toLowerCase().includes(searchFilter.toLowerCase()) ||
      m.name.toLowerCase().includes(searchFilter.toLowerCase());
    return matchesType && matchesSearch;
  });

  const selectedModelObj = modelsList.find((m) => m.id === modelName);

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-8">
      <div className="space-y-1">
        <h1 className="text-2xl font-extrabold text-white flex items-center gap-2.5">
          <Cpu className="w-6 h-6 text-cyan-400" /> Configuración de Motores de IA & Modelos
        </h1>
        <p className="text-sm text-slate-400">
          Ingresá tu API Key para sincronizar el combo de modelos directamente desde la plataforma del proveedor.
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
            {Object.entries(PROVIDERS).map(([key, data]) => (
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
                  {key === 'openai' ? 'API Oficial + Platform' : 'Generación IA'}
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Paso 2: API Key */}
        <div className="space-y-3 pt-4 border-t border-slate-800">
          <div className="flex items-center justify-between">
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
              2. Ingresar tu API Key de {PROVIDERS[provider]?.label}
            </label>
            <button
              type="button"
              onClick={handleSyncModels}
              disabled={loadingModels || !apiKey.trim()}
              className="text-xs text-cyan-400 hover:text-cyan-300 flex items-center gap-1.5 font-medium disabled:opacity-40"
            >
              <RefreshCw className={`w-3.5 h-3.5 ${loadingModels ? 'animate-spin' : ''}`} />
              <span>Sincronizar Modelos desde API</span>
            </button>
          </div>

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
              onBlur={() => {
                if (apiKey.trim()) {
                  fetchModelsForProvider(provider, apiKey);
                }
              }}
              placeholder={PROVIDERS[provider]?.placeholder || 'Ingresá tu API Key...'}
              className="w-full pl-12 pr-4 py-3.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-sm focus:border-cyan-400 focus:outline-none"
            />
          </div>
          <div className="text-[11px] text-slate-400 flex items-center gap-1.5">
            <ShieldCheck className="w-4 h-4 text-emerald-400" />
            Tu clave se almacena cifrada en tu cuenta y se utiliza para invocar los endpoints oficiales.
          </div>
        </div>

        {/* Paso 3: Combo / Selector de Modelos */}
        <div className="space-y-4 pt-4 border-t border-slate-800">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
              3. Selector de Modelo ({modelsList.length} detectados {modelsSource === 'live_api' ? '⚡ en vivo desde tu cuenta' : '📦 catálogo'})
            </label>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => setIsCustomModel(!isCustomModel)}
                className="text-xs text-slate-400 hover:text-cyan-400 transition-colors"
              >
                {isCustomModel ? '← Volver al selector combo' : '✏️ Ingresar ID manual'}
              </button>
            </div>
          </div>

          {!isCustomModel ? (
            <div className="space-y-3">
              {/* Filtros rápidos */}
              <div className="flex flex-wrap items-center gap-2">
                <div className="relative flex-1 min-w-[200px]">
                  <Search className="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    value={searchFilter}
                    onChange={(e) => setSearchFilter(e.target.value)}
                    placeholder="Filtrar por nombre o ID (ej: dall-e, gpt-4o)..."
                    className="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white focus:border-cyan-400 focus:outline-none"
                  />
                </div>

                <div className="flex items-center gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800">
                  <button
                    type="button"
                    onClick={() => setFilterType('all')}
                    className={`px-3 py-1 rounded-lg text-xs font-medium transition-all ${
                      filterType === 'all' ? 'bg-cyan-500/20 text-cyan-300' : 'text-slate-400 hover:text-white'
                    }`}
                  >
                    Todos ({modelsList.length})
                  </button>
                  <button
                    type="button"
                    onClick={() => setFilterType('image')}
                    className={`px-3 py-1 rounded-lg text-xs font-medium transition-all flex items-center gap-1 ${
                      filterType === 'image' ? 'bg-cyan-500/20 text-cyan-300' : 'text-slate-400 hover:text-white'
                    }`}
                  >
                    <ImageIcon className="w-3 h-3" />
                    Imágenes ({modelsList.filter((m) => m.type === 'image').length})
                  </button>
                  <button
                    type="button"
                    onClick={() => setFilterType('chat')}
                    className={`px-3 py-1 rounded-lg text-xs font-medium transition-all flex items-center gap-1 ${
                      filterType === 'chat' ? 'bg-cyan-500/20 text-cyan-300' : 'text-slate-400 hover:text-white'
                    }`}
                  >
                    <MessageSquare className="w-3 h-3" />
                    Chat/Vision ({modelsList.filter((m) => m.type === 'chat').length})
                  </button>
                </div>
              </div>

              {/* Combo Dropdown Select */}
              <div className="relative">
                <select
                  value={modelName}
                  onChange={(e) => {
                    setModelName(e.target.value);
                    setTestResult(null);
                  }}
                  className="w-full appearance-none pl-4 pr-10 py-3.5 rounded-2xl bg-slate-900 border border-slate-700 text-white font-mono text-sm focus:border-cyan-400 focus:outline-none cursor-pointer"
                >
                  {filteredModels.map((m) => (
                    <option key={m.id} value={m.id} className="bg-slate-900 text-white py-2">
                      {m.type === 'image' ? '🖼️ [IMAGEN] ' : m.type === 'chat' ? '💬 [CHAT] ' : '⚙️ '}
                      {m.id} — {m.name}
                    </option>
                  ))}
                </select>
                <ChevronDown className="w-5 h-5 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" />
              </div>

              {/* Tarjeta Informativa del Modelo Seleccionado */}
              <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 flex items-start justify-between gap-4">
                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <span className="text-sm font-bold text-white">
                      {selectedModelObj?.name || modelName}
                    </span>
                    <span className={`px-2 py-0.5 rounded-md text-[10px] font-bold font-mono ${
                      selectedModelObj?.type === 'image'
                        ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                        : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30'
                    }`}>
                      {selectedModelObj?.type?.toUpperCase() || 'ACTIVO'}
                    </span>
                  </div>
                  <div className="text-xs text-slate-400">
                    ID Técnico: <code className="text-cyan-400 font-mono">{modelName}</code>
                  </div>
                  {selectedModelObj?.description && (
                    <div className="text-[11px] text-slate-500">
                      {selectedModelObj.description}
                    </div>
                  )}
                </div>
              </div>
            </div>
          ) : (
            <div className="space-y-2">
              <input
                type="text"
                value={modelName}
                onChange={(e) => {
                  setModelName(e.target.value);
                  setTestResult(null);
                }}
                placeholder="Ingresá el ID exacto del modelo (ej: dall-e-3, ft:dall-e-3:...)"
                className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-cyan-500/50 text-white font-mono text-sm focus:border-cyan-400 focus:outline-none"
              />
              <p className="text-xs text-slate-400">
                Podés escribir cualquier nombre o ID de modelo personalizado de tu plataforma.
              </p>
            </div>
          )}
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
                  <p className="text-xs text-slate-300 max-w-xl break-words">
                    {testResult.message}
                  </p>
                </div>
              </div>

              {/* Imagen de prueba generada */}
              {testResult.image_url && (
                <div className="relative group self-center md:self-auto">
                  <img
                    src={testResult.image_url}
                    alt="Prueba de Modelo"
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
                <span>Generando imagen de prueba con {modelName}...</span>
              </>
            ) : (
              <>
                <FlaskConical className="w-4 h-4 text-cyan-400" />
                <span>Probar Modelo Seleccionado ({modelName})</span>
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
