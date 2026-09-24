import React, { useState, useEffect } from 'react';
import api from '../api/client';
import {
  HardDrive,
  FileSpreadsheet,
  Share2,
  Key,
  Plus,
  Trash2,
  Copy,
  Check,
  ShieldCheck,
  AlertCircle,
  HelpCircle,
  FolderKanban,
  Image,
  Users,
  BookOpen,
  LayoutTemplate,
  ShoppingBag
} from 'lucide-react';

export const Integrations: React.FC = () => {
  const [brands, setBrands] = useState<any[]>([]);
  const [selectedBrandId, setSelectedBrandId] = useState('');
  const [loading, setLoading] = useState(true);

  // 5 Specialized Drive Folders state
  const [logosFolderId, setLogosFolderId] = useState('');
  const [subjectsFolderId, setSubjectsFolderId] = useState('');
  const [brandManualFolderId, setBrandManualFolderId] = useState('');
  const [templatesFolderId, setTemplatesFolderId] = useState('');
  const [productsFolderId, setProductsFolderId] = useState('');
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

  const [activeHelp, setActiveHelp] = useState<string | null>(null);

  const SERVICE_ACCOUNT = "727210347000-compute@developer.gserviceaccount.com";

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
        setLogosFolderId(b.gdrive_logos_folder_id || '');
        setSubjectsFolderId(b.gdrive_subjects_folder_id || '');
        setBrandManualFolderId(b.gdrive_brand_manual_folder_id || '');
        setTemplatesFolderId(b.gdrive_templates_folder_id || '');
        setProductsFolderId(b.gdrive_products_folder_id || '');
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
      setLogosFolderId(b.gdrive_logos_folder_id || '');
      setSubjectsFolderId(b.gdrive_subjects_folder_id || '');
      setBrandManualFolderId(b.gdrive_brand_manual_folder_id || '');
      setTemplatesFolderId(b.gdrive_templates_folder_id || '');
      setProductsFolderId(b.gdrive_products_folder_id || '');
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
        logos_folder_id: logosFolderId,
        subjects_folder_id: subjectsFolderId,
        brand_manual_folder_id: brandManualFolderId,
        templates_folder_id: templatesFolderId,
        products_folder_id: productsFolderId,
        output_folder_id: outputFolderId,
      });
      alert('¡Las 5 carpetas especializadas de Google Drive fueron guardadas con éxito!');
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
            Conectá tus carpetas especializadas de Google Drive, Google Sheets, Metricool y API Keys para agentes MCP.
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

      {/* 1. Google Drive Zero-Storage con 5 Carpetas Especializadas */}
      <div className="p-8 rounded-3xl glass-card space-y-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
            <HardDrive className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">Google Drive — 5 Carpetas Especializadas (Zero-Storage)</h2>
            <p className="text-xs text-slate-400">
              Cada carpeta almacena un tipo de activo específico. Hacé clic en <span className="text-cyan-400 font-bold">(?)</span> para ver cómo nombrarlas en el Sheet.
            </p>
          </div>
        </div>

        {/* Instrucciones de Robot Bot */}
        <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2 text-xs">
          <div className="font-semibold text-white flex items-center gap-1.5">
            <ShieldCheck className="w-4 h-4 text-emerald-400" /> Paso 1: Compartí tus carpetas con el Robot de TecnoGen
          </div>
          <p className="text-slate-400 leading-relaxed">
            Creá tus carpetas en tu Google Drive y compartilas con permisos de <strong>Editor</strong> al siguiente correo:
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

        {/* Formulario de las 5 Carpetas Especializadas */}
        <form onSubmit={handleSaveDrive} className="space-y-5">
          {/* Carpeta 1: Logos */}
          <div className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-white flex items-center gap-2">
                <Image className="w-4 h-4 text-cyan-400" /> 1. Carpeta con LOGOS de la Marca
              </label>
              <button
                type="button"
                onClick={() => setActiveHelp(activeHelp === 'logos' ? null : 'logos')}
                className="text-cyan-400 hover:text-cyan-300 text-xs flex items-center gap-1 font-semibold"
              >
                <HelpCircle className="w-3.5 h-3.5" /> ¿Qué va acá y cómo llamarlo?
              </button>
            </div>
            {activeHelp === 'logos' && (
              <div className="p-3 rounded-xl bg-cyan-950/40 border border-cyan-800/60 text-cyan-200 text-xs leading-relaxed space-y-1">
                <div><strong>📌 Contenido:</strong> Logos oficiales en formato PNG con fondo transparente (canal alfa).</div>
                <div><strong>🏷️ En el Sheet / Prompt:</strong> Se referencian como <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">logo_white.png</code>, <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">logo_color.png</code> o <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">logo_black.png</code>.</div>
              </div>
            )}
            <input
              type="text"
              value={logosFolderId}
              onChange={(e) => setLogosFolderId(e.target.value)}
              placeholder="ID o link de la carpeta de Logos en Google Drive (ej: 1aBcDeF...)"
              className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          {/* Carpeta 2: Personajes / Sujetos */}
          <div className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-white flex items-center gap-2">
                <Users className="w-4 h-4 text-blue-400" /> 2. Carpeta con PERSONAJES / PROFESIONALES (Sujetos)
              </label>
              <button
                type="button"
                onClick={() => setActiveHelp(activeHelp === 'subjects' ? null : 'subjects')}
                className="text-cyan-400 hover:text-cyan-300 text-xs flex items-center gap-1 font-semibold"
              >
                <HelpCircle className="w-3.5 h-3.5" /> ¿Qué va acá y cómo llamarlo?
              </button>
            </div>
            {activeHelp === 'subjects' && (
              <div className="p-3 rounded-xl bg-cyan-950/40 border border-cyan-800/60 text-cyan-200 text-xs leading-relaxed space-y-1">
                <div><strong>📌 Contenido:</strong> Fotos de los profesionales, doctores o modelos en alta resolución (fondo blanco, consultorio o recorte).</div>
                <div><strong>🏷️ En el Sheet / Prompt:</strong> En la columna <strong>"Doctora/Sujeto"</strong> poné el nombre exacto del archivo, ej: <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">Jessica1</code>, <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">Jessica2</code>, <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">Karina</code>.</div>
              </div>
            )}
            <input
              type="text"
              value={subjectsFolderId}
              onChange={(e) => setSubjectsFolderId(e.target.value)}
              placeholder="ID o link de la carpeta de Personajes/Fotos en Google Drive"
              className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          {/* Carpeta 3: Manual de Marca */}
          <div className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-white flex items-center gap-2">
                <BookOpen className="w-4 h-4 text-purple-400" /> 3. Carpeta con MANUAL DE MARCA (PDF o Imágenes)
              </label>
              <button
                type="button"
                onClick={() => setActiveHelp(activeHelp === 'manual' ? null : 'manual')}
                className="text-cyan-400 hover:text-cyan-300 text-xs flex items-center gap-1 font-semibold"
              >
                <HelpCircle className="w-3.5 h-3.5" /> ¿Qué va acá y cómo llamarlo?
              </button>
            </div>
            {activeHelp === 'manual' && (
              <div className="p-3 rounded-xl bg-cyan-950/40 border border-cyan-800/60 text-cyan-200 text-xs leading-relaxed space-y-1">
                <div><strong>📌 Contenido:</strong> El archivo PDF oficial o imágenes del Brand Guidelines (paleta cromática, tonos de comunicación, fuentes permitidas).</div>
                <div><strong>🏷️ En el Sheet / Prompt:</strong> Se lee como fuente canónica de la marca para mantener la coherencia estética en todas las generaciones.</div>
              </div>
            )}
            <input
              type="text"
              value={brandManualFolderId}
              onChange={(e) => setBrandManualFolderId(e.target.value)}
              placeholder="ID o link de la carpeta con el Manual de Marca en Google Drive"
              className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          {/* Carpeta 4: Plantillas */}
          <div className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-white flex items-center gap-2">
                <LayoutTemplate className="w-4 h-4 text-amber-400" /> 4. Carpeta con PLANTILLAS & FONDOS (Templates)
              </label>
              <button
                type="button"
                onClick={() => setActiveHelp(activeHelp === 'templates' ? null : 'templates')}
                className="text-cyan-400 hover:text-cyan-300 text-xs flex items-center gap-1 font-semibold"
              >
                <HelpCircle className="w-3.5 h-3.5" /> ¿Qué va acá y cómo llamarlo?
              </button>
            </div>
            {activeHelp === 'templates' && (
              <div className="p-3 rounded-xl bg-cyan-950/40 border border-cyan-800/60 text-cyan-200 text-xs leading-relaxed space-y-1">
                <div><strong>📌 Contenido:</strong> Fondos vectoriales, texturas o layouts prediseñados en PNG/PSD.</div>
                <div><strong>🏷️ En el Sheet / Prompt:</strong> En la columna <strong>"Notas"</strong> podés escribir <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">Template: FondoClinicoAzul</code> para obligar al render a usarlo como capa base.</div>
              </div>
            )}
            <input
              type="text"
              value={templatesFolderId}
              onChange={(e) => setTemplatesFolderId(e.target.value)}
              placeholder="ID o link de la carpeta de Plantillas/Templates en Google Drive"
              className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          {/* Carpeta 5: Productos / Servicios */}
          <div className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-white flex items-center gap-2">
                <ShoppingBag className="w-4 h-4 text-rose-400" /> 5. Carpeta con PRODUCTOS & SERVICIOS
              </label>
              <button
                type="button"
                onClick={() => setActiveHelp(activeHelp === 'products' ? null : 'products')}
                className="text-cyan-400 hover:text-cyan-300 text-xs flex items-center gap-1 font-semibold"
              >
                <HelpCircle className="w-3.5 h-3.5" /> ¿Qué va acá y cómo llamarlo?
              </button>
            </div>
            {activeHelp === 'products' && (
              <div className="p-3 rounded-xl bg-cyan-950/40 border border-cyan-800/60 text-cyan-200 text-xs leading-relaxed space-y-1">
                <div><strong>📌 Contenido:</strong> Fotos de insumos, instrumental, alineadores invisibles, implantes o kits estéticos.</div>
                <div><strong>🏷️ En el Sheet / Prompt:</strong> Nombralos en la columna <strong>"Notas"</strong>, ej: <code className="bg-black/40 px-1.5 py-0.5 rounded text-white font-mono">Producto: AlineadorInvisalign</code>.</div>
              </div>
            )}
            <input
              type="text"
              value={productsFolderId}
              onChange={(e) => setProductsFolderId(e.target.value)}
              placeholder="ID o link de la carpeta de Productos en Google Drive"
              className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          {/* Carpeta Output */}
          <div className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-2">
            <label className="block text-xs font-bold text-white">
              📁 Carpeta de DESTINO (Donde se depositarán los Carruseles Terminados)
            </label>
            <input
              type="text"
              value={outputFolderId}
              onChange={(e) => setOutputFolderId(e.target.value)}
              placeholder="ID o link de la carpeta donde se guardarán los PNGs finales en Drive"
              className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none"
            />
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={savingDrive}
              className="px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 text-xs transition-all disabled:opacity-50"
            >
              {savingDrive ? 'Guardando...' : 'Guardar las 5 Carpetas de Google Drive'}
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
              Conectá tu hoja de cálculo con columnas de Tema, Doctora/Sujeto, Notas y Estado.
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
