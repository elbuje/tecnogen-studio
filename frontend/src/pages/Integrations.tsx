import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { HardDrive, FileSpreadsheet, Share2, Key, Plus, Trash2, Copy, Check, ShieldCheck, AlertCircle } from 'lucide-react';

export const Integrations: React.FC = () => {
  const [brands, setBrands] = useState<any[]>([]);
  const [selectedBrandId, setSelectedBrandId] = useState('');
  const [loading, setLoading] = useState(true);

  // Drive state
  const [inputFolderId, setInputFolderId] = useState('');
  const [outputFolderId, setOutputFolderId] = useState('');
  const [savingDrive, setSavingDrive] = useState(false);

  // Sheets state
  const [sheetsUrl, setSheetsUrl] = useState('');
  const [savingSheets, setSavingSheets] = useState(false);

  // Metricool state
  const [metricoolToken, setMetricoolToken] = useState('');
  const [metricoolBlogId, setMetricoolBlogId] = useState('');
  const [savingMetricool, setSavingMetricool] = useState(false);

  // API Keys state
  const [apiKeys, setApiKeys] = useState<any[]>([]);
  const [newKeyLabel, setNewKeyLabel] = useState('');
  const [createdKey, setCreatedKey] = useState<string | null>(null);
  const [copiedKey, setCopiedKey] = useState(false);
  const [copiedEmail, setCopiedEmail] = useState(false);

  const SERVICE_ACCOUNT = "drive-bot@tecnogen-studio.iam.gserviceaccount.com";

  const fetchAll = async () => {
    try {
      setLoading(true);
      const [brandsRes, keysRes] = await Promise.all([
        api.get('/brands'),
        api.get('/integrations/api-keys'),
      ]);
      setBrands(brandsRes.data);
      setApiKeys(keysRes.data);

      if (brandsRes.data.length > 0) {
        const b = brandsRes.data[0];
        setSelectedBrandId(b.id);
        setInputFolderId(b.gdrive_input_folder_id || '');
        setOutputFolderId(b.gdrive_output_folder_id || '');
        setSheetsUrl(b.sheets_url || '');
        setMetricoolToken(b.metricool_user_token || '');
        setMetricoolBlogId(b.metricool_blog_id || '');
      }
    } catch (e) {
      console.error('Error fetching integrations:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAll();
  }, []);

  const handleBrandChange = (brandId: string) => {
    setSelectedBrandId(brandId);
    const b = brands.find((item) => item.id === brandId);
    if (b) {
      setInputFolderId(b.gdrive_input_folder_id || '');
      setOutputFolderId(b.gdrive_output_folder_id || '');
      setSheetsUrl(b.sheets_url || '');
      setMetricoolToken(b.metricool_user_token || '');
      setMetricoolBlogId(b.metricool_blog_id || '');
    }
  };

  const handleSaveDrive = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBrandId) return;
    try {
      setSavingDrive(true);
      await api.post('/integrations/drive', {
        brand_id: selectedBrandId,
        input_folder_id: inputFolderId,
        output_folder_id: outputFolderId,
      });
      alert('¡Carpetas de Google Drive vinculadas exitosamente!');
    } catch (e) {
      alert('Error al guardar carpetas de Google Drive.');
    } finally {
      setSavingDrive(false);
    }
  };

  const handleSaveSheets = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBrandId) return;
    try {
      setSavingSheets(true);
      await api.post('/integrations/sheets', {
        brand_id: selectedBrandId,
        sheets_url: sheetsUrl,
      });
      alert('¡Google Sheet vinculado exitosamente!');
    } catch (e) {
      alert('Error al guardar Google Sheet.');
    } finally {
      setSavingSheets(false);
    }
  };

  const handleSaveMetricool = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBrandId) return;
    try {
      setSavingMetricool(true);
      await api.post('/integrations/metricool', {
        brand_id: selectedBrandId,
        metricool_user_token: metricoolToken,
        metricool_blog_id: metricoolBlogId,
      });
      alert('¡Cuenta de Metricool conectada exitosamente!');
    } catch (e) {
      alert('Error al guardar credenciales de Metricool.');
    } finally {
      setSavingMetricool(false);
    }
  };

  const handleCreateApiKey = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newKeyLabel.trim()) return;
    try {
      const res = await api.post('/integrations/api-keys', { label: newKeyLabel });
      setCreatedKey(res.data.api_key);
      setNewKeyLabel('');
      await fetchAll();
    } catch (e) {
      alert('Error al generar API Key.');
    }
  };

  const handleRevokeApiKey = async (keyId: string) => {
    if (!confirm('¿Estás seguro de que querés revocar esta API Key?')) return;
    try {
      await api.delete(`/integrations/api-keys/${keyId}`);
      await fetchAll();
    } catch (e) {
      alert('Error al revocar API Key.');
    }
  };

  const copyToClipboard = (text: string, isKey = false) => {
    navigator.clipboard.writeText(text);
    if (isKey) {
      setCopiedKey(true);
      setTimeout(() => setCopiedKey(false), 3000);
    } else {
      setCopiedEmail(true);
      setTimeout(() => setCopiedEmail(false), 3000);
    }
  };

  if (loading) {
    return <div className="p-8 text-center text-slate-400">Cargando integraciones y APIs...</div>;
  }

  return (
    <div className="p-8 max-w-5xl mx-auto space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white">Integraciones & Conexiones Externas</h1>
          <p className="text-sm text-slate-400">
            Conectá tus carpetas de Google Drive, hojas de cálculo, Metricool y creá API Keys para agentes de IA.
          </p>
        </div>

        {brands.length > 1 && (
          <div className="flex items-center gap-2">
            <span className="text-xs text-slate-400">Marca:</span>
            <select
              value={selectedBrandId}
              onChange={(e) => handleBrandChange(e.target.value)}
              className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs"
            >
              {brands.map((b) => (
                <option key={b.id} value={b.id}>
                  {b.name}
                </option>
              ))}
            </select>
          </div>
        )}
      </div>

      {/* 1. Google Drive Zero-Storage */}
      <div className="p-8 rounded-3xl glass-card space-y-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
              <HardDrive className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-white">Google Drive (Zero-Storage)</h2>
              <p className="text-xs text-slate-400">
                Almacená tus fotos de entrada y recibí los carruseles terminados en tu propio Drive.
              </p>
            </div>
          </div>
        </div>

        {/* Instrucciones de Cuenta de Servicio */}
        <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2 text-xs">
          <div className="font-semibold text-white flex items-center gap-1.5">
            <ShieldCheck className="w-4 h-4 text-emerald-400" /> Paso 1: Compartí tu carpeta con nuestro Robot
          </div>
          <p className="text-slate-400 leading-relaxed">
            Creá tus carpetas en Google Drive y compartilas con permisos de <strong>Editor</strong> al siguiente correo:
          </p>
          <div className="flex items-center gap-2 pt-1">
            <code className="px-3 py-1.5 rounded-lg bg-black/60 text-cyan-300 font-mono text-xs border border-cyan-500/30 flex-1 truncate">
              {SERVICE_ACCOUNT}
            </code>
            <button
              onClick={() => copyToClipboard(SERVICE_ACCOUNT)}
              className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs flex items-center gap-1 transition-colors"
            >
              {copiedEmail ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
              {copiedEmail ? 'Copiado' : 'Copiar'}
            </button>
          </div>
        </div>

        {/* Formulario de IDs de Carpeta */}
        <form onSubmit={handleSaveDrive} className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                ID o Link de Carpeta de Fotos (Input)
              </label>
              <input
                type="text"
                value={inputFolderId}
                onChange={(e) => setInputFolderId(e.target.value)}
                placeholder="Ej: 1ABCxyz... (ID de la carpeta en la URL)"
                className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                ID o Link de Carpeta de Carruseles (Output)
              </label>
              <input
                type="text"
                value={outputFolderId}
                onChange={(e) => setOutputFolderId(e.target.value)}
                placeholder="Ej: 1DEFuvw... (ID donde se guardarán)"
                className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
              />
            </div>
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={savingDrive}
              className="px-6 py-2.5 rounded-xl font-bold bg-cyan-500 hover:bg-cyan-400 text-black text-xs transition-all disabled:opacity-50"
            >
              {savingDrive ? 'Guardando...' : 'Guardar Conexión Google Drive'}
            </button>
          </div>
        </form>
      </div>

      {/* 2. Google Sheets */}
      <div className="p-8 rounded-3xl glass-card space-y-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center border border-green-500/20">
            <FileSpreadsheet className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">Google Sheets (Importación de Guiones)</h2>
            <p className="text-xs text-slate-400">
              Conectá tu hoja de cálculo con columnas de Tema, Guión y Estado.
            </p>
          </div>
        </div>

        <form onSubmit={handleSaveSheets} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              URL Completa del Google Sheet
            </label>
            <input
              type="url"
              value={sheetsUrl}
              onChange={(e) => setSheetsUrl(e.target.value)}
              placeholder="https://docs.google.com/spreadsheets/d/1q9f.../edit"
              className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={savingSheets}
              className="px-6 py-2.5 rounded-xl font-bold bg-cyan-500 hover:bg-cyan-400 text-black text-xs transition-all disabled:opacity-50"
            >
              {savingSheets ? 'Guardando...' : 'Vincular Google Sheet'}
            </button>
          </div>
        </form>
      </div>

      {/* 3. Metricool Integration */}
      <div className="p-8 rounded-3xl glass-card space-y-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center border border-cyan-500/20">
            <Share2 className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">Metricool (Programación en 1 Clic)</h2>
            <p className="text-xs text-slate-400">
              Consultá los mejores horarios de audiencia y programá automáticamente en tus redes.
            </p>
          </div>
        </div>

        <form onSubmit={handleSaveMetricool} className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Metricool User Token
              </label>
              <input
                type="password"
                value={metricoolToken}
                onChange={(e) => setMetricoolToken(e.target.value)}
                placeholder="mc_xxxxxxxxxxxxxxxxxx"
                className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Blog ID / Marca en Metricool
              </label>
              <input
                type="text"
                value={metricoolBlogId}
                onChange={(e) => setMetricoolBlogId(e.target.value)}
                placeholder="Ej: 12345"
                className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
              />
            </div>
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={savingMetricool}
              className="px-6 py-2.5 rounded-xl font-bold bg-cyan-500 hover:bg-cyan-400 text-black text-xs transition-all disabled:opacity-50"
            >
              {savingMetricool ? 'Guardando...' : 'Conectar Metricool'}
            </button>
          </div>
        </form>
      </div>

      {/* 4. API Keys para Agentes MCP */}
      <div className="p-8 rounded-3xl glass-card space-y-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
              <Key className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-white">API Keys para Clientes & Agentes MCP</h2>
              <p className="text-xs text-slate-400">
                Conectá Claude Desktop, ChatGPT, Make o Zapier para generar carruseles vía API.
              </p>
            </div>
          </div>
        </div>

        {/* Modal de API Key creada */}
        {createdKey && (
          <div className="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-2 text-xs">
            <div className="font-bold text-amber-300 flex items-center gap-1.5">
              <AlertCircle className="w-4 h-4" /> ¡API Key Creada! Copiala ahora:
            </div>
            <div className="flex items-center gap-2">
              <code className="p-2 rounded-lg bg-black/80 text-white font-mono text-xs flex-1 truncate">
                {createdKey}
              </code>
              <button
                onClick={() => copyToClipboard(createdKey, true)}
                className="px-3 py-2 rounded-lg bg-amber-500 text-black font-bold text-xs flex items-center gap-1"
              >
                {copiedKey ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
                {copiedKey ? 'Copiada' : 'Copiar'}
              </button>
            </div>
          </div>
        )}

        {/* Formulario Crear Key */}
        <form onSubmit={handleCreateApiKey} className="flex gap-3">
          <input
            type="text"
            required
            value={newKeyLabel}
            onChange={(e) => setNewKeyLabel(e.target.value)}
            placeholder="Etiqueta: Ej. Claude Desktop, Make Webhook..."
            className="flex-1 p-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none"
          />
          <button
            type="submit"
            className="px-5 py-3 rounded-xl font-bold bg-purple-600 hover:bg-purple-500 text-white text-xs flex items-center gap-1.5 shadow-lg shadow-purple-600/20"
          >
            <Plus className="w-4 h-4" /> Crear API Key
          </button>
        </form>

        {/* Lista de Keys */}
        <div className="space-y-2 pt-2">
          {apiKeys.length === 0 ? (
            <div className="text-xs text-slate-500 py-2">No hay API Keys generadas aún.</div>
          ) : (
            apiKeys.map((k) => (
              <div
                key={k.id}
                className="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center justify-between text-xs"
              >
                <div className="space-y-0.5">
                  <div className="font-semibold text-white">{k.label || 'API Key'}</div>
                  <div className="text-[11px] font-mono text-slate-400">{k.key_prefix}</div>
                </div>
                <button
                  onClick={() => handleRevokeApiKey(k.id)}
                  className="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                  title="Revocar Clave"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};
